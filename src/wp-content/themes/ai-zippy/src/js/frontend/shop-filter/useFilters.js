import { useState, useCallback, useRef, useEffect } from "react";

const DEFAULTS = {
	search: "",
	category: "",
	min_price: 0,
	max_price: 0,
	attributes: "",
	stock_status: "",
	orderby: "menu_order",
	order: "ASC",
	page: 1,
	per_page: 12,
};

// Query keys this app owns. Anything else in the URL (utm_*, etc.) is left alone.
const OWNED_KEYS = Object.keys(DEFAULTS);

// Must stay in sync with SORT_OPTIONS in Toolbar.jsx — an orderby the <select>
// can't represent would leave it showing a stale label while the API sorted
// by something else.
const ALLOWED_ORDERBY = ["menu_order", "date", "price", "rating", "popularity"];
const ALLOWED_ORDER = ["ASC", "DESC"];

function readFiltersFromUrl() {
	const params = new URLSearchParams(window.location.search);
	const initial = { ...DEFAULTS };

	for (const key of OWNED_KEYS) {
		const raw = params.get(key);
		if (raw === null) continue;

		if (typeof DEFAULTS[key] === "number") {
			const num = Number(raw);
			if (Number.isFinite(num) && num >= 0) initial[key] = num;
		} else {
			initial[key] = raw;
		}
	}

	// WooCommerce's native sort links use a combined "price-desc" value.
	if (initial.orderby === "price-desc") {
		initial.orderby = "price";
		initial.order = "DESC";
	}

	initial.order = String(initial.order).toUpperCase();
	if (!ALLOWED_ORDERBY.includes(initial.orderby)) initial.orderby = DEFAULTS.orderby;
	if (!ALLOWED_ORDER.includes(initial.order)) initial.order = DEFAULTS.order;

	initial.page = Math.max(1, Math.floor(initial.page) || DEFAULTS.page);
	initial.per_page = Math.min(
		100,
		Math.max(1, Math.floor(initial.per_page) || DEFAULTS.per_page),
	);

	return initial;
}

export default function useFilters(config = {}) {
	// Resolves state from the current URL plus PHP config. Used for the initial
	// state and again on popstate, so back/forward re-seeds identically.
	const resolveFilters = useCallback(() => {
		const initial = readFiltersFromUrl();

		// initial_category pre-seeds the filter on /product-category/* pages.
		// An explicit ?category= in the URL takes precedence over it.
		if (config.initial_category && !initial.category) {
			initial.category = config.initial_category;
		}

		if (config.per_page && !new URLSearchParams(window.location.search).has("per_page")) {
			initial.per_page = Number(config.per_page);
		}

		return initial;
	}, [config.initial_category, config.per_page]);

	const [filters, setFilters] = useState(resolveFilters);

	const timeoutRef = useRef(null);

	const updateFilter = useCallback((key, value) => {
		setFilters((prev) => ({
			...prev,
			[key]: value,
			page: key === "page" ? value : 1, // Reset page when filter changes
		}));
	}, []);

	const updateMultiple = useCallback((updates) => {
		setFilters((prev) => ({
			...prev,
			...updates,
			page: updates.page ?? 1,
		}));
	}, []);

	const resetFilters = useCallback(() => {
		setFilters({ ...DEFAULTS });
	}, []);

	const setSearch = useCallback(
		(value) => {
			// Debounce search
			clearTimeout(timeoutRef.current);
			timeoutRef.current = setTimeout(() => {
				updateFilter("search", value);
			}, 300);
		},
		[updateFilter],
	);

	// Sync filters to URL as a history entry, so back/forward walks the filter
	// trail. Foreign params are preserved — only the keys this app owns change.
	//
	// isFirstSync: skipped on mount, since this effect used to run with the
	// initial state and rewrite the URL from it, stripping whatever the visitor
	// arrived with.
	// isPopState: a back/forward restore already moved the history cursor, so
	// pushing the same URL again would trap the user on the current entry.
	const isFirstSync = useRef(true);
	const isPopState = useRef(false);

	useEffect(() => {
		if (isFirstSync.current) {
			isFirstSync.current = false;
			return;
		}

		if (isPopState.current) {
			isPopState.current = false;
			return;
		}

		const params = new URLSearchParams(window.location.search);

		for (const key of OWNED_KEYS) {
			const value = filters[key];
			if (value !== DEFAULTS[key] && value !== "" && value !== 0) {
				params.set(key, value);
			} else {
				params.delete(key);
			}
		}

		const qs = params.toString();
		const url = window.location.pathname + (qs ? `?${qs}` : "");

		// Nothing to record when the filter change is a no-op for the URL.
		if (url === window.location.pathname + window.location.search) return;

		window.history.pushState(null, "", url);
	}, [filters]);

	// Restore state when the user navigates back/forward.
	useEffect(() => {
		const onPopState = () => {
			isPopState.current = true;
			setFilters(resolveFilters());
		};

		window.addEventListener("popstate", onPopState);
		return () => window.removeEventListener("popstate", onPopState);
	}, [resolveFilters]);

	// Build attributes string from object: { pa_color: ['red'], pa_size: ['l'] } -> "pa_color:red|pa_size:l"
	const toggleAttribute = useCallback(
		(taxonomy, termSlug) => {
			setFilters((prev) => {
				const current = prev.attributes ? parseAttributes(prev.attributes) : {};

				if (!current[taxonomy]) {
					current[taxonomy] = [];
				}

				const idx = current[taxonomy].indexOf(termSlug);
				if (idx === -1) {
					current[taxonomy].push(termSlug);
				} else {
					current[taxonomy].splice(idx, 1);
				}

				if (current[taxonomy].length === 0) {
					delete current[taxonomy];
				}

				return {
					...prev,
					attributes: serializeAttributes(current),
					page: 1,
				};
			});
		},
		[],
	);

	const toggleCategory = useCallback(
		(slug) => {
			setFilters((prev) => {
				const current = prev.category ? prev.category.split(",") : [];
				const idx = current.indexOf(slug);

				if (idx === -1) {
					current.push(slug);
				} else {
					current.splice(idx, 1);
				}

				return { ...prev, category: current.join(","), page: 1 };
			});
		},
		[],
	);

	return {
		filters,
		updateFilter,
		updateMultiple,
		resetFilters,
		setSearch,
		toggleAttribute,
		toggleCategory,
	};
}

function parseAttributes(str) {
	const result = {};
	if (!str) return result;

	str.split("|").forEach((group) => {
		const [taxonomy, terms] = group.split(":");
		if (taxonomy && terms) {
			result[taxonomy] = terms.split(",");
		}
	});
	return result;
}

function serializeAttributes(obj) {
	return Object.entries(obj)
		.filter(([, terms]) => terms.length > 0)
		.map(([taxonomy, terms]) => `${taxonomy}:${terms.join(",")}`)
		.join("|");
}
