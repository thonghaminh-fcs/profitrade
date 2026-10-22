<?php
/**
 * Output a single payment method.
 *
 * Theme override of WooCommerce's checkout/payment-method.php. Two differences
 * from core:
 *
 * 1. The .payment_box's children are wrapped server-side in
 *    <div class="payment_box__clip"><div class="payment_box__inner">…</div></div>.
 *    _wc-checkout.scss animates the outer .payment_box's grid-template-rows,
 *    clips overflow on __clip, and puts padding/background/border-radius on
 *    __inner — a three-layer structure that avoids the padded-clipping-layer
 *    residual-strip pitfall. Rendering the wrappers here (instead of relying
 *    on wc-checkout-phone.js to inject them after DOMContentLoaded) means the
 *    correct styling paints on first render, preventing the flash of white
 *    background + default text before JS runs.
 *
 * 2. The chosen gateway's .payment_box gets .is-open directly, and the inline
 *    style="display:none;" that WC core emits for non-chosen gateways is
 *    omitted — our grid-template-rows:0fr default already collapses unchosen
 *    boxes without needing an inline display swap that would fight the CSS
 *    transition.
 *
 * @package ai-zippy
 * @see /var/www/html/wp-content/plugins/woocommerce/templates/checkout/payment-method.php (v3.5.0)
 */

if (!defined('ABSPATH')) {
	exit;
}
?>
<li class="wc_payment_method payment_method_<?php echo esc_attr($gateway->id); ?>">
	<input id="payment_method_<?php echo esc_attr($gateway->id); ?>" type="radio" class="input-radio" name="payment_method" value="<?php echo esc_attr($gateway->id); ?>" <?php checked($gateway->chosen, true); ?> data-order_button_text="<?php echo esc_attr($gateway->order_button_text); ?>" />

	<label for="payment_method_<?php echo esc_attr($gateway->id); ?>">
		<?php echo $gateway->get_title(); /* phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped */ ?> <?php echo $gateway->get_icon(); /* phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped */ ?>
	</label>
	<?php if ($gateway->has_fields() || $gateway->get_description()) : ?>
		<div class="payment_box payment_method_<?php echo esc_attr($gateway->id); ?><?php echo $gateway->chosen ? ' is-open' : ''; ?>">
			<div class="payment_box__clip">
				<div class="payment_box__inner">
					<?php $gateway->payment_fields(); ?>
				</div>
			</div>
		</div>
	<?php endif; ?>
</li>
