<?php

if (!defined('ABSPATH')) {
    exit;
}

?>
<div class="bold__config__field bold__config__field--configuration">
    <article class="bold__config__field__header">
        <span class="bold__config__field__header__text"><?php echo esc_html__('Configuraciones del plugin', 'bold-pagos-en-linea') ?></span>
    </article>
    <p class="bold__config__field__desc"><?php echo esc_html__('Configura las opciones a tu gusto para tener una mejor experiencia.', 'bold-pagos-en-linea') ?></p>
    <a class="bold__config__field__link" href="<?php echo esc_url( admin_url( 'admin.php?page=bold-pagos-en-linea' ) ) ?>"
       target="_self"><?php echo esc_html__('Ir a configuraciones', 'bold-pagos-en-linea') ?></a>
</div>