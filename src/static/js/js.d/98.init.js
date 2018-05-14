mycryptocheckout_convert_data( 'mycryptocheckout_checkout_data', function( data )
{
	mycryptocheckout_checkout_javascript( data );
} );
$( 'form.plainview_form_auto_tabs' ).plainview_form_auto_tabs();
$( '.mcc_donations' ).mycryptocheckout_donations_javascript();

/**
	@brief		Make these texts into clipboard inputs.
	@since		2018-05-14 19:44:07
**/
$( '.mycryptocheckout .to_input' ).mcc_make_clipboard();
