<?php

if (!defined('ABSPATH')) {
    exit;
}

use BoldPagosEnLinea\BoldCommon;

wp_register_script( 'woocommerce_bold_admin_notifications', plugins_url( '/../assets/libraries/awesome-notifications/dist/index.var.js', __FILE__ ), array(), '3.0.2', true );
wp_enqueue_script( 'woocommerce_bold_admin_notifications' );
wp_register_script( 'woocommerce_bold_admin_panel_js', plugins_url( '/../assets/js/admin-panel.js', __FILE__ ), array(
	'woocommerce_bold_admin_notifications',
	'jquery'
), '3.0.2', true );
wp_enqueue_script( 'woocommerce_bold_admin_panel_js' );
wp_enqueue_style( 'woocommerce_bold_admin_notifications_css', plugin_dir_url( __FILE__ ) . '../assets/libraries/awesome-notifications/dist/style.css', false, '3.0.2', 'all' );
wp_enqueue_style( 'woocommerce_bold_admin_panel_css', plugin_dir_url( __FILE__ ) . '../assets/css/bold_admin_panel_style.css', false, '3.0.2', 'all' );

$prefix                     = BoldCommon::getOptionKey( 'prefix', 'Bold' );
$testMode                   = BoldCommon::getOptionKey( 'test', 'no' );
$colorIsLight               = BoldCommon::getOptionKey( 'logo_is_light', 'no' );
$identityKey                = BoldCommon::getOptionKey( 'prod_api_key', '' );
$secretKey                  = BoldCommon::getOptionKey( 'prod_secret_key', '' );
$testIdentityKey            = BoldCommon::getOptionKey( 'test_api_key', '' );
$testSecretKey              = BoldCommon::getOptionKey( 'test_secret_key', '' );
$origin_url                 = BoldCommon::getOptionKey( 'origin_url', '' );
$woocommerceExist           = class_exists( 'Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType' );
$webhookUrl                 = $woocommerceExist ? add_query_arg( 'wc-api', 'bold_co', trailingslashit( get_home_url() ) ) : '';
$manual_update              = esc_url( wp_nonce_url( admin_url( 'admin.php?page=bold-pagos-en-linea&boldco_status=yes' ) , 'bold-update-orders') );
$requiredFieldValid         = function ( $item ) {
	return strlen( $item ) != 0;
};
$savedConfig                = BoldCommon::isSavedParams( array(
	$identityKey,
	$secretKey,
	$testIdentityKey,
	$testSecretKey,
	$prefix
), $requiredFieldValid, true );
$activatedPaymentMethodText = BoldCommon::getOptionKey( 'enabled', '' ) === 'yes' ? __('Ir a deshabilitar el método de pago', 'bold-pagos-en-linea') : __('Ir a habilitar el método de pago', 'bold-pagos-en-linea');
$wooCommerceConfigUrl       = esc_url( admin_url( 'admin.php?page=wc-settings&tab=checkout&section=bold_co' ) );
$form_url = add_query_arg( array( 
	'page' => 'bold-pagos-en-linea'
), admin_url( 'admin.php' ) );
?>

