<?php

/**
 * Late-Night CTA Banner — frontend render.
 *
 * Dark banner closing the home page; markup quoted from
 * .mockup/slim.html:194-211, copy and values from
 * docs/build/blocks/home-cta-banner.md. Pure static banner — no JS. The
 * mockup's <button sc-camel-on-click="{{ goShop }}"> becomes a real link to
 * the ctaUrl attribute (/shop/ by default).
 *
 * This file ships structural markup only; colours, spacing and typography live
 * in src/scss/sections/_home-cta-banner.scss.
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Inner blocks (unused).
 * @var WP_Block $block      Block instance.
 */

defined('ABSPATH') || exit;

$heading   = isset($attributes['heading']) ? trim((string) $attributes['heading']) : '';
$paragraph = isset($attributes['paragraph']) ? trim((string) $attributes['paragraph']) : '';
$cta_label = isset($attributes['ctaLabel']) ? trim((string) $attributes['ctaLabel']) : '';
$cta_url   = isset($attributes['ctaUrl']) ? trim((string) $attributes['ctaUrl']) : '';
?>
<div <?php echo get_block_wrapper_attributes(['class' => 'sm-cta-banner']); ?>>

	<div class="sm-cta-banner__panel">
		<div class="sm-cta-banner__blob sm-cta-banner__blob--red" aria-hidden="true"></div>
		<div class="sm-cta-banner__blob sm-cta-banner__blob--orange" aria-hidden="true"></div>
		<div class="sm-cta-banner__dot" aria-hidden="true"></div>

		<div class="sm-cta-banner__text">
			<?php if ('' !== $heading) : ?>
				<h2 class="sm-cta-banner__heading"><?php echo esc_html($heading); ?></h2>
			<?php endif; ?>
			<?php if ('' !== $paragraph) : ?>
				<p class="sm-cta-banner__lead"><?php echo esc_html($paragraph); ?></p>
			<?php endif; ?>
		</div>

		<?php if ('' !== $cta_label && '' !== $cta_url) : ?>
			<a class="sm-cta-banner__cta" href="<?php echo esc_url($cta_url); ?>"><?php echo esc_html($cta_label); ?></a>
		<?php endif; ?>
	</div>

</div>
