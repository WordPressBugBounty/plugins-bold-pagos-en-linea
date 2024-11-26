const boldSettings = window.wc.wcSettings.getSetting('bold_co_data', {});
const boldLabelText = window.wp.htmlEntities.decodeEntities(boldSettings.title);
const boldContent = () => {
    return window.wp.element.createElement('div', {
        dangerouslySetInnerHTML: {__html: boldSettings.description}
    });
};
const boldIcon = () => {
    return boldSettings.icon ? window.wp.element.createElement('img', {
        src: boldSettings.icon,
        style: {float: 'right', marginRight: '20px'},
        alt: 'icon'
    }) : null;
};

const boldLabel = () => {
    return (
        window.wp.element.createElement('span', {
                style: {width: '100%'},
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
