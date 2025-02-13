<?php

if (!defined('ABSPATH')) {
    exit;
}

class WC_Digital_Keys_API_Factory {
    public static function create_api_handler($provider, $logger) {
        switch ($provider) {
            case 'codeswholesale':
                return new WC_CodesWholesale_API_Handler($logger);
            case 'kinguin':
                return new WC_Kinguin_API_Handler($logger);
            default:
                throw new Exception("Unknown API provider: $provider");
        }
    }
}