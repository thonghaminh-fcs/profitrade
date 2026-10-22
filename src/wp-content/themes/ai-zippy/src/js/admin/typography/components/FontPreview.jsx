import { useEffect, useMemo, useRef } from "@wordpress/element";
import { __ } from "@wordpress/i18n";

/**
 * Live sample rendering. Lazy-loads the selected font into the page so the
 * preview shows the actual typeface.
 */
export default function FontPreview({ config, uploads, sampleType }) {
	const styleRef = useRef(null);

	// Ensure the selected font is loaded into the page for preview purposes.
	useEffect(() => {
		const head = document.head;
		// Clean up any previously-added element for this slot
		if (styleRef.current && styleRef.current.parentNode) {
			styleRef.current.parentNode.removeChild(styleRef.current);
			styleRef.current = null;
		}

		if (!config.family || config.source === "system") return;

		if (config.source === "google") {
			const id = `zt-google-${slug(config.family)}`;
			if (!document.getElementById(id)) {
				const link = document.createElement("link");
				link.id = id;
				link.rel = "stylesheet";
				link.href = `https://fonts.googleapis.com/css2?family=${encodeURIComponent(config.family).replace(/%20/g, "+")}:wght@400;700&display=swap`;
				head.appendChild(link);
				styleRef.current = link;
			}
		} else if (config.source === "url" && config.url) {
			const link = document.createElement("link");
			link.rel = "stylesheet";
			link.href = config.url;
			head.appendChild(link);
			styleRef.current = link;
		} else if (config.source === "upload") {
			const entry = uploads.find((u) => u.family === config.family);
			if (entry && entry.files.length > 0) {
				const style = document.createElement("style");
				const mimeMap = { woff2: "woff2", woff: "woff", ttf: "truetype", otf: "opentype" };
				style.textContent = entry.files.map((f) =>
					`@font-face{font-family:'${config.family}';src:url('${f.url}') format('${mimeMap[f.ext] || "woff2"}');font-weight:${f.weight};font-style:${f.style};font-display:swap;}`
				).join("\n");
				head.appendChild(style);
				styleRef.current = style;
			}
		}

		return () => {
			if (styleRef.current && styleRef.current.parentNode) {
				styleRef.current.parentNode.removeChild(styleRef.current);
				styleRef.current = null;
			}
		};
	}, [config.source, config.family, config.url, uploads]);

	const fontFamily = useMemo(() => {
		if (!config.family || config.source === "system") {
			return "var(--wp-admin-theme-font, -apple-system, BlinkMacSystemFont, sans-serif)";
		}
		return `'${config.family.replace(/'/g, "\\'")}', -apple-system, BlinkMacSystemFont, sans-serif`;
	}, [config.family, config.source]);

	const sampleText =
		sampleType === "heading"
			? __("The quick brown fox", "ai-zippy")
			: __("The quick brown fox jumps over the lazy dog.", "ai-zippy");

	return (
		<div className="zt__preview" aria-label={__("Font preview", "ai-zippy")}>
			<span className="zt__preview-label">{__("Preview", "ai-zippy")}</span>
			<div
				className={`zt__preview-sample zt__preview-sample--${sampleType}`}
				style={{ fontFamily }}
			>
				{sampleText}
			</div>
		</div>
	);
}

function slug(s) {
	return s.toLowerCase().replace(/\s+/g, "-").replace(/[^a-z0-9-]/g, "");
}
