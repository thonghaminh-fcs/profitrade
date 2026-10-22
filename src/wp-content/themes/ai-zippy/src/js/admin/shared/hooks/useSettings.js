import { useCallback, useEffect, useState } from "@wordpress/element";
import { apiGet, apiPost } from "../api.js";

/**
 * Generic settings hook for AI Zippy admin panels.
 *
 * Usage:
 *   const { value, setValue, save, saving, loading, error, savedAt }
 *     = useSettings("typography");
 *
 * Assumes:
 *   GET  /ai-zippy/v1/{resource} → returns the current settings object
 *   POST /ai-zippy/v1/{resource} → accepts + persists the settings object
 *
 * @param {string} resource REST path segment (e.g. "typography")
 * @param {object} initial  Fallback value while loading
 */
export default function useSettings(resource, initial = {}) {
	const [value,   setValue]   = useState(initial);
	const [loading, setLoading] = useState(true);
	const [saving,  setSaving]  = useState(false);
	const [error,   setError]   = useState(null);
	const [savedAt, setSavedAt] = useState(null);

	// Initial fetch
	useEffect(() => {
		let cancelled = false;
		setLoading(true);
		apiGet(resource)
			.then((data) => {
				if (!cancelled) setValue(data);
			})
			.catch((e) => {
				if (!cancelled) setError(e.message || "Failed to load settings");
			})
			.finally(() => {
				if (!cancelled) setLoading(false);
			});
		return () => { cancelled = true; };
	}, [resource]);

	const save = useCallback(
		async (nextValue) => {
			setSaving(true);
			setError(null);
			try {
				const payload = nextValue ?? value;
				const data = await apiPost(resource, payload);
				setValue(data);
				setSavedAt(Date.now());
				return data;
			} catch (e) {
				setError(e.message || "Save failed");
				throw e;
			} finally {
				setSaving(false);
			}
		},
		[resource, value]
	);

	return { value, setValue, save, saving, loading, error, savedAt };
}
