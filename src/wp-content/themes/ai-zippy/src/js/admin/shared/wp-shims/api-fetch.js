// Shim for @wordpress/api-fetch → reads from window.wp.apiFetch.
const m = (typeof window !== "undefined" && window.wp && window.wp.apiFetch) || null;

// Fallback — will throw a helpful error if apiFetch isn't loaded.
const fallback = () => {
	throw new Error("wp.apiFetch is not available. Ensure 'wp-api-fetch' is enqueued as a script dependency.");
};

const fn = typeof m === "function" ? m : (m && m.default) || fallback;
export default fn;

// Convenience statics
export const createNonceMiddleware = (m && m.createNonceMiddleware) || (() => {});
export const createRootURLMiddleware = (m && m.createRootURLMiddleware) || (() => {});
export const setFetchHandler = (m && m.setFetchHandler) || (() => {});
