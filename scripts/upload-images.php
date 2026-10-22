<?php
/**
 * Sideload images into the Media Library. Spec (docs/build/images.json):
 *   { "<unbundled file e.g. 001a2510.jpg>": { "name": "hero-banner.jpg", "alt": "Hero banner" }, ... }
 * Writes option pfx_media_map: name => attachment ID (read by ai_zippy_child_img()). Idempotent.
 */
require_once __DIR__ . '/_wp-boot.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

$spec = spec_arg();
$dir = '/tmp/mockup-images';
$map = (array) get_option('pfx_media_map', []);
$n = ['uploaded' => 0, 'skipped' => 0, 'failed' => 0];

foreach ($spec as $src => $info) {
    $name = $info['name'] ?? $src;
    $alt = $info['alt'] ?? '';
    if (!empty($map[$name]) && get_post((int) $map[$name])) {
        $n['skipped']++;
        continue;
    }
    $path = "$dir/$src";
    $tmp = wp_tempnam($name);
    if (!is_file($path) || !$tmp || !copy($path, $tmp)) {
        printf("FAILED %s (missing %s)\n", $name, $path);
        $n['failed']++;
        continue;
    }
    $id = media_handle_sideload(['name' => $name, 'tmp_name' => $tmp], 0, $alt);
    if (is_wp_error($id)) {
        @unlink($tmp);
        printf("FAILED %s (%s)\n", $name, $id->get_error_message());
        $n['failed']++;
        continue;
    }
    update_post_meta($id, '_wp_attachment_image_alt', $alt);
    $map[$name] = (int) $id;
    $n['uploaded']++;
    printf("uploaded %s ID=%d\n", $name, $id);
}
update_option('pfx_media_map', $map);
printf("uploaded=%d skipped=%d failed=%d map=%d\n", $n['uploaded'], $n['skipped'], $n['failed'], count($map));
exit($n['failed'] ? 1 : 0);
