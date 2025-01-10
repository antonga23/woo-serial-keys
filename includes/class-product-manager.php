<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Product Manager Class
 *
 * Handles all product-related operations between WooCommerce and CodesWholesale
 *
 * @package WooCommerce_CodesWholesale
 * @since   1.1.0 
 */


class WC_CodesWholesale_Product_Manager {
    private $api_handler;
    private $logger;

    public function __construct($api_handler, $logger) {
        $this->api_handler = $api_handler;
        $this->logger = $logger;
    }

    
    public function create_or_update_woocommerce_products($items) {
        foreach ($items as $item) {
            $this->create_or_update_product($item);
        }
    }

    private function create_or_update_product($item) {
        // Check if product exists by SKU
        $product_id = wc_get_product_id_by_sku($item['productId']);
        $product = $product_id ? wc_get_product($product_id) : null;

        if (!$product) {
            // Create new product
            $product = new WC_Product_Simple();
            $product->set_sku($item['productId']);
        }

        // Update product data
        $product->set_name($item['name']);
        // Handle pricing
        if (!empty($item['prices']) && isset($item['prices'][0]['value'])) {
            $original_price = (float)$item['prices'][0]['value'];
            // Sale price = original_price * 1.10, then rounded up
            $sale_price = ceil($original_price * 1.10);
            // Regular price = sale_price * 1.23, then rounded up
            $regular_price = ceil($sale_price * 1.23);
            
            $product->set_regular_price($regular_price);
            $product->set_sale_price($sale_price);
        }
        
        $product->set_description($item['description'] ?? '');
        
        // Set product meta
        $product->update_meta_data('_codeswholesale_product_id', $item['productId']);
        
        if (isset($item['platform'])) {
            $product->update_meta_data('_codeswholesale_platform', $item['platform']);
        }

        // Save product
        $product_id = $product->save();

        // Handle product image if available
        if ($product_id) {
            $this->handle_product_image($product_id, $item['productId']);
        }

        return $product_id;
    }

    private function handle_product_image($product_id, $cw_product_id) {
        // Get image from API
        $image_response = $this->api_handler->fetch_product_image($cw_product_id);
        
        if (!$image_response['success'] || empty($image_response['data'])) {
            return false;
        }

        // Attach image to product
        return $this->attach_image_from_data(
            $image_response['data'],
            $product_id,
            sanitize_title($cw_product_id) . '.jpg'
        );
    }

    private function attach_image_from_data($image_data, $product_id, $filename) {
        $upload_dir = wp_upload_dir();
        $unique_filename = wp_unique_filename($upload_dir['path'], $filename);
        $filepath = $upload_dir['path'] . '/' . $unique_filename;

        // Save image file
        if (!file_put_contents($filepath, $image_data)) {
            return false;
        }

        // Prepare attachment data
        $filetype = wp_check_filetype($filename, null);
        $attachment = array(
            'post_mime_type' => $filetype['type'],
            'post_title'     => preg_replace('/\.[^.]+$/', '', $filename),
            'post_content'   => '',
            'post_status'    => 'inherit'
        );

        // Insert attachment
        $attach_id = wp_insert_attachment($attachment, $filepath, $product_id);
        if (!$attach_id) {
            return false;
        }

        // Generate attachment metadata
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        $attach_data = wp_generate_attachment_metadata($attach_id, $filepath);
        wp_update_attachment_metadata($attach_id, $attach_data);

        // Set as product image
        set_post_thumbnail($product_id, $attach_id);

        return $attach_id;
    }
}
