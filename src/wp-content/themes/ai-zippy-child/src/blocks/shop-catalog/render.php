<?php

/**
 * Shop Catalog — frontend render.
 *
 * Sidebar (category list with counts + Sort by), product grid and quickview
 * modal; markup quoted from .mockup/slim.html:213-293 (layout), 470-531
 * (quickview modal), 795-800 (toast). render.php queries the Woo catalog,
 * applies the initial ?q= / ?cat= state server-side (so the first paint and
 * the editor preview match the URL) and passes the full catalog to view.js as
 * a JSON script tag for client-side filtering, sorting, quickview and Store
 * API cart adds (logic.js:80-94, 106-113, 133-136).
 *
 * This file ships structural markup only; colours, spacing and typography
 * live in src/scss/sections/_shop-catalog.scss plus the shared components in
 * src/scss/_components.scss (.sm-badge, .sm-btn, .sm-toast, .sm-blob--*).
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Inner blocks (unused).
 * @var WP_Block $block      Block instance.
 */

defined('ABSPATH') || exit;

if (!function_exists('sm_shop_catalog_seed')) :
	/**
	 * SKU-keyed catalog enrichment copied from docs/build/products.json:
	 * badge, variant title (vt), variants [label, price] (v), popularity (pop)
	 * and the mockup image name (img, fallback when the product has no Woo
	 * image). scripts/seed-products.php stores the base fields in Woo only —
	 * multi-variant products are seeded as simple at the first variant's
	 * price — so the display-only variant/badge/popularity data lives here.
	 */
	function sm_shop_catalog_seed(): array
	{
		return [
			'SM-001' => ['badge' => '1-for-1', 'vt' => 'Pack size', 'v' => [['70g', 2.5], ['150g', 4.2]], 'pop' => 1, 'img' => 'product-chips.jpg'],
			'SM-002' => ['vt' => 'Size', 'v' => [['200g', 3.8]], 'pop' => 6, 'img' => 'product-cookies.jpg'],
			'SM-003' => ['badge' => 'Chilled', 'vt' => 'Pack', 'v' => [['6 × 320ml', 5.9], ['12 × 320ml', 10.9], ['24 × 320ml carton', 19.9]], 'pop' => 2, 'img' => 'product-cola.jpg'],
			'SM-004' => ['vt' => 'Size', 'v' => [['1L', 4.5]], 'pop' => 5, 'img' => 'product-orange-juice.jpg'],
			'SM-005' => ['vt' => 'Pack', 'v' => [['1.5L bottle', 1.2], ['6 × 1.5L', 6.5]], 'pop' => 8, 'img' => 'product-mineral-water.jpg'],
			'SM-006' => ['vt' => 'Pack', 'v' => [['6 × 330ml', 18.9], ['24 × 330ml carton', 64]], 'pop' => 4, 'img' => 'product-beer.jpg'],
			'SM-007' => ['vt' => 'Size', 'v' => [['750ml', 28]], 'pop' => 11, 'img' => 'product-wine.jpg'],
			'SM-008' => ['badge' => 'Flash Sale', 'vt' => 'Flavour', 'v' => [['Vanilla', 8.9], ['Chocolate', 8.9], ['Strawberry', 8.9]], 'pop' => 3, 'img' => 'product-icecream.jpg'],
			'SM-009' => ['vt' => 'Size', 'v' => [['12 inch', 9.5]], 'pop' => 9, 'img' => 'product-pizza.jpg'],
			'SM-010' => ['vt' => 'Pack', 'v' => [['20 pcs', 7.2], ['50 pcs', 15.9]], 'pop' => 10, 'img' => 'product-dumplings.jpg'],
			'SM-011' => ['vt' => 'Pack', 'v' => [['Single', 1.1], ['5-pack', 4.9]], 'pop' => 7, 'img' => 'product-noodles.jpg'],
			'SM-012' => ['vt' => 'Size', 'v' => [['500ml', 5.2]], 'pop' => 12, 'img' => 'product-cleaner.jpg'],
			'SM-013' => ['badge' => 'Daily Discount', 'vt' => 'Size', 'v' => [['600g', 2.8]], 'pop' => 13, 'img' => 'product-bread.jpg'],
			'SM-014' => ['vt' => 'Pack', 'v' => [['4 pcs', 5.5], ['8 pcs', 9.9]], 'pop' => 14, 'img' => 'product-croissants.jpg'],
		];
	}
