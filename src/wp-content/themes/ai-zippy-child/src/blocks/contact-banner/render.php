<?php

/**
 * WhatsApp Bulk-Pricing Banner — frontend render.
 *
 * Green banner opening the contact page; markup quoted from
 * .mockup/slim.html:336-350, copy and values from
 * docs/build/blocks/contact-banner.md. Pure static banner — no JS. The
 * mockup's `{{ waLink }}` binding (logic.js:138) becomes a wa.me URL built
 * from the whatsappNumber attribute with the same prefilled message.
 *
 * This file ships structural markup only; colours, spacing and typography live
 * in src/scss/sections/_contact-banner.scss.
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Inner blocks (unused).
 * @var WP_Block $block      Block instance.
 */

defined('ABSPATH') || exit;

$title     = isset($attributes['title']) ? trim((string) $attributes['title']) : '';
$body      = isset($attributes['body']) ? trim((string) $attributes['body']) : '';
$cta_label = isset($attributes['ctaLabel']) ? trim((string) $attributes['ctaLabel']) : '';
$wa_number = isset($attributes['whatsappNumber']) ? preg_replace('/[^0-9]/', '', (string) $attributes['whatsappNumber']) : '';

$wa_url = '';
if ('' !== $wa_number) {
	$wa_url = 'https://wa.me/' . rawurlencode($wa_number)
		. '?text=' . rawurlencode('Hi S.Mart, I would like carton/bulk pricing for:');
}
?>
<div <?php echo get_block_wrapper_attributes(['class' => 'sm-contact-banner']); ?>>

	<div class="sm-contact-banner__panel">
		<div class="sm-contact-banner__blob" aria-hidden="true"></div>

		<div class="sm-contact-banner__text">
			<?php if ('' !== $title) : ?>
				<h2 class="sm-contact-banner__title"><?php echo esc_html($title); ?></h2>
			<?php endif; ?>
			<?php if ('' !== $body) : ?>
				<p class="sm-contact-banner__body"><?php echo esc_html($body); ?></p>
			<?php endif; ?>
		</div>

		<?php if ('' !== $cta_label && '' !== $wa_url) : ?>
			<a class="sm-contact-banner__cta" href="<?php echo esc_url($wa_url); ?>" target="_blank" rel="noopener">
				<span class="sm-contact-banner__cta-dot" aria-hidden="true"></span><?php echo esc_html($cta_label); ?>
			</a>
		<?php endif; ?>
	</div>

</div>
