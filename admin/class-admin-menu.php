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
    }

    public function add_admin_menu() {
        add_menu_page(
            'CodesWholesale Settings',
            'CodesWholesale',
            'manage_options',
            'wc-codeswholesale-settings',
            array($this, 'render_admin_page'),
            'dashicons-admin-generic',
            99
        );
    }

    public function register_settings() {
        register_setting('wc_codeswholesale_options_group', 'wc_codeswholesale_client_id');
        register_setting('wc_codeswholesale_options_group', 'wc_codeswholesale_client_secret');
        register_setting('wc_codeswholesale_options_group', 'wc_codeswholesale_sandbox_mode');
    }

    public function render_admin_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        require_once plugin_dir_path(__FILE__) . 'views/admin-page.php';
    }
}