endif;

if (!function_exists('sm_shop_catalog_tint')) :
	/**
	 * Category tint for the quickview blob (logic.js:11-19 / ui-spec §Palette).
	 */
	function sm_shop_catalog_tint(string $cat_name): string
	{
		$map = [
			'Snacks'               => '#FF9900',
			'Chilled Drinks'       => '#39FF14',
			'Alcoholic Beverages'  => '#FF1A1A',
			'Frozen Foods'         => '#39FF14',
			'Household Essentials' => '#FF9900',
			'Bread & Bakery'       => '#FF1A1A',
			'Instant & Pantry'     => '#FF9900',
		];
		return $map[$cat_name] ?? '#F3F4F6';
	}
endif;

if (!function_exists('sm_shop_catalog_money')) :
	/**
	 * Money format `$X.XX` (logic.js:44).
	 */
	function sm_shop_catalog_money(float $n): string
	{
		return '$' . number_format($n, 2, '.', '');
	}
endif;

if (!function_exists('sm_shop_catalog_specs')) :
	/**
	 * Parse the seeded product description ("Key: Value" per line) into the
	 * quickview specs grid rows.
	 */
	function sm_shop_catalog_specs(string $description): array
	{
		$specs = [];
		foreach (preg_split('/\r\n|\r|\n/', $description) ?: [] as $line) {
			$line = trim($line);
			if ('' === $line || false === strpos($line, ':')) {
				continue;
			}
			list($k, $v) = explode(':', $line, 2);
			$k = trim($k);
			$v = trim($v);
			if ('' !== $k && '' !== $v) {
				$specs[] = [$k, $v];
			}
		}
		return $specs;
	}
endif;

if (!function_exists('sm_shop_catalog_variants')) :
	/**
	 * Variant list for one product: real variations for variable products,
	 * otherwise the seeded variants, otherwise a single entry at the product
	 * price. Title mirrors logic.js:109 — the attribute/seed title when more
	 * than one variant, "Size" when single.
	 *
	 * @return array{title:string,items:array<int,array{label:string,price:float,variation:array}>}
	 */
	function sm_shop_catalog_variants(WC_Product $product, array $seed): array
	{
		if ($product->is_type('variable')) {
			$title = '';
			foreach ($product->get_attributes() as $attribute) {
				if ($attribute instanceof WC_Product_Attribute && $attribute->get_visible()) {
					$title = wc_attribute_label($attribute->get_name());
					break;
				}
			}
			$items = [];
			foreach ($product->get_children() as $child_id) {
				$child = wc_get_product($child_id);
				if (!$child instanceof WC_Product_Variation || !$child->variation_is_visible()) {
					continue;
				}
				$pairs  = [];
				$labels = [];
				foreach ($child->get_attributes() as $taxonomy => $value) {
					$term    = get_term_by('slug', $value, $taxonomy);
					$pairs[] = ['attribute' => $taxonomy, 'value' => $value];
					$labels[] = $term && !is_wp_error($term) ? $term->name : $value;
				}
				$items[] = [
					'label'     => implode(' / ', $labels),
					'price'     => (float) $child->get_price(),
					'variation' => $pairs,
				];
			}
			if (!empty($items)) {
				return ['title' => '' !== $title ? $title : 'Option', 'items' => $items];
			}
		}

		if (!empty($seed['v']) && is_array($seed['v'])) {
			$items = [];
			foreach ($seed['v'] as $pair) {
				$items[] = [
					'label'     => isset($pair[0]) ? (string) $pair[0] : '',
					'price'     => isset($pair[1]) ? (float) $pair[1] : (float) $product->get_price(),
					'variation' => [],
				];
			}
			return ['title' => !empty($seed['vt']) ? (string) $seed['vt'] : 'Option', 'items' => $items];
		}

		return [
			'title' => 'Size',
			'items' => [['label' => '', 'price' => (float) $product->get_price(), 'variation' => []]],
		];
	}
endif;

