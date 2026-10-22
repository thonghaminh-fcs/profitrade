// REST base is injected by ShopAssets via the container's data-config, built
// from rest_url() so it follows the site's permalink setting: the pretty
// "/wp-json/ai-zippy/v1" on pretty permalinks, or "…/index.php?rest_route=/…"
// on Plain. Hardcoding the /wp-json form used to break the whole grid whenever
// permalinks were Plain — WP then serves the homepage HTML at that path with a
// 200, so the fetch "succeeded" but the body wasn't JSON.
//
// The fallback keeps a stale bundle (built before the base was injected) working
// on sites that do use pretty permalinks.
const FALLBACK_BASE = "/wp-json/ai-zippy/v1";

function buildUrl(endpoint, params = {}) {
	// Resolve against origin so a relative FALLBACK_BASE works; an absolute
	// injected base ignores the second arg. searchParams.set merges cleanly into
	// the ?rest_route= query string too, rather than tacking on a second "?".
	const url = new URL(endpoint, window.location.origin);

	Object.entries(params).forEach(([key, value]) => {
		if (value !== "" && value !== null && value !== undefined) {
			url.searchParams.set(key, value);
		}
	});

	return url;
}

async function getJson(url) {
	const res = await fetch(url);

	// An ok status isn't enough: a Plain-permalink misconfig makes WP answer the
	// pretty REST path with a 200 homepage. Guard on the content type so we throw
	// a real error the caller can log, instead of blowing up in res.json() (or
	// worse, leaving the UI stuck on a skeleton).
	if (!res.ok) {
		throw new Error(`Request failed: ${res.status} ${url.pathname}`);
	}

	const contentType = res.headers.get("content-type") || "";
	if (!contentType.includes("application/json")) {
		throw new Error(
			`Expected JSON from ${url.pathname}, got "${contentType}" — check the site's permalink settings.`,
		);
	}

	return res.json();
}

export async function fetchProducts(params = {}, base = FALLBACK_BASE) {
	return getJson(buildUrl(`${base}/products`, params));
}

export async function fetchFilterOptions(base = FALLBACK_BASE) {
	return getJson(buildUrl(`${base}/filter-options`));
}
