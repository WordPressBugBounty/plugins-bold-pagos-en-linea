<?php
namespace BoldPagosEnLinea;

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

        $orderReference = "" . time();
        $amount = isset($attrs["amount"]) ? esc_attr($attrs["amount"]) : '0';
        $currency = isset($attrs["currency"]) ? esc_attr($attrs["currency"]) : "COP";
        $signature = esc_attr(hash("sha256", "{$orderReference}{$amount}{$currency}{$secretKey}"));
        $redirectionUrl = isset($attrs["redirectionurl"]) ? "data-redirection-url='" . esc_attr($attrs["redirectionurl"]) . "'" : '';
        $originUrl = BoldCommon::getOptionKey('origin_url') !== '' ? "data-origin-url='" . esc_attr(BoldCommon::getOptionKey('origin_url')) . "'" : '';
        $description = isset($attrs["description"]) ? "data-description='" . esc_attr($attrs["description"]) . "'" : '';
        $bold_color_button = isset($attrs["color"]) ? esc_attr($attrs["color"]) : 'dark';
        $woocommerce_bold_version = "wordpress-shortcode-3.1.0";

        $tags_enabled = [
            'script' => [
                'data-bold-button' => [],
                'data-order-id' => [],
                'data-amount' => [],
                'data-currency' => [],
                'data-api-key' => [],
                'data-integrity-signature' => [],
                'data-redirection-url' => [],
                'data-description' => [],
                'data-origin-url' => [],
                'data-integration-type' => [],
            ]
        ];

        return wp_kses("
            <script
                data-bold-button='$bold_color_button'
                data-order-id='$orderReference'
                data-amount='$amount'
                data-currency='$currency'
                data-api-key='$apiKey'
                data-integrity-signature='$signature'
                $redirectionUrl
                $description
                $originUrl
                data-integration-type='$woocommerce_bold_version'
            >
            </script>",
            $tags_enabled);
    }
}