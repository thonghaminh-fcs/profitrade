<?php

namespace AiZippy\Checkout;

defined('ABSPATH') || exit;

/**
 * Enqueue checkout assets based on admin template selection.
 *
 * - "react"       → Enqueue Vite-built React checkout app
 * - "woocommerce" → Enqueue WC default checkout styles only
 */
class CheckoutAssets
{
    /**
     * Register hooks.
     */
    public static function register(): void
    {
        add_action('wp_enqueue_scripts', [self::class, 'enqueue']);
        add_action('wp_enqueue_scripts', [self::class, 'dequeueWcCheckoutOnOrderPay'], 25);
        add_action('wp_ajax_az_update_checkout_qty', [self::class, 'ajaxUpdateQty']);
        add_action('wp_ajax_nopriv_az_update_checkout_qty', [self::class, 'ajaxUpdateQty']);
        add_action('wp_ajax_az_get_checkout_totals', [self::class, 'ajaxGetTotals']);
        add_action('wp_ajax_nopriv_az_get_checkout_totals', [self::class, 'ajaxGetTotals']);
        self::removeEposPointInformationDefaultHook();
    }

    /**
     * Dequeue WooCommerce's wc-checkout.js on the order-pay endpoint.
     *
     * WC Blocks' Checkout::dequeue_woocommerce_core_scripts() already strips
     * this script on /checkout/, but only when the Checkout block renders —
     * order-pay uses the classic form-pay.php template, so the script stays
     * enqueued and its click handler on `#order_review input[name="payment_method"]`
     * jQuery-animates `.payment_box` height/display inline, fighting the
     * grid-template-rows transition + `.is-open` toggle that wc-checkout-phone.js
     * drives. Priority 25 runs after WC's own priority-10 enqueue.
     */
    public static function dequeueWcCheckoutOnOrderPay(): void
    {
        if (function_exists('is_checkout_pay_page') && is_checkout_pay_page()) {
            wp_dequeue_script('wc-checkout');
        }
    }

    /**
     * EPOS CRM's membership-point widget defaults to
     * woocommerce_before_checkout_form — outside <form>, above this theme's
     * step indicator, with none of the sidebar's card styling. It's plugin
     * code, so the theme can't just delete the add_action() call; instead
     * form-checkout.php calls Epos_Crm_Web::render_point_information()
     * directly next to the coupon/voucher block, and this removes the
     * plugin's own hook so it doesn't also render at the top of the page.
     * Epos_Crm_Web::get_instance() runs on plugins_loaded (see epos-crm.php),
     * which has already fired by the time a theme's register() runs, so the
     * instance — and the hook to remove — both already exist here.
     */
    private static function removeEposPointInformationDefaultHook(): void
    {
        if (!class_exists('\EPOS_CRM\Src\Web\Epos_Crm_Web')) {
            return;
        }

        remove_action(
            'woocommerce_before_checkout_form',
            [\EPOS_CRM\Src\Web\Epos_Crm_Web::get_instance(), 'render_point_information']
        );
    }

    /**
     * AJAX handler: render the cart totals partial. Called after coupon
     * apply/remove, and after billing/shipping address fields change, so
     * the sidebar (including the shipping line) updates without a reload.
     *
     * WooCommerce's own wc-checkout.js does this via update_order_review,
     * which got dequeued along with the rest of that script — see the note
     * in wc-checkout-phone.js on WC_Blocks\BlockTypes\Checkout::render().
     * Mirrors that endpoint's address handling (WC_AJAX::update_order_review)
     * so shipping zones re-match against the address just typed.
     */
    public static function ajaxGetTotals(): void
    {
        if (!function_exists('WC') || !WC()->cart) {
            wp_send_json_error('Cart not available');
        }

        self::updateCustomerAddressFromRequest();

        WC()->cart->calculate_shipping();
        WC()->cart->calculate_totals();

        ob_start();
        self::renderTotals();
        $html = ob_get_clean();

        wp_send_json_success(['html' => $html]);
    }

