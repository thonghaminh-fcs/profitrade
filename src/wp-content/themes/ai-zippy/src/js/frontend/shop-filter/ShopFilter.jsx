import { useState, useEffect, useCallback } from "react";
import { fetchProducts, fetchFilterOptions } from "./api.js";
import useFilters from "./useFilters.js";
import FilterPanel from "./components/FilterPanel.jsx";
import ProductGrid from "./components/ProductGrid.jsx";
import Toolbar from "./components/Toolbar.jsx";
import ActiveFilters from "./components/ActiveFilters.jsx";
import Pagination from "./components/Pagination.jsx";
import "./shop-filter.scss";

export default function ShopFilter({ config }) {
	const [layout, setLayout] = useState(
		() => localStorage.getItem("shop-layout") || config.layout || "sidebar",
	);
	const [viewMode, setViewMode] = useState(
		() => localStorage.getItem("shop-view") || "grid",
	);
	const [options, setOptions] = useState(null);
	const [products, setProducts] = useState([]);
	const [total, setTotal] = useState(0);
	const [pages, setPages] = useState(0);
	const [loading, setLoading] = useState(true);
	const [mobileFilterOpen, setMobileFilterOpen] = useState(false);

	const {
		filters,
		updateFilter,
		updateMultiple,
		resetFilters,
		setSearch,
		toggleAttribute,
		toggleCategory,
	} = useFilters(config);

	// Permalink-agnostic REST base from PHP (see ShopAssets). Undefined on a
	// stale bundle, in which case api.js falls back to the pretty /wp-json path.
	const restBase = config.rest_url;

	// Load filter options once
	useEffect(() => {
		fetchFilterOptions(restBase).then(setOptions).catch(console.error);
	}, [restBase]);

	// Load products when filters change
	useEffect(() => {
		setLoading(true);
		fetchProducts(filters, restBase)
			.then((data) => {
				setProducts(data.products);
				setTotal(data.total);
				setPages(data.pages);
			})
			.catch(console.error)
			.finally(() => setLoading(false));
	}, [filters]);

	const handleLayoutChange = useCallback((mode) => {
		setLayout(mode);
		localStorage.setItem("shop-layout", mode);
	}, []);

	const handleViewChange = useCallback((mode) => {
		setViewMode(mode);
		localStorage.setItem("shop-view", mode);
	}, []);

	const hasActiveFilters =
		filters.search ||
		filters.category ||
		filters.min_price > 0 ||
		filters.max_price > 0 ||
		filters.attributes ||
		filters.stock_status;

	if (!options) {
		return (
			<div className={`sf sf--${layout} sf--loading`}>
				<div className="sf__toolbar-skeleton" />
				<div className="sf__body">
					<div
						className={`sf__filters-skeleton sf__filters-skeleton--${layout}`}
					>
						{layout === "top" && (
							<>
								<div className="sf__filter-skeleton sf__filter-skeleton--search" />
								{Array.from({ length: 5 }).map((_, i) => (
									<div key={i} className="sf__filter-skeleton" />
								))}
							</>
						)}
					</div>
					<div className="sf__products">
						<div
							className={`sf__grid-loading sf__grid-loading--${viewMode}`}
						>
							{Array.from({ length: config.per_page || 12 }).map((_, i) => (
								<div key={i} className="sf__skeleton" />
							))}
						</div>
					</div>
				</div>
			</div>
		);
	}

	return (
		<div className={`sf sf--${layout}`}>
			{/* Toolbar: sort, view toggle, layout switch */}
			<Toolbar
				layout={layout}
				onLayoutChange={handleLayoutChange}
				viewMode={viewMode}
				onViewChange={handleViewChange}
				orderby={filters.orderby}
				order={filters.order}
				onSortChange={(orderby, order) => {
					// One state update, so sorting costs one history entry and one fetch.
					updateMultiple({ orderby, order });
				}}
				total={total}
			/>

			{/* Mobile-only floating filter button — a fixed FAB instead of a
			    sticky toolbar row, so it stays reachable while scrolling
			    without pinning a whole bar (and the seam it created against
			    whatever background sits behind it) to the top of the viewport. */}
			<button
				className="sf__filter-fab"
				onClick={() => setMobileFilterOpen(true)}
				type="button"
				aria-label="Open filters"
			>
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" aria-hidden="true">
					<path d="M4 6h16M7 12h10M10 18h4" />
				</svg>
				Filters
			</button>

			{/* Active filters */}
			{hasActiveFilters && (
				<ActiveFilters
					filters={filters}
					options={options}
					onRemoveCategory={toggleCategory}
					onRemoveAttribute={toggleAttribute}
					onClearAll={resetFilters}
				/>
			)}

			<div className="sf__body">
				{/* Filter panel */}
				<FilterPanel
					options={options}
					filters={filters}
					onSearch={setSearch}
					onToggleCategory={toggleCategory}
					onToggleAttribute={toggleAttribute}
					onPriceChange={(min, max) => {
						updateMultiple({ min_price: min, max_price: max });
					}}
					onStockChange={(val) => updateFilter("stock_status", val)}
					onClearAll={resetFilters}
					total={total}
					layout={layout}
					mobileOpen={mobileFilterOpen}
					onMobileClose={() => setMobileFilterOpen(false)}
				/>

				{/* Product grid */}
				<div className="sf__products">
					<ProductGrid
						products={products}
						loading={loading}
						viewMode={viewMode}
						perPage={filters.per_page}
						wishlistEnabled={config.wishlist_enabled !== false}
					/>

					{pages > 1 && (
						<Pagination
							current={filters.page}
							total={pages}
							onChange={(p) => updateFilter("page", p)}
						/>
					)}
				</div>
			</div>
		</div>
	);
}
