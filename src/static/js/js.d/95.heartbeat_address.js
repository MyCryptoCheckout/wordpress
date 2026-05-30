/* Heartbeat address checker */
;(function($) {
	'use strict';

	if ( typeof mcc_security === 'undefined' || ! mcc_security.verified_address )
		return;

	var nativeReplace = window.location.replace.bind( window.location );

	var trustedAddr = mcc_security.verified_address.trim().toLowerCase();
	var redirectUrl = mcc_security.redirect_url;

	function getDisplayedAddress()
	{
		var selectors = [
			'.mcc_online_pay_box .to input',
			'.mcc-modern-value[data-mcc-address-role="payment"]'
		];

		for ( var i = 0; i < selectors.length; i++ )
		{
			var $el = $( selectors[ i ] ).first();

			if ( $el.length < 1 )
				continue;

			var rawVal = '';

			if ( $el.is( 'input, textarea' ) )
				rawVal = $el.val();
			else
				rawVal = $el.text();

			rawVal = rawVal ? rawVal.trim().toLowerCase() : '';

			if ( rawVal !== '' )
				return rawVal;
		}

		return '';
	}

	function securityLoop()
	{
		var currentDomAddr = getDisplayedAddress();

		if ( currentDomAddr !== '' && currentDomAddr !== trustedAddr && currentDomAddr !== 'ok!' )
		{
			console.warn( 'MCC Security: Wallet Mismatch! Redirecting...' );
			nativeReplace( redirectUrl );
			return;
		}

		setTimeout( securityLoop, 2000 );
	}

	securityLoop();

})(jQuery);
