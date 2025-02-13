<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1>General Settings</h1>

    <form action="options.php" method="post">
        <?php settings_fields('wc_digital_keys_general_options'); ?>
        <?php do_settings_sections('wc_digital_keys_general_options'); ?>
        
        <table class="form-table">
            <tr>
                <th scope="row"><label for="wc_digital_keys_default_provider">Default Provider</label></th>
                <td>
                    <select id="wc_digital_keys_default_provider" name="wc_digital_keys_general_settings[default_provider]">
                        <option value="codeswholesale" <?php selected(get_option('wc_digital_keys_general_settings')['default_provider'] ?? '', 'codeswholesale'); ?>>Codeswholesale</option>
                        <option value="kinguin" <?php selected(get_option('wc_digital_keys_general_settings')['default_provider'] ?? '', 'kinguin'); ?>>Kinguin</option>
                    </select>
                    <p class="description">Select the default provider for new products.</p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="wc_digital_keys_auto_import">Auto Import</label></th>
                <td>
                    <input type="checkbox" id="wc_digital_keys_auto_import" 
                           name="wc_digital_keys_general_settings[auto_import]" 
                           value="1" <?php checked(get_option('wc_digital_keys_general_settings')['auto_import'] ?? false, 1); ?>>
                    <p class="description">Automatically import new products from providers.</p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="wc_digital_keys_sync_interval">Sync Interval</label></th>
                <td>
                    <select id="wc_digital_keys_sync_interval" name="wc_digital_keys_general_settings[sync_interval]">
                        <option value="hourly" <?php selected(get_option('wc_digital_keys_general_settings')['sync_interval'] ?? '', 'hourly'); ?>>Hourly</option>
                        <option value="twicedaily" <?php selected(get_option('wc_digital_keys_general_settings')['sync_interval'] ?? '', 'twicedaily'); ?>>Twice Daily</option>
                        <option value="daily" <?php selected(get_option('wc_digital_keys_general_settings')['sync_interval'] ?? '', 'daily'); ?>>Daily</option>
                    </select>
                    <p class="description">How often to sync product data with providers.</p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="wc_digital_keys_price_markup">Default Price Markup (%)</label></th>
                <td>
                    <input type="number" id="wc_digital_keys_price_markup" 
                           name="wc_digital_keys_general_settings[price_markup]" 
                           value="<?php echo esc_attr(get_option('wc_digital_keys_general_settings')['price_markup'] ?? '20'); ?>"
                           min="0" max="1000" step="0.1">
                    <p class="description">Default percentage markup on provider prices.</p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="wc_digital_keys_stock_threshold">Low Stock Threshold</label></th>
                <td>
                    <input type="number" id="wc_digital_keys_stock_threshold" 
                           name="wc_digital_keys_general_settings[stock_threshold]" 
                           value="<?php echo esc_attr(get_option('wc_digital_keys_general_settings')['stock_threshold'] ?? '5'); ?>"
                           min="0" max="100">
                    <p class="description">Number of items that triggers low stock notification.</p>
                </td>
            </tr>
        </table>
        
        <?php submit_button('Save Settings'); ?>
    </form>

    <div class="status-section" style="margin-top: 30px;">
        <h2>Integration Status</h2>
        <table class="widefat" style="margin-top: 15px;">
            <thead>
                <tr>
                    <th>Provider</th>
                    <th>Status</th>
                    <th>Last Sync</th>
                    <th>Products</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Codeswholesale</td>
                    <td><?php echo $this->get_codeswholesale_status(); ?></td>
                    <td><?php echo get_option('wc_codeswholesale_last_sync') ? date('Y-m-d H:i:s', get_option('wc_codeswholesale_last_sync')) : 'Never'; ?></td>
                    <td><?php echo $this->get_codeswholesale_product_count(); ?></td>
                </tr>
                <tr>
                    <td>Kinguin</td>
                    <td><?php echo $this->get_kinguin_status(); ?></td>
                    <td><?php echo get_option('wc_kinguin_last_sync') ? date('Y-m-d H:i:s', get_option('wc_kinguin_last_sync')) : 'Never'; ?></td>
                    <td><?php echo $this->get_kinguin_product_count(); ?></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>