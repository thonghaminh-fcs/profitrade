<?php

/**
 * Thank You Page — AI Zippy Override
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package AiZippy
 * @version 8.1.0
 *
 * @var WC_Order $order
 */

defined('ABSPATH') || exit;
?>

<div class="woocommerce-order az-thankyou">

	<?php if ($order) :

		do_action('woocommerce_before_thankyou', $order->get_id());

		if ($order->has_status('failed')) : ?>

			<div class="az-thankyou__header az-thankyou__header--failed">
				<div class="az-thankyou__icon az-thankyou__icon--failed">!</div>
				<h2 class="az-thankyou__title"><?php esc_html_e('Payment Failed', 'ai-zippy'); ?></h2>
				<p class="az-thankyou__desc"><?php esc_html_e('Unfortunately your order cannot be processed. Please try again.', 'ai-zippy'); ?></p>
				<div class="az-thankyou__actions">
					<a href="<?php echo esc_url($order->get_checkout_payment_url()); ?>" class="az-thankyou__btn az-thankyou__btn--primary"><?php esc_html_e('Try Again', 'ai-zippy'); ?></a>
					<?php if (is_user_logged_in()) : ?>
						<a href="<?php echo esc_url(wc_get_page_permalink('myaccount')); ?>" class="az-thankyou__btn az-thankyou__btn--outline"><?php esc_html_e('My Account', 'ai-zippy'); ?></a>
					<?php endif; ?>
				</div>
			</div>

		<?php else : ?>

			<!-- Success Header -->
			<div class="az-thankyou__header">
				<div class="az-thankyou__icon">
					<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round">
						<polyline points="20 6 9 17 4 12" />
					</svg>
				</div>
				<h2 class="az-thankyou__title"><?php esc_html_e('Thank you for your order!', 'ai-zippy'); ?></h2>
				<p class="az-thankyou__desc"><?php esc_html_e('Your order has been received and is being processed', 'ai-zippy'); ?></p>

				<div class="az-thankyou__order-num">
					<div class="az-thankyou__order-num-inner">
						<span class="az-thankyou__order-num-label"><?php esc_html_e('Order number', 'ai-zippy'); ?></span>
						<span class="az-thankyou__order-num-val" id="az-order-number">#<?php echo esc_html($order->get_order_number()); ?></span>
					</div>
					<button class="az-thankyou__copy" id="az-copy-order" type="button" aria-label="<?php esc_attr_e('Copy order number', 'ai-zippy'); ?>">
						<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
							<rect x="9" y="9" width="13" height="13" rx="2" />
							<path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1" />
						</svg>
					</button>
				</div>

				<?php if ($order->get_billing_email()) : ?>
					<p class="az-thankyou__email-note"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/><polyline points="22,4 12,13 2,4"/></svg> <?php printf(esc_html__('Order confirmation sent to %s', 'ai-zippy'), '<strong>' . esc_html($order->get_billing_email()) . '</strong>'); ?></p>
				<?php endif; ?>
			</div>

			<!-- Order Summary -->
			<div class="az-thankyou__card">
				<div class="az-thankyou__card-header">
					<h3 class="az-thankyou__card-title"><?php esc_html_e('Order Summary', 'ai-zippy'); ?></h3>
				</div>

				<div class="az-thankyou__items">
					<?php foreach ($order->get_items() as $item_id => $item) :
						$product = $item->get_product();
						$qty     = $item->get_quantity();
						$total   = $order->get_formatted_line_subtotal($item);
					?>
						<div class="az-thankyou__item">
							<div class="az-thankyou__item-img">
								<?php if ($product) echo wp_kses_post($product->get_image('woocommerce_thumbnail')); ?>
							</div>
							<div class="az-thankyou__item-detail">
								<span class="az-thankyou__item-name"><?php echo wp_kses_post($item->get_name()); ?></span>
								<?php
								$meta = strip_tags(wc_display_item_meta($item, ['echo' => false, 'separator' => ', ']));
								if ($meta) : ?>
									<span class="az-thankyou__item-meta"><?php echo esc_html($meta); ?></span>
								<?php endif; ?>
								<span class="az-thankyou__item-qty"><?php printf(esc_html__('Qty: %s', 'ai-zippy'), esc_html($qty)); ?></span>
							</div>
							<span class="az-thankyou__item-total"><?php echo wp_kses_post($total); ?></span>
						</div>
					<?php endforeach; ?>
				</div>

				<div class="az-thankyou__totals">
					<div class="az-thankyou__totals-row">
						<span><?php esc_html_e('Subtotal', 'ai-zippy'); ?></span>
						<span><?php echo wp_kses_post($order->get_subtotal_to_display()); ?></span>
					</div>

					<?php if ($order->get_shipping_method()) : ?>
						<div class="az-thankyou__totals-row">
							<span><?php esc_html_e('Shipping', 'ai-zippy'); ?></span>
							<span>
								<?php
								$shipping_total = (float) $order->get_shipping_total();
								echo $shipping_total > 0
									? wp_kses_post(wc_price($shipping_total))
									: '<span class="az-thankyou__free">' . esc_html__('Free', 'ai-zippy') . '</span>';
								?>
							</span>
						</div>
					<?php endif; ?>

					<?php if ((float) $order->get_total_tax() > 0) : ?>
						<div class="az-thankyou__totals-row">
							<span><?php esc_html_e('Tax', 'ai-zippy'); ?></span>
							<span><?php echo wp_kses_post(wc_price($order->get_total_tax())); ?></span>
						</div>
					<?php endif; ?>

					<?php if ((float) $order->get_total_discount() > 0) : ?>
						<div class="az-thankyou__totals-row az-thankyou__totals-row--discount">
							<span><?php esc_html_e('Discount', 'ai-zippy'); ?></span>
							<span>-<?php echo wp_kses_post(wc_price($order->get_total_discount())); ?></span>
						</div>
					<?php endif; ?>

					<div class="az-thankyou__totals-row az-thankyou__totals-row--total">
						<span><?php esc_html_e('Total', 'ai-zippy'); ?></span>
						<span><?php echo wp_kses_post($order->get_formatted_order_total()); ?></span>
					</div>
				</div>
			</div>

			<!-- Delivery Details -->
			<?php
			$shipping_address = $order->get_formatted_shipping_address();
			$billing_address  = $order->get_formatted_billing_address();
			$payment_method   = $order->get_payment_method_title();
			?>
			<?php if ($shipping_address || $billing_address || $payment_method) : ?>
				<div class="az-thankyou__card">
					<div class="az-thankyou__card-header">
						<h3 class="az-thankyou__card-title"><?php esc_html_e('Delivery Details', 'ai-zippy'); ?></h3>
					</div>

					<div class="az-thankyou__details">
						<?php if ($shipping_address) : ?>
							<div class="az-thankyou__detail-block">
								<div class="az-thankyou__detail-icon">
									<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
										<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
										<circle cx="12" cy="10" r="3" />
									</svg>
								</div>
								<div>
									<strong><?php esc_html_e('Shipping to', 'ai-zippy'); ?></strong>
									<address><?php echo wp_kses_post($shipping_address); ?></address>
								</div>
							</div>
						<?php endif; ?>

						<?php if ($billing_address) : ?>
							<div class="az-thankyou__detail-block">
								<div class="az-thankyou__detail-icon">
									<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
										<rect x="2" y="5" width="20" height="14" rx="2" />
										<line x1="2" y1="10" x2="22" y2="10" />
									</svg>
								</div>
								<div>
									<strong><?php esc_html_e('Billing address', 'ai-zippy'); ?></strong>
									<address><?php echo wp_kses_post($billing_address); ?></address>
								</div>
							</div>
						<?php endif; ?>

						<?php if ($payment_method) : ?>
							<div class="az-thankyou__detail-block">
								<div class="az-thankyou__detail-icon">
									<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
										<rect x="1" y="4" width="22" height="16" rx="2" />
										<line x1="1" y1="10" x2="23" y2="10" />
									</svg>
								</div>
								<div>
									<strong><?php esc_html_e('Payment method', 'ai-zippy'); ?></strong>
									<span><?php echo wp_kses_post($payment_method); ?></span>
								</div>
							</div>
						<?php endif; ?>
					</div>
				</div>
			<?php endif; ?>

		<?php endif; ?>

		<?php
		// Payment-specific hooks — only for gateways that need post-order processing
		if (!in_array($order->get_payment_method(), ['cod', 'cheque'], true)) {
			do_action('woocommerce_thankyou_' . $order->get_payment_method(), $order->get_id());
		}
		?>

		<!-- Thank You Footer -->
		<div class="az-thankyou__footer">
			<p class="az-thankyou__footer-msg"><?php esc_html_e('We appreciate your business and hope you love your new products.', 'ai-zippy'); ?></p>
			<p class="az-thankyou__footer-sub"><?php esc_html_e('Every purchase helps us continue crafting quality products.', 'ai-zippy'); ?></p>
			<div class="az-thankyou__footer-actions">
				<?php if (is_user_logged_in()) : ?>
					<a href="<?php echo esc_url(wc_get_account_endpoint_url('orders')); ?>" class="az-thankyou__btn az-thankyou__btn--outline"><?php esc_html_e('View orders', 'ai-zippy'); ?></a>
				<?php endif; ?>
				<a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>" class="az-thankyou__btn az-thankyou__btn--primary"><?php esc_html_e('Continue shopping', 'ai-zippy'); ?></a>
			</div>
		</div>

	<?php else : ?>

		<div class="az-thankyou__header">
			<div class="az-thankyou__icon">
				<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round">
					<polyline points="20 6 9 17 4 12" />
				</svg>
			</div>
			<h2 class="az-thankyou__title"><?php esc_html_e('Thank you. Your order has been received.', 'ai-zippy'); ?></h2>
		</div>

	<?php endif; ?>

</div>

<script>
	(function() {
		var btn = document.getElementById('az-copy-order');
		var val = document.getElementById('az-order-number');
		if (!btn || !val) return;
		btn.addEventListener('click', function() {
			navigator.clipboard.writeText(val.textContent.trim().replace('#', '')).then(function() {
				btn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>';
				setTimeout(function() {
					btn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>';
				}, 2000);
			});
		});
	})();
</script>
