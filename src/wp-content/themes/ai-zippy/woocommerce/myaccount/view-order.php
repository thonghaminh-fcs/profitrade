<?php

/**
 * View Order — AI Zippy Override
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package AiZippy
 * @version 10.6.0
 *
 * @var WC_Order $order
 * @var int      $order_id
 */

defined('ABSPATH') || exit;

$notes           = $order->get_customer_order_notes();
$status          = $order->get_status();
$shipping_address = $order->get_formatted_shipping_address();
$billing_address  = $order->get_formatted_billing_address();
$payment_method   = $order->get_payment_method_title();
?>

<div class="ma__view-order">

	<!-- Back link -->
	<a href="<?php echo esc_url(wc_get_account_endpoint_url('orders')); ?>" class="ma__back-link">
		<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="15 18 9 12 15 6"/></svg>
		<?php esc_html_e('Back to Orders', 'ai-zippy'); ?>
	</a>

	<!-- Order header -->
	<div class="ma__vo-header">
		<div class="ma__vo-header-left">
			<h2 class="ma__vo-title">
				<?php printf(esc_html__('Order #%s', 'ai-zippy'), esc_html($order->get_order_number())); ?>
			</h2>
			<span class="ma__vo-date"><?php echo esc_html(wc_format_datetime($order->get_date_created())); ?></span>
		</div>
		<span class="ma__order-status ma__order-status--<?php echo esc_attr($status); ?> ma__order-status--lg">
			<?php echo esc_html(wc_get_order_status_name($status)); ?>
		</span>
	</div>

	<!-- Order updates / notes -->
	<?php if ($notes) : ?>
	<div class="ma__vo-card">
		<h3 class="ma__vo-card-title">
			<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
			<?php esc_html_e('Order Updates', 'ai-zippy'); ?>
		</h3>
		<ol class="ma__vo-notes">
			<?php foreach ($notes as $note) : ?>
			<li class="ma__vo-note">
				<span class="ma__vo-note-date"><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($note->comment_date))); ?></span>
				<div class="ma__vo-note-text"><?php echo wp_kses_post(wpautop(wptexturize($note->comment_content))); ?></div>
			</li>
			<?php endforeach; ?>
		</ol>
	</div>
	<?php endif; ?>

	<!-- Order items -->
	<div class="ma__vo-card">
		<h3 class="ma__vo-card-title">
			<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
			<?php esc_html_e('Order Items', 'ai-zippy'); ?>
		</h3>

		<div class="ma__vo-items">
			<?php foreach ($order->get_items() as $item_id => $item) :
				$product = $item->get_product();
				$qty     = $item->get_quantity();
				$total   = $order->get_formatted_line_subtotal($item);
				$meta    = strip_tags(wc_display_item_meta($item, ['echo' => false, 'separator' => ', ']));
			?>
			<div class="ma__vo-item">
				<div class="ma__vo-item-img">
					<?php if ($product) echo wp_kses_post($product->get_image('woocommerce_thumbnail')); ?>
				</div>
				<div class="ma__vo-item-detail">
					<span class="ma__vo-item-name"><?php echo wp_kses_post($item->get_name()); ?></span>
					<?php if ($meta) : ?>
						<span class="ma__vo-item-meta"><?php echo esc_html($meta); ?></span>
					<?php endif; ?>
					<span class="ma__vo-item-qty"><?php printf(esc_html__('Qty: %s', 'ai-zippy'), esc_html($qty)); ?></span>
				</div>
				<span class="ma__vo-item-total"><?php echo wp_kses_post($total); ?></span>
			</div>
			<?php endforeach; ?>
		</div>

		<!-- Totals -->
		<div class="ma__vo-totals">
			<div class="ma__vo-totals-row">
				<span><?php esc_html_e('Subtotal', 'ai-zippy'); ?></span>
				<span><?php echo wp_kses_post($order->get_subtotal_to_display()); ?></span>
			</div>
			<?php if ($order->get_shipping_method()) : ?>
			<div class="ma__vo-totals-row">
				<span><?php esc_html_e('Shipping', 'ai-zippy'); ?></span>
				<span>
					<?php
					$shipping = (float) $order->get_shipping_total();
					echo $shipping > 0
						? wp_kses_post(wc_price($shipping))
						: '<span class="ma__free">' . esc_html__('Free', 'ai-zippy') . '</span>';
					?>
				</span>
			</div>
			<?php endif; ?>
			<?php if ((float) $order->get_total_tax() > 0) : ?>
			<div class="ma__vo-totals-row">
				<span><?php esc_html_e('Tax', 'ai-zippy'); ?></span>
				<span><?php echo wp_kses_post(wc_price($order->get_total_tax())); ?></span>
			</div>
			<?php endif; ?>
			<?php if ((float) $order->get_total_discount() > 0) : ?>
			<div class="ma__vo-totals-row ma__vo-totals-row--discount">
				<span><?php esc_html_e('Discount', 'ai-zippy'); ?></span>
				<span>-<?php echo wp_kses_post(wc_price($order->get_total_discount())); ?></span>
			</div>
			<?php endif; ?>
			<?php
			// Order fees — covers Points redemption, gift wrap, surcharges,
			// and any other line WC tracks via $order->get_fees(). Negative
			// fees (redemptions/credits) get the discount style.
			foreach ($order->get_fees() as $fee_item) :
				$fee_total = (float) $fee_item->get_total();
				$is_credit = $fee_total < 0;
			?>
			<div class="ma__vo-totals-row<?php echo $is_credit ? ' ma__vo-totals-row--discount' : ''; ?>">
				<span><?php echo esc_html($fee_item->get_name()); ?></span>
				<span><?php echo $is_credit ? '−' : ''; ?><?php echo wp_kses_post(wc_price(abs($fee_total))); ?></span>
			</div>
			<?php endforeach; ?>
			<div class="ma__vo-totals-row ma__vo-totals-row--total">
				<span><?php esc_html_e('Total', 'ai-zippy'); ?></span>
				<span><?php echo wp_kses_post($order->get_formatted_order_total()); ?></span>
			</div>
		</div>
	</div>

	<!-- Delivery details -->
	<?php if ($shipping_address || $billing_address || $payment_method) : ?>
	<div class="ma__vo-card">
		<h3 class="ma__vo-card-title">
			<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
			<?php esc_html_e('Delivery Details', 'ai-zippy'); ?>
		</h3>
		<div class="ma__vo-addresses">
			<?php if ($shipping_address) : ?>
			<div class="ma__vo-address-block">
				<span class="ma__vo-address-label"><?php esc_html_e('Ship to', 'ai-zippy'); ?></span>
				<address><?php echo wp_kses_post($shipping_address); ?></address>
			</div>
			<?php endif; ?>
			<?php if ($billing_address) : ?>
			<div class="ma__vo-address-block">
				<span class="ma__vo-address-label"><?php esc_html_e('Bill to', 'ai-zippy'); ?></span>
				<address><?php echo wp_kses_post($billing_address); ?></address>
			</div>
			<?php endif; ?>
			<?php if ($payment_method) : ?>
			<div class="ma__vo-address-block">
				<span class="ma__vo-address-label"><?php esc_html_e('Payment', 'ai-zippy'); ?></span>
				<span><?php echo wp_kses_post($payment_method); ?></span>
			</div>
			<?php endif; ?>
		</div>
	</div>
	<?php endif; ?>

	<?php do_action('woocommerce_view_order', $order_id); ?>

</div>
