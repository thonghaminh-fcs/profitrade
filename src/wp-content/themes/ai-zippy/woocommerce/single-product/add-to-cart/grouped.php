<?php
/**
 * Grouped product add to cart — theme override.
 *
 * Copied from woocommerce/templates/single-product/add-to-cart/grouped.php @ 11.0.0.
 * Re-check this file against the plugin's copy when WooCommerce bumps that version.
 *
 * Deltas from the original, and only these:
 *   - form/table/button carry zp- classes so the theme's styles apply
 *   - woocommerce_quantity_input() swapped for the theme's -/+ stepper, so child
 *     rows match the quantity control on simple and variable product pages
 *   - the submit button gets the .zp-add-btn markup (icon + text + spinner slot)
 *
 * Deliberately NOT changed: the table structure, every do_action/apply_filters,
 * and the per-child branching (children with options, out of stock, or sold
 * individually). Those carry the edge cases and third-party extension points.
 *
 * The button opts into AJAX via .az-add-to-cart. The theme's handler special-cases
 * this form: the Store API adds one product per request, so it walks the
 * quantity[<child_id>] rows and fires a request per selected child. Without JS the
 * button still submits the form and WooCommerce handles it server-side.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package AiZippy
 * @version 11.0.0
 */

defined( 'ABSPATH' ) || exit;

use AiZippy\Product\ProductShortcode;

global $product, $post;

do_action( 'woocommerce_before_add_to_cart_form' ); ?>

