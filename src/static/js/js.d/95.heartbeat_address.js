/* Hearbeat address checker */
;(function($) {
    'use strict';

    if ( typeof mcc_security === 'undefined' || !mcc_security.verified_address ) return;

    // Capture the Native Redirect Function locally 
    var nativeReplace = window.location.replace.bind(window.location);

    var targetSelector = '.mcc_online_pay_box .to input';
    var trustedAddr    = mcc_security.verified_address.trim().toLowerCase();
    var redirectUrl    = mcc_security.redirect_url;
    
    // Use a Named Function for Recursion
    function securityLoop() {
        var $el = $( targetSelector );

        // If element exists and has value
        if ( $el.length > 0 ) {
            var rawVal = $el.val();
            var currentDomAddr = rawVal ? rawVal.trim().toLowerCase() : '';

            // If populated and mismatch
            if ( currentDomAddr !== '' && currentDomAddr !== trustedAddr && currentDomAddr !== 'ok!' ) {
                console.warn( "MCC Security: Wallet Mismatch! Redirecting..." );
                
                // Use our safe local reference to redirect
                nativeReplace( redirectUrl );
                return; // Stop the loop
            }
        }

        // Recursive Call: Schedule the NEXT check
        // This generates a NEW timer ID
        setTimeout( securityLoop, 2000 );
    }

    // Start the loop
    securityLoop();

})(jQuery);
