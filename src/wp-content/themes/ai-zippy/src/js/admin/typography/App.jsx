import { useCallback, useState } from "@wordpress/element";
import { Button, Notice, Spinner, __experimentalHeading as Heading, __experimentalText as Text } from "@wordpress/components";
import { __ } from "@wordpress/i18n";
import useSettings from "../shared/hooks/useSettings.js";
import FontSlot from "./components/FontSlot.jsx";
import UploadSection from "./components/UploadSection.jsx";

export default function App() {
	const { value, setValue, save, saving, loading, error, savedAt } =
		useSettings("typography", { body: null, heading: null, uploads: [], googleFonts: [] });

	const [localError, setLocalError] = useState(null);

	const setBody = useCallback(
		(body) => setValue((v) => ({ ...v, body })),
		[setValue],
	);

	const setHeading = useCallback(
		(heading) => setValue((v) => ({ ...v, heading })),
		[setValue],
	);

	const handleSave = useCallback(async () => {
		setLocalError(null);
		try {
			await save({ body: value.body, heading: value.heading });
		} catch (e) {
			setLocalError(e.message || "Save failed");
		}
	}, [save, value.body, value.heading]);

	// Replace uploads list after upload/delete succeeds.
	const refreshUploads = useCallback(
		(nextUploads) => setValue((v) => ({ ...v, uploads: nextUploads })),
		[setValue],
	);

	if (loading || !value.body) {
		return (
			<div className="zt">
				<div className="zt__loading">
					<Spinner />
					<Text>{__("Loading typography settings…", "ai-zippy")}</Text>
				</div>
			</div>
		);
	}

	return (
		<div className="zt">
			<header className="zt__header">
				<Heading level={1} className="zt__title">
					{__("Typography", "ai-zippy")}
				</Heading>
				<Text className="zt__sub">
					{__(
						"Choose fonts for the site. Pick from Google Fonts, upload custom files, or paste a stylesheet URL.",
						"ai-zippy",
					)}
				</Text>
			</header>

			{(error || localError) && (
				<Notice status="error" isDismissible={false}>
					{error || localError}
				</Notice>
			)}

			<div className="zt__grid">
				<FontSlot
					title={__("Body Font", "ai-zippy")}
					description={__("Used for body text, paragraphs, and UI.", "ai-zippy")}
					value={value.body}
					onChange={setBody}
					uploads={value.uploads}
					googleFonts={value.googleFonts}
					sampleType="body"
				/>
				<FontSlot
					title={__("Heading Font", "ai-zippy")}
					description={__("Used for h1–h6. Leave as System to match the body font.", "ai-zippy")}
					value={value.heading}
					onChange={setHeading}
					uploads={value.uploads}
					googleFonts={value.googleFonts}
					sampleType="heading"
				/>
			</div>

			<div className="zt__actions">
				<Button
					variant="primary"
					size="compact"
					isBusy={saving}
					onClick={handleSave}
					disabled={saving}
				>
					{saving ? __("Saving…", "ai-zippy") : __("Save Changes", "ai-zippy")}
				</Button>
				{savedAt && !saving && (
					<span className="zt__saved-hint">{__("Saved.", "ai-zippy")}</span>
				)}
			</div>

			<UploadSection uploads={value.uploads} onUploadsChange={refreshUploads} />
		</div>
	);
}
