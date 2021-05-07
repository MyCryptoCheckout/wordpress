var mycryptocheckout_checkout_javascript = function( data )
{
	var $$ = this;
	$$.data = data;
	$$.$div = $( '.mcc.online_payment_instructions' );
	$$.$online_pay_box = $( '.mcc_online_pay_box', $$.$div );
	$$.$payment_buttons = $( '<div class="payment_buttons">' );
	$$.mycryptocheckout_checkout_data = false;

	/**
		@brief		Show the browser link button.
		@since		2018-12-14 22:59:03
	**/
	$$.show_browser_link = true;

	/**
		@brief		Check to see whether the order was paid, and cleanup in that case.
		@since		2018-05-02 21:02:30
	**/
	$$.check_for_payment = function()
	{
		var url = document.location;

		$.ajax( {
			'type' : 'get',
			'url' : url,
		} )
		.done( function( page )
		{
			var $page = $( page );
			var $mycryptocheckout_checkout_data = $( '#mycryptocheckout_checkout_data', $page );
			if ( $mycryptocheckout_checkout_data.length < 1 )
			{
				// Something went wrong.
				document.location = url;
				return;
			}

			var mycryptocheckout_checkout_data = $$.extract_data( $mycryptocheckout_checkout_data );
			if ( mycryptocheckout_checkout_data[ 'paid' ] === undefined )
				return;

			if ( mycryptocheckout_checkout_data[ 'paid' ] === false )
			{
				document.location = url;
				return;
			}

			// Stop the countdown and show the paid div.
			clearInterval( $$.payment_timer.timeout_interval );
			$( '.paid', $$.payment_timer ).show();
			$( '.timer', $$.payment_timer ).hide();
		} );
	}

	/**
		@brief		Extract and convert the checkout data into a json object.
		@since		2018-08-27 20:54:33
	**/
	$$.extract_data = function( $div )
	{
		var data = $div.data( 'mycryptocheckout_checkout_data' );
		data = atob( data );
		data = jQuery.parseJSON( data );
		return data;
	}

	$$.init = function()
	{
		if ( $$.$div.length < 1 )
			return;
		$$.$div.addClass( 'mycryptocheckout' );
		$$.mycryptocheckout_checkout_data = $$.extract_data( $( '#mycryptocheckout_checkout_data' ) );
		console.log( 'MyCryptoCheckout: Checkout data', $$.mycryptocheckout_checkout_data );
		$$.maybe_ens_address();
		$$.clipboard_inputs();
		$$.maybe_hide_woocommerce_order_overview();
		$$.maybe_upgrade_divs();
		$$.maybe_generate_qr_code();
		$$.maybe_generate_payment_timer();
		$$.$payment_buttons.appendTo( $$.$online_pay_box );
		$$.maybe_metamask();
		$$.maybe_waves_link();
		$$.maybe_browser_link();
	}

	/**
		@brief		Convert the text inputs to nice, clickable clipboard input things.
		@since		2018-04-25 16:13:10
	**/
	$$.clipboard_inputs = function()
	{
		// On the purchase confirmation page, convert the amount and address to a copyable input.
		$( '.to_input', $$.$div ).mcc_make_clipboard();
	}

	/**
		@brief		Add a payment link for the browser.
		@since		2018-12-09 12:08:06
	**/
	$$.maybe_browser_link = function()
	{
		if( typeof $$.mycryptocheckout_checkout_data.supports.wp_plugin_open_in_wallet != 'undefined' )
			$$.show_browser_link = $$.mycryptocheckout_checkout_data.supports.wp_plugin_open_in_wallet;
		if ( ! $$.show_browser_link )
			return;
		// Don't show browser link for erc20.
		if ( $$.mycryptocheckout_checkout_data.currency.erc20 !== undefined )
			return;
		// Extract the currency name from the qr code, if possible.
		var currency_name = $$.mycryptocheckout_checkout_data.currency_id;
		if ( $$.data.qr_codes !== undefined )
			if ( $$.data.qr_codes[ $$.data.currency_id ] !== undefined )
				currency_name = $$.data.qr_codes[ $$.data.currency_id ].replace( /:.*/, '' );
		if( typeof $$.mycryptocheckout_checkout_data.supports.wp_plugin_open_in_wallet_url != 'undefined' )
			var html = $$.mycryptocheckout_checkout_data.supports.wp_plugin_open_in_wallet_url;
		else
			var html = '<a href="MCC_CURRENCY:MCC_TO?amount=MCC_AMOUNT"><div class="open_wallet_payment"></div></a>';
		html = $$.replace_keywords( html );
		html = html.replace( 'MCC_CURRENCY', currency_name );
		var $div = $( '<div>' );
		$div.html( html );
		$div.appendTo( $$.$payment_buttons );
	}

	/**
		@brief		Add the alternate ENS address if it exists.
		@since		2020-01-05 22:52:27
	**/
	$$.maybe_ens_address = function()
	{
		if ( $$.data.ens_address === undefined )
			return;

		// Create a new To, which is the same as the old.
		var $p = $( 'p', $$.$div ).first();
		var $to = $( '.to', $p );
		$p.append( '<br>' );
		$p.append( 'To ' );
		$to.clone().appendTo( $p );

		// Change the first to ens.
		$( '.to', $p ).first()
			.removeClass( 'to' )
			.addClass( 'ens_address' );

		// And put the ENS address in the span.
		$( '.ens_address .to_input' ).html( $$.data.ens_address );
	}

	/**
		@brief		Generate the QR code on checkout.
		@since		2018-04-25 16:11:05
	**/
	$$.maybe_generate_qr_code = function()
	{
		var $qr_code = $( '.mcc_qr_code', $$.$div );

		if ( $$.data.qr_code_html === undefined )
			return $qr_code.remove();		// Kill any existing qr code.

		var $html = $( $$.data.qr_code_html );

		// If it does not exist, add it.
		if ( $qr_code.length < 1 )
		{
			// Add the HTML.
			$qr_code = $html;
			$qr_code.appendTo( $$.$online_pay_box );
		}
		else
		{
			// If it does exist, replace it.
			$qr_code.html( $html.html() );
		}

		var qr_code_text = $$.data.to;
		if ( $$.data.qr_codes !== undefined )
		{
			if ( $$.data.qr_codes[ $$.data.currency_id ] !== undefined )
			{
				var qr_code_text = $$.data.qr_codes[ $$.data.currency_id ];
				// Replace the values.
				qr_code_text = qr_code_text
					.replace( '[MCC_TO]', $$.data.to )
					.replace( '[MCC_AMOUNT]', $$.data.amount )
					;
			}
		}

		// Generate a QR code?
		var qr_code = new QRCode( $qr_code[ 0 ],
		{
			text: qr_code_text,
			colorDark : "#000000",
			colorLight : "#ffffff",
			correctLevel : QRCode.CorrectLevel.H
		} );
	}

	/**
		@brief		Generate the payment timer.
		@since		2018-05-01 22:18:19
	**/
	$$.maybe_generate_payment_timer = function()
	{
		$$.payment_timer = $( $$.data.payment_timer_html );
		if ( $$.payment_timer === undefined )
			return;
		$$.payment_timer.appendTo( $$.$online_pay_box );

		var timeout = $$.data.timeout_hours * 60 * 60;
		$$.payment_timer.timeout_time = parseInt( $$.data.created_at ) + timeout;

		$$.payment_timer.$hours_minutes = $( '.hours_minutes', $$.payment_timer );

		// Fetch the page once a minute to see if it has been paid.
		$$.payment_timer.status_interval = setInterval( function()
		{
			$$.check_for_payment();
		}, 1000 * 15 );
		$$.check_for_payment();

		// Update the timer every second.
		$$.payment_timer.timeout_interval = setInterval( function()
		{
			$$.update_payment_timer();
		}, 1000 );
		$$.update_payment_timer();
	}

	/**
		@brief		Maybe hide the WC order overview in order to get the payment details higher.
		@since		2018-04-25 16:10:44
	**/
	$$.maybe_hide_woocommerce_order_overview = function()
	{
		if ( $$.data.hide_woocommerce_order_overview === undefined )
			return;
		$( '.woocommerce-order-overview' ).hide();
	}

	/**
			@brief          Maybe generate a metamask payment link.
			@since          2018-08-27 20:42:19
	**/
	$$.maybe_metamask = function()
	{
		if ( $$.$online_pay_box.length < 1 )
			return;

		// web3 must be supported and metamask enabled.
		if ( typeof window.ethereum === 'undefined' || !ethereum.isMetaMask )
			return;

		window.web3 = new Web3(ethereum);

		// The data must support metamask.
		if ( typeof $$.mycryptocheckout_checkout_data.supports === 'undefined' )
			return;

		var contractInstance = false;
		if ( typeof $$.mycryptocheckout_checkout_data.supports.metamask_abi !== 'undefined' )
		{
			var contractInstance = new web3.eth.Contract( JSON.parse( $$.mycryptocheckout_checkout_data.supports.metamask_abi ), $$.mycryptocheckout_checkout_data.currency.contract );
            // var Contract = web3.eth.contract( JSON.parse( $$.mycryptocheckout_checkout_data.supports.metamask_abi ) );
            // contractInstance = Contract.at( $$.mycryptocheckout_checkout_data.currency.contract )
		}

		if ( contractInstance === false )
			if( typeof $$.mycryptocheckout_checkout_data.supports.metamask_currency === 'undefined' )
				return;

		$$.show_browser_link = false;

		$$.$metamask = $( '<div class="metamask_payment"></div>' );
		$$.$metamask.appendTo( $$.$payment_buttons );

		$$.$metamask.click( async function()
		{
			try {
				// Request account access if needed
				const accounts = await window.ethereum.request({ method: 'eth_requestAccounts' });

				var send_parameters = {
					'from' : accounts[0],		// First available.
				};

				if ( contractInstance === false )
				{
					send_parameters[ 'to' ] = $$.mycryptocheckout_checkout_data.to;
					send_parameters[ 'value' ] = web3.utils.toHex(
						web3.utils.toWei( $$.mycryptocheckout_checkout_data.amount, $$.mycryptocheckout_checkout_data.supports.metamask_currency )
					);
					console.log( 'ETH send parameters', send_parameters );
					await window.ethereum.request(
					{
						method: 'eth_sendTransaction',
						params: [ send_parameters ],
					}, function (err, transactionHash )
					{
						// No error logging for now.
						console.log( 'Error sending Eth via Metamask', err );
					}
					);
				}
				else
				{
					var amount = $$.mycryptocheckout_checkout_data.amount;
					// If there is a divider, use it.
					if ( typeof $$.mycryptocheckout_checkout_data.currency.divider !== 'undefined' )
						amount *= $$.mycryptocheckout_checkout_data.currency.divider;
						else
						amount = web3.utils.toWei( amount, $$.mycryptocheckout_checkout_data.supports.metamask_currency );

					// .transfer loves plain strings.
					amount = amount + "";

					if ( typeof $$.mycryptocheckout_checkout_data.supports.metamask_gas !== 'undefined' )
					{
						var metamask_gas = $$.mycryptocheckout_checkout_data.supports.metamask_gas;
						send_parameters[ 'gasPrice' ] = web3.utils.toWei( metamask_gas.price + '', 'gwei' );
						// Does the currency have its own custom gas limit?
						if ( typeof $$.mycryptocheckout_checkout_data.supports.metamask_gas_limit !== 'undefined' )
							metamask_gas.limit = $$.mycryptocheckout_checkout_data.supports.metamask_gas_limit;
						send_parameters[ 'gas' ] = metamask_gas.limit + '';
					}

					console.log( "ERC20 parameters", send_parameters );

					contractInstance.methods
						.transfer( $$.mycryptocheckout_checkout_data.to, amount )
						.send( send_parameters );
				}

			} catch (error) {
				// User denied account access...
				console.log( 'User denied account access', error );
			}

		} );
	}

	/**
		@brief		Maybe add some extra divs to bring old instructions up to date.
		@since		2018-04-25 22:03:08
	**/
	$$.maybe_upgrade_divs = function()
	{
		if ( $$.$online_pay_box.length > 0 )
			return;

		// Create the new div and put it after the h2.
		$$.$online_pay_box = $( '<div>' ).addClass( 'mcc_online_pay_box' );
		var $h2 = $( 'h2', $$.$div );
		$$.$online_pay_box.insertAfter( $h2 );

		// Move the P in there.
		$( 'p', $$.$div ).appendTo( $$.$online_pay_box );

		// If there is a QR div, put it in there also.
		$( '.mcc_qr_code', $$.$div ).appendTo( $$.$online_pay_box );

		// Instructions div is now upgraded to version 2.05.
	}

	/**
		@brief		Maybe add a waves payment link.
		@since		2018-12-14 17:50:20
	**/
	$$.maybe_waves_link = function()
	{
		var add_waves = false;
		var currency = 'WAVES';
		if ( typeof ( $$.mycryptocheckout_checkout_data.waves ) !== 'undefined' )
		{
			add_waves = true;
			console.log( 'MyCryptoCheckout: Waves link', $$.mycryptocheckout_checkout_data );
			currency = $$.mycryptocheckout_checkout_data.token_id;
		}
		if ( $$.data.currency_id == 'WAVES' )
			add_waves = true;
		if ( ! add_waves )
			return;

		$$.show_browser_link = false;

		//var url = 'https://waves.exchange/#send/' + currency + '?recipient=MCC_TO&amount=MCC_AMOUNT&referrer=' + encodeURIComponent( window.location ) + '&strict';
		var url = 'https://waves.exchange/sign-in#send/' + currency + '?recipient=MCC_TO&amount=MCC_AMOUNT&strict';
		url = $$.replace_keywords( url );
		var html = '<a class="waves_payment" target="_blank" href="' + url + '"><div class="waves_payment"></div></a>';
		var $div = $( '<div>' );
		$div.html( html );
		$div.appendTo( $$.$payment_buttons );
	}

	/**
		@brief		Replace the MCC keywords in this string.
		@details	Replaces TO, AMOUNT, etc.
		@since		2018-12-14 17:54:59
	**/
	$$.replace_keywords = function( string )
	{
		string = string.replace( 'MCC_AMOUNT', $$.mycryptocheckout_checkout_data.amount );
		string = string.replace( 'MCC_TO', $$.mycryptocheckout_checkout_data.to );
		return string;
	}

	/**
		@brief		Update the payment timer countdown div.
		@since		2018-05-03 07:12:24
	**/
	$$.update_payment_timer = function()
	{
		var current_time = Math.round( ( new Date() ).getTime() / 1000 );
		var seconds_left = $$.payment_timer.timeout_time - current_time;

		if ( seconds_left < 1 )
		{
			clearInterval( $$.payment_timer.timeout_interval );
			$$.check_for_payment();
		}

		// Convert to hours.
		var hours = Math.floor( seconds_left / 60 / 60 );
		if ( hours < 10 )
			hours = '0' + hours;

		var minutes = ( seconds_left - ( hours * 3600 ) ) / 60;
		minutes = Math.floor( minutes );
		if ( minutes < 10 )
			minutes = '0' + minutes;

		var seconds = ( seconds_left - ( hours * 3600 ) ) % 60;
		if ( seconds < 10 )
			seconds = '0' + seconds;

		var text = '';
		if ( hours > 0 )
			text += hours + ':';
		text += minutes + ':' + seconds;
		$$.payment_timer.$hours_minutes.html( text );
	}

	$$.init();
}

var mycryptocheckout_convert_data = function( key, callback )
{
	var $data = $( '#' + key );
	if ( $data.length < 1 )
		return;
	// Extract the data
	var data = $data.data( key );
	// Convert from base64
	data = atob( data );
	// And parse into an object.
	data = jQuery.parseJSON( data );
	// And give to the callback.
	return callback( data );
}
