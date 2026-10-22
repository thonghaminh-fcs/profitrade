<?php

/**
 * Mini cart — frontend render.
 *
 * Renders the full initial state server-side: the toggle, the item rows, the
 * totals and the free-shipping progress. The drawer is therefore readable and
 * the links work with JavaScript disabled; view logic only takes over to keep
 * it in sync after a cart change.
 *
 * Class names are ours (az-mc__*), never WooCommerce internals, so a Woo
 * release cannot break the design. This file ships only structural markup —
 * colours, spacing and typography belong to the child theme.
 *
 * MiniCart::available() returns false unless WooCommerce is loaded and its cart
 * exists, so wc_* functions below need no function_exists() guard.
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Inner blocks (unused).
 * @var WP_Block $block      Block instance.
 */

defined('ABSPATH') || exit;

use AiZippy\Cart\MiniCart;
use AiZippy\Core\L10n;

// Nothing to show when WooCommerce is inactive or the feature is switched off.
if (!MiniCart::available()) {
    return;
}

if (!function_exists('ai_zippy_mini_cart_item_row')) :
    /**
     * One cart line. Mirrored by renderItem() in modules/mini-cart.js — keep
     * both in step when the structure changes.
     *
     * @param array<string, mixed> $item
     * @param callable             $text string key => translated string
     */
    function ai_zippy_mini_cart_item_row(array $item, callable $text): void
    {
        $name = (string) $item['name'];
        ?>
        <li class="az-mc__item" data-key="<?php echo esc_attr($item['key']); ?>">
            <?php if ($item['image']) : ?>
                <img class="az-mc__item-image" src="<?php echo esc_url($item['image']); ?>" alt="" width="64" height="64" loading="lazy" />
            <?php endif; ?>

            <div class="az-mc__item-body">
                <?php if ($item['permalink']) : ?>
                    <a class="az-mc__item-name" href="<?php echo esc_url($item['permalink']); ?>"><?php echo esc_html($name); ?></a>
                <?php else : ?>
                    <span class="az-mc__item-name"><?php echo esc_html($name); ?></span>
                <?php endif; ?>

                <span class="az-mc__item-price"><?php echo wp_kses_post($item['price']); ?></span>

                <div class="az-mc__qty">
                    <button class="az-mc__qty-down" type="button" aria-label="<?php echo esc_attr(sprintf($text('minicart.decrease'), $name)); ?>">&minus;</button>
                    <input
                        class="az-mc__qty-input"
                        type="number"
                        inputmode="numeric"
                        min="0"
                        step="1"
                        value="<?php echo esc_attr($item['quantity']); ?>"
                        aria-label="<?php echo esc_attr(sprintf($text('minicart.quantity'), $name)); ?>"
                    />
                    <button class="az-mc__qty-up" type="button" aria-label="<?php echo esc_attr(sprintf($text('minicart.increase'), $name)); ?>">+</button>
                </div>
            </div>

            <button class="az-mc__remove" type="button" aria-label="<?php echo esc_attr(sprintf($text('minicart.remove'), $name)); ?>">&times;</button>
        </li>
        <?php
    }
endif;

$strings = L10n::strings();
$state   = MiniCart::state();
$items   = MiniCart::items();
$icons   = MiniCart::icons();

// ai_zippy_i18n_strings is public, so a child theme may hand back a trimmed
// array. Read through a closure rather than indexing it directly.
$text = static function (string $key) use ($strings): string {
    return isset($strings[$key]) ? (string) $strings[$key] : '';
};

$uid   = wp_unique_id('az-mc-');
$count = $state['count'];

// WooCommerce suppresses its own drawer on cart and checkout, where a slide-in
// panel duplicates the page. Behave the same way: plain link to the cart.
$is_link_mode = is_cart() || is_checkout();
$cart_url     = wc_get_cart_url();

$icon_style = (string) ($attributes['iconStyle'] ?? 'bag');
$custom_url = (string) ($attributes['customIconUrl'] ?? '');
$use_custom = $icon_style === 'custom' && $custom_url !== '';

$threshold = 0.0;

if (!empty($attributes['showFreeShippingProgress'])) {
    $threshold = ($attributes['thresholdSource'] ?? 'woo') === 'manual'
        ? (float) ($attributes['manualThreshold'] ?? 0)
        : MiniCart::freeShippingThreshold();
}

$remaining = max(0.0, $threshold - $state['subtotalRaw']);
$percent   = $threshold > 0 ? min(100, (int) round($state['subtotalRaw'] / $threshold * 100)) : 0;

$cross_sell_ids = !empty($attributes['showCrossSell'])
    ? MiniCart::crossSellIds((int) ($attributes['crossSellCount'] ?? 3))
    : [];

$wrapper = get_block_wrapper_attributes([
    'class' => 'az-mc az-mc--' . ($is_link_mode ? 'link' : 'drawer'),
]);
?>

<div
    <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by core. ?>
    data-side="<?php echo esc_attr($attributes['drawerSide'] ?? 'right'); ?>"
    data-open-on-add="<?php echo !empty($attributes['openOnAdd']) && !$is_link_mode ? 'true' : 'false'; ?>"
    data-threshold="<?php echo esc_attr((string) $threshold); ?>"
    data-cart-url="<?php echo esc_url($cart_url); ?>"
