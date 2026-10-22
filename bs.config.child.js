import browserSync from "browser-sync";
import { readFileSync } from "fs";

// =============================================================================
// Child theme BrowserSync — port 3001
// For the team working on client customizations. Runs alongside parent BS
// (port 3000) without conflicts. Watches child output + child PHP/HTML.
// =============================================================================

const envContent = readFileSync(".env", "utf-8");
const hostMatch  = envContent.match(/PROJECT_HOST=(.+)/);
const wpUrl      = hostMatch ? hostMatch[1].trim() : "http://localhost:24";
const childDir   = "src/wp-content/themes/ai-zippy-child";

browserSync.create("child").init({
	proxy: wpUrl,
	port:  3001,
	open:  false,
	notify: false,
	ui: { port: 3011 }, // Child BS UI on 3011

	files: [
		// CSS inject (no reload)
		`${childDir}/assets/dist/css/**/*.css`,
		`${childDir}/assets/blocks/**/*.css`,

		// Full reload on JS / PHP / HTML
		{
			match: [
				`${childDir}/assets/dist/js/**/*.js`,
				`${childDir}/assets/blocks/**/*.js`,
				`${childDir}/**/*.php`,
				`${childDir}/templates/**/*.html`,
				`${childDir}/parts/**/*.html`,
				`${childDir}/patterns/**/*.php`,
			],
			fn: function () { this.reload(); },
		},
	],

	ghostMode: false,
});
