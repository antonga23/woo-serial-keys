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
        add_action('plugins_loaded', array($this, 'init_plugin'));
    }

    public function init_plugin() {
        if (!class_exists('WooCommerce')) {
            add_action('admin_notices', array($this, 'woocommerce_missing_notice'));
            return;
        }

        $this->init_components();
        $this->init_hooks();
    }

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
        $this->admin_menu = new WC_CodesWholesale_Admin_Menu($this);
    }

    private function init_hooks() {
        // Admin post handlers
        add_action('admin_post_test_endpoint', array($this, 'handle_test_endpoint'));
        add_action('admin_post_fetch_cw_platforms', array($this, 'handle_fetch_platforms'));
        add_action('admin_post_fetch_cw_products', array($this, 'handle_fetch_products'));
    }

    public function woocommerce_missing_notice() {
        ?>
        <div class="error">
            <p><?php _e('CodesWholesale Integration requires WooCommerce to be installed and active.', 'woocommerce-codeswholesale'); ?></p>
        </div>
        <?php
    }

    public function handle_test_endpoint() {
        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'test_endpoint_nonce')) {
            wp_die('Security check failed');
        }

        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized access');
        }

        $sandbox_mode = get_option('wc_codeswholesale_sandbox_mode') ? true : false;
        $endpoint = $sandbox_mode
            ? 'https://sandbox.codeswholesale.com/oauth/token'
            : 'https://api.codeswholesale.com/oauth/token';

        $response = wp_remote_get($endpoint);

        if (is_wp_error($response)) {
            $message = 'Endpoint test failed: ' . $response->get_error_message();
            $type = 'error';
            $this->logger->log('error', $message);
        } else {
            $response_code = wp_remote_retrieve_response_code($response);
            if ($response_code === 405 || $response_code === 401) {
                $message = 'Endpoint test successful! The endpoint is accessible.';
                $type = 'updated';
                $this->logger->log('info', 'Endpoint test successful');
            } else {
                $message = 'Endpoint test failed: Unexpected response code ' . $response_code;
                $type = 'error';
                $this->logger->log('error', $message);
            }
        }

        add_settings_error(
            'wc_codeswholesale_messages',
            'endpoint_test',
            $message,
            $type
        );

        wp_redirect(add_query_arg(
            array('page' => 'wc-codeswholesale-settings'),
            admin_url('admin.php')
        ));
        exit;
    }

    public function handle_fetch_platforms() {
        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'fetch_cw_platforms_nonce')) {
            wp_die('Security check failed');
        }

        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized access');
        }

        $response = $this->api_handler->fetch_platforms();

        if ($response['success']) {
            add_settings_error(
                'wc_codeswholesale_messages',
                'platforms_fetch',
                'Platforms fetched successfully!',
                'updated'
            );
        } else {
            add_settings_error(
                'wc_codeswholesale_messages',
                'platforms_fetch',
                'Failed to fetch platforms: ' . $response['message'],
                'error'
            );
        }

        wp_redirect(add_query_arg(
            array('page' => 'wc-codeswholesale-settings'),
            admin_url('admin.php')
        ));
        exit;
    }

    public function handle_fetch_products() {
        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'fetch_cw_products_nonce')) {
            wp_die('Security check failed');
        }

        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized access');
        }

        $response = $this->api_handler->fetch_products();

        if ($response['success']) {
            // Create/update WooCommerce products
            $this->product_manager->create_or_update_woocommerce_products($response['data']);

            add_settings_error(
                'wc_codeswholesale_messages',
                'products_fetch',
                'Products fetched and synchronized successfully!',
                'updated'
            );
        } else {
            add_settings_error(
                'wc_codeswholesale_messages',
                'products_fetch',
                'Failed to fetch products: ' . $response['message'],
                'error'
            );
        }

        wp_redirect(add_query_arg(
            array('page' => 'wc-codeswholesale-settings'),
            admin_url('admin.php')
        ));
        exit;
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
        wp_die('CodesWholesale Integration plugin deactivated');
        // Log deactivation
        if (class_exists('WC_Logger')) {
            $logger = wc_get_logger();
            $logger->info('CodesWholesale Integration plugin deactivated', array('source' => 'codeswholesale-integration'));
        }
    }
}
