<?php

/**
 * Mini Cart (S.Mart drawer) — frontend render.
 *
 * Child adaptation of the parent `ai-zippy/mini-cart` block (docs/build/blocks/
 * mini-cart.md; markup quoted from .mockup/slim.html:51-64 and 532-624). Prints
 * the full initial cart state server-side so the drawer is readable with
 * JavaScript disabled; view.js keeps it in sync afterwards through the Woo
 * Store API and the parent's `ai-zippy-cart-updated` runtime event.
 *
 * Drawer open/close is owned by src/js/child.js through the shared hooks
 * (.sm-drawer, .sm-drawer-overlay, [data-sm-drawer-open], [data-sm-drawer-close]);
 * the delivery picker reads/writes the shared {slot, express} state that
 * child.js persists in sessionStorage under `sm-delivery` — the same state
 * delivery-bar edits and checkout-fulfilment sends to the order.
 *
 * This file ships structural markup only; colours, spacing and typography live
 * in src/scss/sections/_mini-cart.scss.
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Inner blocks (unused).
 * @var WP_Block $block      Block instance.
 */

defined('ABSPATH') || exit;

// The five 3-hour slots, in order — must match delivery-bar/render.php and
// the SLOTS constant in view.js.
$sm_slots = [
	'8:00 – 11:00 AM',
	'11:00 AM – 2:00 PM',
	'2:00 – 5:00 PM',
	'5:00 – 8:00 PM',
	'8:00 – 11:00 PM',
];

$sm_fee       = isset($attributes['expressFee']) ? (float) $attributes['expressFee'] : 20;
$sm_fee_label = function_exists('wc_price')
	? wp_strip_all_tags((string) wc_price($sm_fee))
	: '$' . number_format($sm_fee, 2);

if (!function_exists('sm_mini_cart_money')) :
	/**
	 * A plain-text price string ("$12.34"), Woo-formatted when available.
	 *
	 * @param float $amount
	 */
	function sm_mini_cart_money(float $amount): string
	{
		if (function_exists('wc_price')) {
			return wp_strip_all_tags((string) wc_price($amount));
		}
		return '$' . number_format($amount, 2);
	}
endif;

$sm_cart = function_exists('WC') && WC()->cart instanceof WC_Cart ? WC()->cart : null;

$sm_count    = 0;
$sm_subtotal = 0.0;
$sm_items    = [];

if ($sm_cart) {
	$sm_count    = (int) $sm_cart->get_cart_contents_count();
	$sm_subtotal = (float) $sm_cart->get_displayed_subtotal();

	foreach ($sm_cart->get_cart() as $sm_key => $sm_line) {
		$sm_product = $sm_line['data'] ?? null;
		if (!$sm_product instanceof WC_Product) {
			continue;
		}

		$sm_qty   = (int) ($sm_line['quantity'] ?? 0);
		$sm_image = '';

		$sm_image_id = $sm_product->get_image_id();
		if ($sm_image_id) {
			$sm_image = (string) wp_get_attachment_image_url((int) $sm_image_id, 'woocommerce_thumbnail');
		}
		if ('' === $sm_image && function_exists('wc_placeholder_img_src')) {
			$sm_image = (string) wc_placeholder_img_src();
		}

		$sm_variant = function_exists('wc_get_formatted_cart_item_data')
			? wp_strip_all_tags((string) wc_get_formatted_cart_item_data($sm_line, true))
			: '';

		$sm_items[] = [
			'key'       => (string) $sm_key,
			'name'      => (string) $sm_product->get_name(),
			'permalink' => $sm_product->is_visible() ? (string) $sm_product->get_permalink() : '',
			'image'     => $sm_image,
			'quantity'  => $sm_qty,
			'variant'   => $sm_variant,
			'unit'      => sm_mini_cart_money((float) wc_get_price_to_display($sm_product)),
			'line'      => wp_strip_all_tags((string) $sm_cart->get_product_subtotal($sm_product, $sm_qty)),
		];
	}
}

$sm_has_items = $sm_count > 0 && $sm_items !== [];

