import { useCallback } from "@wordpress/element";
import {
	Card,
	CardHeader,
	CardBody,
	__experimentalToggleGroupControl as ToggleGroupControl,
	__experimentalToggleGroupControlOption as ToggleGroupControlOption,
	SelectControl,
	TextControl,
	__experimentalText as Text,
} from "@wordpress/components";
import { __ } from "@wordpress/i18n";
import FontPreview from "./FontPreview.jsx";

export default function FontSlot({ title, description, value, onChange, uploads, googleFonts, sampleType }) {
	const update = useCallback(
		(patch) => onChange({ ...value, ...patch }),
		[onChange, value],
	);

	const setSource = useCallback((source) => update({ source, family: "", url: "" }), [update]);

	return (
		<Card className="zt__slot" size="small">
			<CardHeader className="zt__slot-header">
				<div>
					<h2 className="zt__slot-title">{title}</h2>
					<Text className="zt__slot-desc">{description}</Text>
				</div>
			</CardHeader>
			<CardBody>
				<div className="zt__slot-field">
					<label className="zt__label">{__("Source", "ai-zippy")}</label>
					<ToggleGroupControl
						__nextHasNoMarginBottom
						isBlock
						value={value.source}
						onChange={setSource}
						hideLabelFromVision
						label={__("Source", "ai-zippy")}
					>
						<ToggleGroupControlOption value="system" label={__("System", "ai-zippy")} />
						<ToggleGroupControlOption value="google" label={__("Google", "ai-zippy")} />
						<ToggleGroupControlOption value="upload" label={__("Upload", "ai-zippy")} />
						<ToggleGroupControlOption value="url"    label={__("URL",    "ai-zippy")} />
					</ToggleGroupControl>
				</div>

				{value.source === "google" && (
					<div className="zt__slot-field">
						<SelectControl
							__nextHasNoMarginBottom
							label={__("Google font", "ai-zippy")}
							value={value.family}
							onChange={(family) => update({ family })}
							options={[
								{ label: __("— Select —", "ai-zippy"), value: "" },
								...googleFonts,
							]}
						/>
					</div>
				)}

				{value.source === "upload" && (
					<div className="zt__slot-field">
						{uploads.length === 0 ? (
							<Text variant="muted">
								{__("No fonts uploaded yet. Add one in the section below.", "ai-zippy")}
							</Text>
						) : (
							<SelectControl
								__nextHasNoMarginBottom
								label={__("Uploaded family", "ai-zippy")}
								value={value.family}
								onChange={(family) => update({ family })}
								options={[
									{ label: __("— Select —", "ai-zippy"), value: "" },
									...uploads.map((u) => ({ label: u.family, value: u.family })),
								]}
							/>
						)}
					</div>
				)}

				{value.source === "url" && (
					<>
						<div className="zt__slot-field">
							<TextControl
								__nextHasNoMarginBottom
								label={__("Family name", "ai-zippy")}
								help={__("CSS family name — must match what the external stylesheet declares.", "ai-zippy")}
								value={value.family}
								onChange={(family) => update({ family })}
								placeholder="Proxima Nova"
							/>
						</div>
						<div className="zt__slot-field">
							<TextControl
								__nextHasNoMarginBottom
								type="url"
								label={__("Stylesheet URL", "ai-zippy")}
								help={__("Full URL of the CSS file from your font provider (Adobe Fonts, Fontshare, etc.).", "ai-zippy")}
								value={value.url}
								onChange={(url) => update({ url })}
								placeholder="https://use.typekit.net/xxxxxxx.css"
							/>
						</div>
					</>
				)}

				<FontPreview config={value} uploads={uploads} sampleType={sampleType} />
			</CardBody>
		</Card>
	);
}
