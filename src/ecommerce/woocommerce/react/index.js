import React, { useState, useEffect, useRef } from 'react';
import { decodeEntities } from '@wordpress/html-entities';
import DOMPurify from 'dompurify';

const { registerPaymentMethod } = window.wc.wcBlocksRegistry
const { getSetting } = window.wc.wcSettings

const settings = getSetting( 'mycryptocheckout_data', {} );

const label = decodeEntities( settings.title );

/**
 * Content component
 */
const Content = (props) => {
    const { eventRegistration } = props;
    const { onPaymentSetup } = eventRegistration;

    // State to store the selected currency
    const [selectedCurrency, setSelectedCurrency] = useState('');

    // Ref for the container to render the unsafe HTML
    const containerRef = useRef(null);

    // Effect to handle payment processing
    useEffect(() => {
        const unsubscribe = onPaymentSetup(() => {
            if (selectedCurrency) {
                return {
                    type: 'success',
                    meta: {
                        paymentMethodData: {
                            selectedCurrency,
                        },
                    },
                };
            } else {
                return {
                    type: 'error',
                    message: 'Please select a currency.',
                };
            }
        });

        return () => unsubscribe();
    }, [selectedCurrency, onPaymentSetup]);

    // Effect to handle dynamic HTML and event binding
    useEffect(() => {
        if (containerRef.current) {
            const selectElement = containerRef.current.querySelector('select#mcc_currency_id');
            if (selectElement) {
                selectElement.addEventListener('change', (event) => {
                    setSelectedCurrency(event.target.value);
                });
            }
        }
    }, []); // Empty dependency array ensures this runs only once on mount

    return (
        <div ref={containerRef} dangerouslySetInnerHTML={{ __html: DOMPurify.sanitize(decodeEntities(settings.payment_fields)) }} />
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