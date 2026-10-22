import { useRef, useEffect, useState } from "react";
import useWishlist from "../useWishlist.js";

export default function ProductGrid({
	products,
	loading,
	viewMode,
	perPage,
	wishlistEnabled = true,
	onWishlistChange,
	onWishlistRequest,
	wishlistMode = "toggle",
	wishlistRemoveLabel = "Remove %s from wishlist",
}) {
	const containerRef = useRef(null);
	const lastHeightRef = useRef(0);
	const wishlist = useWishlist();

	useEffect(() => {
		if (!containerRef.current) return;

		if (loading) {
			const h = containerRef.current.offsetHeight;
			if (h > 0) {
				lastHeightRef.current = h;
				containerRef.current.style.minHeight = `${h}px`;
			}
		} else {
			const timer = setTimeout(() => {
				if (containerRef.current) {
					containerRef.current.style.minHeight = "";
				}
			}, 100);
			return () => clearTimeout(timer);
		}
	}, [loading]);

	const skeletonCount = perPage || 12;

	return (
		<div ref={containerRef} className="sf__grid-wrap">
			{loading && (
				<div className={`sf__grid-loading sf__grid-loading--${viewMode}`}>
					{Array.from({ length: skeletonCount }).map((_, i) => (
						<div key={i} className="sf__skeleton" />
					))}
				</div>
			)}

			{!loading && products.length === 0 && (
				<div className="sf__empty">
					<p>No products found matching your filters.</p>
				</div>
			)}

			{!loading && products.length > 0 && (
				<div className={`sf__grid sf__grid--${viewMode}`}>
					{products.map((product, index) => (
						<ProductCard
							key={product.id}
							product={product}
							viewMode={viewMode}
							index={index}
							wishlistEnabled={wishlistEnabled}
							wishlisted={wishlist.isWishlisted(product.id)}
							wishlistMode={wishlistMode}
							wishlistRemoveLabel={wishlistRemoveLabel}
							onToggleWishlist={() => {
								if (onWishlistRequest) {
									onWishlistRequest(product);
									return;
								}

								const isWishlisted = wishlist.toggle(product.id);
								onWishlistChange?.(product.id, isWishlisted);
							}}
						/>
					))}
				</div>
			)}
		</div>
	);
}

