import { useState, useRef, useEffect } from "react";

export default function FilterPanel({
	options,
	filters,
	onSearch,
	onToggleCategory,
	onToggleAttribute,
	onPriceChange,
	onStockChange,
	onClearAll,
	total,
	layout,
	mobileOpen,
	onMobileClose,
}) {
	const selectedCategories = filters.category
		? filters.category.split(",")
		: [];

	const selectedAttributes = parseAttributes(filters.attributes);

	const panelClass = [
		"sf__filters",
		layout === "top" ? "sf__filters--top" : "sf__filters--sidebar",
		mobileOpen ? "is-open" : "",
	].join(" ");

	return (
		<>
			{/* Mobile overlay */}
			{mobileOpen && (
				<div className="sf__overlay" onClick={onMobileClose} />
			)}

			<aside className={panelClass}>
				{/* Drag handle — mobile bottom-sheet only (hidden on desktop
				    sidebar via CSS), signals the drawer can be swiped down. */}
				<div className="sf__filters-grabber" aria-hidden="true" />

				{/* Mobile close button */}
				<div className="sf__filters-header">
					<span className="sf__filters-title">Filters</span>
					<button
						className="sf__filters-close"
						onClick={onMobileClose}
						type="button"
					>
						&times;
					</button>
				</div>

				{/* Scrolls independently of the footer below, so "Clear all" /
				    "Show N products" stay pinned at the bottom of the sheet
				    instead of scrolling away with the filter list. */}
				<div className="sf__filters-body">
					{/* Search — always visible, no accordion */}
					<div className="sf__section sf__section--search">
						<SearchInput
							currentValue={filters.search}
							onChange={onSearch}
						/>
					</div>

					{/* Categories */}
					{options.categories.length > 0 && (
						<FilterSection title="Categories" defaultOpen>
							<CategoryList
								categories={options.categories}
								selected={selectedCategories}
								onToggle={onToggleCategory}
							/>
						</FilterSection>
					)}

					{/* Price Range */}
					<FilterSection title="Price" defaultOpen>
						<PriceRange
							min={options.price_range.min}
							max={options.price_range.max}
							currentMin={filters.min_price}
							currentMax={filters.max_price}
							onChange={onPriceChange}
						/>
					</FilterSection>

					{/* Product Attributes (color, size, brand, etc.) */}
					{options.attributes.map((attr) => (
						<FilterSection key={attr.slug} title={attr.name}>
							<AttributeFilter
								attribute={attr}
								selected={selectedAttributes[attr.slug] || []}
								onToggle={(termSlug) =>
									onToggleAttribute(attr.slug, termSlug)
								}
							/>
						</FilterSection>
					))}

					{/* Stock Status */}
					<FilterSection title="Availability">
						<StockFilter
							current={filters.stock_status}
							onChange={onStockChange}
						/>
					</FilterSection>
				</div>

				{/* Mobile-only action bar (hidden on desktop sidebar via CSS) */}
				<div className="sf__filters-footer">
					<button
						className="sf__filters-clear"
						type="button"
						onClick={onClearAll}
					>
						Clear all
					</button>
					<button
						className="sf__filters-apply"
						type="button"
						onClick={onMobileClose}
					>
						Show {total} products
					</button>
				</div>
			</aside>
		</>
	);
}

// --- Sub-components ---

function FilterSection({ title, defaultOpen = false, children }) {
	const [open, setOpen] = useState(defaultOpen);

	return (
		<div className={`sf__section ${open ? "is-open" : ""}`}>
			<button
				className="sf__section-toggle"
				onClick={() => setOpen(!open)}
				type="button"
				aria-expanded={open}
			>
				<span>{title}</span>
				<svg
					width="12"
					height="12"
					viewBox="0 0 12 12"
					fill="none"
					stroke="currentColor"
					strokeWidth="2"
					style={{
						transform: open ? "rotate(180deg)" : "rotate(0deg)",
						transition: "transform 0.2s",
					}}
				>
					<polyline points="2 4 6 8 10 4" />
				</svg>
			</button>
			{/* Always mounted so expand/collapse animates via CSS (see
			    .sf__section-content) instead of snapping on mount/unmount. The
			    inner wrapper is the single grid item the 0fr->1fr rows size
			    against — same technique as .sf__cat-children. */}
			<div className="sf__section-content">
				<div className="sf__section-content-clip">
					<div className="sf__section-content-inner">{children}</div>
				</div>
			</div>
		</div>
	);
}

function SearchInput({ currentValue, onChange }) {
	const [value, setValue] = useState(currentValue);

	// Follow the committed filter, so a back/forward restore (or Clear all)
	// updates the field instead of leaving stale text in it. Typing is debounced
	// upstream and commits the same string, so this never fights the user.
	useEffect(() => {
		setValue(currentValue);
	}, [currentValue]);

	return (
		<div className="sf__search">
			<svg
				className="sf__search-icon"
				width="16"
				height="16"
				viewBox="0 0 24 24"
				fill="none"
				stroke="currentColor"
				strokeWidth="2"
			>
				<circle cx="11" cy="11" r="8" />
				<line x1="21" y1="21" x2="16.65" y2="16.65" />
			</svg>
			<input
				type="text"
				className="sf__search-input"
				placeholder="Search name or SKU..."
				value={value}
				onChange={(e) => {
					setValue(e.target.value);
					onChange(e.target.value);
				}}
			/>
			{value && (
				<button
					className="sf__search-clear"
					onClick={() => {
						setValue("");
						onChange("");
					}}
					type="button"
				>
					&times;
				</button>
			)}
		</div>
	);
}

