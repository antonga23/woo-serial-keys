<?php
if (!defined('ABSPATH')) {
    exit;
}

class WC_Kinguin_Integration {
    private $logger;
    private $api_handler;
    private $order_manager;
    private $product_manager;
    private $order_statuses;

    public function __construct() {
        $this->logger = wc_get_logger();
        $this->api_handler = new WC_Kinguin_API_Handler($this->logger);
        $this->order_manager = new WC_Kinguin_Order_Manager($this->api_handler, $this->logger);
        $this->product_manager = new WC_Kinguin_Product_Manager($this->api_handler, $this->logger);
        $this->order_statuses = new WC_Kinguin_Order_Statuses();
    }

    public static function activate() {
        flush_rewrite_rules();
    }

    public static function deactivate() {
        unregister_post_status('wc-kinguin-pending');
        flush_rewrite_rules();
    }
}