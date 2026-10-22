<?php

/**
 * Contact Inquiry Form — frontend render.
 *
 * Markup quoted from .mockup/slim.html:377-408, copy and values from
 * docs/build/blocks/contact-form.md. The block wrapper is the white card; it is
 * column 2 of the contact page grid, which the page composition owns. The
 * success pane ships hidden — view.js swaps it in on submit and swaps the form
 * back on "Send another" (logic.js:139-140). Submission is real: view.js posts
 * the form to admin-ajax (action sm_contact_submit, handler in the theme's
 * inc/contact-form.php) with the nonce printed on the wrapper below, and only
 * shows the success pane when the server confirms the mail was sent.
 *
 * This file ships structural markup only; colours, spacing and typography live
 * in src/scss/sections/_contact-form.scss.
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Inner blocks (unused).
 * @var WP_Block $block      Block instance.
 */

defined('ABSPATH') || exit;

$title         = isset($attributes['title']) ? trim((string) $attributes['title']) : '';
$success_title = isset($attributes['successTitle']) ? trim((string) $attributes['successTitle']) : '';
$success_body  = isset($attributes['successBody']) ? trim((string) $attributes['successBody']) : '';
?>
<div <?php echo get_block_wrapper_attributes([
	'class'           => 'sm-contact-form',
	'data-ajax-url'   => admin_url('admin-ajax.php'),
	'data-nonce'      => wp_create_nonce('sm_contact_submit'),
]); ?>>

	<div class="sm-contact-form__success" hidden>
		<div class="sm-contact-form__check" aria-hidden="true">✓</div>
		<?php if ('' !== $success_title) : ?>
			<p class="sm-contact-form__success-title" tabindex="-1"><?php echo esc_html($success_title); ?></p>
		<?php endif; ?>
		<?php if ('' !== $success_body) : ?>
			<p class="sm-contact-form__success-body"><?php echo esc_html($success_body); ?></p>
		<?php endif; ?>
		<button type="button" class="sm-contact-form__reset"><?php echo esc_html__('Send another', 'ai-zippy-child'); ?></button>
	</div>

	<form class="sm-contact-form__form" method="post">
		<?php if ('' !== $title) : ?>
			<h2 class="sm-contact-form__title"><?php echo esc_html($title); ?></h2>
		<?php endif; ?>

		<label class="sm-contact-form__field">
			<?php echo esc_html__('Name', 'ai-zippy-child'); ?>
			<input class="sm-contact-form__input" type="text" name="name" required autocomplete="name" placeholder="<?php echo esc_attr__('Your name', 'ai-zippy-child'); ?>">
		</label>

		<label class="sm-contact-form__field">
			<?php echo esc_html__('Email', 'ai-zippy-child'); ?>
			<input class="sm-contact-form__input" type="email" name="email" required autocomplete="email" placeholder="<?php echo esc_attr__('you@email.com', 'ai-zippy-child'); ?>">
		</label>

		<label class="sm-contact-form__field">
			<?php echo esc_html__('Message', 'ai-zippy-child'); ?>
			<textarea class="sm-contact-form__textarea" name="message" rows="5" required placeholder="<?php echo esc_attr__('How can we help?', 'ai-zippy-child'); ?>"></textarea>
		</label>

		<!-- Honeypot: invisible to people, tempting to bots. -->
		<input class="sm-contact-form__hp" type="text" name="company" tabindex="-1" autocomplete="off" aria-hidden="true">

		<button type="submit" class="sm-contact-form__submit"><?php echo esc_html__('Send message', 'ai-zippy-child'); ?></button>
	</form>

</div>