function CategoryList({ categories, selected, onToggle }) {
	// Build tree structure
	const roots = categories.filter((c) => c.parent === 0);
	const children = (parentId) =>
		categories.filter((c) => c.parent === parentId);

	// Previewing a parent's sub-categories is independent of filtering by it —
	// selecting a parent still opens its children (you're already narrowed to
	// it, seeing what's inside makes sense by default), but the chevron must
	// be able to open the same list WITHOUT also checking the parent. Both are
	// tracked here rather than deriving "expanded" from `selected`, since the
	// chevron needs to set one without the other.
	const [expanded, setExpanded] = useState([]);

	const renderItem = (cat) => {
		const isChecked = selected.includes(cat.slug);
		const isExpanded = isChecked || expanded.includes(cat.id);
		const kids = children(cat.id);

		return (
			<li key={cat.slug}>
				<div className="sf__checkbox">
					<label className="sf__checkbox-main">
						<input
							type="checkbox"
							checked={isChecked}
							onChange={() => onToggle(cat.slug)}
						/>
						<span className="sf__checkbox-label">{cat.name}</span>
					</label>
					{/* Separate button, not part of the <label> — a <label> wraps
					    everything in it into the checkbox's click target, so a
					    chevron placed inside used to also check the parent (and
					    apply its filter) on every click. This previews the
					    children without touching `selected`. */}
					{kids.length > 0 && (
						<button
							type="button"
							className={`sf__cat-toggle ${isExpanded ? "is-open" : ""}`}
							aria-expanded={isExpanded}
							aria-label={`${isExpanded ? "Hide" : "Show"} sub-categories of ${cat.name}`}
							onClick={() =>
								setExpanded((prev) =>
									prev.includes(cat.id)
										? prev.filter((id) => id !== cat.id)
										: [...prev, cat.id],
								)
							}
						>
							<svg
								width="12"
								height="12"
								viewBox="0 0 12 12"
								fill="none"
								stroke="currentColor"
								strokeWidth="2"
								aria-hidden="true"
							>
								<polyline points="3 4.5 6 7.5 9 4.5" />
							</svg>
						</button>
					)}
					<span className="sf__checkbox-count">{cat.count}</span>
				</div>
				{/* Always mounted (not conditionally rendered) so toggling
				    .is-open animates via CSS — mounting/unmounting the <ul>
				    gives the transition nothing to transition, it just snaps.
				    The grid-rows collapse needs a SINGLE grid item to size, so
				    the rows go in an inner <ul> rather than directly here — with
				    multiple <li> straight in the grid, only the first collapses
				    and the rest keep their height (reserving empty space). */}
				{kids.length > 0 && (
					<div className={`sf__cat-children ${isExpanded ? "is-open" : ""}`}>
						<ul className="sf__cat-sublist">
							{kids.map(renderItem)}
						</ul>
					</div>
				)}
			</li>
		);
	};

	return <ul className="sf__cat-list">{roots.map(renderItem)}</ul>;
}

function PriceRange({ min, max, currentMin, currentMax, onChange }) {
	const [localMin, setLocalMin] = useState(currentMin || min);
	const [localMax, setLocalMax] = useState(currentMax || max);
	const timeoutRef = useRef(null);

	useEffect(() => {
		setLocalMin(currentMin || min);
		setLocalMax(currentMax || max);
	}, [currentMin, currentMax, min, max]);

	const handleChange = (newMin, newMax) => {
		setLocalMin(newMin);
		setLocalMax(newMax);
		clearTimeout(timeoutRef.current);
		timeoutRef.current = setTimeout(() => onChange(newMin, newMax), 400);
	};

	return (
		<div className="sf__price">
			<div className="sf__price-inputs">
				<input
					type="number"
					className="sf__price-input"
					value={localMin}
					min={min}
					max={max}
					onChange={(e) =>
						handleChange(Number(e.target.value), localMax)
					}
					placeholder="Min"
				/>
				<span className="sf__price-sep">&ndash;</span>
				<input
					type="number"
					className="sf__price-input"
					value={localMax}
					min={min}
					max={max}
					onChange={(e) =>
						handleChange(localMin, Number(e.target.value))
					}
					placeholder="Max"
				/>
			</div>
			<input
				type="range"
				className="sf__price-slider"
				min={min}
				max={max}
				value={localMax || max}
				onChange={(e) => handleChange(localMin, Number(e.target.value))}
			/>
		</div>
	);
}

function AttributeFilter({ attribute, selected, onToggle }) {
	return (
		<div className="sf__attr">
			{attribute.options.map((opt) => (
				<label key={opt.slug} className="sf__checkbox">
					<input
						type="checkbox"
						checked={selected.includes(opt.slug)}
						onChange={() => onToggle(opt.slug)}
					/>
					<span className="sf__checkbox-label">{opt.name}</span>
					<span className="sf__checkbox-count">{opt.count}</span>
				</label>
			))}
		</div>
	);
}

function StockFilter({ current, onChange }) {
	const options = [
		{ value: "", label: "All" },
		{ value: "instock", label: "In Stock" },
		{ value: "outofstock", label: "Out of Stock" },
		{ value: "onbackorder", label: "On Backorder" },
	];

	return (
		<div className="sf__stock">
			{options.map((opt) => (
				<label key={opt.value} className="sf__radio">
					<input
						type="radio"
						name="stock_status"
						checked={current === opt.value}
						onChange={() => onChange(opt.value)}
					/>
					<span>{opt.label}</span>
				</label>
			))}
		</div>
	);
}

function parseAttributes(str) {
	const result = {};
	if (!str) return result;
	str.split("|").forEach((group) => {
		const [taxonomy, terms] = group.split(":");
		if (taxonomy && terms) result[taxonomy] = terms.split(",");
	});
	return result;
}
