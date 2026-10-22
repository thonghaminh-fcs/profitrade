// =============================================================================
// Search Bar — live typeahead with modal (icon mode) or dropdown (inline mode)
// =============================================================================
// Handles all .zs__block instances on the page (multiple placements supported).
// Config per block is read from data-* attributes set by render.php.
// Endpoint, nonce, tunables and every visible string come from window.aiZippy
// (AiZippy\Core\Runtime) — nothing user-facing is hardcoded here.
// =============================================================================

import {
	azEndpoint,
	azNonce,
	azSetting,
	azText,
	azUrlWithQuery,
} from "../core/config.js";

// Read once at init — the config is printed before this bundle runs.
let debounceMs = 260;
let minChars   = 2;

export function initSearchBar() {
	if (!searchEndpoint()) return;

	debounceMs = azSetting("searchDebounce", debounceMs);
	minChars   = azSetting("searchMinChars", minChars);

	document.querySelectorAll(".zs__block").forEach(initBlock);

	// Global Esc key closes any open modal
	document.addEventListener("keydown", (e) => {
		if (e.key === "Escape") {
			document.querySelectorAll(".zs__modal:not([hidden])").forEach(closeModal);
		}
	});
}

// =============================================================================
// Per-block init
// =============================================================================

function initBlock(block) {
	const mode       = block.dataset.mode       || "inline";
	const scope      = block.dataset.scope      || "products";
	const maxResults =
		parseInt(block.dataset.maxResults, 10) || azSetting("searchMaxResults", 8);

	if (mode === "icon") {
		initIconMode(block, scope, maxResults);
	} else {
		initInlineMode(block, scope, maxResults);
	}
}

// -----------------------------------------------------------------------
// Icon mode: button → modal
// -----------------------------------------------------------------------

function initIconMode(block, scope, maxResults) {
	const trigger = block.querySelector(".zs__icon-trigger");
	const modal   = block.querySelector(".zs__modal");
	if (!trigger || !modal) return;

	const input    = modal.querySelector(".zs__input");
	const results  = modal.querySelector(".zs__results");
	const clearBtn = modal.querySelector(".zs__clear");
	const closeBtn = modal.querySelector(".zs__modal-close");
	const backdrop = modal.querySelector(".zs__modal-backdrop");

	// The trigger is a real link (browse/search fallback for the no-JS path);
	// with the typeahead active we open the modal instead.
	trigger.addEventListener("click", (e) => {
		e.preventDefault();
		openModal(modal, input);
	});
	backdrop.addEventListener("click", () => closeModal(modal));
	if (closeBtn) {
		closeBtn.addEventListener("click", () => closeModal(modal));
	}

	if (input && results) {
		wireInputToResults(input, results, clearBtn, scope, maxResults, true);
	}
}

function openModal(modal, input) {
	modal.hidden = false;
	document.body.style.overflow = "hidden";
	const trigger = modal.previousElementSibling;
	if (trigger) trigger.setAttribute("aria-expanded", "true");
	// Focus after paint so the input is rendered
	requestAnimationFrame(() => input && input.focus());
}

function closeModal(modal) {
	modal.hidden = true;
	document.body.style.overflow = "";
	const trigger = modal.previousElementSibling;
	if (trigger) {
		trigger.setAttribute("aria-expanded", "false");
		trigger.focus();
	}
	const input   = modal.querySelector(".zs__input");
	const results = modal.querySelector(".zs__results");
	const clearBtn = modal.querySelector(".zs__clear");
	if (input) input.value = "";
	toggleClear(clearBtn, false);
	if (results) clearResults(results);
}

// -----------------------------------------------------------------------
// Inline mode: dropdown below input
// -----------------------------------------------------------------------

function initInlineMode(block, scope, maxResults) {
	const input    = block.querySelector(".zs__input");
	const results  = block.querySelector(".zs__results");
	const clearBtn = block.querySelector(".zs__clear");
	if (!input || !results) return;

	wireInputToResults(input, results, clearBtn, scope, maxResults, false);

	// Close dropdown when clicking outside
	document.addEventListener("click", (e) => {
		if (!block.contains(e.target)) {
			clearResults(results);
		}
	});
}

