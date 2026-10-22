<?php

/**
 * My Account Dashboard — AI Zippy Override
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package AiZippy
 * @version 4.4.0
 *
 * @var WP_User $current_user
 */

defined('ABSPATH') || exit;

$customer = new WC_Customer(get_current_user_id());

// Fetch last 3 orders for the recent orders panel
$recent_orders = wc_get_orders([
    'customer' => get_current_user_id(),
    'limit'    => 3,
    'orderby'  => 'date',
    'order'    => 'DESC',
    'status'   => array_keys(wc_get_order_statuses()),
]);
?>

<div class="ma__dashboard">

	<!-- Welcome banner -->
	<div class="ma__dash-welcome">
		<div class="ma__dash-welcome-text">
			<h2 class="ma__dash-title">
				<?php printf(
					/* translators: %s: user first name */
					esc_html__('Welcome back, %s!', 'ai-zippy'),
					esc_html($current_user->first_name ?: $current_user->display_name)
				); ?>
			</h2>
			<p class="ma__dash-subtitle"><?php esc_html_e('Here\'s a summary of your account activity.', 'ai-zippy'); ?></p>
		</div>
		<a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>" class="ma__btn ma__btn--outline">
			<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
			<?php esc_html_e('Continue Shopping', 'ai-zippy'); ?>
		</a>
	</div>

	<!-- Quick stats -->
	<div class="ma__dash-stats">
		<?php
		$total_orders  = count(wc_get_orders(['customer' => get_current_user_id(), 'limit' => -1, 'return' => 'ids']));
		$total_spent   = $customer->get_total_spent();
		$last_order    = $recent_orders[0] ?? null;
		?>
		<div class="ma__dash-stat">
			<div class="ma__dash-stat-icon ma__dash-stat-icon--orders">
				<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
			</div>
			<div class="ma__dash-stat-body">
				<span class="ma__dash-stat-value"><?php echo esc_html($total_orders); ?></span>
				<span class="ma__dash-stat-label"><?php esc_html_e('Total Orders', 'ai-zippy'); ?></span>
			</div>
		</div>

		<div class="ma__dash-stat">
			<div class="ma__dash-stat-icon ma__dash-stat-icon--spent">
				<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
			</div>
			<div class="ma__dash-stat-body">
				<span class="ma__dash-stat-value"><?php echo wp_kses_post(wc_price($total_spent)); ?></span>
				<span class="ma__dash-stat-label"><?php esc_html_e('Total Spent', 'ai-zippy'); ?></span>
			</div>
		</div>

		<?php if ($last_order) : ?>
		<div class="ma__dash-stat">
			<div class="ma__dash-stat-icon ma__dash-stat-icon--last">
				<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
			</div>
			<div class="ma__dash-stat-body">
				<span class="ma__dash-stat-value"><?php echo esc_html(wc_format_datetime($last_order->get_date_created())); ?></span>
				<span class="ma__dash-stat-label"><?php esc_html_e('Last Order', 'ai-zippy'); ?></span>
			</div>
		</div>
		<?php endif; ?>
	</div>

	<!-- Recent orders -->
	<?php if (!empty($recent_orders)) : ?>
	<div class="ma__dash-section">
		<div class="ma__dash-section-header">
			<h3 class="ma__dash-section-title"><?php esc_html_e('Recent Orders', 'ai-zippy'); ?></h3>
			<a href="<?php echo esc_url(wc_get_account_endpoint_url('orders')); ?>" class="ma__dash-section-link">
				<?php esc_html_e('View all', 'ai-zippy'); ?>
				<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="9 18 15 12 9 6"/></svg>
			</a>
		</div>

		<div class="ma__order-list">
			<?php foreach ($recent_orders as $order) :
				$status     = $order->get_status();
				$item_count = $order->get_item_count() - $order->get_item_count_refunded();
				$items      = $order->get_items();
				$first_item = reset($items);
				$product    = $first_item ? $first_item->get_product() : null;
			?>
			<div class="ma__order-card">
				<div class="ma__order-card-thumb">
					<?php if ($product) : ?>
						<?php echo wp_kses_post($product->get_image('thumbnail')); ?>
					<?php else : ?>
						<div class="ma__order-card-thumb-placeholder">
							<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
						</div>
					<?php endif; ?>
					<?php if (count($items) > 1) : ?>
						<span class="ma__order-card-count">+<?php echo esc_html(count($items) - 1); ?></span>
					<?php endif; ?>
				</div>
				<div class="ma__order-card-info">
					<div class="ma__order-card-top">
						<span class="ma__order-card-num"><?php echo esc_html('#' . $order->get_order_number()); ?></span>
						<span class="ma__order-status ma__order-status--<?php echo esc_attr($status); ?>"><?php echo esc_html(wc_get_order_status_name($status)); ?></span>
					</div>
					<span class="ma__order-card-date"><?php echo esc_html(wc_format_datetime($order->get_date_created())); ?></span>
					<span class="ma__order-card-items">
						<?php printf(
							/* translators: %d: item count */
							esc_html(_n('%d item', '%d items', $item_count, 'ai-zippy')),
							$item_count
						); ?>
					</span>
				</div>
				<div class="ma__order-card-right">
					<span class="ma__order-card-total"><?php echo wp_kses_post($order->get_formatted_order_total()); ?></span>
					<a href="<?php echo esc_url($order->get_view_order_url()); ?>" class="ma__btn ma__btn--sm ma__btn--outline">
						<?php esc_html_e('View', 'ai-zippy'); ?>
					</a>
				</div>
			</div>
			<?php endforeach; ?>
		</div>
	</div>
	<?php else : ?>
	<div class="ma__dash-empty">
		<div class="ma__dash-empty-icon">
			<svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
		</div>
		<p class="ma__dash-empty-text"><?php esc_html_e('You haven\'t placed any orders yet.', 'ai-zippy'); ?></p>
		<a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>" class="ma__btn ma__btn--primary"><?php esc_html_e('Start Shopping', 'ai-zippy'); ?></a>
	</div>
	<?php endif; ?>

</div>

<?php
do_action('woocommerce_account_dashboard');
do_action('woocommerce_before_my_account'); // deprecated, keep for compat
do_action('woocommerce_after_my_account');  // deprecated, keep for compat
