// =============================================================================
// Single product page enhancements
// =============================================================================
// Bootstrap on DOMContentLoaded — modules are gated by element presence so any
// can be missing without breaking the others.
// =============================================================================

import { initLightbox }      from "./lightbox.js";
import { initVariations }    from "./variations.js";
import { initStickyCart }    from "./sticky-cart.js";
import { initLazyRelated }   from "./lazy-related.js";
import { initQuantity }      from "./quantity.js";
import { initBreadcrumb }    from "./breadcrumb.js";
import { initTabs }          from "./tabs.js";

function boot() {
	initQuantity();
	initLightbox();
	initVariations();
	initStickyCart();
	initLazyRelated();
	initBreadcrumb();
	initTabs();
}

if (document.readyState === "loading") {
	document.addEventListener("DOMContentLoaded", boot, { once: true });
} else {
	boot();
}
