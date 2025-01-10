<?php

if (!defined('ABSPATH')) {
    exit;
}

class WC_CodesWholesale_Order_Manager {
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

        // Change order status to "pending delivery"
        $order->update_status('pending-delivery', 'Order marked as pending delivery before contacting CodesWholesale');

        // Prepare order data
        $order_data = $this->prepare_order_data($order);

        // Create order in CodesWholesale
        $response = $this->api_handler->create_order($order_data);

        if (!$response['success']) {
            $this->handle_failed_order($order, $order_data, $response);
            return;
        }

        // Update order status based on response
        $this->process_successful_order($order, $response['data']);
    }

    private function prepare_order_data($order) {
        $products = array();
        foreach ($order->get_items() as $item) {
            $product = $item->get_product();
            if (!$product) {
                continue;
            }

            $products[] = array(
                'productId' => $product->get_sku() ?: ('no-sku-' . $product->get_id()),
                'price'     => (float) $product->get_price(),
                'quantity'  => (int) $item->get_quantity()
            );
        }

        return array(
            'allowPreOrder' => true,
            'orderId'      => wp_generate_uuid4(),
            'products'     => $products
        );
    }

    private function process_successful_order($order, $response_data) {
        $order->update_status('codeswhole-order-pending', 'Order is now pending codes delivery confirmation');

        $cw_order_status = isset($response_data['status']) ? $response_data['status'] : '';

        if ($cw_order_status === 'Completed') {
            $this->process_completed_order($order, $response_data);
        }
    }

    private function process_completed_order($order, $response_data) {
        $order->update_status('codes-delivered', 'Codes have been delivered successfully.');
        
        $delivered_codes = $this->extract_delivered_codes($order, $response_data);
        
        if (!empty($delivered_codes)) {
            update_post_meta($order->get_id(), 'order_product_codes', $delivered_codes);
            $this->send_codes_email($order, $delivered_codes);
        }
    }

    private function extract_delivered_codes($order, $response_data) {
        $delivered_codes = array();
        $response_products = isset($response_data['products']) ? $response_data['products'] : array();

        // Create lookup map for response products
        $response_map = array();
        foreach ($response_products as $resp_product) {
            if (isset($resp_product['productId'], $resp_product['codes'][0]['code'])) {
                $response_map[$resp_product['productId']] = array(
                    'name' => $resp_product['name'],
                    'code' => $resp_product['codes'][0]['code']
                );
            }
        }

        // Match order items to response products
        foreach ($order->get_items() as $item) {
            $product = $item->get_product();
            if (!$product) {
                continue;
            }

            $product_identifier = $product->get_sku() ?: ('no-sku-' . $product->get_id());

            if (isset($response_map[$product_identifier])) {
                $delivered_codes[] = array(
                    'product_id' => $product->get_id(),
                    'name'       => $response_map[$product_identifier]['name'],
                    'code'       => $response_map[$product_identifier]['code']
                );
            }
        }

        return $delivered_codes;
    }

    private function send_codes_email($order, $delivered_codes) {
        $customer_email = $order->get_billing_email();
        $customer_name = $order->get_billing_first_name();

        $body = "Hello {$customer_name},\n\n"
             . "Find the keys for your order below:\n\n";

        foreach ($delivered_codes as $code_info) {
            $body .= "Product: {$code_info['name']}\n"
                  . "Code: {$code_info['code']}\n\n";
        }

        $body .= "Thank you for shopping with us!";

        wp_mail(
            $customer_email,
            'Your Order Keys from Our Store',
            $body,
            array('Content-Type: text/plain; charset=UTF-8')
        );
    }

    private function handle_failed_order($order, $order_data, $response) {
        $admin_email = get_option('admin_email');
        $subject = 'CodesWholesale Order Creation Failed';
        $body = "There was an error creating the order for order #{$order->get_id()}.\n\n"
             . "Order Data:\n"
             . print_r($order_data, true) . "\n\n"
             . "Response:\n"
             . print_r($response, true);

        wp_mail(
            $admin_email,
            $subject,
            $body,
            array('Content-Type: text/plain; charset=UTF-8')
        );
    }
}
