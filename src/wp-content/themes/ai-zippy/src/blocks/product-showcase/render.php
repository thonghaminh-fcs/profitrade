<?php

/**
 * Server-side render for Product Showcase block.
 *
 * Card markup lives in \AiZippy\Product\Cards so it can be reused by other
 * components (related products on single product page, etc).
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block inner content.
 * @var WP_Block $block      Block instance.
 */

defined('ABSPATH') || exit;

$heading        = $attributes['heading'] ?? '';
$columns        = (int) ($attributes['columns'] ?? 4);
$rows           = (int) ($attributes['rows'] ?? 2);
$category       = sanitize_text_field($attributes['category'] ?? '');
$brand          = sanitize_text_field($attributes['brand'] ?? '');
$display_style  = $attributes['displayStyle'] ?? 'grid';
$orderby        = $attributes['orderby'] ?? 'date';
$show_sale      = $attributes['showSaleBadge'] ?? true;
$show_rating    = $attributes['showRating'] ?? true;
$show_cart      = $attributes['showAddToCart'] ?? true;
$autoplay       = $attributes['autoplay'] ?? false;
$autoplay_delay = (int) ($attributes['autoplayDelay'] ?? 5000);
$total_items    = $columns * $rows;

// Query products
$args = [
    'status'  => 'publish',
    'limit'   => $total_items,
    'orderby' => $orderby,
    'order'   => $orderby === 'price' ? 'ASC' : 'DESC',
];

if (!empty($category)) {
    $args['category'] = [$category];
}

if (!empty($brand)) {
    $args['tax_query'] = [
        [
            'taxonomy' => 'pa_brand',
            'field'    => 'slug',
            'terms'    => [$brand],
        ],
    ];
}

$products = wc_get_products($args);

if (empty($products)) {
    return;
}

$wrapper_attributes = get_block_wrapper_attributes([
    'class' => 'ps ps--' . $display_style,
]);

// Slider data attributes
$slider_data = '';
if ($display_style === 'slider') {
    $slider_config = wp_json_encode([
        'columns'       => $columns,
        'autoplay'      => $autoplay,
        'autoplayDelay' => $autoplay_delay,
        'rows'          => $rows,
    ]);
    $slider_data = ' data-swiper-config="' . esc_attr($slider_config) . '"';
}

$card_opts = [
    'show_sale'   => (bool) $show_sale,
    'show_rating' => (bool) $show_rating,
    'show_cart'   => (bool) $show_cart,
];
?>

<div <?php echo $wrapper_attributes; ?><?php echo $slider_data; ?>>

    <?php if (!empty($heading)) : ?>
        <div class="ps__header">
            <h2 class="ps__heading"><?php echo esc_html($heading); ?></h2>
            <?php if ($display_style === 'slider') : ?>
                <div class="ps__nav">
                    <button class="ps__nav-btn ps__nav-prev" aria-label="Previous">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
                    </button>
                    <button class="ps__nav-btn ps__nav-next" aria-label="Next">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 6 15 12 9 18"/></svg>
                    </button>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if ($display_style === 'slider') : ?>
        <div class="swiper ps__swiper">
            <div class="swiper-wrapper">
                <?php foreach ($products as $product) : ?>
                    <div class="swiper-slide">
                        <?php echo \AiZippy\Product\Cards::render($product, 'full', $card_opts); ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="swiper-pagination ps__pagination"></div>
        </div>
    <?php else : ?>
        <div class="ps__grid" style="grid-template-columns: repeat(<?php echo (int) $columns; ?>, 1fr);">
            <?php foreach ($products as $product) : ?>
                <?php echo \AiZippy\Product\Cards::render($product, 'full', $card_opts); ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>
