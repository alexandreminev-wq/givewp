<?php

namespace Give\Shared;

defined('ABSPATH') || exit;

/**
 * Shared design-system styles for GiveWP's admin screens: a flag, the screens they apply to, and
 * what gets loaded. Off by default; with the flag off nothing is added to any page.
 *
 * @since TBD
 */
final class Loader
{
    /**
     * The option that switches the shared styles on ('1') or off ('0', the default).
     * Set it with: wp option update givewp_shared_styles 1
     *
     * @since TBD
     */
    public const FLAG = 'givewp_shared_styles';

    /**
     * The class the mapping in mapping.css is scoped to.
     *
     * @since TBD
     */
    public const BODY_CLASS = 'uc-themed';

    /**
     * @since TBD
     */
    public static function register(): void
    {
        add_filter('admin_body_class', [self::class, 'addBodyClass']);
        // Priority 15: after the callbacks GiveWP hooks on admin_print_styles at 10 (the form builder
        // enqueues its CSS there) and before WordPress prints the queue at 20, so these print last.
        add_action('admin_print_styles', [self::class, 'enqueue'], 15);
    }

    /**
     * @since TBD
     */
    public static function isEnabled(): bool
    {
        return '1' === get_option(self::FLAG, '0');
    }

    /**
     * Whether this admin request is one of GiveWP's screens: any screen of the give_forms post type
     * (forms, campaigns, donors, donations, reports, settings, ...) or a hidden page such as the
     * form builder, whose screen id contains "give".
     *
     * @since TBD
     */
    public static function isGiveScreen(): bool
    {
        $screen = function_exists('get_current_screen') ? get_current_screen() : null;

        if (!$screen) {
            return false;
        }

        return 'give_forms' === $screen->post_type || false !== strpos((string)$screen->id, 'give');
    }

    /**
     * Add the class the mapping is scoped to. Admin body classes are a single string.
     *
     * @since TBD
     *
     * @param string $classes
     */
    public static function addBodyClass($classes): string
    {
        if (self::isEnabled() && self::isGiveScreen()) {
            $classes .= ' ' . self::BODY_CLASS;
        }

        return $classes;
    }

    /**
     * Load the token file, then the mapping.
     *
     * @since TBD
     */
    public static function enqueue(): void
    {
        if (!self::isEnabled() || !self::isGiveScreen()) {
            return;
        }

        $version = require __DIR__ . '/version.php';
        $url = plugin_dir_url(__FILE__);

        wp_enqueue_style('givewp-undercurrent', $url . 'undercurrent.css', [], $version);
        wp_enqueue_style('givewp-undercurrent-mapping', $url . 'mapping.css', ['givewp-undercurrent'], $version);
    }
}

Loader::register();
