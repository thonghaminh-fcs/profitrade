<?php

namespace AiZippy\Checkout;

defined('ABSPATH') || exit;

/**
 * Field customizations for the classic WooCommerce checkout template
 * (form-checkout.php). No effect on the React checkout app, which validates
 * phone itself — see CheckoutValidation for that path.
 */
class ClassicCheckoutFields
{
    public const DEFAULT_COUNTRY = 'SG';

    /**
     * Register hooks.
     */
    public static function register(): void
    {
        add_filter('woocommerce_checkout_fields', [self::class, 'requirePhone']);
        add_filter('woocommerce_checkout_get_value', [self::class, 'defaultCountry'], 10, 2);
    }

    /**
     * Make the billing phone field required at checkout.
     */
    public static function requirePhone(array $fields): array
    {
        if (isset($fields['billing']['billing_phone'])) {
            $fields['billing']['billing_phone']['required'] = true;
        }

        return $fields;
    }

    /**
     * Default the billing country to SG when the customer hasn't set one yet.
     */
    public static function defaultCountry($value, string $input)
    {
        if ($input === 'billing_country' && empty($value)) {
            return self::DEFAULT_COUNTRY;
        }

        return $value;
    }
}
