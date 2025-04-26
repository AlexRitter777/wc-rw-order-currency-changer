<?php

/**
 * Plugin Name: WooCommerce RW Order Currency Changer
 * Description: A plugin to change the order and payment currency in WooCommerce based on a specified currency and exchange rate. Ensures recalculation of order totals, updates metadata, and displays the correct amounts on the checkout page, emails, and payments. Note: The plugin does not support taxes or additional fees, as it is designed for use cases where such charges are not applicable. Compatible with Fox Currency Switcher for seamless currency handling.
 * Version: 1.2.2
 * Author: Alexej Bogačev (RAIN WOOLF s.r.o.)
 * Text Domain: wc-rw-order-currency-changer
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit; // Prevent direct access
}


/**
 * Main class for the WooCommerce RW Order Currency Changer.
 */
class Wc_Rw_Order_Currency_Changer
{

    const VERSION = '1.2.2';

    /**
     * Wc_Rw_Order_Currency_Changer constructor.
     * Initializes the plugin by registering hooks.
     */
    public function __construct()
    {

        $this->register_hooks();
    }


    /**
     * Registers all necessary hooks for the plugin.
     */
    private function register_hooks()
    {
        // Load the text domain for translations
        add_action('plugins_loaded', [$this, 'wc_rw_load_text_domain']);

        // Initialize the plugin's main functionality
        add_action('plugins_loaded', [$this, 'wc_rw_initialize_plugin']);

        // Load scripts and styles common for entire plugin
        add_action('wp_enqueue_scripts', [$this, 'wc_rw_load_public_scripts']);

        // Add a custom settings page to WooCommerce
        add_filter('plugins_loaded', [$this, 'wc_rw_create_woocommerce_custom_settings_page'], 10);

    }

    /**
     * Initializes the plugin.
     *
     * - Loads required classes for the plugin's functionality.
     * - Checks if WooCommerce is active; deactivates the plugin if not.
     * - Initializes the main functionality of the plugin.
     * - Handles deactivation if WooCommerce is deactivated.
     */
    public function wc_rw_initialize_plugin()
    {
        $this->wc_rw_load_classes();

        // Check if WooCommerce is active and compatible
        Wc_Rw_Woocommerce_Checker::check_initialization(plugin_basename(__FILE__));

        // Initialize the core plugin logic
        new Wc_Rw_Order_Currency_Changer_Init();

        // Handle the case when WooCommerce is deactivated
        Wc_Rw_Woocommerce_Checker::handle_woocommerce_deactivation(plugin_basename(__FILE__));
    }


    /**
     * Loads required classes for the plugin's functionality.
     */
    private function wc_rw_load_classes()
    {
        require_once WP_PLUGIN_DIR . '/wc-rw-order-currency-changer/includes/class-wc-rw-order-currency-changer-init.php';
        require_once WP_PLUGIN_DIR . '/wc-rw-order-currency-changer/includes/class-wc-rw-debug.php';
    }


    /**
     * Load public scripts and styles. Only for checkout page.
     */
    public function wc_rw_load_public_scripts()
    {

        if (is_checkout()) {

            // Load the main CSS file for the plugin
            wp_enqueue_style(
                'wc-rw-order-currency-changer-style',
                plugins_url('assets/css/style.css', __FILE__),
                array(),
                Wc_Rw_Order_Currency_Changer::VERSION
            );
        }
    }

    /**
     * Load the plugin text domain for translations.
     */
    public function wc_rw_load_text_domain()
    {
        // Load the text domain from the /languages directory
        load_plugin_textdomain('wc-rw-order-currency-changer', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }


    /**
     * Adds a custom settings page to WooCommerce.
     *
     * - Ensures the WC_Settings_Page class is loaded if not already.
     * - Loads the custom settings page class for the plugin.
     * - Registers the custom settings page using the WooCommerce filter.
     *
     * @param array $settings Existing WooCommerce settings pages.
     * @return void
     */
    public function wc_rw_create_woocommerce_custom_settings_page($settings){

        if (!class_exists('WC_Settings_Page')) {
            require_once WP_PLUGIN_DIR . '/woocommerce/includes/admin/settings/class-wc-settings-page.php';
        }

        require_once WP_PLUGIN_DIR . '/wc-rw-order-currency-changer/includes/class-wc-rw-order-currency-changer-woo-settings.php';

        add_filter('woocommerce_get_settings_pages', function($settings) {
            $settings[] = new Wc_Rw_Order_Currency_Changer_Woo_Settings();
            return $settings;
        });
    }



}

/**
 * Initialize and return an instance of the main plugin class.
 *
 * @return Wc_Rw_Order_Currency_Changer
 */
function wc_rw_order_currency_changer(): Wc_Rw_Order_Currency_Changer
{
    return new Wc_Rw_Order_Currency_Changer();
}

require_once WP_PLUGIN_DIR . '/wc-rw-order-currency-changer/includes/class-wc-rw-woocommerce-checker.php';
register_activation_hook(__FILE__, [Wc_Rw_Woocommerce_Checker::class, 'check_activation']);

// Start the plugin execution.
wc_rw_order_currency_changer();