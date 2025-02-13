<?php

if (!defined('ABSPATH')) {
    exit;
}
/**
 * WooCommerce CodesWholesale Order Management
 *
 * Handles the processing, tracking, and management of digital product orders
 * within the CodesWholesale integration for WooCommerce.
 *
 * @package WooCommerce_CodesWholesale
 * @subpackage Orders
 * @since 1.1.0
 */
class WC_CodesWholesale_Order_Statuses {
    public function __construct() {
        add_action('init', array($this, 'register_order_statuses'));
        add_filter('wc_order_statuses', array($this, 'add_order_statuses'));
    }

    public function register_order_statuses() {
        register_post_status('wc-pending-delivery', array(
            'label'                     => 'Pending Delivery',
            'public'                    => true,
            'exclude_from_search'       => false,
            'show_in_admin_all_list'    => true,
            'show_in_admin_status_list' => true,
            'label_count'               => _n_noop('Pending Delivery <span class="count">(%s)</span>', 'Pending Delivery <span class="count">(%s)</span>')
        ));

        register_post_status('wc-codeswhole-order-pending', array(
            'label'                     => 'CodesWhole Order Pending',
            'public'                    => true,
            'exclude_from_search'       => false,
            'show_in_admin_all_list'    => true,
            'show_in_admin_status_list' => true,
            'label_count'               => _n_noop('CodesWhole Order Pending <span class="count">(%s)</span>', 'CodesWhole Order Pending <span class="count">(%s)</span>')
        ));

        register_post_status('wc-codes-delivered', array(
            'label'                     => 'Codes Delivered',
            'public'                    => true,
            'exclude_from_search'       => false,
            'show_in_admin_all_list'    => true,
            'show_in_admin_status_list' => true,
            'label_count'               => _n_noop('Codes Delivered <span class="count">(%s)</span>', 'Codes Delivered <span class="count">(%s)</span>')
        ));
    }

    public function add_order_statuses($order_statuses) {
        $new_statuses = array();

        foreach ($order_statuses as $key => $status) {
            $new_statuses[$key] = $status;

            if ('wc-completed' === $key) {
                $new_statuses['wc-pending-delivery'] = 'Pending Delivery';
                $new_statuses['wc-codeswhole-order-pending'] = 'CodesWhole Order Pending';
                $new_statuses['wc-codes-delivered'] = 'Codes Delivered';
            }
        }

        return $new_statuses;
    }
}
