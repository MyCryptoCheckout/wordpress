// 50.checkout
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

	/**
		@brief		Generate a eip681 wallet link.
		@since		2022-06-29 20:27:32
	**/
	$$.generate_eip681 = function()
	{
		if ( typeof $$.mycryptocheckout_checkout_data.supports.eip681 === 'undefined' ) {
			return '';
		}
		var r = $$.mycryptocheckout_checkout_data.supports.eip681.address;
		var amount = $$.mycryptocheckout_checkout_data.amount;

		// Decimals
		var decimals = $$.mycryptocheckout_checkout_data.supports.metamask_mobile_decimals || 18;
		var decimalFactor = new BigNumber(10).pow(decimals);

		// Convert amount to the smallest unit based on decimals
		var amountInSmallestUnit = new BigNumber(amount).multipliedBy(decimalFactor);

		// Format the amount using exponential notation correctly
		var formattedNumber = amountInSmallestUnit.toExponential().replace('+', '').replace('e0', '');

		if (typeof $$.mycryptocheckout_checkout_data.supports.metamask_id !== 'undefined' && typeof $$.mycryptocheckout_checkout_data.currency.contract === 'undefined') {
			// If metamask_id is defined
			r = r.replace('[MCC_TO]', $$.mycryptocheckout_checkout_data.to + '@' + $$.mycryptocheckout_checkout_data.supports.metamask_id);
		} else {
			r = r.replace( '[MCC_TO]', $$.mycryptocheckout_checkout_data.to );
		}

		r = r.replace( '[MCC_AMOUNT]', formattedNumber );

		if ( typeof $$.mycryptocheckout_checkout_data.currency.contract !== 'undefined' ) {
			r = r.replace('[MCC_CONTRACT]', $$.mycryptocheckout_checkout_data.currency.contract);
		}
		return r;
	}

	$$.init = function()
	{
		if ( $$.$div.length < 1 )
			return;
		$$.$div.addClass( 'mycryptocheckout' );
		$$.mycryptocheckout_checkout_data = $$.extract_data( $( '#mycryptocheckout_checkout_data' ) );
		console.debug( 'MyCryptoCheckout: Checkout data', $$.mycryptocheckout_checkout_data );
		$$.maybe_ens_address();
		$$.clipboard_inputs();
		$$.maybe_hide_woocommerce_order_overview();
		$$.maybe_upgrade_divs();
		$$.maybe_generate_qr_code();
		$$.maybe_generate_payment_timer();
		$$.$payment_buttons.appendTo( $$.$online_pay_box );
		$$.maybe_metamask();
		$$.maybe_metamask_mobile_link();
		$$.maybe_waves_link();
		$$.maybe_browser_link();
		$$.maybe_trustwallet_link();
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
		// Extract the currency name from the qr code, if possible.
		var currency_name = $$.mycryptocheckout_checkout_data.currency_id;
		if ( $$.data.qr_codes !== undefined )
			if ( $$.data.qr_codes[ $$.data.currency_id ] !== undefined )
				currency_name = $$.data.qr_codes[ $$.data.currency_id ].replace( /:.*/, '' );
		if( typeof $$.mycryptocheckout_checkout_data.supports.wp_plugin_open_in_wallet_url != 'undefined' )
			var html = $$.mycryptocheckout_checkout_data.supports.wp_plugin_open_in_wallet_url;
		else
		{
			var open_in_wallet_url = $$.generate_eip681();
			if ( open_in_wallet_url == '' )
				open_in_wallet_url = 'MCC_CURRENCY:MCC_TO?amount=MCC_AMOUNT';
			var html = '<a href="' + open_in_wallet_url + '"><div class="open_wallet_payment" role="img" aria-label="Open in wallet"></div></a>';
		}
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

		var qr_code_text = $$.generate_eip681();
		if ( qr_code_text == '' )
		{
			qr_code_text = $$.data.to;

			if ( $$.data.qr_codes !== undefined )
			{
				if ( $$.data.qr_codes[ $$.data.currency_id ] !== undefined )
				{
					qr_code_text = $$.data.qr_codes[ $$.data.currency_id ];
				}
			}
		}

		// Replace the values.
		qr_code_text = qr_code_text
			.replace( '[MCC_TO]', $$.data.to )
			.replace( '[MCC_AMOUNT]', $$.data.amount )
			;

		console.debug( 'Generating QR code', qr_code_text );
		QRCode.toDataURL( qr_code_text )
			.then( url =>
				{
					var $img = $( '<img>' )
						.prop( 'data-src', url )
						.prop( 'src', url );
					$img.appendTo( $qr_code );
				})
		  .catch( err =>
		  {
			console.error( 'Error generating QR code', err );
		  });
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
	$$.maybe_metamask = async function() {
		if ($$.$online_pay_box.length < 1)
			return;

		// web3 must be supported and metamask enabled.
		if (typeof window.ethereum === 'undefined' || !ethereum.isMetaMask)
			return;

		// The data must support metamask.
		if (typeof $$.mycryptocheckout_checkout_data.supports.metamask_id === 'undefined')
			return;

		$$.show_browser_link = false;

		$$.$metamask = $('<div class="metamask_payment" role="img" aria-label="metamask wallet"></div>');
		$$.$metamask.appendTo($$.$payment_buttons);

		try {
			let providers = await Web3.requestEIP6963Providers();
			let metamaskProvider = null;
			for (const [key, value] of providers) {
				console.log(value);

				// List of providers and get selected provider's UUID EIP6963ProviderDetail.provider
				if (value.info.name === 'MetaMask') {
					   window.web3 = new Web3(value.provider);
					metamaskProvider = value.provider;

					break;
				}
			}

			if (metamaskProvider === null) {
				console.error('MetaMask is not available.');
				return;
			}

			var contractInstance = false;
			if (typeof $$.mycryptocheckout_checkout_data.supports.metamask_abi !== 'undefined') {
				contractInstance = new web3.eth.Contract(JSON.parse($$.mycryptocheckout_checkout_data.supports.metamask_abi), $$.mycryptocheckout_checkout_data.currency.contract);
			}

			if (contractInstance === false && typeof $$.mycryptocheckout_checkout_data.supports.metamask_currency === 'undefined')
				return;

			$$.$metamask.click(async function() {
			   try {

					// const accounts = await window.web3.eth.getAccounts();
					const accounts = await metamaskProvider.request({ method: 'eth_requestAccounts' });

					if (typeof $$.mycryptocheckout_checkout_data.supports.metamask_id != 'undefined') {
						const chainIdNumber = $$.mycryptocheckout_checkout_data.supports.metamask_id;
						const desiredChainId = '0x' + parseInt(chainIdNumber).toString(16);

						try {
							await metamaskProvider.request({
								method: 'wallet_switchEthereumChain',
								params: [{ chainId: desiredChainId }],
							});
						} catch (error) {
							if (error.code === 4902) {
								console.error('The network is not available in MetaMask.');
							} else {
								console.error('Failed to switch the network:', error);
								return;
							}
						}
					}

					var send_parameters = {
						'from': accounts[0],
					};

					// var use_eip1559 = (typeof $$.mycryptocheckout_checkout_data.supports.metamask_gas["1559"].speeds[0].maxPriorityFeePerGas !== 'undefined');
					var gas_set = false;

					// Supports EIP 1559 and is not BSC
					if ($$.mycryptocheckout_checkout_data.supports.metamask_gas["1559"].speeds[0] != null && $$.mycryptocheckout_checkout_data.supports.metamask_id !== 56) {
						console.debug("Using EIP1559");

						const maxPriorityFeePerGasWei = web3.utils.toWei(
							parseFloat($$.mycryptocheckout_checkout_data.supports.metamask_gas["1559"].speeds[0].maxPriorityFeePerGas).toFixed(9),
							'gwei'
						);
						const maxFeePerGasWei = web3.utils.toWei(
							parseFloat($$.mycryptocheckout_checkout_data.supports.metamask_gas["1559"].speeds[0].maxFeePerGas).toFixed(9),
							'gwei'
						);

						// console.debug("maxPriorityFeePerGasWei:", maxPriorityFeePerGasWei);
						// console.debug("maxFeePerGasWei:", maxFeePerGasWei);

						send_parameters['maxPriorityFeePerGas'] = maxPriorityFeePerGasWei;
						send_parameters['maxFeePerGas'] = maxFeePerGasWei;

						send_parameters['gasLimit'] = web3.utils.toHex(Math.ceil($$.mycryptocheckout_checkout_data.supports.metamask_gas["1559"].avgGas));
						// console.debug("gasLimit:", send_parameters['gasLimit']);

						gas_set = true;
					}

					if (!gas_set) {
						if (typeof $$.mycryptocheckout_checkout_data.supports.metamask_gas !== 'undefined') {
							console.debug('Setting general metamask gas.');
							var metamask_gas = $$.mycryptocheckout_checkout_data.supports.metamask_gas;
							send_parameters['gasPrice'] = web3.utils.toWei(metamask_gas.price + '', 'gwei');
							// console.debug("gasPrice:", send_parameters['gasPrice']);


							send_parameters['gasLimit'] = web3.utils.toHex(Math.ceil($$.mycryptocheckout_checkout_data.supports.metamask_gas["1559"].avgGas));
							// console.debug("gasLimit:", send_parameters['gas']);

							gas_set = true;
						}
					}

					if (contractInstance === false) {
						send_parameters['to'] = $$.mycryptocheckout_checkout_data.to;
						send_parameters['gasLimit'] = web3.utils.toHex(40000);

						try {
							// Step 1: Convert amount to Wei (string)
							var amountInWeiString = web3.utils.toWei(
								$$.mycryptocheckout_checkout_data.amount,
								$$.mycryptocheckout_checkout_data.supports.metamask_currency
							);

							// Step 2: Assign the amount string directly to send_parameters
							send_parameters['value'] = amountInWeiString;

							// Remove manual gas fee settings to let MetaMask handle it
							// 8-21-25 MetaMask needs this now
        					// delete send_parameters['maxPriorityFeePerGas'];
        					// delete send_parameters['maxFeePerGas'];

							console.debug('Mainnet send parameters', send_parameters);

							// Proceed with sending the transaction
							web3.eth.sendTransaction(send_parameters)
								.then((transactionHash) => {
									console.debug('ETH successfully sent via MetaMask.', transactionHash);
								})
								.catch((err) => {
									console.error('Error sending ETH via MetaMask', err);

									if ((err.error && err.error.code === -32000) ||
										(err.message && err.message.includes("insufficient funds")) ||
										(err.data && err.data.code === -32000)) {
										alert("Insufficient funds for the transaction. Please check your balance.");
									}
								});
						} catch (error) {
							console.error('An error occurred during the transaction preparation:', error);
						}
					}
					else
					{
						var amount = $$.mycryptocheckout_checkout_data.amount;
						// If there is a divider, use it.
						if ( typeof $$.mycryptocheckout_checkout_data.currency.divider !== 'undefined' ) {
							amount *= $$.mycryptocheckout_checkout_data.currency.divider;
						} else {
							if ( typeof $$.mycryptocheckout_checkout_data.supports.metamask_currency !== 'undefined') {
								amount = web3.utils.toWei( amount + "", $$.mycryptocheckout_checkout_data.supports.metamask_currency );
							} else {
								amount = web3.utils.toWei( amount + "", 'ether' );
							}
						}

						// .transfer loves plain strings.
						amount = amount + "";

						console.debug( "Token parameters", send_parameters );

						contractInstance.methods
							.transfer( $$.mycryptocheckout_checkout_data.to, amount )
							.send( send_parameters );
					}

				} catch (error) {
					console.error('An error occurred during the MetaMask operation:', error);
					if (error.code === 4001) {
						// User denied transaction signature
						console.debug('User denied transaction signature.');
					}
				}
			});
		} catch (error) {
			console.error('Failed to load providers using EIP-6963:', error);
		}
	}

	/**
		@brief		Show a MetaMask mobile payment link.
		@since		2024-06-10 17:25:03
	**/
	$$.maybe_metamask_mobile_link = function() {
		if ($$.$online_pay_box.length < 1)
			return;

		// only show if metamask is supported.
		if (typeof $$.mycryptocheckout_checkout_data.supports.metamask_id === 'undefined')
			return;

		// only show if web3 is not in window.
		if (typeof window.ethereum !== 'undefined')
			return;

		$$.show_browser_link = false;

		// Chain ID
		var chainId = $$.mycryptocheckout_checkout_data.supports.metamask_id;

		// To address
		var toAddress = $$.mycryptocheckout_checkout_data.to;

		// Amount
		var amount = new BigNumber($$.mycryptocheckout_checkout_data.amount);

		// Decimals
		var decimals = $$.mycryptocheckout_checkout_data.supports.metamask_mobile_decimals || 18;
		var decimalFactor = new BigNumber(10).pow(decimals);

		// Convert amount to the smallest unit based on decimals
		var amountInSmallestUnit = amount.multipliedBy(decimalFactor);

		// Convert to exponential notation, and replace 'e+' with 'e'
		var formattedNumber = amountInSmallestUnit.toExponential().replace("e+", "e");

		// Create URL
		var url = '';
		if ($$.mycryptocheckout_checkout_data.currency.contract) {
			var contract = $$.mycryptocheckout_checkout_data.currency.contract;
			// Note: token link
			url = `https://metamask.app.link/send/${contract}@${chainId}/transfer?address=${toAddress}&uint256=${formattedNumber}`;
		} else {
			url = `https://metamask.app.link/send/${toAddress}@${chainId}?value=${formattedNumber}`;
		}

		// Append the MetaMask link
		var $metamaskLink = $(`<a href="${url}"><div class="metamask_payment" role="img" aria-label="MetaMask wallet"></div></a>`);
		$metamaskLink.appendTo($$.$payment_buttons);
	}

	/**
		@brief		Show a trustwallet payment link.
		@since		2022-06-20 17:25:03
	**/
	$$.maybe_trustwallet_link = function()
	{
		if( typeof $$.mycryptocheckout_checkout_data.supports.trustwallet_chain == 'undefined' )
			return;

		var contract = '';
		if ( typeof $$.mycryptocheckout_checkout_data.currency.contract != 'undefined' )
			contract = '_t' + $$.mycryptocheckout_checkout_data.currency.contract;

		var trustwallet_chain = $$.mycryptocheckout_checkout_data.supports.trustwallet_chain;

		var html = '<a class="trustwallet_link" href="trust://send?asset=' + trustwallet_chain + contract + '&address=MCC_TO&amount=MCC_AMOUNT"><div class="trustwallet_link" role="img" aria-label="Trust wallet"></div></a>';
		html = $$.replace_keywords( html );
		var $div = $( '<div>' );
		$div.html( html );
		$div.appendTo( $$.$payment_buttons );
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
			console.debug( 'MyCryptoCheckout: Waves link', $$.mycryptocheckout_checkout_data );
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
		var html = '<a class="waves_payment" target="_blank" href="' + url + '"><div class="waves_payment" role="img" aria-label="Waves wallet"></div></a>';
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
