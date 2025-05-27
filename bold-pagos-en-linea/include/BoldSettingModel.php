<?php
namespace BoldPagosEnLinea;

if (!defined('ABSPATH')) {
    exit;
}

use BoldPagosEnLinea\BoldCommon;
use BoldPagosEnLinea\BoldConstants;

class BoldSettingModel {
	private string $enabled;
	private string $prefix;
	private string $test;
	private string $logo_is_light;
	private string $prod_api_key;
	private string $prod_secret_key;
	private string $test_api_key;
	private string $test_secret_key;
	private string $origin_url;
	private string $image_checkout_url;

	public function setPrefix( $prefix ): void {
		$test_prefix    = 'test';
		$default_prefix = empty( BoldCommon::getOptionKey( 'prefix' ) ) ? "Bold" : BoldCommon::getOptionKey( 'prefix' );;
		$orders_pending = $this->get_gateway()->bold_get_pending_orders();

		if ( $orders_pending && $prefix != BoldCommon::getOptionKey( 'prefix' ) ) {
			$this->prefix = $default_prefix;
			$message_error = __('Tienes órdenes pendientes de pago o en estado fallido en este momento, no se puede editar el prefijo hasta que se actualicen.', 'bold-pagos-en-linea');
			throw new \InvalidArgumentException( esc_html($message_error) );
		}

		if ( ! $prefix ) {
			$this->prefix = $default_prefix;
			$message_error = __('Debes agregar un prefijo. Por defecto tomara un identificador.', 'bold-pagos-en-linea');
			throw new \InvalidArgumentException( esc_html($message_error) );

		}

		if ( preg_match( '/[^a-z0-9_\-]/i', $prefix ) ) {
			$this->prefix = $default_prefix;
			$message_error = __('Solo se aceptan valores alfanuméricos, "_" y "-"', 'bold-pagos-en-linea');
			throw new \InvalidArgumentException( esc_html($message_error) );
		}

		if ( substr( $prefix, 0, strlen( $test_prefix ) ) === $test_prefix ) {
			$this->prefix = $default_prefix;
			/* translators: %1$s the custom prefix set by the admin for their orders processed with Bold */
			$message_error_translate = __('El prefijo no puede iniciar con la palabra "%1$s"', 'bold-pagos-en-linea');
			$message_error = sprintf(
				$message_error_translate,
				esc_attr( $test_prefix, 'bold-pagos-en-linea' )
			);
			throw new \InvalidArgumentException( esc_html($message_error) );
		}

		$this->prefix = $prefix;
	}

	public function setTest( $test ): void {
		$this->test = $test;
	}

	public function setLogoIsLight( $logo_is_light ): void {
		$this->logo_is_light = $logo_is_light;
	}

	public function setProdApiKey( $prod_api_key ): void {
		$this->prod_api_key = $prod_api_key;
	}

	public function setProdSecretKey( $prod_secret_key ): void {
		$this->prod_secret_key = $prod_secret_key;
	}

	public function setTestApiKey( $test_api_key ): void {
		$this->test_api_key = $test_api_key;
	}

	public function setTestSecretKey( $test_secret_key ): void {
		$this->test_secret_key = $test_secret_key;
	}

	public function setOriginUrl( $origin_url ): void {
		if(strlen(trim($origin_url)) > 0){
			$origin_url_cleaned = sanitize_url(trim($origin_url), ['https']);
			if ( !filter_var($origin_url_cleaned, FILTER_VALIDATE_URL) ) {
				$this->origin_url ='';
				$message_error = __('La URL de retorno por abandono es inválida, debe iniciar con el protocolo https:// y a ser posible del dominio de tu negocio online.', 'bold-pagos-en-linea');
				throw new \InvalidArgumentException( esc_html($message_error) );
			}
		}else{
			$origin_url_cleaned = '';
		}
		$this->origin_url = $origin_url_cleaned;
	}

	public function setImageCheckoutUrl( $image_checkout_url ): void {
		if(strlen(trim($image_checkout_url)) > 0){
			$image_checkout_url_cleaned = sanitize_url(trim($image_checkout_url), ['https']);
			if ( !filter_var($image_checkout_url_cleaned, FILTER_VALIDATE_URL) ) {
				$this->image_checkout_url ='';
				$message_error = __('La URL de de la imagen es inválida, debe iniciar con el protocolo https:// y a ser posible del dominio de tu negocio online.', 'bold-pagos-en-linea');
				throw new \InvalidArgumentException( esc_html($message_error) );
			}
		}else{
			$image_checkout_url_cleaned = '';
		}
		$this->image_checkout_url = $image_checkout_url_cleaned;
	}

	public function setEnabled( $enabled ): void {
		$this->enabled = $enabled;
	}

	public function getPrefix(): string {
		return $this->prefix;
	}

	public function getTest(): string {
		return $this->test;
	}

	public function getLogoIsLight(): string {
		return $this->logo_is_light;
	}

	public function getProdApiKey(): string {
		return $this->prod_api_key;
	}

