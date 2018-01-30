jQuery( document ).ready( function( $ )
{
	var clipboard_svg = 'PHN2ZyBoZWlnaHQ9IjEwMjQiIHdpZHRoPSI4OTYiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+DQo8cGF0aCBkPSJNMTI4IDc2OGgyNTZ2NjRIMTI4di02NHogbTMyMC0zODRIMTI4djY0aDMyMHYtNjR6IG0xMjggMTkyVjQ0OEwzODQgNjQwbDE5MiAxOTJWNzA0aDMyMFY1NzZINTc2eiBtLTI4OC02NEgxMjh2NjRoMTYwdi02NHpNMTI4IDcwNGgxNjB2LTY0SDEyOHY2NHogbTU3NiA2NGg2NHYxMjhjLTEgMTgtNyAzMy0xOSA0NXMtMjcgMTgtNDUgMTlINjRjLTM1IDAtNjQtMjktNjQtNjRWMTkyYzAtMzUgMjktNjQgNjQtNjRoMTkyQzI1NiA1NyAzMTMgMCAzODQgMHMxMjggNTcgMTI4IDEyOGgxOTJjMzUgMCA2NCAyOSA2NCA2NHYzMjBoLTY0VjMyMEg2NHY1NzZoNjQwVjc2OHpNMTI4IDI1Nmg1MTJjMC0zNS0yOS02NC02NC02NGgtNjRjLTM1IDAtNjQtMjktNjQtNjRzLTI5LTY0LTY0LTY0LTY0IDI5LTY0IDY0LTI5IDY0LTY0IDY0aC02NGMtMzUgMC02NCAyOS02NCA2NHoiIC8+DQo8L3N2Zz4NCg==';

	// On the purchase confirmation page, convert the amount and address to a copyable input.
	$.each( $( '.mcc.online_payment_instructions .to_input' ), function( index, item )
	{
		var $item = $( item );

		// How big should the input be?
		var text = $item.html();
		var length = text.length;
		// Create an input.
		var $input = $( '<input readonly="readonly">' );
		$input.attr( 'size', length );
		$input.attr( 'value', text );

		// Add a clipboard image.
		var $clipboard = $( '<img src="data:image/svg+xml;base64,' + clipboard_svg + '" />' );
		$clipboard.attr( 'title', 'Copy to clipboard' );
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

		$clipboard.appendTo( $item );
	} );

	// Maybe hide the purchase details
	var $hide_woocommerce_order_overview = $( '.hide_woocommerce_order_overview' );
	if ( $hide_woocommerce_order_overview.length > 0 )
		$( '.woocommerce-order-overview' ).hide();
} );