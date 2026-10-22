const SORT_OPTIONS = [
	{ label: "Default", orderby: "menu_order", order: "ASC" },
	{ label: "Newest", orderby: "date", order: "DESC" },
	{ label: "Price: Low to High", orderby: "price", order: "ASC" },
	{ label: "Price: High to Low", orderby: "price", order: "DESC" },
	{ label: "Best Rating", orderby: "rating", order: "DESC" },
	{ label: "Popularity", orderby: "popularity", order: "DESC" },
];

export default function Toolbar({
	layout,
	onLayoutChange,
	viewMode,
	onViewChange,
	orderby,
	order,
	onSortChange,
	total,
}) {
	const currentSort = `${orderby}-${order}`;

	return (
		<div className="sf__toolbar">
			<div className="sf__toolbar-left">
				<span className="sf__count">{total} products</span>
			</div>

			<div className="sf__toolbar-right">
				{/* Sort */}
				<select
					className="sf__sort"
					value={currentSort}
					onChange={(e) => {
						const [ob, od] = e.target.value.split("-");
						onSortChange(ob, od);
					}}
				>
					{SORT_OPTIONS.map((opt) => (
						<option
							key={`${opt.orderby}-${opt.order}`}
							value={`${opt.orderby}-${opt.order}`}
						>
							{opt.label}
						</option>
					))}
				</select>

				{/* Layout mode toggle */}
				<div className="sf__layout-toggle">
					<button
						className={`sf__layout-btn ${layout === "sidebar" ? "is-active" : ""}`}
						onClick={() => onLayoutChange("sidebar")}
						title="Sidebar filters"
						type="button"
					>
						<svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
							<rect x="0" y="0" width="5" height="16" rx="1" opacity="0.4" />
							<rect x="7" y="0" width="9" height="16" rx="1" />
						</svg>
					</button>
					<button
						className={`sf__layout-btn ${layout === "top" ? "is-active" : ""}`}
						onClick={() => onLayoutChange("top")}
						title="Top filters"
						type="button"
					>
						<svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
							<rect x="0" y="0" width="16" height="5" rx="1" opacity="0.4" />
							<rect x="0" y="7" width="16" height="9" rx="1" />
						</svg>
					</button>
				</div>

				{/* Grid / List toggle */}
				<div className="sf__view-toggle">
					<button
						className={`sf__view-btn ${viewMode === "grid" ? "is-active" : ""}`}
						onClick={() => onViewChange("grid")}
						type="button"
					>
						<svg width="14" height="14" viewBox="0 0 16 16" fill="currentColor">
							<rect x="0" y="0" width="7" height="7" rx="1" />
							<rect x="9" y="0" width="7" height="7" rx="1" />
							<rect x="0" y="9" width="7" height="7" rx="1" />
							<rect x="9" y="9" width="7" height="7" rx="1" />
						</svg>
					</button>
					<button
						className={`sf__view-btn ${viewMode === "list" ? "is-active" : ""}`}
						onClick={() => onViewChange("list")}
						type="button"
					>
						<svg width="14" height="14" viewBox="0 0 16 16" fill="currentColor">
							<rect x="0" y="0" width="16" height="4" rx="1" />
							<rect x="0" y="6" width="16" height="4" rx="1" />
							<rect x="0" y="12" width="16" height="4" rx="1" />
						</svg>
					</button>
				</div>
			</div>
		</div>
	);
}
