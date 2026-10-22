<?php

/**
 * Today's Promotions — frontend render.
 *
 * Horizontal snap-scroll scroller of promo cards; markup quoted from
 * .mockup/slim.html:155-193. The mockup's click-handler divs become real
 * <a href="/shop/"> links. Cards flagged `countdown` print a server-computed
 * HH:MM:SS-to-midnight as the no-JS state; view.js owns the live 1s tick
 * through the [data-sm-countdown] hook (logic.js:57,115-117,132).
 *
 * This file ships structural markup only; colours, spacing and typography live
 * in src/scss/sections/_home-promotions.scss. Per-card colours are attributes,
 * so they are printed as inline styles.
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Inner blocks (unused).
 * @var WP_Block $block      Block instance.
 */

defined('ABSPATH') || exit;

if (!function_exists('sm_home_promotions_countdown')) :
	/**
	 * Seconds left until the next local midnight, as HH:MM:SS.
	 * Mirrors the countdown getter in .mockup/logic.js:115-117,132.
	 */
	function sm_home_promotions_countdown(): string
	{
		$since_midnight = (int) current_time('timestamp') % DAY_IN_SECONDS;
		$left           = DAY_IN_SECONDS - $since_midnight;
		return sprintf('%02d:%02d:%02d', floor($left / 3600), floor(($left % 3600) / 60), $left % 60);
	}
endif;

if (!function_exists('sm_home_promotions_image_url')) :
	/**
	 * Card image source: Media Library pick (imageId) > editor URL (imageUrl)
	 * > seeded mockup name resolved through ai_zippy_child_img().
	 */
	function sm_home_promotions_image_url(array $card): string
	{
		$id = isset($card['imageId']) ? (int) $card['imageId'] : 0;
		if ($id > 0) {
			$url = wp_get_attachment_image_url($id, 'large');
			if ($url) {
				return $url;
			}
		}
		if (!empty($card['imageUrl'])) {
			return (string) $card['imageUrl'];
		}
		$name = !empty($card['image']) ? (string) $card['image'] : 'product-fallback.jpg';
		return function_exists('ai_zippy_child_img') ? ai_zippy_child_img($name) : '';
	}
endif;

$heading = isset($attributes['heading']) ? trim((string) $attributes['heading']) : '';
if ('' === $heading) {
	$heading = "Today's promotions";
}

$cards = (isset($attributes['cards']) && is_array($attributes['cards'])) ? array_values($attributes['cards']) : [];

$shop_url  = home_url('/shop/');
$countdown = '';
?>
<div <?php echo get_block_wrapper_attributes(['class' => 'sm-promotions']); ?>>
	<h2 class="sm-promotions__heading"><?php echo esc_html($heading); ?></h2>
	<div class="sm-promotions__scroller">
		<?php foreach ($cards as $index => $card) :
			$card = is_array($card) ? $card : [];

			$title       = isset($card['title']) ? (string) $card['title'] : '';
			$sub         = isset($card['sub']) ? (string) $card['sub'] : '';
			$badge       = isset($card['badge']) ? (string) $card['badge'] : '';
			$is_countdown = !empty($card['countdown']);
			if ($is_countdown && '' === $countdown) {
				$countdown = sm_home_promotions_countdown();
			}

			$style = sprintf(
				'background:%s;color:%s',
				esc_attr(!empty($card['bg']) ? $card['bg'] : '#FF1A1A'),
				esc_attr(!empty($card['fg']) ? $card['fg'] : '#FFFFFF')
			);

			$badge_style = sprintf(
				'background:%s;color:%s',
				esc_attr(!empty($card['badgeBg']) ? $card['badgeBg'] : '#FFFFFF'),
				esc_attr(!empty($card['badgeFg']) ? $card['badgeFg'] : '#FF1A1A')
			);

			$image_url = sm_home_promotions_image_url($card);
			$modifier  = 'sm-promotions__card--' . (($index % 3) + 1);
		?>
		<a class="sm-promotions__card <?php echo esc_attr($modifier); ?>" href="<?php echo esc_url($shop_url); ?>" style="<?php echo $style; // phpcs:ignore WordPress.Security.EscapeOutput -- built from esc_attr() parts above. ?>">
			<?php if ('' !== $image_url) : ?>
			<img class="sm-promotions__card-img" src="<?php echo esc_url($image_url); ?>" alt="" width="210" height="210" loading="lazy">
			<?php endif; ?>
			<?php if ('' !== $badge) : ?>
			<span class="sm-promotions__badge" style="<?php echo $badge_style; // phpcs:ignore WordPress.Security.EscapeOutput -- built from esc_attr() parts above. ?>"><?php echo esc_html($badge); ?></span>
			<?php endif; ?>
			<span class="sm-promotions__text">
				<span class="sm-promotions__title"><?php echo esc_html($title); ?></span>
				<?php if ($is_countdown) : ?>
				<span class="sm-promotions__sub sm-promotions__sub--countdown" data-sm-countdown><?php echo esc_html__('Ends in', 'ai-zippy-child') . ' ' . esc_html($countdown); ?></span>
				<?php else : ?>
				<span class="sm-promotions__sub"><?php echo esc_html($sub); ?></span>
				<?php endif; ?>
			</span>
		</a>
		<?php endforeach; ?>
	</div>
</div>
