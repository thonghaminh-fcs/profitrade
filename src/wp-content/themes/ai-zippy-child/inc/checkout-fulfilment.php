<?php

/**
 * Checkout Fulfilment Picker — order persistence + express fee.
 *
 * The checkout page ([ai_zippy_checkout]) is a Store-API React checkout: order
 * creation is a POST to /wp-json/wc/store/v1/checkout, which never renders the
 * block, so hooks registered inside the block's render.php do not fire and the
 * classic woocommerce_checkout_create_order action is not used. This file is
 * loaded from functions.php on every request and therefore:
 *
 *  1. declares sm_fulfilment / sm_express / sm_slot as Store-API checkout
 *     extension data (woocommerce_store_api_register_endpoint_data);
 *  2. saves the choice as order meta from the checkout request
 *     (woocommerce_store_api_checkout_update_order_from_request), preferring
 *     request extensions and falling back to the sm_* cookies the block's
 *     view.js keeps in sync (the parent React app does not forward the
 *     block's hidden inputs);
 *  3. keeps the classic-checkout path (woocommerce_checkout_create_order);
 *  4. applies the "+$20.00" 1-Hour Express fee on every cart calculation,
 *     including the Store-API checkout request that builds the final order
 *     totals (woocommerce_cart_calculate_fees, cookie-driven);
 *  5. surfaces the choice on the wp-admin order screen and in order emails.
 */

defined('ABSPATH') || exit;

/**
 * The five 3-hour delivery slots, in order — must match the block's
 * render.php / view.js and delivery-bar.
 *
 * @return string[]
 */
function ai_zippy_child_sm_slots(): array
{
    return [
        '8:00 – 11:00 AM',
        '11:00 AM – 2:00 PM',
        '2:00 – 5:00 PM',
        '5:00 – 8:00 PM',
        '8:00 – 11:00 PM',
    ];
}

/**
 * Resolve the fulfilment choice for the current request.
 *
 * Order of preference: Store-API extension data → posted form fields →
 * sm_* cookies (written by the block's view.js) → block defaults.
 *
 * @param WP_REST_Request|null $request Store-API checkout request, if any.
 * @return array{0: string, 1: string, 2: string} [fulfilment, express ('0'|'1'), slot label]
 */
function ai_zippy_child_sm_fulfilment_for_request($request = null): array
{
    $slots      = ai_zippy_child_sm_slots();
    $fulfilment = '';
    $express    = '';
    $slot       = '';

    // 1. Store-API extension data (namespace "sm-fulfilment" on /checkout).
    if ($request instanceof WP_REST_Request) {
        $extensions = $request['extensions'];
        $ext        = is_array($extensions) && isset($extensions['sm-fulfilment']) && is_array($extensions['sm-fulfilment'])
            ? $extensions['sm-fulfilment']
            : [];
        if (isset($ext['fulfilment']) && in_array($ext['fulfilment'], ['delivery', 'pickup'], true)) {
            $fulfilment = $ext['fulfilment'];
        }
        if (isset($ext['express'])) {
            $express = (is_bool($ext['express']) || '1' === (string) $ext['express'] || 'true' === (string) $ext['express']) ? '1' : '0';
        }
        if (isset($ext['slot']) && is_string($ext['slot']) && in_array($ext['slot'], $slots, true)) {
            $slot = $ext['slot'];
        }
    }

    // 2. Classic checkout posts the block's hidden inputs.
    if ('' === $fulfilment && isset($_POST['sm_fulfilment'])) {
        $posted = sanitize_key(wp_unslash($_POST['sm_fulfilment']));
        if (in_array($posted, ['delivery', 'pickup'], true)) {
            $fulfilment = $posted;
        }
    }
    if ('' === $express && isset($_POST['sm_express'])) {
        $express = '1' === (string) wp_unslash($_POST['sm_express']) ? '1' : '0';
    }
    if ('' === $slot && isset($_POST['sm_slot'])) {
        $posted_slot = sanitize_text_field(wp_unslash($_POST['sm_slot']));
        if (in_array($posted_slot, $slots, true)) {
            $slot = $posted_slot;
        }
    }

    // 3. Cookies kept in sync by the block's view.js.
    if ('' === $fulfilment) {
        $fulfilment = isset($_COOKIE['sm_fulfilment']) && 'pickup' === $_COOKIE['sm_fulfilment'] ? 'pickup' : 'delivery';
    }
    if ('' === $express) {
        $express = isset($_COOKIE['sm_express']) && '1' === $_COOKIE['sm_express'] ? '1' : '0';
    }
    if ('' === $slot && isset($_COOKIE['sm_slot'])) {
        $cookie_slot = sanitize_text_field(wp_unslash($_COOKIE['sm_slot']));
        if (in_array($cookie_slot, $slots, true)) {
            $slot = $cookie_slot;
        }
    }

    // 4. Block defaults.
    if ('' === $slot) {
        $slot = $slots[2];
    }

    return [$fulfilment, $express, $slot];
}

