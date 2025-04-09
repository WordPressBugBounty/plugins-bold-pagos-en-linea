<?php
namespace BoldPagosEnLinea\Components;

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('get_plugins')) {
    require_once ABSPATH . 'wp-admin/includes/plugin.php';
}


use BoldPagosEnLinea\BoldCommon;

class BoldShortcode
{
    public function __construct()
    {
        add_shortcode("bold-button", [$this, "renderShortcodeButton"]);
    }

    public function renderShortcodeButton($attrs = []): string
    {
        $attrs = array_change_key_case((array)$attrs, CASE_LOWER);

        // Obtener las claves API dependiendo del modo de prueba
        $test_mode = BoldCommon::getOptionKey('test');
        if (isset($attrs["apikey"]) && isset($attrs["secretkey"])) {
            $apiKey = $attrs["apikey"];
            $secretKey = $attrs["secretkey"];
        } elseif ($test_mode === "yes") {
            $apiKey = BoldCommon::getOptionKey('test_api_key');
            $secretKey = BoldCommon::getOptionKey('test_secret_key');
        } elseif ($test_mode === "no") {
            $apiKey = BoldCommon::getOptionKey('prod_api_key');
            $secretKey = BoldCommon::getOptionKey('prod_secret_key');
        } else {
            return '<h6>' . esc_html__('Por favor verifica la configuración.', 'bold-pagos-en-linea') . '</h6>';
        }

        $orderReference = "WP-SC-" . sprintf('%.0f', microtime(true) * 1e9);
        $amount = isset($attrs["amount"]) ? esc_attr($attrs["amount"]) : '0';
        $currency = isset($attrs["currency"]) ? esc_attr($attrs["currency"]) : "COP";
        $signature = esc_attr(hash("sha256", "{$orderReference}{$amount}{$currency}{$secretKey}"));
        $redirectionUrl = isset($attrs["redirectionurl"]) ? esc_attr($attrs["redirectionurl"]) : '';
        $description = isset($attrs["description"]) ? esc_attr($attrs["description"]) : '';
        $style_parts = isset($attrs["color"]) ? explode('-', esc_attr($attrs["color"])) : ['dark', 'L'];
        $color_button = $style_parts[0];
        $size_button = isset($style_parts[1]) ? esc_attr($style_parts[1]) : 'L';
        $woocommerce_bold_version = "wordpress-shortcode-3.2.0";

        return wp_kses(BoldCommon::getButtonScript(
            esc_attr($apiKey),
            esc_attr($amount),
            esc_attr($currency),
            esc_attr($orderReference),
            esc_attr($signature),
            esc_attr($description),
            esc_attr($redirectionUrl),
            esc_attr($color_button),
            esc_attr($woocommerce_bold_version),
            esc_attr($size_button)
        ), BoldCommon::getTagsButtonScriptEnabled());
    }
}