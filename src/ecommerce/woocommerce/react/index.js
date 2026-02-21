import React, { useState, useEffect, useRef } from 'react';
import { decodeEntities } from '@wordpress/html-entities';
import DOMPurify from 'dompurify';

const { registerPaymentMethod } = window.wc.wcBlocksRegistry;
const { getSetting } = window.wc.wcSettings;

const settings = getSetting('mycryptocheckout_data', {});

const label = decodeEntities(settings.title);

/**
 * Content component
 */
const Content = (props) => {
    const { eventRegistration, emitResponse } = props;
	const { onPaymentSetup } = eventRegistration;

    // State to store the selected currency
    const [selectedCurrency, setSelectedCurrency] = useState('');

    // Ref for the container to render the unsafe HTML
    const containerRef = useRef(null);

    // Effect to handle payment processing
    useEffect(() => {
        const unsubscribe = onPaymentSetup(() => {
            console.log("Setup called with selectedCurrency:", selectedCurrency);  // Debugging selected currency
            if (selectedCurrency) {
                return {
                    type: emitResponse.responseTypes.SUCCESS,
                    meta: {
                        paymentMethodData: {
                            selectedCurrency,  // Key used to send data
                        },
                    },
                };
            } else {
                return {
                    type: emitResponse.responseTypes.ERROR,
                    message: 'Please select a currency.',
                };
            }
        });

        return () => unsubscribe();
    }, [emitResponse.responseTypes.ERROR,
		emitResponse.responseTypes.SUCCESS, selectedCurrency, onPaymentSetup]);

    // Effect to handle dynamic HTML and event binding
    useEffect(() => {
        if (containerRef.current) {
            const selectElement = containerRef.current.querySelector('select#mcc_currency_id');
            if (selectElement) {
                // Set initial state to the first option's value if not already set
                if (selectElement.length > 0 && !selectedCurrency) {
                    const firstCurrency = selectElement.options[0].value;
                    setSelectedCurrency(firstCurrency);
                    console.log("Initial currency set to:", firstCurrency);  // Debugging initial set
                }

                // 1. Define the function properly so we can remove it later
                const handleCurrencyChange = (event) => {
                    console.log("Currency changed to:", event.target.value);
                    setSelectedCurrency(event.target.value);
                };

                // 2. Add Listener
                selectElement.addEventListener('change', handleCurrencyChange);

                // 3. Return the cleanup function
                return () => {
                    selectElement.removeEventListener('change', handleCurrencyChange);
                };
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
const Label = (props) => {
    const { PaymentMethodLabel } = props.components;
    return <PaymentMethodLabel text={label} />;
};

/**
 * Payment method config object.
 */
const MCC_Block_Gateway = {
    name: "mycryptocheckout",
    label: React.createElement( Label, null ),
	content: React.createElement( Content, null ),
	edit: React.createElement( Content, null ),
    canMakePayment: () => true,
    ariaLabel: label,
    supports: {
        features: settings.supports,
    },
};

registerPaymentMethod(MCC_Block_Gateway);