<?php
namespace BoldPagosEnLinea;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use BoldPagosEnLinea\BoldTinyHtmlMinifier;
use BoldPagosEnLinea\BoldConstants;

class BoldCommon {
    // Key for obfuscation
    private static $obfuscationKey = "BoldPaymentButton";

    // Valid character set for obfuscation
    private static $validCharacters = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_.~=&";

    // Custom delimiter for parameters
    private static $customDelimiter = "<bold>";

    // Obtener el key del campo
    public static function getFieldKey( string $key ): string {
        return 'woocommerce_' . 'bold_co_' . $key;
    }

    // Obtener el key de la opción
    public static function getOptionKey( string $key, string $default = "" ): string {
        if ( is_multisite() ) {
            return empty( get_site_option( self::getFieldKey( $key ) ) ) ? $default : get_site_option( self::getFieldKey( $key ) );
        }else{
            return empty( get_option( self::getFieldKey( $key ) ) ) ? $default : get_option( self::getFieldKey( $key ) );
        }
    }

    // Pasar HTML a una sola línea
    private static function tinyHtmlMinifier( string $html, array $options = [] ): string {
        $minifier = new BoldTinyHtmlMinifier( $options );
        return $minifier->minify( $html );
    }

    // Registrar eventos en un archivo de registro
    public static function logEvent( string $message ): void {
        $current_time = current_time( 'mysql' );
        $log_message  = "[$current_time] $message\n";

        // Verificar si WooCommerce está habilitado y usar WC_Logger
        if ( class_exists( 'WC_Logger' ) ) {
            $logger = new \WC_Logger();
            $logger->add( 'plugin-bold', $log_message );
        }
    }

    // Cargar la descripción personalizada del método de pago
    public static function uploadFileHtml( string $template_name ): string {
        $html = file_get_contents( $template_name, true );
        return self::tinyHtmlMinifier( $html, [
            'collapse_whitespace' => true,
            'disable_comments'    => true,
        ]);
    }

