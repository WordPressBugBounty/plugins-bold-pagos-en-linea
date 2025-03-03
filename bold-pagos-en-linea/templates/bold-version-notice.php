<?php

if (!defined('ABSPATH')) {
    exit;
}

use BoldPagosEnLinea\BoldCommon;
$plugin_local_version  = BoldCommon::getPluginVersion();
$plugin_remove_version = BoldCommon::getPluginVersionRemote();
wp_enqueue_style( 'woocommerce_bold_admin_notification_css', plugin_dir_url( __FILE__ ) . '../assets/css/bold_admin_notification_style.css', false, '3.1.7', 'all' );
?>
<div
    class="notice notice-warning is-dismissible bold_plugin_update_notification">
    <img
        class="bold_plugin_update_notification__icon"
        src="<?php echo esc_url(plugin_dir_url( __DIR__ )."assets/img/bold_logo_dark_icon.svg"); ?>"
        alt="settings icon"
    />
    <h2 class="bold_plugin_update_notification__title">
    <?php 
        /* translators: %s is the remote version of the plugin that is available */
        echo esc_html(sprintf(__('La nueva versión del Plugin Bold %s ya está disponible para ti', 'bold-pagos-en-linea'), $plugin_remove_version)); 
        ?>
    </h2>
    <h4 class="bold_plugin_update_notification__subtitle">
        <?php 
        /* translators: %s is the local version of the plugin that the user has installed */
        echo esc_html(sprintf(__('Tu versión actual es la %s, actualízala ahora para tener una mejor experiencia y evitar posibles errores.', 'bold-pagos-en-linea'), $plugin_local_version)); 
        ?>
    </h4>

    <button
            type="button"
            class="bold_plugin_update_notification__link"
    >
        <a
                href="https://developers.bold.co/pagos-en-linea/boton-de-pagos/plugins/wordpress"
                target="_blank"
        >Actualizar</a>
        <img
            class="bold_plugin_update_notification__arrow"
            src="<?php echo esc_url(plugin_dir_url( __DIR__ )."assets/img/arrow_down_icon.svg"); ?>"
            alt="settings icon"
        />
    </button>
</div>