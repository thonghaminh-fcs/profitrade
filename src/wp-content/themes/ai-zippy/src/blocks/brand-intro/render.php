<?php

defined('ABSPATH') || exit;

$tagline    = $attributes['tagline'] ?? '';
$subtitle   = $attributes['subtitle'] ?? '';
$brand_name = $attributes['brandName'] ?? '';
$story      = $attributes['story'] ?? '';
$link_text  = $attributes['linkText'] ?? '';
$link_url   = $attributes['linkUrl'] ?? '#';
$hero_url   = $attributes['heroImageUrl'] ?? '';
$bg_color   = $attributes['bgColor'] ?? '#f5f0eb';

$wrapper = get_block_wrapper_attributes(['class' => 'bi']);
?>
<div <?php echo $wrapper; ?>>

	<!-- Hero Image + Arch -->
	<div class="bi__hero">
		<?php if ($hero_url) : ?>
			<img class="bi__hero-img" src="<?php echo esc_url($hero_url); ?>" alt="" loading="lazy" />
		<?php endif; ?>

		<div class="bi__arch">
			<h2 class="bi__tagline"><?php echo wp_kses_post(nl2br($tagline)); ?></h2>
		</div>
	</div>

	<!-- Subtitle -->
	<div class="bi__subtitle-wrap">
		<p class="bi__subtitle"><?php echo wp_kses_post($subtitle); ?></p>
	</div>

	<!-- Brand Story -->
	<div class="bi__story" style="background-color: <?php echo esc_attr($bg_color); ?>">
		<h2 class="bi__brand-name"><?php echo wp_kses_post($brand_name); ?></h2>
		<p class="bi__story-text"><?php echo wp_kses_post($story); ?></p>

		<?php if ($link_text && $link_url) : ?>
			<a class="bi__link" href="<?php echo esc_url($link_url); ?>">
				<?php echo esc_html($link_text); ?> &rarr;
			</a>
		<?php endif; ?>
	</div>
</div>
