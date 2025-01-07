<?php
namespace BoldPagosEnLinea;

if (!defined('ABSPATH')) {
    exit;
}

use BoldPagosEnLinea\BoldCommon;
if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
    return; // WooCommerce no está activo, se evita el fatal error.
}

class BoldPaymentGatewayWoo extends \WC_Payment_Gateway {
	// Constructor method
	private string $test_prefix;

	public function __construct() {
		$this->id                 = 'bold_co';
		$this->method_title       = __( 'Bold', 'bold-pagos-en-linea' );
		$this->order_button_text  = __( 'Paga en línea con Bold', 'bold-pagos-en-linea' );
		$this->method_description = __( 'Integración de la pasarela de pagos Bold con WooCommerce.', 'bold-pagos-en-linea' );
		$this->test_prefix        = "test";
		$this->has_fields         = true;

		$this->init_form_fields();
		$this->init_settings();

		$this->description = $this->bold_upload_checkout_description();
		$this->title       = __( 'Paga en línea con Bold ', 'bold-pagos-en-linea' );
		$this->bold_upload_icon();

		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array(
			$this,
			'process_admin_options'
		) );
		add_action( 'woocommerce_thankyou_order_received_text', array( $this, 'bold_received_order_text' ), 10, 2 );
		add_action( 'woocommerce_before_thankyou', array( $this, 'bold_received_order' ), 10, 1 );
		add_action( 'woocommerce_api_' . $this->id, array( $this, 'bold_handle_webhook' ) );
		add_action( 'woocommerce_settings_save_checkout', array( $this, 'bold_validate_gateway_settings' ) );
	}

	private function bold_register_scripts(){
		wp_register_script( 'woocommerce_bold_checkout_web_component_js', plugins_url( '/../assets/js/bold-checkout-ui.js', __FILE__ ), array(), '3.1.3', true );
		wp_enqueue_script( 'woocommerce_bold_checkout_web_component_js' );
	}

	public function bold_upload_checkout_description(): string {
		$this->bold_register_scripts();
		return BoldCommon::uploadFilePhp( 'templates/bold-basic-checkout.php', array( 'test_mode' => $this->get_option( 'test' ) ) );
	}

    /**
     * Render gateway checkout template
     *
     * @return void
     */
    public function payment_fields(): void
    {
		$this->bold_register_scripts();
        wc_get_template('/../templates/bold-basic-checkout.php', array( 'test_mode' => $this->get_option( 'test' ) ), null, __DIR__);
    }

	public function get_option_custom( $key, $default = "" ) {
		$custom_key = BoldCommon::getOptionKey( $key, $default );
		if ( $custom_key ) {
			$this->settings[ $key ] = $custom_key;
		} else {
			$this->settings[ $key ] = $this->get_option( $key );
		}

		if ( $default != "" && "" === $this->settings[ $key ] ) {
			$this->settings[ $key ] = $default;
		}

		return $this->settings[ $key ];
	}

	// Get the key from the JSon sent in post request
	public function bold_get_from_post( $key ): string {
		if ( empty( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field(wp_unslash($_POST['_wpnonce'])), 'woocommerce-settings' ) ) {
			return '';
		}
		if(isset( $_POST[ $this->get_field_key( $key ) ] )){
			$sanitized_data = sanitize_text_field( wp_unslash($_POST[ $this->get_field_key( $key ) ]) );
			$value = $sanitized_data;
		}else{
			$value = '';
		}
		return $value;
	}

	// Validate the setting when the payment gateway is enabled
	public function bold_validate_gateway_settings(): void {
		if ( empty( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field(wp_unslash($_POST['_wpnonce'])), 'woocommerce-settings' ) ) {
			return;
		}
		$section = (isset($_GET['section'])) ? wp_unslash( sanitize_text_field(wp_unslash($_GET['section'])) ) : null;
		if ( ! ( isset( $section ) && $section == $this->id ) ) {
			return;
		}

		if ( $this->bold_get_from_post( 'enabled' ) != "1" ) {
			return;
		}
		$test_mode = empty( $this->bold_get_from_post( 'test' ) ) ? $this->get_option_custom( 'test' ) : $this->bold_get_from_post( 'test' );

		if ( $test_mode == "1" || $test_mode == "yes" ) {
			$api_key    = empty( $this->bold_get_from_post( 'test_api_key' ) ) ? $this->get_option_custom( 'test_api_key' ) : $this->bold_get_from_post( 'test_api_key' );
			$secret_key = empty( $this->bold_get_from_post( 'test_secret_key' ) ) ? $this->get_option_custom( 'test_secret_key' ) : $this->bold_get_from_post( 'test_secret_key' );
			$message    = "pruebas";
		} else {
			$api_key    = empty( $this->bold_get_from_post( 'prod_api_key' ) ) ? $this->get_option_custom( 'prod_api_key' ) : $this->bold_get_from_post( 'prod_api_key' );
			$secret_key = empty( $this->bold_get_from_post( 'prod_secret_key' ) ) ? $this->get_option_custom( 'prod_secret_key' ) : $this->bold_get_from_post( 'prod_secret_key' );
			$message    = "produccion";
		}
		$not_bold_available = empty( $api_key ) || empty( $secret_key );

		if ( $not_bold_available ) {
			/* translators: %1$s addon message if is sanbox */
			$message_error_translate = __('Recuerda configurar tus llaves de integración en modo %1$s antes de habilitar el plugin.', 'bold-pagos-en-linea');
			$message_error = sprintf(
				$message_error_translate,
				$message
			);
			\WC_Admin_Settings::add_error( $message_error );
			$_POST[ $this->get_field_key( 'enabled' ) ] = null;
		}

		if ( ! in_array( get_woocommerce_currency(), $this->bold_get_supported_currency() ) ) {
			/* translators: %1$s currencies allowed for use payment method */
			$message_error_translate = __('Recuerda que en Bold por el momento solo aceptamos %1$s', 'bold-pagos-en-linea');
			$message_error = sprintf(
				$message_error_translate,
				implode( ',', $this->bold_get_supported_currency() )
			);
			\WC_Admin_Settings::add_error( $message_error );
			$_POST[ $this->get_field_key( 'enabled' ) ] = null;
		}
	}

	// Validate signature request from webhook
	public function bold_validate_signature( $signature, $request_body, $secret_key_merchant ): bool {
		$signature_calculate = hash_hmac( 'sha256', base64_encode( $request_body ), $secret_key_merchant );

		if ( hash_equals( $signature_calculate, $signature ) ) {
			return true;
		} else {
			return false;
		}
	}

	// Handle webhook request
	public function bold_handle_webhook() {
		if ( isset($_SERVER["REQUEST_METHOD"]) && sanitize_text_field(wp_unslash($_SERVER["REQUEST_METHOD"])) == "GET" ) {
			$this->bold_get_async_order_status();
			if(isset($_SERVER["HTTP_REFERER"])){
				$sanitized_referer = sanitize_url(wp_unslash($_SERVER["HTTP_REFERER"]));
				wp_redirect( $sanitized_referer );
			}else{
				wp_redirect( home_url() );
			}
			exit;
		}
		$headers      = array_change_key_case( getallheaders() );
		$signature    = $headers['x-bold-signature'];
		$request_body = file_get_contents( 'php://input' );
		$response     = json_decode( $request_body );

		header( 'Content-Type: application/json' );
		if ( ! isset( $signature ) ) {
			wp_die(
				wp_json_encode( array( 'error' => 'The signature is missing' ) ),
				'The signature is missing',
				array( 'response' => 400, 'content_type' => 'application/json' )
			);
		}

		if ( ! property_exists( $response, 'data' ) ) {
			wp_die(
				wp_json_encode( array( 'error' => 'The data is missing' ) ),
				'The data is missing',
				array( 'response' => 401, 'content_type' => 'application/json' )
			);
		}

		$metadata = $response->data->metadata;
		if ( ! property_exists( $metadata, 'reference' ) ) {
			wp_die(
				new \WP_Error( $this->id, "The reference is missing" ),
				402,
				array( 'response' => 402, 'content_type' => 'application/json' )
			);
		}

		$parts    = explode( '~', $metadata->reference );
		$order_id = end( $parts );
		if ( $parts[0] == $this->test_prefix ) {
			$secret_key_merchant = $this->get_option_custom( 'test_secret_key' );
			$valid_prefix        = $parts[1] == $this->get_option_custom( 'prefix' );
		} else {
			$secret_key_merchant = $this->get_option_custom( 'prod_secret_key' );
			$valid_prefix        = $parts[0] == $this->get_option_custom( 'prefix' );
		}
		if ( ! $valid_prefix ) {
			wp_die(
				new \WP_Error( $this->id, "The reference is not from store " . get_bloginfo( 'name' ) . " WooCommerce" ),
				403,
				array( 'response' => 403, 'content_type' => 'application/json' )
			);
		}

		if ( ! $this->bold_validate_signature( $signature, $request_body, $secret_key_merchant ) ) {
			wp_die(
				new \WP_Error( $this->id, 'Bad signature' ),
				404,
				array( 'response' => 404, 'content_type' => 'application/json' )
			);
		}

		$get_response = [
			"link_id"			=> "",
			"total"				=> $response->data->amount->total,
			"subtotal"			=> $response->data->amount->total,
			"description"		=> "",
			"reference_id"		=> $metadata->reference,
			"payment_status"	=> $response->type,
			"transaction_id"	=> $response->data->payment_id,
			"payer_email"		=> "",
			"transaction_date"	=> $response->data->created_at,
			"payment_method"	=> $response->data->payment_method
		];
		if ( ! $get_response || array_key_exists( 'errors', $get_response ) ) {
			wp_die(
				new \WP_Error( $this->id, "The reference don't found in voucher service" ),
				405,
				array( 'response' => 405, 'content_type' => 'application/json' )
			);
		}

		$order = wc_get_order( $order_id );
		$this->bold_update_status_payment( $order, $get_response );

		http_response_code( 200 );
		echo wp_json_encode( array( 'success' => true ) );
		exit;
	}


	// Cargar la imagen de bold
	function bold_upload_icon(): void {
		if ( $this->get_option_custom( 'logo_is_light' ) === "yes" ) {
			$this->icon = apply_filters( 'woocommerce_bold_co_icon', plugins_url( '/../assets/img/bold_logo_light_icon.svg?light', __FILE__ ) );
		} else {
			$this->icon = apply_filters( 'woocommerce_bold_co_icon', plugins_url( '/../assets/img/bold_logo_dark_icon.svg', __FILE__ ) );
		}
	}

	// Monedas soportadas por Bold
	function bold_get_supported_currency(): array {
		return [ 'COP', 'USD' ];
	}

	// Mensaje en el checkout de WooCommerce cuando no está disponible el método de pago Bold
	function bold_not_available( $text ): string {
		/* translators: %1$s prefix message if is sanbox */
		$message_error_translate = __('%1$s En este momento no puedes pagar con Bold. Si quieres usar este método de pago, por favor comunícate con el comercio.', 'bold-pagos-en-linea');
		$message_error = sprintf(
			$message_error_translate,
			$text
		);
		return '<div class="bold_co_woocommerce_not_available">' . $message_error . '</div>';
	}

	// Captura el estado del de la orden, si no llega el valor de la orden se obtiene por url del checkout de Bold
	function bold_get_status_payment( $order_reference ) {
		$public_key = $this->get_option_custom( 'test' ) === 'yes' ? $this->get_option_custom( 'test_api_key' ) : $this->get_option_custom( 'prod_api_key' );
		$url_status = 'https://payments.api.bold.co/v2/payment-voucher/';
		$url_ltp    = $url_status . $order_reference;

		$i = 1;

		while ( $i <= 3 ) {
			try {
				$response = wp_remote_get( $url_ltp, array(
					'headers' => array(
						"Content-Type"  => "application/json",
						'Authorization' => 'x-api-key ' . $public_key
					)
				) );
	
				$body = wp_remote_retrieve_body( $response );
	
				$body_decode = json_decode( $body, true );
				if ( ! $body_decode or array_key_exists( 'message', $body_decode ) ) {
					sleep( $i * $i );
					$i ++;
					continue;
				}
	
				return $body_decode;
			} catch (\Throwable $th) {
				$i ++;
				continue;
			}
		}

		return null;
	}

	// Actualización del estado de la transacción
	function bold_update_status_payment( $order, $get_response ): string {
		$status_response = strtoupper( sanitize_text_field($get_response['payment_status']) );

		switch ( $status_response ) {
			//Transactions in process
			case 'PENDING':
			case 'PROCESSING':
				/* translators: %1$s payment status reported from Bold */
				$message_error_translate = __('La transacción en Bold se encuentra en estado: %1$s', 'bold-pagos-en-linea');
				$message_status = sprintf(
					$message_error_translate,
					$status_response
				);
				$order->add_order_note( $message_status );
				return "$status_response";
			//Final states
			case 'SALE_APPROVED':
			case 'APPROVED':
				$is_payed = $order->is_paid();
				if(!$is_payed){
					if(array_key_exists( 'payment_method', $get_response ) && isset($get_response['payment_method'])){
						$payment_method = sanitize_text_field( $get_response['payment_method'] );
						/* translators: %1$s payment method reported from Bold */
						$message_order_translate = __('El pago se realizó a través de %1$s', 'bold-pagos-en-linea');
						$message_order = sprintf(
							$message_order_translate,
							$payment_method
						);
						$order->add_order_note( $message_order );
					}
					if ( array_key_exists( 'transaction_id', $get_response ) ) {
						$transaction_id = sanitize_text_field($get_response['transaction_id']);
						/* translators: %1$s number of order. %2$s status of transaction. */
						$message_order_translate = __('El numero de transacción en Bold para esta orden  %1$s es: %2$s', 'bold-pagos-en-linea');
						$message_order = sprintf(
							$message_order_translate,
							$order->get_id(),
							$transaction_id
						);
						$order->add_order_note( $message_order );
						$order->set_transaction_id( $transaction_id );
						$order->payment_complete($transaction_id);
					} else {
						$order->payment_complete();
					}
				}

				return "processing";
			case 'VOID_APPROVED':
			case 'VOIDED':
				$message_order = __('Se realizó anulación de la transacción', 'bold-pagos-en-linea');
				$order->add_order_note( $message_order );
				$order->update_status( 'refunded' );
				return "refunded";
			case 'SALE_REJECTED':
			case 'REJECTED':
			case 'FAILED':
				$order->update_status( 'failed' );

				return "failed";
		}

		return "inconsistent";
	}

	// Captura todas las órdenes pendientes de Bold
	function bold_get_pending_orders(): array {
		// Get all orders pending
		$args = array(
			'status'         => [ 'pending', 'failed' ],
			'limit'          => - 1,
			'payment_method' => $this->id,
		);

		return wc_get_orders( $args );
	}

	// Válida las órdenes pendientes de pago y las actualiza
	public function bold_get_async_order_status(): bool {
		// Registrar la ejecución del evento
		BoldCommon::logEvent( 'Iniciando validación de estados: ' . gmdate( 'Y-m-d H:i:s' ) );

		$orders_pending = $this->bold_get_pending_orders();
		if ( ! $orders_pending ) {
			BoldCommon::logEvent( 'No hay órdenes pendientes' );

			return true;
		}
		try {
			foreach ( $orders_pending as $order ) {
				$is_test_order = get_post_meta( $order->get_id(), '_is_test_order', true );

				if ( $is_test_order ) {
					$order_reference = $this->test_prefix . '~' . $this->get_option_custom( 'prefix' ) . '~' . $order->get_id();
					BoldCommon::logEvent( 'Validando la orden de prueba: ' . $order_reference );
				} else {
					$order_reference = $this->get_option_custom( 'prefix' ) . '~' . $order->get_id();
					BoldCommon::logEvent( 'Validando la orden: ' . $order_reference );
				}

				$get_response = $this->bold_get_status_payment( $order_reference );
				if ( ! $get_response or array_key_exists( 'errors', $get_response ) ) {
					BoldCommon::logEvent( 'No se pudo obtener el estado de la orden: ' . $order_reference . " se validara la retro compatibilidad." );
					$order_reference = $this->get_option_custom( 'prefix' ) . $order->get_id();
					$get_response    = $this->bold_get_status_payment( $order_reference );
				}

				if ( ! $get_response or array_key_exists( 'errors', $get_response ) ) {
					BoldCommon::logEvent( 'No se pudo obtener el estado de la orden: ' . $order_reference );
					continue;
				}
				BoldCommon::logEvent( 'El estado de la orden ' . $order_reference . ' es: ' . $get_response['payment_status'] );
				$this->bold_update_status_payment( $order, $get_response );
			}

			return true;
		} catch ( Exception $e ) {
			return false;
		}
	}

	// Personalizar texto de salida cuando se recibe una orden
	function bold_received_order_text( $text, $order ): string {
		$test_message = '';
		if ( $this->get_option_custom( 'test' ) === 'yes' ) {
			$test_message = __('Esta orden es de prueba. ', 'bold-pagos-en-linea');
		}
		if ( ! $order ) {
			return '<div class="woocommerce-error">'.__('La orden no fue recibida. Por favor, póngase en contacto con el administrador para validar el pago.', 'bold-pagos-en-linea').'</div>';
		}

		$status = $order->get_status();

		if ( $status != 'pending' ) {
			/* translators: %1$s type of transaction test/prod. %2$s custom message to note in order. */
			$message_order_translate = __('%1$s Tu orden tiene el estado "%2$s"', 'bold-pagos-en-linea');
			$message_order = sprintf(
				$message_order_translate,
				esc_attr( $text, 'bold-pagos-en-linea' ),
				esc_attr( $status, 'bold-pagos-en-linea' )
			);
			return '<div class="woocommerce-info">' . $message_order . '.</div>';
		}

		$get_response = BoldCommon::getTxStatusCheckout();

		if ( ! $get_response ) {
			return '<div class="woocommerce-info">'.__('Aún no confirmamos el estado de tu pago. Espera unos segundos y recarga la página nuevamente.', 'bold-pagos-en-linea').'</div>';
		}
		/* translators: %1$s type of transaction test/prod. %2$s custom message to note in order. %3$s the status of transaction received from Bold */
		$message_transaction_status_translate = __('<b>%1$s</b>%2$s El estado de tu transacción es: <b>"%3$s"</b>', 'bold-pagos-en-linea');
		$message_transaction_status = sprintf(
			$message_transaction_status_translate,
			esc_html($test_message),
			esc_html( $text, 'bold-pagos-en-linea' ),
			esc_html( BoldConstants::getTransactionStatus($get_response), 'bold-pagos-en-linea' )
		);
		return $message_transaction_status;
	}

	// Recibe la orden desde el checkout de Bold
	function bold_received_order( $order_id ): void {
		WC()->cart->empty_cart();
		$order  = wc_get_order( $order_id );
		$status = $order->get_status();

		if ( $status != 'pending' && $status != 'failed' ) {
			return;
		}

		$order_reference = BoldCommon::getOrderIdCheckout();
		if ( ! $order_reference ) {
			return;
		}

		$get_response = $this->bold_get_status_payment( $order_reference );

		if ( ! $get_response || array_key_exists( 'errors', $get_response ) ) {
			return;
		}
		$this->bold_update_status_payment( $order, $get_response );
	}

	// Válida si el método de pago Bold se puede mostrar en el checkout de Woocommerce
	public function is_available(): bool {
		if ( $this->get_option_custom( 'test' ) === 'yes' ) {
			$bold_available = ! empty( $this->get_option_custom( 'test_api_key' ) ) &&
			                  ! empty( $this->get_option_custom( 'test_secret_key' ) ) &&
			                  in_array( get_woocommerce_currency(), $this->bold_get_supported_currency() );
		} else {
			$bold_available = ! empty( $this->get_option_custom( 'prod_api_key' ) ) &&
			                  ! empty( $this->get_option_custom( 'prod_secret_key' ) ) &&
			                  in_array( get_woocommerce_currency(), $this->bold_get_supported_currency() );
		}

		if ( $bold_available && parent::is_available() ) {
			return true;
		}

		add_action( 'woocommerce_no_available_payment_methods_message', array( $this, 'bold_not_available' ) );

		return false;
	}

	// Carga los datos de configuración para usar Bold como pasarela de pagos
	public function init_form_fields(): void {
		wp_enqueue_style( 'woocommerce_bold_admin_notifications_css', plugin_dir_url( __FILE__ ) . '../assets/libraries/awesome-notifications/dist/style.css', false, '3.1.3', 'all' );
		wp_enqueue_style( 'woocommerce_bold_gateway_form_css', plugins_url( '/../assets/css/bold_woocommerce_form_styles.css', __FILE__ ), false, '3.1.3', 'all' );
		$this->form_fields = array(
			'config_bold' => array(
				'title'       => '',
				'type'        => 'title',
				'description' => BoldCommon::uploadFilePhp( 'templates/config-fields/config-field.php' ),
			),
			'enabled'     => array(
				'title'       => '',
				'type'        => 'checkbox',
				'class'       => 'bold__config__field__woocommerce__input',
				'description' => BoldCommon::uploadFilePhp( 'templates/config-fields/payment-method-field.php', array(
					"enabled" => $this->get_option( 'enabled' ),
					'prefix'  => $this->get_option_custom( 'prefix' )
				) ),
				'default'     => 'no',
			),
		);
	}

	public function get_data_billing_order( $order ): array {
		$customer_data = array(
			"email"    => $order->get_billing_email(),
			"fullName" => $order->get_formatted_billing_full_name(),
			"phone"    => $order->get_billing_phone(),
		);
		$billing_address = array(
			"address" => $order->get_billing_address_1(),
			"zipCode" => $order->get_billing_postcode(),
			"city"    => $order->get_billing_city(),
			"state"   => $order->get_billing_state(),
			"country" => $order->get_billing_country(),
		);
		ksort($customer_data);
		ksort($billing_address);

		$data_billing = array(
			"customer_data"   => $customer_data,
			"billing_address" => $billing_address
		);

		ksort($data_billing);
		return $data_billing;
	}

	function prepare_data_redirection( $order_id ): array {
		$order = wc_get_order( $order_id );

		$currency           = $order->get_currency();

		if($currency === 'COP'){
			$amount_in_cents    = number_format(round($order->get_total(), 0), 0, '.', '');
		}else{
			$amount_in_cents    = number_format(round($order->get_total(), 2), 2, '.', '');
		}

		$auth_token         = $this->get_option_custom( 'test' ) === 'yes' ? esc_attr( $this->get_option_custom( 'test_api_key' ) ) : esc_attr( $this->get_option_custom( 'prod_api_key' ) );
		$order_reference    = $this->get_option_custom( 'test' ) === 'yes' ? $this->test_prefix . '~' . esc_attr( $this->get_option_custom( 'prefix' ) ) . "~" . $order_id : esc_attr( $this->get_option_custom( 'prefix' ) ) . "~" . $order_id;
		$secret_key         = $this->get_option_custom( 'test' ) === 'yes' ? $this->get_option_custom( 'test_secret_key' ) : $this->get_option_custom( 'prod_secret_key' );
		$signature          = esc_attr( hash( 'sha256', "{$order_reference}{$amount_in_cents}{$currency}{$secret_key}" ) );
		$data_billing_order = $this->get_data_billing_order( $order );
		$origin_url         = $this->get_option_custom( 'origin_url' ) !== '' ? esc_attr( $this->get_option_custom( 'origin_url' ) ) : $order->get_cancel_order_url_raw();
		/* translators: %s the custom short description for payments in checkout processed with Bold */
		$description		= sprintf(__('Pago de mi pedido #%s', 'bold-pagos-en-linea'), $order_id);

		$return_url = $this->get_return_url( $order );
		if ( substr( $return_url, 0, 4 ) != "http" ) {
			$return_url = wc_get_checkout_url() . $return_url;
		}

		$data_order_bold = array(
			'currency'         		=> $currency,
			'api-key'          		=> $auth_token,
			'amount'  		   		=> $amount_in_cents,
			'order-id'  	   		=> $order_reference,
			'description'	   		=> $description,
			'integrity-signature'	=> $signature,
			'redirection-url'  		=> $return_url,
			'origin-url'       		=> $origin_url,
			'integration-type' 		=> 'wordpress-woocommerce-3.1.3',
			'customer-data'    		=> wp_json_encode($data_billing_order['customer_data']) ,
			'billing-address'  		=> wp_json_encode($data_billing_order['billing_address']),
			'opening-time'	   		=> (int) (microtime(true) * 1000000),
		);

		ksort($data_order_bold);

		$stock_is_enabled = wc_string_to_bool( get_option( 'woocommerce_manage_stock', 'yes' ) );
		$held_duration = absint(get_option( 'woocommerce_hold_stock_minutes',0));
		if($stock_is_enabled && $held_duration>=5){
			$held_duration = get_option( 'woocommerce_hold_stock_minutes' );
			$current_nanoseconds = microtime(true) * 1e9;
			$held_duration_nanoseconds = $held_duration * 60 * 1e9;
			$expiration_date = $current_nanoseconds + $held_duration_nanoseconds;
			$data_order_bold['expiration-date'] = number_format($expiration_date, 0, '.', '');
		}

		$image_url = BoldCommon::getLogoStore();
		if(!empty($image_url)){
			$data_order_bold['image-url'] = esc_url_raw($image_url);
		}

		return $data_order_bold;
	}

	// Función interna para Woocommerce
	function process_payment( $order_id ) {
		try {
			if ( $this->get_option_custom( 'test' ) === 'yes' ) {
				wc_get_order( $order_id )->add_order_note( __( "Esta orden es de prueba", 'bold-pagos-en-linea' ) );
				update_post_meta( $order_id, '_is_test_order', true );
			}
	
			$params = $this->prepare_data_redirection( $order_id );

			$data_url_redirect = BoldCommon::generateObfuscatedUrl($params);

			if(empty($data_url_redirect)){
				throw new \Exception("Empty url redirect checkout");
			}else{
				return array(
					'result'   => 'success',
					'redirect' => $data_url_redirect
				);
			}
		} catch (\Throwable $th) {
			$message_error = __('No se logró redireccionar a la pasarela de pagos de Bold', 'bold-pagos-en-linea');

			wc_add_notice( $message_error, 'notice' );
			wc_get_order( $order_id )->update_status('failed', $message_error );

            BoldCommon::logEvent("Error: " . $th->getMessage() . " in file " . $th->getFile() . " line " . $th->getLine());

			return;
		}
	}
}
