<?php

if (!defined('ABSPATH')) {
    exit;
}
/**
 * CodesWholesale API Handler
 *
 * Manages all API interactions between WooCommerce and the CodesWholesale platform.
 * Handles authentication, request processing, error management, and data synchronization.
 *
 * @package WooCommerce_CodesWholesale
 * @subpackage API
 * @since 1.1.0
 */
class WC_CodesWholesale_API_Handler {
    private $client_id;
    private $client_secret;
    private $sandbox_mode;
    private $logger;

    public function __construct($logger) {
        $this->client_id = get_option('wc_codeswholesale_client_id');
        $this->client_secret = get_option('wc_codeswholesale_client_secret');
        $this->sandbox_mode = get_option('wc_codeswholesale_sandbox_mode');
        $this->logger = $logger;
    }

    private function get_api_url($endpoint, $version = 'v2') {
        $base_url = $this->sandbox_mode ? 
            'https://sandbox.codeswholesale.com' : 
            'https://api.codeswholesale.com';
        
        // If endpoint already includes version prefix, use it as-is
        if (preg_match('#^/?v\d+/#', $endpoint)) {
            return $base_url . '/' . ltrim($endpoint, '/');
        }
        
        // If version is empty or false, don't add version prefix
        if (empty($version)) {
            return $base_url . '/' . ltrim($endpoint, '/');
        }
        
        // Add specified version prefix
        return $base_url . '/' . trim($version, '/') . '/' . ltrim($endpoint, '/');
    }

    private function log($message, $level = 'info')
    {
        if ($this->logger) {
            $context = array('source' => 'woo-digital-keys-integration');
            $this->logger->log($level, $message, $context);
        }
    }

    public function generate_access_token() {
        $token_url = $this->get_api_url('oauth/token', false); // No version prefix
        $args = array(
            'method' => 'POST',
            'timeout' => 45,
            'body' => array(
                'grant_type' => 'client_credentials',
                'client_id' => $this->client_id,
                'client_secret' => $this->client_secret
            )
        );

        $response = wp_remote_post($token_url, $args);

        if (is_wp_error($response)) {
            $this->log('Token generation failed: ' . $response->get_error_message(), 'error');
            return false;
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        if (isset($body['access_token'])) {
            update_option('wc_codeswholesale_access_token', $body['access_token']);
            update_option('wc_codeswholesale_token_expiry', time() + $body['expires_in']);
            $this->log('Access token generated successfully');
            return $body['access_token'];
        }

        return false;
    }

    public function get_current_token() {
        $token = get_option('wc_codeswholesale_access_token');
        $expiry = get_option('wc_codeswholesale_token_expiry', 0);

        if (!$token || time() >= $expiry) {
            return $this->generate_access_token();
        }

        return $token;
    }

    private function make_request($endpoint, $method = 'GET', $body = null) {
        $token = $this->get_current_token();
        if (!$token) {
            return array('success' => false, 'message' => 'Failed to obtain access token');
        }

        $args = array(
            'method' => $method,
            'headers' => array(
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json'
            )
        );

        if ($body !== null) {
            $args['body'] = json_encode($body);
        }

        $response = wp_remote_request($this->get_api_url($endpoint), $args);

        if (is_wp_error($response)) {
            $this->log('API request failed: ' . $response->get_error_message(), 'error');
            return array('success' => false, 'message' => $response->get_error_message());
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        $code = wp_remote_retrieve_response_code($response);

        if ($code >= 200 && $code < 300) {
            $this->log('API request successful' . 'Endpoint' . $endpoint, 'info');
            return array('success' => true, 'data' => $body);
        }

        return array('success' => false, 'message' => isset($body['message']) ? $body['message'] : 'Unknown error');
    }

    public function fetch_platforms() {
        return $this->make_request('platforms');
    }

    public function fetch_products($params = array()) {
        $query = http_build_query($params);
        return $this->make_request('products' . ($query ? "?$query" : ''));
    }

    public function create_order($order_data) {
        return $this->make_request('orders', 'POST', $order_data);
    }

    public function fetch_product_image($product_id, $format = 'MEDIUM') {
        return $this->make_request("products/$product_id/image?format=$format");
    }
}
