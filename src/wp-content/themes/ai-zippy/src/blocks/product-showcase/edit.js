import { __ } from "@wordpress/i18n";
import { useBlockProps, InspectorControls, RichText } from "@wordpress/block-editor";
import {
	PanelBody,
	RangeControl,
	SelectControl,
	ToggleControl,
	Spinner,
	TextControl,
} from "@wordpress/components";
import { useEffect, useState } from "@wordpress/element";
import apiFetch from "@wordpress/api-fetch";

export default function Edit({ attributes, setAttributes }) {
	const {
		heading,
		columns,
		rows,
		category,
		brand,
		displayStyle,
		orderby,
		showSaleBadge,
		showRating,
		showAddToCart,
		autoplay,
		autoplayDelay,
	} = attributes;

	const [categories, setCategories] = useState([]);
	const [brands, setBrands] = useState([]);
	const [products, setProducts] = useState([]);
	const [loading, setLoading] = useState(true);

	const totalItems = columns * rows;

	// Load categories
	useEffect(() => {
		apiFetch({ path: "/wc/store/v1/products/categories?per_page=100" })
			.then((data) => {
				setCategories(data.map((c) => ({ label: c.name, value: c.slug })));
			})
			.catch(() => {});
	}, []);

	// Load brands (pa_brand taxonomy)
	useEffect(() => {
		apiFetch({ path: "/wc/store/v1/products/attributes" })
			.then((attrs) => {
				const brandAttr = attrs.find(
					(a) => a.name.toLowerCase() === "brand" || a.slug === "pa_brand",
				);
				if (brandAttr) {
					apiFetch({
						path: `/wc/store/v1/products/attributes/${brandAttr.id}/terms?per_page=100`,
					})
						.then((terms) => {
							setBrands(terms.map((t) => ({ label: t.name, value: t.slug })));
						})
						.catch(() => {});
				}
			})
			.catch(() => {});
	}, []);

	// Load products for preview
	useEffect(() => {
		setLoading(true);
		const params = new URLSearchParams({
			per_page: totalItems,
			orderby: orderby === "popularity" ? "date" : orderby,
		});
		if (category) params.set("category", category);

		apiFetch({ path: `/ai-zippy/v1/products?${params.toString()}` })
			.then((data) => setProducts(data.products || []))
			.catch(() => setProducts([]))
			.finally(() => setLoading(false));
	}, [category, brand, orderby, totalItems]);

	const blockProps = useBlockProps();

	return (
		<>
			<InspectorControls>
				<PanelBody title={__("Layout", "ai-zippy")} initialOpen>
					<SelectControl
						label={__("Display Style", "ai-zippy")}
						value={displayStyle}
						options={[
							{ label: "Grid", value: "grid" },
							{ label: "Slider / Carousel", value: "slider" },
						]}
						onChange={(val) => setAttributes({ displayStyle: val })}
					/>
					<RangeControl
						label={__("Columns per row", "ai-zippy")}
						value={columns}
						onChange={(val) => setAttributes({ columns: val })}
						min={2}
						max={6}
					/>
					<RangeControl
						label={__("Number of rows", "ai-zippy")}
						value={rows}
						onChange={(val) => setAttributes({ rows: val })}
						min={1}
						max={5}
					/>
				</PanelBody>

				<PanelBody title={__("Filter Products", "ai-zippy")} initialOpen>
					<SelectControl
						label={__("Category", "ai-zippy")}
						value={category}
						options={[
							{ label: "All Categories", value: "" },
							...categories,
						]}
						onChange={(val) => setAttributes({ category: val })}
					/>
					{brands.length > 0 && (
						<SelectControl
							label={__("Brand", "ai-zippy")}
							value={brand}
							options={[
								{ label: "All Brands", value: "" },
								...brands,
							]}
							onChange={(val) => setAttributes({ brand: val })}
						/>
					)}
					<SelectControl
						label={__("Order by", "ai-zippy")}
						value={orderby}
						options={[
							{ label: "Newest", value: "date" },
							{ label: "Price: Low to High", value: "price" },
							{ label: "Popularity", value: "popularity" },
							{ label: "Best Rating", value: "rating" },
							{ label: "Menu Order", value: "menu_order" },
						]}
						onChange={(val) => setAttributes({ orderby: val })}
					/>
				</PanelBody>

				<PanelBody title={__("Card Options", "ai-zippy")} initialOpen={false}>
					<ToggleControl
						label={__("Show sale badge", "ai-zippy")}
						checked={showSaleBadge}
						onChange={(val) => setAttributes({ showSaleBadge: val })}
					/>
					<ToggleControl
						label={__("Show rating", "ai-zippy")}
						checked={showRating}
						onChange={(val) => setAttributes({ showRating: val })}
					/>
					<ToggleControl
						label={__("Show Add to Cart", "ai-zippy")}
						checked={showAddToCart}
						onChange={(val) => setAttributes({ showAddToCart: val })}
					/>
				</PanelBody>

				{displayStyle === "slider" && (
					<PanelBody
						title={__("Slider Settings", "ai-zippy")}
						initialOpen={false}
					>
						<ToggleControl
							label={__("Autoplay", "ai-zippy")}
							checked={autoplay}
							onChange={(val) => setAttributes({ autoplay: val })}
						/>
						{autoplay && (
							<RangeControl
								label={__("Autoplay delay (ms)", "ai-zippy")}
								value={autoplayDelay}
								onChange={(val) =>
									setAttributes({ autoplayDelay: val })
								}
								min={2000}
								max={10000}
								step={500}
							/>
						)}
					</PanelBody>
				)}
			</InspectorControls>

			<div {...blockProps}>
				<div className="ps-editor">
					{/* Section heading */}
					<RichText
						tagName="h2"
						className="ps-editor__heading"
						value={heading}
						onChange={(val) => setAttributes({ heading: val })}
						placeholder={__("Section Title (optional)", "ai-zippy")}
					/>

					{/* Layout badge */}
					<div className="ps-editor__meta">
						<span className="ps-editor__badge">
							{displayStyle === "slider" ? "Slider" : "Grid"} — {columns}×{rows} ({totalItems} items)
						</span>
						{category && (
							<span className="ps-editor__badge ps-editor__badge--cat">
								{category}
							</span>
						)}
					</div>

					{/* Product preview */}
					{loading ? (
						<div className="ps-editor__loading">
							<Spinner />
						</div>
					) : products.length === 0 ? (
						<div className="ps-editor__empty">
							{__("No products found for this selection.", "ai-zippy")}
						</div>
					) : (
						<div
							className="ps-editor__grid"
							style={{
								gridTemplateColumns: `repeat(${columns}, 1fr)`,
							}}
						>
							{products.slice(0, totalItems).map((p) => (
								<div key={p.id} className="ps-editor__card">
									<div className="ps-editor__card-img">
										<img src={p.image} alt={p.name} />
										{p.on_sale && showSaleBadge && (
											<span className="ps-editor__sale">Sale</span>
										)}
									</div>
									<div className="ps-editor__card-info">
										<span className="ps-editor__card-name">{p.name}</span>
										<span
											className="ps-editor__card-price"
											dangerouslySetInnerHTML={{
												__html: p.price_html,
											}}
										/>
									</div>
								</div>
							))}
						</div>
					)}
				</div>
			</div>
		</>
	);
}
