<?php
/**
 * Checkout shipping information form — AI Zippy Override
 *
 * Identical to WooCommerce core's form-shipping.php EXCEPT the trailing
 * `.woocommerce-additional-fields` (order notes) block has been removed.
 *
 * Core bundles the order-notes field into this shipping template, but our
 * custom form-checkout.php renders order notes itself inside the numbered
 * "2. Additional information" card. Leaving core's copy in place produced two
 * `order_comments` textareas (duplicate id, and the empty one could overwrite
 * the filled one on submit). Dropping it here makes Card 2 the single source.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package AiZippy
 * @version 3.6.0
 * @global WC_Checkout $checkout
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="woocommerce-shipping-fields">
	<?php if ( true === WC()->cart->needs_shipping_address() ) : ?>

		<h3 id="ship-to-different-address">
			<label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">
				<input id="ship-to-different-address-checkbox" class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" <?php checked( apply_filters( 'woocommerce_ship_to_different_address_checked', 'shipping' === get_option( 'woocommerce_ship_to_destination' ) ? 1 : 0 ), 1 ); ?> type="checkbox" name="ship_to_different_address" value="1" /> <span><?php esc_html_e( 'Ship to a different address?', 'woocommerce' ); ?></span>
			</label>
		</h3>

		<div class="shipping_address">

			<?php do_action( 'woocommerce_before_checkout_shipping_form', $checkout ); ?>

			<div class="woocommerce-shipping-fields__field-wrapper">
				<?php
				$fields = $checkout->get_checkout_fields( 'shipping' );

				foreach ( $fields as $key => $field ) {
					woocommerce_form_field( $key, $field, $checkout->get_value( $key ) );
				}
				?>
			</div>

			<?php do_action( 'woocommerce_after_checkout_shipping_form', $checkout ); ?>

		</div>

	<?php endif; ?>
</div>
