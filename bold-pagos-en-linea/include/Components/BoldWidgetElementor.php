<?php
namespace BoldPagosEnLinea\Components;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( '\Elementor\Widget_Base' ) ) {
    return;
}

use BoldPagosEnLinea\BoldCommon;
use \Elementor\Plugin;

/**
 * Elementor Button Payment Bold Widget.
 *
 * Elementor widget that inserts an embbedable content into the page.
 *
 * @since 1.0.0
 */
class BoldWidgetElementor extends \Elementor\Widget_Base {

	/**
	 * Get widget name.
	 *
	 * Retrieve bold-elementor-button widget name.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return string Widget name.
	 */
	public function get_name(): string {
		return 'bold-elementor-button';
	}

	/**
	 * Get widget title.
	 *
	 * Retrieve widget title.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return string Widget title.
	 */
	public function get_title(): string {
		return esc_html__( 'Botón de pagos Bold', 'bold-pagos-en-linea' );
	}

	/**
	 * Get widget icon.
	 *
	 * Retrieve widget icon.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return string Widget icon.
	 */
	public function get_icon(): string {
		return 'boldicon';
	}

	/**
	 * Get widget categories.
	 *
	 * Retrieve the list of categories the widget belongs to.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return array Widget categories.
	 */
	public function get_categories(): array {
		return [ 'basic', 'general' ];
	}

	/**
	 * Get widget keywords.
	 *
	 * Retrieve the list of keywords the widget belongs to.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return array Widget keywords.
	 */
	public function get_keywords(): array {
		return [ 'bold', 'payment', 'button' ];
	}

	/**
	 * Get custom help URL.
	 *
	 * Retrieve a URL where the user can get more information about the widget.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return string Widget help URL.
	 */
	public function get_custom_help_url(): string {
		return 'https://developers.bold.co/pagos-en-linea/boton-de-pagos/plugins/wordpress/shortcode';
	}

	/**
	 * Whether the widget requires inner wrapper.
	 *
	 * Determine whether to optimize the DOM size.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @return bool Whether to optimize the DOM size.
	 */
	public function has_widget_inner_wrapper(): bool {
		return false;
	}

	/**
	 * Whether the element returns dynamic content.
	 *
	 * Determine whether to cache the element output or not.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @return bool Whether to cache the element output.
	 */
	protected function is_dynamic_content(): bool {
		return true;
	}

	/**
	 * Inside the widget class we can deffine the required CSS dependencies the following way
	 *
	 * @since 1.0.0
	 * @access protected
	 * @return array list of style dependencies paths or urls
	 */
	public function get_style_depends(): array {
		return [ 'bold-elementor-style' ];
	}

