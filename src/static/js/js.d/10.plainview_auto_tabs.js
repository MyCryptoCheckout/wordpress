/**
	@brief		Convert the form fieldsets in a form2 table to ajaxy tabs.
	@since		2015-07-11 19:47:46
**/
;(function( $ )
{
    $.fn.extend(
    {
        plainview_form_auto_tabs : function()
        {
            return this.each( function()
            {
                var $this = $(this);

                if ( $this.hasClass( 'auto_tabbed' ) )
                	return;

                $this.addClass( 'auto_tabbed' );

				var $fieldsets = $( 'div.fieldset', $this );
				// At least two fieldsets for this to make sense.
				if ( $fieldsets.length < 2 )
					return;

				$this.prepend( '<div style="clear: both"></div>' );
				// Create the "tabs", which are normal Wordpress tabs.
				var $subsubsub = $( '<ul class="subsubsub">' )
					.prependTo( $this );

				$.each( $fieldsets, function( index, item )
				{
					var $item = $(item);
					var $h3 = $( 'h3.title', $item );
					var h3_slug = $h3.text().replace( /[^0-9a-zA-Z]/g, '_' ).toLowerCase();
					var $a = $( '<a class="' + h3_slug + '" href="#' + h3_slug + '">' ).html( $h3.html() );
					$h3.remove();
					var $li = $( '<li>' );
					$a.appendTo( $li );
					$li.appendTo( $subsubsub );

					// We add a separator if we are not the last li.
					if ( index < $fieldsets.length - 1 )
						$li.append( '<span class="sep">&emsp;|&emsp;</span>' );

					// When clicking on a tab, show it
					$a.click( function()
					{
						$( 'li a', $subsubsub ).removeClass( 'current' );
						$(this).addClass( 'current' );
						$fieldsets.hide();
						$item.show();

						// Also add the hash to the forma action.
						var $form = $subsubsub.closest( 'form' );
						var form_action = $form.prop( 'action' );
						// Remove the hash.
						form_action = form_action.replace( /#.*/, '' );
						$form.prop( 'action', form_action + '#' + h3_slug );
					} );

				} );

				// Check if there is a # in the location
				if ( document.location.hash.length > 1 )
				{
					// Select it
					var tab_hash = document.location.hash.replace( '#', '' );
					$( 'li a.' + tab_hash , $subsubsub ).click();
				}
				else
				{
					// Select the first tab
					$( 'li a', $subsubsub ).first().click();
				}
            } ); // return this.each( function()
        } // plugin: function()
    } ); // $.fn.extend({
} )( jQuery );
