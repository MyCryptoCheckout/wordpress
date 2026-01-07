/**
	@brief		Handle the new currency / wallet form.
	@since		2018-09-21 17:49:39
**/
;(function( $ )
{
    $.fn.extend(
    {
        mycryptocheckout_sort_wallets : function()
        {
            return this.each( function()
            {
                var $this = $(this);

                if ( $this.hasClass( 'sortable' ) )
                	return;
                $this.addClass( 'sortable' );

                $this.data( 'nonce', $this.parent().data( 'nonce' ) );

                // Make it sortable.
				$this.sortable( {
					'handle' : 'td:first',
					placeholder: "wallet_placeholder",
        			'update' : function( event, ui )
					{
						$this.fadeTo( 250, 0.25 );
						var wallets = [];
						// Find all of the rows.
						var $rows = $( 'tr', $this );
						$.each( $rows, function( index, row )
						{
							var $row = $( row );
							wallets[ index + 10 ] = $row.data( 'index' );
						} );

						var data = {
							'action' : 'mycryptocheckout_sort_wallets',
							'nonce' : $this.data( 'nonce' ),
							'wallets' : wallets,
						};

						// Now send the new order to the server.
						$.post( {
							'data' : data,
							'url' : ajaxurl,
							'success' : function()
							{
								$this.fadeTo( 250, 1 );
							},
						} );
					},
				} )
				;//.disableSelection();
            } ); // return this.each( function()
        } // plugin: function()
    } ); // $.fn.extend({
} )( jQuery );
