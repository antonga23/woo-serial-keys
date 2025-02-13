<?php
if (!defined('ABSPATH')) {
    exit;
}



class WC_CodesWholesale_Integration {
    private $logger;
    private $api_handler;
    private $order_manager;
    private $product_manager;
    private $order_statuses;
    private $admin_menu;

    public function __construct() {
        $this->init_components();
        // add_action('plugins_loaded', array($this, 'init_plugin'));
    }

    // public function init_plugin() {
    //     if (!class_exists('WooCommerce')) {
    //         add_action('admin_notices', array($this, 'woocommerce_missing_notice'));
    //         return;
    //     }

    //     $this->init_components();
    //     $this->init_hooks();
    // }

    private function init_components() {
        // Initialize logger
        if (class_exists('WC_Logger')) {
            $this->logger = wc_get_logger();
        }
        // Initialize components
        $this->api_handler = new WC_CodesWholesale_API_Handler($this->logger);
        $this->order_manager = new WC_CodesWholesale_Order_Manager($this->api_handler, $this->logger);
        $this->product_manager = new WC_CodesWholesale_Product_Manager($this->api_handler, $this->logger);
        $this->order_statuses = new WC_CodesWholesale_Order_Statuses();
        require_once WC_DIGITALKEYS_PLUGIN_DIR . 'admin/class-admin-menu.php';
        $this->admin_menu = new WC_CodesWholesale_Admin_Menu($this);
    }

    public function woocommerce_missing_notice() {
        ?>
        <div class="error">
            <p><?php _e('CodesWholesale Integration requires WooCommerce to be installed and active.', 'woocommerce-codeswholesale'); ?></p>
        </div>
        <?php
    }


    public function get_api_handler() {
        return $this->api_handler;
    }

    public function get_product_manager() {
        return $this->product_manager;
    }
    // get logger
    public function get_logger() {
        return $this->logger;
    }
    public static function activate() {
        if (!class_exists('WooCommerce')) {
            deactivate_plugins(plugin_basename(__FILE__));
            wp_die('This plugin requires WooCommerce to be installed and active.');
        }
    }

    public static function deactivate() {
        // Clean up tokens
        delete_option('wc_codeswholesale_access_token');
        delete_option('wc_codeswholesale_token_expires');
        delete_option('wc_codeswholesale_last_platforms');
        delete_option('wc_codeswholesale_last_products');
        // Log deactivation
        if (class_exists('WC_Logger')) {
            $logger = wc_get_logger();
            $logger->info('CodesWholesale Integration plugin deactivated', array('source' => 'woo-digital-keys-integration'));
        }
    }
}