// =============================================================================
// Core: wire input → debounce → fetch → render
// =============================================================================

function wireInputToResults(input, results, clearBtn, scope, maxResults, isModal) {
	let debounceTimer = null;
	let activeIndex   = -1;
	let currentQuery  = "";

	// The input sits in a real GET form so search still works with the typeahead
	// off; while it's on we drive navigation ourselves, so stop the native submit.
	input.closest("form")?.addEventListener("submit", (e) => e.preventDefault());

	// ---- Input handler ----
	input.addEventListener("input", () => {
		const q = input.value.trim();
		currentQuery = q;
		toggleClear(clearBtn, q.length > 0);

		clearTimeout(debounceTimer);

		if (q.length < minChars) {
			clearResults(results);
			return;
		}

		debounceTimer = setTimeout(() => {
			fetchResults(q, scope, maxResults).then((items) => {
				// Discard stale response
				if (q !== input.value.trim()) return;
				activeIndex = -1;
				renderResults(results, items, q, scope, isModal);
			});
		}, debounceMs);
	});

	// ---- Keyboard nav ----
	input.addEventListener("keydown", (e) => {
		const items = results.querySelectorAll(".zs__item");

		if (e.key === "ArrowDown") {
			e.preventDefault();
			activeIndex = Math.min(activeIndex + 1, items.length - 1);
			setActive(items, activeIndex);
		} else if (e.key === "ArrowUp") {
			e.preventDefault();
			activeIndex = Math.max(activeIndex - 1, -1);
			setActive(items, activeIndex);
		} else if (e.key === "Enter") {
			e.preventDefault();
			if (activeIndex >= 0 && items[activeIndex]) {
				items[activeIndex].click();
			} else if (input.value.trim().length >= minChars) {
				// Fallback: navigate to the site search page
				window.location.href = azUrlWithQuery("search", {
					s: input.value.trim(),
				});
			}
		} else if (e.key === "Escape" && !isModal) {
			clearResults(results);
		}
	});

	// ---- Clear button ----
	if (clearBtn) {
		clearBtn.addEventListener("click", () => {
			input.value = "";
			toggleClear(clearBtn, false);
			clearResults(results);
			input.focus();
		});
	}
}

// =============================================================================
// Fetch
// =============================================================================

async function fetchResults(q, scope, maxResults) {
	const url = new URL(searchEndpoint());
	url.searchParams.set("q", q);
	url.searchParams.set("scope", scope);
	url.searchParams.set("per_page", maxResults);

	try {
		const res = await fetch(url.toString(), {
			headers: { "X-WP-Nonce": searchNonce() },
		});
		if (!res.ok) return [];
		const data = await res.json();
		return data.results || [];
	} catch {
		return [];
	}
}

// =============================================================================
// Render
// =============================================================================

function renderResults(container, items, query, scope, isModal) {
	if (!items.length) {
		const empty = azText(
			"search.no_results",
			"No results for “%s”",
			escHtml(query),
		);
		container.innerHTML = `<div class="zs__state">${empty}</div>`;
		container.classList.add("is-open");
		return;
	}

	// Separate products and posts
	const products = items.filter((i) => i.type === "product");
	const posts    = items.filter((i) => i.type === "post");

	let html = "";

	if (products.length) {
		if (scope === "both") {
			html += `<div class="zs__group-label">${escHtml(azText("search.group_products", "Products"))}</div>`;
		}
		products.forEach((item) => {
			html += renderProductItem(item, query);
		});
	}

	if (posts.length) {
		if (scope === "both") {
			html += `<div class="zs__group-label">${escHtml(azText("search.group_posts", "Blog Posts"))}</div>`;
		}
		posts.forEach((item) => {
			html += renderPostItem(item, query);
		});
	}

	// "View all" link for products
	if (products.length && scope !== "posts") {
		const searchUrl = azUrlWithQuery("shop", { s: query });
		const label = azText(
			"search.view_all",
			"View all results for %s",
			`<strong>${escHtml(query)}</strong>`,
		);
		html += `<a href="${escAttr(searchUrl)}" class="zs__view-all">${label} →</a>`;
	}

	container.innerHTML = html;
	container.classList.add("is-open");

	// Attach click listeners (close modal / clear inline)
	container.querySelectorAll(".zs__item").forEach((el) => {
		el.addEventListener("click", () => {
			if (isModal) {
				const modal = container.closest(".zs__modal");
				if (modal) closeModal(modal);
			}
		});
	});
}

