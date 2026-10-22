<?php

/**
 * Shop by Category — frontend render.
 *
 * Markup quoted from .mockup/slim.html:133-154; copy, order, images and tints
 * from docs/build/blocks/home-categories.md. Walks the six home categories in
 * mockup order and prefers the live `product_cat` term when one exists (terms
 * with products only): the Woo term image (`thumbnail_id`) overrides the
 * mapped product image and term meta `sm_tint` overrides the mockup tint.
 * Terms outside the list (e.g. "Instant & Pantry") never render on the home
 * page; a missing term falls back to the static mockup entry. Tiles link to
 * `/shop/?cat=<slug>` — the query arg consumed by the shop-catalog block.
 *
 * This file ships structural markup only; colours, spacing and typography live
 * in src/scss/sections/_home-categories.scss. The per-tile tint and image are
 * data, emitted inline as --sm-cat-tint / background-image.
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Inner blocks (unused).
 * @var WP_Block $block      Block instance.
 */

defined('ABSPATH') || exit;

// The six home categories, in mockup order (logic.js CATS with home:true).
$sm_defaults = [
	[ 'name' => 'Snacks',               'slug' => 'snacks',               'image' => 'product-chips.jpg',    'tint' => '#FF9900' ],
	[ 'name' => 'Chilled Drinks',       'slug' => 'chilled-drinks',       'image' => 'product-cola.jpg',     'tint' => '#39FF14' ],
	[ 'name' => 'Alcoholic Beverages',  'slug' => 'alcoholic-beverages',  'image' => 'product-beer.jpg',     'tint' => '#FF1A1A' ],
	[ 'name' => 'Frozen Foods',         'slug' => 'frozen-foods',         'image' => 'product-icecream.jpg', 'tint' => '#39FF14' ],
	[ 'name' => 'Household Essentials', 'slug' => 'household-essentials', 'image' => 'product-cleaner.jpg',  'tint' => '#FF9900' ],
	[ 'name' => 'Bread & Bakery',       'slug' => 'bread-bakery',         'image' => 'product-bread.jpg',    'tint' => '#FF1A1A' ],
];

$sm_shop_url = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/shop/');

$sm_heading = isset($attributes['heading']) ? trim((string) $attributes['heading']) : '';
if ('' === $sm_heading) {
	$sm_heading = 'Shop by category';
}

$sm_view_all_label = isset($attributes['viewAllLabel']) ? trim((string) $attributes['viewAllLabel']) : '';
if ('' === $sm_view_all_label) {
	$sm_view_all_label = 'View all products →';
}

$sm_view_all_url = isset($attributes['viewAllUrl']) ? trim((string) $attributes['viewAllUrl']) : '';
if ('' === $sm_view_all_url) {
	$sm_view_all_url = $sm_shop_url;
}

// Index live product_cat terms that have products, by slug and lower-case name.
$sm_terms = [];
if (taxonomy_exists('product_cat')) {
	$sm_found = get_terms(
		[
			'taxonomy'   => 'product_cat',
			'hide_empty' => true,
		]
	);
	if (! is_wp_error($sm_found)) {
		foreach ($sm_found as $sm_term) {
			$sm_terms[ $sm_term->slug ]                      = $sm_term;
			$sm_terms[ 'name:' . strtolower($sm_term->name) ] = $sm_term;
		}
	}
}

$sm_items = [];
foreach ($sm_defaults as $sm_default) {
	$sm_term = $sm_terms[ $sm_default['slug'] ] ?? $sm_terms[ 'name:' . strtolower($sm_default['name']) ] ?? null;

	$sm_item = [
		'name'  => $sm_default['name'],
		'slug'  => $sm_default['slug'],
		'tint'  => $sm_default['tint'],
		'image' => function_exists('ai_zippy_child_img') ? ai_zippy_child_img($sm_default['image']) : '',
	];

	if ($sm_term instanceof WP_Term) {
		$sm_item['name'] = $sm_term->name;
		$sm_item['slug'] = $sm_term->slug;

		$sm_tint = (string) get_term_meta($sm_term->term_id, 'sm_tint', true);
		if (1 === preg_match('/^#(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $sm_tint)) {
			$sm_item['tint'] = $sm_tint;
		}

		$sm_thumb_id = (int) get_term_meta($sm_term->term_id, 'thumbnail_id', true);
		if ($sm_thumb_id > 0) {
			$sm_thumb_url = wp_get_attachment_image_url($sm_thumb_id, 'large');
			if ($sm_thumb_url) {
				$sm_item['image'] = $sm_thumb_url;
			}
		}
	}

	$sm_item['url'] = add_query_arg('cat', $sm_item['slug'], $sm_shop_url);
	$sm_items[]     = $sm_item;
}
?>
<div <?php echo get_block_wrapper_attributes(['class' => 'sm-home-categories']); ?>>
	<div class="sm-home-categories__inner">

		<div class="sm-home-categories__head">
			<h2 class="sm-home-categories__title"><?php echo esc_html($sm_heading); ?></h2>
			<a class="sm-home-categories__all" href="<?php echo esc_url($sm_view_all_url); ?>"><?php echo esc_html($sm_view_all_label); ?></a>
		</div>

		<div class="sm-home-categories__grid">
			<?php foreach ($sm_items as $sm_item) : ?>
				<a class="sm-home-categories__tile" href="<?php echo esc_url($sm_item['url']); ?>">
					<span class="sm-home-categories__disc">
						<span class="sm-home-categories__blob" aria-hidden="true" style="--sm-cat-tint:<?php echo esc_attr($sm_item['tint']); ?>"></span>
						<span
							class="sm-home-categories__img"
							role="img"
							aria-label="<?php echo esc_attr($sm_item['name']); ?>"
							<?php if ('' !== $sm_item['image']) : ?>
								style="background-image:url('<?php echo esc_url($sm_item['image']); ?>')"
							<?php endif; ?>
						></span>
					</span>
					<span class="sm-home-categories__name"><?php echo esc_html($sm_item['name']); ?></span>
				</a>
			<?php endforeach; ?>
		</div>

	</div>
</div>
