import { defineConfig } from "vite";
import react from "@vitejs/plugin-react";
import { resolve } from "path";

// =============================================================================
// Parent theme (ai-zippy) Vite config
// =============================================================================
// Builds parent theme JS + SCSS only. Parent blocks are handled by @wordpress/scripts.
// Child theme has its own vite.config.child.js — the two processes are independent.
// =============================================================================

const parentDir = resolve(__dirname, "src/wp-content/themes/ai-zippy");
const parentSrc = resolve(parentDir, "src");

export default defineConfig({
	plugins: [react()],

	build: {
		outDir: resolve(parentDir, "assets/dist"),
		emptyOutDir: true,

		// Watch mode for `npm run dev`
		watch: process.env.NODE_ENV === "development" ? {} : null,

		rollupOptions: {
			input: {
				theme: resolve(parentSrc, "js/frontend/theme.js"),
				style: resolve(parentSrc, "scss/style.scss"),
				"shop-filter": resolve(parentSrc, "js/frontend/shop-filter/index.jsx"),
				wishlist: resolve(parentSrc, "js/frontend/wishlist.jsx"),
				cart: resolve(parentSrc, "js/frontend/cart/index.jsx"),
				checkout: resolve(parentSrc, "js/frontend/checkout/index.jsx"),
				product: resolve(parentSrc, "js/frontend/product/index.js"),
				"wc-checkout": resolve(parentSrc, "scss/wc-checkout-entry.scss"),
				"wc-checkout-phone": resolve(parentSrc, "js/frontend/wc-checkout-phone.js"),
				// Deprecated core mini-cart overrides. Its own entry so it only
				// loads on sites still using woocommerce/mini-cart.
				"mini-cart-legacy": resolve(parentSrc, "scss/mini-cart-legacy-entry.scss"),
			},
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
			"@": parentSrc,
			"@scss": resolve(parentSrc, "scss"),
		},
	},
});
