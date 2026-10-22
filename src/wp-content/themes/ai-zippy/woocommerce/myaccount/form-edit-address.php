<?php

/**
 * Edit Address Form — AI Zippy Override
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package AiZippy
 * @version 9.3.0
 *
 * @var string $load_address
 * @var array  $address
 */

defined('ABSPATH') || exit;

$page_title = ('billing' === $load_address)
    ? esc_html__('Billing address', 'woocommerce')
    : esc_html__('Shipping address', 'woocommerce');

// Card heading inside the box — mirrors the "Profile" card on Account Details,
// so it stays distinct from the page title above it.
$card_title = ('billing' === $load_address)
    ? esc_html__('Billing Details', 'ai-zippy')
    : esc_html__('Shipping Details', 'ai-zippy');

do_action('woocommerce_before_edit_account_address_form');
?>

<?php if (!$load_address) : ?>
	<?php wc_get_template('myaccount/my-address.php'); ?>
<?php else : ?>

<a href="<?php echo esc_url(wc_get_endpoint_url('edit-address')); ?>" class="ma__back-link">
	<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="15 18 9 12 15 6"/></svg>
	<?php esc_html_e('Back to addresses', 'ai-zippy'); ?>
</a>

<div class="ma__section-header">
	<h2 class="ma__section-title">
		<?php echo esc_html(apply_filters('woocommerce_my_account_edit_address_title', $page_title, $load_address)); // phpcs:ignore ?>
	</h2>
</div>

<form method="post" novalidate class="ma__form">

	<div class="woocommerce-address-fields">
		<?php do_action("woocommerce_before_edit_address_form_{$load_address}"); ?>

		<div class="ma__form-card">
			<h3 class="ma__form-card-title">
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
				<?php echo esc_html($card_title); ?>
			</h3>

			<div class="woocommerce-address-fields__field-wrapper ma__form-grid">
				<?php foreach ($address as $key => $field) {
					woocommerce_form_field($key, $field, wc_get_post_data_by_key($key, $field['value']));
				} ?>
			</div>
		</div>

		<?php do_action("woocommerce_after_edit_address_form_{$load_address}"); ?>

		<div class="ma__form-actions">
			<?php wp_nonce_field('woocommerce-edit_address', 'woocommerce-edit-address-nonce'); ?>
			<input type="hidden" name="action" value="edit_address" />
			<button type="submit" name="save_address" value="<?php esc_attr_e('Save address', 'woocommerce'); ?>" class="ma__btn ma__btn--primary">
				<?php esc_html_e('Save Address', 'ai-zippy'); ?>
			</button>
		</div>
	</div>

</form>

<?php endif; ?>

<?php do_action('woocommerce_after_edit_account_address_form'); ?>
