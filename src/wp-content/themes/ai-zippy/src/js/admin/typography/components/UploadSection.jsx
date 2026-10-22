import { useCallback, useRef, useState } from "@wordpress/element";
import {
	Card,
	CardHeader,
	CardBody,
	Button,
	TextControl,
	Notice,
	DropZone,
	__experimentalText as Text,
} from "@wordpress/components";
import { __ } from "@wordpress/i18n";
import { apiUpload, apiDelete } from "../../shared/api.js";

const ACCEPT = ".woff2,.woff,.ttf,.otf";
const VALID_EXT = ["woff2", "woff", "ttf", "otf"];

export default function UploadSection({ uploads, onUploadsChange }) {
	const [family,   setFamily]   = useState("");
	const [uploading, setUploading] = useState(false);
	const [error,    setError]    = useState(null);
	const inputRef = useRef(null);

	const upload = useCallback(
		async (files) => {
			if (!family.trim()) {
				setError(__("Enter a family name first.", "ai-zippy"));
				return;
			}
			setError(null);
			setUploading(true);
			try {
				for (const file of files) {
					const ext = (file.name.split(".").pop() || "").toLowerCase();
					if (!VALID_EXT.includes(ext)) {
						throw new Error(__("Only .woff2, .woff, .ttf, .otf files are allowed.", "ai-zippy"));
					}
					const form = new FormData();
					form.append("family", family.trim());
					form.append("font_file", file);
					const res = await apiUpload("typography/upload", form);
					if (res && res.uploads) onUploadsChange(res.uploads);
				}
				// Clear file input after success
				if (inputRef.current) inputRef.current.value = "";
			} catch (e) {
				setError(e.message || __("Upload failed.", "ai-zippy"));
			} finally {
				setUploading(false);
			}
		},
		[family, onUploadsChange],
	);

	const handleFileChange = (e) => {
		const files = Array.from(e.target.files || []);
		if (files.length) upload(files);
	};

	const handleDrop = (files) => {
		if (files && files.length) upload(Array.from(files));
	};

	const handleDelete = useCallback(
		async (fontFamily, filename) => {
			if (!window.confirm(__("Delete this font file?", "ai-zippy"))) return;
			try {
				const res = await apiDelete(
					`typography/fonts/${encodeURIComponent(fontFamily)}/${encodeURIComponent(filename)}`,
				);
				if (res && res.uploads) onUploadsChange(res.uploads);
			} catch (e) {
				setError(e.message || __("Delete failed.", "ai-zippy"));
			}
		},
		[onUploadsChange],
	);

	return (
		<Card className="zt__upload" size="small">
			<CardHeader>
				<div>
					<h2 className="zt__slot-title">{__("Custom Font Uploads", "ai-zippy")}</h2>
					<Text className="zt__slot-desc">
						{__(
							"Upload .woff2 / .woff / .ttf / .otf files. Filenames containing \"bold\", \"medium\", \"italic\" map to weights automatically.",
							"ai-zippy",
						)}
					</Text>
				</div>
			</CardHeader>
			<CardBody>
				{error && (
					<Notice status="error" onRemove={() => setError(null)}>
						{error}
					</Notice>
				)}

				<div className="zt__upload-form">
					<TextControl
						__nextHasNoMarginBottom
						label={__("Family name", "ai-zippy")}
						value={family}
						onChange={setFamily}
						placeholder={__("e.g. Brand Sans", "ai-zippy")}
					/>

					<div className="zt__upload-drop">
						<DropZone onFilesDrop={handleDrop} label={__("Drop font files here", "ai-zippy")} />
						<div className="zt__upload-drop-inner">
							<Text>
								{uploading
									? __("Uploading…", "ai-zippy")
									: __("Drag & drop files here, or:", "ai-zippy")}
							</Text>
							<input
								ref={inputRef}
								type="file"
								accept={ACCEPT}
								multiple
								onChange={handleFileChange}
								disabled={uploading || !family.trim()}
								className="zt__upload-input"
							/>
						</div>
					</div>
				</div>

				{uploads.length > 0 && (
					<div className="zt__upload-list">
						<h3 className="zt__upload-list-title">{__("Uploaded Fonts", "ai-zippy")}</h3>
						{uploads.map((family) => (
							<div key={family.family} className="zt__upload-family">
								<div className="zt__upload-family-name">{family.family}</div>
								<ul className="zt__upload-files">
									{family.files.map((f) => (
										<li key={f.filename} className="zt__upload-file">
											<code>{f.filename}</code>
											<span className="zt__upload-file-meta">
												{__("Weight", "ai-zippy")}: {f.weight}, {f.style}
											</span>
											<Button
												variant="tertiary"
												size="small"
												isDestructive
												onClick={() => handleDelete(family.family, f.filename)}
											>
												{__("Delete", "ai-zippy")}
											</Button>
										</li>
									))}
								</ul>
							</div>
						))}
					</div>
				)}
			</CardBody>
		</Card>
	);
}
