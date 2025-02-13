<?php

if (!defined('ABSPATH')) {
    exit;
}
/**
 * CodesWholesale Admin Page Template
 *
 * This file contains the HTML template for the CodesWholesale admin settings page.
 * It provides a user interface for configuring and managing CodesWholesale 
 * integration settings within the WordPress admin dashboard.
 *
 * @package WooCommerce_CodesWholesale
 * @subpackage Admin_Views
 * @since 1.0.0
 */
class WC_CodesWholesale_Admin_Menu {
    private $integration;
    
    public function __construct($integration) {
        $this->integration = $integration;
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        $this->init_hooks();
    }


    private function init_hooks() {
        // Admin post handlers
        add_action('admin_post_generate_cw_token', array($this, 'handle_generate_token'));
        add_action('admin_post_test_endpoint', array($this, 'handle_test_endpoint'));
        add_action('admin_post_save_cw_settings', array($this, 'handle_save_cw_settings'));
    }
    
    public function add_admin_menu() {
        // Add main menu item
        add_menu_page(
            'Woo-Digital-Keys',          // Page title
            'Woo-Digital-Keys',          // Menu title
            'manage_options',            // Capability
            'woo-digital-keys',          // Menu slug
            array($this, 'render_general_settings'), // Callback function
            'dashicons-admin-generic',   // Icon
            99                          // Position
        );
        
        // Add submenu items
        add_submenu_page(
            'woo-digital-keys',         // Parent slug
            'Codeswholesale',           // Page title
            'Codeswholesale',           // Menu title
            'manage_options',           // Capability
            'woo-digital-keys-cw',      // Menu slug
            array($this, 'render_codeswholesale_page') // Callback function
        );
        
        add_submenu_page(
            'woo-digital-keys',         // Parent slug
            'Kinguin',                  // Page title
            'Kinguin',                  // Menu title
            'manage_options',           // Capability
            'woo-digital-keys-kinguin', // Menu slug
            array($this, 'render_kinguin_page') // Callback function
        );
        
        // Rename the default submenu item to "General Settings"
        global $submenu;
        if (isset($submenu['woo-digital-keys'])) {
            $submenu['woo-digital-keys'][0][0] = 'General Settings';
        }
    }
    
    public function register_settings() {
        // Codeswholesale settings
        register_setting('wc_codeswholesale_options_group', 'wc_codeswholesale_client_id');
        register_setting('wc_codeswholesale_options_group', 'wc_codeswholesale_client_secret');
        register_setting('wc_codeswholesale_options_group', 'wc_codeswholesale_sandbox_mode');
        
        // Kinguin settings
        register_setting('wc_kinguin_options_group', 'wc_kinguin_api_key');
        register_setting('wc_kinguin_options_group', 'wc_kinguin_environment');
        register_setting('wc_kinguin_options_group', 'wc_kinguin_webhook_url');
        
        // General settings
        register_setting('wc_digital_keys_general_options', 'wc_digital_keys_general_settings');
    }

    // public function handle_test_endpoint() {
    //     if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'test_endpoint_nonce')) {
    //         wp_die('Security check failed');
    //     }

    //     if (!current_user_can('manage_options')) {
    //         wp_die('Unauthorized access');
    //     }

    //     $sandbox_mode = get_option('wc_codeswholesale_sandbox_mode') ? true : false;
    //     $endpoint = $sandbox_mode
    //         ? 'https://sandbox.codeswholesale.com/oauth/token'
    //         : 'https://api.codeswholesale.com/oauth/token';

    //     $response = wp_remote_get($endpoint);

    //     if (is_wp_error($response)) {
    //         $message = 'Endpoint test failed: ' . $response->get_error_message();
    //         $type = 'error';
    //         $this->logger->log('error', $message);
    //     } else {
    //         $response_code = wp_remote_retrieve_response_code($response);
    //         if ($response_code === 405 || $response_code === 401) {
    //             $message = 'Endpoint test successful! The endpoint is accessible.';
    //             $type = 'updated';
    //             $this->logger->log('info', 'Endpoint test successful');
    //         } else {
    //             $message = 'Endpoint test failed: Unexpected response code ' . $response_code;
    //             $type = 'error';
    //             $this->logger->log('error', $message);
    //         }
    //     }

    //     add_settings_error(
    //         'wc_codeswholesale_messages',
    //         'endpoint_test',
    //         $message,
    //         $type
    //     );

    //     wp_redirect(add_query_arg(
    //         array('page' => 'woo-digital-keys'),
    //         admin_url('admin.php')
    //     ));
    //     exit;
    // }

