/**
 * Shop page — Grid / List view toggle with localStorage persistence.
 */
export function initShopViewToggle() {
	const toggle = document.querySelector(".shop-view-toggle");
	if (!toggle) return;

	const gridBtn = createViewBtn("grid", true);
	const listBtn = createViewBtn("list", false);

	toggle.prepend(listBtn);
	toggle.prepend(gridBtn);

	const productList = document.querySelector(".shop-product-list");
	if (!productList) return;

	// Restore saved preference
	const saved = localStorage.getItem("shop-view");
	if (saved === "list") {
		productList.classList.add("is-list-view");
		gridBtn.classList.remove("is-active");
		listBtn.classList.add("is-active");
	}

	gridBtn.addEventListener("click", () => {
		productList.classList.remove("is-list-view");
		gridBtn.classList.add("is-active");
		listBtn.classList.remove("is-active");
		localStorage.setItem("shop-view", "grid");
	});

	listBtn.addEventListener("click", () => {
		productList.classList.add("is-list-view");
		listBtn.classList.add("is-active");
		gridBtn.classList.remove("is-active");
		localStorage.setItem("shop-view", "list");
	});
}

function createViewBtn(type, isActive) {
	const btn = document.createElement("button");
	btn.className = `shop-view-btn${isActive ? " is-active" : ""}`;
	btn.setAttribute("aria-label", `${type} view`);
	btn.innerHTML =
		type === "grid"
			? '<svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><rect x="0" y="0" width="7" height="7" rx="1"/><rect x="9" y="0" width="7" height="7" rx="1"/><rect x="0" y="9" width="7" height="7" rx="1"/><rect x="9" y="9" width="7" height="7" rx="1"/></svg>'
			: '<svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><rect x="0" y="0" width="16" height="4" rx="1"/><rect x="0" y="6" width="16" height="4" rx="1"/><rect x="0" y="12" width="16" height="4" rx="1"/></svg>';
	return btn;
}
