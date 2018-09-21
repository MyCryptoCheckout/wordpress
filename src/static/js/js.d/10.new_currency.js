/**
	@brief		Handle the new currency / wallet form.
	@since		2018-09-21 17:49:39
**/
;(function( $ )
{
    $.fn.extend(
    {
        mycryptocheckout_new_currency : function()
        {
            return this.each( function()
            {
                var $this = $(this);

                if ( $this.hasClass( 'mycryptocheckout_new_currency' ) )
                	return;
                $this.addClass( 'mycryptocheckout_new_currency' );

                // Find the currency selector.
                $this.$currency_id = $( '.currency_id', $this );

               	var $currencies = $( '.only_for_currency', $this );
               	$currencies.parentsUntil( 'tr' ).parent().hide();

                $this.$currency_id.change( function()
                {
                	// Hide all currencies.
                	$currencies.parentsUntil( 'tr' ).parent().hide();
                	// And show only the selected one.
                	var currency_id = $this.$currency_id.val();
                	var selector = '.only_for_currency.' + currency_id;
                	$( selector, $this ).parentsUntil( 'tr' ).parent().show();
                } )
                .change();
            } ); // return this.each( function()
        } // plugin: function()
    } ); // $.fn.extend({
} )( jQuery );
