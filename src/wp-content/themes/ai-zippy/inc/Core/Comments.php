<?php

namespace AiZippy\Core;

use AiZippy\Admin\ThemeOptions;
use WP_Admin_Bar;

defined('ABSPATH') || exit;

/**
 * Site-wide comments switch.
 *
 * Existing comments remain in the database. When enabled, this module closes
 * comment entry points, hides existing comments on the front end, removes the
 * related admin UI, and blocks comment APIs until the option is disabled.
 */
final class Comments
{
    /**
     * Register comment-disabling hooks when the setting is enabled.
     */
    public static function register(): void
    {
        if (!ThemeOptions::areCommentsDisabled()) {
            return;
        }

        add_action('registered_post_type', [self::class, 'disablePostTypeSupport'], PHP_INT_MAX, 1);
        add_action('init', [self::class, 'disableAllPostTypeSupport'], PHP_INT_MAX);

        add_filter('comments_open', '__return_false', PHP_INT_MAX);
        add_filter('pings_open', '__return_false', PHP_INT_MAX);
        add_filter('comments_array', '__return_empty_array', PHP_INT_MAX);
        add_filter('get_comments_number', '__return_zero', PHP_INT_MAX);
        add_filter('feed_links_show_comments_feed', '__return_false', PHP_INT_MAX);

        add_action('pre_comment_on_post', [self::class, 'rejectCommentSubmission'], PHP_INT_MAX);
        add_filter('rest_endpoints', [self::class, 'removeCommentRestEndpoints'], PHP_INT_MAX);
        add_filter('xmlrpc_methods', [self::class, 'removeCommentXmlRpcMethods'], PHP_INT_MAX);

        add_action('admin_menu', [self::class, 'removeAdminMenus'], PHP_INT_MAX);
        add_action('admin_bar_menu', [self::class, 'removeAdminBarMenu'], PHP_INT_MAX);
        add_action('wp_dashboard_setup', [self::class, 'removeDashboardWidget'], PHP_INT_MAX);
        add_action('admin_init', [self::class, 'redirectCommentAdminScreens'], PHP_INT_MAX);
    }

    /**
     * Remove comments and trackbacks from one registered post type.
     */
    public static function disablePostTypeSupport(string $post_type): void
    {
        remove_post_type_support($post_type, 'comments');
        remove_post_type_support($post_type, 'trackbacks');
    }

    /**
     * Cover built-in and custom post types registered before the late init hook.
     */
    public static function disableAllPostTypeSupport(): void
    {
        foreach (get_post_types([], 'names') as $post_type) {
            self::disablePostTypeSupport($post_type);
        }
    }

    /**
     * Reject traditional comment form submissions.
     */
    public static function rejectCommentSubmission(): void
    {
        wp_die(
            esc_html__('Comments are disabled on this site.', 'ai-zippy'),
            esc_html__('Comments Disabled', 'ai-zippy'),
            ['response' => 403, 'back_link' => true]
        );
    }

    /**
     * Remove core comments and WooCommerce product-review REST routes.
     *
     * @param array<string, mixed> $endpoints
     * @return array<string, mixed>
     */
    public static function removeCommentRestEndpoints(array $endpoints): array
    {
        foreach (array_keys($endpoints) as $route) {
            if (
                preg_match('#^/wp/v\d+/comments(?:/|$)#', $route)
                || preg_match('#^/wc/(?:v\d+|store/v\d+)/products/reviews(?:/|$)#', $route)
            ) {
                unset($endpoints[$route]);
            }
        }

        return $endpoints;
    }

    /**
     * Remove XML-RPC methods that read, create, edit, or ping comments.
     *
     * @param array<string, callable> $methods
     * @return array<string, callable>
     */
    public static function removeCommentXmlRpcMethods(array $methods): array
    {
        $comment_methods = [
            'wp.getCommentCount',
            'wp.getComment',
            'wp.getComments',
            'wp.deleteComment',
            'wp.editComment',
            'wp.newComment',
            'pingback.ping',
            'pingback.extensions.getPingbacks',
        ];

        foreach ($comment_methods as $method) {
            unset($methods[$method]);
        }

        return $methods;
    }

    /**
     * Remove comment-related dashboard menu entries.
     */
    public static function removeAdminMenus(): void
    {
        remove_menu_page('edit-comments.php');
        remove_submenu_page('options-general.php', 'options-discussion.php');
        remove_submenu_page('edit.php?post_type=product', 'product-reviews');
    }

    /**
     * Remove the comments shortcut from the admin toolbar.
     */
    public static function removeAdminBarMenu(WP_Admin_Bar $admin_bar): void
    {
        $admin_bar->remove_node('comments');
    }

    /**
     * Remove the Recent Comments dashboard widget.
     */
    public static function removeDashboardWidget(): void
    {
        remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');
    }

    /**
     * Prevent direct access to comment management screens hidden from menus.
     */
    public static function redirectCommentAdminScreens(): void
    {
        if (wp_doing_ajax()) {
            return;
        }

        global $pagenow;

        $is_comment_screen = in_array(
            $pagenow,
            ['edit-comments.php', 'comment.php', 'options-discussion.php'],
            true
        );
        $is_product_reviews_screen = $pagenow === 'edit.php'
            && isset($_GET['post_type'], $_GET['page'])
            && sanitize_key(wp_unslash($_GET['post_type'])) === 'product'
            && sanitize_key(wp_unslash($_GET['page'])) === 'product-reviews';

        if (!$is_comment_screen && !$is_product_reviews_screen) {
            return;
        }

        wp_safe_redirect(admin_url());
        exit;
    }
}
