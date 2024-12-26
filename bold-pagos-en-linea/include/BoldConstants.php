<?php
namespace BoldPagosEnLinea;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class BoldConstants {
    
    private $TRANSACTION_STATUS;

    public function __construct() {
        $this->TRANSACTION_STATUS = [
            'APPROVED' => __('APPROVED', 'bold-pagos-en-linea'),
            'REJECTED' => __('REJECTED', 'bold-pagos-en-linea'),
            'PENDING' => __('PENDING', 'bold-pagos-en-linea'),
            'FAILED' => __('FAILED', 'bold-pagos-en-linea'),
            'PROCESSING' => __('PROCESSING', 'bold-pagos-en-linea'),
        ];
    }

    public function getTransactionStatus(string $status): string {
        $status_uppercase = strtoupper($status);
        if(iset($this->TRANSACTION_STATUS[$status_uppercase]))
            return $this->TRANSACTION_STATUS[$status_uppercase];
        else
            return $status;
    }

    const URL_CHECKOUT = "https://checkout.bold.co";

    const COLUMNS_KEYS = array(
        'test',
        'prod_api_key',
        'prod_secret_key',
        'test_api_key',
        'test_secret_key',
        'origin_url',
        'enabled',
        'logo_is_light',
        'prefix',
        'settings',
    );
    
    const TAGS_ENABLED = array(
        'a' => array(
            'id' => array(),
            'class' => array(),
            'style' => array(),
            'href' => array(),
            'title' => array(),
            'target' => array(),
            'rel' => array(),
            'download' => array(),
            'data-href' => array(),
            'data-saved-config' => array(),
        ),
        'article' => array(
            'id' => array(),
            'class' => array(),
            'style' => array(),
            'data-href' => array(),
            'data-saved-config' => array(),
        ),
        'b' => array(
            'id' => array(),
            'class' => array(),
            'style' => array(),
            'data-href' => array(),
            'data-saved-config' => array(),
        ),
        'button' => array(
            'id' => array(),
            'class' => array(),
            'style' => array(),
            'type' => array(),
            'name' => array(),
            'value' => array(),
            'disabled' => array(),
            'autofocus' => array(),
            'form' => array(),
            'data-href' => array(),
            'data-saved-config' => array(),
        ),
        'div' => array(
            'id' => array(),
            'class' => array(),
            'style' => array(),
            'data-href' => array(),
            'data-saved-config' => array(),
        ),
        'form' => array(
            'id' => array(),
            'class' => array(),
            'style' => array(),
            'action' => array(),
            'method' => array(),
            'enctype' => array(),
            'name' => array(),
            'autocomplete' => array(),
            'novalidate' => array(),
            'target' => array(),
            'data-href' => array(),
            'data-saved-config' => array(),
        ),
        'h1' => array(
            'id' => array(),
            'class' => array(),
            'style' => array(),
            'data-href' => array(),
            'data-saved-config' => array(),
        ),
        'h2' => array(
            'id' => array(),
            'class' => array(),
            'style' => array(),
            'data-href' => array(),
            'data-saved-config' => array(),
        ),
        'h3' => array(
            'id' => array(),
            'class' => array(),
            'style' => array(),
            'data-href' => array(),
            'data-saved-config' => array(),
        ),
        'h4' => array(
            'id' => array(),
            'class' => array(),
            'style' => array(),
            'data-href' => array(),
            'data-saved-config' => array(),
        ),
        'h5' => array(
            'id' => array(),
            'class' => array(),
            'style' => array(),
            'data-href' => array(),
            'data-saved-config' => array(),
        ),
        'h6' => array(
            'id' => array(),
            'class' => array(),
            'style' => array(),
            'data-href' => array(),
            'data-saved-config' => array(),
        ),
        'i' => array(
            'id' => array(),
            'class' => array(),
            'style' => array(),
            'data-href' => array(),
            'data-saved-config' => array(),
        ),
        'img' => array(
            'id' => array(),
            'class' => array(),
            'style' => array(),
            'src' => array(),
            'alt' => array(),
            'width' => array(),
            'height' => array(),
            'loading' => array(),
            'srcset' => array(),
            'sizes' => array(),
            'data-href' => array(),
            'data-saved-config' => array(),
        ),
        'input' => array(
            'id' => array(),
            'class' => array(),
            'style' => array(),
            'type' => array(),
            'name' => array(),
            'value' => array(),
            'placeholder' => array(),
            'disabled' => array(),
            'readonly' => array(),
            'required' => array(),
            'autocomplete' => array(),
            'autofocus' => array(),
            'form' => array(),
            'checked' => array(),
            'data-href' => array(),
            'data-saved-config' => array(),
        ),
        'label' => array(
            'id' => array(),
            'class' => array(),
            'style' => array(),
            'for' => array(),
            'form' => array(),
            'data-href' => array(),
            'data-saved-config' => array(),
        ),
        'p' => array(
            'id' => array(),
            'class' => array(),
            'style' => array(),
            'data-href' => array(),
            'data-saved-config' => array(),
        ),
        'section' => array(
            'id' => array(),
            'class' => array(),
            'style' => array(),
            'data-href' => array(),
            'data-saved-config' => array(),
        ),
        'span' => array(
            'id' => array(),
            'class' => array(),
            'style' => array(),
            'data-href' => array(),
            'data-saved-config' => array(),
        ),
        'ul' => array(
            'id' => array(),
            'class' => array(),
            'style' => array(),
            'type' => array(),
            'data-href' => array(),
            'data-saved-config' => array(),
        ),
        'li' => array(
            'id' => array(),
            'class' => array(),
            'style' => array(),
            'value' => array(),
            'data-href' => array(),
            'data-saved-config' => array(),
        ),
    );
}