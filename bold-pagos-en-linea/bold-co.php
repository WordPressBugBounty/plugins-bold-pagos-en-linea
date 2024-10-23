<?php
/*
 * Plugin Name: Bold pagos en linea 
 * Plugin URI: https://developers.bold.co/pagos-en-linea/boton-de-pagos/plugins/wordpress
 * Description: Integra la pasarela de pagos Bold en tu sitio web.
 * Version: 3.0.2
 * Author: Bold
 * Author URI: http://www.bold.co/
 * Network: true
 * Text Domain: bold-pagos-en-linea
 * WC requires at least: 5.5.2
 * WC tested up to: 8.1.0
 * Requires PHP: 7.4
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
*/

if (!defined('ABSPATH')) {
    exit;
}

//borrar plugin viejo
$path_old_plugin = plugin_dir_path(__FILE__) . '/../woocommerce-bold';
if(file_exists($path_old_plugin)){
    bol_co_deleteDirectoryRecursively($path_old_plugin);
}
function bol_co_deleteDirectoryRecursively($directory) {
    // Check if the directory is valid
    if (is_dir($directory)) {
        // Open the directory
        $items = scandir($directory);
        foreach ($items as $item) {
            // Ignore the current and parent directory indicators
            if ($item !== "." && $item !== "..") {
                $fullPath = $directory . DIRECTORY_SEPARATOR . $item;
                // If it's a directory, call the function recursively
                if (is_dir($fullPath)) {
                    bol_co_deleteDirectoryRecursively($fullPath);
                } else {
                    unlink($fullPath);
                }
            }
        }
        // Once empty, remove the directory
        rmdir($directory);
    }
}

// Autoload function for classes within the BoldPagosEnLinea namespace
$file_autoload = plugin_dir_path(__FILE__) . '/vendor/autoload.php';
if ( file_exists( $file_autoload ) && is_file($file_autoload) && is_readable($file_autoload)) {
    require_once $file_autoload;
}else{
	add_action('admin_notices', fn() => $file_autoload && include __DIR__ . '/templates/error-autoload.php');
	return false;
}

use BoldPagosEnLinea\BoldCommon;
use BoldPagosEnLinea\BoldConstants;

// Función para registrar y cargar el script de botón de pago
function bold_co_custom_header_code(): void {
    wp_register_script('woocommerce_bold_payment_button_js', 'https://checkout.bold.co/library/boldPaymentButton.js', [], '3.0.2', true);
    wp_enqueue_script('woocommerce_bold_payment_button_js');
}

// Añade enlaces rápidos de ajustes y documentación en la pantalla de plugins
function bold_co_plugin_action_generic_links($links): array {
    $plugin_links = array(
        '<a href="' . esc_url(admin_url('admin.php?page=bold-pagos-en-linea')) . '">' . esc_html__('Ajustes', 'bold-pagos-en-linea') . '</a>',
        '<a href="https://developers.bold.co/pagos-en-linea/boton-de-pagos/plugins/wordpress" target="_blank">' . esc_html__('Documentación', 'bold-pagos-en-linea') . '</a>',
        '<a href="mailto:soporte.online@bold.co">' . esc_html__('Soporte', 'bold-pagos-en-linea') . '</a>',
    );

    return array_merge($plugin_links, $links);
}

// Inicializar el plugin
function bold_co_payment_gateway_woocommerce(): void {
    // Cargar el script del botón de pago en el encabezado
    add_action('wp_head', 'bold_co_custom_header_code');

    // Añadir enlaces rápidos a la pantalla de plugins
    add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'bold_co_plugin_action_generic_links');
    
    // Iniciar BoldShortcode
    if (class_exists('BoldPagosEnLinea\BoldShortcode')) {
        new \BoldPagosEnLinea\BoldShortcode();
    }
    
    // Cargar el menú de administración
    if (class_exists('BoldPagosEnLinea\BoldMenuAdmin')) {
        $menu_admin = new \BoldPagosEnLinea\BoldMenuAdmin();
    }

    // Iniciar BoldWoo
    if (class_exists('BoldPagosEnLinea\BoldWoo')) {
        $bold_woo = new \BoldPagosEnLinea\BoldWoo();
        $bold_woo->init();
    }
}

// Hook para cargar el plugin después de que todos los plugins hayan sido cargados
add_action('plugins_loaded', 'bold_co_payment_gateway_woocommerce', 0);

register_uninstall_hook( __FILE__, 'bold_co_uninstall' );

function bold_co_uninstall(){

    $settings_options_bold = BoldConstants::COLUMNS_KEYS;
    
    foreach ($settings_options_bold as $option_name) {
        try {
            $option_key = BoldCommon::getFieldKey($option_name);
            if ( is_multisite() ) {
                delete_site_option($option_key);
            }else{
                delete_option($option_key);
            }
        } catch (\Throwable $th) {
            error_log($th->getMessage(), 3, WP_CONTENT_DIR . '/bold_button_event_log.txt' );
        }
    }
}
