import browserSync from "browser-sync";
import { readFileSync } from "fs";

// =============================================================================
// Parent theme BrowserSync — port 3000
// Watches parent theme output + PHP/HTML. Does NOT watch the child theme —
// the team's BrowserSync (port 3001) handles that.
// =============================================================================

const envContent = readFileSync(".env", "utf-8");
const hostMatch  = envContent.match(/PROJECT_HOST=(.+)/);
const wpUrl      = hostMatch ? hostMatch[1].trim() : "http://localhost:24";
const themeDir   = "src/wp-content/themes/ai-zippy";

browserSync.create("parent").init({
	proxy: wpUrl,
	port:  3000,
	open:  false,
	notify: false,
	ui: { port: 3010 }, // Parent BS UI on 3010; child BS uses 3001 + 3011

	files: [
		// CSS inject (no reload)
		`${themeDir}/assets/dist/css/**/*.css`,
		`${themeDir}/assets/blocks/**/*.css`,

		// Full reload on JS / PHP / HTML
		{
			match: [
				`${themeDir}/assets/dist/js/**/*.js`,
				`${themeDir}/assets/blocks/**/*.js`,
				`${themeDir}/**/*.php`,
				`${themeDir}/templates/**/*.html`,
				`${themeDir}/parts/**/*.html`,
				`${themeDir}/patterns/**/*.php`,
			],
			fn: function () { this.reload(); },
		},
	],

	ghostMode: false,
});
