<?php
/**
 * External product add to cart — theme override.
 *
 * Copied from woocommerce/templates/single-product/add-to-cart/external.php @ 7.0.1.
 * Re-check this file against the plugin's copy when WooCommerce bumps that version.
 *
 * Only delta: the button carries the theme's .zp-add-btn markup so an affiliate
 * product's call to action matches Add to Cart on every other product page. Class
 * list, hooks and wc_query_string_form_fields() are untouched.
 *
 * No cart icon here — this navigates off-site rather than adding anything, and no
 * .az-add-to-cart either, for the same reason.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package AiZippy
 * @version 7.0.1
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_add_to_cart_form' ); ?>

<form class="cart zp-external" action="<?php echo esc_url( $product_url ); ?>" method="get">
	<?php do_action( 'woocommerce_before_add_to_cart_button' ); ?>

	<button type="submit" class="zp-add-btn single_add_to_cart_button button alt<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>">
		<span class="zp-add-btn__text"><?php echo esc_html( $button_text ); ?></span>
	</button>

	<?php wc_query_string_form_fields( $product_url ); ?>

	<?php do_action( 'woocommerce_after_add_to_cart_button' ); ?>
</form>

<?php do_action( 'woocommerce_after_add_to_cart_form' ); ?>
