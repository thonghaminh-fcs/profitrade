<?php

/**
 * My Account Login — AI Zippy Override
 *
 * Tabbed sign-in / register card.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package AiZippy
 * @version 9.9.0
 */

defined('ABSPATH') || exit;

// Redirect guests to the dedicated /login/ page if it exists.
if (!is_user_logged_in()) {
    $login_page = get_page_by_path('login');
    if ($login_page) {
        wp_safe_redirect(get_permalink($login_page->ID));
        exit;
    }
}

$registration_enabled = 'yes' === get_option('woocommerce_enable_myaccount_registration');
$gen_username         = 'yes' === get_option('woocommerce_registration_generate_username');
$gen_password         = 'yes' === get_option('woocommerce_registration_generate_password');

// If the POST request was a register attempt (and failed validation), default to the register tab.
$initial_tab = (!empty($_POST['register'])) ? 'register' : 'login';

do_action('woocommerce_before_customer_login_form');
?>

<div class="ma__login-wrap">

	<div class="ma__login-card" data-initial-tab="<?php echo esc_attr($initial_tab); ?>">

		<!-- Brand mark -->
		<div class="ma__login-brand">
			<div class="ma__login-brand-icon">
				<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
			</div>
		</div>

		<?php if ($registration_enabled) : ?>
		<!-- Tab switcher -->
		<div class="ma__auth-tabs" role="tablist">
			<button type="button" class="ma__auth-tab is-active" data-tab="login" role="tab" aria-selected="true">
				<?php esc_html_e('Sign In', 'ai-zippy'); ?>
			</button>
			<button type="button" class="ma__auth-tab" data-tab="register" role="tab" aria-selected="false">
				<?php esc_html_e('Register', 'ai-zippy'); ?>
			</button>
			<span class="ma__auth-tabs-slider" aria-hidden="true"></span>
		</div>
		<?php endif; ?>

		<!-- ================= LOGIN PANEL ================= -->
		<div class="ma__auth-panel is-active" data-panel="login">

			<div class="ma__login-header">
				<h2 class="ma__login-title"><?php esc_html_e('Welcome back', 'ai-zippy'); ?></h2>
				<p class="ma__login-sub"><?php esc_html_e('Sign in to manage your orders and account.', 'ai-zippy'); ?></p>
			</div>

			<form class="ma__login-form" method="post" novalidate>

				<?php do_action('woocommerce_login_form_start'); ?>

				<div class="ma__field">
					<label class="ma__label" for="username">
						<?php esc_html_e('Username or email', 'ai-zippy'); ?>
					</label>
					<div class="ma__input-wrap ma__input-wrap--icon">
						<span class="ma__input-icon" aria-hidden="true">
							<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
						</span>
						<input
							type="text"
							class="ma__input"
							name="username"
							id="username"
							autocomplete="username"
							placeholder="<?php esc_attr_e('you@example.com', 'ai-zippy'); ?>"
							required
							aria-required="true"
							value="<?php echo (!empty($_POST['username']) && is_string($_POST['username'])) ? esc_attr(wp_unslash($_POST['username'])) : ''; ?>"
						/>
					</div>
				</div>

				<div class="ma__field">
					<label class="ma__label" for="password">
						<?php esc_html_e('Password', 'ai-zippy'); ?>
					</label>
					<div class="ma__input-wrap ma__input-wrap--icon">
						<span class="ma__input-icon" aria-hidden="true">
							<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
						</span>
						<input
							class="ma__input"
							type="password"
							name="password"
							id="password"
							autocomplete="current-password"
							placeholder="<?php esc_attr_e('Enter your password', 'ai-zippy'); ?>"
							required
							aria-required="true"
						/>
						<button type="button" class="ma__toggle-pw" aria-label="<?php esc_attr_e('Toggle password visibility', 'ai-zippy'); ?>">
							<svg class="ma__eye-show" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
							<svg class="ma__eye-hide" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
						</button>
					</div>
				</div>

				<?php do_action('woocommerce_login_form'); ?>

				<div class="ma__login-row">
					<label class="ma__checkbox-label">
						<input type="checkbox" name="rememberme" id="rememberme" value="forever" class="ma__checkbox" />
						<span><?php esc_html_e('Remember me', 'ai-zippy'); ?></span>
					</label>
					<a href="<?php echo esc_url(wp_lostpassword_url()); ?>" class="ma__lost-pw">
						<?php esc_html_e('Forgot password?', 'ai-zippy'); ?>
					</a>
				</div>

				<?php wp_nonce_field('woocommerce-login', 'woocommerce-login-nonce'); ?>

				<button type="submit" name="login" value="<?php esc_attr_e('Log in', 'woocommerce'); ?>" class="ma__btn ma__btn--primary ma__btn--full ma__btn--lg">
					<?php esc_html_e('Sign In', 'ai-zippy'); ?>
					<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="9 18 15 12 9 6"/></svg>
				</button>

				<?php do_action('woocommerce_login_form_end'); ?>

			</form>

			<?php if ($registration_enabled) : ?>
			<p class="ma__login-footer">
				<?php esc_html_e("Don't have an account?", 'ai-zippy'); ?>
				<button type="button" class="ma__link" data-switch-tab="register"><?php esc_html_e('Create one', 'ai-zippy'); ?></button>
			</p>
			<?php endif; ?>

		</div>

		<?php if ($registration_enabled) : ?>
		<!-- ================= REGISTER PANEL ================= -->
		<div class="ma__auth-panel" data-panel="register" hidden>

			<div class="ma__login-header">
				<h2 class="ma__login-title"><?php esc_html_e('Create your account', 'ai-zippy'); ?></h2>
				<p class="ma__login-sub"><?php esc_html_e('Sign up to track orders and save your details.', 'ai-zippy'); ?></p>
			</div>

			<form method="post" class="ma__login-form" <?php do_action('woocommerce_register_form_tag'); ?>>

				<?php do_action('woocommerce_register_form_start'); ?>

				<?php if (!$gen_username) : ?>
				<div class="ma__field">
					<label class="ma__label" for="reg_username"><?php esc_html_e('Username', 'ai-zippy'); ?></label>
					<div class="ma__input-wrap ma__input-wrap--icon">
						<span class="ma__input-icon" aria-hidden="true">
							<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
						</span>
						<input type="text" class="ma__input" name="username" id="reg_username" autocomplete="username" placeholder="<?php esc_attr_e('Choose a username', 'ai-zippy'); ?>" required aria-required="true" value="<?php echo (!empty($_POST['username'])) ? esc_attr(wp_unslash($_POST['username'])) : ''; ?>" />
					</div>
				</div>
				<?php endif; ?>

				<div class="ma__field">
					<label class="ma__label" for="reg_email"><?php esc_html_e('Email address', 'ai-zippy'); ?></label>
					<div class="ma__input-wrap ma__input-wrap--icon">
						<span class="ma__input-icon" aria-hidden="true">
							<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><polyline points="22,4 12,13 2,4"/></svg>
						</span>
						<input type="email" class="ma__input" name="email" id="reg_email" autocomplete="email" placeholder="<?php esc_attr_e('you@example.com', 'ai-zippy'); ?>" required aria-required="true" value="<?php echo (!empty($_POST['email'])) ? esc_attr(wp_unslash($_POST['email'])) : ''; ?>" />
					</div>
				</div>

				<?php if (!$gen_password) : ?>
				<div class="ma__field">
					<label class="ma__label" for="reg_password"><?php esc_html_e('Password', 'ai-zippy'); ?></label>
					<div class="ma__input-wrap ma__input-wrap--icon">
						<span class="ma__input-icon" aria-hidden="true">
							<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
						</span>
						<input type="password" class="ma__input" name="password" id="reg_password" autocomplete="new-password" placeholder="<?php esc_attr_e('Create a strong password', 'ai-zippy'); ?>" required aria-required="true" />
						<button type="button" class="ma__toggle-pw" aria-label="<?php esc_attr_e('Toggle password visibility', 'ai-zippy'); ?>">
							<svg class="ma__eye-show" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
							<svg class="ma__eye-hide" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
						</button>
					</div>
					<span class="ma__hint"><?php esc_html_e('Use at least 8 characters with a mix of letters and numbers.', 'ai-zippy'); ?></span>
				</div>
				<?php else : ?>
				<div class="ma__info-note">
					<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
					<span><?php esc_html_e('A link to set your password will be sent to your email.', 'ai-zippy'); ?></span>
				</div>
				<?php endif; ?>

				<?php do_action('woocommerce_register_form'); ?>

				<?php wp_nonce_field('woocommerce-register', 'woocommerce-register-nonce'); ?>

				<button type="submit" name="register" value="<?php esc_attr_e('Register', 'woocommerce'); ?>" class="ma__btn ma__btn--primary ma__btn--full ma__btn--lg">
					<?php esc_html_e('Create Account', 'ai-zippy'); ?>
					<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="9 18 15 12 9 6"/></svg>
				</button>

				<?php do_action('woocommerce_register_form_end'); ?>

			</form>

			<p class="ma__login-footer">
				<?php esc_html_e('Already have an account?', 'ai-zippy'); ?>
				<button type="button" class="ma__link" data-switch-tab="login"><?php esc_html_e('Sign in', 'ai-zippy'); ?></button>
			</p>

		</div>
		<?php endif; ?>

	</div>

