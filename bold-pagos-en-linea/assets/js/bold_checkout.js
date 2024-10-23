const settings = window.wc.wcSettings.getSetting('bold_co_data', {});
const label = window.wp.htmlEntities.decodeEntities(settings.title);
const Content = () => {
    return window.wp.element.createElement('div', {
        dangerouslySetInnerHTML: {__html: settings.description}
    });
};
const Icon = () => {
    return settings.icon ? window.wp.element.createElement('img', {
        src: settings.icon,
        style: {float: 'right', marginRight: '20px'},
        alt: 'icon'
    }) : null;
};

const Label = () => {
    return (
        window.wp.element.createElement('span', {
                style: {width: '100%'},
            },
            window.wp.element.createElement(Icon, null),
            label
        )
    )
};

const Block_Gateway = {
    name: 'bold_co',
    label: window.wp.element.createElement(Label, null),
    content: window.wp.element.createElement(Content, null),
    edit: window.wp.element.createElement(Content, null),
    canMakePayment: () => true,
    ariaLabel: label,
    supports: {
        features: settings.supports,
    },
};
window.wc.wcBlocksRegistry.registerPaymentMethod(Block_Gateway);