    /**
     * Apply posted billing/shipping address fields to the customer session,
     * same field names WooCommerce's own checkout.js posts them under.
     */
    private static function updateCustomerAddressFromRequest(): void
    {
        $customer = WC()->customer;

        $customer->set_props([
            'billing_country'   => isset($_POST['country']) ? wc_clean(wp_unslash($_POST['country'])) : null,
            'billing_state'     => isset($_POST['state']) ? wc_clean(wp_unslash($_POST['state'])) : null,
            'billing_postcode'  => isset($_POST['postcode']) ? wc_clean(wp_unslash($_POST['postcode'])) : null,
            'billing_city'      => isset($_POST['city']) ? wc_clean(wp_unslash($_POST['city'])) : null,
            'billing_address_1' => isset($_POST['address']) ? wc_clean(wp_unslash($_POST['address'])) : null,
        ]);

        if (wc_ship_to_billing_address_only() || empty($_POST['ship_to_different_address'])) {
            $customer->set_props([
                'shipping_country'   => isset($_POST['country']) ? wc_clean(wp_unslash($_POST['country'])) : null,
                'shipping_state'     => isset($_POST['state']) ? wc_clean(wp_unslash($_POST['state'])) : null,
                'shipping_postcode'  => isset($_POST['postcode']) ? wc_clean(wp_unslash($_POST['postcode'])) : null,
                'shipping_city'      => isset($_POST['city']) ? wc_clean(wp_unslash($_POST['city'])) : null,
                'shipping_address_1' => isset($_POST['address']) ? wc_clean(wp_unslash($_POST['address'])) : null,
            ]);
        } else {
            $customer->set_props([
                'shipping_country'   => isset($_POST['s_country']) ? wc_clean(wp_unslash($_POST['s_country'])) : null,
                'shipping_state'     => isset($_POST['s_state']) ? wc_clean(wp_unslash($_POST['s_state'])) : null,
                'shipping_postcode'  => isset($_POST['s_postcode']) ? wc_clean(wp_unslash($_POST['s_postcode'])) : null,
                'shipping_city'      => isset($_POST['s_city']) ? wc_clean(wp_unslash($_POST['s_city'])) : null,
                'shipping_address_1' => isset($_POST['s_address']) ? wc_clean(wp_unslash($_POST['s_address'])) : null,
            ]);
        }

        // WooCommerce's cart-shipping template shows "Enter your address to
        // view shipping options" instead of the calculated rates whenever
        // this is false — separate from WC_Cart::has_calculated_shipping(),
        // which is already true by this point. WC_AJAX::update_order_review()
        // sets it from a has_full_address flag the client computes; simpler
        // and just as correct to key it off the one field that's actually
        // required to resolve a shipping zone.
        $customer->set_calculated_shipping((bool) $customer->get_shipping_country());

        $customer->save();
    }

    /**
     * wc_cart_totals_shipping_html() prints WooCommerce's whole
     * <tr><th>Shipment</th><td>…</td></tr>. Browsers foster-parent that
     * orphan <tr> out of our <span> wrapper into the nearest ancestor
     * <table> (the classic order-review table this theme hides), leaving
     * the <th>'s text sitting bare in the DOM — CSS can't target text
     * nodes, so this strips the <th> before it ever reaches the page,
     * keeping only the rates/message the wrapping "Shipping" label needs.
     */
    private static function shippingHtmlWithoutLabel(): string
    {
        ob_start();
        wc_cart_totals_shipping_html();
        $html = ob_get_clean();

        return (string) preg_replace('#<tr[^>]*>\s*<th[^>]*>.*?</th>\s*<td[^>]*>(.*?)</td>\s*</tr>#s', '$1', $html);
    }

