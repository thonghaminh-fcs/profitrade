<?php

/**
 * Edit Account Form — AI Zippy Override
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package AiZippy
 * @version 10.5.0
 *
 * @var WP_User $user
 */

defined('ABSPATH') || exit;

do_action('woocommerce_before_edit_account_form');
?>

<div class="ma__section-header">
	<h2 class="ma__section-title"><?php esc_html_e('Account Details', 'ai-zippy'); ?></h2>
</div>

<form class="woocommerce-EditAccountForm edit-account ma__form" action="" method="post" <?php do_action('woocommerce_edit_account_form_tag'); ?>>

	<?php do_action('woocommerce_edit_account_form_start'); ?>

	<!-- Profile card -->
	<div class="ma__form-card">
		<h3 class="ma__form-card-title">
			<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
			<?php esc_html_e('Profile', 'ai-zippy'); ?>
		</h3>

		<div class="ma__form-grid">
			<div class="ma__field">
				<label class="ma__label" for="account_first_name">
					<?php esc_html_e('First name', 'woocommerce'); ?> <span class="ma__required" aria-hidden="true">*</span>
				</label>
				<input type="text" class="ma__input" name="account_first_name" id="account_first_name" autocomplete="given-name" value="<?php echo esc_attr($user->first_name); ?>" aria-required="true" />
			</div>

			<div class="ma__field">
				<label class="ma__label" for="account_last_name">
					<?php esc_html_e('Last name', 'woocommerce'); ?> <span class="ma__required" aria-hidden="true">*</span>
				</label>
				<input type="text" class="ma__input" name="account_last_name" id="account_last_name" autocomplete="family-name" value="<?php echo esc_attr($user->last_name); ?>" aria-required="true" />
			</div>

			<div class="ma__field ma__field--full">
				<label class="ma__label" for="account_display_name">
					<?php esc_html_e('Display name', 'woocommerce'); ?> <span class="ma__required" aria-hidden="true">*</span>
				</label>
				<input type="text" class="ma__input" name="account_display_name" id="account_display_name" value="<?php echo esc_attr($user->display_name); ?>" aria-required="true" />
				<span class="ma__hint"><?php esc_html_e('This will be how your name is displayed in the account section and reviews.', 'ai-zippy'); ?></span>
			</div>

			<div class="ma__field ma__field--full">
				<label class="ma__label" for="account_email">
					<?php esc_html_e('Email address', 'woocommerce'); ?> <span class="ma__required" aria-hidden="true">*</span>
				</label>
				<input type="email" class="ma__input" name="account_email" id="account_email" autocomplete="email" value="<?php echo esc_attr($user->user_email); ?>" aria-required="true" />
			</div>
		</div>

		<?php do_action('woocommerce_edit_account_form_fields'); ?>
	</div>

	<!-- Password card -->
	<div class="ma__form-card">
		<h3 class="ma__form-card-title">
			<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
			<?php esc_html_e('Change Password', 'ai-zippy'); ?>
		</h3>
		<p class="ma__form-card-sub"><?php esc_html_e('Leave blank to keep your current password.', 'ai-zippy'); ?></p>

		<div class="ma__form-grid">
			<div class="ma__field ma__field--full">
				<label class="ma__label" for="password_current"><?php esc_html_e('Current password', 'ai-zippy'); ?></label>
				<div class="ma__input-wrap">
					<input type="password" class="ma__input" name="password_current" id="password_current" autocomplete="current-password" />
					<button type="button" class="ma__toggle-pw" aria-label="<?php esc_attr_e('Toggle password visibility', 'ai-zippy'); ?>">
						<svg class="ma__eye-show" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
						<svg class="ma__eye-hide" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
					</button>
				</div>
			</div>

			<div class="ma__field">
				<label class="ma__label" for="password_1"><?php esc_html_e('New password', 'ai-zippy'); ?></label>
				<div class="ma__input-wrap">
					<input type="password" class="ma__input" name="password_1" id="password_1" autocomplete="new-password" />
					<button type="button" class="ma__toggle-pw" aria-label="<?php esc_attr_e('Toggle password visibility', 'ai-zippy'); ?>">
						<svg class="ma__eye-show" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
						<svg class="ma__eye-hide" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
					</button>
				</div>
			</div>

			<div class="ma__field">
				<label class="ma__label" for="password_2"><?php esc_html_e('Confirm new password', 'ai-zippy'); ?></label>
				<input type="password" class="ma__input" name="password_2" id="password_2" autocomplete="new-password" />
			</div>
		</div>
	</div>

	<?php do_action('woocommerce_edit_account_form'); ?>

	<div class="ma__form-actions">
		<?php wp_nonce_field('save_account_details', 'save-account-details-nonce'); ?>
		<input type="hidden" name="action" value="save_account_details" />
		<button type="submit" name="save_account_details" value="<?php esc_attr_e('Save changes', 'woocommerce'); ?>" class="ma__btn ma__btn--primary">
			<?php esc_html_e('Save Changes', 'ai-zippy'); ?>
		</button>
	</div>

	<?php do_action('woocommerce_edit_account_form_end'); ?>
</form>

<script>
(function(){
	document.querySelectorAll('.ma__form .ma__toggle-pw').forEach(function(btn){
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

<?php do_action('woocommerce_after_edit_account_form'); ?>
