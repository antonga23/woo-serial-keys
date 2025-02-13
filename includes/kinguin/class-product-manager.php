<?php
if (!defined('ABSPATH')) {
    exit;
}

class WC_Kinguin_Product_Manager {
    private $api_handler;
    private $logger;

    public function __construct($api_handler, $logger) {
        $this->api_handler = $api_handler;
        $this->logger = $logger;
    }

    public function synchronize_products() {
        $response = $this->api_handler->fetch_products();

        if ($response['success']) {
            $products = $response['data'];
            foreach ($products as $product_data) {
                $this->create_or_update_product($product_data);
            }
        } else {
            $this->logger->error('Failed to fetch products from Kinguin: ' . $response['message']);
        }
    }

    private function create_or_update_product($product_data) {
        $product = wc_get_product(array('sku' => $product_data['sku']));

        if (!$product) {
            $product = new WC_Product_Simple();
            $product->set_sku($product_data['sku']);
        }

        $product->set_name($product_data['name']);
        $product->set_regular_price($product_data['price']);
        $product->set_description($product_data['description']);
        $product->save();
    }
}