<form class="cart grouped_form zp-grouped" action="<?php echo esc_url( apply_filters( 'woocommerce_add_to_cart_form_action', $product->get_permalink() ) ); ?>" method="post" enctype='multipart/form-data'>
	<table cellspacing="0" class="woocommerce-grouped-product-list group_table zp-grouped__table" role="presentation">
		<tbody>
			<?php
			$quantites_required      = false;
			$previous_post           = $post;
			$grouped_product_columns = apply_filters(
				'woocommerce_grouped_product_columns',
				array(
					'quantity',
					'label',
					'price',
				),
				$product
			);
			$show_add_to_cart_button = false;

			do_action( 'woocommerce_grouped_product_list_before', $grouped_product_columns, $quantites_required, $product );

			foreach ( $grouped_products as $grouped_product_child ) {
				$post_object        = get_post( $grouped_product_child->get_id() );
				$quantites_required = $quantites_required || ( $grouped_product_child->is_purchasable() && ! $grouped_product_child->has_options() );
				$post               = $post_object; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
				setup_postdata( $post );

				if ( $grouped_product_child->is_in_stock() ) {
					$show_add_to_cart_button = true;
				}

				echo '<tr id="product-' . esc_attr( $grouped_product_child->get_id() ) . '" class="woocommerce-grouped-product-list-item zp-grouped__row ' . esc_attr( implode( ' ', wc_get_product_class( '', $grouped_product_child ) ) ) . '">';

				// Output columns for each product.
				foreach ( $grouped_product_columns as $column_id ) {
					do_action( 'woocommerce_grouped_product_list_before_' . $column_id, $grouped_product_child );

					switch ( $column_id ) {
						case 'quantity':
							ob_start();

							if ( ! $grouped_product_child->is_purchasable() || $grouped_product_child->has_options() || ! $grouped_product_child->is_in_stock() ) {
								woocommerce_template_loop_add_to_cart();
							} elseif ( $grouped_product_child->is_sold_individually() ) {
								echo '<input type="checkbox" name="' . esc_attr( 'quantity[' . $grouped_product_child->get_id() . ']' ) . '" value="1" class="wc-grouped-product-add-to-cart-checkbox zp-grouped__checkbox" id="' . esc_attr( 'quantity-' . $grouped_product_child->get_id() ) . '" />';
								echo '<label for="' . esc_attr( 'quantity-' . $grouped_product_child->get_id() ) . '" class="screen-reader-text">';
								if ( $grouped_product_child->is_on_sale() ) {
									printf(
										/* translators: %1$s: Product name. %2$s: Sale price. %3$s: Regular price */
										esc_html__( 'Buy one of %1$s on sale for %2$s, original price was %3$s', 'woocommerce' ),
										esc_html( $grouped_product_child->get_name() ),
										esc_html( wp_strip_all_tags( wc_price( $grouped_product_child->get_price() ) ) ),
										esc_html( wp_strip_all_tags( wc_price( $grouped_product_child->get_regular_price() ) ) )
									);
								} else {
									printf(
										/* translators: %1$s: Product name. %2$s: Product price */
										esc_html__( 'Buy one of %1$s for %2$s', 'woocommerce' ),
										esc_html( $grouped_product_child->get_name() ),
										esc_html( wp_strip_all_tags( wc_price( $grouped_product_child->get_price() ) ) )
									);
								}
								echo '</label>';

							} else {
								do_action( 'woocommerce_before_add_to_cart_quantity' );

								ProductShortcode::renderQuantityStepper(
									$grouped_product_child,
									array(
										'name'        => 'quantity[' . $grouped_product_child->get_id() . ']',
										// Blank, not 1: a child is only added if the shopper raises
										// its quantity. WC treats an empty or 0 row as "skip".
										'value'       => isset( $_POST['quantity'][ $grouped_product_child->get_id() ] ) ? wc_stock_amount( wc_clean( wp_unslash( $_POST['quantity'][ $grouped_product_child->get_id() ] ) ) ) : '', // phpcs:ignore WordPress.Security.NonceVerification.Missing
										'placeholder' => '0',
										'min'         => apply_filters( 'woocommerce_quantity_input_min', 0, $grouped_product_child ),
										'max'         => $grouped_product_child->get_max_purchase_quantity(),
										/* translators: %s: Product name. */
										'aria_label'  => sprintf( __( 'Quantity of %s', 'ai-zippy' ), $grouped_product_child->get_name() ),
									)
								);

								do_action( 'woocommerce_after_add_to_cart_quantity' );
							}

							$value = ob_get_clean();
							break;
						case 'label':
							$value  = '<label for="product-' . esc_attr( $grouped_product_child->get_id() ) . '">';
							$value .= $grouped_product_child->is_visible() ? '<a href="' . esc_url( apply_filters( 'woocommerce_grouped_product_list_link', $grouped_product_child->get_permalink(), $grouped_product_child->get_id() ) ) . '">' . $grouped_product_child->get_name() . '</a>' : $grouped_product_child->get_name();
							$value .= '</label>';
							break;
						case 'price':
							$value = $grouped_product_child->get_price_html() . wc_get_stock_html( $grouped_product_child );
							break;
						default:
							$value = '';
							break;
					}

					echo '<td class="woocommerce-grouped-product-list-item__' . esc_attr( $column_id ) . ' zp-grouped__cell zp-grouped__cell--' . esc_attr( $column_id ) . '">' . apply_filters( 'woocommerce_grouped_product_list_column_' . $column_id, $value, $grouped_product_child ) . '</td>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

					do_action( 'woocommerce_grouped_product_list_after_' . $column_id, $grouped_product_child );
				}

				echo '</tr>';
			}
			$post = $previous_post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			setup_postdata( $post );

			do_action( 'woocommerce_grouped_product_list_after', $grouped_product_columns, $quantites_required, $product );
			?>
		</tbody>
	</table>

	<input type="hidden" name="add-to-cart" value="<?php echo esc_attr( $product->get_id() ); ?>" />

	<?php if ( $quantites_required && $show_add_to_cart_button ) : ?>

		<?php do_action( 'woocommerce_before_add_to_cart_button' ); ?>

		<button type="submit" class="zp-add-btn single_add_to_cart_button button alt az-add-to-cart<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>">
			<span class="zp-add-btn__text"><?php echo esc_html( $product->single_add_to_cart_text() ); ?></span>
			<svg class="zp-add-btn__icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
				<circle cx="9" cy="21" r="1"/>
				<circle cx="20" cy="21" r="1"/>
				<path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
			</svg>
			<span class="zp-add-btn__spinner" aria-hidden="true"></span>
		</button>

		<?php do_action( 'woocommerce_after_add_to_cart_button' ); ?>

	<?php endif; ?>
</form>

<?php do_action( 'woocommerce_after_add_to_cart_form' ); ?>
