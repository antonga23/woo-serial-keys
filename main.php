<?php
/**
 * Plugin Name: Digital Keys Integration for WooCommerce
 * Plugin URI: 
 * Description: Integrates digital key providers like CodesWholesale and Kinguin with WooCommerce
 * Version: 1.1.0
 * Author: 
 * Author URI: 
 * Text Domain: wc-digitalkeys
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 7.2
 * WC requires at least: 4.0
 * WC tested up to: 8.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('WC_DIGITALKEYS_VERSION', '1.1.0');
define('WC_DIGITALKEYS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WC_DIGITALKEYS_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once WC_DIGITALKEYS_PLUGIN_DIR . 'includes/interface-digital-keys-api.php';
require_once WC_DIGITALKEYS_PLUGIN_DIR . 'includes/class-digital-keys-integration.php';
require_once WC_DIGITALKEYS_PLUGIN_DIR . 'admin/class-admin-menu.php';


// Autoloader for plugin classes
// Updated autoloader with admin directory support
spl_autoload_register(function ($class) {
    $prefixes = array(
        'WC_CodesWholesale_' => array(
            'includes/codeswholesale',
            'admin'  // Add admin directory to search paths
        ),
        'WC_Kinguin_' => array(
            'includes/kinguin',
            'admin'
        ),
        'WC_Digital_Keys_' => 'includes'
    );

    foreach ($prefixes as $prefix => $directories) {
        if (strpos($class, $prefix) === 0) {
            $class_path = str_replace($prefix, '', $class);
            $class_path = str_replace('_', '-', strtolower($class_path));
            
            // Handle both single directory and array of directories
            $search_dirs = is_array($directories) ? $directories : array($directories);
            
            foreach ($search_dirs as $dir) {
                $file_path = WC_DIGITALKEYS_PLUGIN_DIR . "{$dir}/class-{$class_path}.php";
                if (file_exists($file_path)) {
                    require_once $file_path;
                    return;
                }
            }
        }
    }
});

// Initialize the plugin
function wc_digitalkeys_init() {
    if (!class_exists('WooCommerce')) {
        add_action('admin_notices', 'wc_digitalkeys_woocommerce_missing_notice');
        return;
    }
    
    global $wc_digitalkeys;
    $wc_digitalkeys = new WC_Digital_Keys_Integration();
    return $wc_digitalkeys;
}

// WooCommerce missing notice
function wc_digitalkeys_woocommerce_missing_notice() {
    ?>
    <div class="error">
        <p><?php _e('Digital Keys Integration requires WooCommerce to be installed and active.', 'wc-digitalkeys'); ?></p>
    </div>
    <?php
}

// Initialize plugin
add_action('plugins_loaded', 'wc_digitalkeys_init');

// Register activation and deactivation hooks
register_activation_hook(__FILE__, array('WC_Digital_Keys_Integration', 'activate'));
register_deactivation_hook(__FILE__, array('WC_Digital_Keys_Integration', 'deactivate'));