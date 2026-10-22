<?php

/**
 * Customer Reset Password Email — AI Zippy Override
 *
 * Self-contained standalone layout (bypasses the shared email-header/footer)
 * so the reset-password email gets its own branded look without affecting
 * every other WC transactional email.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package AiZippy
 * @version 10.4.0
 *
 * @var string $email_heading
 * @var string $user_login
 * @var string $reset_key
 * @var int    $user_id
 * @var string $blogname
 * @var string $additional_content
 * @var WC_Email $email
 */

defined('ABSPATH') || exit;

// Build the reset link
$reset_url = add_query_arg(
    [
        'key'   => $reset_key,
        'id'    => $user_id,
        'login' => rawurlencode($user_login),
    ],
    wc_get_endpoint_url('lost-password', '', wc_get_page_permalink('myaccount'))
);

// Branding
$store_name = get_bloginfo('name', 'display');
$logo_url   = '';
if (function_exists('get_custom_logo')) {
    $logo_id  = get_theme_mod('custom_logo');
    $logo_src = $logo_id ? wp_get_attachment_image_src($logo_id, 'full') : false;
    if ($logo_src) {
        $logo_url = $logo_src[0];
    }
}
$home_url     = home_url('/');
$shop_url     = wc_get_page_permalink('shop');
$account_url  = wc_get_page_permalink('myaccount');
$support_mail = get_option('woocommerce_email_from_address', get_option('admin_email'));
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=<?php bloginfo('charset'); ?>" />
	<meta content="width=device-width, initial-scale=1.0" name="viewport">
	<title><?php echo esc_html($email_heading); ?></title>
