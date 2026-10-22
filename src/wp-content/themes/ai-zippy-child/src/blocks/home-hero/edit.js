import { __ } from "@wordpress/i18n";
import {
	useBlockProps,
	InspectorControls,
	MediaUpload,
	MediaUploadCheck,
} from "@wordpress/block-editor";
import {
	PanelBody,
	TextControl,
	TextareaControl,
	Button,
} from "@wordpress/components";
import ServerSideRender from "@wordpress/server-side-render";

export default function Edit({ attributes, setAttributes }) {
	const {
		eyebrow,
		heading,
		highlightText,
		paragraph,
		primaryLabel,
		primaryUrl,
		secondaryLabel,
		secondaryUrl,
		imageId,
		imageUrl,
		nextSlotLabel,
	} = attributes;
	const blockProps = useBlockProps();

	return (
		<>
			<InspectorControls>
				<PanelBody title={__("Content", "ai-zippy-child")} initialOpen>
					<TextControl
						label={__("Eyebrow pill", "ai-zippy-child")}
						value={eyebrow}
						onChange={(value) => setAttributes({ eyebrow: value })}
					/>
					<TextControl
						label={__("Heading", "ai-zippy-child")}
						value={heading}
						onChange={(value) => setAttributes({ heading: value })}
					/>
					<TextControl
						label={__("Highlighted words (red)", "ai-zippy-child")}
						value={highlightText}
						onChange={(value) => setAttributes({ highlightText: value })}
					/>
					<TextareaControl
						label={__("Paragraph", "ai-zippy-child")}
						value={paragraph}
						onChange={(value) => setAttributes({ paragraph: value })}
					/>
				</PanelBody>

				<PanelBody title={__("Buttons", "ai-zippy-child")}>
					<TextControl
						label={__("Primary label", "ai-zippy-child")}
						value={primaryLabel}
						onChange={(value) => setAttributes({ primaryLabel: value })}
					/>
					<TextControl
						label={__("Primary URL", "ai-zippy-child")}
						value={primaryUrl}
						onChange={(value) => setAttributes({ primaryUrl: value })}
					/>
					<TextControl
						label={__("Secondary label", "ai-zippy-child")}
						value={secondaryLabel}
						onChange={(value) => setAttributes({ secondaryLabel: value })}
					/>
					<TextControl
						label={__("Secondary URL", "ai-zippy-child")}
						value={secondaryUrl}
						onChange={(value) => setAttributes({ secondaryUrl: value })}
					/>
				</PanelBody>

				<PanelBody title={__("Image", "ai-zippy-child")}>
					<MediaUploadCheck>
						<MediaUpload
							allowedTypes={["image"]}
							value={imageId || undefined}
							onSelect={(media) =>
								setAttributes({
									imageId: media.id,
									imageUrl: media.url,
								})
							}
							render={({ open }) => (
								<Button variant="secondary" onClick={open}>
									{imageId
										? __("Replace image", "ai-zippy-child")
										: __("Choose image", "ai-zippy-child")}
								</Button>
							)}
						/>
					</MediaUploadCheck>
					{!!imageId && (
						<p>
							<img
								src={imageUrl}
								alt=""
								style={{ maxWidth: "100%", height: "auto" }}
							/>
						</p>
					)}
					{!!imageId && (
						<Button
							variant="link"
							isDestructive
							onClick={() => setAttributes({ imageId: 0, imageUrl: "" })}
						>
							{__("Use default image", "ai-zippy-child")}
						</Button>
					)}
					<p className="components-base-control__help">
						{__(
							"Defaults to hero-shelves.jpg from the Media Library.",
							"ai-zippy-child"
						)}
					</p>
				</PanelBody>

				<PanelBody title={__("Next slot card", "ai-zippy-child")}>
					<TextControl
						label={__("Card eyebrow", "ai-zippy-child")}
						help={__(
							"The slot time below it follows the delivery state on the frontend.",
							"ai-zippy-child"
						)}
						value={nextSlotLabel}
						onChange={(value) => setAttributes({ nextSlotLabel: value })}
					/>
				</PanelBody>
			</InspectorControls>

			<div {...blockProps}>
				<ServerSideRender block="ai-zippy/home-hero" attributes={attributes} />
			</div>
		</>
	);
}
