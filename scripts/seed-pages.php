<?php
/**
 * Create/update pages. Spec (docs/build/pages.json):
 *   { "front": "home", "pages": [ { "title": "Home", "slug": "home", "template": "page-home",
 *       "blocks": [ { "name": "home-hero", "attrs": {} } ] } ] }
 * `blocks` become self-closing block comments <!-- wp:ai-zippy/{name} {attrs} /-->. Idempotent by slug.
 */
require_once __DIR__ . '/_wp-boot.php';
$spec = spec_arg();

foreach ($spec['pages'] as $p) {
    $content = '';
    foreach ($p['blocks'] ?? [] as $b) {
        $attrs = !empty($b['attrs']) ? ' ' . wp_json_encode($b['attrs'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : '';
        $content .= "<!-- wp:ai-zippy/{$b['name']}{$attrs} /-->\n\n";
    }
    $data = ['post_title' => $p['title'], 'post_name' => $p['slug'], 'post_content' => trim($content),
        'post_status' => 'publish', 'post_type' => 'page'];
    $ex = get_page_by_path($p['slug'], OBJECT, 'page');
    if ($ex) {
        $data['ID'] = $ex->ID;
        $id = wp_update_post($data, true);
    } else {
        $id = wp_insert_post($data, true);
    }
    if (is_wp_error($id) || !$id) {
        echo "FAILED {$p['slug']}\n";
        exit(1);
    }
    if (!empty($p['template'])) {
        update_post_meta($id, '_wp_page_template', $p['template']);
    }
    printf("%s ID=%d template=%s blocks=%d\n", $p['slug'], $id, $p['template'] ?? '-', count($p['blocks'] ?? []));
}
if (!empty($spec['front']) && ($h = get_page_by_path($spec['front'], OBJECT, 'page'))) {
    update_option('show_on_front', 'page');
    update_option('page_on_front', $h->ID);
    echo "front page -> {$h->ID}\n";
}
global $wp_rewrite;
$wp_rewrite->set_permalink_structure('/%postname%/');
$wp_rewrite->flush_rules();
echo "pages seeded\n";