	/**
	 * Register list widget controls.
	 *
	 * Add input fields to allow the user to customize the widget settings.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function register_controls(): void{

		$this->start_controls_section(
			'content_section',
			[
				'label' => esc_html__( 'Configuración del botón', 'bold-pagos-en-linea' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'amount',
			[
				'classes' => 'bold-css-input',
				'label' => esc_html__( 'Monto', 'bold-pagos-en-linea' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'min' => 0,
				'step' => 50,
				'default' => 0,
				'description' => esc_html__('Si quieres que tu cliente decida cuánto quiere pagar el monto deberá ser cero', 'bold-pagos-en-linea'),
				'label_block' => true,
			]
		);

		$this->add_control(
			'currency',
			[
				'classes' => 'bold-css-input',
				'label' => esc_html__( 'Divisa', 'bold-pagos-en-linea' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'COP',
				'options' => [
					'COP' => 'COP',
					'USD' => 'USD',
				],
				'description' => esc_html__('Si el monto es cero, se cobrará en COP', 'bold-pagos-en-linea'),
				'label_block' => true,
			]
		);

		$this->add_control(
			'description',
			[
				'classes' => 'bold-css-input',
				'label' => esc_html__( 'Descripción de la venta', 'bold-pagos-en-linea' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'Opcional', 'bold-pagos-en-linea' ),
				'label_block' => true,
			]
		);

		$this->add_control(
			'redirectionUrl',
			[
				'classes' => 'bold-css-input',
				'label' => esc_html__( 'URL a la que redirigir al cliente tras finalizar una transacción', 'bold-pagos-en-linea' ),
				'type' => \Elementor\Controls_Manager::URL,
				'options' => false,
				'default' => [
					'url' => '',
				],
				'placeholder' => esc_html__( 'Opcional', 'bold-pagos-en-linea' ),
				'title' => __('Debe ser una URL válida que comience con https://', 'bold-pagos-en-linea'),
				'label_block' => true,
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'style_content_section',
			[
				'label' => esc_html__( 'Estilo del botón', 'bold-pagos-en-linea' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'color',
			[
				'classes' => 'bold-css-input',
				'label' => esc_html__( 'Color', 'bold-pagos-en-linea' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'dark',
				'options' => [
					'dark' => 'Dark',
					'light' => 'Light',
				],
				'label_block' => true,
			]
		);

		$this->add_control(
			'size',
			[
				'classes' => 'bold-css-input',
				'label' => esc_html__( 'Tamaño', 'bold-pagos-en-linea' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'L',
				'options' => [
					'L' => 'L (48px)',
					'M' => 'M (40px)',
					'S' => 'S (32px)',
				],
				'label_block' => true,
			]
		);

		$this->end_controls_section();

	}

	/**
	 * Render list widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function render(): void {
		$settings = $this->get_settings_for_display();
		if (Plugin::$instance->editor->is_edit_mode())
		{
			$BoldImageDark = plugins_url('../../assets/img/admin-panel/bold_co_button_example_dark.svg', __FILE__);
			$BoldImageLight = plugins_url('../../assets/img/admin-panel/bold_co_button_example_light.svg', __FILE__);
			echo '<div 
				class="bold-button-preview bold-button-'.esc_attr($settings["color"]).' size-'.esc_attr($settings["size"]).'" 
				style="background-image: url(\''.(($settings["color"]=='dark')?esc_url($BoldImageDark):esc_url($BoldImageLight)).'\')">
			</div>';
		} else {
			$test_mode = BoldCommon::getOptionKey('test');
			if ($test_mode === "yes") {
				$apiKey = BoldCommon::getOptionKey('test_api_key');
				$secretKey = BoldCommon::getOptionKey('test_secret_key');
			} elseif ($test_mode === "no") {
				$apiKey = BoldCommon::getOptionKey('prod_api_key');
				$secretKey = BoldCommon::getOptionKey('prod_secret_key');
			} else {
				echo '<h6>' . esc_html__('Por favor verifica la configuración.', 'bold-pagos-en-linea') . '</h6>';
			}
	
			$orderReference = "WP-WE-" . sprintf('%.0f', microtime(true) * 1e9);
			$amount = esc_attr($settings["amount"]);
			$currency = esc_attr($settings["currency"]);
			$signature = esc_attr(hash("sha256", "{$orderReference}{$amount}{$currency}{$secretKey}"));
			$redirectionUrl = $settings["redirectionUrl"] ? esc_attr($settings["redirectionUrl"]['url']) : '';
			$description = $settings["description"] ? esc_attr($settings["description"]) : '';
			$bold_color_button = esc_attr($settings["color"]);
			$bold_size_button = esc_attr($settings["size"]);
			$woocommerce_bold_version = "wordpress-elementor-3.1.4";
	
			$html_tag = [
				'ordered' => 'ol',
				'unordered' => 'ul',
				'other' => 'ul',
			];
			echo BoldCommon::getButtonScript(
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

	/**
	 * Render list widget output in the editor.
	 *
	 * Written as a Backbone JavaScript template and used to generate the live preview.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function content_template(): void
	{
		$BoldImageDark = plugins_url('../../assets/img/admin-panel/bold_co_button_example_dark.svg', __FILE__);
		$BoldImageLight = plugins_url('../../assets/img/admin-panel/bold_co_button_example_light.svg', __FILE__);
		?>
		<div 
			class="bold-button-preview bold-button-{{{settings.color}}} size-{{{settings.size}}}" 
			style="background-image: url('{{{((settings.color=='dark')?'<?php echo esc_url($BoldImageDark) ?>':'<?php echo esc_url($BoldImageLight) ?>')}}}')">
		</div>
		<?php
	}

}