if (!function_exists('sm_shop_catalog_image_url')) :
	/**
	 * Product image: Woo attachment first, then the seeded mockup file name
	 * through ai_zippy_child_img(), then the shared fallback.
	 */
	function sm_shop_catalog_image_url(WC_Product $product, array $seed): string
	{
		$image_id = $product->get_image_id();
		if ($image_id) {
			$url = wp_get_attachment_image_url((int) $image_id, 'large');
			if ($url) {
				return $url;
			}
		}
		$name = !empty($seed['img']) ? (string) $seed['img'] : 'product-fallback.jpg';
		return function_exists('ai_zippy_child_img') ? ai_zippy_child_img($name) : '';
	}
endif;

$per_page = isset($attributes['perPage']) ? (int) $attributes['perPage'] : 14;
$columns  = isset($attributes['columns']) ? (int) $attributes['columns'] : 0;
$seed_map = sm_shop_catalog_seed();

// ---- Initial state: ?q= / ?cat= URL args, product_cat archives seed cat ----
$q = isset($_GET['q']) ? trim(sanitize_text_field(wp_unslash($_GET['q']))) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only display filter.

$cat = 'all';
if (isset($_GET['cat']) && '' !== $_GET['cat']) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only display filter.
	$raw_cat = sanitize_text_field(wp_unslash($_GET['cat'])); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if (is_numeric($raw_cat)) {
		$cat = (int) $raw_cat;
	} else {
		$slug_term = get_term_by('slug', $raw_cat, 'product_cat');
		$cat       = $slug_term instanceof WP_Term ? (int) $slug_term->term_id : 'all';
	}
}
if (function_exists('is_tax') && is_tax('product_cat')) {
	$queried = get_queried_object();
	if ($queried instanceof WP_Term) {
		$cat = (int) $queried->term_id;
	}
}

// ---- Catalog: published Woo products enriched with the seed map ----
$catalog = [];
if (function_exists('wc_get_products')) {
	$products = wc_get_products([
		'status'  => 'publish',
		'limit'   => $per_page > 0 ? $per_page : -1,
		'orderby' => 'title',
		'order'   => 'ASC',
	]);

	foreach ($products as $product) {
		if (!$product instanceof WC_Product) {
			continue;
		}
		$sku  = $product->get_sku();
		$seed = isset($seed_map[$sku]) && is_array($seed_map[$sku]) ? $seed_map[$sku] : [];

		$cat_id   = 0;
		$cat_name = '';
		$terms    = wp_get_post_terms($product->get_id(), 'product_cat');
		if (!is_wp_error($terms) && isset($terms[0]) && $terms[0] instanceof WP_Term) {
			$cat_id   = (int) $terms[0]->term_id;
			$cat_name = $terms[0]->name;
		}

		$variants = sm_shop_catalog_variants($product, $seed);
		$items    = $variants['items'];
		$multi    = count($items) > 1;
		$base     = $items[0]['price'];

		$badge = !empty($seed['badge']) ? (string) $seed['badge'] : '';
		$was   = $product->is_on_sale() ? (float) $product->get_regular_price() : 0.0;

		$catalog[] = [
			'id'           => (int) $product->get_id(),
			'name'         => $product->get_name(),
			'catId'        => $cat_id,
			'catName'      => $cat_name,
			'img'          => sm_shop_catalog_image_url($product, $seed),
			'tint'         => sm_shop_catalog_tint($cat_name),
			'badge'        => $badge,
			'badgeClass'   => '' !== $badge ? 'sm-badge--' . sanitize_title($badge) : '',
			'unit'         => $multi ? sprintf('%d options', count($items)) : $items[0]['label'],
			'priceLabel'   => ($multi ? 'from ' : '') . sm_shop_catalog_money($base),
			'wasLabel'     => $was > 0 ? sm_shop_catalog_money($was) : '',
			'basePrice'    => $base,
			'pop'          => !empty($seed['pop']) ? (int) $seed['pop'] : (int) $product->get_total_sales(),
			'stock'        => $product->get_stock_quantity(),
			'desc'         => wp_strip_all_tags($product->get_short_description()),
			'specs'        => sm_shop_catalog_specs(wp_strip_all_tags($product->get_description())),
			'variantTitle' => $multi ? $variants['title'] : 'Size',
			'variants'     => $items,
			'type'         => $product->is_type('variable') ? 'variable' : 'simple',
		];
	}
}

