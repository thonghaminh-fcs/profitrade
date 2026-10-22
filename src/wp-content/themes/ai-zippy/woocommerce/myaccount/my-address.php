<?php

/**
 * My Addresses — AI Zippy Override
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package AiZippy
 * @version 9.3.0
 */

defined('ABSPATH') || exit;

$customer_id = get_current_user_id();

if (!wc_ship_to_billing_address_only() && wc_shipping_enabled()) {
    $get_addresses = apply_filters(
        'woocommerce_my_account_get_addresses',
        [
            'billing'  => __('Billing address', 'woocommerce'),
            'shipping' => __('Shipping address', 'woocommerce'),
        ],
        $customer_id
    );
} else {
    $get_addresses = apply_filters(
        'woocommerce_my_account_get_addresses',
        ['billing' => __('Billing address', 'woocommerce')],
        $customer_id
    );
}

$address_icons = [
    'billing' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>',
    'shipping' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 3h5v5"/><path d="M21 3l-7 7"/><path d="M8 21H3v-5"/><path d="M3 21l7-7"/></svg>',
];
?>

<div class="ma__section-header">
	<h2 class="ma__section-title"><?php esc_html_e('My Addresses', 'ai-zippy'); ?></h2>
</div>

<p class="ma__intro">
	<?php echo esc_html(apply_filters(
		'woocommerce_my_account_my_address_description',
		__('The following addresses will be used on the checkout page by default.', 'woocommerce')
	)); ?>
</p>

<div class="ma__addr-grid">
	<?php foreach ($get_addresses as $name => $address_title) :
		$address = wc_get_account_formatted_address($name);
		$icon    = $address_icons[$name] ?? $address_icons['billing'];
	?>
	<div class="ma__addr-card <?php echo $address ? 'ma__addr-card--filled' : 'ma__addr-card--empty'; ?>">
		<div class="ma__addr-card-header">
			<span class="ma__addr-card-icon"><?php echo $icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			<h3 class="ma__addr-card-title"><?php echo esc_html($address_title); ?></h3>
		</div>

		<div class="ma__addr-card-body">
			<?php if ($address) : ?>
				<address><?php echo wp_kses_post($address); ?></address>
			<?php else : ?>
				<p class="ma__addr-empty-text"><?php esc_html_e('No address set yet.', 'ai-zippy'); ?></p>
			<?php endif; ?>

			<?php do_action('woocommerce_my_account_after_my_address', $name); ?>
		</div>

		<div class="ma__addr-card-footer">
			<a href="<?php echo esc_url(wc_get_endpoint_url('edit-address', $name)); ?>" class="ma__btn ma__btn--sm ma__btn--outline">
				<?php echo $address ? esc_html__('Edit', 'ai-zippy') : esc_html__('Add address', 'ai-zippy'); ?>
			</a>
		</div>
	</div>
	<?php endforeach; ?>
</div>