    /**
     * Render the totals block. Used both server-side from form-checkout.php
     * AND from the ajaxGetTotals() handler so coupon updates produce identical
     * markup to the initial page render.
     */
    public static function renderTotals(): void
    {
        $cart = WC()->cart;
        if (!$cart) {
            return;
        }
        ?>
        <div class="az-checkout__totals-row">
            <span><?php esc_html_e('Subtotal', 'ai-zippy'); ?></span>
            <span><?php wc_cart_totals_subtotal_html(); ?></span>
        </div>

        <?php foreach ($cart->get_coupons() as $code => $coupon) :
            $amount     = $cart->get_coupon_discount_amount($coupon->get_code(), $cart->display_cart_ex_tax);
            $remove_url = wp_nonce_url(
                add_query_arg('remove_coupon', rawurlencode($coupon->get_code()), wc_get_checkout_url()),
                'remove_coupon_' . $coupon->get_code(),
                '_wpnonce'
            );
            // WC's own default here is "Coupon: CODE"; a plugin (e.g. EPOS
            // CRM's virtual vouchers) can relabel its own codes via this
            // filter — apply it instead of printing get_code() raw, or a
            // voucher shows its internal slug (epos_voucher_welcomegift)
            // instead of the human label ("Voucher: WELCOMEGIFT").
            $coupon_label = apply_filters(
                'woocommerce_cart_totals_coupon_label',
                sprintf(esc_html__('Coupon: %s', 'ai-zippy'), $coupon->get_code()),
                $coupon
            );
        ?>
        <div class="az-checkout__totals-row az-checkout__totals-row--discount">
            <span class="az-checkout__coupon-label">
                <span class="az-checkout__coupon-tag">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M20.59 13.41 13.42 20.58a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/>
                        <line x1="7" y1="7" x2="7.01" y2="7"/>
                    </svg>
                    <?php echo wp_kses_post($coupon_label); ?>
                </span>
                <a href="<?php echo esc_url($remove_url); ?>" class="az-checkout__coupon-remove" data-az-remove-coupon="<?php echo esc_attr($coupon->get_code()); ?>" aria-label="<?php echo esc_attr(sprintf(__('Remove coupon %s', 'ai-zippy'), $coupon->get_code())); ?>" title="<?php esc_attr_e('Remove', 'ai-zippy'); ?>">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <line x1="18" y1="6" x2="6" y2="18"/>
                        <line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                </a>
            </span>
            <span class="az-checkout__coupon-amount">−<?php echo wc_price($amount); ?></span>
        </div>
        <?php endforeach; ?>

        <?php if ($cart->needs_shipping() && $cart->show_shipping()) : ?>
        <div class="az-checkout__totals-row">
            <span><?php esc_html_e('Shipping', 'ai-zippy'); ?></span>
            <span><?php echo self::shippingHtmlWithoutLabel(); ?></span>
        </div>
        <?php endif; ?>

        <?php foreach ($cart->get_fees() as $fee) : ?>
        <div class="az-checkout__totals-row">
            <span><?php echo esc_html($fee->name); ?></span>
            <span><?php wc_cart_totals_fee_html($fee); ?></span>
        </div>
        <?php endforeach; ?>

        <?php if (wc_tax_enabled() && !$cart->display_prices_including_tax()) : ?>
        <div class="az-checkout__totals-row">
            <span><?php esc_html_e('Tax', 'ai-zippy'); ?></span>
            <span><?php wc_cart_totals_taxes_total_html(); ?></span>
        </div>
        <?php endif; ?>

        <div class="az-checkout__totals-row az-checkout__totals-row--total">
            <span><?php esc_html_e('Total', 'ai-zippy'); ?></span>
            <span><?php wc_cart_totals_order_total_html(); ?></span>
        </div>
        <?php
    }

