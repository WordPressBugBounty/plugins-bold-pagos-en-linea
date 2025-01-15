<?php
/*
 * Plugin Name: Bold pagos en linea 
 * Plugin URI: https://developers.bold.co/pagos-en-linea/boton-de-pagos/plugins/wordpress
 * Description: Recibe pagos en tu tienda de forma segura con los métodos de pago más usados y con la mejor experiencia para tus clientes.
 * Version: 3.1.4
 * Author: Bold
 * Author URI: http://www.bold.co/
 * Network: true
 * Text Domain: bold-pagos-en-linea
 * WC requires at least: 5.5.2
 * WC tested up to: 9.4.1
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
    wp_register_script('woocommerce_bold_payment_button_js', BoldConstants::URL_CHECKOUT.'/library/boldPaymentButton.js', [], '3.1.4', true);
    wp_enqueue_script('woocommerce_bold_payment_button_js');

    wp_register_script(
        'bold-assets-js',
        plugin_dir_url(__FILE__) . './build/index.js',
        array('wp-blocks', 'wp-editor', 'wp-components', 'wp-element'),
        filemtime(plugin_dir_path(__FILE__) . './build/index.js'),
        true
    );
    wp_enqueue_script( 'bold-assets-js' );

    wp_localize_script('bold-assets-js', 'boldBlockData', array(
        'iconUrl' => plugins_url('./assets/img/admin-panel/bold_co_button_light.png', __FILE__),
        'exampleButtonLight' => plugins_url('./assets/img/admin-panel/bold_co_button_example_light.svg', __FILE__),
        'exampleButtonDark' => plugins_url('./assets/img/admin-panel/bold_co_button_example_dark.svg', __FILE__),
    ));

    wp_register_style(
        'bold-assets-styles',
        plugin_dir_url(__FILE__) . './build/index.css',
        [],
        filemtime(plugin_dir_path(__FILE__) . './build/index.css')
    );
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

// Añade enlaces rápidos seccion metadata en la pantalla de plugins
function bold_add_5_star_review_link( $plugin_meta, $plugin_file )
{
    if ( strpos( $plugin_file, 'bold-co.php' ) !== false ) {
        $u    = get_current_user_id();
        $site = get_site_url();
        $url_rate = esc_url('https://wordpress.org/support/plugin/bold-pagos-en-linea/reviews/?filter=5&site=' . esc_attr( $site ) . '&u=' . esc_attr( $u ));

        $plugin_meta[] = '<a href="' . $url_rate . '" target="_blank" rel="noopener noreferrer" title="' . esc_attr__( 'Califica Bold pagos en linea en WordPress.org', 'bold-pagos-en-linea' ) . '" style="color: #ffb900">'
            . str_repeat( '<span class="dashicons dashicons-star-filled" style="font-size: 16px; width:16px; height: 16px"></span>', 5 )
            . '</a>';
    }
    return $plugin_meta;
}

// Inicializar el plugin
function bold_co_payment_gateway_woocommerce(): void {

    // Añadir enlaces rápidos a la pantalla de plugins
    add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'bold_co_plugin_action_generic_links');

    // Añadir enlaces rápidos en secion metadata a la pantalla de plugins
	add_filter( 'plugin_row_meta', 'bold_add_5_star_review_link', 10, 2 );

    // Añadir categoria personalizada para elementos de bloque
    add_filter('block_categories_all', 'bold_register_custom_category', 10, 2);
    
    // Iniciar BoldShortcode
    if (class_exists('BoldPagosEnLinea\Components\BoldShortcode')) {
        new \BoldPagosEnLinea\Components\BoldShortcode();
    }

    // Iniciar BoldButtonBlock
    if (class_exists('BoldPagosEnLinea\Components\BoldButtonBlock')) {
        new \BoldPagosEnLinea\Components\BoldButtonBlock();
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

// Custom category for elements of Bold
function bold_register_custom_category($categories) {
    return array_merge(
        $categories,
        [
            [
                'slug'  => 'bold-category',
                'title' => __('Bold pagos en línea', 'bold-pagos-en-linea'),
                'icon'  => 'boldicon',
            ],
        ]
    );
}

// Hook para cargar el plugin después de que todos los plugins hayan sido cargados
add_action('plugins_loaded', 'bold_co_payment_gateway_woocommerce', 0);
// Cargar el scripts y styles
add_action('init', 'bold_co_custom_header_code');
add_action('init', 'bold_load_textdomain' );
 
/**
 * Load plugin textdomain.
 */
function bold_load_textdomain()
{
    load_plugin_textdomain( 'bold-pagos-en-linea', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}

//register elementor widget
bold_add_styles_elementor_widget();
add_action( 'elementor/preview/enqueue_styles', function() {
    bold_add_styles_elementor_widget();
} );
add_action( 'elementor/editor/before_enqueue_scripts', function() {
    bold_add_styles_elementor_widget();
 } );
function bold_add_styles_elementor_widget() {
    wp_enqueue_style(
        'bold-elementor-style', 
        plugins_url( 'assets/css/bold-elementor-widget.css', __FILE__ ), 
        false, 
        filemtime(plugin_dir_path(__FILE__) . 'assets/css/bold-elementor-widget.css'), 
        'all'
    );
}
function bold_register_elementor_widget($widgets_manager ) {
    bold_add_styles_elementor_widget();
    // Iniciar BoldWidgetElementor
    if (class_exists('BoldPagosEnLinea\Components\BoldWidgetElementor') && class_exists('\Elementor\Widget_Base')) {
        $widgets_manager->register( new \BoldPagosEnLinea\Components\BoldWidgetElementor() );
    }
}
add_action( 'elementor/widgets/register', 'bold_register_elementor_widget' );

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
            BoldCommon::logEvent("Error: " . $th->getMessage() . " in file " . $th->getFile() . " line " . $th->getLine());
        }
    }
}
