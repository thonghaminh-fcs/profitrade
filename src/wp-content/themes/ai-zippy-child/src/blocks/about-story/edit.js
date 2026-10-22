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
	const { eyebrow, heading, paragraphs, imageId } = attributes;
	const blockProps = useBlockProps();

	const updateParagraph = (index, value) =>
		setAttributes({
			paragraphs: paragraphs.map((text, i) => (i === index ? value : text)),
		});

	const moveParagraph = (index, delta) => {
		const to = index + delta;
		if (to < 0 || to >= paragraphs.length) return;
		const next = [...paragraphs];
		[next[index], next[to]] = [next[to], next[index]];
		setAttributes({ paragraphs: next });
	};

	const removeParagraph = (index) =>
		setAttributes({ paragraphs: paragraphs.filter((_, i) => i !== index) });

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
					<Button
						variant="secondary"
						onClick={() =>
							setAttributes({ paragraphs: [...paragraphs, ""] })
						}
					>
						{__("Add paragraph", "ai-zippy-child")}
					</Button>
				</PanelBody>
				{paragraphs.map((text, index) => (
					<PanelBody
						key={index}
						title={`${__("Paragraph", "ai-zippy-child")} ${index + 1}`}
						initialOpen={false}
					>
						<TextareaControl
							label={__("Text", "ai-zippy-child")}
							rows={4}
							value={text}
							onChange={(value) => updateParagraph(index, value)}
						/>
						<Button
							variant="secondary"
							icon="arrow-up-alt2"
							onClick={() => moveParagraph(index, -1)}
						>
							{__("Move up", "ai-zippy-child")}
						</Button>{" "}
						<Button
							variant="secondary"
							icon="arrow-down-alt2"
							onClick={() => moveParagraph(index, 1)}
						>
							{__("Move down", "ai-zippy-child")}
						</Button>{" "}
						<Button
							variant="link"
							isDestructive
							onClick={() => removeParagraph(index)}
						>
							{__("Remove paragraph", "ai-zippy-child")}
						</Button>
					</PanelBody>
				))}
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
								<>
									<Button variant="secondary" onClick={open}>
										{imageId
											? __("Replace image", "ai-zippy-child")
											: __("Choose image", "ai-zippy-child")}
									</Button>{" "}
									{imageId ? (
										<Button
											variant="link"
											isDestructive
											onClick={() =>
												setAttributes({ imageId: 0, imageUrl: "" })
											}
										>
											{__("Clear", "ai-zippy-child")}
										</Button>
									) : null}
								</>
							)}
						/>
					</MediaUploadCheck>
					<p className="components-base-control__help">
						{__(
							"Defaults to store-aisle.jpg from the media map.",
							"ai-zippy-child"
						)}
					</p>
				</PanelBody>
			</InspectorControls>

			<div {...blockProps}>
				<ServerSideRender
					block="ai-zippy/about-story"
					attributes={attributes}
				/>
			</div>
		</>
	);
}