// ---- Initial filter + sort (mirrors view.js so first paint matches) ----
$q_lower = '' !== $q ? function_exists('mb_strtolower') ? mb_strtolower($q) : strtolower($q) : '';
$list    = [];
foreach ($catalog as $entry) {
	if ('all' !== $cat && (int) $entry['catId'] !== (int) $cat) {
		continue;
	}
	if ('' !== $q_lower) {
		$haystack = function_exists('mb_strtolower') ? mb_strtolower($entry['name'] . ' ' . $entry['catName']) : strtolower($entry['name'] . ' ' . $entry['catName']);
		if (false === strpos($haystack, $q_lower)) {
			continue;
		}
	}
	$list[] = $entry;
}
usort($list, static function (array $a, array $b): int {
	return $a['pop'] <=> $b['pop']; // default sort: Most popular (logic.js:787).
});

// ---- Sidebar categories: All products + every non-empty product_cat ----
$cat_terms = get_terms([
	'taxonomy'   => 'product_cat',
	'hide_empty' => true,
	'orderby'    => 'term_id', // seed order = products.json order.
	'order'      => 'ASC',
]);
$cat_terms = is_wp_error($cat_terms) ? [] : (array) $cat_terms;

$total_products = (int) wp_count_posts('product')->publish;

$active_cat_name = '';
if ('all' !== $cat) {
	$active_term = get_term((int) $cat, 'product_cat');
	if ($active_term instanceof WP_Term) {
		$active_cat_name = $active_term->name;
	}
}
$heading = '' !== $active_cat_name ? $active_cat_name : 'All products';

$result_label = sprintf('%d item%s', count($list), 1 === count($list) ? '' : 's');
if ('' !== $q) {
	$result_label .= sprintf(' for "%s"', $q);
}

$fallback_img = function_exists('ai_zippy_child_img') ? ai_zippy_child_img('product-fallback.jpg') : '';

$grid_style = $columns > 0 ? 'grid-template-columns:repeat(' . $columns . ',minmax(0,1fr))' : '';

