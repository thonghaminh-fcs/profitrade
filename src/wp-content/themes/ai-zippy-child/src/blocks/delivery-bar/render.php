<?php

/**
 * Delivery Bar — frontend render.
 *
 * Dark bar under the header brand row; markup quoted from .mockup/slim.html:66-94.
 * PHP prints the default (no-JS) state: the configured slot selected, express
 * off, and the matching delivery note. child.js owns the live {slot, express}
 * delivery state and re-renders through the data-sm-* hooks below — the hook
 * names are shared with the mini-cart and checkout-fulfilment blocks, so do not
 * rename them.
 *
 * This file ships structural markup only; colours, spacing and typography live
 * in src/scss/sections/_delivery-bar.scss.
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Inner blocks (unused).
 * @var WP_Block $block      Block instance.
 */

defined('ABSPATH') || exit;

// The five 3-hour slots, in order (logic.js:37).
$slots = [
	'8:00 – 11:00 AM',
	'11:00 AM – 2:00 PM',
	'2:00 – 5:00 PM',
	'5:00 – 8:00 PM',
	'8:00 – 11:00 PM',
];

$open_hours = isset($attributes['openHoursText']) ? trim((string) $attributes['openHoursText']) : '';
if ('' === $open_hours) {
	$open_hours = 'Open today 8:00 AM – 12:00 AM';
}

$fee       = isset($attributes['expressFee']) ? (float) $attributes['expressFee'] : 20;
$fee_label = '$' . number_format($fee, 2, '.', '');

$slot_index = isset($attributes['defaultSlot']) ? (int) $attributes['defaultSlot'] : 2;
if (!array_key_exists($slot_index, $slots)) {
	$slot_index = 0;
}

// Mirrors deliveryNote() in src/js/child.js: the slot label, lower-cased.
$note = 'Delivering today, ' . strtolower($slots[$slot_index]);

$select_id = wp_unique_id('sm-delivery-slot-');
?>
<div <?php echo get_block_wrapper_attributes(['class' => 'sm-delivery-bar']); ?>>
	<div class="sm-delivery-bar__inner">

		<span class="sm-delivery-bar__status">
			<span class="sm-delivery-bar__dot" aria-hidden="true"></span><?php echo esc_html($open_hours); ?>
		</span>

		<div class="sm-delivery-bar__slot" data-sm-slot-wrap>
			<label class="sm-delivery-bar__slot-label" for="<?php echo esc_attr($select_id); ?>"><?php esc_html_e('3-hour slot', 'ai-zippy-child'); ?></label>
			<select class="sm-delivery-bar__select" id="<?php echo esc_attr($select_id); ?>" data-sm-slot>
				<?php foreach ($slots as $index => $label) : ?>
					<option value="<?php echo esc_attr((string) $index); ?>"<?php selected($index, $slot_index); ?>><?php echo esc_html($label); ?></option>
				<?php endforeach; ?>
			</select>
		</div>

		<button class="sm-delivery-bar__express" type="button" role="switch" aria-checked="false" data-sm-express>
			<span class="sm-delivery-bar__track" aria-hidden="true"><span class="sm-delivery-bar__knob"></span></span>
			<?php esc_html_e('1-Hour Express', 'ai-zippy-child'); ?>
			<span class="sm-delivery-bar__fee">+<?php echo esc_html($fee_label); ?></span>
		</button>

		<span class="sm-delivery-bar__note" data-sm-note><?php echo esc_html($note); ?></span>

	</div>
</div>
