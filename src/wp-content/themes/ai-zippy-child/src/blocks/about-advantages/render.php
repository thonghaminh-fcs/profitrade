<?php

/**
 * Why S.Mart by Profitrade — frontend render.
 *
 * About-page advantages grid; markup quoted from .mockup/slim.html:315-330,
 * copy and values from docs/build/blocks/about-advantages.md (logic.js ADV).
 * Pure static section — no JS. The mockup's <sc-for list="{{ advantages }}">
 * becomes a PHP loop over the `items` attribute; per-item blob colours are
 * passed as CSS custom properties consumed by
 * src/scss/sections/_about-advantages.scss.
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Inner blocks (unused).
 * @var WP_Block $block      Block instance.
 */

defined('ABSPATH') || exit;

$sm_heading = isset($attributes['heading']) ? trim((string) $attributes['heading']) : '';
$sm_items   = isset($attributes['items']) && is_array($attributes['items']) ? $attributes['items'] : [];
?>
<div <?php echo get_block_wrapper_attributes(['class' => 'sm-advantages']); ?>>

	<?php if ('' !== $sm_heading) : ?>
		<h2 class="sm-advantages__heading"><?php echo esc_html($sm_heading); ?></h2>
	<?php endif; ?>

	<div class="sm-advantages__grid">
		<?php foreach ($sm_items as $sm_item) : ?>
			<?php
			if (!is_array($sm_item)) {
				continue;
			}
			$sm_number = isset($sm_item['number']) ? trim((string) $sm_item['number']) : '';
			$sm_title  = isset($sm_item['title']) ? trim((string) $sm_item['title']) : '';
			$sm_body   = isset($sm_item['body']) ? trim((string) $sm_item['body']) : '';
			$sm_color  = isset($sm_item['color']) ? trim((string) $sm_item['color']) : '';
			$sm_fg     = isset($sm_item['foreground']) ? trim((string) $sm_item['foreground']) : '';
			?>
			<div class="sm-advantages__card">
				<?php if ('' !== $sm_number) : ?>
					<div
						class="sm-advantages__blob"
						style="--sm-adv-blob-bg:<?php echo esc_attr($sm_color); ?>;--sm-adv-blob-fg:<?php echo esc_attr($sm_fg); ?>"
					><?php echo esc_html($sm_number); ?></div>
				<?php endif; ?>
				<?php if ('' !== $sm_title) : ?>
					<div class="sm-advantages__title"><?php echo esc_html($sm_title); ?></div>
				<?php endif; ?>
				<?php if ('' !== $sm_body) : ?>
					<div class="sm-advantages__body"><?php echo esc_html($sm_body); ?></div>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>
	</div>

</div>
