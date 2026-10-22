export default function ActiveFilters({
	filters,
	options,
	onRemoveCategory,
	onRemoveAttribute,
	onClearAll,
}) {
	const tags = [];

	// Search
	if (filters.search) {
		tags.push({
			key: "search",
			label: `"${filters.search}"`,
			onRemove: () => onRemoveCategory("__search__"),
		});
	}

	// Categories
	if (filters.category) {
		filters.category.split(",").forEach((slug) => {
			const cat = options.categories.find((c) => c.slug === slug);
			tags.push({
				key: `cat-${slug}`,
				label: cat ? cat.name : slug,
				onRemove: () => onRemoveCategory(slug),
			});
		});
	}

	// Price
	if (filters.min_price > 0 || filters.max_price > 0) {
		const label = `$${filters.min_price || 0} – $${filters.max_price || "∞"}`;
		tags.push({ key: "price", label, onRemove: null });
	}

	// Attributes
	if (filters.attributes) {
		filters.attributes.split("|").forEach((group) => {
			const [taxonomy, terms] = group.split(":");
			const attrDef = options.attributes.find((a) => a.slug === taxonomy);

			if (terms) {
				terms.split(",").forEach((termSlug) => {
					const termDef = attrDef?.options.find(
						(o) => o.slug === termSlug,
					);
					tags.push({
						key: `${taxonomy}-${termSlug}`,
						label: `${attrDef?.name || taxonomy}: ${termDef?.name || termSlug}`,
						onRemove: () => onRemoveAttribute(taxonomy, termSlug),
					});
				});
			}
		});
	}

	// Stock
	if (filters.stock_status) {
		tags.push({
			key: "stock",
			label: filters.stock_status.replace("instock", "In Stock").replace("outofstock", "Out of Stock"),
			onRemove: null,
		});
	}

	if (tags.length === 0) return null;

	return (
		<div className="sf__active">
			{tags.map((tag) => (
				<span key={tag.key} className="sf__tag">
					{tag.label}
					{tag.onRemove && (
						<button
							onClick={tag.onRemove}
							className="sf__tag-remove"
							type="button"
						>
							&times;
						</button>
					)}
				</span>
			))}
			<button onClick={onClearAll} className="sf__clear-all" type="button">
				Clear all
			</button>
		</div>
	);
}
