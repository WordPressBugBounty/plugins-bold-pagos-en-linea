const boldSettings = window.wc.wcSettings.getSetting('bold_co_data', {});
const boldLabelText = window.wp.htmlEntities.decodeEntities(boldSettings.title);
const boldContent = () => {
    return window.wp.element.createElement('div', {
        dangerouslySetInnerHTML: {__html: boldSettings.description}
    });
};
if (!window.bold) {
    window.bold = {};
}
if (typeof window.bold.iconRenderToken === 'undefined') {
    window.bold.iconRenderToken = 0;
}

const boldIcon = () => {
    try {
        if (!boldSettings.icon) {
            return null;
        }

        const isLight = boldSettings.icon.indexOf('light') !== -1;
        const existingScripts = Array.from(document.querySelectorAll('script[src*="checkout.bold.co/library/ui-kit.js"]'));
        existingScripts.forEach(script => script.remove());

        const renderToken = ++window.bold.iconRenderToken;
        const timestamp = new Date().getTime();
        const script = document.createElement('script');
        script.src = 'https://checkout.bold.co/library/ui-kit.js?hideLogo&type=slider&target=bold-icon-checkout'+((isLight)?'&theme=dark':'')+`&v=${timestamp}`;
        script.async = true;
        script.onerror = () => {
            if (window.bold.iconRenderToken !== renderToken) {
                return;
            }
            try {
                const container = document.getElementById('bold-icon-checkout');
                if (!container) {
                    return;
                }
                const BoldImage = document.createElement('img');
                BoldImage.src = boldSettings.icon;
                BoldImage.style.float = 'right';
                BoldImage.style.marginRight = '20px';
                BoldImage.alt = 'Bold';

                container.innerHTML = '';
                container.appendChild(BoldImage);
            } catch (error) {
                console.error('Bold: error al aplicar icono de respaldo en checkout de bloques:', error);
            }
        };
        document.body.appendChild(script);

        return window.wp.element.createElement('div', {id: 'bold-icon-checkout', style: {float: 'right', marginRight: '20px', maxWidth: '40%'}});
    } catch (error) {
        console.error('Bold: error al renderizar icono en checkout de bloques:', error);
        return null;
    }
};

const boldLabel = () => {
    return (
        window.wp.element.createElement('div', {
                style: {width: '100%', display: 'inline'},
            },
            window.wp.element.createElement(boldIcon, null),
            boldLabelText
        )
    )
};

const boldBlockGateway = {
    name: 'bold_co',
    label: window.wp.element.createElement(boldLabel, null),
    content: window.wp.element.createElement(boldContent, null),
    edit: window.wp.element.createElement(boldContent, null),
    canMakePayment: () => true,
    ariaLabel: boldLabelText,
    supports: {
        features: boldSettings.supports,
    },
};
window.wc.wcBlocksRegistry.registerPaymentMethod(boldBlockGateway);
