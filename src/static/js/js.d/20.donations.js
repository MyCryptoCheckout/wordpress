/**
	@brief		Handle the donations div.
	@since		2018-05-12 21:24:33
**/
;(function( $ )
{
    $.fn.extend(
    {
        mycryptocheckout_donations_javascript: function()
        {
            return this.each( function()
            {
                var $$ = $(this);
                /**
                	@brief		The data object for this div.
                	@since		2018-05-12 21:59:10
                **/
                $$.div_data = null;

                $$.address = $( '.address', $$ );
                $$.currency_selector = $( '.currency_selector select', $$ );

                /**
                	@brief		Extract and convert the data json.
                	@since		2018-05-12 21:57:55
                **/
                $$.extract_data = function()
                {
                	var data = $$.data( 'mycryptocheckout_donations_data' );
					// Convert from base64
					data = atob( data );
					// And parse into an object.
					$$.div_data = jQuery.parseJSON( data );
                }

                /**
                	@brief		Init this donations div.
                	@since		2018-05-12 21:57:37
                **/
                $$.init = function()
                {
                	$$.extract_data();
                	$$.init_icons();
                	$$.init_currency_selector();
                	// Set the first available currency.
                	var primary_currency = $$.div_data[ 'primary_currency' ];
                	$$.set_currency_id( primary_currency );
                	$$.addClass( 'alignment_' + $$.div_data[ 'alignment' ] );
                }

                /**
                	@brief		Init the currency selector, if any.
                	@since		2018-05-12 22:02:52
                **/
                $$.init_currency_selector = function()
                {
                	if ( $$.currency_selector.length < 1 )
                		return;
                	// And the settings must say that we use the selector.
                	if ( $$.div_data[ 'show_currencies_as_select' ] != '1' )
                		return;
                	// Put all of the currencies in the selector.
                	$.each( $$.div_data[ 'currencies' ], function( index, currency )
                	{
                		var $option = $( '<option>' );
                		$option.html( currency.currency_name );
                		$option.attr( 'value', currency.currency_id );
                		$option.appendTo( $$.currency_selector );
                	} );
                	$$.currency_selector.change( function()
                	{
                		// Get the currency ID.
                		var currency_id = $$.currency_selector.val();
                		var currency = $$.div_data.currencies[ currency_id ];
                		var address = currency[ 'address' ];
                		// Show the address for this currency in the address field.
                		$$.set_currency_id( currency_id );
                	} ).change();
                	$( '.currency_selector', $$ ).show();
                }

                $$.init_icons = function()
                {
                	$$.icons = $( '.currency_icons', $$ );
                	// The div must exist.
                	if ( $$.icons.length < 1 )
                		return;
                	// And the settings must say that we use icons.
                	if ( $$.div_data[ 'show_currencies_as_icons' ] != '1' )
                		return;
                	$.each( $$.div_data[ 'currencies' ], function( index, currency )
                	{
                		var $icon = $( '<img>' );
                		$icon.addClass( 'mcc_donation_icon' );
                		$icon.attr( 'src', currency.icon );
                		$icon.appendTo( $$.icons );

                		// Make the icon clickable.
                		$icon.click( function()
                		{
							$$.set_currency_id( currency.currency_id );
                		} );
                	} );
                	$$.icons.show();
                }

                /**
                	@brief		Show a qr code with this address.
                	@since		2018-05-12 22:11:28
                **/
                $$.qr_code = function( address )
                {
                	if ( $$.div_data[ 'qr_code_enabled' ] != '1' )
            			return;
            		var $qr_code = $( '.qr_code', $$ );
            		// Set the div size.
            		var width = $$.div_data[ 'qr_code_max_width' ];
            		$qr_code.css( {
            			'height' : 'auto',
            			'max-width' : width,
            		} );
            		$qr_code.html( '' );
					var qr_code = new QRCode( $qr_code[ 0 ],
					{
						text: address,
						colorDark : "#000000",
						colorLight : "#ffffff",
						correctLevel : QRCode.CorrectLevel.H,
						'height' : width,
						'width' : width,
					} );
            		$qr_code.show();
                }

                /**
                	@brief		Convenience method to set the address everywhere.
                	@since		2018-05-12 22:43:48
                **/
                $$.set_currency_id = function( currency_id )
                {
                	var address = $$.div_data[ 'currencies' ][ currency_id ][ 'address' ];
                	$$.currency_selector.val( currency_id );
					$$.show_address( address );
                	$$.show_currency_name( $$.div_data[ 'currencies' ][ currency_id ][ 'currency_name' ] );
					$$.qr_code( address );
                }

                /**
                	@brief		Show the address.
                	@since		2018-05-12 22:44:05
                **/
                $$.show_address = function( address )
                {
                	if ( $$.div_data[ 'show_address' ] != '1' )
            			return;
            		$$.address.html( address ).show();
            		$$.address.removeClass( 'clipboarded' );
            		$( '.mycryptocheckout .to_input' ).mcc_make_clipboard();
                }

                /**
                	@brief		Show the name of the currency.
                	@since		2018-05-14 23:09:54
                **/
                $$.show_currency_name = function( currency_name )
                {
                	if ( $$.div_data[ 'show_currency_as_text' ] != '1' )
            			return;
                	$( '.selected_currency', $$ ).html( currency_name ).show();
                }

                $$.init();
            } ); // return this.each( function()
        } // plugin: function()
    } ); // $.fn.extend({
} )( jQuery );
