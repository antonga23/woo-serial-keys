<?php
if (!defined('ABSPATH')) {
    exit;
}

class WC_Kinguin_Order_Statuses {
    public function __construct() {
        add_action('init', array($this, 'register_order_statuses'));
        add_filter('wc_order_statuses', array($this, 'add_order_statuses'));
    }

    public function register_order_statuses() {
        register_post_status('wc-kinguin-pending', array(
            'label' => 'Kinguin Pending',
            'public' => true,
        ));
    }

    public function add_order_statuses($order_statuses) {
        $order_statuses['wc-kinguin-pending'] = __('Kinguin Pending', 'woocommerce');
        return $order_statuses;
    }
}