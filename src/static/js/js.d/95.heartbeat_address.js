/* Hearbeat address checker */
;(function($) {
    'use strict';

    // 1. Restriction: Only run if mcc_security exists (WooCommerce only).
    if ( typeof mcc_security === 'undefined' || !mcc_security.verified_address ) {
        return;
    }

    // 2. Selector targeting ONLY the "To" address input
    var targetSelector = '.mcc_online_pay_box .to input';
    
    // Normalize PHP address
    var trustedAddr  = mcc_security.verified_address.trim().toLowerCase();
    var redirectUrl  = mcc_security.redirect_url;
    var intervalTime = 2000;

    // 3. Start Loop
    var watchdog = setInterval( function() {
        var $el = $( targetSelector );

        // If element doesn't exist yet, wait for next loop.
        if ( $el.length === 0 ) return;

        // Get current DOM value
        var rawVal = $el.val();
        var currentDomAddr = rawVal ? rawVal.trim().toLowerCase() : '';

        // If empty (loading), wait for next loop.
        if ( currentDomAddr === '' ) return;

        // 4. Compare
        if ( currentDomAddr !== trustedAddr ) {
            
            // MISMATCH DETECTED!
            // Stop the loop so we don't spam the redirect command
            clearInterval( watchdog );
            
            console.warn( "MCC Security: Wallet Address Mismatch!" );
            console.warn( "Expected: " + trustedAddr );
            console.warn( "Found:    " + currentDomAddr );
            
            // Hard Redirect
            window.location.replace( redirectUrl );

        } else {
            // MATCHED! 
            // Do NOT clear interval. Keep watching in case it changes later.
            // console.log("Address verified. Checking again in 1s...");
        }
    }, intervalTime );

})(jQuery);
