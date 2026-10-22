<?php
/**
 * Create WooCommerce products. Spec (docs/build/products.json):
 *   [ { "sku": "SM-001", "name": "...", "price": "3.50", "regular_price": "4.00"?, "category": "Snacks",
 *       "image": "<media-map name>", "short_description": "", "description": "", "stock": 100 } ]
 * Idempotent by SKU. Run AFTER upload-images.php.
 */
require_once __DIR__ . '/_wp-boot.php';
if (!class_exists('WC_Product_Simple')) {
    fwrite(STDERR, "FAIL: WooCommerce inactive\n");
    exit(2);
}
$map = (array) get_option('pfx_media_map', []);
$c = ['created' => 0, 'updated' => 0];
foreach (spec_arg() as $p) {
    $id = wc_get_product_id_by_sku($p['sku']);
    $prod = $id ? wc_get_product($id) : new WC_Product_Simple();
    $prod->set_name($p['name']);
    $prod->set_sku($p['sku']);
    $prod->set_status('publish');
    $prod->set_regular_price($p['regular_price'] ?? $p['price']);
    if (!empty($p['regular_price']) && $p['regular_price'] !== $p['price']) {
        $prod->set_sale_price($p['price']);
    }
    $prod->set_short_description($p['short_description'] ?? '');
    $prod->set_description($p['description'] ?? '');
    $prod->set_manage_stock(true);
    $prod->set_stock_quantity((int) ($p['stock'] ?? 100));
    if (!empty($p['image']) && !empty($map[$p['image']])) {
        $prod->set_image_id((int) $map[$p['image']]);
    }
    if (!empty($p['category'])) {
        $t = term_exists($p['category'], 'product_cat') ?: wp_insert_term($p['category'], 'product_cat');
        if (!is_wp_error($t)) {
            $prod->set_category_ids([(int) (is_array($t) ? $t['term_id'] : $t)]);
        }
    }
    $prod->save();
    $c[$id ? 'updated' : 'created']++;
}
printf("products created=%d updated=%d total=%d\n", $c['created'], $c['updated'], wp_count_posts('product')->publish);
