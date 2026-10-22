import { __ } from "@wordpress/i18n";
import { useBlockProps, InspectorControls } from "@wordpress/block-editor";
import { PanelBody, TextControl, TextareaControl } from "@wordpress/components";
import ServerSideRender from "@wordpress/server-side-render";

export default function Edit({ attributes, setAttributes }) {
	const { heading, paragraph, ctaLabel, ctaUrl } = attributes;
	const blockProps = useBlockProps();

	return (
		<>
			<InspectorControls>
				<PanelBody title={__("Content", "ai-zippy-child")} initialOpen>
					<TextControl
						label={__("Heading", "ai-zippy-child")}
						value={heading}
						onChange={(value) => setAttributes({ heading: value })}
					/>
					<TextareaControl
						label={__("Paragraph", "ai-zippy-child")}
						value={paragraph}
						onChange={(value) => setAttributes({ paragraph: value })}
					/>
				</PanelBody>

				<PanelBody title={__("Button", "ai-zippy-child")}>
					<TextControl
						label={__("Label", "ai-zippy-child")}
						value={ctaLabel}
						onChange={(value) => setAttributes({ ctaLabel: value })}
					/>
					<TextControl
						label={__("URL", "ai-zippy-child")}
						value={ctaUrl}
						onChange={(value) => setAttributes({ ctaUrl: value })}
					/>
				</PanelBody>
			</InspectorControls>

			<div {...blockProps}>
				<ServerSideRender
					block="ai-zippy/home-cta-banner"
					attributes={attributes}
				/>
			</div>
		</>
	);
}
