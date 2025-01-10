<?php

/**
 * WooCommerce CodesWholesale Integration
 * Provides core integration functionality between WooCommerce and CodesWholesale,
 * managing product synchronization, order processing, and plugin interactions.
 * @package     WooCommerce_CodesWholesale
 * @author      Alatha Ntonga
 * @license     GPL-2.0+
 *
 * @wordpress-plugin
 * Plugin Name: WooCommerce CodesWholesale Integration
 * Plugin URI:  [Your plugin URI]
 * Description: Integrates WooCommerce with CodesWholesale API for digital product distribution
 * Version:     1.1.0
 * Author:      Alatha Ntonga
 * Text Domain: woocommerce-codeswholesale
 */

if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('WC_CODESWHOLESALE_VERSION', '1.1.0');
define('WC_CODESWHOLESALE_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WC_CODESWHOLESALE_PLUGIN_URL', plugin_dir_url(__FILE__));

// Autoloader for plugin classes/**
spl_autoload_register(function ($class) {
    // Only handle our namespace
    if (strpos($class, 'WC_CodesWholesale_') !== 0) {
        return;
    }

    // Convert class name to file path
    $class_path = str_replace('WC_CodesWholesale_', '', $class);
    $class_path = str_replace('_', '-', strtolower($class_path));
    $file = WC_CODESWHOLESALE_PLUGIN_DIR . 'includes/class-' . $class_path . '.php';

    // Check if file exists in includes directory
    if (file_exists($file)) {
        require_once $file;
        return;
    }

    // Check if file exists in includes/order directory
    $order_file = WC_CODESWHOLESALE_PLUGIN_DIR . 'includes/order/class-' . $class_path . '.php';
    if (file_exists($order_file)) {
        require_once $order_file;
        return;
    }

    // Check if file exists in admin directory
    $admin_file = WC_CODESWHOLESALE_PLUGIN_DIR . 'admin/class-' . $class_path . '.php';
    if (file_exists($admin_file)) {
        require_once $admin_file;
        return;
    }
});

// Initialize the plugin
function wc_codeswholesale_init() {
    if (!class_exists('WooCommerce')) {
        add_action('admin_notices', 'wc_codeswholesale_woocommerce_missing_notice');
        return;
    }
    
    return new WC_CodesWholesale_Integration();
}

// WooCommerce missing notice
function wc_codeswholesale_woocommerce_missing_notice() {
    ?>
    <div class="error">
        <p><?php _e('CodesWholesale Integration requires WooCommerce to be installed and active.', 'woocommerce-codeswholesale'); ?></p>
    </div>
    <?php
}

// Initialize plugin
add_action('plugins_loaded', 'wc_codeswholesale_init');

// Register activation and deactivation hooks
register_activation_hook(__FILE__, array('WC_CodesWholesale_Integration', 'activate'));
register_deactivation_hook(__FILE__, array('WC_CodesWholesale_Integration', 'deactivate'));