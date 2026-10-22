<?php

namespace AiZippy\Admin;

defined('ABSPATH') || exit;

/**
 * Typography — hybrid font picker for the theme.
 *
 * Admin chooses a font for body + heading, from three sources:
 *   1. Google Fonts (curated list) — auto-enqueued with preconnect.
 *   2. Custom upload — .woff2/.woff/.ttf/.otf files uploaded per weight
 *      into wp-content/uploads/ai-zippy-fonts/. We emit @font-face rules.
 *   3. External URL / CSS import — paste a CSS URL (Adobe Fonts, Fontshare,
 *      Typekit, etc.). We inject <link rel="stylesheet">.
 *
 * The admin UI is a React app mounted inside a submenu under "Zippy AI".
 * All data operations go through \AiZippy\Api\TypographyApi (REST routes under
 * /ai-zippy/v1/typography). This class owns the data layer and frontend CSS.
 *
 * CSS variables emitted on :root for parent + child SCSS and blocks:
 *   --zippy-font-primary  → body
 *   --zippy-font-heading  → headings
 */
class Typography
{
    public const OPTION_BODY    = 'ai_zippy_font_body';
    public const OPTION_HEADING = 'ai_zippy_font_heading';
    public const UPLOAD_DIR     = 'ai-zippy-fonts';
    public const SUBMENU_SLUG   = 'zippy-ai-typography';

    /**
     * Curated Google Fonts list.
     */
    public const GOOGLE_FONTS = [
        'Inter'            => ['family' => 'Inter',            'weights' => '400;500;600;700'],
        'Poppins'          => ['family' => 'Poppins',          'weights' => '400;500;600;700'],
        'Nunito Sans'      => ['family' => 'Nunito+Sans',      'weights' => '400;600;700'],
        'DM Sans'          => ['family' => 'DM+Sans',          'weights' => '400;500;700'],
        'Roboto'           => ['family' => 'Roboto',           'weights' => '400;500;700'],
        'Open Sans'        => ['family' => 'Open+Sans',        'weights' => '400;600;700'],
        'Montserrat'       => ['family' => 'Montserrat',       'weights' => '400;600;700'],
        'Raleway'          => ['family' => 'Raleway',          'weights' => '400;600;700'],
        'Playfair Display' => ['family' => 'Playfair+Display', 'weights' => '400;600;700'],
        'Merriweather'     => ['family' => 'Merriweather',     'weights' => '400;700'],
        'Lora'             => ['family' => 'Lora',             'weights' => '400;600;700'],
    ];

    public static function register(): void
    {
        add_action('admin_init',          [self::class, 'registerSettings']);
        add_action('admin_menu',          [self::class, 'addSubMenu'], 20);
        add_action('admin_enqueue_scripts', [self::class, 'enqueueAdminApp']);
        add_action('wp_head',             [self::class, 'renderFrontendCss'], 5);
        add_action('admin_head',          [self::class, 'renderEditorCss'], 5);
    }

    // =========================================================================
    // Settings
    // =========================================================================

    public static function registerSettings(): void
    {
        foreach ([self::OPTION_BODY, self::OPTION_HEADING] as $key) {
            register_setting(
                'zippy_ai_typography_group',
                $key,
                [
                    'type'              => 'array',
                    'sanitize_callback' => [self::class, 'sanitizeFontConfig'],
                    'default'           => self::defaultConfig(),
                    'show_in_rest'      => false, // exposed via our own REST API
                ]
            );
        }
    }

    public static function defaultConfig(): array
    {
        return [
            'source' => 'system', // system | google | upload | url
            'family' => '',
            'url'    => '',
        ];
    }

    public static function sanitizeFontConfig($input): array
    {
        $input  = is_array($input) ? $input : [];
        $config = self::defaultConfig();

        $source = $input['source'] ?? 'system';
        if (!in_array($source, ['system', 'google', 'upload', 'url'], true)) {
            $source = 'system';
        }
        $config['source'] = $source;

        if ($source === 'google') {
            $family = sanitize_text_field($input['family'] ?? '');
            if (isset(self::GOOGLE_FONTS[$family])) {
                $config['family'] = $family;
            } else {
                $config['source'] = 'system';
            }
        } elseif ($source === 'upload') {
            $config['family'] = sanitize_text_field($input['family'] ?? '');
            if ($config['family'] === '') {
                $config['source'] = 'system';
            }
        } elseif ($source === 'url') {
            $config['family'] = sanitize_text_field($input['family'] ?? '');
            $config['url']    = esc_url_raw($input['url'] ?? '');
            if ($config['family'] === '' || $config['url'] === '') {
                $config['source'] = 'system';
            }
        }

        return $config;
    }

    public static function getConfig(string $option): array
    {
        $stored = get_option($option, []);
        return array_merge(self::defaultConfig(), is_array($stored) ? $stored : []);
    }

    // =========================================================================
    // Admin submenu — mounts the React app
    // =========================================================================

    public static function addSubMenu(): void
    {
        add_submenu_page(
            ThemeOptions::SLUG,
            __('Typography', 'ai-zippy'),
            __('Typography', 'ai-zippy'),
            'manage_options',
            self::SUBMENU_SLUG,
            [self::class, 'renderAppMount']
        );
    }

