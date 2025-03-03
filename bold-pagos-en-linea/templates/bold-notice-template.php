<?php

if (!defined('ABSPATH')) {
    exit;
}

?>
<div
    class="notice <?php echo esc_attr($class) ?> is-dismissible bold_plugin_notification">
    <img
        class="bold_plugin_notification__icon"
        src="<?php echo esc_url(plugin_dir_url( __DIR__ )."assets/img/".$type.".png"); ?>"
        alt="<?php echo esc_attr($type) ?> icon"
    />
    <h4 class="bold_plugin_notification__subtitle"><?php echo esc_html($message) ?></h4>
</div>