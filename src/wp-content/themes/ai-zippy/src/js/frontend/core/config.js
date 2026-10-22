/**
 * Runtime config accessors.
 *
 * Every value here comes from PHP (AiZippy\Core\Runtime) as window.aiZippy.
 * Core modules must read URLs, text, selectors, feature flags and tunables
 * through these helpers instead of hardcoding them, so a client site can change
 * behaviour from its child theme without forking the parent bundle.
 *
 * All accessors tolerate a missing config — the bundle also loads in contexts
 * where PHP never printed it (editor preview, isolated admin screens).
 */

const EMPTY = {};

/** The raw config object. */
export function azConfig() {
	return window.aiZippy || EMPTY;
}

/**
 * A translated string by key, with %s placeholders filled from `args` in order.
 *
 * azText("cart.add_success_many", "%s products added to cart", 3)
 *
 * The replacer is a function on purpose: passing a value as the replacement
 * string would let `$&`, `$'` and friends inside a search query rewrite the
 * result.
 */
export function azText(key, fallback = "", ...args) {
	const strings = azConfig().i18n || EMPTY;
	const template = typeof strings[key] === "string" ? strings[key] : fallback;

	return args.reduce((out, arg) => out.replace("%s", () => String(arg)), template);
}

/** A page URL by name: home, shop, cart, checkout, account, search. */
export function azUrl(name, fallback = "/") {
	const url = (azConfig().urls || EMPTY)[name];
	return typeof url === "string" && url ? url : fallback;
}

/**
 * A page URL with query parameters applied. Permalinks vary per site, so the
 * base always comes from the config and never from a hardcoded path.
 */
export function azUrlWithQuery(name, params = {}, fallback = "/") {
	const base = azUrl(name, fallback);

	try {
		const url = new URL(base, window.location.origin);
		Object.entries(params).forEach(([key, value]) => {
			url.searchParams.set(key, String(value));
		});
		return url.toString();
	} catch {
		// Malformed base — fall back to plain string concatenation.
		const query = new URLSearchParams(params).toString();
		return `${base}${base.includes("?") ? "&" : "?"}${query}`;
	}
}

/** A REST endpoint URL by name. */
export function azEndpoint(name, fallback = "") {
	const url = (azConfig().endpoints || EMPTY)[name];
	return typeof url === "string" && url ? url : fallback;
}

/** The REST nonce for authenticated calls to our own endpoints. */
export function azNonce() {
	return (azConfig().rest || EMPTY).nonce || "";
}

/**
 * A REST route on this site, resolved against the site's own REST base.
 *
 * The base must come from the config: with plain permalinks rest_url() is
 * `/?rest_route=/`, so a hardcoded `/wp-json/...` path 404s. It also differs on
 * a subdirectory install or behind a custom rest prefix.
 *
 *   azRestUrl("wc/store/v1/cart")
 */
export function azRestUrl(route) {
	const base = (azConfig().rest || EMPTY).url || "/wp-json/";

	return `${base.endsWith("/") ? base : `${base}/`}${route.replace(/^\/+/, "")}`;
}

/**
 * Whether a core feature is on. Defaults to true so a module still runs if the
 * config failed to print — losing behaviour silently is worse than running it.
 */
export function azFeature(key, fallback = true) {
	const features = azConfig().features || EMPTY;
	return key in features ? Boolean(features[key]) : fallback;
}

/** A numeric tunable. */
export function azSetting(key, fallback) {
	const value = (azConfig().settings || EMPTY)[key];
	const parsed = Number(value);

	return Number.isFinite(parsed) ? parsed : fallback;
}

/** A DOM selector from the markup contract. */
export function azSelector(name, fallback) {
	const selector = (azConfig().selectors || EMPTY)[name];
	return typeof selector === "string" && selector ? selector : fallback;
}
