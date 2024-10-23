<?php

if (!defined('ABSPATH')) {
    exit;
}

use BoldPagosEnLinea\BoldCommon;

$pluginUrl = plugin_dir_url(__FILE__);
$test_mode = BoldCommon::getOptionKey('test');
$is_light = BoldCommon::getOptionKey('logo_is_light');
?>

<bold-checkout-element 
    plugin_url="<?php echo esc_url($pluginUrl) ?>" 
    test_mode="<?php echo esc_attr($test_mode) ?>" 
    is_light="<?php echo esc_attr($is_light) ?>">
</bold-checkout-element>
