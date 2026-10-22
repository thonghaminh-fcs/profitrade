import { useBlockProps, MediaUpload, RichText, InspectorControls } from "@wordpress/block-editor";
import { PanelBody, Button, TextControl, ColorPicker } from "@wordpress/components";

export default function Edit({ attributes, setAttributes }) {
	const {
		heroImageId,
		heroImageUrl,
		tagline,
		subtitle,
		brandName,
		story,
		linkText,
		linkUrl,
		bgColor,
	} = attributes;

	const blockProps = useBlockProps({ className: "bi" });

	return (
		<>
			<InspectorControls>
				<PanelBody title="Hero Image" initialOpen={true}>
					<MediaUpload
						onSelect={(media) =>
							setAttributes({ heroImageId: media.id, heroImageUrl: media.url })
						}
						allowedTypes={["image"]}
						value={heroImageId}
						render={({ open }) => (
							<div>
								{heroImageUrl && (
									<img
										src={heroImageUrl}
										alt="Hero"
										style={{ width: "100%", borderRadius: 8, marginBottom: 8 }}
									/>
								)}
								<Button variant="secondary" onClick={open} style={{ width: "100%" }}>
									{heroImageUrl ? "Replace Image" : "Select Image"}
								</Button>
								{heroImageUrl && (
									<Button
										variant="link"
										isDestructive
										onClick={() => setAttributes({ heroImageId: 0, heroImageUrl: "" })}
										style={{ marginTop: 4 }}
									>
										Remove
									</Button>
								)}
							</div>
						)}
					/>
				</PanelBody>
				<PanelBody title="Link" initialOpen={false}>
					<TextControl
						label="Link Text"
						value={linkText}
						onChange={(val) => setAttributes({ linkText: val })}
					/>
					<TextControl
						label="Link URL"
						value={linkUrl}
						onChange={(val) => setAttributes({ linkUrl: val })}
					/>
				</PanelBody>
				<PanelBody title="Background Color" initialOpen={false}>
					<ColorPicker
						color={bgColor}
						onChangeComplete={(val) => setAttributes({ bgColor: val.hex })}
					/>
				</PanelBody>
			</InspectorControls>

			<div {...blockProps}>
				{/* Hero Image */}
				<div className="bi__hero">
					{heroImageUrl ? (
						<img className="bi__hero-img" src={heroImageUrl} alt="" />
					) : (
						<MediaUpload
							onSelect={(media) =>
								setAttributes({ heroImageId: media.id, heroImageUrl: media.url })
							}
							allowedTypes={["image"]}
							render={({ open }) => (
								<div className="bi__hero-placeholder" onClick={open} role="button" tabIndex={0}>
									<span>+ Select hero image</span>
								</div>
							)}
						/>
					)}

					{/* Arch overlay */}
					<div className="bi__arch">
						<RichText
							tagName="h2"
							className="bi__tagline"
							value={tagline}
							onChange={(val) => setAttributes({ tagline: val })}
							placeholder="Tagline..."
						/>
					</div>
				</div>

				{/* Subtitle */}
				<div className="bi__subtitle-wrap">
					<RichText
						tagName="p"
						className="bi__subtitle"
						value={subtitle}
						onChange={(val) => setAttributes({ subtitle: val })}
						placeholder="Short description..."
					/>
				</div>

				{/* Brand Story */}
				<div className="bi__story" style={{ backgroundColor: bgColor }}>
					<RichText
						tagName="h2"
						className="bi__brand-name"
						value={brandName}
						onChange={(val) => setAttributes({ brandName: val })}
						placeholder="Brand Name"
					/>
					<RichText
						tagName="p"
						className="bi__story-text"
						value={story}
						onChange={(val) => setAttributes({ story: val })}
						placeholder="Brand story..."
					/>
					{linkText && (
						<span className="bi__link">{linkText} &rarr;</span>
					)}
				</div>
			</div>
		</>
	);
}
