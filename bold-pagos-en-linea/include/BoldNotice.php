<?php
namespace BoldPagosEnLinea;

if (!defined('ABSPATH')) {
    exit;
}
use BoldPagosEnLinea\BoldCommon;

class BoldNotice {

    private $allowed_types = ['error', 'success', 'warning', 'info'];
    private $type;
    private $message;

    public function __construct($type, $message) {
        if (!in_array($type, $this->allowed_types)) {
            $this->type = 'info';
        } else {
            $this->type = $type;
        }

        $this->message = $message;
    }

    public function show() {
        $class = $this->get_class_by_type($this->type);
        $message = $this->message;
        $type = $this->type;
        $template_name = 'templates/bold-notice-template.php';
        ob_start();
        include( WP_PLUGIN_DIR . "/" . BoldCommon::getPluginPath() . "/" . $template_name );
        return ob_get_clean();
    }

    private function get_class_by_type($type) {
        switch ($type) {
            case 'error':
                return 'notice-error';
            case 'success':
                return 'notice-success';
            case 'warning':
                return 'notice-warning';
            case 'info':
            default:
                return 'notice-info';
        }
    }
}