	public function getProdSecretKey(): string {
		return $this->prod_secret_key;
	}

	public function getTestApiKey(): string {
		return $this->test_api_key;
	}

	public function getTestSecretKey(): string {
		return $this->test_secret_key;
	}

	public function getOriginUrl(): string {
		return $this->origin_url;
	}

	public function getImageCheckoutUrl(): string {
		return $this->image_checkout_url;
	}

	public function getEnabled(): string {
		if ( class_exists( 'Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType' ) ) {
			return $this->enabled;
		}

		return "yes";
	}

	function get_gateway() {
		$gateways = WC()->payment_gateways->payment_gateways();

		return $gateways["bold_co"];
	}

	private function toArray( $completeInitialization ): array {
		if ( ! $completeInitialization ) {
			return [
				'enabled' => $this->enabled,
			];
		}

		if ( class_exists( 'Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType' ) ) {
			return [
				'enabled'         => $this->enabled,
				'prefix'          => $this->prefix,
				'logo_is_light'   => $this->logo_is_light,
				'test'            => $this->test,
				'prod_api_key'    => $this->prod_api_key,
				'prod_secret_key' => $this->prod_secret_key,
				'test_api_key'    => $this->test_api_key,
				'test_secret_key' => $this->test_secret_key,
				'origin_url' 	  => $this->origin_url,
				'image_checkout_url' => $this->image_checkout_url,
			];
		}

		return [
			'test'            => $this->test,
			'prod_api_key'    => $this->prod_api_key,
			'prod_secret_key' => $this->prod_secret_key,
			'test_api_key'    => $this->test_api_key,
			'test_secret_key' => $this->test_secret_key,
			'origin_url' 	  => $this->origin_url,
			'image_checkout_url' => $this->image_checkout_url,
		];
	}

	public function mapPostDataToSettingModel( array $postData ): BoldSettingModel {
		$settings_options_bold = BoldConstants::COLUMNS_KEYS;
		foreach ( $postData as $key => $value ) {
			// Sanitizamos la clave y el valor
			$sanitized_key = sanitize_key( $key );
			$sanitized_value = sanitize_text_field( $value );
	
			// Validamos la clave contra una lista permitida
			if ( in_array( $sanitized_key, $settings_options_bold, true ) ) {
				$method = 'set' . str_replace( ' ', '', ucwords( str_replace( '_', ' ', $sanitized_key ) ) );
				if ( method_exists( $this, $method ) ) {
					$this->$method( $sanitized_value );
				}
			}
		}
	
		return $this;
	}	


	public function saveSettingModelToOptions( $saveSettingModelToOptions = true ): void {
		$data = $this->toArray( $saveSettingModelToOptions );
		foreach ( $data as $key => $value ) {
			if ( is_array( $value ) ) {
				$value = implode( ',', $value );
			}
            if ( is_multisite() ) {
				update_site_option( BoldCommon::getFieldKey( $key ), sanitize_text_field($value) );
            }else{
				update_option( BoldCommon::getFieldKey( $key ), sanitize_text_field($value) );
            }
		}
	}

	function fillFirstTimeSettings(): void {
		$identityKey            = BoldCommon::getOptionKey( 'prod_api_key', '' );
		$gateway                = $this->get_gateway();
		$this->enabled          = $gateway->get_option( 'enabled' );
		$completeInitialization = false;
		if ( empty( $identityKey ) ) {
			$completeInitialization = true;
			$this->prefix           = $gateway->get_option( 'prefix' );
			$this->test             = $gateway->get_option( 'test' );
			$this->logo_is_light    = $gateway->get_option( 'logo_is_light' );
			$this->prod_api_key     = $gateway->get_option( 'prod_api_key' );
			$this->prod_secret_key  = $gateway->get_option( 'prod_secret_key' );
			$this->test_api_key     = $gateway->get_option( 'test_api_key' );
			$this->test_secret_key  = $gateway->get_option( 'test_secret_key' );
			$this->origin_url  		= $gateway->get_option( 'origin_url' );
			$this->image_checkout_url = $gateway->get_option( 'image_checkout_url' );
		}

		$this->saveSettingModelToOptions( $completeInitialization );

	}

	public function verifyWebhookRemote( array $postData ): void {
		if(isset($postData['prod_api_key'])){
			$prod_api_key = sanitize_text_field($postData['prod_api_key']);
			$webhook_url = add_query_arg( 'wc-api', 'bold_co', trailingslashit( get_home_url() ) );
			$is_configured = BoldCommon::getVerifyWebhookRemote($prod_api_key, $webhook_url);
			if(!$is_configured){
				/* translators: %1$s the webhook API url that automatically updates orders processed with Bold */
				$message_error_translate = __('Tu webhook aun no esta configurado, inicia sesión en Bold y configura el webhook %1$s', 'bold-pagos-en-linea');
				$message_error = sprintf(
					$message_error_translate,
					$webhook_url
				);
				throw new \InvalidArgumentException( esc_html($message_error) );
			}
		}
	}

}