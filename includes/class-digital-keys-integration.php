<?php
if (!defined('ABSPATH')) {
    exit;
}

class WC_Digital_Keys_Integration {
    private $codeswholesale_integration;
    private $kinguin_integration;

    public function __construct() {
        $this->codeswholesale_integration = new WC_CodesWholesale_Integration();
        $this->kinguin_integration = new WC_Kinguin_Integration();
    }

    public static function activate() {
        WC_CodesWholesale_Integration::activate();
        // WC_Kinguin_Integration::activate();
    }

    public static function deactivate() {
        WC_CodesWholesale_Integration::deactivate();
        // WC_Kinguin_Integration::deactivate();
    }
}