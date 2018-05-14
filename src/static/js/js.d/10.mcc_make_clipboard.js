/**
	@brief		Convert a text into a copy-pastable input.
	@since		2018-05-14 19:38:22
**/
;(function( $ )
{
    $.fn.extend(
    {
        mcc_make_clipboard : function()
        {
            return this.each( function()
            {
                var $item = $(this);

                if ( $item.hasClass( 'clipboarded' ) )
                	return;

				$item.addClass( 'clipboardable' );
				$item.addClass( 'clipboarded' );

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

					$input.attr( 'value', 'OK!' );
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

				} ); // return this.each( function()
        } // plugin: function()
    } ); // $.fn.extend({
} )( jQuery );