    public function handle_save_cw_settings()
    {
        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'save_cw_settings_nonce')) {
            wp_die('Security check failed');
        }

        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized access');
        }

        // Update your options manually
        $client_id = isset($_POST['wc_codeswholesale_client_id']) ? sanitize_text_field($_POST['wc_codeswholesale_client_id']) : '';
        $client_secret = isset($_POST['wc_codeswholesale_client_secret']) ? sanitize_text_field($_POST['wc_codeswholesale_client_secret']) : '';
        $sandbox_mode = isset($_POST['wc_codeswholesale_sandbox_mode']) ? 1 : 0;

        update_option('wc_codeswholesale_client_id', $client_id);
        update_option('wc_codeswholesale_client_secret', $client_secret);
        update_option('wc_codeswholesale_sandbox_mode', $sandbox_mode);

        add_settings_error(
            'wc_codeswholesale_messages',
            'settings_saved',
            'Settings saved successfully!',
            'updated'
        );
        $this->integration->get_logger()->info('Settings saved successfully', array('source' => 'woo-digital-keys-integration'));

        // Redirect back to your settings page without showing options.php
        wp_redirect(add_query_arg(array('page' => 'woo-digital-keys-cw', 'settings-updated' => 'true'), admin_url('admin.php')));
        exit;
    }
    public function handle_generate_token()
    {
        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'generate_cw_token_nonce')) {
            wp_die('Security check failed');
        }

        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized access');
        }

        delete_option('wc_codeswholesale_access_token');
        delete_option('wc_codeswholesale_token_expires');

        $token_data = $this->integration->get_api_handler()->get_current_token();

        if ($token_data) {
            add_settings_error(
                'wc_codeswholesale_messages',
                'token_generated',
                'Token generated successfully!',
                'updated'
            );
            $this->integration->get_logger()->info('Token generated successfully', array('source' => 'woo-digital-keys-integration'));
        } else {
            add_settings_error(
                'wc_codeswholesale_messages',
                'token_error',
                'Failed to generate token. Please check your credentials.',
                'error'
            );
            $this->integration->get_logger()->error('Failed to generate token', array('source' => 'woo-digital-keys-integration'));
        }

        wp_redirect(add_query_arg(
            array(
                'page' => 'woo-digital-keys-cw',
                'token-updated' => time()
            ),
            admin_url('admin.php')
        ));
        exit;
    }

    public function get_codeswholesale_status() {
        $client_id = get_option('wc_codeswholesale_client_id');
        $client_secret = get_option('wc_codeswholesale_client_secret');
        
        if (empty($client_id) || empty($client_secret)) {
            return '<span class="status-badge error" style="background: #d63638; color: white; padding: 3px 8px; border-radius: 3px;">Not Configured</span>';
        }

        $token_info = $this->integration->get_api_handler()->get_current_token();
        if ($token_info && !empty($token_info['access_token'])) {
            return '<span class="status-badge connected" style="background: #00a32a; color: white; padding: 3px 8px; border-radius: 3px;">Connected</span>';
        }

        return '<span class="status-badge error" style="background: #d63638; color: white; padding: 3px 8px; border-radius: 3px;">Connection Failed</span>';
    }

    public function get_kinguin_status() {
        $api_key = get_option('wc_kinguin_api_key');
        
        if (empty($api_key)) {
            return '<span class="status-badge error" style="background: #d63638; color: white; padding: 3px 8px; border-radius: 3px;">Not Configured</span>';
        }

        // In real implementation, would check API connection
        return '<span class="status-badge connected" style="background: #00a32a; color: white; padding: 3px 8px; border-radius: 3px;">Connected</span>';
    }
    
    public function render_codeswholesale_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        settings_errors('wc_codeswholesale_messages');
        
        if (isset($_GET['settings-updated'])) {
            add_settings_error(
                'wc_codeswholesale_messages',
                'wc_codeswholesale_message',
                'Settings Saved',
                'updated'
            );
        }

        $access_token = get_option('wc_codeswholesale_token_expiry');
        $token_info = [];
        if ($access_token) {
            $token_info['expires_in'] = get_option('wc_codeswholesale_token_expiry');
            $token_info['access_token'] = $access_token;
        }
        
        // Pass necessary data to the view
        $view_data = [
            'token_info' => $token_info,
            'client_id' => get_option('wc_codeswholesale_client_id'),
            'client_secret' => get_option('wc_codeswholesale_client_secret'),
            'sandbox_mode' => get_option('wc_codeswholesale_sandbox_mode'),
            'admin_post_url' => admin_url('admin-post.php'),
            'page_title' => 'Codeswholesale Settings',
            'test_data' => $this->integration->get_api_handler()->get_current_token()
        ];
        
        require_once plugin_dir_path(__FILE__) . 'views/codeswholesale-page.php';
    }
    
    public function render_kinguin_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        settings_errors('wc_kinguin_messages');
        
        // if (isset($_GET['settings-updated'])) {
        //     add_settings_error(
        //         'wc_kinguin_messages',
        //         'wc_kinguin_message',
        //         'Settings Saved',
        //         'updated'
        //     );
        // }
        
        require_once plugin_dir_path(__FILE__) . 'views/kinguin-page.php';
    }
    
    public function render_general_settings() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        settings_errors('wc_digital_keys_messages');
        
        // if (isset($_GET['settings-updated'])) {
        //     add_settings_error(
        //         'wc_digital_keys_messages',
        //         'wc_digital_keys_message',
        //         'Settings Saved',
        //         'updated'
        //     );
        // }
        
        require_once plugin_dir_path(__FILE__) . 'views/general-settings.php';
    }
}
