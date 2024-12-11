<?php

if (!defined('ABSPATH')) {
    exit;
}

use BoldPagosEnLinea\BoldCommon;

$pluginUrl = plugin_dir_url(__FILE__);
$test_mode = BoldCommon::getOptionKey('test');
$is_light = BoldCommon::getOptionKey('logo_is_light');

$checkout_description = __("Te llevaremos a la Pasarela de pagos Bold para completar tu compra con la mejor experiencia, fácil y segura.", 'bold-pagos-en-linea');
$test_mode_text = __("Modo de prueba", 'bold-pagos-en-linea');
$secure_purchase = __("Compra 100% protegida", 'bold-pagos-en-linea');
?>

<bold-checkout-element 
    plugin_url="<?php echo esc_url($pluginUrl); ?>" 
    test_mode="<?php echo esc_attr($test_mode); ?>" 
    is_light="<?php echo esc_attr($is_light); ?>"
>
    <div id="bold_co_container_info_checkout_page">
        <div id="bold_co_checkout_page" class="bold_checkout_element">
            <div id="bold_co_checkout_page_body">
                <div class="bold_co_checkout_page_body_payments_method" id="bold_co_checkout_page_body_payments_method"></div>
                <div id="bold_co_checkout_page_body_test_mode">
                <img src="<?php echo esc_url($pluginUrl); ?>../assets/img/warning.png" alt="<?php echo esc_attr($test_mode_text); ?>" />
                <?php echo esc_attr($test_mode_text); ?>
                </div>
                <div class="bold_co_checkout_page_body_text">
                <?php echo esc_attr($checkout_description); ?>
                </div>
            </div>
            <article id="bold_co_checkout_page_footer">
                <p class="bold_co_checkout_page_body_text" id="bold_co_checkout_page_footer_title">
                <?php echo esc_attr($secure_purchase); ?>
                </p>
                <img id="bold_co_checkout_page_footer_icons" src="<?php echo esc_url($pluginUrl); ?>../assets/img/medios_pago.png" alt="icon" />
            </article>
        </div>
    </div>
</bold-checkout-element>
