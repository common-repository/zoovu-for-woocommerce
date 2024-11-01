<?php
/**
 * Plugin Name: Zoovu for WooCommerce
 * Description: Integrates WooCommerce with the Zoovu digital commerce search platform
 * Author: Zoovu
 * Author URI: https://zoovu.com
 * Version: 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// Add autoloader
require __DIR__ . '/vendor/autoload.php';

class ZoovuFeeds
{
    /**
     * Plugin locale slug
     */
    const LOCALE_SLUG = 'progressus-zoovu-feed';

    /**
     * Plugin version
     */
    const VERSION = '1.0.0';

    /**
     * @var ZoovuFeeds
     */
    private static $instance;

    /**
     * ZoovuFeeds constructor.
     */
    private function __construct()
    {
        $this->setLocalization();

        if ( class_exists( 'woocommerce' ) ) {
            $this->loadFunctionality();
            $this->addAssets();
        } else {
            \Progressus\Zoovu\Helpers\Notice::error(__('Zoovu for WooCommerce requires WooCommerce plugin enabled!', self::LOCALE_SLUG), false);
        }
    }

    /**
     * Plugin localization parameters
     */
    private function setLocalization()
    {
        load_plugin_textdomain(
            self::LOCALE_SLUG,
            false,
            dirname( plugin_basename( __FILE__ ) ) . '/languages'
        );
    }

    private function loadFunctionality()
    {
        add_action('init', function () {
            // Load Options
            \Progressus\Zoovu\Options::load();

            // Register Plugin Events
            \Progressus\Zoovu\Feeds\FeedEvents::register();

            // Register Post Types
            \Progressus\Zoovu\Types::register();

            // Add Plugin Pages and page functionality
            \Progressus\Zoovu\Pages::register();

            // Add Page settings
            \Progressus\Zoovu\PageSettings::register();

            // Add Track Script
            \Progressus\Zoovu\TrackScript::register();
        });

        // Add Integration to Woo Integrations
        \Progressus\Zoovu\WC\IntegrationLoader::init();
    }

    private function addAssets()
    {
        add_action('admin_enqueue_scripts', function ($hook) {
            if ("woocommerce_page_zoovo-feeds-create" !== $hook) {
                return;
            }

            wp_enqueue_script('zoovu-feeds', plugin_dir_url(__FILE__) . 'dist/app.js', ['jquery'], null, true);
            wp_enqueue_style('zoovu-feeds-css', plugin_dir_url(__FILE__). 'dist/app.css', [], null);
        });
    }

    /**
     * Make sure we have only single instance of this class
     *
     * @return ZoovuFeeds
     */
    public static function getInstance()
    {
        if (! isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}

if (! function_exists('ZOVUFEED')) {
    /**
     * @return ZoovuFeeds
     */
    function ZOVUFEED()
    {
        return ZoovuFeeds::getInstance();
    }

    ZOVUFEED();
}

