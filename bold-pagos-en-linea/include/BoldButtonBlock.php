<?php
namespace BoldPagosEnLinea;

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
        // Registra los scripts y estilos del bloque.
        wp_register_script(
            'bold-gutenberg-block',
            plugin_dir_url(__FILE__) . '../build/index.js',
            array('wp-blocks', 'wp-editor', 'wp-components', 'wp-element'),
            filemtime(plugin_dir_path(__FILE__) . '../build/index.js'),
            true
        );

        wp_localize_script('bold-gutenberg-block', 'boldBlockData', array(
            'iconUrl' => plugins_url('../assets/img/admin-panel/bold_co_button_light.png', __FILE__),
            'exampleButtonLight' => plugins_url('../assets/img/admin-panel/bold_co_button_example_light.svg', __FILE__),
            'exampleButtonDark' => plugins_url('../assets/img/admin-panel/bold_co_button_example_dark.svg', __FILE__),
        ));

        wp_register_style(
            'bold-gutenberg-block-style',
            plugin_dir_url(__FILE__) . '../build/index.css',
            [],
            filemtime(plugin_dir_path(__FILE__) . '../build/index.css')
        );

        register_block_type('bold/button-block', [
            'editor_script' => 'bold-gutenberg-block',
            'style'         => 'bold-gutenberg-block-style',
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
            return '<h6>' . esc_html__('Por favor verifica la configuración.', 'bold-pagos-en-linea') . '</h6>';
        }

        $orderReference = "WP-BB-" . sprintf('%.0f', microtime(true) * 1e9);
        $amount = esc_attr($attrs["amount"]);
        $currency = esc_attr($attrs["currency"]);
        $signature = esc_attr(hash("sha256", "{$orderReference}{$amount}{$currency}{$secretKey}"));
        $redirectionUrl = $attrs["redirectionUrl"] ? esc_attr($attrs["redirectionUrl"]) : '';
        $description = $attrs["description"] ? esc_attr($attrs["description"]) : '';
        $bold_color_button = esc_attr($attrs["color"]);
        $bold_size_button = esc_attr($attrs["size"]);
        $woocommerce_bold_version = "wordpress-block-3.1.1";

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
