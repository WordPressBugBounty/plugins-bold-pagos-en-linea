import './styles.scss';
import './editor.scss';
import { PanelBody, TextControl, SelectControl } from '@wordpress/components';
import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import { InspectorControls } from '@wordpress/blockEditor';
import { useState } from '@wordpress/element';

registerBlockType('bold/button-block', {
    title: __('Bóton manual', 'bold-pagos-en-linea'),
    icon: () => (
        <img 
            src={boldBlockData.iconUrl} 
            alt={__('Icono del botón de pagos Bold', 'bold-pagos-en-linea')} 
            style={{ width: '24px' }}
        />
    ),
    category: 'bold-category',
    attributes: {
        amount: { type: 'string', default: 0 },
        currency: { type: 'string', default: 'COP' },
        description: { type: 'string', default: '' },
        redirectionUrl: { type: 'string', default: '' },
        color: { type: 'string', default: 'dark' },
        size: { type: 'string', default: 'L' },
    },
    edit: ({ attributes, setAttributes }) => {
        const { amount, currency, description, redirectionUrl, color, size } = attributes;
        const [urlValid, setUrlValid] = useState(true);

        const handleUrlChange = (value) => {
            const regex = /https:\/\/(localhost|(?:[a-zA-Z0-9-]+\.)+[a-zA-Z]{2,})(\/.*)?|http:\/\/localhost(\/.*)?/;
            setAttributes({ redirectionUrl: value });
            if (value.length===0 || regex.test(value)) {
                setUrlValid(true);
            } else {
                setUrlValid(false);
            }
        };

        return (
            <>
                <InspectorControls>
                    <PanelBody title={__('Configuración del botón', 'bold-pagos-en-linea')}>
                        <TextControl
                            type='number'
                            label={__('Monto a cobrar', 'bold-pagos-en-linea')}
                            value={amount}
                            onChange={(value) => setAttributes({ amount: value })}
                            help={__('Si quieres que tu cliente decida cuánto quiere pagar el monto deberá ser cero', 'bold-pagos-en-linea')}
                            required='true'
                            min='0'
                        />
                        <SelectControl
                            label={__('Divisa', 'bold-pagos-en-linea')}
                            value={currency}
                            options={[
                                { label: 'COP', value: 'COP' },
                                { label: 'USD', value: 'USD' },
                            ]}
                            default='COP'
                            onChange={(value) => setAttributes({ currency: value })}
                            help={__('Si el monto es cero, se cobrará en COP', 'bold-pagos-en-linea')}
                        />
                        <TextControl
                            label={__('Descripción de la venta', 'bold-pagos-en-linea')}
                            value={description}
                            onChange={(value) => setAttributes({ description: value })}
                            help={__('Opcional', 'bold-pagos-en-linea')}
                            maxlength='100'
                            minlength='2'
                        />
                        <TextControl
                            type='url'
                            label={__('URL a la que redirigir al cliente tras finalizar una transacción', 'bold-pagos-en-linea')}
                            value={redirectionUrl}
                            onChange={ handleUrlChange }
                            help={__('Opcional', 'bold-pagos-en-linea')+'. '+__('Debe ser una URL válida que comience con https://', 'bold-pagos-en-linea')}
                            pattern="https://.+|http://localhost(/.*)?"
                            className={urlValid ? '' : 'bold-invalid-input'}
                        />
                        <SelectControl
                            label={__('Color', 'bold-pagos-en-linea')}
                            value={color}
                            options={[
                                { label: 'Dark', value: 'dark' },
                                { label: 'Light', value: 'light' },
                            ]}
                            default='dark'
                            onChange={(value) => setAttributes({ color: value })}
                        />
                        <SelectControl
                            label={__('Tamaño', 'bold-pagos-en-linea')}
                            value={size}
                            options={[
                                { label: 'L (48px)', value: 'L' },
                                { label: 'M (40px)', value: 'M' },
                                { label: 'S (32px)', value: 'S' },
                            ]}
                            default='L'
                            onChange={(value) => setAttributes({ size: value })}
                        />
                    </PanelBody>
                </InspectorControls>
                <div 
                    className={`bold-button-preview bold-button-${color} size-${size}`} 
                    style={{
                        backgroundImage:'url('+((color=='dark')?boldBlockData.exampleButtonDark:boldBlockData.exampleButtonLight)+')',
                        }}>
                </div>
            </>
        );
    },
    save: () => null,
    example: {
        attributes: {
            amount: 10000,
            currency: 'COP',
            description: 'Descripción de ejemplo',
            redirectionUrl: 'https://example.com/finaliza-compra',
            color: 'dark',
        },
        innerHTML:  '<div style="text-align:center">'+
                    '<div className="bold-button-preview bold-button-light" style="background-image:url('+boldBlockData.exampleButtonLight+'></div>'+
                    '<div className="bold-button-preview bold-button-dark" style="background-image:url('+boldBlockData.exampleButtonDark+'></div>'+
                    '</div>',
    },
});
