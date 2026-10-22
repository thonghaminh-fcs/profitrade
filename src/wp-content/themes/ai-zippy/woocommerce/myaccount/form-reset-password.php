<?php

/**
 * Reset Password Form — AI Zippy Override
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package AiZippy
 * @version 9.2.0
 *
 * @var array $args
 */

defined('ABSPATH') || exit;

do_action('woocommerce_before_reset_password_form');
?>

<div class="ma__login-wrap">
	<div class="ma__login-card">

		<div class="ma__login-brand">
			<div class="ma__login-brand-icon">
				<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
			</div>
		</div>

		<div class="ma__login-header">
			<h2 class="ma__login-title"><?php esc_html_e('Create a new password', 'ai-zippy'); ?></h2>
			<p class="ma__login-sub">
				<?php echo esc_html(apply_filters(
					'woocommerce_reset_password_message',
					__('Choose a strong password you haven\'t used before.', 'ai-zippy')
				)); ?>
			</p>
		</div>

		<form method="post" class="ma__login-form woocommerce-ResetPassword lost_reset_password">

			<div class="ma__field">
				<label class="ma__label" for="password_1">
					<?php esc_html_e('New password', 'ai-zippy'); ?> <span class="ma__required" aria-hidden="true">*</span>
				</label>
				<div class="ma__input-wrap ma__input-wrap--icon">
					<span class="ma__input-icon" aria-hidden="true">
						<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
					</span>
					<input type="password" name="password_1" id="password_1" class="ma__input" autocomplete="new-password" placeholder="<?php esc_attr_e('At least 8 characters', 'ai-zippy'); ?>" required aria-required="true" />
					<button type="button" class="ma__toggle-pw" aria-label="<?php esc_attr_e('Toggle password visibility', 'ai-zippy'); ?>">
						<svg class="ma__eye-show" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
						<svg class="ma__eye-hide" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
					</button>
				</div>
			</div>

			<div class="ma__field">
				<label class="ma__label" for="password_2">
					<?php esc_html_e('Confirm new password', 'ai-zippy'); ?> <span class="ma__required" aria-hidden="true">*</span>
				</label>
				<div class="ma__input-wrap ma__input-wrap--icon">
					<span class="ma__input-icon" aria-hidden="true">
						<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
					</span>
					<input type="password" name="password_2" id="password_2" class="ma__input" autocomplete="new-password" placeholder="<?php esc_attr_e('Repeat password', 'ai-zippy'); ?>" required aria-required="true" />
				</div>
			</div>

			<input type="hidden" name="reset_key" value="<?php echo esc_attr($args['key']); ?>" />
			<input type="hidden" name="reset_login" value="<?php echo esc_attr($args['login']); ?>" />
			<input type="hidden" name="wc_reset_password" value="true" />

			<?php do_action('woocommerce_resetpassword_form'); ?>
			<?php wp_nonce_field('reset_password', 'woocommerce-reset-password-nonce'); ?>

			<button type="submit" value="<?php esc_attr_e('Save', 'woocommerce'); ?>" class="ma__btn ma__btn--primary ma__btn--full ma__btn--lg">
				<?php esc_html_e('Save Password', 'ai-zippy'); ?>
			</button>
		</form>

	</div>
</div>

<script>
(function(){
	document.querySelectorAll('.ma__login-form .ma__toggle-pw').forEach(function(btn){
		btn.addEventListener('click', function(){
			var wrap  = btn.closest('.ma__input-wrap');
			var input = wrap && wrap.querySelector('input');
			if (!input) return;
			var showing = input.type === 'text';
			input.type = showing ? 'password' : 'text';
			btn.querySelector('.ma__eye-show').style.display = showing ? '' : 'none';
			btn.querySelector('.ma__eye-hide').style.display = showing ? 'none' : '';
		});
	});
}());
</script>

<?php do_action('woocommerce_after_reset_password_form'); ?>
