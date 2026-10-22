import { __ } from "@wordpress/i18n";
import { useBlockProps, InspectorControls } from "@wordpress/block-editor";
import { PanelBody, TextControl, TextareaControl } from "@wordpress/components";
import ServerSideRender from "@wordpress/server-side-render";

export default function Edit({ attributes, setAttributes }) {
	const { title, body, ctaLabel, whatsappNumber } = attributes;
	const blockProps = useBlockProps();

	return (
		<>
			<InspectorControls>
				<PanelBody title={__("Content", "ai-zippy-child")} initialOpen>
					<TextControl
						label={__("Title", "ai-zippy-child")}
						value={title}
						onChange={(value) => setAttributes({ title: value })}
					/>
					<TextareaControl
						label={__("Body", "ai-zippy-child")}
						value={body}
						onChange={(value) => setAttributes({ body: value })}
					/>
				</PanelBody>

				<PanelBody title={__("WhatsApp CTA", "ai-zippy-child")}>
					<TextControl
						label={__("Button label", "ai-zippy-child")}
						value={ctaLabel}
						onChange={(value) => setAttributes({ ctaLabel: value })}
					/>
					<TextControl
						label={__("WhatsApp number (international, digits only)", "ai-zippy-child")}
						help={__("Used to build the wa.me link, e.g. 6590000000.", "ai-zippy-child")}
						value={whatsappNumber}
						onChange={(value) => setAttributes({ whatsappNumber: value })}
					/>
				</PanelBody>
			</InspectorControls>

			<div {...blockProps}>
				<ServerSideRender
					block="ai-zippy/contact-banner"
					attributes={attributes}
				/>
			</div>
		</>
	);
}
