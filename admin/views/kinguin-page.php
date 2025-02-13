<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1>Kinguin Integration Settings</h1>

    <form action="options.php" method="post">
        <?php settings_fields('wc_kinguin_options_group'); ?>
        <?php do_settings_sections('wc_kinguin_options_group'); ?>
        
        <table class="form-table">
            <tr>
                <th scope="row"><label for="wc_kinguin_api_key">API Key</label></th>
                <td>
                    <input type="text" id="wc_kinguin_api_key" name="wc_kinguin_api_key"
                        value="<?php echo esc_attr(get_option('wc_kinguin_api_key')); ?>" class="regular-text">
                    <p class="description">Provide Kinguin API key. You can obtain it from Your integration account.</p>
                    <p class="description">Dashboard <a href="https://www.kinguin.net/integration/" target="_blank">https://www.kinguin.net/integration/</a></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="wc_kinguin_environment">API Environment</label></th>
                <td>
                    <select id="wc_kinguin_environment" name="wc_kinguin_environment" class="regular-text">
                        <option value="sandbox" <?php selected(get_option('wc_kinguin_environment'), 'sandbox'); ?>>Sandbox</option>
                        <option value="production" <?php selected(get_option('wc_kinguin_environment'), 'production'); ?>>Production</option>
                    </select>
                    <p class="description">Select sandbox if you want to test Kinguin integration.</p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="wc_kinguin_webhook_url">Products Webhook</label></th>
                <td>
                    <input type="text" id="wc_kinguin_webhook_url" name="wc_kinguin_webhook_url"
                        value="<?php echo esc_attr(get_option('wc_kinguin_webhook_url')); ?>" class="regular-text" readonly>
                    <p class="description">Copy Webhook Url into Product Update webhook url field within your Kinguin store configuration.</p>
                </td>
            </tr>
        </table>
        <?php submit_button('Save Settings'); ?>
    </form>

    <div class="products-section" style="margin-top: 30px;">
        <h2>Kinguin Products</h2>
        <?php 
        // Add a placeholder for products list or implement display_kinguin_products() method
        if (method_exists($this, 'display_kinguin_products')) {
            $this->display_kinguin_products();
        }
        ?>
    </div>

    <div class="products-fetch-section" style="margin-top: 30px;">
        <h2>Fetch Kinguin Products</h2>
        <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
            <input type="hidden" name="action" value="fetch_kinguin_products">
            <?php wp_nonce_field('fetch_kinguin_products_nonce'); ?>
            
            <table class="form-table">
                <tr>
                    <th scope="row">Filters</th>
                    <td>
                        <p><label>Page: <input type="number" name="page" min="1" value="1"></label></p>
                        <p><label>Limit: <input type="number" name="limit" min="1" max="100" value="50"></label></p>
                        <p><label>Region: <input type="text" name="region" placeholder="e.g., europe"></label></p>
                        <p><label>Platform: <input type="text" name="platform" placeholder="e.g., steam"></label></p>
                    </td>
                </tr>
            </table>
            
            <?php submit_button('Fetch Products', 'secondary', 'fetch_products', false); ?>
        </form>
    </div>
</div>