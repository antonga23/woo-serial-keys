<?php
if (!defined('ABSPATH')) {
    exit;
}

class WC_Kinguin_Order_Manager {
    private $api_handler;
    private $logger;

    public function __construct($api_handler, $logger) {
        $this->api_handler = $api_handler;
        $this->logger = $logger;
        add_action('woocommerce_order_status_completed', array($this, 'handle_completed_order'), 10, 1);
    }

    public function handle_completed_order($order_id) {
        $order = wc_get_order($order_id);
        if (!$order) {
            return;
        }

        $order_data = $this->prepare_order_data($order);
        $response = $this->api_handler->create_order($order_data);

        if ($response['success']) {
            $order->add_order_note('Order synced with Kinguin: ' . $response['data']['order_id']);
        } else {
            $this->logger->error('Failed to create order on Kinguin: ' . $response['message']);
            $order->add_order_note('Failed to sync order with Kinguin: ' . $response['message']);
        }
    }

    private function prepare_order_data($order) {
        $data = array(
            'order_id' => $order->get_id(),
            'customer_email' => $order->get_billing_email(),
            'items' => array()
        );

        foreach ($order->get_items() as $item) {
            $product = $item->get_product();
            $data['items'][] = array(
                'product_id' => $product->get_id(),
                'quantity' => $item->get_quantity()
            );
        }

        return $data;
    }
}