    private static function getListTemplatesAllowed(): array {
        // Ruta base del directorio de plantillas
        $templates_base_dir = realpath(WP_PLUGIN_DIR . "/" . self::getPluginPath() . "/templates");
    
        // Verificar que el directorio base es válido
        if (!$templates_base_dir || !is_dir($templates_base_dir)) {
            return [];
        }
    
        try {
            $directory_iterator = new \RecursiveDirectoryIterator($templates_base_dir, \FilesystemIterator::SKIP_DOTS);
            $iterator = new \RecursiveIteratorIterator($directory_iterator);
        } catch (\Exception $e) {
            return [];
        }
    
        // Obtener todos los archivos .php en el directorio y subdirectorios
        $allowed_templates = [];
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                // Guardar la ruta relativa al directorio de plantillas
                $allowed_templates[] = ltrim(str_replace('\\', '/', substr($file->getPathname(), strlen($templates_base_dir) + 1)), '/');
            }
        }
    
        return $allowed_templates;
    }
    
    // Cargar archivos PHP
    public static function loadTemplatePhp( string $template_name, array $params = [] ): string {
        // Obtener lista de plantillas permitidas
        $allowed_templates = self::getListTemplatesAllowed();
    
        // Validar que el archivo solicitado esté en la lista permitida
        if (!in_array($template_name, $allowed_templates, true)) {
            return '';
        }
    
        // Construir ruta completa
        $file_path = realpath(WP_PLUGIN_DIR . "/" . self::getPluginPath() . "/templates/" . $template_name);
    
        // Verificar que el archivo existe
        if (!$file_path || !is_file($file_path)) {
            return '';
        }
    
        ob_start();
        include $file_path;
        $content = ob_get_clean();
    
        return self::tinyHtmlMinifier($content, [
            'collapse_whitespace' => true,
            'disable_comments'    => true,
        ]);
    }    

    // Obtener ID de la orden en checkout
    public static function getOrderIdCheckout(): ?string {
        if ( isset( $_SERVER['QUERY_STRING'] ) ) {
            $unslash_args = sanitize_text_field( wp_unslash( $_SERVER['QUERY_STRING'] ) );
            wp_parse_str( $unslash_args, $qs );
            $id_reference = 'bold-order-id';

            if ( ! array_key_exists( $id_reference, $qs ) ) {
                return null;
            }

            return sanitize_text_field( $qs[ $id_reference ] );
        }
        return null;
    }

    // Obtener estado de la transacción en checkout
    public static function getTxStatusCheckout(): ?string {
        if ( isset( $_SERVER['QUERY_STRING'] ) ) {
            $unslash_args = sanitize_text_field( wp_unslash( $_SERVER['QUERY_STRING'] ) );
            wp_parse_str( $unslash_args, $qs );

            $transaction_status = 'bold-tx-status';

            if ( ! array_key_exists( $transaction_status, $qs ) ) {
                return null;
            }

            return sanitize_text_field( $qs[ $transaction_status ] );
        }
        return null;
    }

    // Obtener la ruta del plugin
    public static function getPluginPath(): string {
        return basename( dirname( plugin_dir_path( __FILE__ ) ) );
    }

    // Obtener la ruta del archivo principal del plugin
    private static function getPathRunFile(): string {
        return WP_PLUGIN_DIR . '/' . self::getPluginPath() . '/bold-co.php';
    }

    // Obtener la versión del plugin
    public static function getPluginVersion(): string {
        if ( ! function_exists( 'get_plugin_data' ) ) {
            require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        }

        $plugin_path_main_file = self::getPathRunFile();
        $plugin_data           = get_plugin_data( $plugin_path_main_file );

        return $plugin_data['Version'];
    }

    // Obtener la versión remota del plugin
    public static function getPluginVersionRemote(): string {
        $version_url    = BoldConstants::URL_CHECKOUT . '/plugins/woocommerce/version.txt';
        try {
            $remote_version = wp_remote_get( $version_url );
    
            if ( is_wp_error( $remote_version ) ) {
                return '0.0.0';
            }
    
            $response_code = wp_remote_retrieve_response_code( $remote_version );
            if ( $response_code == 200 ) {
                return trim( wp_remote_retrieve_body( $remote_version ) );
            }
    
            return '0.0.0';
        } catch (\Throwable $th) {
            return '0.0.0';
        }
    }

    // Obtener los webhooks desde el servidor remoto
    public static function getWebhooksRemote( string $api_key ): array {
        try {
            $webhooks_url = 'https://merchants-cde.api.bold.co/merchants/myself/configurations/webhook';
            $response = wp_remote_get( $webhooks_url, [
                'headers' => ['Authorization' => 'x-api-key ' . $api_key]
            ]);
    
            if ( ( !is_wp_error( $response ) ) && ( 200 === wp_remote_retrieve_response_code( $response ) ) ) {
                $responseBody = json_decode( $response['body'], true );
                if ( json_last_error() === JSON_ERROR_NONE ) {
                    return $responseBody;
                } else {
                    return [];
                }
            } else {
                $responseBody = ( is_array( $response ) ) ? json_decode( $response['body'], true ) : null;
                if ( json_last_error() === JSON_ERROR_NONE && is_array( $responseBody ) && $responseBody['hint'] === 'INVALID_TOKEN' ) {
                    throw new \InvalidArgumentException( esc_html__( 'Tus llaves de identidad y secreta son inválidas, revisa la información.', 'bold-pagos-en-linea' ) );
                } else {
                    $webhookUrl = add_query_arg( 'wc-api', 'bold_co', trailingslashit( get_home_url() ) );
                    return [ [ 'url' => $webhookUrl ] ];
                }
            }
        } catch (\InvalidArgumentException $e) {
            throw new \InvalidArgumentException( esc_html( $e->getMessage() ) );
        } catch (\Throwable $th) {
            $webhookUrl = add_query_arg( 'wc-api', 'bold_co', trailingslashit( get_home_url() ) );
            return [ [ 'url' => $webhookUrl ] ];
        }
    }

    public static function isSavedParams( $array, $function, $condition ) {
        $size = 0;
        foreach ( $array as $value ) {
            if ( call_user_func( $function, $value ) === $condition ) {
                $size ++;
            }
        }

        return $size === count( $array );
    }

    /**
     * Retrieves the store's logo URL in WooCommerce or the theme's custom logo.
     *
     * The function first attempts to get the logo configured in WooCommerce. If none is found,
     * it then looks for the logo set in the theme via the `custom_logo` option.
     * Additionally, it validates that the image is in JPG, PNG, or WEBP format; if not,
     * it returns an empty string.
     *
     * @return string The URL of the logo in JPG, PNG, or WEBP format. Returns an empty string if
     *                no valid logo is found in either location or if the file type is unsupported.
     */
    public static function getLogoStore() {
        $logo_personalized = self::getOptionKey('image_checkout_url');
        if(!empty($logo_personalized)){
            return self::getValidatedImage($logo_personalized);
        }
        
        $logo_id = get_option('woocommerce_store_logo');

        if ($logo_id) {
            $logo_url = wp_get_attachment_url($logo_id);
        } else {
            $logo_id = get_theme_mod('custom_logo');
            $logo_url = $logo_id ? wp_get_attachment_image_url($logo_id, 'medium') : '';
        }

        return self::getValidatedImage($logo_url);
    }

    public static function getValidatedImage($image)
    {
        if ($image && preg_match('/\.(jpg|jpeg|png|webp)$/i', $image)) {

            $parsed_url = wp_parse_url($image);

            $encoded_path = isset($parsed_url['path']) ? self::encodeAccentsInPath($parsed_url['path']) : '';
            $safe_url = $parsed_url['scheme'] . '://' . $parsed_url['host'] . $encoded_path;

            return $safe_url;
        }else{
            return '';
        }
    }

    private static function encodeAccentsInPath($path)
    {
        return preg_replace_callback('/[^\x20-\x7E]/u', function ($matches) {
            return rawurlencode($matches[0]);
        }, $path);
    }
    
    /**
     * Generates the full obfuscated URL with parameters.
     *
     * @param array $params Key-value pair of parameters.
     * @return string The obfuscated URL.
     */
    public static function generateObfuscatedUrl(array $params): string
    {
        $encodedParams = self::encodeParamsWithDelimiter($params);
        return BoldConstants::URL_CHECKOUT . "/btn?" . urlencode($encodedParams);
    }

    /**
     * Encodes parameters into a string, applies a delimiter, and obfuscates the result.
     *
     * @param array $params Key-value pair of parameters.
     * @return string Obfuscated parameters as a single string.
     */
    private static function encodeParamsWithDelimiter(array $params): string
    {
        $paramString = self::convertParamsToDelimitedString($params);
        return self::obfuscateString($paramString);
    }

    /**
     * Converts an array of parameters into a delimited string.
     *
     * @param array $params Key-value pair of parameters.
     * @return string Parameters as a string with a custom delimiter.
     */
    private static function convertParamsToDelimitedString(array $params): string
    {
        $pairs = array_map(function ($key, $value) {
            return self::toKebabCase($key) . "=" . $value;
        }, array_keys($params), $params);

        return implode(self::$customDelimiter, $pairs);
    }

    /**
     * Converts a string to kebab-case format.
     *
     * @param string $input The input string.
     * @return string The kebab-case formatted string.
     */
    private static function toKebabCase(string $input): string
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', $input));
    }

    /**
     * Obfuscates a string using the obfuscation key and valid character set.
     *
     * @param string $input The input string to obfuscate.
     * @return string The obfuscated string.
     */
    private static function obfuscateString(string $input): string
    {
        $obfuscated = '';
        $keyLength = strlen(self::$obfuscationKey);
        $validCharLength = strlen(self::$validCharacters);

        for ($i = 0, $len = strlen($input); $i < $len; $i++) {
            $charCode = ord(self::$obfuscationKey[$i % $keyLength]);
            $obfuscated .= self::shiftCharacter($input[$i], $charCode, $validCharLength);
        }

        return $obfuscated;
    }

    /**
     * Shifts a character based on its index in the valid character set.
     *
     * @param string $char The character to shift.
     * @param int $offset The offset value for the shift.
     * @param int $setLength The length of the valid character set.
     * @return string The shifted character.
     */
    private static function shiftCharacter(string $char, int $offset, int $setLength): string
    {
        $index = strpos(self::$validCharacters, $char);
        if ($index === false) {
            return $char; // Return the original character if not in the valid set.
        }

        $shiftedIndex = ($index + $offset + $setLength) % $setLength;
        return self::$validCharacters[$shiftedIndex];
    }

    /**
     * Retrieves the enabled tags for the Bold payment button script.
     *
     * @return array An array of enabled tags for the Bold payment button script.
     */
    public static function getTagsButtonScriptEnabled() : Array {
        return [
            'script' => [
                'integrity' => [],
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
                'data-render-mode' => [],
                'data-image-url' => [],
            ]
        ];
    }

    /**
     * Generates the HTML script for embedding the Bold payment button.
     *
     * @param string $apiKey The API key. Required.
     * @param float $amount The transaction amount. Optional. Default is 0.
     * @param string $currency The transaction currency. Optional. Default is 'COP'.
     * @param string $orderReference The order reference. Optional. Default is an empty string.
     * @param string $signature The transaction signature. Optional. Default is an empty string.
     * @param string|null $description The order description. Optional. Default is null.
     * @param string|null $redirectionUrl The URL for redirection after payment. Optional. Default is null.
     * @param string $bold_color_button The color of the button. Optional. Default is 'dark'.
     * @param string $woocommerce_bold_version The Bold integration version. Optional. Default is 'wordpress-3.2.0'.
     * @param string $size The button size. Optional. Default is 'L'.
     * @return string The HTML script for the payment button.
     */
    public static function getButtonScript(
        $apiKey,
        $amount = 0,
        $currency = 'COP',
        $orderReference = '',
        $signature = '',
        $description = null,
        $redirectionUrl = null,
        $bold_color_button = 'dark',
        $woocommerce_bold_version = 'wordpress-3.2.0',
        $size = 'L'
        ) : string
    {
        $tags_enabled = self::getTagsButtonScriptEnabled();
        $redirectionUrl = $redirectionUrl ? "data-redirection-url='" . esc_attr($redirectionUrl) . "'" : '';
        $description = $description ? "data-description='" . esc_attr($description) . "'" : '';
        $originUrl = self::getOptionKey('origin_url') !== '' ? "data-origin-url='" . esc_attr(self::getOptionKey('origin_url')) . "'" : '';
        $integrity_script = base64_encode(hash('sha384', $orderReference, true));
        $image_url = self::getLogoStore();
        $image_url_formated = !empty($image_url) ? "data-image-url='" . esc_attr($image_url) . "'" : '';
        
        return wp_kses("
            <script integrity='sha384-$integrity_script'
                data-bold-button='$bold_color_button-$size'
                data-order-id='$orderReference'
                data-amount='$amount'
                data-currency='$currency'
                data-api-key='$apiKey'
                data-integrity-signature='$signature'
                $redirectionUrl
                $description
                $originUrl
                $image_url_formated
                data-integration-type='$woocommerce_bold_version'
                data-render-mode='embedded'
            >/**$orderReference**/
            </script>",
            $tags_enabled);
    }
}
