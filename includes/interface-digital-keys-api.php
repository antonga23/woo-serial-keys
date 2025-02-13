<?php

if (!defined('ABSPATH')) {
    exit;
}

interface WC_Digital_Keys_API_Interface {
    public function generate_access_token();
    public function get_current_token();
    public function fetch_platforms();
    public function fetch_products($params = array());
    public function create_order($order_data);
    public function fetch_product_image($product_id, $format = 'MEDIUM');
}

abstract class WC_Digital_Keys_API_Base implements WC_Digital_Keys_API_Interface {
    protected $logger;
    protected $sandbox_mode;

    public function __construct($logger) {
        $this->logger = $logger;
        $this->sandbox_mode = get_option('wc_digital_keys_sandbox_mode', false);
    }

    protected function make_request($endpoint, $method = 'GET', $body = null) {
        $token = $this->get_current_token();
        if (!$token) {
            return array('success' => false, 'message' => 'Failed to obtain access token');
        }

        $args = $this->prepare_request_args($method, $token, $body);
        $response = wp_remote_request($this->get_api_url($endpoint), $args);

        if (is_wp_error($response)) {
            $this->logger->log('error', 'API request failed: ' . $response->get_error_message());
            return array('success' => false, 'message' => $response->get_error_message());
        }

        return $this->handle_response($response);
    }

    abstract protected function get_api_url($endpoint);
    abstract protected function prepare_request_args($method, $token, $body = null);
    abstract protected function handle_response($response);
}