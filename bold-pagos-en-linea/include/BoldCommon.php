<?php
namespace BoldPagosEnLinea;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use BoldPagosEnLinea\BoldTinyHtmlMinifier;
use BoldPagosEnLinea\BoldConstants;

class BoldCommon {
    // Maximum number of characters supported by Bold for the reference
    const REFERENCE_MAX_LENGTH = 60;

    // Key for obfuscation
    private static $obfuscationKey = "BoldPaymentButton";

    // Valid character set for obfuscation
    private static $validCharacters = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_.~=&";

    // Custom delimiter for parameters
    private static $customDelimiter = "<bold>";

    /**
     * Builds the WooCommerce settings field key for a given option.
     *
     * @param string $key The option key.
     * @return string The full field key.
     */
    public static function getFieldKey( string $key ): string {
        return 'woocommerce_' . 'bold_co_' . $key;
    }

    /**
     * Retrieves the value of a plugin option, supporting multisite installs.
     *
     * @param string $key The option key.
     * @param string $default Optional. The default value if the option is empty. Default is an empty string.
     * @return string The option value, or the default if not set.
     */
    public static function getOptionKey( string $key, string $default = "" ): string {
        if ( is_multisite() ) {
            return empty( get_site_option( self::getFieldKey( $key ) ) ) ? $default : get_site_option( self::getFieldKey( $key ) );
        }else{
            return empty( get_option( self::getFieldKey( $key ) ) ) ? $default : get_option( self::getFieldKey( $key ) );
        }
    }

    /**
     * Minifies HTML into a single line.
     *
     * @param string $html The HTML content to minify.
     * @param array $options Optional. Minifier options. Default is an empty array.
     * @return string The minified HTML.
     */
    private static function tinyHtmlMinifier( string $html, array $options = [] ): string {
        $minifier = new BoldTinyHtmlMinifier( $options );
        return $minifier->minify( $html );
    }

    /**
     * Logs an event message to the plugin log file.
     *
     * @param string $message The message to log.
     * @return void
     */
    public static function logEvent( string $message ): void {
        $current_time = current_time( 'mysql' );
        $log_message  = "[$current_time] $message\n";

        // Log via WC_Logger only when WooCommerce is active
        if ( class_exists( 'WC_Logger' ) ) {
            $logger = new \WC_Logger();
            $logger->add( 'plugin-bold', $log_message );
        }
    }

    /**
     * Loads and minifies the custom payment method description template.
     *
     * @param string $template_name The path of the template file to load.
     * @return string The minified HTML content.
     */
    public static function uploadFileHtml( string $template_name ): string {
        $html = file_get_contents( $template_name, true );
        return self::tinyHtmlMinifier( $html, [
            'collapse_whitespace' => true,
            'disable_comments'    => true,
        ]);
    }