function renderProductItem(item, query) {
	const thumb = item.image
		? `<img src="${escAttr(item.image)}" alt="${escAttr(item.title)}" loading="lazy" width="48" height="48">`
		: svgPlaceholder();

	const badges = [];
	if (item.on_sale) {
		badges.push(`<span class="zs__item-badge zs__item-badge--sale">${escHtml(azText("search.badge_sale", "Sale"))}</span>`);
	}
	if (!item.in_stock) {
		badges.push(`<span class="zs__item-badge zs__item-badge--oos">${escHtml(azText("search.badge_oos", "Out of stock"))}</span>`);
	}

	const meta = [
		item.category  ? `<span class="zs__item-cat">${escHtml(item.category)}</span>` : "",
		item.sku       ? `<span class="zs__item-sku">${azText("search.sku", "SKU: %s", escHtml(item.sku))}</span>` : "",
		...badges,
	].filter(Boolean).join(" · ");

	return `
<a href="${escAttr(item.url)}" class="zs__item" role="option" tabindex="-1">
	<div class="zs__item-thumb">
		${thumb}
	</div>
	<div class="zs__item-body">
		<div class="zs__item-title">${highlight(item.title, query)}</div>
		${meta ? `<div class="zs__item-meta">${meta}</div>` : ""}
	</div>
	${item.price_html ? `<div class="zs__item-price">${item.price_html}</div>` : ""}
</a>`;
}

function renderPostItem(item, query) {
	const thumb = item.image
		? `<img src="${escAttr(item.image)}" alt="${escAttr(item.title)}" loading="lazy" width="48" height="48">`
		: svgDocIcon();

	return `
<a href="${escAttr(item.url)}" class="zs__item" role="option" tabindex="-1">
	<div class="zs__item-thumb zs__item-thumb--post">
		${thumb}
	</div>
	<div class="zs__item-body">
		<div class="zs__item-title">${highlight(item.title, query)}</div>
		<div class="zs__item-meta">
			${item.date ? `<span>${escHtml(item.date)}</span>` : ""}
			${item.excerpt ? `<span>${escHtml(item.excerpt)}</span>` : ""}
		</div>
	</div>
</a>`;
}

// =============================================================================
// Helpers
// =============================================================================

/**
 * Search endpoint, falling back to the legacy window.aiZippySearch global so a
 * stale bundle/config combination still resolves.
 */
function searchEndpoint() {
	return azEndpoint("search", window.aiZippySearch?.apiUrl || "");
}

function searchNonce() {
	return azNonce() || window.aiZippySearch?.nonce || "";
}

function clearResults(container) {
	container.innerHTML = "";
	container.classList.remove("is-open");
}

function toggleClear(btn, show) {
	if (!btn) return;
	btn.hidden = !show;
}

function setActive(items, index) {
	items.forEach((el, i) => {
		el.classList.toggle("is-active", i === index);
		if (i === index) el.scrollIntoView({ block: "nearest" });
	});
}

function highlight(text, query) {
	if (!query) return escHtml(text);
	const escaped = escHtml(text);
	const re = new RegExp(`(${escRegex(query)})`, "gi");
	return escaped.replace(re, "<mark>$1</mark>");
}

function escHtml(str) {
	return String(str)
		.replace(/&/g, "&amp;")
		.replace(/</g, "&lt;")
		.replace(/>/g, "&gt;")
		.replace(/"/g, "&quot;");
}

function escAttr(str) {
	return String(str).replace(/"/g, "&quot;");
}

function escRegex(str) {
	return str.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
}

function svgPlaceholder() {
	return `<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#ddd" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9l9-6 9 6"/><path d="M9 22V12h6v10"/></svg>`;
}

function svgDocIcon() {
	return `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#ccc" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>`;
}
