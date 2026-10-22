import { defineConfig } from "vite";
import react from "@vitejs/plugin-react";
import { resolve } from "path";
import { existsSync } from "fs";

// =============================================================================
// Child theme (ai-zippy-child) Vite config
// =============================================================================
// Team uses this for client-specific customizations. Builds independently from
// the parent theme — team can run `npm run dev:child` without touching parent.
//
// Shared SCSS: use `@use "@parent/variables" as *;` to access parent's tokens
// (colors, breakpoints, mixins).
// =============================================================================

const parentDir = resolve(__dirname, "src/wp-content/themes/ai-zippy");
const parentSrc = resolve(parentDir, "src");

const childDir = resolve(__dirname, "src/wp-content/themes/ai-zippy-child");
const childSrc = resolve(childDir, "src");

// Only include entries that actually exist — lets the team add/remove without
// editing config.
const input = {};
const candidates = {
	"child-theme": resolve(childSrc, "js/child.js"),
	"child-style": resolve(childSrc, "scss/style.scss"),
};
for (const [key, path] of Object.entries(candidates)) {
	if (existsSync(path)) input[key] = path;
}

export default defineConfig({
	plugins: [react()],

	build: {
		outDir: resolve(childDir, "assets/dist"),
		emptyOutDir: true,

		watch: process.env.NODE_ENV === "development" ? {} : null,

		// No entry points? Skip the build entirely (first-run convenience).
		...(Object.keys(input).length === 0
			? { lib: false, write: false }
			: {}),

		rollupOptions: {
			input,
			output: {
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
		alias: {
			"@": childSrc,
			"@scss": resolve(childSrc, "scss"),

			// Shared with parent — child can import parent's tokens/mixins
			"@parent": parentSrc,
			"@parent-scss": resolve(parentSrc, "scss"),
		},
	},
});