    /**
     * Lists the relative paths of all PHP template files allowed to be loaded.
     *
     * @return array The list of allowed template relative paths.
     */
    private static function getListTemplatesAllowed(): array {
        // Base directory of the templates
        $templates_base_dir = realpath(WP_PLUGIN_DIR . "/" . self::getPluginPath() . "/templates");

        // Bail out if the base directory is invalid
        if (!$templates_base_dir || !is_dir($templates_base_dir)) {
            return [];
        }

        try {
            $directory_iterator = new \RecursiveDirectoryIterator($templates_base_dir, \FilesystemIterator::SKIP_DOTS);
            $iterator = new \RecursiveIteratorIterator($directory_iterator);
        } catch (\Exception $e) {
            return [];
        }

        // Collect every .php file in the directory and its subdirectories
        $allowed_templates = [];
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                // Store the path relative to the templates directory
                $allowed_templates[] = ltrim(str_replace('\\', '/', substr($file->getPathname(), strlen($templates_base_dir) + 1)), '/');
            }
        }

        return $allowed_templates;
    }

    /**
     * Renders a whitelisted PHP template file and returns the minified HTML.
     *
     * @param string $template_name The relative path of the template to load.
     * @param array $params Optional. Variables to expose to the template. Default is an empty array.
     * @return string The rendered and minified HTML, or an empty string if the template isn't allowed.
     */
    public static function loadTemplatePhp( string $template_name, array $params = [] ): string {
        // Get the list of allowed templates
        $allowed_templates = self::getListTemplatesAllowed();

        // Bail out if the requested file isn't in the allowed list
        if (!in_array($template_name, $allowed_templates, true)) {
            return '';
        }

        // Build the full path
        $file_path = realpath(WP_PLUGIN_DIR . "/" . self::getPluginPath() . "/templates/" . $template_name);

        // Bail out if the file doesn't exist
        if (!$file_path || !is_file($file_path)) {
            return '';
        }

        ob_start();
        include $file_path; // nosemgrep
        $content = ob_get_clean();

        return self::tinyHtmlMinifier($content, [
            'collapse_whitespace' => true,
            'disable_comments'    => true,
        ]);
    }

    /**
     * Retrieves the order ID from the checkout query string.
     *
     * @return string|null The sanitized order ID, or null if not present.
     */
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

    /**
     * Retrieves the transaction status from the checkout query string.
     *
     * @return string|null The sanitized transaction status, or null if not present.
     */
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

    /**
     * Retrieves the plugin's directory name.
     *
     * @return string The plugin directory name.
     */
    public static function getPluginPath(): string {
        return basename( dirname( plugin_dir_path( __FILE__ ) ) );
    }

    /**
     * Retrieves the absolute path of the plugin's main file.
     *
     * @return string The absolute path to the plugin's main file.
     */
    private static function getPathRunFile(): string {
        return WP_PLUGIN_DIR . '/' . self::getPluginPath() . '/bold-co.php';
    }

    /**
     * Retrieves the currently installed plugin version.
     *
     * @return string The plugin version.
     */
    public static function getPluginVersion(): string {
        if ( ! function_exists( 'get_plugin_data' ) ) {
            require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        }

        $plugin_path_main_file = self::getPathRunFile();
        $plugin_data           = get_plugin_data( $plugin_path_main_file );

        return $plugin_data['Version'];
    }

    /**
     * Retrieves the latest plugin version published remotely.
     *
     * @return string The remote plugin version, or '0.0.0' on failure.
     */
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

    /**
     * Verifies with Bold's remote API that the webhook URL is registered.
     *
     * @param string $api_key The API key for authentication.
     * @param string $webhook_url The webhook URL to verify.
     * @return bool True if the webhook is registered, false otherwise.
     */
    public static function getVerifyWebhookRemote( string $api_key, string $webhook_url ): bool {
        try {
            $webhooks_url = BoldConstants::URL_API_ONLINE . '/ecommerce/v1/verify-webhook?url='.$webhook_url;
            $response = wp_remote_get( $webhooks_url, [
                'headers' => ['Authorization' => 'x-api-key ' . $api_key]
            ]);

            if ( ( !is_wp_error( $response ) ) && ( 200 === wp_remote_retrieve_response_code( $response ) ) ) {
                return true;
            } elseif ( !is_wp_error( $response ) && ( 404 === wp_remote_retrieve_response_code( $response ) )  ) {
                self::logEvent( 'Webhook no configurado: "' . $webhooks_url . '"' );
                return false;
            } else {
                $responseBody = ( is_array( $response ) ) ? json_decode( $response['body'], true ) : null;
                if ( json_last_error() === JSON_ERROR_NONE && is_array( $responseBody ) && $responseBody['hint'] === 'INVALID_TOKEN' ) {
                    throw new \InvalidArgumentException( esc_html__( 'Tus llaves de identidad y secreta son inválidas, revisa la información.', 'bold-pagos-en-linea' ) );
                } else {
                    self::logEvent( 'Error al validar el webhook: "' . ( is_array( $response ) ? json_encode($response ) : $response ) . '"' );
                    return false;
                }
            }
        } catch (\InvalidArgumentException $e) {
            throw new \InvalidArgumentException( esc_html( $e->getMessage() ) );
        } catch (\Throwable $th) {
            self::logEvent( 'Error al validar el webhook: ' . $th->getMessage() );
            return false;
        }
    }

    /**
     * Calls Bold API to create a payment link.
     *
     * @param string $api_key The API key for authentication.
     * @param array $payload The payment data payload.
     * @return string|null The redirect URL from Bold, or null on failure.
     */
    public static function createPaymentLink(string $api_key, array $payload): ?string {
        $url = BoldConstants::URL_API_ONLINE . '/v2/payment-btn';

        try {
            $response = wp_remote_post($url, [
                'headers' => [
                    'Content-Type'  => 'application/json',
                    'Authorization' => 'x-api-key ' . $api_key,
                ],
                'body'    => wp_json_encode($payload),
                'timeout' => 30,
            ]);

            if (is_wp_error($response)) {
                self::logEvent('Error al crear link de pago Bold: ' . $response->get_error_message());
                return null;
            }

            $response_code = wp_remote_retrieve_response_code($response);
            $body = wp_remote_retrieve_body($response);
            $body_decoded = json_decode($body, true);

            if ($response_code === 200 || $response_code === 201) {
                if (isset($body_decoded['payload']['url'])) {
                    return $body_decoded['payload']['url'];
                }
                if (isset($body_decoded['url'])) {
                    return $body_decoded['url'];
                }
                self::logEvent('Respuesta exitosa de Bold pero sin URL de redirección: ' . $body);
                return null;
            }

            $error_message = (is_array($body)) ? json_encode($body) : $body;

            self::logEvent('Error en API Bold (HTTP ' . $response_code . '): ' . $error_message);
            return null;

        } catch (\Throwable $th) {
            self::logEvent('Error al crear link de pago Bold: ' . $th->getMessage() . ' in file ' . $th->getFile() . ' line ' . $th->getLine());
            return null;
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
    public static function encodeParamsWithDelimiter(array $params): string
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
     * @param string $woocommerce_bold_version The Bold integration version. Optional. Default is 'wordpress-3.4.0'.
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
        $woocommerce_bold_version = 'wordpress-3.4.0',
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

    /**
     * Retrieves the server-side fingerprint information based on the user agent and device type.
     *
     * @return array An associative array containing device type, operating system, model, browser, and platform.
     */
    public static function getServerSideFingerprint(): array
    {
        $user_agent = self::getUserAgent();
        $os = self::detectOS($user_agent);
        $browser = self::detectBrowser($user_agent);

        return [
            "device_type" => wp_is_mobile() ? 'PHONE' : 'DESKTOP',
            "platform"    => "WEB",
            "os"          => $os !== 'Unknown' ? $os : null,
            "model"       => null,
            "browser"     => $browser !== 'Unknown' ? $browser : null,
            "ip"          => self::getClientIp(),
        ];
    }

    /**
     * Retrieves the user agent string using WordPress core functions.
     *
     * @return string The sanitized user agent string.
     */
    private static function getUserAgent(): string
    {
        if ( function_exists( 'wc_get_user_agent' ) ) {
            return wc_get_user_agent();
        }

        return isset( $_SERVER['HTTP_USER_AGENT'] )
            ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) )
            : '';
    }

    /**
     * Retrieves the client's IP address.
     *
     * Uses WC_Geolocation::get_ip_address() if available (handles proxy headers automatically),
     * otherwise falls back to REMOTE_ADDR.
     *
     * @return string The client IP address, or empty string if not available.
     */
    private static function getClientIp(): string
    {
        if ( class_exists( 'WC_Geolocation' ) ) {
            return \WC_Geolocation::get_ip_address();
        }

        $ip = isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : '';
        return filter_var($ip, FILTER_VALIDATE_IP) ?: '';
    }

    /**
     * Detects the operating system from the user agent string.
     *
     * @param string $user_agent The user agent string.
     * @return string The detected operating system name.
     */
    private static function detectOS( string $user_agent ): string
    {
        $os_patterns = [
            'Windows'  => '/windows nt/i',
            'Android'  => '/android/i',
            'iOS'      => '/iphone|ipad|ipod/i',
            'MacOS'    => '/macintosh|mac os x/i',
            'ChromeOS' => '/cros/i',
            'Linux'    => '/linux/i',
        ];

        foreach ( $os_patterns as $os_name => $pattern ) {
            if ( preg_match( $pattern, $user_agent ) ) {
                return $os_name;
            }
        }

        return 'Unknown';
    }

    /**
     * Detects the browser from the user agent string.
     * Order matters: more specific patterns must come before generic ones.
     *
     * @param string $user_agent The user agent string.
     * @return string The detected browser name.
     */
    private static function detectBrowser( string $user_agent ): string
    {
        $browser_patterns = [
            'Opera'           => '/opera|opr\//i',
            'Samsung Internet' => '/samsungbrowser/i',
            'Microsoft Edge'  => '/edg/i',
            'Google Chrome'   => '/chrome|chromium|crios/i',
            'Mozilla Firefox' => '/firefox|fxios/i',
            'Apple Safari'    => '/safari/i',
        ];

        foreach ( $browser_patterns as $browser_name => $pattern ) {
            if ( preg_match( $pattern, $user_agent ) ) {
                return $browser_name;
            }
        }

        return 'Unknown';
    }

    /**
     * Retrieves the site's domain without its extension (.com, .co, etc), stripped
     * of any non-alphanumeric characters.
     *
     * @return string The sanitized domain slug.
     */
    public static function getSiteDomainSlug(): string {
        $host = wp_parse_url( home_url(), PHP_URL_HOST );
        if ( empty( $host ) ) {
            $host = isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : 'site';
        }
        $host     = preg_replace( '/:\d+$/', '', $host );
        $segments = explode( '.', $host );
        if ( count( $segments ) > 1 ) {
            array_pop( $segments );
        }
        $domain = preg_replace( '/[^A-Za-z0-9]/', '', implode( '', $segments ) );

        return strtolower( $domain );
    }

    /**
     * Builds a unique order reference: {prefix}~{order_id}~{domain}{timestamp_ns}.
     *
     * The prefix and order ID are never truncated, since they identify the store
     * and the order. If the result would exceed REFERENCE_MAX_LENGTH, the domain
     * segment is shortened to make room, since it only helps prevent collisions.
     *
     * @param string $prefix The merchant's configured reference prefix.
     * @param mixed $order_id The WooCommerce order ID.
     * @param bool $is_test Whether the order is a test/sandbox order.
     * @param string $test_prefix The prefix used to mark test references.
     * @return string The generated order reference.
     */
    public static function buildOrderReference( string $prefix, $order_id, bool $is_test, string $test_prefix ): string {
        $fixed_part   = ( $is_test ? $test_prefix . '~' : '' ) . $prefix . '~' . $order_id . '~';
        $timestamp_ns = number_format( microtime( true ) * 1e9, 0, '.', '' );
        $max_domain   = self::REFERENCE_MAX_LENGTH - strlen( $fixed_part ) - strlen( $timestamp_ns );
        $domain       = substr( self::getSiteDomainSlug(), 0, max( 0, $max_domain ) );

        return $fixed_part . $domain . $timestamp_ns;
    }

    /**
     * Parses an order reference into its prefix, order ID, and test-mode flag.
     * Compatible with the legacy "Bold~order_id" format.
     *
     * @param string $reference The order reference to parse.
     * @param string $test_prefix The prefix used to mark test references.
     * @return array{is_test: bool, prefix: string, order_id: string} The parsed reference components.
     */
    public static function parseOrderReference( string $reference, string $test_prefix ): array {
        $parts   = explode( '~', $reference );
        $is_test = isset( $parts[0] ) && $parts[0] === $test_prefix;
        if ( $is_test ) {
            array_shift( $parts );
        }

        return [
            'is_test'  => $is_test,
            'prefix'   => $parts[0] ?? '',
            'order_id' => $parts[1] ?? '',
        ];
    }
}
