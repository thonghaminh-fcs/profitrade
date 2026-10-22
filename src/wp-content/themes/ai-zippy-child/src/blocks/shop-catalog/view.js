// =============================================================================
// Shop Catalog frontend behaviour (docs/build/blocks/shop-catalog.md,
// logic.js:80-94, 106-113, 133-136): client-side category/search filtering
// and sorting over the catalog JSON printed by render.php, quickview modal,
// Woo Store API cart adds with toast + mini-cart refresh, ?q=/?cat= URL sync.
// Vanilla JS; card markup is server-rendered, this only hides/reorders it.
// =============================================================================

(function () {
	const root = document.querySelector(".sm-shop");
	if (!root) return;

	const dataEl = root.querySelector("[data-sm-catalog]");
	if (!dataEl) return;

	let data;
	try {
		data = JSON.parse(dataEl.textContent);
	} catch (_) {
		return;
	}

	const products = data.products || [];
	const byId = new Map(products.map((p) => [Number(p.id), p]));
	const state = {
		q: (data.state && data.state.q) || "",
		cat: data.state && data.state.cat != null ? data.state.cat : "all",
		sort: "popular",
	};

	const money = (n) => "$" + Number(n).toFixed(2);
	const $ = (sel) => root.querySelector(sel);

	const grid = $("[data-sm-grid]");
	const titleEl = $("[data-sm-title]");
	const countEl = $("[data-sm-count]");
	const emptyEl = $("[data-sm-empty]");
	const emptyTextEl = $("[data-sm-empty-text]");
	const sortSelect = $("[data-sm-sort]");
	const catButtons = Array.from(root.querySelectorAll("[data-sm-cat]"));
	const modal = $("[data-sm-modal]");

	// ---------------------------------------------------------------------------
	// Filtering / sorting (mirrors the server-side first paint)
	// ---------------------------------------------------------------------------
	function matches(p) {
		const q = state.q.trim().toLowerCase();
		const catOk =
			state.cat === "all" || String(p.catId) === String(state.cat);
		const qOk = !q || (p.name + " " + p.catName).toLowerCase().includes(q);
		return catOk && qOk;
	}

	function sorted(list) {
		return list.slice().sort((a, b) =>
			state.sort === "low"
				? a.basePrice - b.basePrice
				: state.sort === "high"
				? b.basePrice - a.basePrice
				: a.pop - b.pop
		);
	}

	function syncUrl() {
		const url = new URL(window.location.href);
		const q = state.q.trim();
		if (q) url.searchParams.set("q", q);
		else url.searchParams.delete("q");
		if (state.cat !== "all") url.searchParams.set("cat", String(state.cat));
		else url.searchParams.delete("cat");
		window.history.replaceState({}, "", url);
	}

	function catName(catId) {
		const btn = root.querySelector(
			`[data-sm-cat="${String(catId).replace(/"/g, "")}"]`
		);
		if (btn && btn.dataset.smName) return btn.dataset.smName;
		const p = products.find((x) => String(x.catId) === String(catId));
		return p ? p.catName : "All products";
	}

	function render() {
		if (!grid) return;
		const visible = sorted(products.filter(matches));
		const visibleIds = new Set(visible.map((p) => Number(p.id)));

		grid.querySelectorAll("[data-sm-card]").forEach((card) => {
			card.hidden = !visibleIds.has(Number(card.dataset.smId));
		});
		// Reorder so the DOM order matches the active sort.
		visible.forEach((p) => {
			const card = grid.querySelector(
				`[data-sm-card][data-sm-id="${Number(p.id)}"]`
			);
			if (card) grid.appendChild(card);
		});

		const n = visible.length;
		if (countEl) {
			countEl.textContent = `${n} item${n === 1 ? "" : "s"}${
				state.q.trim() ? ` for "${state.q.trim()}"` : ""
			}`;
		}
		if (titleEl) {
			titleEl.textContent = state.cat === "all" ? "All products" : catName(state.cat);
		}
		if (emptyEl) emptyEl.hidden = n !== 0;
		if (emptyTextEl) emptyTextEl.textContent = `No products match "${state.q.trim()}"`;
		catButtons.forEach((b) =>
			b.classList.toggle("is-active", String(b.dataset.smCat) === String(state.cat))
		);
	}

	catButtons.forEach((b) =>
		b.addEventListener("click", () => {
			state.cat = b.dataset.smCat;
			syncUrl();
			render();
		})
	);
	if (sortSelect) {
		sortSelect.addEventListener("change", () => {
			state.sort = sortSelect.value;
			render();
		});
	}
	const clearBtn = $("[data-sm-clear]");
	if (clearBtn) {
		clearBtn.addEventListener("click", () => {
			state.q = "";
			state.cat = "all";
			syncUrl();
			render();
		});
	}

	// ---------------------------------------------------------------------------
	// Toast — dark pill, green dot, auto-hides after 1800ms (logic.js:67)
	// ---------------------------------------------------------------------------
	const toastEl = $("[data-sm-toast]");
	const toastTextEl = $("[data-sm-toast-text]");
	let toastTimer = 0;

	function showToast(message) {
		if (!toastEl || !toastTextEl) return;
		toastTextEl.textContent = message;
		toastEl.classList.add("is-visible");
		clearTimeout(toastTimer);
		toastTimer = setTimeout(() => toastEl.classList.remove("is-visible"), 1800);
	}

	// ---------------------------------------------------------------------------
	// Cart — Woo Store API add-item; the parent mini-cart listens for
	// wc-blocks_added_to_cart (with detail.cart) and repaints from it.
	// ---------------------------------------------------------------------------
	const restUrl = String(data.restUrl || "").replace(/\/$/, "");

	async function addToCart(product, variantIndex, qty, btn) {
		const variant = (product.variants || [])[variantIndex] || {};
		const body = { id: Number(product.id), quantity: qty };
		if (
			product.type === "variable" &&
			Array.isArray(variant.variation) &&
			variant.variation.length
		) {
			body.variation = variant.variation;
		}
		if (btn) {
			btn.disabled = true;
			btn.classList.add("is-loading");
		}
		try {
			const headers = { "Content-Type": "application/json" };
			const nonce =
				window.wcBlocksMiddlewareConfig &&
				window.wcBlocksMiddlewareConfig.storeApiNonce;
			if (nonce) headers["Nonce"] = nonce;

			const res = await fetch(`${restUrl}/cart/add-item`, {
				method: "POST",
				headers,
				body: JSON.stringify(body),
			});
			if (!res.ok) {
				const err = await res.json().catch(() => ({}));
				throw new Error(err.message || "Could not add to cart");
			}
			const cart = await res.json();
			document.body.dispatchEvent(
				new CustomEvent("wc-blocks_added_to_cart", {
					detail: { cart },
					bubbles: true,
				})
			);
			showToast(`Added ${qty} × ${product.name}`);
			return true;
		} catch (err) {
			showToast(err.message || "Could not add to cart");
			return false;
		} finally {
			if (btn) {
				btn.disabled = false;
				btn.classList.remove("is-loading");
			}
		}
	}

	// ---------------------------------------------------------------------------
	// Quickview modal
	// ---------------------------------------------------------------------------
	const panel = $("[data-sm-modal-panel]");
	const mBlob = $("[data-sm-modal-blob]");
	const mImg = $("[data-sm-modal-img]");
	const mCat = $("[data-sm-modal-cat]");
	const mName = $("[data-sm-modal-name]");
	const mDesc = $("[data-sm-modal-desc]");
	const mVTitle = $("[data-sm-modal-vtitle]");
	const mVariants = $("[data-sm-modal-variants]");
	const mSpecs = $("[data-sm-modal-specs]");
	const mQty = $("[data-sm-modal-qty]");
	const mTotal = $("[data-sm-modal-total]");
	const mStock = $("[data-sm-modal-stock]");
	const mAdd = $("[data-sm-modal-add]");
	const mClose = $("[data-sm-modal-close]");
	let mState = { id: 0, variant: 0, qty: 1 };
	let lastFocus = null;

	function renderVariants(p) {
		if (!mVariants) return;
		mVariants.textContent = "";
		(p.variants || []).forEach((v, i) => {
			const b = document.createElement("button");
			b.type = "button";
			b.className = "sm-shop__variant" + (i === mState.variant ? " is-selected" : "");
			const label = document.createElement("span");
			label.textContent = v.label;
			const price = document.createElement("span");
			price.className = "sm-shop__variant-price";
			price.textContent = money(v.price);
			b.append(label, price);
			b.addEventListener("click", () => {
				mState.variant = i;
				renderVariants(p);
				updateTotal(p);
			});
			mVariants.appendChild(b);
		});
	}

	function renderSpecs(p) {
		if (!mSpecs) return;
		mSpecs.textContent = "";
		const rows = (p.specs || []).map((row) => ({
			k: Array.isArray(row) ? row[0] : row.k,
			v: Array.isArray(row) ? row[1] : row.v,
		}));
		rows.push({
			k: "Type",
			v: p.type === "variable" ? "Variable product" : "Simple product",
		});
		rows.forEach((row) => {
			const k = document.createElement("span");
			k.textContent = row.k;
			const v = document.createElement("span");
			v.className = "sm-shop__spec-v";
			v.textContent = row.v;
			mSpecs.append(k, v);
		});
	}

	function updateTotal(p) {
		const v = (p.variants || [])[mState.variant];
		if (mTotal) mTotal.textContent = v ? money(v.price * mState.qty) : "";
		if (mQty) mQty.textContent = String(mState.qty);
	}

	function openModal(id) {
		const p = byId.get(Number(id));
		if (!p || !modal) return;
		mState = { id: Number(p.id), variant: 0, qty: 1 };

		if (mBlob) mBlob.style.background = p.tint;
		if (mImg) {
			mImg.src = p.img;
			mImg.alt = p.name;
		}
		if (mCat) mCat.textContent = p.catName;
		if (mName) mName.textContent = p.name;
		if (mDesc) mDesc.textContent = p.desc;
		if (mVTitle) mVTitle.textContent = p.variantTitle;
		renderVariants(p);
		renderSpecs(p);
		updateTotal(p);
		if (mStock) {
			mStock.textContent =
				"● " +
				(p.stock == null ? "" : p.stock + " ") +
				"in stock at store · synced with POS";
		}
		if (panel) panel.setAttribute("aria-label", p.name);

		lastFocus = document.activeElement;
		modal.classList.add("is-open");
		modal.setAttribute("aria-hidden", "false");
		document.body.classList.add("sm-shop-lock");
		if (mClose) mClose.focus();
	}

	function closeModal() {
		if (!modal || !modal.classList.contains("is-open")) return;
		modal.classList.remove("is-open");
		modal.setAttribute("aria-hidden", "true");
		document.body.classList.remove("sm-shop-lock");
		if (lastFocus && lastFocus.isConnected) lastFocus.focus();
	}

	if (grid) {
		grid.addEventListener("click", (e) => {
			const quickAdd = e.target.closest("[data-sm-quick-add]");
			if (quickAdd) {
				e.stopPropagation();
				const card = quickAdd.closest("[data-sm-card]");
				const p = card && byId.get(Number(card.dataset.smId));
				// Quick-add adds the first variant, qty 1 (logic.js:793).
				if (p) addToCart(p, 0, 1, quickAdd);
				return;
			}
			const card = e.target.closest("[data-sm-card]");
			if (card) openModal(card.dataset.smId);
		});
	}

	if (modal) {
		modal.addEventListener("click", (e) => {
			if (!e.target.closest("[data-sm-modal-panel]")) closeModal();
		});
	}
	if (mClose) mClose.addEventListener("click", closeModal);
	document.addEventListener("keydown", (e) => {
		if (e.key === "Escape") closeModal();
	});

	const decBtn = $("[data-sm-modal-dec]");
	const incBtn = $("[data-sm-modal-inc]");
	if (decBtn) {
		decBtn.addEventListener("click", () => {
			mState.qty = Math.max(1, mState.qty - 1);
			updateTotal(byId.get(mState.id) || {});
		});
	}
	if (incBtn) {
		incBtn.addEventListener("click", () => {
			mState.qty += 1;
			updateTotal(byId.get(mState.id) || {});
		});
	}
	if (mAdd) {
		mAdd.addEventListener("click", async () => {
			const p = byId.get(mState.id);
			if (!p) return;
			const ok = await addToCart(p, mState.variant, mState.qty, mAdd);
			if (ok) closeModal(); // logic.js:848 — add then close.
		});
	}

	render();
})();
