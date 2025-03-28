<?php
if (!defined('ABSPATH')) {
    exit;
}

// Extract variables for use in template
extract($view_data);
?>

<div class="wrap">
    <h1><?php echo esc_html($page_title); ?></h1>

    <form action="<?php echo esc_url($admin_post_url); ?>" method="post">
        <input type="hidden" name="action" value="save_cw_settings">
        <?php wp_nonce_field('save_cw_settings_nonce'); ?>
        <table class="form-table">
            <tr>
                <th scope="row"><label for="wc_codeswholesale_client_id">Client ID</label></th>
                <td><input type="text" id="wc_codeswholesale_client_id" name="wc_codeswholesale_client_id"
                        value="<?php echo esc_attr($client_id); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th scope="row"><label for="wc_codeswholesale_client_secret">Client Secret</label></th>
                <td><input type="password" id="wc_codeswholesale_client_secret" name="wc_codeswholesale_client_secret"
                        value="<?php echo esc_attr($client_secret); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th scope="row"><label for="wc_codeswholesale_sandbox_mode">Sandbox Mode</label></th>
                <td>
                    <input type="checkbox" id="wc_codeswholesale_sandbox_mode" name="wc_codeswholesale_sandbox_mode"
                        value="1" <?php checked(1, $sandbox_mode, true); ?> />
                </td>
            </tr>
        </table>
        <?php submit_button('Save Settings'); ?>
    </form>

    <div class="token-section" style="margin-top: 30px;">
        <h2>Access Token Management</h2>

        <form action="<?php echo esc_url($admin_post_url); ?>" method="post">
            <input type="hidden" name="action" value="generate_cw_token">
            <?php wp_nonce_field('generate_cw_token_nonce'); ?>
            <?php submit_button('Generate New Token', 'primary', 'generate_token', false); ?>
        </form>

        <?php if ($token_info) : ?>
            <div class="token-info" style="margin-top: 20px; padding: 15px; background: #fff; border: 1px solid #ccd0d4; border-radius: 4px;">
                <h3>Current Token Information</h3>
                <p><strong>Access Token:</strong><br>
                    <code style="display: block; word-break: break-all; background: #f0f0f1; padding: 10px; margin: 5px 0;">
                        <?php echo esc_html($token_info['access_token']); ?>
                    </code>
                </p>
                <p><strong>Expires In:</strong> <?php echo esc_html($token_info['expires_in']); ?> seconds</p>
            </div>
        <?php endif; ?>
    </div>

    <div class="platforms-section" style="margin-top: 30px;">
        <h2>Platforms</h2>
        <?php $this->display_platforms(); ?>
    </div>

    <div class="platforms-fetch-section" style="margin-top: 30px;">
        <h2>Fetch Platforms</h2>
        <form action="<?php echo esc_url($admin_post_url); ?>" method="post">
            <input type="hidden" name="action" value="fetch_cw_platforms">
            <?php wp_nonce_field('fetch_cw_platforms_nonce'); ?>
            <?php submit_button('Fetch Platforms', 'secondary', 'fetch_platforms', false); ?>
        </form>
    </div>

    <div class="products-section" style="margin-top: 30px;">
        <h2>Products</h2>
        <?php $this->display_products(); ?>
    </div>

    <div class="products-fetch-section" style="margin-top: 30px;">
        <h2>Fetch Products</h2>
        <form action="<?php echo esc_url($admin_post_url); ?>" method="post">
            <input type="hidden" name="action" value="fetch_cw_products">
            <?php wp_nonce_field('fetch_cw_products_nonce'); ?>
            <p>inStockDaysAgo: <input type="number" name="inStockDaysAgo" min="0" max="60"></p>
            <p>language: <input type="text" name="language"></p>
            <p>region: <input type="text" name="region"></p>
            <p>platform: <input type="text" name="platform"></p>
            <p>productIds (comma-separated): <input type="text" name="productIds"></p>
            <?php submit_button('Fetch Products', 'secondary', 'fetch_products', false); ?>
        </form>
    </div>
</div>