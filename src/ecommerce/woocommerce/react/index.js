import { decodeEntities } from '@wordpress/html-entities';
import DOMPurify from 'dompurify';

const { registerPaymentMethod } = window.wc.wcBlocksRegistry
const { getSetting } = window.wc.wcSettings

const settings = getSetting( 'mycryptocheckout_data', {} );

const label = decodeEntities( settings.title )

/**
 * Content component
 */
const Content = () => {
    // Ensure any HTML entities are decoded before sanitization
    const rawHTML = decodeEntities(settings.payment_fields);

    // Sanitize the HTML
    const safeHTML = DOMPurify.sanitize(rawHTML, {
        ADD_ATTR: ['data-plugin', 'data-allow-clear', 'aria-hidden', 'data-placeholder', 'data-priority'], // Add any non-standard attributes
    });

    return (
        <div dangerouslySetInnerHTML={{ __html: safeHTML }} />
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