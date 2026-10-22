import { defineConfig } from "vite";
import react from "@vitejs/plugin-react";
import { resolve } from "path";
import { existsSync } from "fs";

// =============================================================================
// Admin React apps Vite config
// =============================================================================
// Builds React apps that mount inside wp-admin pages. Instead of bundling
// React + @wordpress/components (~500KB), we alias those imports to tiny shim
// files that read from window.wp.* at runtime — WP provides the real modules.
//
// This keeps admin bundles small (~15KB) and ensures we use the exact same
// React/components runtime that WP core uses (no version mismatch).
// =============================================================================

const parentDir = resolve(__dirname, "src/wp-content/themes/ai-zippy");
const parentSrc = resolve(parentDir, "src");
const shims     = resolve(parentSrc, "js/admin/shared/wp-shims");

// Admin entries — only include if source file exists.
const input = {};
const candidates = {
	"admin-typography": resolve(parentSrc, "js/admin/typography/index.jsx"),
	// Future admin apps go here.
};
for (const [key, path] of Object.entries(candidates)) {
	if (existsSync(path)) input[key] = path;
}

export default defineConfig({
	// Classic JSX runtime — transforms <Foo /> into React.createElement(Foo).
	// wp.element exposes createElement but NOT the automatic runtime's
	// jsx/jsxs helpers, so classic is the only shim-compatible path.
	plugins: [react({ jsxRuntime: "classic" })],

	build: {
		outDir: resolve(parentDir, "assets/dist-admin"),
		emptyOutDir: true,
		watch: process.env.NODE_ENV === "development" ? {} : null,
		cssCodeSplit: true,

		rollupOptions: {
			input,
			output: {
				format: "es",
				entryFileNames: "js/[name].js",
				chunkFileNames: "js/chunks/[name]-[hash].js",
				assetFileNames: (assetInfo) => {
					if (assetInfo.name && assetInfo.name.endsWith(".css")) {
						return "css/[name].css";
					}
					return "assets/[name]-[hash][extname]";
				},
			},
		},

		manifest: true,
		sourcemap: true,
	},

	css: {
		preprocessorOptions: {
			scss: { api: "modern-compiler" },
		},
	},

	resolve: {
		alias: [
			// Alias WP packages → local shim files that proxy to window.wp.*
			{ find: /^@wordpress\/element$/,   replacement: resolve(shims, "element.js") },
			{ find: /^@wordpress\/components$/, replacement: resolve(shims, "components.js") },
			{ find: /^@wordpress\/i18n$/,       replacement: resolve(shims, "i18n.js") },
			{ find: /^@wordpress\/api-fetch$/,  replacement: resolve(shims, "api-fetch.js") },

			// React: use WP's element re-exports so refs, contexts, etc. are
			// the same React instance WP uses (avoids dual-React bugs).
			{ find: /^react$/,     replacement: resolve(shims, "element.js") },
			{ find: /^react-dom$/, replacement: resolve(shims, "element.js") },

			// Project aliases
			{ find: "@",      replacement: parentSrc },
			{ find: "@scss",  replacement: resolve(parentSrc, "scss") },
			{ find: "@admin", replacement: resolve(parentSrc, "js/admin") },
		],
	},
});