    /**
     * Render the mount point for the React admin app.
     */
    public static function renderAppMount(): void
    {
        if (!current_user_can('manage_options')) {
            return;
        }
        echo '<div class="wrap" id="ai-zippy-typography-app"></div>';
    }

    /**
     * Enqueue the React admin app on our submenu page only.
     */
    public static function enqueueAdminApp(string $hook): void
    {
        // Hook format: "zippy-ai_page_zippy-ai-typography" (from add_submenu_page)
        if (!str_contains($hook, self::SUBMENU_SLUG)) {
            return;
        }

        \AiZippy\Core\ViteAssets::enqueueAdmin(
            'ai-zippy-admin-typography',
            'src/wp-content/themes/ai-zippy/src/js/admin/typography/index.jsx'
        );
    }

    // =========================================================================
    // Uploads (called from TypographyApi)
    // =========================================================================

    public static function getUploadDir(): array
    {
        $uploads = wp_upload_dir();
        return [
            'path' => trailingslashit($uploads['basedir']) . self::UPLOAD_DIR,
            'url'  => trailingslashit($uploads['baseurl']) . self::UPLOAD_DIR,
        ];
    }

    /**
     * Scan upload dir; return families keyed by family name.
     */
    public static function getUploadedFonts(): array
    {
        $dir = self::getUploadDir();
        if (!is_dir($dir['path'])) {
            return [];
        }

        $fonts = [];
        foreach (glob($dir['path'] . '/*', GLOB_ONLYDIR) as $family_path) {
            $family = basename($family_path);
            $files  = [];
            foreach (glob($family_path . '/*.{woff2,woff,ttf,otf}', GLOB_BRACE) as $file) {
                $basename = basename($file);
                $parsed   = self::parseFontFilename($basename);
                $files[]  = [
                    'filename' => $basename,
                    'url'      => $dir['url'] . '/' . rawurlencode($family) . '/' . rawurlencode($basename),
                    'weight'   => $parsed['weight'],
                    'style'    => $parsed['style'],
                    'ext'      => $parsed['ext'],
                ];
            }
            if (!empty($files)) {
                $fonts[$family] = $files;
            }
        }
        ksort($fonts);
        return $fonts;
    }

    /**
     * Save an uploaded font file. Returns ['family' => ..., 'filename' => ...]
     * or throws \RuntimeException.
     */
    public static function saveUploadedFont(string $family, array $file): array
    {
        if ($family === '') {
            throw new \RuntimeException(__('Family name is required.', 'ai-zippy'));
        }
        if (empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            throw new \RuntimeException(__('Invalid upload.', 'ai-zippy'));
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['woff2', 'woff', 'ttf', 'otf'], true)) {
            throw new \RuntimeException(__('Unsupported file type.', 'ai-zippy'));
        }

        $mime = function_exists('mime_content_type') ? mime_content_type($file['tmp_name']) : 'application/octet-stream';
        $allowed_mimes = [
            'font/woff2', 'font/woff', 'font/ttf', 'font/otf',
            'application/font-woff', 'application/font-woff2',
            'application/x-font-ttf', 'application/x-font-otf',
            'application/vnd.ms-opentype',
            'application/octet-stream',
        ];
        if (!in_array($mime, $allowed_mimes, true)) {
            throw new \RuntimeException(__('File does not appear to be a valid font.', 'ai-zippy'));
        }

        if ($file['size'] > 5 * 1024 * 1024) {
            throw new \RuntimeException(__('Font file exceeds 5MB limit.', 'ai-zippy'));
        }

        $dir        = self::getUploadDir();
        $family_dir = $dir['path'] . '/' . $family;

        if (!wp_mkdir_p($family_dir)) {
            throw new \RuntimeException(__('Could not create font directory.', 'ai-zippy'));
        }

        // Block PHP execution in fonts dir (defense in depth)
        $htaccess = $dir['path'] . '/.htaccess';
        if (!file_exists($htaccess)) {
            @file_put_contents($htaccess, "Options -Indexes\n<FilesMatch \"\\.(php|phtml)$\">\nRequire all denied\n</FilesMatch>\n");
        }

        $safe_name = sanitize_file_name($file['name']);
        $target    = $family_dir . '/' . $safe_name;

        if (!move_uploaded_file($file['tmp_name'], $target)) {
            throw new \RuntimeException(__('Could not save uploaded file.', 'ai-zippy'));
        }

