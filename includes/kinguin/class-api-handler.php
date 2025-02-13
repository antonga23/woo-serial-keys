<?php

if (!defined('ABSPATH')) {
    exit;
}

class WC_Kinguin_API_Handler extends WC_Digital_Keys_API_Base {
    private $api_key;

    public function __construct($logger) {
        parent::__construct($logger);
        $this->api_key = get_option('wc_kinguin_api_key');
    }

    protected function get_api_url($endpoint) {
        $base_url = $this->sandbox_mode ? 
            'https://sandbox-api.kinguin.net/api/v1/' : 
            'https://api.kinguin.net/api/v1/';
        return $base_url . ltrim($endpoint, '/');
    }

    public function generate_access_token() {
        $response = wp_remote_post($this->get_api_url('auth'), array(
            'headers' => array(
                'X-Api-Key' => $this->api_key,
                'Content-Type' => 'application/json'
            )
        ));

        if (is_wp_error($response)) {
            $this->logger->log('error', 'Failed to generate Kinguin token: ' . $response->get_error_message());
            return false;
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        if (isset($body['token'])) {
            update_option('wc_kinguin_access_token', $body['token']);
            update_option('wc_kinguin_token_expiry', time() + 3600); // Kinguin tokens typically expire in 1 hour
            return $body['token'];
        }

        return false;
    }

    public function get_current_token() {
        $token = get_option('wc_kinguin_access_token');
        $expiry = get_option('wc_kinguin_token_expiry', 0);

        if (!$token || time() >= $expiry) {
            return $this->generate_access_token();
        }

        return $token;
    }

    protected function prepare_request_args($method, $token, $body = null) {
        $args = array(
            'method' => $method,
            'headers' => array(
                'Authorization' => 'Bearer ' . $token,
                'X-Api-Key' => $this->api_key,
                'Content-Type' => 'application/json'
            )
        );

        if ($body !== null) {
            $args['body'] = json_encode($body);
        }

        return $args;
    }

    protected function handle_response($response) {
        $body = json_decode(wp_remote_retrieve_body($response), true);
        $code = wp_remote_retrieve_response_code($response);

        if ($code >= 200 && $code < 300) {
            return array('success' => true, 'data' => $body);
        }

        return array(
            'success' => false, 
            'message' => isset($body['message']) ? $body['message'] : 'Unknown error'
        );
    }

    public function fetch_platforms() {
        return $this->make_request('platforms');
    }

    public function fetch_products($params = array()) {
        $query = http_build_query($params);
        return $this->make_request('products' . ($query ? "?$query" : ''));
    }

    public function create_order($order_data) {
        // Transform WooCommerce order data to Kinguin format
        $kinguin_order = $this->transform_order_data($order_data);
        return $this->make_request('order', 'POST', $kinguin_order);
    }

    public function fetch_product_image($product_id, $format = 'MEDIUM') {
        return $this->make_request("products/$product_id/image");
    }

    private function transform_order_data($order_data) {
        // Transform order data to match Kinguin's API requirements
        $transformed = array(
            'products' => array_map(function($product) {
                return array(
                    'kinguinId' => $product['productId'],
                    'qty' => $product['quantity'],
                    'price' => $product['price']
                );
            }, $order_data['products'])
        );

        return $transformed;
    }
}