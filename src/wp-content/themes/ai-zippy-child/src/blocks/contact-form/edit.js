import { __ } from "@wordpress/i18n";
import { useBlockProps, InspectorControls } from "@wordpress/block-editor";
import { PanelBody, TextControl, TextareaControl } from "@wordpress/components";
import ServerSideRender from "@wordpress/server-side-render";

export default function Edit({ attributes, setAttributes }) {
	const { title, successTitle, successBody, recipient } = attributes;
	const blockProps = useBlockProps();

	return (
		<>
			<InspectorControls>
				<PanelBody title={__("Content", "ai-zippy-child")} initialOpen>
					<TextControl
						label={__("Form title", "ai-zippy-child")}
						value={title}
						onChange={(value) => setAttributes({ title: value })}
					/>
				</PanelBody>

				<PanelBody title={__("Success pane", "ai-zippy-child")}>
					<TextControl
						label={__("Heading", "ai-zippy-child")}
						value={successTitle}
						onChange={(value) => setAttributes({ successTitle: value })}
					/>
					<TextareaControl
						label={__("Body", "ai-zippy-child")}
						value={successBody}
						onChange={(value) => setAttributes({ successBody: value })}
					/>
				</PanelBody>

				<PanelBody title={__("Delivery", "ai-zippy-child")}>
					<TextControl
						label={__("Recipient email", "ai-zippy-child")}
						help={__(
							"Leave empty to send inquiries to the site admin email.",
							"ai-zippy-child"
						)}
						value={recipient}
						onChange={(value) => setAttributes({ recipient: value })}
					/>
				</PanelBody>
			</InspectorControls>

			<div {...blockProps}>
				<ServerSideRender
					block="ai-zippy/contact-form"
					attributes={attributes}
				/>
			</div>
		</>
	);
}