        return ['family' => $family, 'filename' => $safe_name];
    }

    /**
     * Delete an uploaded font file. Returns true on success.
     */
    public static function deleteUploadedFont(string $family, string $filename): bool
    {
        $family   = sanitize_text_field($family);
        $filename = sanitize_file_name($filename);
        if ($family === '' || $filename === '') {
            return false;
        }

        $dir  = self::getUploadDir();
        $path = $dir['path'] . '/' . $family . '/' . $filename;

        // Defense: ensure path is inside the upload dir
        $real_base = realpath($dir['path']);
        $real_path = realpath($path);
        if (!$real_base || !$real_path || !str_starts_with($real_path, $real_base)) {
            return false;
        }

        if (file_exists($real_path)) {
            @unlink($real_path);
        }

        // Clean up empty family dir
        $family_dir = $dir['path'] . '/' . $family;
        if (is_dir($family_dir) && count(glob($family_dir . '/*')) === 0) {
            @rmdir($family_dir);
        }

        return true;
    }

    private static function parseFontFilename(string $filename): array
    {
        $ext  = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $name = strtolower(pathinfo($filename, PATHINFO_FILENAME));

        $weight = 400;
        foreach ([
            'thin' => 100, 'hairline' => 100, 'extralight' => 200, 'ultralight' => 200,
            'light' => 300, 'regular' => 400, 'normal' => 400, 'book' => 400,
            'medium' => 500, 'semibold' => 600, 'demibold' => 600,
            'bold' => 700, 'extrabold' => 800, 'ultrabold' => 800,
            'black' => 900, 'heavy' => 900,
        ] as $needle => $w) {
            if (str_contains($name, $needle)) {
                $weight = $w;
                break;
            }
        }

        $style = (str_contains($name, 'italic') || str_contains($name, 'oblique')) ? 'italic' : 'normal';

        return ['weight' => $weight, 'style' => $style, 'ext' => $ext];
    }

    // =========================================================================
    // Frontend CSS
    // =========================================================================

    public static function renderFrontendCss(): void
    {
        self::renderCss(false);
    }

    public static function renderEditorCss(): void
    {
        $screen = function_exists('get_current_screen') ? get_current_screen() : null;
        if (!$screen || !method_exists($screen, 'is_block_editor') || !$screen->is_block_editor()) {
            return;
        }
        self::renderCss(true);
    }

    private static function renderCss(bool $editor): void
    {
        $body    = self::getConfig(self::OPTION_BODY);
        $heading = self::getConfig(self::OPTION_HEADING);

        $google_families = [];
        $face_css        = '';
        $url_hrefs       = [];

        foreach ([$body, $heading] as $cfg) {
            if ($cfg['source'] === 'google' && !empty($cfg['family']) && isset(self::GOOGLE_FONTS[$cfg['family']])) {
                $g = self::GOOGLE_FONTS[$cfg['family']];
                $google_families[$cfg['family']] = $g['family'] . ':wght@' . $g['weights'];
            } elseif ($cfg['source'] === 'upload' && !empty($cfg['family'])) {
                $face_css .= self::buildFontFaceCss($cfg['family']);
            } elseif ($cfg['source'] === 'url' && !empty($cfg['url'])) {
                $url_hrefs[$cfg['url']] = true;
            }
        }

        if (!empty($google_families)) {
            echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
            echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
            $href = 'https://fonts.googleapis.com/css2?' .
                implode('&', array_map(fn($spec) => 'family=' . $spec, array_values($google_families))) .
                '&display=swap';
            echo '<link rel="stylesheet" href="' . esc_url($href) . '">' . "\n";
        }

        foreach (array_keys($url_hrefs) as $href) {
            echo '<link rel="stylesheet" href="' . esc_url($href) . '">' . "\n";
        }

        $body_family    = self::resolveFamily($body);
        $heading_family = self::resolveFamily($heading);

        echo '<style id="ai-zippy-typography">' . "\n";
        if ($face_css) {
            echo $face_css;
        }
        echo ':root{';
        if ($body_family)    echo '--zippy-font-primary:' . $body_family . ';';
        if ($heading_family) echo '--zippy-font-heading:' . $heading_family . ';';
        echo '}';
        if ($body_family) {
            echo 'body{font-family:var(--zippy-font-primary);}';
        }
        if ($heading_family) {
            echo 'h1,h2,h3,h4,h5,h6{font-family:var(--zippy-font-heading);}';
        }
        echo "\n</style>\n";
    }

    private static function buildFontFaceCss(string $family): string
    {
        $fonts = self::getUploadedFonts();
        if (empty($fonts[$family])) {
            return '';
        }

        $mime_map = [
            'woff2' => 'woff2',
            'woff'  => 'woff',
            'ttf'   => 'truetype',
            'otf'   => 'opentype',
        ];

        $css = '';
        foreach ($fonts[$family] as $file) {
            $format = $mime_map[$file['ext']] ?? 'woff2';
            $css   .= "@font-face{font-family:" . self::cssQuote($family) . ";"
                    . "src:url('" . esc_url($file['url']) . "') format('" . $format . "');"
                    . "font-weight:" . (int) $file['weight'] . ";"
                    . "font-style:" . $file['style'] . ";"
                    . "font-display:swap;}\n";
        }
        return $css;
    }

    private static function resolveFamily(array $cfg): string
    {
        if ($cfg['source'] === 'system' || empty($cfg['family'])) {
            return '';
        }
        $fallback = "-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif";
        return self::cssQuote($cfg['family']) . ',' . $fallback;
    }

    private static function cssQuote(string $family): string
    {
        if (preg_match('/\s/', $family)) {
            return "'" . addslashes($family) . "'";
        }
        return $family;
    }
}
