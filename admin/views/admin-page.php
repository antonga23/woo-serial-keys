<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <?php settings_errors('wc_codeswholesale_messages'); ?>

    <form method="post" action="<?php echo esc_url(admin_url('options.php')); ?>">
        <?php settings_fields('wc_codeswholesale_options_group'); ?>
        <?php do_settings_sections('wc_codeswholesale_options_group'); ?>
        
        <table class="form-table">
            <tr>
                <th scope="row">Client ID</th>
                <td>
                    <input type="text" name="wc_codeswholesale_client_id" 
                           value="<?php echo esc_attr(get_option('wc_codeswholesale_client_id')); ?>" 
                           class="regular-text">
                </td>
            </tr>
            <tr>
                <th scope="row">Client Secret</th>
                <td>
                    <input type="password" name="wc_codeswholesale_client_secret" 
                           value="<?php echo esc_attr(get_option('wc_codeswholesale_client_secret')); ?>" 
                           class="regular-text">
                </td>
            </tr>
            <tr>
                <th scope="row">Sandbox Mode</th>
                <td>
                    <input type="checkbox" name="wc_codeswholesale_sandbox_mode" 
                           value="1" <?php checked(1, get_option('wc_codeswholesale_sandbox_mode'), true); ?>>
                    <span class="description">Enable sandbox mode for testing</span>
                </td>
            </tr>
        </table>

        <?php submit_button('Save Settings'); ?>
    </form>

    <hr>

    <h2>API Connection Test</h2>
    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <?php wp_nonce_field('test_endpoint_nonce'); ?>
        <input type="hidden" name="action" value="test_endpoint">
        <?php submit_button('Test API Connection', 'secondary', 'test_endpoint', false); ?>
    </form>

    <hr>

    <h2>Fetch Platforms</h2>
    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <?php wp_nonce_field('fetch_cw_platforms_nonce'); ?>
        <input type="hidden" name="action" value="fetch_cw_platforms">
        <?php submit_button('Fetch Platforms', 'secondary', 'fetch_platforms', false); ?>
    </form>

    <hr>

    <h2>Fetch Products</h2>
    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <?php wp_nonce_field('fetch_cw_products_nonce'); ?>
        <input type="hidden" name="action" value="fetch_cw_products">
        <?php submit_button('Fetch Products', 'secondary', 'fetch_products', false); ?>
    </form>
</div>