</head>
<body style="margin:0; padding:0; background:#f4f4f7; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif; color:#1a1a1a;">

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f4f4f7; padding:32px 16px;">
	<tr>
		<td align="center">

			<!-- Outer container -->
			<table role="presentation" width="560" cellpadding="0" cellspacing="0" border="0" style="max-width:560px; width:100%;">

				<!-- Brand header -->
				<tr>
					<td align="center" style="padding:0 0 20px;">
						<?php if ($logo_url) : ?>
							<a href="<?php echo esc_url($home_url); ?>" style="text-decoration:none;">
								<img src="<?php echo esc_url($logo_url); ?>" alt="<?php echo esc_attr($store_name); ?>" style="max-height:40px; width:auto; border:0; display:inline-block;" />
							</a>
						<?php else : ?>
							<a href="<?php echo esc_url($home_url); ?>" style="font-size:20px; font-weight:700; color:#1a1a1a; text-decoration:none; letter-spacing:-0.01em;">
								<?php echo esc_html($store_name); ?>
							</a>
						<?php endif; ?>
					</td>
				</tr>

				<!-- Main card -->
				<tr>
					<td style="background:#ffffff; border-radius:16px; border:1px solid rgba(0,0,0,0.06); padding:0; overflow:hidden;">

						<!-- Icon -->
						<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
							<tr>
								<td align="center" style="padding:40px 40px 8px;">
									<table role="presentation" cellpadding="0" cellspacing="0" border="0">
										<tr>
											<td align="center" style="width:64px; height:64px; background:#f5f0e6; border-radius:50%; color:#c8a97e;">
												<!-- Lock icon -->
												<img src="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='28' height='28' viewBox='0 0 24 24' fill='none' stroke='%23c8a97e' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><rect x='3' y='11' width='18' height='11' rx='2' ry='2'/><path d='M7 11V7a5 5 0 0 1 10 0v4'/></svg>" width="28" height="28" alt="" style="display:block;" />
											</td>
										</tr>
									</table>
								</td>
							</tr>

							<!-- Heading -->
							<tr>
								<td align="center" style="padding:20px 40px 8px;">
									<h1 style="margin:0; font-size:24px; font-weight:700; color:#1a1a1a; line-height:1.25; letter-spacing:-0.01em;">
										<?php esc_html_e('Reset your password', 'ai-zippy'); ?>
									</h1>
								</td>
							</tr>

							<!-- Greeting -->
							<tr>
								<td style="padding:16px 40px 0;">
									<p style="margin:0 0 12px; font-size:15px; line-height:1.6; color:#1a1a1a;">
										<?php printf(
											/* translators: %s: username */
											esc_html__('Hi %s,', 'ai-zippy'),
											'<strong>' . esc_html($user_login) . '</strong>'
										); ?>
									</p>
									<p style="margin:0 0 16px; font-size:15px; line-height:1.6; color:#555;">
										<?php printf(
											/* translators: %s: store name */
											esc_html__('We received a request to reset the password for your %s account. Click the button below to choose a new password.', 'ai-zippy'),
											'<strong>' . esc_html($blogname) . '</strong>'
										); ?>
									</p>
								</td>
							</tr>

							<!-- Account info pill -->
							<tr>
								<td style="padding:8px 40px 0;">
									<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f8f8fa; border-radius:10px;">
										<tr>
											<td style="padding:14px 16px;">
												<span style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.04em; color:#999999; display:block; margin-bottom:2px;">
													<?php esc_html_e('Account', 'ai-zippy'); ?>
												</span>
												<span style="font-size:14px; font-weight:600; color:#1a1a1a;">
													<?php echo esc_html($user_login); ?>
												</span>
											</td>
										</tr>
									</table>
								</td>
							</tr>

							<!-- CTA button -->
							<tr>
								<td align="center" style="padding:28px 40px 12px;">
									<table role="presentation" cellpadding="0" cellspacing="0" border="0">
										<tr>
											<td align="center" style="border-radius:10px; background:#1a1a1a;">
												<a href="<?php echo esc_url($reset_url); ?>"
													style="display:inline-block; padding:14px 32px; font-size:15px; font-weight:700; color:#ffffff; text-decoration:none; border-radius:10px; letter-spacing:0.01em;">
													<?php esc_html_e('Reset Password', 'ai-zippy'); ?> &rarr;
												</a>
											</td>
										</tr>
									</table>
								</td>
							</tr>

							<!-- Fallback link -->
							<tr>
								<td style="padding:0 40px 8px;">
									<p style="margin:0; font-size:12px; line-height:1.6; color:#999999; text-align:center;">
										<?php esc_html_e('Button not working? Copy and paste this link into your browser:', 'ai-zippy'); ?>
									</p>
									<p style="margin:6px 0 0; text-align:center;">
										<a href="<?php echo esc_url($reset_url); ?>" style="font-size:12px; color:#c8a97e; word-break:break-all; text-decoration:underline;">
											<?php echo esc_html($reset_url); ?>
										</a>
									</p>
								</td>
							</tr>

							<!-- Divider -->
							<tr>
								<td style="padding:24px 40px 0;">
									<div style="height:1px; background:#eeeeee; line-height:1px; font-size:1px;">&nbsp;</div>
								</td>
							</tr>

							<!-- Security notice -->
							<tr>
								<td style="padding:20px 40px 32px;">
									<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
										<tr>
											<td valign="top" style="width:24px; padding-top:2px;">
												<img src="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23999999' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><path d='M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z'/></svg>" width="16" height="16" alt="" style="display:block;" />
											</td>
											<td style="padding-left:8px;">
												<p style="margin:0 0 4px; font-size:13px; font-weight:700; color:#1a1a1a;">
													<?php esc_html_e('Didn\'t request this?', 'ai-zippy'); ?>
												</p>
												<p style="margin:0; font-size:13px; line-height:1.55; color:#777;">
													<?php esc_html_e('If you didn\'t request a password reset, you can safely ignore this email — your password will remain unchanged.', 'ai-zippy'); ?>
												</p>
												<p style="margin:8px 0 0; font-size:12px; line-height:1.55; color:#999;">
													<?php esc_html_e('This link will expire in 24 hours for security reasons.', 'ai-zippy'); ?>
												</p>
											</td>
										</tr>
									</table>
								</td>
							</tr>

							<?php if ($additional_content) : ?>
							<!-- Admin-configured additional content -->
							<tr>
								<td style="padding:0 40px 32px;">
									<div style="padding:16px 18px; background:#f8f8fa; border-radius:10px; font-size:13px; line-height:1.55; color:#555;">
										<?php echo wp_kses_post(wpautop(wptexturize($additional_content))); ?>
									</div>
								</td>
							</tr>
							<?php endif; ?>

						</table>

					</td>
				</tr>

				<!-- Footer -->
				<tr>
					<td style="padding:24px 16px 8px;" align="center">
						<p style="margin:0 0 6px; font-size:13px; color:#1a1a1a; font-weight:600;">
							<?php printf(esc_html__('Need help? Contact us at %s', 'ai-zippy'), '<a href="mailto:' . esc_attr($support_mail) . '" style="color:#c8a97e; text-decoration:none;">' . esc_html($support_mail) . '</a>'); ?>
						</p>
						<p style="margin:0 0 12px; font-size:12px; color:#999999;">
							<a href="<?php echo esc_url($shop_url); ?>" style="color:#999999; text-decoration:none; margin:0 8px;"><?php esc_html_e('Shop', 'ai-zippy'); ?></a>
							<span style="color:#dddddd;">&bull;</span>
							<a href="<?php echo esc_url($account_url); ?>" style="color:#999999; text-decoration:none; margin:0 8px;"><?php esc_html_e('My Account', 'ai-zippy'); ?></a>
							<span style="color:#dddddd;">&bull;</span>
							<a href="<?php echo esc_url($home_url); ?>" style="color:#999999; text-decoration:none; margin:0 8px;"><?php esc_html_e('Visit Site', 'ai-zippy'); ?></a>
						</p>
						<p style="margin:0; font-size:11px; color:#bbbbbb; line-height:1.5;">
							&copy; <?php echo esc_html(date('Y')); ?> <?php echo esc_html($store_name); ?>. <?php esc_html_e('All rights reserved.', 'ai-zippy'); ?><br>
							<?php esc_html_e('You are receiving this email because a password reset was requested for your account.', 'ai-zippy'); ?>
						</p>
					</td>
				</tr>

			</table>

		</td>
	</tr>
</table>

</body>
</html>
