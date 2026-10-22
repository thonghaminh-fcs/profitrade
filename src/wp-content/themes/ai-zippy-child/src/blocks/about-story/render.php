<?php

/**
 * About Story — frontend render.
 *
 * Markup quoted from .mockup/slim.html:296-314; copy and values from
 * docs/build/blocks/about-story.md. Static section: eyebrow pill, headline,
 * paragraph list and the red/green blob image composition. No JS behaviour.
 *
 * This file ships structural markup only; colours, spacing and typography live
 * in src/scss/sections/_about-story.scss.
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Inner blocks (unused).
 * @var WP_Block $block      Block instance.
 */

defined('ABSPATH') || exit;

$eyebrow    = isset($attributes['eyebrow']) ? trim((string) $attributes['eyebrow']) : '';
$heading    = isset($attributes['heading']) ? trim((string) $attributes['heading']) : '';
$paragraphs = isset($attributes['paragraphs']) && is_array($attributes['paragraphs']) ? $attributes['paragraphs'] : [];
$paragraphs = array_values(array_filter(array_map(static function ($text) {
	return trim((string) $text);
}, $paragraphs), static function ($text) {
	return '' !== $text;
}));

// Image: Media Library selection first, then the semantic-name resolver.
$image_id  = isset($attributes['imageId']) ? (int) $attributes['imageId'] : 0;
$image_url = $image_id ? (string) wp_get_attachment_image_url($image_id, 'large') : '';
if ('' === $image_url) {
	$image_url = isset($attributes['imageUrl']) ? trim((string) $attributes['imageUrl']) : '';
}
if ('' === $image_url && function_exists('ai_zippy_child_img')) {
	$image_url = ai_zippy_child_img('store-aisle.jpg');
}

$image_alt = $image_id ? (string) get_post_meta($image_id, '_wp_attachment_image_alt', true) : '';
if ('' === $image_alt) {
	$image_alt = __('S.Mart store aisle', 'ai-zippy-child');
}
?>
<div <?php echo get_block_wrapper_attributes(['class' => 'sm-about-story']); ?>>

	<div class="sm-about-story__text">
		<?php if ('' !== $eyebrow) : ?>
			<span class="sm-about-story__eyebrow"><?php echo esc_html($eyebrow); ?></span>
		<?php endif; ?>

		<h1 class="sm-about-story__title"><?php echo esc_html($heading); ?></h1>

		<?php foreach ($paragraphs as $paragraph) : ?>
			<p class="sm-about-story__para"><?php echo wp_kses_post($paragraph); ?></p>
		<?php endforeach; ?>
	</div>

	<div class="sm-about-story__media">
		<div class="sm-about-story__blob sm-about-story__blob--red" aria-hidden="true"></div>
		<div class="sm-about-story__blob sm-about-story__blob--green" aria-hidden="true"></div>
		<?php if ('' !== $image_url) : ?>
			<img class="sm-about-story__img" src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($image_alt); ?>">
		<?php endif; ?>
	</div>

</div>