    /**
     * AJAX handler: update cart item quantity from checkout sidebar.
     */
    public static function ajaxUpdateQty(): void
    {
        check_ajax_referer('az-checkout-qty', 'security');

        $cart_key = sanitize_text_field($_POST['cart_key'] ?? '');
        $quantity = absint($_POST['quantity'] ?? 0);

        if (empty($cart_key)) {
            wp_send_json_error('Invalid cart key');
        }

        if ($quantity === 0) {
            WC()->cart->remove_cart_item($cart_key);
        } else {
            WC()->cart->set_quantity($cart_key, $quantity);
        }

        wp_send_json_success();
    }

    /**
     * Enqueue checkout assets on checkout page only.
     */
    public static function enqueue(): void
    {
        if (!is_checkout() && !is_page('checkout')) {
            return;
        }

        if (CheckoutSettings::isReact()) {
            self::enqueueReactCheckout();
        } else {
            self::enqueueWcCheckout();
        }
    }

    /**
     * Enqueue React checkout app.
     */
    private static function enqueueReactCheckout(): void
    {
        \AiZippy\Core\ViteAssets::enqueue(
            'ai-zippy-checkout',
            'src/wp-content/themes/ai-zippy/src/js/frontend/checkout/index.jsx'
        );

        wp_localize_script('ai-zippy-checkout', 'aiZippyCheckout', [
            'paymentGateways'   => self::getPaymentGateways(),
            'shippingEnabled'   => 'yes' === get_option('woocommerce_calc_shipping', 'yes'),
            'shipToDestination' => get_option('woocommerce_ship_to_destination', 'shipping'),
            'customer'          => self::getCustomerData(),
        ]);
    }

    /**
     * Enqueue WooCommerce default checkout styles.
     */
    private static function enqueueWcCheckout(): void
    {
        \AiZippy\Core\ViteAssets::enqueue(
            'ai-zippy-wc-checkout',
            'src/wp-content/themes/ai-zippy/src/scss/wc-checkout-entry.scss'
        );

        \AiZippy\Core\ViteAssets::enqueue(
            'ai-zippy-wc-checkout-phone',
            'src/wp-content/themes/ai-zippy/src/js/frontend/wc-checkout-phone.js'
        );

        // intl-tel-input's flag sprite, served from a fixed theme URL rather
        // than through Vite — see the note in wc-checkout-phone.js.
        wp_localize_script('ai-zippy-wc-checkout-phone', 'wcCheckoutPhone', [
            'flagsUrl1x' => AI_ZIPPY_THEME_URI . '/assets/vendor/intl-tel-input/flags.webp',
            'flagsUrl2x' => AI_ZIPPY_THEME_URI . '/assets/vendor/intl-tel-input/flags@2x.webp',
        ]);
    }

    /**
     * Get enabled WooCommerce payment gateways.
     */
    private static function getPaymentGateways(): array
    {
        $gateways = [];

        if (!function_exists('WC') || !WC()->payment_gateways()) {
            return $gateways;
        }

        foreach (WC()->payment_gateways()->get_available_payment_gateways() as $gateway) {
            $gateways[] = [
                'id'          => $gateway->id,
                'title'       => $gateway->get_title(),
                'description' => $gateway->get_description(),
            ];
        }

        return $gateways;
    }

    /**
     * Get logged-in customer data for form pre-fill.
     */
    private static function getCustomerData(): array
    {
        if (!is_user_logged_in() || !function_exists('WC') || !WC()->customer) {
            return [];
        }

        $c = WC()->customer;

        return [
            'firstName' => $c->get_billing_first_name(),
            'lastName'  => $c->get_billing_last_name(),
            'email'     => $c->get_billing_email(),
            'phone'     => $c->get_billing_phone(),
            'billing'   => [
                'address_1' => $c->get_billing_address_1(),
                'address_2' => $c->get_billing_address_2(),
                'city'      => $c->get_billing_city(),
                'state'     => $c->get_billing_state(),
                'postcode'  => $c->get_billing_postcode(),
                'country'   => $c->get_billing_country() ?: 'SG',
                'company'   => $c->get_billing_company(),
            ],
        ];
    }
}
