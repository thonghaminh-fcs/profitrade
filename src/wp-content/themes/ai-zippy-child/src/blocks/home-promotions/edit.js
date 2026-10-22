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
	ToggleControl,
	Button,
} from "@wordpress/components";
import ServerSideRender from "@wordpress/server-side-render";

// Shape of a card added through the inspector; the three seeded defaults live
// in block.json and match docs/build/blocks/home-promotions.md.
const NEW_CARD = {
	bg: "#FF9900",
	fg: "#111827",
	badge: "New Deal",
	badgeBg: "#111827",
	badgeFg: "#FFFFFF",
	title: "Deal title",
	sub: "Deal subtitle",
	image: "",
	imageUrl: "",
	imageId: 0,
	countdown: false,
};

export default function Edit({ attributes, setAttributes }) {
	const { heading, cards } = attributes;
	const blockProps = useBlockProps();

	const updateCard = (index, patch) =>
		setAttributes({
			cards: cards.map((card, i) =>
				i === index ? { ...card, ...patch } : card
			),
		});

	const moveCard = (index, delta) => {
		const to = index + delta;
		if (to < 0 || to >= cards.length) return;
		const next = [...cards];
		[next[index], next[to]] = [next[to], next[index]];
		setAttributes({ cards: next });
	};

	const removeCard = (index) =>
		setAttributes({ cards: cards.filter((_, i) => i !== index) });

	return (
		<>
			<InspectorControls>
				<PanelBody title={__("Section", "ai-zippy-child")} initialOpen>
					<TextControl
						label={__("Heading", "ai-zippy-child")}
						value={heading}
						onChange={(value) => setAttributes({ heading: value })}
					/>
					<Button
						variant="secondary"
						onClick={() => setAttributes({ cards: [...cards, { ...NEW_CARD }] })}
					>
						{__("Add card", "ai-zippy-child")}
					</Button>
				</PanelBody>
				{cards.map((card, index) => (
					<PanelBody
						key={index}
						title={`${__("Card", "ai-zippy-child")} ${index + 1}: ${card.title || ""}`}
						initialOpen={false}
					>
						<TextControl
							label={__("Badge", "ai-zippy-child")}
							value={card.badge}
							onChange={(value) => updateCard(index, { badge: value })}
						/>
						<TextControl
							label={__("Title", "ai-zippy-child")}
							value={card.title}
							onChange={(value) => updateCard(index, { title: value })}
						/>
						<TextControl
							label={__("Subtitle", "ai-zippy-child")}
							help={
								card.countdown
									? __("Replaced at runtime by the live countdown.", "ai-zippy-child")
									: undefined
							}
							value={card.sub}
							onChange={(value) => updateCard(index, { sub: value })}
						/>
						<ToggleControl
							label={__("Live countdown to midnight", "ai-zippy-child")}
							checked={!!card.countdown}
							onChange={(value) => updateCard(index, { countdown: !!value })}
						/>
						<TextControl
							label={__("Card background", "ai-zippy-child")}
							value={card.bg}
							onChange={(value) => updateCard(index, { bg: value })}
						/>
						<TextControl
							label={__("Card text colour", "ai-zippy-child")}
							value={card.fg}
							onChange={(value) => updateCard(index, { fg: value })}
						/>
						<TextControl
							label={__("Badge background", "ai-zippy-child")}
							value={card.badgeBg}
							onChange={(value) => updateCard(index, { badgeBg: value })}
						/>
						<TextControl
							label={__("Badge text colour", "ai-zippy-child")}
							value={card.badgeFg}
							onChange={(value) => updateCard(index, { badgeFg: value })}
						/>
						<TextControl
							label={__("Image name (media map)", "ai-zippy-child")}
							help={__("Fallback file name, e.g. product-chips.jpg.", "ai-zippy-child")}
							value={card.image}
							onChange={(value) => updateCard(index, { image: value })}
						/>
						<MediaUploadCheck>
							<MediaUpload
								allowedTypes={["image"]}
								value={card.imageId || undefined}
								onSelect={(media) =>
									updateCard(index, {
										imageId: media.id,
										imageUrl: media.url,
									})
								}
								render={({ open }) => (
									<Button variant="secondary" onClick={open}>
										{card.imageId
											? __("Replace image", "ai-zippy-child")
											: __("Choose image", "ai-zippy-child")}
									</Button>
								)}
							/>
						</MediaUploadCheck>
						<p>
							<Button variant="tertiary" onClick={() => moveCard(index, -1)}>
								{__("Move up", "ai-zippy-child")}
							</Button>{" "}
							<Button variant="tertiary" onClick={() => moveCard(index, 1)}>
								{__("Move down", "ai-zippy-child")}
							</Button>{" "}
							<Button isDestructive variant="tertiary" onClick={() => removeCard(index)}>
								{__("Remove", "ai-zippy-child")}
							</Button>
						</p>
					</PanelBody>
				))}
			</InspectorControls>

			<div {...blockProps}>
				<ServerSideRender
					block="ai-zippy/home-promotions"
					attributes={attributes}
				/>
			</div>
		</>
	);
}
