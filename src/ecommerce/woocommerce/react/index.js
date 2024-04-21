

import { decodeEntities } from '@wordpress/html-entities';

const { registerPaymentMethod } = window.wc.wcBlocksRegistry
const { getSetting } = window.wc.wcSettings

const settings = getSetting( 'mycryptocheckout_data', {} );

const label = decodeEntities( settings.title )

/**
 * Content component
 */
const Content = () => {
    return (
        <div dangerouslySetInnerHTML={{ __html: decodeEntities(settings.payment_fields) }} />
    );
};
/**
 * Label component
 *
 * @param {*} props Props from payment API.
 */
const Label = ( props ) => {
	const { PaymentMethodLabel } = props.components;
	return <PaymentMethodLabel text={ label } />;
};

/**
 * Payment method config object.
 */
const MCC_Block_Gateway = {
	name: "mycryptocheckout",
	label: <Label />,
	content: <Content />,
	edit: <Content />,
	canMakePayment: () => true,
	ariaLabel: label,
	supports: {
		features: settings.supports,
	},
};

registerPaymentMethod( MCC_Block_Gateway );