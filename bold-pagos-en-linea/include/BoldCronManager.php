<?php
namespace BoldPagosEnLinea;

if (!defined('ABSPATH')) {
    exit;
}

use BoldPagosEnLinea\BoldCommon;
use BoldPagosEnLinea\BoldSettingModel;

class BoldCronManager{
    private static $cron_interval = 'hourly';
    const CRON_HOOK = 'bold_update_orders_cron';

    public static function schedule_cron() {
        if ( ! wp_next_scheduled( self::CRON_HOOK ) ) {
			wp_schedule_event( time() + 600, self::$cron_interval, self::CRON_HOOK );
		}
    }

    public static function execute_cron_task() {
        $settings_model = new BoldSettingModel();
        $settings_model->fillFirstTimeSettings();
        $status = $settings_model->get_gateway()->bold_get_async_order_status();
        if ($status) {
            BoldCommon::logEvent(esc_html__('Se actualizarón tus órdenes.', 'bold-pagos-en-linea'));
        } else {
            BoldCommon::logEvent(esc_html__('No se pudieron actualizar tus órdenes, vuelve a intentarlo.', 'bold-pagos-en-linea'));
        }
    }

    public static function deactivate() {
        $timestamp = wp_next_scheduled(self::CRON_HOOK);
        if ($timestamp) {
            wp_unschedule_event($timestamp, self::CRON_HOOK);
        }
    }
}