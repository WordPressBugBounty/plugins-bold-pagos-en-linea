<?php
namespace BoldPagosEnLinea;

if (!defined('ABSPATH')) {
    exit;
}

use BoldPagosEnLinea\BoldConstants;
use BoldPagosEnLinea\BoldCommon;
use BoldPagosEnLinea\BoldSettingModel;
use BoldPagosEnLinea\BoldNotice;

class BoldMenuAdmin
{
    public function __construct()
    {
        add_action('admin_menu', [$this, 'addMenuAdminConfiguration']);
        add_action('admin_notices', [$this, 'showVersionPlugin']);
    }

    public function showVersionPlugin()
    {
        $plugin_local_version = BoldCommon::getPluginVersion();
        $plugin_remote_version = BoldCommon::getPluginVersionRemote();
        $com_version = version_compare($plugin_local_version, $plugin_remote_version, "<");

        if ($com_version) {
            $template_name = 'templates/bold-version-notice.php';
            ob_start();
            include(WP_PLUGIN_DIR . "/" . BoldCommon::getPluginPath() . "/" . $template_name);
            $content = ob_get_clean();
            echo wp_kses($content, BoldConstants::TAGS_ENABLED);
        }
    }

    // Add administration page
    public function addMenuAdminConfiguration(): void
    {
        add_menu_page(
            'Bold',
            'Bold',
            'manage_options',
            'bold-pagos-en-linea',
            [$this, 'showConfigPage'],
            plugin_dir_url(__DIR__) . 'assets/img/admin-panel/bold_co_icon.svg',
            55
        );
    }

    private function addAssetsAdmin(): void
    {
        wp_register_script( 'woocommerce_bold_admin_panel_js', plugins_url( '/../assets/js/admin-panel.js', __FILE__ ),
            array('bold-assets-js', 'jquery'), '3.2.0', true );
        wp_enqueue_script( 'woocommerce_bold_admin_panel_js' );
        wp_enqueue_style( 'woocommerce_bold_admin_panel_css', plugin_dir_url( __FILE__ ) . '../assets/css/bold_admin_panel_style.css', false, '3.2.0', 'all' );

        wp_register_script( 'woocommerce_bold_icons-dark-ui', BoldConstants::URL_CHECKOUT.'/library/ui-kit.js?layout=vertical&type=slider&target=bold-config-dark-icons', null, '3.2.0', true );
        wp_enqueue_script( 'woocommerce_bold_icons-dark-ui' );
        wp_register_script( 'woocommerce_bold_icons-light-ui', BoldConstants::URL_CHECKOUT.'/library/ui-kit.js?layout=vertical&type=slider&theme=dark&target=bold-config-light-icons', null, '3.2.0', true );
        wp_enqueue_script( 'woocommerce_bold_icons-light-ui' );

        wp_enqueue_media();
        wp_enqueue_script('woocommerce_bold_media_uploader', plugins_url( '/../assets/js/bold-media-uploader.js', __FILE__ ), ['jquery', 'wp-i18n'], '3.2.0', true);
        wp_localize_script('woocommerce_bold_media_uploader', 'BoldPlugin', ['pluginUrl' => plugin_dir_url(__DIR__)]);
    }

    // Show and get config template
    public function showConfigPage(): void
    {
        $this->addAssetsAdmin();
        $settings_model = new BoldSettingModel();
        $woocommerceExist   = class_exists( 'Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType' );

        if ($woocommerceExist) {
            $settings_model->fillFirstTimeSettings();
            if (isset($_GET["boldco_status"]) && !(empty( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'bold-update-orders' ))) {
                $status = $settings_model->get_gateway()->bold_get_async_order_status();
                if ($status) {
                    $success_notice = new BoldNotice('success', esc_html__('Actualizaste tus órdenes.', 'bold-pagos-en-linea'));
                    echo wp_kses($success_notice->show(), wp_kses_allowed_html('post'));
                } else {
                    $message_error = esc_html__('No se pudieron actualizar tus órdenes, vuelve a intentarlo.', 'bold-pagos-en-linea');
                    $error_notice = new BoldNotice('error', $message_error);
                    $error_notice_script = "<script>document.addEventListener('DOMContentLoaded', function() {if(window.Notiflix) window.Notiflix.Notify.failure('".$message_error."',{zindex: 99999});});</script>";
                    echo wp_kses($error_notice->show(), wp_kses_allowed_html('post'));
                    echo wp_kses($error_notice_script, ['script'=>[]]);
                }
            }else if(isset($_GET["boldco_status"])){
                $message_error = esc_html__('No se pudo actualizar la información, no paso la verificación de seguridad. Intente de nuevo.', 'bold-pagos-en-linea');
                $error_notice = new BoldNotice('warning', $message_error);
                $error_notice_script = "<script>document.addEventListener('DOMContentLoaded', function() {if(window.Notiflix) window.Notiflix.Notify.warning('".$message_error."',{zindex: 99999});});</script>";
                echo wp_kses($error_notice->show(), wp_kses_allowed_html('post'));
                echo wp_kses($error_notice_script, ['script'=>[]]);
            }
        }

        if (isset($_SERVER["REQUEST_METHOD"]) && sanitize_text_field(wp_unslash($_SERVER["REQUEST_METHOD"])) === "POST") {
            if ( empty( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field(wp_unslash($_POST['_wpnonce'])), 'bold-update-settings' ) ) {
                $message_error = esc_html__('No se pudo guardar la información, no paso la verificación de seguridad. Intente de nuevo.', 'bold-pagos-en-linea');
                $error_notice = new BoldNotice('warning', $message_error);
                $error_notice_script = "<script>document.addEventListener('DOMContentLoaded', function() {if(window.Notiflix) window.Notiflix.Notify.warning('".$message_error."',{zindex: 99999});});</script>";
                echo wp_kses($error_notice->show(), wp_kses_allowed_html('post'));
                echo wp_kses($error_notice_script, ['script'=>[]]);
            }else{
                try {
                    $settings_model->mapPostDataToSettingModel($_POST);
                    if (class_exists('Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType')) {
                        $settings_model->verifyWebhookRemote($_POST);
                    }
                    $settings_model->saveSettingModelToOptions();
                    $message = $settings_model->getEnabled() === 'yes' 
                        ? esc_html__('Guardaste tus configuraciones.', 'bold-pagos-en-linea') 
                        : esc_html__('Guardaste tus configuraciones, recuerda habilitar el método de pago.', 'bold-pagos-en-linea');
                    $success_notice = new BoldNotice('success', $message);
                    echo wp_kses($success_notice->show(), wp_kses_allowed_html('post'));
                } catch (\InvalidArgumentException $e) {
                    $error_notice = new BoldNotice('error', 'Error: ' . $e->getMessage());
                    $error_notice_script = "<script>document.addEventListener('DOMContentLoaded', function() {if(window.Notiflix) window.Notiflix.Notify.failure('".esc_html($e->getMessage())."',{zindex: 99999});});</script>";
                    echo wp_kses($error_notice->show(), wp_kses_allowed_html('post'));
                    echo wp_kses($error_notice_script, ['script'=>[]]);
                }
            }
        }

        $template_name = 'templates/bold-admin_panel.php';
        ob_start();
        include(WP_PLUGIN_DIR . "/" . BoldCommon::getPluginPath() . "/" . $template_name);
        $content = ob_get_clean();
        echo wp_kses($content, BoldConstants::TAGS_ENABLED);
    }
}