import { __ } from "@wordpress/i18n";
import {
	useBlockProps,
	InspectorControls,
	MediaUpload,
	MediaUploadCheck,
} from "@wordpress/block-editor";
import {
	PanelBody,
	SelectControl,
	ToggleControl,
	RangeControl,
	Button,
	Notice,
} from "@wordpress/components";

/**
 * Editor preview.
 *
 * The drawer itself is never shown here: it is fixed-position and would cover
 * the canvas. Only the toggle is previewed, with a placeholder count, because
 * the real count comes from the visitor's session at render time.
 */
export default function Edit({ attributes, setAttributes }) {
	const {
		iconStyle,
		customIconUrl,
		showCount,
		showSubtotal,
		badgePosition,
		drawerSide,
		openOnAdd,
		showFreeShippingProgress,
		thresholdSource,
		manualThreshold,
		showCrossSell,
		crossSellCount,
	} = attributes;

	const blockProps = useBlockProps({ className: "az-mc az-mc--editor" });

	return (
		<>
			<InspectorControls>
				<PanelBody title={__("Toggle", "ai-zippy")} initialOpen>
					<SelectControl
						label={__("Icon", "ai-zippy")}
						value={iconStyle}
						options={[
							{ label: __("Shopping bag", "ai-zippy"), value: "bag" },
							{ label: __("Shopping cart", "ai-zippy"), value: "cart" },
							{ label: __("Basket", "ai-zippy"), value: "basket" },
							{ label: __("Custom image", "ai-zippy"), value: "custom" },
						]}
						onChange={(value) => setAttributes({ iconStyle: value })}
					/>

					{iconStyle === "custom" && (
						<MediaUploadCheck>
							<MediaUpload
								allowedTypes={["image"]}
								value={attributes.customIconId}
								onSelect={(media) =>
									setAttributes({ customIconUrl: media.url, customIconId: media.id })
								}
								render={({ open }) => (
									<div style={{ marginBottom: "16px" }}>
										{customIconUrl && (
											<img
												src={customIconUrl}
												alt=""
												style={{ display: "block", width: "32px", height: "32px", objectFit: "contain", marginBottom: "8px" }}
											/>
										)}
										<Button variant="secondary" onClick={open}>
											{customIconUrl
												? __("Replace icon", "ai-zippy")
												: __("Select icon", "ai-zippy")}
										</Button>
										{customIconUrl && (
											<Button
												variant="tertiary"
												isDestructive
												onClick={() => setAttributes({ customIconUrl: "", customIconId: 0 })}
											>
												{__("Remove", "ai-zippy")}
											</Button>
										)}
									</div>
								)}
							/>
						</MediaUploadCheck>
					)}

					<ToggleControl
						label={__("Show item count", "ai-zippy")}
						checked={showCount}
						onChange={(value) => setAttributes({ showCount: value })}
					/>

					{showCount && (
						<SelectControl
							label={__("Count position", "ai-zippy")}
							value={badgePosition}
							options={[
								{ label: __("Badge, top right", "ai-zippy"), value: "top-right" },
								{ label: __("Badge, top left", "ai-zippy"), value: "top-left" },
								{ label: __("Next to the icon", "ai-zippy"), value: "inline" },
							]}
							onChange={(value) => setAttributes({ badgePosition: value })}
						/>
					)}

					<ToggleControl
						label={__("Show subtotal", "ai-zippy")}
						checked={showSubtotal}
						onChange={(value) => setAttributes({ showSubtotal: value })}
					/>
				</PanelBody>

				<PanelBody title={__("Drawer", "ai-zippy")} initialOpen={false}>
					<SelectControl
						label={__("Slides in from", "ai-zippy")}
						value={drawerSide}
						options={[
							{ label: __("Right", "ai-zippy"), value: "right" },
							{ label: __("Left", "ai-zippy"), value: "left" },
						]}
						onChange={(value) => setAttributes({ drawerSide: value })}
					/>
					<ToggleControl
						label={__("Open after adding to cart", "ai-zippy")}
						checked={openOnAdd}
						onChange={(value) => setAttributes({ openOnAdd: value })}
						help={__("The drawer never opens by itself on the cart and checkout pages.", "ai-zippy")}
					/>
				</PanelBody>

				<PanelBody title={__("Free shipping progress", "ai-zippy")} initialOpen={false}>
					<ToggleControl
						label={__("Show progress towards free shipping", "ai-zippy")}
						checked={showFreeShippingProgress}
						onChange={(value) => setAttributes({ showFreeShippingProgress: value })}
					/>

					{showFreeShippingProgress && (
						<>
							<SelectControl
								label={__("Threshold", "ai-zippy")}
								value={thresholdSource}
								options={[
									{ label: __("From WooCommerce shipping zones", "ai-zippy"), value: "woo" },
									{ label: __("Enter manually", "ai-zippy"), value: "manual" },
								]}
								onChange={(value) => setAttributes({ thresholdSource: value })}
							/>

							{thresholdSource === "woo" ? (
								<Notice status="info" isDismissible={false}>
									{__(
										"Uses the lowest “free shipping over” amount found in your shipping zones. The bar stays hidden if no zone has one.",
										"ai-zippy"
									)}
								</Notice>
							) : (
								<RangeControl
									label={__("Minimum order amount", "ai-zippy")}
									value={manualThreshold}
									onChange={(value) => setAttributes({ manualThreshold: value || 0 })}
									min={0}
									max={1000}
									step={5}
								/>
							)}
						</>
					)}
				</PanelBody>

				<PanelBody title={__("Cross-sells", "ai-zippy")} initialOpen={false}>
					<ToggleControl
						label={__("Suggest related products", "ai-zippy")}
						checked={showCrossSell}
						onChange={(value) => setAttributes({ showCrossSell: value })}
						help={__("Uses the cross-sells configured on the products in the cart.", "ai-zippy")}
					/>
					{showCrossSell && (
						<RangeControl
							label={__("How many", "ai-zippy")}
							value={crossSellCount}
							onChange={(value) => setAttributes({ crossSellCount: value || 1 })}
							min={1}
							max={6}
						/>
					)}
				</PanelBody>
			</InspectorControls>

			<div {...blockProps}>
				<span className="az-mc__toggle" data-badge={badgePosition}>
					{iconStyle === "custom" && customIconUrl ? (
						<img className="az-mc__icon" src={customIconUrl} alt="" width="24" height="24" />
					) : (
						<span className="az-mc__icon" aria-hidden="true">
							{{ bag: "🛍", cart: "🛒", basket: "🧺" }[iconStyle] || "🛍"}
						</span>
					)}
					{showCount && <span className="az-mc__count">3</span>}
					{showSubtotal && <span className="az-mc__subtotal">$0.00</span>}
				</span>
			</div>
		</>
	);
}