// =============================================================================
// 1. Declare the extension data on the Store-API checkout endpoint.
// =============================================================================

add_action('woocommerce_blocks_loaded', 'ai_zippy_child_sm_register_store_api_data');

/**
 * Register the sm-fulfilment extension data on the Store-API checkout endpoint.
 */
function ai_zippy_child_sm_register_store_api_data(): void
{
    if (!function_exists('woocommerce_store_api_register_endpoint_data') || !class_exists('\Automattic\WooCommerce\StoreApi\Schemas\V1\CheckoutSchema')) {
        return;
    }

    woocommerce_store_api_register_endpoint_data([
        'endpoint'        => \Automattic\WooCommerce\StoreApi\Schemas\V1\CheckoutSchema::IDENTIFIER,
        'namespace'       => 'sm-fulfilment',
        'data_callback'   => static fn () => [
            'fulfilment' => '',
            'express'    => '',
            'slot'       => '',
        ],
        'schema_callback' => static fn () => [
            'fulfilment' => [
                'description' => 'Fulfilment method chosen in the S.Mart checkout picker.',
                'type'        => 'string',
                'enum'        => ['delivery', 'pickup', ''],
                'default'     => '',
            ],
            'express'    => [
                'description' => 'Whether 1-Hour Express delivery is chosen ("1"/"0").',
                'type'        => 'string',
                'default'     => '',
            ],
            'slot'       => [
                'description' => 'Label of the chosen 3-hour delivery slot.',
                'type'        => 'string',
                'default'     => '',
            ],
        ],
        'schema_type'     => ARRAY_A,
    ]);
}

// =============================================================================
// 2 + 3. Save the choice as order meta (Store API + classic checkout).
// =============================================================================

add_action('woocommerce_store_api_checkout_update_order_from_request', 'ai_zippy_child_sm_save_store_api_order_meta', 10, 2);

/**
 * Persist the fulfilment choice on a Store-API (React checkout) order.
 *
 * @param WC_Order        $order   Order being placed.
 * @param WP_REST_Request $request Checkout request.
 */
function ai_zippy_child_sm_save_store_api_order_meta($order, $request): void
{
    [$fulfilment, $express, $slot] = ai_zippy_child_sm_fulfilment_for_request($request);
    $order->update_meta_data('_sm_fulfilment', $fulfilment);
    $order->update_meta_data('_sm_express', $express);
    $order->update_meta_data('_sm_slot', $slot);
}

add_action('woocommerce_checkout_create_order', 'ai_zippy_child_sm_save_classic_order_meta', 10, 2);

/**
 * Persist the fulfilment choice on a classic-checkout order.
 *
 * @param WC_Order $order Order being created.
 * @param array    $data  Posted checkout data (unused — values come from
 *                        $_POST / cookies via the shared resolver).
 */
function ai_zippy_child_sm_save_classic_order_meta($order, $data): void
{
    [$fulfilment, $express, $slot] = ai_zippy_child_sm_fulfilment_for_request();
    $order->update_meta_data('_sm_fulfilment', $fulfilment);
    $order->update_meta_data('_sm_express', $express);
    $order->update_meta_data('_sm_slot', $slot);
}

// =============================================================================
// 4. Express fee — registered on every request so it also lands in the
//    Store-API checkout totals, not just page-rendered cart fragments.
// =============================================================================

