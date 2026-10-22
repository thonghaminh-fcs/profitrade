<?php

/**
 * Lost Password Form — AI Zippy Override
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package AiZippy
 * @version 9.2.0
 */

defined('ABSPATH') || exit;

do_action('woocommerce_before_lost_password_form');
?>

<div class="ma__login-wrap">
	<div class="ma__login-card">

		<div class="ma__login-brand">
			<div class="ma__login-brand-icon">
				<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/></svg>
			</div>
		</div>

		<div class="ma__login-header">
			<h2 class="ma__login-title"><?php esc_html_e('Forgot your password?', 'ai-zippy'); ?></h2>
			<p class="ma__login-sub">
				<?php echo esc_html(apply_filters(
					'woocommerce_lost_password_message',
					__('Enter your username or email and we\'ll send you a link to reset your password.', 'ai-zippy')
				)); ?>
			</p>
		</div>

		<form method="post" class="ma__login-form woocommerce-ResetPassword lost_reset_password">

			<div class="ma__field">
				<label class="ma__label" for="user_login">
					<?php esc_html_e('Username or email', 'ai-zippy'); ?> <span class="ma__required" aria-hidden="true">*</span>
				</label>
				<div class="ma__input-wrap ma__input-wrap--icon">
					<span class="ma__input-icon" aria-hidden="true">
						<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><polyline points="22,4 12,13 2,4"/></svg>
					</span>
					<input type="text" name="user_login" id="user_login" class="ma__input" autocomplete="username" placeholder="<?php esc_attr_e('you@example.com', 'ai-zippy'); ?>" required aria-required="true" />
				</div>
			</div>

			<?php do_action('woocommerce_lostpassword_form'); ?>

			<input type="hidden" name="wc_reset_password" value="true" />
			<?php wp_nonce_field('lost_password', 'woocommerce-lost-password-nonce'); ?>

			<button type="submit" value="<?php esc_attr_e('Reset password', 'woocommerce'); ?>" class="ma__btn ma__btn--primary ma__btn--full ma__btn--lg">
				<?php esc_html_e('Send Reset Link', 'ai-zippy'); ?>
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="9 18 15 12 9 6"/></svg>
			</button>
		</form>

		<p class="ma__login-footer">
			<?php esc_html_e('Remembered it?', 'ai-zippy'); ?>
			<a href="<?php echo esc_url(wc_get_page_permalink('myaccount')); ?>" class="ma__link"><?php esc_html_e('Back to sign in', 'ai-zippy'); ?></a>
		</p>

	</div>
</div>

<?php do_action('woocommerce_after_lost_password_form'); ?>