function ProductCard({
	product,
	viewMode,
	index = 0,
	wishlistEnabled = true,
	wishlisted = false,
	wishlistMode = "toggle",
	wishlistRemoveLabel = "Remove %s from wishlist",
	onToggleWishlist,
}) {
	// First 12 cards eager-load — they're above the fold (or close to it) and
	// need to be fully painted before a view transition click can capture them.
	// Cards 13+ stay lazy so we don't blow up first-paint bandwidth.
	const imgLoading = index < 12 ? "eager" : "lazy";
	const [activeThumb, setActiveThumb] = useState(0);

	// Main image + gallery thumbnails
	const allImages = [product.image, ...(product.gallery || [])].filter(Boolean);
	const currentImage = allImages[activeThumb] || product.image;
	const extraCount = allImages.length > 3 ? allImages.length - 3 : 0;

	// Only meaningful when WooCommerce is tracking stock for this product —
	// stock_quantity is null/0 for products with "Track stock" off, which
	// would otherwise read as "always low stock".
	const lowStockThreshold = 10;
	const hasLowStock =
		product.stock_status === "instock" &&
		typeof product.stock_quantity === "number" &&
		product.stock_quantity > 0 &&
		product.stock_quantity < lowStockThreshold;

	// Sale percentage
	const salePercent =
		product.on_sale && product.regular_price && product.sale_price
			? Math.round(
					((product.regular_price - product.sale_price) /
						product.regular_price) *
						100,
				)
			: 0;

	return (
		<div className="sf__card">
			{/* Image area */}
			<div className="sf__card-image">
				<a href={product.permalink}>
					<img src={currentImage} alt={product.name} loading={imgLoading} fetchPriority={index < 4 ? "high" : "auto"} />
				</a>

				{/* Sale badge */}
				{product.on_sale && salePercent > 0 && (
					<span className="sf__badge sf__badge--sale">
						{salePercent}% OFF
					</span>
				)}
				{product.on_sale && salePercent === 0 && (
					<span className="sf__badge sf__badge--sale">Sale</span>
				)}

				{/* Stock state. Backordered products are still purchasable, so they
				    get a badge of their own rather than being lumped in with sold out. */}
				{product.stock_status === "outofstock" && (
					<span className="sf__badge sf__badge--oos">Sold Out</span>
				)}
				{product.stock_status === "onbackorder" && (
					<span className="sf__badge sf__badge--backorder">On Backorder</span>
				)}

				{/* Wishlist — the only toggle for this card; the actions row below
				    used to carry a second one (sf__card-wish-sm), which just
				    duplicated this button rather than covering a different
				    viewport. */}
				{wishlistEnabled && (
					<button
						className={`sf__card-wish ${wishlistMode === "remove" ? "sf__card-wish--remove" : ""} ${wishlisted ? "is-active" : ""}`}
						onClick={onToggleWishlist}
						type="button"
						aria-label={
							wishlistMode === "remove"
								? wishlistRemoveLabel.replace("%s", product.name)
								: wishlisted
									? "Remove from wishlist"
									: "Add to wishlist"
						}
						aria-pressed={wishlistMode === "toggle" ? wishlisted : undefined}
					>
						{wishlistMode === "remove" ? (
							<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.25" aria-hidden="true">
								<line x1="18" y1="6" x2="6" y2="18" />
								<line x1="6" y1="6" x2="18" y2="18" />
							</svg>
						) : (
							<svg
								width="18"
								height="18"
								viewBox="0 0 24 24"
								fill={wishlisted ? "currentColor" : "none"}
								stroke="currentColor"
								strokeWidth="2"
								aria-hidden="true"
							>
								<path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
							</svg>
						)}
					</button>
				)}

				{/* Gallery thumbnails */}
				{allImages.length > 1 && (
					<div className="sf__card-thumbs">
						{allImages.slice(0, 3).map((img, idx) => (
							<button
								key={idx}
								className={`sf__card-thumb ${idx === activeThumb ? "is-active" : ""}`}
								onMouseEnter={() => setActiveThumb(idx)}
								onClick={() => setActiveThumb(idx)}
								type="button"
							>
								<img src={img} alt="" />
							</button>
						))}
						{extraCount > 0 && (
							<a href={product.permalink} className="sf__card-thumb sf__card-thumb--more">
								+{extraCount}
							</a>
						)}
					</div>
				)}
			</div>

			{/* Product info */}
			<div className="sf__card-info">
				{product.categories.length > 0 && (
					<span className="sf__card-cat">
						{product.categories[0].name}
					</span>
				)}

				<a href={product.permalink} className="sf__card-title">
					{product.name}
				</a>

				{product.average_rating > 0 && (
					<div className="sf__card-rating">
						<Stars rating={product.average_rating} />
						<span className="sf__card-rating-count">
							({product.rating_count})
						</span>
					</div>
				)}

				{product.sku && viewMode === "list" && (
					<span className="sf__card-sku">SKU: {product.sku}</span>
				)}

				{viewMode === "list" && product.short_description && (
					<p className="sf__card-desc">{product.short_description}</p>
				)}

				{/* Price + low-stock notice share a row (price left, notice right),
				    matching how WC's own .stock indicator sits beside the price. */}
				<div className="sf__card-pricing">
					<span
						className="sf__card-price"
						dangerouslySetInnerHTML={{ __html: product.price_html }}
					/>
					{hasLowStock && (
						<p className="sf__card-low-stock">Only {product.stock_quantity} left</p>
					)}
				</div>

				{/* Add to cart. Out of stock keeps this button in place (disabled,
				    relabeled) instead of removing it — dropping the button reflows
				    the card and reads as broken rather than unavailable.
				    Variable / grouped / external products can't be AJAX-added without
				    a chosen variation, so we route them to the product page instead. */}
				<div className="sf__card-actions">
					{product.stock_status === "outofstock" ? (
						<button className="sf__card-btn" type="button" disabled>
							{/* Icon so the icon-only 2-up grid (label hidden below
							    550px, same rule as the Add to Cart button) still
							    shows something instead of an empty box. */}
							<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
								<circle cx="12" cy="12" r="10" />
								<line x1="4.93" y1="4.93" x2="19.07" y2="19.07" />
							</svg>
							<span className="sf__card-btn-label">Sold Out</span>
						</button>
					) : (() => {
						const needsOptions = product.type && product.type !== "simple";
						return (
							<a
								href={needsOptions ? product.permalink : product.add_to_cart_url}
								className={`sf__card-btn${needsOptions ? "" : " az-add-to-cart"}`}
								data-product-id={product.id}
							>
								<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
									<circle cx="9" cy="21" r="1" />
									<circle cx="20" cy="21" r="1" />
									<path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6" />
								</svg>
								{/* Hidden below 550px (2-up grid) — the card isn't wide
								    enough for icon + this label on one line without
								    wrapping to a second, oversized row. */}
								<span className="sf__card-btn-label">
									{needsOptions ? "SELECT OPTIONS" : "ADD TO CART"}
								</span>
							</a>
						);
					})()}
				</div>
			</div>
		</div>
	);
}

function Stars({ rating }) {
	const full = Math.floor(rating);
	const half = rating % 1 >= 0.5 ? 1 : 0;
	const empty = 5 - full - half;

	return (
		<span className="sf__stars">
			{"★".repeat(full)}
			{half ? "½" : ""}
			{"☆".repeat(empty)}
		</span>
	);
}
