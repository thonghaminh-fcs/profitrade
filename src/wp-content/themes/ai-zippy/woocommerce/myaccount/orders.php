<?php

/**
 * My Account — Orders — AI Zippy Override
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package AiZippy
 * @version 9.5.0
 *
 * @var stdClass $customer_orders
 * @var int      $current_page
 * @var bool     $has_orders
 */

defined('ABSPATH') || exit;

do_action('woocommerce_before_account_orders', $has_orders);
?>

<div class="ma__section-header">
	<h2 class="ma__section-title"><?php esc_html_e('My Orders', 'ai-zippy'); ?></h2>
	<?php if ($has_orders) : ?>
		<span class="ma__section-count"><?php echo esc_html($customer_orders->total ?? ''); ?></span>
	<?php endif; ?>
</div>

<?php if ($has_orders) : ?>

	<div class="ma__orders-list">
		<?php foreach ($customer_orders->orders as $customer_order) :
			$order      = wc_get_order($customer_order);
			$status     = $order->get_status();
			$item_count = $order->get_item_count() - $order->get_item_count_refunded();
			$items      = $order->get_items();
			$actions    = wc_get_account_orders_actions($order);
		?>
		<div class="ma__order-row ma__order-row--status-<?php echo esc_attr($status); ?>">
			<!-- Order thumbnails -->
			<div class="ma__order-thumbs">
				<?php
				$shown = 0;
				foreach ($items as $item) :
					if ($shown >= 3) break;
					$product = $item->get_product();
				?>
					<div class="ma__order-thumb">
						<?php if ($product) : ?>
							<?php echo wp_kses_post($product->get_image('thumbnail')); ?>
						<?php else : ?>
							<div class="ma__order-thumb-placeholder"></div>
						<?php endif; ?>
					</div>
				<?php
					$shown++;
				endforeach;
				if (count($items) > 3) : ?>
					<div class="ma__order-thumb ma__order-thumb--more">+<?php echo esc_html(count($items) - 3); ?></div>
				<?php endif; ?>
			</div>

			<!-- Order info -->
			<div class="ma__order-info">
				<div class="ma__order-meta">
					<a href="<?php echo esc_url($order->get_view_order_url()); ?>" class="ma__order-num">
						<?php echo esc_html('#' . $order->get_order_number()); ?>
					</a>
					<span class="ma__order-status ma__order-status--<?php echo esc_attr($status); ?>">
						<?php echo esc_html(wc_get_order_status_name($status)); ?>
					</span>
				</div>
				<div class="ma__order-details">
					<span class="ma__order-detail">
						<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
						<?php echo esc_html(wc_format_datetime($order->get_date_created())); ?>
					</span>
					<span class="ma__order-detail">
						<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
						<?php printf(
							esc_html(_n('%d item', '%d items', $item_count, 'ai-zippy')),
							$item_count
						); ?>
					</span>
				</div>
			</div>

			<!-- Total + actions -->
			<div class="ma__order-right">
				<span class="ma__order-total"><?php echo wp_kses_post($order->get_formatted_order_total()); ?></span>
				<div class="ma__order-actions">
					<a href="<?php echo esc_url($order->get_view_order_url()); ?>" class="ma__btn ma__btn--sm ma__btn--outline">
						<?php esc_html_e('Details', 'ai-zippy'); ?>
					</a>
					<?php foreach ($actions as $key => $action) :
						if ($key === 'view') continue; // already shown as Details
					?>
						<a href="<?php echo esc_url($action['url']); ?>" class="ma__btn ma__btn--sm ma__btn--primary" aria-label="<?php echo esc_attr($action['name']); ?>">
							<?php echo esc_html($action['name']); ?>
						</a>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
		<?php endforeach; ?>
	</div>

	<?php do_action('woocommerce_before_account_orders_pagination'); ?>

	<?php if (1 < $customer_orders->max_num_pages) : ?>
		<div class="ma__pagination">
			<?php if (1 !== $current_page) : ?>
				<a class="ma__page-btn" href="<?php echo esc_url(wc_get_endpoint_url('orders', $current_page - 1)); ?>">
					<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="15 18 9 12 15 6"/></svg>
					<?php esc_html_e('Previous', 'woocommerce'); ?>
				</a>
			<?php endif; ?>
			<span class="ma__page-info">
				<?php printf(esc_html__('Page %1$s of %2$s', 'ai-zippy'), $current_page, intval($customer_orders->max_num_pages)); ?>
			</span>
			<?php if (intval($customer_orders->max_num_pages) !== $current_page) : ?>
				<a class="ma__page-btn" href="<?php echo esc_url(wc_get_endpoint_url('orders', $current_page + 1)); ?>">
					<?php esc_html_e('Next', 'woocommerce'); ?>
					<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="9 18 15 12 9 6"/></svg>
				</a>
			<?php endif; ?>
		</div>
	<?php endif; ?>

<?php else : ?>

	<div class="ma__empty-state">
		<div class="ma__empty-icon">
			<svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
		</div>
		<p class="ma__empty-text"><?php esc_html_e('You haven\'t placed any orders yet.', 'ai-zippy'); ?></p>
		<a href="<?php echo esc_url(apply_filters('woocommerce_return_to_shop_redirect', wc_get_page_permalink('shop'))); ?>" class="ma__btn ma__btn--primary">
			<?php esc_html_e('Browse Products', 'woocommerce'); ?>
		</a>
	</div>

<?php endif; ?>

<?php do_action('woocommerce_after_account_orders', $has_orders); ?>
