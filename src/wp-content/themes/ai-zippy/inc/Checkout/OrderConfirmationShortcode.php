<?php

namespace AiZippy\Checkout;

defined('ABSPATH') || exit;

/**
 * [ai_zippy_order_confirmation] shortcode.
 *
 * Always renders the custom thankyou.php template (works for both React and WC checkout).
 * The HTML is buffered early in template_redirect to avoid wpautop interference.
 */
class OrderConfirmationShortcode
{
    private static string $bufferedHtml = '';

    /**
     * Register hooks.
     */
    public static function register(): void
    {
        add_shortcode('ai_zippy_order_confirmation', [self::class, 'render']);
        add_action('template_redirect', [self::class, 'bufferThankyou']);
    }

    /**
     * Buffer the thankyou.php output early (before content filters).
     */
    public static function bufferThankyou(): void
    {
        if (!is_wc_endpoint_url('order-received')) {
            return;
        }

        global $wp;
        $order_id = isset($wp->query_vars['order-received']) ? absint($wp->query_vars['order-received']) : 0;
        $order = $order_id ? wc_get_order($order_id) : false;

        if ($order && !empty($_GET['key'])) {
            $order_key = sanitize_text_field(wp_unslash($_GET['key']));
            if ($order->get_order_key() !== $order_key) {
                $order = false;
            }
        }

        ob_start();
        wc_get_template('checkout/thankyou.php', ['order' => $order]);
        $html = ob_get_clean();

        // Collapse whitespace between tags to prevent wpautop from injecting <p>/<br>
        self::$bufferedHtml = preg_replace('/>\s+</', '><', $html);
    }

    /**
     * Shortcode render — return pre-buffered HTML.
     */
    public static function render(): string
    {
        return '<div class="az-thankyou-wrap">' . self::$bufferedHtml . '</div>';
    }
}
