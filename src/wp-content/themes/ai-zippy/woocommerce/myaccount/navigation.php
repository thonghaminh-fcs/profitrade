<?php

/**
 * My Account Navigation — AI Zippy Override
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package AiZippy
 * @version 9.3.0
 */

defined('ABSPATH') || exit;

// Icon map per endpoint slug. Plugins can extend this via the filter below.
$nav_icons = apply_filters('ai_zippy_account_nav_icons', [
    'dashboard'       => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>',
    'orders'          => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>',
    'downloads'       => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>',
    'edit-address'    => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>',
    'payment-methods' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>',
    'edit-account'    => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
    'customer-logout' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>',
]);

do_action('woocommerce_before_account_navigation');
?>

<nav class="ma__nav" aria-label="<?php esc_attr_e('Account pages', 'woocommerce'); ?>">

	<?php
	$current_user = wp_get_current_user();
	$avatar       = get_avatar($current_user->ID, 56, '', '', ['class' => 'ma__nav-avatar-img']);
	?>
	<div class="ma__nav-user">
		<div class="ma__nav-avatar">
			<?php if ($avatar) : ?>
				<?php echo $avatar; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php else : ?>
				<div class="ma__nav-avatar-placeholder">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
				</div>
			<?php endif; ?>
		</div>
		<div class="ma__nav-user-info">
			<span class="ma__nav-user-name"><?php echo esc_html($current_user->display_name); ?></span>
			<span class="ma__nav-user-email"><?php echo esc_html($current_user->user_email); ?></span>
		</div>
	</div>

	<ul class="ma__nav-list">
		<?php
		// Endpoints with a shorter label used only on mobile (see the
		// .ma__nav-label[data-short-label] rule in _account.scss). Keeps the
		// full WC/i18n label on desktop while the horizontal-scroll mobile nav
		// gets a compact one that fits under the icon.
		$mobile_short_labels = apply_filters('ai_zippy_account_nav_mobile_labels', [
			'edit-account' => __('Account', 'ai-zippy'),
		]);

		foreach (wc_get_account_menu_items() as $endpoint => $label) :
			$classes     = wc_get_account_menu_item_classes($endpoint);
			$icon        = $nav_icons[$endpoint] ?? $nav_icons['dashboard'];
			$is_logout   = 'customer-logout' === $endpoint;
			$short_label = $mobile_short_labels[$endpoint] ?? '';
		?>
			<li class="ma__nav-item <?php echo esc_attr($classes); ?><?php echo $is_logout ? ' ma__nav-item--logout' : ''; ?>">
				<a
					href="<?php echo esc_url(wc_get_account_endpoint_url($endpoint)); ?>"
					class="ma__nav-link"
					title="<?php echo esc_attr($label); ?>"
					<?php echo wc_is_current_account_menu_item($endpoint) ? 'aria-current="page"' : ''; ?>
				>
					<span class="ma__nav-icon"><?php echo $icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<span
						class="ma__nav-label"
						<?php if ($short_label !== '') : ?>data-short-label="<?php echo esc_attr($short_label); ?>"<?php endif; ?>
					><?php echo esc_html($label); ?></span>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
</nav>

<?php do_action('woocommerce_after_account_navigation'); ?>