$payload = [
	'restUrl'  => esc_url_raw(rest_url('wc/store/v1/')),
	'state'    => ['q' => $q, 'cat' => $cat],
	'products' => $catalog,
];
?>
<div <?php echo get_block_wrapper_attributes(['class' => 'sm-shop']); ?>>
	<section class="sm-shop__section">
		<aside class="sm-shop__sidebar">
			<div class="sm-shop__card-panel">
				<div class="sm-shop__eyebrow sm-shop__eyebrow--pad">Categories</div>
				<div class="sm-shop__cats">
					<button type="button" class="sm-shop__cat<?php echo 'all' === $cat ? ' is-active' : ''; ?>" data-sm-cat="all" data-sm-name="All products">
						<span>All products</span><span class="sm-shop__cat-count"><?php echo esc_html((string) $total_products); ?></span>
					</button>
					<?php foreach ($cat_terms as $term) :
						if (!$term instanceof WP_Term) {
							continue;
						}
					?>
					<button type="button" class="sm-shop__cat<?php echo 'all' !== $cat && (int) $cat === (int) $term->term_id ? ' is-active' : ''; ?>" data-sm-cat="<?php echo esc_attr((string) $term->term_id); ?>" data-sm-name="<?php echo esc_attr($term->name); ?>">
						<span><?php echo esc_html($term->name); ?></span><span class="sm-shop__cat-count"><?php echo esc_html((string) (int) $term->count); ?></span>
					</button>
					<?php endforeach; ?>
				</div>
			</div>
			<div class="sm-shop__card-panel">
				<label class="sm-shop__sort-wrap">
					<span class="sm-shop__eyebrow sm-shop__eyebrow--pad">Sort by</span>
					<select class="sm-shop__sort" data-sm-sort>
						<option value="popular">Most popular</option>
						<option value="low">Price: low to high</option>
						<option value="high">Price: high to low</option>
					</select>
				</label>
			</div>
		</aside>

		<div class="sm-shop__main">
			<div class="sm-shop__head">
				<h1 class="sm-shop__title" data-sm-title><?php echo esc_html($heading); ?></h1>
				<span class="sm-shop__count" data-sm-count><?php echo esc_html($result_label); ?></span>
			</div>

			<div class="sm-shop__empty" data-sm-empty<?php echo empty($list) ? '' : ' hidden'; ?>>
				<span class="sm-shop__empty-text" data-sm-empty-text>No products match "<?php echo esc_html($q); ?>"</span>
				<button type="button" class="sm-btn sm-btn--dark" data-sm-clear>Clear filters</button>
			</div>

			<div class="sm-shop__grid" data-sm-grid<?php echo '' !== $grid_style ? ' style="' . esc_attr($grid_style) . '"' : ''; ?>>
				<?php foreach ($list as $entry) : ?>
				<article class="sm-shop__product" data-sm-card data-sm-id="<?php echo esc_attr((string) $entry['id']); ?>">
					<div class="sm-shop__product-media">
						<img class="sm-shop__product-img" src="<?php echo esc_url($entry['img']); ?>" alt="<?php echo esc_attr($entry['name']); ?>" loading="lazy" decoding="async">
						<?php if ('' !== $entry['badge']) : ?>
						<span class="sm-badge <?php echo esc_attr($entry['badgeClass']); ?>"><?php echo esc_html($entry['badge']); ?></span>
						<?php endif; ?>
					</div>
					<div class="sm-shop__product-body">
						<span class="sm-shop__product-cat"><?php echo esc_html($entry['catName']); ?></span>
						<button type="button" class="sm-shop__product-name"><?php echo esc_html($entry['name']); ?></button>
						<span class="sm-shop__product-unit"><?php echo esc_html($entry['unit']); ?></span>
						<div class="sm-shop__product-price-row">
							<span class="sm-shop__product-prices">
								<span class="sm-price"><?php echo esc_html($entry['priceLabel']); ?></span>
								<?php if ('' !== $entry['wasLabel']) : ?>
								<span class="sm-price sm-price--was sm-shop__product-was"><?php echo esc_html($entry['wasLabel']); ?></span>
								<?php endif; ?>
							</span>
							<button type="button" class="sm-shop__quick-add" data-sm-quick-add aria-label="<?php echo esc_attr('Add to cart'); ?>">+</button>
						</div>
					</div>
				</article>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<div class="sm-shop__modal" data-sm-modal aria-hidden="true">
		<div class="sm-shop__modal-panel" data-sm-modal-panel role="dialog" aria-modal="true" aria-label="Product quickview">
			<button type="button" class="sm-shop__modal-close" data-sm-modal-close aria-label="Close">×</button>
			<div class="sm-shop__modal-media">
				<div class="sm-shop__modal-blob sm-blob--a" data-sm-modal-blob></div>
				<div class="sm-shop__modal-disc sm-blob--c">
					<img data-sm-modal-img src="<?php echo esc_url($fallback_img); ?>" alt="">
				</div>
			</div>
			<div class="sm-shop__modal-info">
				<span class="sm-shop__modal-cat" data-sm-modal-cat></span>
				<h2 class="sm-shop__modal-name" data-sm-modal-name></h2>
				<p class="sm-shop__modal-desc" data-sm-modal-desc></p>
				<div class="sm-shop__modal-variants">
					<span class="sm-shop__eyebrow" data-sm-modal-vtitle></span>
					<div class="sm-shop__variant-list" data-sm-modal-variants></div>
				</div>
				<div class="sm-shop__specs" data-sm-modal-specs></div>
				<div class="sm-shop__modal-actions">
					<div class="sm-shop__stepper">
						<button type="button" data-sm-modal-dec aria-label="Decrease quantity">−</button>
						<span data-sm-modal-qty>1</span>
						<button type="button" data-sm-modal-inc aria-label="Increase quantity">+</button>
					</div>
					<button type="button" class="sm-shop__add" data-sm-modal-add>
						<span>Add to Cart</span><span data-sm-modal-total></span>
					</button>
				</div>
				<span class="sm-shop__stock" data-sm-modal-stock></span>
			</div>
		</div>
	</div>

	<div class="sm-toast" data-sm-toast role="status" aria-live="polite">
		<span class="sm-toast__dot"></span><span data-sm-toast-text></span>
	</div>

	<script type="application/json" data-sm-catalog><?php echo wp_json_encode($payload, JSON_HEX_TAG | JSON_UNESCAPED_UNICODE); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON for view.js, tags escaped via JSON_HEX_TAG. ?></script>
</div>
