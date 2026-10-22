<?php

namespace AiZippy\Api;

use AiZippy\Admin\Typography;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

defined('ABSPATH') || exit;

/**
 * REST endpoints backing the Typography admin app.
 *
 *   GET    /ai-zippy/v1/typography              — current config + uploads + Google list
 *   POST   /ai-zippy/v1/typography              — save body + heading configs
 *   POST   /ai-zippy/v1/typography/upload       — multipart file upload
 *   DELETE /ai-zippy/v1/typography/fonts/{family}/{filename}
 */
class TypographyApi
{
    public const NAMESPACE = 'ai-zippy/v1';

    public static function register(): void
    {
        register_rest_route(self::NAMESPACE, '/typography', [
            [
                'methods'             => 'GET',
                'callback'            => [self::class, 'getSettings'],
                'permission_callback' => [self::class, 'canManage'],
            ],
            [
                'methods'             => 'POST',
                'callback'            => [self::class, 'saveSettings'],
                'permission_callback' => [self::class, 'canManage'],
            ],
        ]);

        register_rest_route(self::NAMESPACE, '/typography/upload', [
            'methods'             => 'POST',
            'callback'            => [self::class, 'uploadFont'],
            'permission_callback' => [self::class, 'canManage'],
        ]);

        register_rest_route(self::NAMESPACE, '/typography/fonts/(?P<family>[^/]+)/(?P<filename>[^/]+)', [
            'methods'             => 'DELETE',
            'callback'            => [self::class, 'deleteFont'],
            'permission_callback' => [self::class, 'canManage'],
            'args'                => [
                'family'   => ['type' => 'string'],
                'filename' => ['type' => 'string'],
            ],
        ]);
    }

    public static function canManage(): bool
    {
        return current_user_can('manage_options');
    }

    /**
     * GET /typography — returns everything the admin app needs to render.
     */
    public static function getSettings(): WP_REST_Response
    {
        $google = [];
        foreach (Typography::GOOGLE_FONTS as $label => $cfg) {
            $google[] = ['label' => $label, 'value' => $label];
        }

        return rest_ensure_response([
            'body'        => Typography::getConfig(Typography::OPTION_BODY),
            'heading'     => Typography::getConfig(Typography::OPTION_HEADING),
            'uploads'     => self::formatUploadsForClient(),
            'googleFonts' => $google,
        ]);
    }

    /**
     * POST /typography — persist body + heading configs.
     */
    public static function saveSettings(WP_REST_Request $request)
    {
        $params  = $request->get_json_params() ?: [];
        $body    = Typography::sanitizeFontConfig($params['body']    ?? []);
        $heading = Typography::sanitizeFontConfig($params['heading'] ?? []);

        update_option(Typography::OPTION_BODY,    $body);
        update_option(Typography::OPTION_HEADING, $heading);

        return rest_ensure_response([
            'body'        => $body,
            'heading'     => $heading,
            'uploads'     => self::formatUploadsForClient(),
            'googleFonts' => array_map(
                fn($label) => ['label' => $label, 'value' => $label],
                array_keys(Typography::GOOGLE_FONTS)
            ),
        ]);
    }

    /**
     * POST /typography/upload — multipart: fields { family } + file { font_file }.
     */
    public static function uploadFont(WP_REST_Request $request)
    {
        $family = sanitize_text_field($request->get_param('family') ?? '');
        $file   = $request->get_file_params()['font_file'] ?? null;

        if (!$file) {
            return new WP_Error('no_file', __('No file provided.', 'ai-zippy'), ['status' => 400]);
        }

        try {
            Typography::saveUploadedFont($family, $file);
        } catch (\RuntimeException $e) {
            return new WP_Error('upload_failed', $e->getMessage(), ['status' => 400]);
        }

        return rest_ensure_response([
            'uploads' => self::formatUploadsForClient(),
        ]);
    }

    /**
     * DELETE /typography/fonts/{family}/{filename}
     */
    public static function deleteFont(WP_REST_Request $request)
    {
        $family   = rawurldecode($request->get_param('family'));
        $filename = rawurldecode($request->get_param('filename'));

        if (!Typography::deleteUploadedFont($family, $filename)) {
            return new WP_Error('delete_failed', __('Could not delete font.', 'ai-zippy'), ['status' => 400]);
        }

        return rest_ensure_response([
            'uploads' => self::formatUploadsForClient(),
        ]);
    }

    /**
     * Shape uploads for the client: [{ family, files: [{ filename, url, weight, style, ext }] }]
     */
    private static function formatUploadsForClient(): array
    {
        $result = [];
        foreach (Typography::getUploadedFonts() as $family => $files) {
            $result[] = [
                'family' => $family,
                'files'  => $files,
            ];
        }
        return $result;
    }
}