>
    <?php if ($is_link_mode) : ?>
        <a
            class="az-mc__toggle"
            href="<?php echo esc_url($cart_url); ?>"
            aria-label="<?php echo esc_attr(sprintf($count === 1 ? $text('minicart.items_one') : $text('minicart.items_many'), $count)); ?>"
            data-badge="<?php echo esc_attr($attributes['badgePosition'] ?? 'top-right'); ?>"
        >
    <?php else : ?>
        <button
            class="az-mc__toggle"
            type="button"
            aria-expanded="false"
            aria-controls="<?php echo esc_attr($uid); ?>-drawer"
            aria-label="<?php echo esc_attr(sprintf($count === 1 ? $text('minicart.items_one') : $text('minicart.items_many'), $count)); ?>"
            data-badge="<?php echo esc_attr($attributes['badgePosition'] ?? 'top-right'); ?>"
        >
    <?php endif; ?>

        <?php if ($use_custom) : ?>
            <img class="az-mc__icon" src="<?php echo esc_url($custom_url); ?>" alt="" width="24" height="24" />
        <?php else : ?>
            <?php // Raw SVG: kses strips <svg>, and ai_zippy_mini_cart_icons is documented as trusted markup. ?>
            <?php echo $icons[$icon_style] ?? (string) reset($icons); // phpcs:ignore WordPress.Security.EscapeOutput ?>
        <?php endif; ?>

        <?php if (!empty($attributes['showCount'])) : ?>
            <span class="az-mc__count" data-empty="<?php echo $count === 0 ? 'true' : 'false'; ?>"><?php echo esc_html((string) $count); ?></span>
        <?php endif; ?>

        <?php if (!empty($attributes['showSubtotal'])) : ?>
            <span class="az-mc__subtotal"><?php echo wp_kses_post($state['subtotal']); ?></span>
        <?php endif; ?>

    <?php echo $is_link_mode ? '</a>' : '</button>'; ?>

    <?php if (!$is_link_mode) : ?>
        <div class="az-mc__drawer" id="<?php echo esc_attr($uid); ?>-drawer" hidden>
            <div class="az-mc__backdrop" data-az-mc-close></div>

            <div class="az-mc__panel" role="dialog" aria-modal="true" tabindex="-1" aria-label="<?php echo esc_attr($text('minicart.title')); ?>">
                <div class="az-mc__header">
                    <span class="az-mc__title"><?php echo esc_html($text('minicart.title')); ?></span>
                    <button class="az-mc__close" type="button" aria-label="<?php echo esc_attr($text('common.close')); ?>" data-az-mc-close>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true" focusable="false">
                            <path d="M6 6l12 12M18 6L6 18" />
                        </svg>
                    </button>
                </div>

                <?php if ($threshold > 0) : ?>
                    <div class="az-mc__shipping" data-reached="<?php echo $remaining <= 0 ? 'true' : 'false'; ?>">
                        <p class="az-mc__shipping-text">
                            <?php
                            echo $remaining <= 0
                                ? esc_html($text('minicart.ship_reached'))
                                : esc_html(sprintf($text('minicart.ship_progress'), wp_strip_all_tags(wc_price($remaining))));
                            ?>
                        </p>
                        <div class="az-mc__shipping-track" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?php echo esc_attr((string) $percent); ?>">
                            <span class="az-mc__shipping-bar" style="width:<?php echo esc_attr((string) $percent); ?>%"></span>
                        </div>
                    </div>
                <?php endif; ?>

                <ul class="az-mc__items"><?php
                    foreach ($items as $item) {
                        ai_zippy_mini_cart_item_row($item, $text);
                    }
                ?></ul>

                <p class="az-mc__empty"<?php echo $count > 0 ? ' hidden' : ''; ?>>
                    <?php echo esc_html($text('minicart.empty')); ?>
                    <a class="az-mc__keep-shopping" href="<?php echo esc_url(wc_get_page_permalink('shop', home_url('/'))); ?>">
                        <?php echo esc_html($text('minicart.keep_shopping')); ?>
                    </a>
                </p>

                <?php if ($cross_sell_ids) : ?>
                    <div class="az-mc__cross-sell">
                        <span class="az-mc__cross-sell-title"><?php echo esc_html($text('minicart.cross_sell')); ?></span>
                        <ul class="az-mc__cross-sell-list">
                            <?php foreach ($cross_sell_ids as $product_id) : ?>
                                <?php $product = wc_get_product($product_id); ?>
                                <?php if (!$product || !$product->is_visible()) : continue; endif; ?>
                                <li class="az-mc__cross-sell-item">
                                    <a href="<?php echo esc_url($product->get_permalink()); ?>">
                                        <?php echo wp_kses_post($product->get_image('woocommerce_gallery_thumbnail')); ?>
                                        <span class="az-mc__cross-sell-name"><?php echo esc_html($product->get_name()); ?></span>
                                        <span class="az-mc__cross-sell-price"><?php echo wp_kses_post($product->get_price_html()); ?></span>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <div class="az-mc__footer"<?php echo $count === 0 ? ' hidden' : ''; ?>>
                    <div class="az-mc__totals">
                        <span class="az-mc__totals-label"><?php echo esc_html($text('minicart.subtotal')); ?></span>
                        <span class="az-mc__totals-value"><?php echo wp_kses_post($state['subtotal']); ?></span>
                    </div>

                    <div class="az-mc__actions">
                        <a class="az-mc__button az-mc__button--cart" href="<?php echo esc_url($cart_url); ?>">
                            <?php echo esc_html($text('minicart.view_cart')); ?>
                        </a>
                        <a class="az-mc__button az-mc__button--checkout" href="<?php echo esc_url(wc_get_checkout_url()); ?>">
                            <?php echo esc_html($text('minicart.checkout')); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
