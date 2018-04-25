jQuery( document ).ready( function( $ )
{
	var mycryptocheckout_checkout_javascript = function()
	{
		var $$ = this;
		$$.data = mycryptocheckout_checkout_data;
		$$.$div = $( '.mcc.online_payment_instructions' );
		$$.$online_pay_box = $( '.mcc_online_pay_box', $$.$div );

		$$.init = function()
		{
			if ( $$.$div.length < 1 )
				return;
			$$.clipboard_inputs();
			$$.maybe_hide_woocommerce_order_overview();
			$$.maybe_generate_qr_code();
			$$.maybe_upgrade_divs();
		}

		/**
			@brief		Convert the text inputs to nice, clickable clipboard input things.
			@since		2018-04-25 16:13:10
		**/
		$$.clipboard_inputs = function()
		{
			// On the purchase confirmation page, convert the amount and address to a copyable input.
			$.each( $( '.to_input', $$.$div ), function( index, item )
			{
				var $item = $( item );
				$item.addClass( 'clipboardable' );

				// How big should the input be?
				var text = $item.html();
				var length = text.length;
				// Create an input.
				var $input = $( '<input readonly="readonly">' );
				// Add a clipboard image to each input.
				$input.attr( 'size', length );
				$input.attr( 'value', text );

				// Make a clipboard input that hides above the clipboard.
				var $clipboard = $( '<span class="mcc_woocommerce_clipboard">' );

				$clipboard.click( function()
				{
					var old_value = $input.attr( 'value' );
					var new_value = old_value.replace( / .*/, '' );

					// Create an invisible input just to copy the value.
					var $temp_input = $( '<input value="' + new_value + '" />' );
					$temp_input.css( {
						'position' : 'absolute',
						'left' : '-1000000px',
						'top' : '-1000000px',
					} );
					$temp_input.appendTo( $item );
					$temp_input.attr( 'value', new_value );
					$temp_input.select();
					document.execCommand( "copy" );

					$input.attr( 'value', 'Copied!' );
					setTimeout( function()
					{
						$input.attr( 'value', old_value );
						$input.select();
					}, 1500 );
				} );

				$item.html( $input );

				// Add the clipboard to the item that now contains the new input.
				$clipboard.appendTo( $item );

				// Adjust the size and position of the invisible clipboard div to match the input.
				var input_height = $input.outerHeight();
				$clipboard.css( {
					'height' : input_height,
					'width' : input_height,
					'top' : - ( $input.outerHeight() - $item.outerHeight() ) / 2,
				} );
			} );
		}

		/**
			@brief		Generate the QR code on checkout.
			@since		2018-04-25 16:11:05
		**/
		$$.maybe_generate_qr_code = function()
		{
			// Generate a QR code?
			var $qr_code = $( '.mcc_qr_code', $$.$div );
			if ( $qr_code.length > 0 )
			{
				var to = $qr_code.data( 'to' );
				var qr_code = new QRCode( $qr_code[ 0 ],
				{
					text: to,
					colorDark : "#000000",
					colorLight : "#ffffff",
					correctLevel : QRCode.CorrectLevel.H
				} );
			}
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

			// Instructions div is now upgraded!
		}

		$$.init();
	}
	mycryptocheckout_checkout_javascript();
} );
