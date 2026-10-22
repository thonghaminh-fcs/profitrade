<?php

/**
 * Checkout Fulfilment Picker — frontend render.
 *
 * Checkout step "1 · Fulfilment" placed above the Woo checkout form
 * (templates/page-checkout.html); markup per docs/build/blocks/checkout-fulfilment.md
 * (.mockup/slim.html:653-686). PHP prints the no-JS default (or the state left
 * in the sm_fulfilment/sm_express/sm_slot cookies); view.js owns the live
 * behaviour and reads the shared {slot, express} delivery state from child.js
 * (sessionStorage "sm-delivery", "sm:delivery" event).
 *
 * Hidden inputs sm_fulfilment / sm_express / sm_slot post with the classic Woo
 * checkout form (view.js associates them via the form attribute — the block
 * sits OUTSIDE the <form>). Order-meta persistence, the "+$20.00" express fee
 * and admin/email display are owned by the theme's inc/checkout-fulfilment.php
 * (loaded on every request, incl. Store-API React checkouts, which never
 * render this block and post cookies instead of the hidden inputs).
 *
 * This file ships structural markup only; colours, spacing and typography live
 * in src/scss/sections/_checkout-fulfilment.scss.
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Inner blocks (unused).
 * @var WP_Block $block      Block instance.
 */

defined('ABSPATH') || exit;

// The five 3-hour slots, in order — must match child.js / delivery-bar.
$slots = [
	'8:00 – 11:00 AM',
	'11:00 AM – 2:00 PM',
	'2:00 – 5:00 PM',
	'5:00 – 8:00 PM',
	'8:00 – 11:00 PM',
];

$fee       = isset($attributes['expressFee']) ? (float) $attributes['expressFee'] : 20;
$fee_label = '$' . number_format($fee, 2, '.', '');

// Initial state: cookies written by view.js on a previous render, else defaults.
$fulfilment = isset($_COOKIE['sm_fulfilment']) && 'pickup' === $_COOKIE['sm_fulfilment'] ? 'pickup' : 'delivery';
$express    = isset($_COOKIE['sm_express']) && '1' === $_COOKIE['sm_express'] ? '1' : '0';
$slot_label = isset($_COOKIE['sm_slot']) ? sanitize_text_field(wp_unslash($_COOKIE['sm_slot'])) : '';
if (!in_array($slot_label, $slots, true)) {
	$slot_label = $slots[2];
}

$is_pickup = 'pickup' === $fulfilment;

// Pickup note: bold the store address when the default copy is in use.
$note    = isset($attributes['pickupNote']) ? trim((string) $attributes['pickupNote']) : '';
$address = 'S.Mart, 10 Example Road #01-01';
if ('' === $note) {
	$note = "Collect at {$address}. We'll message you when your order is packed, usually within 30 minutes.";
}
$note_pos = strpos($note, $address);
?>
<div <?php echo get_block_wrapper_attributes([
	'class'            => 'sm-fulfilment' . ($is_pickup ? ' is-pickup' : ''),
	'data-express-fee' => $fee_label,
]); ?>>

	<p class="sm-fulfilment__eyebrow"><?php esc_html_e('1 · Fulfilment', 'ai-zippy-child'); ?></p>

	<div class="sm-fulfilment__options" role="radiogroup" aria-label="<?php esc_attr_e('Fulfilment method', 'ai-zippy-child'); ?>">

		<div
			class="sm-fulfilment__option<?php echo $is_pickup ? '' : ' is-selected'; ?>"
			role="radio"
			aria-checked="<?php echo $is_pickup ? 'false' : 'true'; ?>"
			tabindex="<?php echo $is_pickup ? '-1' : '0'; ?>"
			data-sm-fulfil-option="delivery"
		>
			<span class="sm-fulfilment__option-title" data-sm-fulfil-delivery-title><?php echo '1' === $express ? esc_html__('1-Hour Express', 'ai-zippy-child') : esc_html__('Delivery', 'ai-zippy-child'); ?></span>
			<span class="sm-fulfilment__option-sub" data-sm-fulfil-delivery-sub><?php
				echo '1' === $express
					? esc_html(sprintf(__('Within 60 min · %s', 'ai-zippy-child'), $fee_label))
					: esc_html(sprintf(__('Today, %1$s · Free', 'ai-zippy-child'), $slot_label));
			?></span>
		</div>

		<div
			class="sm-fulfilment__option<?php echo $is_pickup ? ' is-selected' : ''; ?>"
			role="radio"
			aria-checked="<?php echo $is_pickup ? 'true' : 'false'; ?>"
			tabindex="<?php echo $is_pickup ? '0' : '-1'; ?>"
			data-sm-fulfil-option="pickup"
		>
			<span class="sm-fulfilment__option-title"><?php esc_html_e('Walk-in Pickup', 'ai-zippy-child'); ?></span>
			<span class="sm-fulfilment__option-sub"><?php esc_html_e('Collect at store · Free', 'ai-zippy-child'); ?></span>
		</div>

	</div>

	<p class="sm-fulfilment__pickup-note"><?php
		if (false !== $note_pos) {
			echo esc_html(substr($note, 0, $note_pos));
			echo '<strong>' . esc_html($address) . '</strong>';
			echo esc_html(substr($note, $note_pos + strlen($address)));
		} else {
			echo esc_html($note);
		}
	?></p>

	<div class="sm-fulfilment__fields" aria-hidden="true">
		<input type="hidden" name="sm_fulfilment" value="<?php echo esc_attr($fulfilment); ?>" data-sm-fulfilment-input>
		<input type="hidden" name="sm_express" value="<?php echo esc_attr($express); ?>" data-sm-express-input>
		<input type="hidden" name="sm_slot" value="<?php echo esc_attr($slot_label); ?>" data-sm-slot-input>
	</div>

</div>
<?php
// Order-meta persistence, the express fee and admin/email display live in the
// theme's inc/checkout-fulfilment.php — they must run on EVERY request (the
// Store-API React checkout never renders this block), not only on page renders.