</div>

<script>
(function () {
	var card = document.querySelector('.ma__login-card');
	if (!card) return;

	// --- Tab switcher ---
	function activateTab(name) {
		card.querySelectorAll('.ma__auth-tab').forEach(function (btn) {
			var on = btn.dataset.tab === name;
			btn.classList.toggle('is-active', on);
			btn.setAttribute('aria-selected', on ? 'true' : 'false');
		});
		card.querySelectorAll('.ma__auth-panel').forEach(function (panel) {
			var on = panel.dataset.panel === name;
			panel.classList.toggle('is-active', on);
			panel.hidden = !on;
		});
	}

	card.querySelectorAll('.ma__auth-tab').forEach(function (btn) {
		btn.addEventListener('click', function () {
			activateTab(btn.dataset.tab);
		});
	});

	card.querySelectorAll('[data-switch-tab]').forEach(function (btn) {
		btn.addEventListener('click', function (e) {
			e.preventDefault();
			activateTab(btn.dataset.switchTab);
		});
	});

	// Honor POST-driven initial tab (e.g. after failed register)
	if (card.dataset.initialTab === 'register') {
		activateTab('register');
	}

	// --- Password visibility toggle ---
	card.querySelectorAll('.ma__toggle-pw').forEach(function (btn) {
		btn.addEventListener('click', function () {
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

<?php do_action('woocommerce_after_customer_login_form'); ?>
