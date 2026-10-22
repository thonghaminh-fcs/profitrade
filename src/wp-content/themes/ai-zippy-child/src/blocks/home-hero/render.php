<?php

/**
 * Home Hero — frontend render.
 *
 * Markup quoted from .mockup/slim.html:98-132; copy and values from
 * docs/build/blocks/home-hero.md. PHP prints the default (no-JS) state of the
 * floating Next-slot card ("Today, 2:00 – 5:00 PM", slot index 2); view.js
 * keeps [data-sm-hero-slot] in sync with the delivery state owned by child.js
 * (sessionStorage "sm-delivery" + "sm:delivery" event).
 *
 * This file ships structural markup only; colours, spacing and typography live
 * in src/scss/sections/_home-hero.scss.
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Inner blocks (unused).
 * @var WP_Block $block      Block instance.
 */

defined('ABSPATH') || exit;

$eyebrow    = isset($attributes['eyebrow']) ? trim((string) $attributes['eyebrow']) : '';
$heading    = isset($attributes['heading']) ? trim((string) $attributes['heading']) : '';
$highlight  = isset($attributes['highlightText']) ? trim((string) $attributes['highlightText']) : '';
$paragraph  = isset($attributes['paragraph']) ? trim((string) $attributes['paragraph']) : '';
$prim_label = isset($attributes['primaryLabel']) ? trim((string) $attributes['primaryLabel']) : '';
$prim_url   = isset($attributes['primaryUrl']) ? trim((string) $attributes['primaryUrl']) : '';
$sec_label  = isset($attributes['secondaryLabel']) ? trim((string) $attributes['secondaryLabel']) : '';
$sec_url    = isset($attributes['secondaryUrl']) ? trim((string) $attributes['secondaryUrl']) : '';
$slot_label = isset($attributes['nextSlotLabel']) ? trim((string) $attributes['nextSlotLabel']) : '';
if ('' === $slot_label) {
	$slot_label = 'Next slot';
}

// Image: Media Library selection first, then the semantic-name resolver.
$image_id  = isset($attributes['imageId']) ? (int) $attributes['imageId'] : 0;
$image_url = $image_id ? (string) wp_get_attachment_image_url($image_id, 'large') : '';
if ('' === $image_url) {
	$image_url = isset($attributes['imageUrl']) ? trim((string) $attributes['imageUrl']) : '';
}
if ('' === $image_url && function_exists('ai_zippy_child_img')) {
	$image_url = ai_zippy_child_img('hero-shelves.jpg');
}

$image_alt = $image_id ? (string) get_post_meta($image_id, '_wp_attachment_image_alt', true) : '';
if ('' === $image_alt) {
	$image_alt = __('Mini-mart shelves', 'ai-zippy-child');
}

// Server-rendered default (no-JS/editor state); view.js rewrites it from the
// delivery state. Mirrors slot index 2 in src/js/child.js.
$slot_value = __('Today, 2:00 – 5:00 PM', 'ai-zippy-child');
?>
<div <?php echo get_block_wrapper_attributes(['class' => 'sm-hero']); ?>>

	<div class="sm-hero__text">
		<?php if ('' !== $eyebrow) : ?>
			<span class="sm-hero__eyebrow"><?php echo esc_html($eyebrow); ?></span>
		<?php endif; ?>

		<h1 class="sm-hero__title"><?php echo esc_html($heading); ?><?php if ('' !== $highlight) : ?> <span class="sm-hero__highlight"><?php echo esc_html($highlight); ?></span><?php endif; ?></h1>

		<?php if ('' !== $paragraph) : ?>
			<p class="sm-hero__lead"><?php echo esc_html($paragraph); ?></p>
		<?php endif; ?>

		<div class="sm-hero__ctas">
			<?php if ('' !== $prim_label && '' !== $prim_url) : ?>
				<a class="sm-hero__cta sm-hero__cta--primary" href="<?php echo esc_url($prim_url); ?>"><?php echo esc_html($prim_label); ?></a>
			<?php endif; ?>
			<?php if ('' !== $sec_label && '' !== $sec_url) : ?>
				<a class="sm-hero__cta sm-hero__cta--secondary" href="<?php echo esc_url($sec_url); ?>"><?php echo esc_html($sec_label); ?></a>
			<?php endif; ?>
		</div>
	</div>

	<div class="sm-hero__media">
		<div class="sm-hero__blob sm-hero__blob--green" aria-hidden="true"></div>
		<div class="sm-hero__blob sm-hero__blob--orange" aria-hidden="true"></div>
		<div class="sm-hero__photo">
			<?php if ('' !== $image_url) : ?>
				<img class="sm-hero__img" src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($image_alt); ?>">
			<?php endif; ?>
		</div>
		<div class="sm-hero__dot" aria-hidden="true"></div>
		<div class="sm-hero__slot-card">
			<span class="sm-hero__slot-eyebrow"><?php echo esc_html($slot_label); ?></span>
			<span class="sm-hero__slot-value" data-sm-hero-slot><?php echo esc_html($slot_value); ?></span>
		</div>
	</div>

</div>
