const settings = window.wc.wcSettings.getSetting( 'mycryptocheckout_data', {} );
const label = window.wp.htmlEntities.decodeEntities( settings.title ) || window.wp.i18n.__( 'MyCryptoCheckout' );
const Content = () => {
    return window.wp.htmlEntities.decodeEntities( settings.description || '' );
};
const MCC_Block_Gateway = {
    name: 'MyCryptoCheckout',
    label: label,
    content: Object( window.wp.element.createElement )( Content, null ),
    edit: Object( window.wp.element.createElement )( Content, null ),
    canMakePayment: () => true,
    ariaLabel: label,
    supports:
    {
        features: settings.supports,
    },
};
window.wc.wcBlocksRegistry.registerPaymentMethod( MCC_Block_Gateway );