add_action('woocommerce_cart_calculate_fees', 'ai_zippy_child_sm_express_fee');

/**
 * Add the "Express delivery" fee while 1-Hour Express delivery is chosen.
 * Reads the cookies view.js keeps in sync; the fee amount travels in the
 * sm_express_fee cookie (block attribute default: $20.00).
 */
function ai_zippy_child_sm_express_fee(): void
{
    if (!function_exists('WC') || !WC()->cart) {
        return;
    }

    // The sm_* cookies outlive the cart, so an emptied cart would otherwise
    // keep charging the fee and show a $20.00 total with nothing in it.
    if (WC()->cart->is_empty()) {
        return;
    }

    $fulfilment = isset($_COOKIE['sm_fulfilment']) && 'pickup' === $_COOKIE['sm_fulfilment'] ? 'pickup' : 'delivery';
    $express    = isset($_COOKIE['sm_express']) && '1' === $_COOKIE['sm_express'];
    if ('delivery' !== $fulfilment || !$express) {
        return;
    }

    $fee = isset($_COOKIE['sm_express_fee']) ? (float) $_COOKIE['sm_express_fee'] : 20;
    if ($fee <= 0) {
        return;
    }

    WC()->cart->add_fee(__('Express delivery', 'ai-zippy-child'), $fee, false);
}

// =============================================================================
// 5. Show the choice in wp-admin and in order emails.
// =============================================================================

add_action('woocommerce_admin_order_data_after_shipping_address', 'ai_zippy_child_sm_render_admin_order_meta');

/**
 * Print the fulfilment choice on the wp-admin order screen.
 *
 * @param WC_Order $order Order being viewed.
 */
function ai_zippy_child_sm_render_admin_order_meta($order): void
{
    $fields = ai_zippy_child_sm_order_meta_fields($order);
    if (!$fields) {
        return;
    }

    echo '<div class="sm-fulfilment-order-meta"><h3>' . esc_html__('Fulfilment', 'ai-zippy-child') . '</h3>';
    foreach ($fields as $label => $value) {
        echo '<p><strong>' . esc_html($label) . ':</strong> ' . esc_html($value) . '</p>';
    }
    echo '</div>';
}

add_filter('woocommerce_email_order_meta_fields', 'ai_zippy_child_sm_email_order_meta_fields', 10, 3);

/**
 * Add the fulfilment choice to the order-meta block of Woo emails.
 *
 * @param array    $fields        Existing meta fields.
 * @param bool     $sent_to_admin Whether this is an admin email.
 * @param WC_Order $order         Order.
 * @return array
 */
function ai_zippy_child_sm_email_order_meta_fields(array $fields, $sent_to_admin, $order): array
{
    foreach (ai_zippy_child_sm_order_meta_fields($order) as $label => $value) {
        $fields['sm_' . sanitize_key($label)] = [
            'label' => $label,
            'value' => $value,
        ];
    }
    return $fields;
}

/**
 * Human-readable fulfilment fields for an order (empty when none saved).
 *
 * @param WC_Order $order Order.
 * @return array<string, string> label => value
 */
function ai_zippy_child_sm_order_meta_fields($order): array
{
    $fulfilment = $order->get_meta('_sm_fulfilment');
    if (!in_array($fulfilment, ['delivery', 'pickup'], true)) {
        return [];
    }

    $express = '1' === $order->get_meta('_sm_express');
    $fields  = [
        __('Fulfilment', 'ai-zippy-child') => 'pickup' === $fulfilment
            ? __('Walk-in Pickup', 'ai-zippy-child')
            : ($express ? __('1-Hour Express', 'ai-zippy-child') : __('Delivery', 'ai-zippy-child')),
    ];

    if ('delivery' === $fulfilment) {
        $slot = $order->get_meta('_sm_slot');
        if ('' !== $slot) {
            $fields[__('Delivery slot', 'ai-zippy-child')] = $express
                ? sprintf(__('Express (within 60 min) — %s slot on record', 'ai-zippy-child'), $slot)
                : $slot;
        }
    }

    return $fields;
}
