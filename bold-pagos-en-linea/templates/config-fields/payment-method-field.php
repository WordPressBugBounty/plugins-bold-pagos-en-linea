<?php

if (!defined('ABSPATH')) {
    exit;
}

?>
<div class="bold__config__field bold__config__field--enabled">
    <article class="bold__config__field__header">
        <span class="bold__config__field__header__text"><?php echo esc_html__('Método de pago', 'bold-pagos-en-linea') ?></span>
    </article>
    <p class="bold__config__field__desc"><?php echo esc_html__('Habilita el método de pago, haz clic en "Guardar cambios", y empieza a recibir
        pagos con Bold.', 'bold-pagos-en-linea') ?></p>
    <label class="bold__config__field__switch">
        <div class="bold__config__field__switch__item <?php echo $params['prefix'] === '' ? 'bold__config__empty' : '' ?>"
             data-status="<?php echo esc_attr($params['enabled']) ?>"></div>
        <span class="bold__config__field__switch__slider bold__config__field__switch__round"></span>
    </label>
</div>