<form id="form__admin__panel" action="<?php echo esc_url($form_url); ?>" method="POST"
      class="bold_admin_panel">
    <?php echo wp_kses(wp_nonce_field( 'bold-update-settings' ), wp_kses_allowed_html('post')); ?>
    <section class="banner">
        <img src="<?php echo esc_url(plugin_dir_url( __DIR__ )."assets/img/admin-panel/banner_nairo_bg.jpg"); ?>" class="banner__bg"
             alt="banner nairo bg"/>
        <article class="banner__info">
            <h1 class="banner__info__title">
                <?php echo esc_html__('Lleva las ventas de tu página web a otro nivel con Bold', 'bold-pagos-en-linea') ?>
            </h1>
            <p class="banner__info__subtitle">
                <?php echo esc_html__('Pásate al Botón de pagos con la mejor experiencia para ti y tus clientes.', 'bold-pagos-en-linea') ?>
            </p>
        </article>
        <img
                class="banner__info__icon"
                src="<?php echo esc_url(plugin_dir_url( __DIR__ )."assets/img/admin-panel/banner_icon.png"); ?>"
                alt="banner icon"
        />
    </section>
	<?php if ( $woocommerceExist ): ?>
        <section id="bold__payment__method__status" class="bold-card">
            <div id="bold__payment__method__item">
                <span id="bold__payment__method__item__title"><?php echo esc_html__('Método de pago', 'bold-pagos-en-linea') ?></span>
                <p id="bold__payment__method__item__desc"><?php echo esc_html__('Para habilitar el método de pago y empezar a recibir pagos con Bold, debes completar las configuraciones y hacer clic en "Guardar cambios".', 'bold-pagos-en-linea') ?></p>
                <button type="button" data-href="<?php echo esc_url($wooCommerceConfigUrl); ?>" id="bold__payment__method__item__btn"
                        data-saved-config="<?php echo $savedConfig ? 1 : 0 ?>"
                        class="bold__payment__method__item__btn--<?php echo $savedConfig ? 'bg-king-blue' : 'bg-opaque-blue' ?>">
					<?php echo esc_html($activatedPaymentMethodText) ?>
                    <img
                            class="bold__payment__method__item__btn__icon"
                            src="<?php echo esc_url(plugin_dir_url( __DIR__ )."assets/img/admin-panel/arrow_right_icon.svg"); ?>"
                            alt="banner icon"
                    />
                </button>
            </div>
        </section>
	<?php endif; ?>
    <section class="bold-card">
        <div id="previous__requirements">
            <article class="title__image">
                <img
                        class="title__image__icon"
                        src="<?php echo esc_url(plugin_dir_url( __DIR__ )."assets/img/admin-panel/ic_settings.svg"); ?>"
                        alt="settings icon"
                />
                <span class="title__image__text"><?php echo esc_html__('Requisitos previos', 'bold-pagos-en-linea') ?></span>
            </article>
            <ul id="previous__requirements__list">
                <li>PHP >= 7.4</li>
                <li><?php echo esc_html__('Version mínima recomendada de', 'bold-pagos-en-linea') ?> WordPress: 6.1</li>
                <li><?php echo esc_html__('Version mínima recomendada de', 'bold-pagos-en-linea') ?> WooCommerce: 8.1</li>
                <li><?php echo esc_html__('Llaves de integración', 'bold-pagos-en-linea') ?></li>
            </ul>
            <p id="previous__requirements__text">
                <?php echo esc_html__('Para conocer más detalles acerca de la configuración e integración del plugin del Botón de pagos Bold, puedes ver la', 'bold-pagos-en-linea') ?>
                <a
                        href="https://developers.bold.co/pagos-en-linea/boton-de-pagos/plugins/wordpress"
                        target="_blank"
                        class="link__info__blue previous__requirements__link"
                >
                    <?php echo esc_html__('documentación oficial', 'bold-pagos-en-linea') ?>.
                </a>
            </p>
        </div>
    </section>
    <section class="bold-card">
        <div id="rates">
            <article class="title__image">
                <img
                        class="title__image__icon"
                        src="<?php echo esc_url(plugin_dir_url( __DIR__ )."assets/img/admin-panel/ic_bank_account.svg"); ?>"
                        alt="settings icon"
                />
                <span class="title__image__text"><?php echo esc_html__('Tarifas', 'bold-pagos-en-linea') ?></span>
            </article>
            <article id="rates__list">
        <span class="rates__list__item"
        ><b><?php echo esc_html__('Entre 5 y 10 millones mensuales:', 'bold-pagos-en-linea') ?></b> 2.99% + $900</span
        >
                <span class="rates__list__item"
                ><b><?php echo esc_html__('Entre 3 y 5 millones mensuales:', 'bold-pagos-en-linea') ?></b> 3.09% + $900</span
                >
                <span class="rates__list__item"
                ><b><?php echo esc_html__('Menores a 3 millones mensuales:', 'bold-pagos-en-linea') ?></b> 3.29% + $900</span
                >
                <p id="retention__rates">(<?php echo esc_html__('+ retención de impuestos de ley', 'bold-pagos-en-linea') ?>)</p>
            </article>
            <a id="rates__link" href="https://bold.co/tarifas" target="_blank" class="link__info">
                <?php echo esc_html__('Conocer más sobre tarifas', 'bold-pagos-en-linea') ?>
            </a>
        </div>
    </section>
    <section id="authentication__keys">
        <h2 id="authentication__keys__title" class="section__title">
            1. <?php echo esc_html__('Ingresa tus llaves de integración', 'bold-pagos-en-linea') ?>
        </h2>
        <p id="authentication__keys__desc">
            <?php echo esc_html__('Conoce más sobre las llaves de integración y cómo puedes acceder a ellas', 'bold-pagos-en-linea') ?>
            <a
                    id="authentication__keys__desc__link"
                    class="link__info__blue"
                    href="https://developers.bold.co/pagos-en-linea/llaves-de-integracion"
                    target="_blank"
            >
                <?php echo esc_html__('aquí.', 'bold-pagos-en-linea') ?>
            </a>
        </p>
        <div class="bold-card">
            <div class="authentication__keys__container">
                <article class="authentication__keys__container__header">
                <span class="authentication__keys__container__title"
                ><?php echo esc_html__('Llaves de producción', 'bold-pagos-en-linea') ?></span
                >
                    <p class="authentication__keys__container__item__desc">
                        <?php echo esc_html__('Estas llaves sirven para identificar tu comercio dentro de Bold y garantizar la seguridad en las transacciones.', 'bold-pagos-en-linea') ?>
                    </p>
                </article>
                <article class="authentication__keys__container__item">
                    <label for="inp_prod_api_key"><span class="authentication__keys__container__item__title"><?php echo esc_html__('Llave de identidad', 'bold-pagos-en-linea') ?></span></label>
                    <input
                            id="inp_prod_api_key"
                            type="password"
                            name="prod_api_key"
                            class="authentication__keys__container__item__input bold_co_input_access_key"
                            value="<?php echo esc_attr($identityKey) ?>"
                            required
                    />
                </article>
                <article class="authentication__keys__container__item">
                    <label for="inp_prod_secret_key"><span class="authentication__keys__container__item__title"><?php echo esc_html__('Llave secreta', 'bold-pagos-en-linea') ?></span></label>
                    <input
                            id="inp_prod_secret_key"
                            type="password"
                            name="prod_secret_key"
                            class="authentication__keys__container__item__input bold_co_input_access_key"
                            value="<?php echo esc_attr($secretKey) ?>"
                            required
                    />
                </article>
            </div>
        </div>
        <div class="bold-card">
            <div class="authentication__keys__container">
                <article class="authentication__keys__container__header">
                <span class="authentication__keys__container__title"
                ><?php echo esc_html__('Llaves de ambiente de pruebas', 'bold-pagos-en-linea') ?></span
                >
                    <p class="authentication__keys__container__item__desc">
                        <?php echo esc_html__('Estas llaves sirven para testear la implementación del módulo de Bold en tu página web.', 'bold-pagos-en-linea') ?>
                    </p>
                </article>
                <article class="authentication__keys__container__item">
                    <label for="inp_test_api_key"><span class="authentication__keys__container__item__title"><?php echo esc_html__('Llave de identidad de prueba', 'bold-pagos-en-linea') ?></span></label>
                    <input
                            id="inp_test_api_key"
                            type="password"
                            name="test_api_key"
                            class="authentication__keys__container__item__input bold_co_input_access_key"
                            value="<?php echo esc_attr($testIdentityKey) ?>"
                            required
                    />
                </article>
                <article class="authentication__keys__container__item">
                    <label for="inp_test_secret_key"><span class="authentication__keys__container__item__title"><?php echo esc_html__('Llave secreta de prueba', 'bold-pagos-en-linea') ?></span></label>
                    <input
                            id="inp_test_secret_key"
                            type="password"
                            name="test_secret_key"
                            class="authentication__keys__container__item__input bold_co_input_access_key"
                            value="<?php echo esc_attr($testSecretKey) ?>"
                            required
                    />
                </article>
            </div>
        </div>
    </section>
    <section id="release__mode">
        <h3 id="release__mode__title" class="section__title">
            2. <?php echo esc_html__('Escoge qué modo quieres usar', 'bold-pagos-en-linea') ?>
        </h3>
        <div class="bold-card bold-card__environment">
            <div class="release__mode__item">
                <label
                        class="radio__input release__mode__item__input"
                        for="production__mode"
                >
                    <input
                            id="production__mode"
                            class="release__mode__item__input__el"
                            type="radio"
                            name="test"
                            value="no"
						<?php echo $testMode === 'no' ? 'checked' : '' ?>
                    />
                    <i></i>
                </label>
                <span class="release__mode__item__title"><?php echo esc_html__('Modo de producción', 'bold-pagos-en-linea') ?></span>
                <p class="release__mode__item__desc">
                    <?php echo esc_html__('Usa este modo luego de haber completado toda la integración, para que tus clientes puedan hacer
                    pagos reales en tu página web.', 'bold-pagos-en-linea') ?>
                </p>
                <span
                        class="release__mode__item__tag release__mode__item__tag--active"
                ><?php echo $testMode === 'no' ? esc_html__('Activo', 'bold-pagos-en-linea') : esc_html__('Inactivo', 'bold-pagos-en-linea') ?></span
                >
            </div>
        </div>
        <div class="bold-card bold-card__environment">
            <div class="release__mode__item">
                <label
                        class="radio__input release__mode__item__input"
                        for="test__mode"
                >
                    <input
                            id="test__mode"
                            class="release__mode__item__input__el"
                            type="radio"
                            name="test"
                            value="yes"
						<?php echo $testMode === 'yes' ? 'checked' : '' ?>
                    />
                    <i></i>
                </label>
                <span class="release__mode__item__title"><?php echo esc_html__('Modo de prueba', 'bold-pagos-en-linea') ?></span>
                <p class="release__mode__item__desc">
                    <?php echo esc_html__('Usa este modo para que puedas integrar nuestras soluciones de pago sin tener que usar dinero real durante la fase de desarrollo.', 'bold-pagos-en-linea') ?>
                </p>
                <span
                        class="release__mode__item__tag release__mode__item__tag--inactive"
                ><?php echo $testMode === 'yes' ? esc_html__('Activo', 'bold-pagos-en-linea') : esc_html__('Inactivo', 'bold-pagos-en-linea') ?></span
                >
            </div>
        </div>
        <section id="test__data__information" class="bold-card">
            <img
                    id="test__data__information__icon"
                    src="<?php echo esc_url(plugin_dir_url( __DIR__ )."assets/img/info.png"); ?>"
                    alt="feedback icon"
            />
            <span id="test__data__information__title"><?php echo esc_html__('Datos de prueba', 'bold-pagos-en-linea') ?></span>
            <p id="test__data__information__desc">
                <?php echo esc_html__('Haz una compra simulada y testea tu integración, activando el modo de prueba. Usa los', 'bold-pagos-en-linea') ?>
                <a
                        id="test__data__information__link"
                        href="https://developers.bold.co/pagos-en-linea/boton-de-pagos/ambiente-pruebas#datos-de-prueba"
                        class="link__info__blue"
                        target="_blank"
                >
                    <?php echo esc_html__('datos de prueba.', 'bold-pagos-en-linea') ?>
                </a>
            </p>
        </section>
    </section>
	<?php if ( $woocommerceExist ): ?>
        <section id="sale__notifications">
            <h2 class="section__title">3. <?php echo esc_html__('Notificaciones de venta', 'bold-pagos-en-linea') ?></h2>
            <div id="sale__notifications__webhook" class="bold-card sale__notifications__container">
                <span class="sale__notifications__container__title"
                ><?php echo esc_html__('Webhook (Obligatorio)', 'bold-pagos-en-linea') ?></span
                >
                <p class="sale__notifications__container__desc">
                    <?php echo esc_html__('Configura esta opción para recibir notificaciones automatizadas de los estados de las transacciones
                    hechas con nuestros métodos de pago, o si estás presentando inconvenientes con la actualización del
                    estado de las órdenes.', 'bold-pagos-en-linea') ?> <?php echo esc_html__('Conoce más', 'bold-pagos-en-linea') ?> <a
                            href="https://developers.bold.co/pagos-en-linea/boton-de-pagos/plugins/wordpress/woocommerce#configuraci%C3%B3n-del-webhook"
                            class="link__info__blue sale__notifications__container__link"
                            target="_blank"
                    ><?php echo esc_html__('aquí', 'bold-pagos-en-linea') ?></a
                    >
                </p>
                <p class="sale__notifications__container__desc">
                    <?php echo esc_html__('Inicia sesión en', 'bold-pagos-en-linea') ?>
                    <a
                            href="https://bold.co"
                            class="link__info__blue sale__notifications__container__link"
                            target="_blank"
                    >bold.co</a
                    >
                    <?php echo esc_html__('y en la sección Integraciones configura el webhook ingresando la siguiente URL:', 'bold-pagos-en-linea') ?>
                </p>
                <article class="sale__notifications__container__row">
                    <p class="sale__notifications__container__row__webhook">
						<?php echo esc_url($webhookUrl) ?>
                    </p>
                    <input id="webhook__input__url" type="hidden" value="<?php echo esc_url($webhookUrl) ?>"/>
                    <button
                            id="webhook__url__copy"
                            type="button"
                            class="sale__notifications__container__row__copy"
                    >
                        <?php echo esc_html__('Copiar URL', 'bold-pagos-en-linea') ?>
                    </button>
                </article>
            </div>
            <div id="sale__notifications__manual__update" class="bold-card sale__notifications__container">
                <span class="sale__notifications__container__title"
                ><?php echo esc_html__('Actualización manual', 'bold-pagos-en-linea') ?></span
                >
                <article class="sale__notifications__container__row">
                    <p class="sale__notifications__container__desc">
                        <?php echo esc_html__('Actualiza de forma manual los estados de las transacciones hechas con nuestros métodos de pago.', 'bold-pagos-en-linea') ?>
                    </p>
                    <a href="<?php echo esc_url($manual_update) ?>" class="sale__notifications__container__update">
                        <?php echo esc_html__('Actualizar las órdenes de', 'bold-pagos-en-linea') ?> Bold
                    </a>
                </article>
            </div>
        </section>
    <?php endif; ?>
        <section id="additional__settings">
	        <?php if ( $woocommerceExist ): ?>
            <h3 id="additional__settings__title" class="section__title">
                4. <?php echo esc_html__('Configuraciones adicionales', 'bold-pagos-en-linea') ?>
            </h3>
            <div id="additional__settings__button" class="bold-card">
                <article id="additional__settings__button__info">
                    <span class="additional__settings__title"><?php echo esc_html__('Color del logo', 'bold-pagos-en-linea') ?> Bold</span>
                    <p id="additional__settings__button__info__desc"><?php echo esc_html__('Selecciona el color del logo Bold que mejor se
                        ajuste al diseño de tu página web.', 'bold-pagos-en-linea') ?> </p>
                </article>
                <div id="additional__settings__button__selector">
                    <article class="additional__settings__button__selector__item">
                        <label
                                class="radio__input additional__settings__button__selector__item__input"
                                for="dark_button"
                        >
                            <input
                                    type="radio"
                                    name="logo_is_light"
                                    id="dark_button"
                                    value="no"
								<?php echo $colorIsLight === 'no' ? 'checked' : '' ?>
                            />
                            <i></i>
                        </label>
                        <img src="<?php echo esc_url(plugin_dir_url( __DIR__ )."assets/img/admin-panel/bold_co_button_dark.png"); ?>"
                             class="additional__settings__button__selector__item__icon" alt="dark button"/>
                        <span class="additional__settings__button__selector__item__desc"><?php echo esc_html__('Logo de color para fondos claros', 'bold-pagos-en-linea') ?></span>
                    </article>
                    <article class="additional__settings__button__selector__item">
                        <label
                                class="radio__input additional__settings__button__selector__item__input"
                                for="light_button"
                        >
                            <input
                                    type="radio"
                                    name="logo_is_light"
                                    id="light_button"
                                    value="yes"
								<?php echo $colorIsLight === 'yes' ? 'checked' : '' ?>
                            />
                            <i></i>
                        </label>
                        <img src="<?php echo esc_url(plugin_dir_url( __DIR__ )."assets/img/admin-panel/bold_co_button_light.png"); ?>"
                             class="additional__settings__button__selector__item__icon" alt="light button"/>
                        <span class="additional__settings__button__selector__item__desc"><?php echo esc_html__('Logo de color para fondos oscuros', 'bold-pagos-en-linea') ?></span>
                    </article>
                </div>
            </div>
            <div id="additional__settings__prefix" class="bold-card">
                <label for="additional__settings__prefix__input"><span
                            class="additional__settings__title"><?php echo esc_html__('Prefijo', 'bold-pagos-en-linea') ?></span></label>
                <input
                        type="text"
                        name="prefix"
                        id="additional__settings__prefix__input"
                        value="<?php echo esc_attr($prefix) ?>"
                        required
                />
                <p id="additional__settings__prefix__desc">
                    <?php echo esc_html__('Puedes agregar un prefijo al número de las órdenes. Sólo se aceptan valores alfanuméricos, guiones
                    bajos "_" y medios "-".', 'bold-pagos-en-linea') ?> <?php echo esc_html__('Conoce más', 'bold-pagos-en-linea') ?> <a id="additional__settings__prefix__desc__link"
                                                          href="https://developers.bold.co/pagos-en-linea/boton-de-pagos/plugins/wordpress/woocommerce"
                                                          target="_blank" class="link__info__blue"><?php echo esc_html__('aquí', 'bold-pagos-en-linea') ?>.</a>
                </p>
            </div>
            <?php endif; ?>
            <div id="additional__settings__originurl" class="bold-card">
                <label for="additional__settings__originurl__input"><span
                            class="additional__settings__title"><?php echo esc_html__('URL retorno por abandono', 'bold-pagos-en-linea') ?></span></label>
                <input
                        type="url"
                        name="origin_url"
                        id="additional__settings__originurl__input"
                        placeholder="https://bold.co"
                        value="<?php echo esc_attr($origin_url) ?>"
                />
                <p id="additional__settings__originurl__desc">
                    <?php echo esc_html__('Configura una URL de retorno para redirigir al usuario si abandona o cancela. Si no lo haces, usaremos la URL del checkout predeterminada.',
                     'bold-pagos-en-linea') ?> <?php echo esc_html__('Conoce más', 'bold-pagos-en-linea') ?> <a id="additional__settings__prefix__desc__link"
                                                          href="https://developers.bold.co/pagos-en-linea/boton-de-pagos/plugins/wordpress/woocommerce#configuraciones-espec%C3%ADficas-para-woocommerce"
                                                          target="_blank" class="link__info__blue"><?php echo esc_html__('aquí', 'bold-pagos-en-linea') ?>.</a>
                </p>
            </div>
        </section>
    <button type="submit" id="btn__admin__save"><?php echo esc_html__('Guardar cambios', 'bold-pagos-en-linea') ?></button>
</form>