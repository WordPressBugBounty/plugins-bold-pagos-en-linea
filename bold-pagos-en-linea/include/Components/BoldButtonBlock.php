<?php
namespace BoldPagosEnLinea\Components;

if (!defined('ABSPATH')) {
    exit;
}

use BoldPagosEnLinea\BoldCommon;

class BoldButtonBlock
{
    public function __construct()
    {
        add_action('init', [$this, 'registerBoldBlock']);
    }
    

    public function registerBoldBlock()
    {
        register_block_type('bold/button-block', [
            'editor_script' => 'bold-assets-js',
            'style'         => 'bold-assets-styles',
            'render_callback' => [$this, 'renderBoldBlock'],
            'attributes'    => [
                'amount' => [
                    'type'    => 'string',
                    'default' => '0',
                    'help'    => __('Si quieres que tu cliente decida cuánto quiere pagar el monto deberá ser cero', 'bold-pagos-en-linea'),
                    'required'=> true,
                ],
                'currency' => [
                    'type'    => 'string',
                    'default' => 'COP',
                    'help'    => __('Si el monto es cero, se cobrará en COP', 'bold-pagos-en-linea'),
                ],
                'description' => [
                    'type'    => 'string',
                    'default' => '',
                    'help'    => __('Opcional', 'bold-pagos-en-linea'),
                ],
                'redirectionUrl' => [
                    'type'    => 'string',
                    'default' => '',
                    'help'    => __('Opcional', 'bold-pagos-en-linea'),
                ],
                'color' => [
                    'type'    => 'string',
                    'default' => 'dark',
                ],
                'size' => [
                    'type'    => 'string',
                    'default' => 'L',
                ],
            ],
        ]);
    }

    public function renderBoldBlock($attrs)
    {
        $test_mode = BoldCommon::getOptionKey('test');
        if ($test_mode === "yes") {
            $apiKey = BoldCommon::getOptionKey('test_api_key');
            $secretKey = BoldCommon::getOptionKey('test_secret_key');
        } elseif ($test_mode === "no") {
            $apiKey = BoldCommon::getOptionKey('prod_api_key');
            $secretKey = BoldCommon::getOptionKey('prod_secret_key');
        } else {
            return '<h6>' . esc_html__('Por favor verifica la configuración', 'bold-pagos-en-linea') . '</h6>';
        }

        $orderReference = "WP-BB-" . sprintf('%.0f', microtime(true) * 1e9);
        $amount = esc_attr($attrs["amount"]);
        $currency = esc_attr($attrs["currency"]);
        $signature = esc_attr(hash("sha256", "{$orderReference}{$amount}{$currency}{$secretKey}"));
        $redirectionUrl = $attrs["redirectionUrl"] ? esc_attr($attrs["redirectionUrl"]) : '';
        $description = $attrs["description"] ? esc_attr($attrs["description"]) : '';
        $bold_color_button = esc_attr($attrs["color"]);
        $bold_size_button = esc_attr($attrs["size"]);
        $woocommerce_bold_version = "wordpress-block-3.2.1";

        return BoldCommon::getButtonScript(
            $apiKey,
            $amount,
            $currency,
            $orderReference,
            $signature,
            $description,
            $redirectionUrl,
            $bold_color_button,
            $woocommerce_bold_version,
            $bold_size_button
        );
    }
}
