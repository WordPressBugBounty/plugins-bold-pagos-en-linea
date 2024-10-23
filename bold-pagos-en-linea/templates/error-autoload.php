<?php

if (!defined('ABSPATH')) {
    exit;
}

?>

<div class="notice notice-error">
    <p>
        <b>No se puede encontrar el cargador automático del composer en <code><?php echo esc_html($file_autoload) ?></code></b>
    </p>
    <p>Su instalación de Bold está incompleta.</p>
</div>