$sm_shop_url     = function_exists('wc_get_page_permalink') ? (string) wc_get_page_permalink('shop') : home_url('/shop/');
$sm_checkout_url = function_exists('wc_get_checkout_url') ? (string) wc_get_checkout_url() : home_url('/checkout/');
$sm_logo         = function_exists('ai_zippy_child_img') ? ai_zippy_child_img('logo-smart.jpg') : '';
$sm_drawer_id    = wp_unique_id('sm-cart-drawer-');

// Server-rendered default of the shared delivery state (child.js re-renders
// from sessionStorage on load): slot index 2, express off.
$sm_slot_sub = 'Today, ' . $sm_slots[2];
?>
<div
	<?php echo get_block_wrapper_attributes(['class' => 'sm-mini-cart']); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by core. ?>
	data-express-fee="<?php echo esc_attr((string) $sm_fee); ?>"
	data-express-fee-label="<?php echo esc_attr($sm_fee_label); ?>"
>
	<button
		class="sm-cart-button"
		type="button"
		aria-expanded="false"
		aria-controls="<?php echo esc_attr($sm_drawer_id); ?>"
		aria-label="<?php echo esc_attr(sprintf(/* translators: %s: number of items. */ _n('%s item in cart', '%s items in cart', $sm_count, 'ai-zippy-child'), $sm_count)); ?>"
		data-sm-drawer-open
	>
		<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
			<path d="M2 3h3l2.6 12.2a1 1 0 0 0 1 .8h9.7a1 1 0 0 0 1-.8L21 7H6" />
			<circle cx="9" cy="20" r="1.4" />
			<circle cx="18" cy="20" r="1.4" />
		</svg>
		<span class="sm-cart-button__total" data-sm-cart-total><?php echo esc_html(sm_mini_cart_money($sm_subtotal)); ?></span>
		<span class="sm-cart-button__count" data-sm-cart-count><?php echo esc_html((string) $sm_count); ?></span>
	</button>

	<div class="sm-cart-drawer-overlay sm-drawer-overlay" data-sm-drawer-close aria-hidden="true"></div>

	<aside
		class="sm-cart-drawer sm-drawer"
		id="<?php echo esc_attr($sm_drawer_id); ?>"
		role="dialog"
		aria-modal="true"
		aria-label="<?php esc_attr_e('Your cart', 'ai-zippy-child'); ?>"
		aria-hidden="true"
	>
		<div class="sm-cart-drawer__head">
			<span class="sm-cart-drawer__title">
				<?php esc_html_e('Your cart', 'ai-zippy-child'); ?>
				<span class="sm-cart-drawer__title-count" data-sm-cart-count-title>(<?php echo esc_html((string) $sm_count); ?>)</span>
			</span>
			<button class="sm-cart-drawer__close" type="button" aria-label="<?php esc_attr_e('Close cart', 'ai-zippy-child'); ?>" data-sm-drawer-close>&times;</button>
		</div>

		<div class="sm-cart-drawer__empty"<?php echo $sm_has_items ? ' hidden' : ''; ?>>
			<?php if ($sm_logo) : ?>
				<img class="sm-cart-drawer__empty-logo" src="<?php echo esc_url($sm_logo); ?>" alt="" width="110" height="110" loading="lazy" />
			<?php endif; ?>
			<span class="sm-cart-drawer__empty-text"><?php esc_html_e('Your cart is empty', 'ai-zippy-child'); ?></span>
			<a class="sm-cart-drawer__empty-link" href="<?php echo esc_url($sm_shop_url); ?>"><?php esc_html_e('Browse products', 'ai-zippy-child'); ?></a>
		</div>

		<ul class="sm-cart-drawer__items"<?php echo $sm_has_items ? '' : ' hidden'; ?>>
			<?php foreach ($sm_items as $sm_item) : ?>
				<li class="sm-cart-item" data-key="<?php echo esc_attr($sm_item['key']); ?>">
					<?php if ($sm_item['image']) : ?>
						<img class="sm-cart-item__image" src="<?php echo esc_url($sm_item['image']); ?>" alt="" width="64" height="64" loading="lazy" />
					<?php endif; ?>

					<div class="sm-cart-item__body">
						<?php if ($sm_item['permalink']) : ?>
							<a class="sm-cart-item__name" href="<?php echo esc_url($sm_item['permalink']); ?>"><?php echo esc_html($sm_item['name']); ?></a>
						<?php else : ?>
							<span class="sm-cart-item__name"><?php echo esc_html($sm_item['name']); ?></span>
						<?php endif; ?>

						<span class="sm-cart-item__meta">
							<?php
							$sm_meta = array_filter([$sm_item['variant'], $sm_item['unit']], 'strlen');
							echo esc_html(implode(' · ', $sm_meta));
							?>
						</span>

						<div class="sm-cart-item__controls">
							<div class="sm-cart-item__qty">
								<button class="sm-cart-item__qty-down" type="button" aria-label="<?php echo esc_attr(sprintf(/* translators: %s: product name. */ __('Decrease quantity of %s', 'ai-zippy-child'), $sm_item['name'])); ?>">&minus;</button>
								<span class="sm-cart-item__qty-value"><?php echo esc_html((string) $sm_item['quantity']); ?></span>
								<button class="sm-cart-item__qty-up" type="button" aria-label="<?php echo esc_attr(sprintf(/* translators: %s: product name. */ __('Increase quantity of %s', 'ai-zippy-child'), $sm_item['name'])); ?>">+</button>
							</div>
							<button class="sm-cart-item__remove" type="button"><?php esc_html_e('Remove', 'ai-zippy-child'); ?></button>
						</div>
					</div>

					<span class="sm-cart-item__line"><?php echo esc_html($sm_item['line']); ?></span>
				</li>
			<?php endforeach; ?>
		</ul>

		<div class="sm-cart-drawer__footer"<?php echo $sm_has_items ? '' : ' hidden'; ?>>
			<span class="sm-cart-drawer__eyebrow"><?php esc_html_e('Delivery', 'ai-zippy-child'); ?></span>

			<div class="sm-cart-drawer__picker">
				<button class="sm-cart-picker sm-cart-picker--slot is-selected" type="button" aria-pressed="true" data-sm-pick-slot>
					<span class="sm-cart-picker__title"><?php esc_html_e('3-Hour Slot', 'ai-zippy-child'); ?></span>
					<span class="sm-cart-picker__sub" data-sm-slot-sub><?php echo esc_html($sm_slot_sub . ' · Free'); ?></span>
				</button>
				<button class="sm-cart-picker sm-cart-picker--express" type="button" aria-pressed="false" data-sm-pick-express>
					<span class="sm-cart-picker__title"><?php esc_html_e('1-Hour Express', 'ai-zippy-child'); ?></span>
					<span class="sm-cart-picker__sub"><?php echo esc_html(sprintf(/* translators: %s: express fee, e.g. $20.00. */ __('Within 60 min · %s', 'ai-zippy-child'), $sm_fee_label)); ?></span>
				</button>
			</div>

			<div class="sm-cart-drawer__rows">
				<div class="sm-cart-drawer__row">
					<span class="sm-cart-drawer__row-label"><?php esc_html_e('Subtotal', 'ai-zippy-child'); ?></span>
					<span class="sm-cart-drawer__row-value" data-sm-subtotal><?php echo esc_html(sm_mini_cart_money($sm_subtotal)); ?></span>
				</div>
				<div class="sm-cart-drawer__row">
					<span class="sm-cart-drawer__row-label"><?php esc_html_e('Delivery', 'ai-zippy-child'); ?></span>
					<span class="sm-cart-drawer__row-value" data-sm-delivery-fee><?php esc_html_e('Free', 'ai-zippy-child'); ?></span>
				</div>
				<div class="sm-cart-drawer__row sm-cart-drawer__row--total">
					<span class="sm-cart-drawer__row-label"><?php esc_html_e('Total', 'ai-zippy-child'); ?></span>
					<span class="sm-cart-drawer__row-value" data-sm-total><?php echo esc_html(sm_mini_cart_money($sm_subtotal)); ?></span>
				</div>
			</div>

			<a class="sm-cart-drawer__checkout" href="<?php echo esc_url($sm_checkout_url); ?>"><?php esc_html_e('Checkout', 'ai-zippy-child'); ?></a>
		</div>
	</aside>
</div>
