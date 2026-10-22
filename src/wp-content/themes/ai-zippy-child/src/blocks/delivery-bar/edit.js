import { __ } from "@wordpress/i18n";
import { useBlockProps, InspectorControls } from "@wordpress/block-editor";
import { PanelBody, SelectControl, TextControl } from "@wordpress/components";
import ServerSideRender from "@wordpress/server-side-render";

// The five 3-hour slots, in order — must match the list in render.php.
const SLOTS = [
	"8:00 – 11:00 AM",
	"11:00 AM – 2:00 PM",
	"2:00 – 5:00 PM",
	"5:00 – 8:00 PM",
	"8:00 – 11:00 PM",
];

export default function Edit({ attributes, setAttributes }) {
	const { expressFee, openHoursText, defaultSlot } = attributes;
	const blockProps = useBlockProps();

	return (
		<>
			<InspectorControls>
				<PanelBody title={__("Delivery Bar", "ai-zippy-child")} initialOpen>
					<TextControl
						label={__("Opening hours text", "ai-zippy-child")}
						value={openHoursText}
						onChange={(value) => setAttributes({ openHoursText: value })}
					/>
					<SelectControl
						label={__("Default 3-hour slot", "ai-zippy-child")}
						value={String(defaultSlot)}
						options={SLOTS.map((label, index) => ({
							label,
							value: String(index),
						}))}
						onChange={(value) =>
							setAttributes({ defaultSlot: Number(value) || 0 })
						}
					/>
					<TextControl
						label={__("Express fee", "ai-zippy-child")}
						help={__("Shown as “+$20.00” beside the toggle.", "ai-zippy-child")}
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
				</PanelBody>
			</InspectorControls>

			<div {...blockProps}>
				<ServerSideRender
					block="ai-zippy/delivery-bar"
					attributes={attributes}
				/>
			</div>
		</>
	);
}
