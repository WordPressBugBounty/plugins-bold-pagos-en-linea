<?php
namespace BoldPagosEnLinea;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use BoldPagosEnLinea\BoldTinyHtmlMinifier;

class BoldCommon {

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
        } else {
            // Si WooCommerce no está habilitado, usar error_log()
            error_log( $log_message, 3, WP_CONTENT_DIR . '/bold_button_event_log.txt' );
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

    // Cargar archivos PHP
    public static function uploadFilePhp( string $template_name, array $params = [] ): string {
        ob_start();
        include( WP_PLUGIN_DIR . "/" . self::getPluginPath() . "/" . $template_name );
        $content = ob_get_clean();
        return self::tinyHtmlMinifier( $content, [
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
        $version_url    = 'https://checkout.bold.co/plugins/woocommerce/version.txt';
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
}
