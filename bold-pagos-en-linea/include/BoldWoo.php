<?php

namespace BoldPagosEnLinea;

if (!defined('ABSPATH')) {
    exit;
}

use Automattic\WooCommerce\Utilities\FeaturesUtil;
use Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry;
use BoldPagosEnLinea\BoldGatewayBlocks;
use BoldPagosEnLinea\BoldPaymentGatewayWoo;

class BoldWoo {

	// Inicia la funcionalidad del plugin
	public function init() {
		if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
			return;
		}
		// Crear hooks de WooCommerce
		$this->create_woocommerce_hooks();
	}

	// Declarar compatibilidad con las tablas de pedidos personalizadas de WooCommerce
	public function declare_compatibilities() {
		if ( class_exists( FeaturesUtil::class ) ) {
			$path_plugin = plugin_dir_path( plugin_dir_path( __FILE__ ) ) . 'bold-co.php';
			FeaturesUtil::declare_compatibility( 'custom_order_tables', $path_plugin, true);
			FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', $path_plugin, true);
			FeaturesUtil::declare_compatibility( 'high_performance_order_storage', $path_plugin, true);
		}
	}

	// Cargar scripts de JS para la pasarela de pagos
	public function load_scripts_js() {	
		wp_register_script( 'woocommerce_bold_gateway_js', plugin_dir_url( __FILE__ ) . '../assets/js/admin-index.js', [ 'jquery', 'bold-assets-js' ], '3.2.1', true );
		wp_enqueue_script( 'woocommerce_bold_gateway_js' );
	}

	// Cargar estilos CSS para la pasarela de pagos
	public function load_scripts_css() {
		wp_enqueue_style( 'woocommerce_bold_gateway_css', plugin_dir_url( __FILE__ ) . '../assets/css/bold_woocommerce_styles.css', false, '3.2.1', 'all' );
	}

	// Crear todos los hooks relacionados con WooCommerce
	public function create_woocommerce_hooks() {
		// Cargar scripts y estilos
		add_action( 'admin_enqueue_scripts', [ $this, 'load_scripts_js' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'load_scripts_css' ] );

		// Añadir métodos de pago
		add_action( 'before_woocommerce_init', [ $this, 'declare_compatibilities' ] );
		add_action( 'woocommerce_payment_gateways', [ $this, 'add_payment_method' ] );
		add_action( 'woocommerce_blocks_loaded', [ $this, 'register_order_approval_payment_method_type' ] );
	}

	// Agregar el método de pago a WooCommerce
	public function add_payment_method( $methods ) {
		$methods[] = '\BoldPagosEnLinea\BoldPaymentGatewayWoo';
		return $methods;
	}

	// Registrar un tipo de método de pago personalizado para bloques de WooCommerce
	public function register_order_approval_payment_method_type() {
		add_action(
			'woocommerce_blocks_payment_method_type_registration',
			function ( PaymentMethodRegistry $payment_method_registry ) {
				if(!$payment_method_registry->is_registered('bold_co')){
					// Registrar una instancia de BoldGatewayBlocks
					$payment_method_registry->register( new BoldGatewayBlocks );
				}
			}
		);
	}
}
