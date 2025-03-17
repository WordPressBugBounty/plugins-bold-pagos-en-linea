(function () {
    if(!window.bold){
        window.bold = {};
    }
    const paymentInput = document.getElementById('payment_method_bold_co');
    if (paymentInput) {
        paymentInput.addEventListener('change', bold_load_icon_basic);
    }
    jQuery( document.body ).on( 'updated_checkout', function() {
        window.boldUIKKitAlreadyProcessed = undefined;
        bold_load_icon_basic();
    });
})();

function bold_load_icon_basic(){
    const label = document.querySelector('label[for="payment_method_bold_co"]');
    if (label) {
        const boldIcon = label?.querySelector(':scope > img')?.src;
        if (!window.bold.label) {
            window.bold.label = label.innerText.trim();
        }
        if(boldIcon){
            window.bold.icon = boldIcon;
            window.bold.isLight = boldIcon.indexOf('light') !== -1;
        }

        const timestamp = new Date().getTime();
        const iconsIdContainer = 'bold-icon-checkout'+timestamp;
        label.innerHTML = window.bold.label ? window.bold.label : label.innerText;
        const divIcons = document.createElement('div');
        divIcons.id = iconsIdContainer;
        Object.assign(divIcons.style, {
            float: 'right',
            marginRight: '20px',
            marginLeft: 'auto',
            maxWidth: '40%'
        });
        label.appendChild(divIcons);
        const existingScripts = Array.from(document.querySelectorAll(`script[src*="${BoldPlugin.checkoutUrl}/library/ui-kit.js"]`));
        existingScripts.forEach(script => script.remove());

        const script = document.createElement('script');
        script.src = `${BoldPlugin.checkoutUrl}/library/ui-kit.js?hideLogo&type=slider&target=${iconsIdContainer}${window.bold.isLight ? '&theme=dark' : ''}&v=${timestamp}`;
        script.async = true;

        script.onload = () => {
            label.querySelector('img')?.remove();
        };

        script.onerror = () => {
            const BoldImage = document.createElement('img');
            BoldImage.src = window.bold.icon;
            BoldImage.style.float = 'right';
            BoldImage.style.marginRight = '20px';
            BoldImage.alt = 'Bold';
        
            label.innerHTML = label.innerText;
            label.appendChild(BoldImage);
        };

        document.body.appendChild(script);
    }
}
