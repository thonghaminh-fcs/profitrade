import { __ } from "@wordpress/i18n";
import { useBlockProps, InspectorControls } from "@wordpress/block-editor";
import { PanelBody, TextControl, TextareaControl } from "@wordpress/components";
import ServerSideRender from "@wordpress/server-side-render";

export default function Edit({ attributes, setAttributes }) {
	const { expressFee, pickupNote } = attributes;
	const blockProps = useBlockProps();

	return (
		<>
			<InspectorControls>
				<PanelBody
					title={__("Checkout Fulfilment", "ai-zippy-child")}
					initialOpen
				>
					<TextControl
						label={__("Express fee", "ai-zippy-child")}
						help={__(
							"Charged as “Express delivery” when 1-Hour Express is selected.",
							"ai-zippy-child"
						)}
						type="number"
						min="0"
						step="0.01"
						value={String(expressFee)}
						onChange={(value) =>
							setAttributes({
								expressFee: Math.max(0, parseFloat(value) || 0),
							})
						}
					/>
					<TextareaControl
						label={__("Pickup note", "ai-zippy-child")}
						help={__(
							"Shown instead of the shipping address fields when Walk-in Pickup is selected.",
							"ai-zippy-child"
						)}
						rows={4}
						value={pickupNote}
						onChange={(value) => setAttributes({ pickupNote: value })}
					/>
				</PanelBody>
			</InspectorControls>

			<div {...blockProps}>
				<ServerSideRender
					block="ai-zippy/checkout-fulfilment"
					attributes={attributes}
				/>
			</div>
		</>
	);
}
