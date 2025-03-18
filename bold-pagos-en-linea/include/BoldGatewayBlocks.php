<?php
namespace BoldPagosEnLinea;

if (!defined('ABSPATH')) {
    exit;
}

use BoldPagosEnLinea\BoldCommon;
use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

final class BoldGatewayBlocks extends AbstractPaymentMethodType {

	private $gateway;
	protected $name = "bold_co";

	public function initialize(): void {
		if ( is_multisite() ) {
			$this->settings = get_site_option( "woocommerce_{$this->name}_settings", [] );
        }else{
			$this->settings = get_option( "woocommerce_{$this->name}_settings", [] );
        }

		if (empty($this->settings)) {
            BoldCommon::logEvent('La configuración del gateway está vacía.');
        }

		// initialize payment gateway
		$gateways      = WC()->payment_gateways->payment_gateways();
		$this->gateway = $gateways[ $this->name ];

		if (!$this->gateway) {
            BoldCommon::logEvent('Gateway no encontrado.');
        }
	}

	public function is_active() {
		return $this->gateway && $this->gateway->is_available();
	}

	public function get_payment_method_script_handles(): array {

		wp_register_script(
			"{$this->name}-blocks-integration",
			plugin_dir_url( __FILE__ ) . '../assets/js/bold_checkout.js',
			[
				'wc-blocks-registry',
				'wc-settings',
				'wp-element',
				'wp-html-entities',
				'wp-i18n',
			],
			'3.1.9',
			true
		);
		if ( function_exists( 'wp_set_script_translations' ) ) {
			wp_set_script_translations( "{$this->name}-blocks-integration" );

		}

		return [ "{$this->name}-blocks-integration" ];
	}

	public function get_payment_method_data(): array {
		return [
			'title'       => $this->gateway->title,
			'description' => $this->gateway->description,
			'icon'        => $this->gateway->icon,
		];
	}

}