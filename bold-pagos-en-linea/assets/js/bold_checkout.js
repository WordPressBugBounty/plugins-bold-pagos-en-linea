const boldSettings = window.wc.wcSettings.getSetting('bold_co_data', {});
const boldLabelText = window.wp.htmlEntities.decodeEntities(boldSettings.title);
const boldContent = () => {
    return window.wp.element.createElement('div', {
        dangerouslySetInnerHTML: {__html: boldSettings.description}
    });
};
const boldIcon = () => {
    const isLight = boldSettings.icon.indexOf('light') !== -1;
    const existingScripts = Array.from(document.querySelectorAll(`script[src^="https://checkout.bold.co/library/ui-kit.js"]`));
        existingScripts.forEach(script => script.remove());
    const script = document.createElement('script');
    script.src = 'https://checkout.bold.co/library/ui-kit.js?hideLogo&type=slider&target=bold-icon-checkout'+((isLight)?'&theme=dark':'');
    script.async = true;
    script.onerror = () => {
        const BoldImage = document.createElement('img');
        BoldImage.src = boldSettings.icon;
        BoldImage.style.float = 'right';
        BoldImage.style.marginRight = '20px';
        BoldImage.alt = 'Bold';
    
        const container = document.getElementById('bold-icon-checkout');
        container.innerHTML = '';
        container.appendChild(BoldImage);
    };
    document.body.appendChild(script);

    return boldSettings.icon ? 
        window.wp.element.createElement('div', {id: 'bold-icon-checkout', style: {float: 'right', marginRight: '20px', maxWidth: '40%'}}
        ) : null;
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
