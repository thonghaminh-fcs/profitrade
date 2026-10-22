import { __ } from "@wordpress/i18n";
import { useBlockProps, InspectorControls } from "@wordpress/block-editor";
import {
	PanelBody,
	TextControl,
	Button,
} from "@wordpress/components";
import ServerSideRender from "@wordpress/server-side-render";

function LineList({ label, lines, onChange }) {
	const update = (index, value) =>
		onChange(lines.map((text, i) => (i === index ? value : text)));

	const move = (index, delta) => {
		const to = index + delta;
		if (to < 0 || to >= lines.length) return;
		const next = [...lines];
		[next[index], next[to]] = [next[to], next[index]];
		onChange(next);
	};

	const remove = (index) => onChange(lines.filter((_, i) => i !== index));

	return (
		<PanelBody title={label} initialOpen={false}>
			{lines.map((text, index) => (
				<div key={index} style={{ marginBottom: "12px" }}>
					<TextControl
						label={`${__("Line", "ai-zippy-child")} ${index + 1}`}
						value={text}
						onChange={(value) => update(index, value)}
					/>
					<Button
						variant="secondary"
						icon="arrow-up-alt2"
						onClick={() => move(index, -1)}
					>
						{__("Move up", "ai-zippy-child")}
					</Button>{" "}
					<Button
						variant="secondary"
						icon="arrow-down-alt2"
						onClick={() => move(index, 1)}
					>
						{__("Move down", "ai-zippy-child")}
					</Button>{" "}
					<Button variant="link" isDestructive onClick={() => remove(index)}>
						{__("Remove", "ai-zippy-child")}
					</Button>
				</div>
			))}
			<Button variant="secondary" onClick={() => onChange([...lines, ""])}>
				{__("Add line", "ai-zippy-child")}
			</Button>
		</PanelBody>
	);
}

export default function Edit({ attributes, setAttributes }) {
	const { mapEmbedUrl, addressLines, hoursLines, pickupNote } = attributes;
	const blockProps = useBlockProps();

	return (
		<>
			<InspectorControls>
				<PanelBody title={__("Map", "ai-zippy-child")} initialOpen>
					<TextControl
						label={__("Map embed URL", "ai-zippy-child")}
						help={__(
							"OpenStreetMap embed URL. Coordinates are placeholders — confirm the real store location.",
							"ai-zippy-child"
						)}
						value={mapEmbedUrl}
						onChange={(value) => setAttributes({ mapEmbedUrl: value })}
					/>
				</PanelBody>
				<LineList
					label={__("Address lines", "ai-zippy-child")}
					lines={addressLines}
					onChange={(next) => setAttributes({ addressLines: next })}
				/>
				<LineList
					label={__("Hours lines", "ai-zippy-child")}
					lines={hoursLines}
					onChange={(next) => setAttributes({ hoursLines: next })}
				/>
				<PanelBody title={__("Pickup note", "ai-zippy-child")}>
					<TextControl
						label={__("Note", "ai-zippy-child")}
						value={pickupNote}
						onChange={(value) => setAttributes({ pickupNote: value })}
					/>
				</PanelBody>
			</InspectorControls>

			<div {...blockProps}>
				<ServerSideRender
					block="ai-zippy/contact-location"
					attributes={attributes}
				/>
			</div>
		</>
	);
}
