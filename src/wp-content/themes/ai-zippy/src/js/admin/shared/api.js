import apiFetch from "@wordpress/api-fetch";

// =============================================================================
// Shared REST client for AI Zippy admin apps.
// All routes live under /wp-json/ai-zippy/v1/*. apiFetch auto-attaches the
// nonce injected by WordPress when enqueueing wp-api-fetch.
// =============================================================================

const BASE = "/ai-zippy/v1/";

/**
 * GET /ai-zippy/v1/{path}
 */
export function apiGet(path) {
	return apiFetch({ path: BASE + path.replace(/^\//, "") });
}

/**
 * POST JSON to /ai-zippy/v1/{path}
 */
export function apiPost(path, data = {}) {
	return apiFetch({
		path: BASE + path.replace(/^\//, ""),
		method: "POST",
		data,
	});
}

/**
 * DELETE /ai-zippy/v1/{path}
 */
export function apiDelete(path) {
	return apiFetch({
		path: BASE + path.replace(/^\//, ""),
		method: "DELETE",
	});
}

/**
 * Upload a file via multipart/form-data.
 * @param {string} path  - REST path, e.g. "typography/upload"
 * @param {FormData} formData - must already contain the file + fields
 */
export function apiUpload(path, formData) {
	return apiFetch({
		path: BASE + path.replace(/^\//, ""),
		method: "POST",
		body: formData, // apiFetch preserves FormData + lets browser set Content-Type
	});
}
