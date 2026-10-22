import { __, sprintf } from "@wordpress/i18n";
import { useBlockProps, InspectorControls } from "@wordpress/block-editor";
import { PanelBody, TextControl, TextareaControl } from "@wordpress/components";
import ServerSideRender from "@wordpress/server-side-render";

export default function Edit({ attributes, setAttributes }) {
	const { heading, items } = attributes;
	const blockProps = useBlockProps();

	const updateItem = (index, key, value) => {
		const next = items.map((item, i) =>
			i === index ? { ...item, [key]: value } : item
		);
		setAttributes({ items: next });
	};

	return (
		<>
			<InspectorControls>
				<PanelBody title={__("Heading", "ai-zippy-child")} initialOpen>
					<TextControl
						label={__("Heading", "ai-zippy-child")}
						value={heading}
						onChange={(value) => setAttributes({ heading: value })}
					/>
				</PanelBody>

				{(items || []).map((item, index) => (
					<PanelBody
						key={index}
						title={sprintf(
							/* translators: %s: card number, e.g. 01 */
							__("Card %s", "ai-zippy-child"),
							item.number || index + 1
						)}
						initialOpen={false}
					>
						<TextControl
							label={__("Number", "ai-zippy-child")}
							value={item.number}
							onChange={(value) => updateItem(index, "number", value)}
						/>
						<TextControl
							label={__("Title", "ai-zippy-child")}
							value={item.title}
							onChange={(value) => updateItem(index, "title", value)}
						/>
						<TextareaControl
							label={__("Body", "ai-zippy-child")}
							value={item.body}
							onChange={(value) => updateItem(index, "body", value)}
						/>
						<TextControl
							label={__("Blob background", "ai-zippy-child")}
							help={__("CSS colour, e.g. #FF1A1A.", "ai-zippy-child")}
							value={item.color}
							onChange={(value) => updateItem(index, "color", value)}
						/>
						<TextControl
							label={__("Blob number colour", "ai-zippy-child")}
							help={__("CSS colour, e.g. #FFFFFF.", "ai-zippy-child")}
							value={item.foreground}
							onChange={(value) => updateItem(index, "foreground", value)}
						/>
					</PanelBody>
				))}
			</InspectorControls>

			<div {...blockProps}>
				<ServerSideRender
					block="ai-zippy/about-advantages"
					attributes={attributes}
				/>
			</div>
		</>
	);
}
