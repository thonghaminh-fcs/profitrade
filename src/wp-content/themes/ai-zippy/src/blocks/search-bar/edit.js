import { __ } from "@wordpress/i18n";
import { useBlockProps, InspectorControls, MediaUpload, MediaUploadCheck } from "@wordpress/block-editor";
import {
	PanelBody,
	SelectControl,
	TextControl,
	RangeControl,
	Button,
} from "@wordpress/components";

export default function Edit({ attributes, setAttributes }) {
	const { displayMode, searchScope, placeholder, maxResults, iconUrl, iconId, iconSize } = attributes;

	const blockProps = useBlockProps({ className: "zs__editor-wrap" });

	const scopeLabel = {
		products: "Products",
		posts: "Blog Posts",
		both: "Products & Posts",
	}[searchScope];

	return (
		<>
			<InspectorControls>
				<PanelBody title={__("Display", "ai-zippy")} initialOpen>
					<SelectControl
						label={__("Display Mode", "ai-zippy")}
						value={displayMode}
						options={[
							{ label: "Inline (input always visible)", value: "inline" },
							{ label: "Icon only (popup on click)", value: "icon" },
						]}
						onChange={(val) => setAttributes({ displayMode: val })}
						help={
							displayMode === "icon"
								? __("Shows a magnifier icon. Clicking opens a full search popup.", "ai-zippy")
								: __("Shows the search input bar inline.", "ai-zippy")
						}
					/>
					<TextControl
						label={__("Placeholder text", "ai-zippy")}
						value={placeholder}
						onChange={(val) => setAttributes({ placeholder: val })}
					/>

					<div style={{ marginTop: "16px" }}>
						<p style={{ marginBottom: "8px", fontWeight: 500 }}>
							{__("Custom search icon", "ai-zippy")}
						</p>
						{iconUrl && (
							<div style={{ marginBottom: "8px", display: "flex", alignItems: "center", gap: "8px" }}>
								<img
									src={iconUrl}
									alt=""
									style={{ width: "32px", height: "32px", objectFit: "contain", border: "1px solid #e0e0e0", borderRadius: "4px", padding: "2px", background: "#fff" }}
								/>
								<Button
									variant="tertiary"
									isDestructive
									onClick={() => setAttributes({ iconUrl: "", iconId: 0 })}
									size="small"
								>
									{__("Remove", "ai-zippy")}
								</Button>
							</div>
						)}
						<MediaUploadCheck>
							<MediaUpload
								onSelect={(media) => setAttributes({ iconUrl: media.url, iconId: media.id })}
								allowedTypes={["image/svg+xml", "image"]}
								value={iconId}
								render={({ open }) => (
									<Button variant="secondary" onClick={open} size="small">
										{iconUrl ? __("Replace icon", "ai-zippy") : __("Upload icon", "ai-zippy")}
									</Button>
								)}
							/>
						</MediaUploadCheck>
						<p style={{ marginTop: "6px", fontSize: "11px", color: "#757575" }}>
							{__("Accepts SVG or any image. Leave empty to use the default magnifier.", "ai-zippy")}
						</p>
					</div>

					<RangeControl
						label={__("Icon size (px)", "ai-zippy")}
						value={iconSize}
						onChange={(val) => setAttributes({ iconSize: val })}
						min={12}
						max={48}
						step={1}
					/>
				</PanelBody>

				<PanelBody title={__("Search Settings", "ai-zippy")} initialOpen={false}>
					<SelectControl
						label={__("Search scope", "ai-zippy")}
						value={searchScope}
						options={[
							{ label: "Products only", value: "products" },
							{ label: "Blog posts only", value: "posts" },
							{ label: "Products + Blog posts", value: "both" },
						]}
						onChange={(val) => setAttributes({ searchScope: val })}
						help={__("Products scope also searches by SKU and product ID.", "ai-zippy")}
					/>
					<RangeControl
						label={__("Max results shown", "ai-zippy")}
						value={maxResults}
						onChange={(val) => setAttributes({ maxResults: val })}
						min={3}
						max={20}
					/>
				</PanelBody>
			</InspectorControls>

			<div {...blockProps}>
				<div className={`zs__preview zs__preview--${displayMode}`}>
					{displayMode === "icon" ? (
						<button className="zs__icon-trigger" aria-label="Search" disabled>
							{iconUrl
								? <img src={iconUrl} alt="" width={iconSize} height={iconSize} style={{ objectFit: "contain" }} />
								: <svg width={iconSize} height={iconSize} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
									<circle cx="11" cy="11" r="8" />
									<line x1="21" y1="21" x2="16.65" y2="16.65" />
								</svg>
							}
						</button>
					) : (
						<div className="zs__input-wrap">
							{iconUrl
								? <img className="zs__input-icon" src={iconUrl} alt="" width={iconSize} height={iconSize} style={{ position: "absolute", left: "0.875rem", objectFit: "contain" }} />
								: <svg className="zs__input-icon" width={iconSize} height={iconSize} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
									<circle cx="11" cy="11" r="8" />
									<line x1="21" y1="21" x2="16.65" y2="16.65" />
								</svg>
							}
							<input
								className="zs__input"
								type="text"
								placeholder={placeholder}
								readOnly
								tabIndex={-1}
							/>
						</div>
					)}
					<span className="zs__preview-badge">{scopeLabel}</span>
				</div>
			</div>
		</>
	);
}
