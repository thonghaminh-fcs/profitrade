<?php

/**
 * Store Map & Hours — frontend render.
 *
 * Markup quoted from .mockup/slim.html:351-376 (left card of the contact grid);
 * copy and values from docs/build/blocks/contact-location.md. Static section:
 * an embedded OpenStreetMap iframe above an address cell and an hours cell.
 * No JS behaviour.
 *
 * This file ships structural markup only; colours, spacing and typography live
 * in src/scss/sections/_contact-location.scss.
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Inner blocks (unused).
 * @var WP_Block $block      Block instance.
 */

defined('ABSPATH') || exit;

$map_url = isset($attributes['mapEmbedUrl']) ? trim((string) $attributes['mapEmbedUrl']) : '';

/**
 * Normalise a repeating-lines attribute into a clean, zero-indexed list.
 *
 * @param mixed $lines Raw attribute value.
 * @return string[]
 */
if (!function_exists('sm_contact_location_lines')) {
	function sm_contact_location_lines($lines) {
		if (!is_array($lines)) {
			return [];
		}
		$lines = array_map(static function ($line) {
			return trim((string) $line);
		}, $lines);
		return array_values(array_filter($lines, static function ($line) {
			return '' !== $line;
		}));
	}
}

$address_lines = sm_contact_location_lines(isset($attributes['addressLines']) ? $attributes['addressLines'] : []);
$hours_lines   = sm_contact_location_lines(isset($attributes['hoursLines']) ? $attributes['hoursLines'] : []);
$pickup_note   = isset($attributes['pickupNote']) ? trim((string) $attributes['pickupNote']) : '';
?>
<div <?php echo get_block_wrapper_attributes(['class' => 'sm-contact-location']); ?>>

	<?php if ('' !== $map_url) : ?>
		<iframe
			class="sm-contact-location__map"
			title="<?php esc_attr_e('Store map', 'ai-zippy-child'); ?>"
			src="<?php echo esc_url($map_url); ?>"
			loading="lazy"
			referrerpolicy="no-referrer-when-downgrade"></iframe>
	<?php endif; ?>

	<div class="sm-contact-location__body">

		<?php if (!empty($address_lines)) : ?>
			<div class="sm-contact-location__cell">
				<span class="sm-contact-location__eyebrow"><?php esc_html_e('Address', 'ai-zippy-child'); ?></span>
				<span class="sm-contact-location__value"><?php
					foreach ($address_lines as $i => $line) {
						if ($i > 0) {
							echo '<br>';
						}
						echo esc_html($line);
					}
				?></span>
			</div>
		<?php endif; ?>

		<?php if (!empty($hours_lines)) : ?>
			<div class="sm-contact-location__cell">
				<span class="sm-contact-location__eyebrow"><?php esc_html_e('Hours', 'ai-zippy-child'); ?></span>
				<span class="sm-contact-location__value"><?php
					foreach ($hours_lines as $i => $line) {
						if ($i > 0) {
							echo '<br>';
						}
						echo esc_html($line);
					}
				?></span>
				<?php if ('' !== $pickup_note) : ?>
					<span class="sm-contact-location__note"><?php echo esc_html($pickup_note); ?></span>
				<?php endif; ?>
			</div>
		<?php endif; ?>

	</div>

</div>
