jQuery( document ).ready( function( $ )
{
	// On the purchase confirmation page, convert the amount and address to a copyable input.
	$.each( $( '.mcc.online_payment_instructions .to_input' ), function( index, item )
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

	// Maybe hide the purchase details
	var $hide_woocommerce_order_overview = $( '.hide_woocommerce_order_overview' );
	if ( $hide_woocommerce_order_overview.length > 0 )
		$( '.woocommerce-order-overview' ).hide();

	// Generate a QR code?
	var $qr_code = $( '.mcc.online_payment_instructions .mcc_qr_code' );
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
} );
