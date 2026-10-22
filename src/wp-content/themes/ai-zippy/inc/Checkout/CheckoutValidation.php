<?php

namespace AiZippy\Checkout;

defined('ABSPATH') || exit;

/**
 * Checkout server-side validation.
 *
 * Validates phone and email when order is placed via WC Store API.
 * Frontend validation is handled by the React checkout app.
 */
class CheckoutValidation
{
    /**
     * Register all hooks.
     */
    public static function register(): void
    {
        add_action(
            'woocommerce_store_api_checkout_update_order_from_request',
            [self::class, 'processCheckout'],
            10,
            2
        );
    }

    /**
     * Validate and process checkout fields before order is placed.
     */
    public static function processCheckout(\WC_Order $order, \WP_REST_Request $request): void
    {
        self::validatePhone($order);
        self::validateEmail($order);
    }

    /**
     * Validate phone number format.
     * Accepts: +65 91234567, +1-555-123-4567, etc.
     */
    private static function validatePhone(\WC_Order $order): void
    {
        $phone = $order->get_billing_phone();

        if (empty($phone)) {
            return;
        }

        // Strip spaces, dashes, parentheses for validation
        $cleaned = preg_replace('/[\s\-\(\).]/', '', $phone);

        // Must start with + and contain 7-15 digits
        if (!preg_match('/^\+\d{7,15}$/', $cleaned)) {
            throw new \Exception(
                __('Please enter a valid phone number with country code (e.g. +65 91234567).', 'ai-zippy')
            );
        }

        // Save the cleaned version
        $order->set_billing_phone($cleaned);
    }

    /**
     * Validate email format.
     */
    private static function validateEmail(\WC_Order $order): void
    {
        $email = $order->get_billing_email();

        if (empty($email)) {
            return;
        }

        if (!is_email($email)) {
            throw new \Exception(
                __('Please enter a valid email address.', 'ai-zippy')
            );
        }
    }
}
