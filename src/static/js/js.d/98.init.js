mycryptocheckout_convert_data( 'mycryptocheckout_checkout_data', function( data )
{
	mycryptocheckout_checkout_javascript( data );
} );
$( 'form.plainview_form_auto_tabs' ).plainview_form_auto_tabs();
$( '.mcc_donations' ).mycryptocheckout_donations_javascript();

$( 'form#currencies' ).mycryptocheckout_new_currency();

/**
	@brief		Make the wallets sortable.
	@since		2018-10-17 17:38:58
**/
$( 'table.currencies tbody' ).mycryptocheckout_sort_wallets();