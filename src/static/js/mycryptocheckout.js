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
					var $a = $( '<a href="#">' ).html( $h3.html() );
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
					} );

				} );

				$( 'li a', $subsubsub ).first().click();
            } ); // return this.each( function()
        } // plugin: function()
    } ); // $.fn.extend({
} )( jQuery );

jQuery( document ).ready( function( $ )
{
	var mycryptocheckout_checkout_javascript = function( data )
	{
		var $$ = this;
		$$.data = data;
		$$.$div = $( '.mcc.online_payment_instructions' );
		$$.$online_pay_box = $( '.mcc_online_pay_box', $$.$div );

		/**
			@brief		Check to see whether the order was paid, and cleanup in that case.
			@since		2018-05-02 21:02:30
		**/
		$$.check_for_payment = function()
		{
			var url = document.location;

			$.ajax( {
				'type' : 'get',
				'url' : url,
			} )
			.done( function( page )
			{
				var $page = $( page );
				var $mycryptocheckout_checkout_data = $( '#mycryptocheckout_checkout_data', $page );
				if ( $mycryptocheckout_checkout_data.length < 1 )
				{
					// Something went wrong.
					document.location( url );
				}

				var mycryptocheckout_checkout_data = $mycryptocheckout_checkout_data.data( 'mycryptocheckout_checkout_data' );
				mycryptocheckout_checkout_data = atob( mycryptocheckout_checkout_data );
				mycryptocheckout_checkout_data = jQuery.parseJSON( mycryptocheckout_checkout_data );
				if ( mycryptocheckout_checkout_data[ 'paid' ] === undefined )
					return;

				// Stop the countdown and show the paid div.
				clearInterval( $$.payment_timer.timeout_interval );
				$( '.paid', $$.payment_timer ).show();
				$( '.timer', $$.payment_timer ).hide();
			} );
		}

		$$.init = function()
		{
			if ( $$.$div.length < 1 )
				return;
			$$.clipboard_inputs();
			$$.maybe_hide_woocommerce_order_overview();
			$$.maybe_upgrade_divs();
			$$.maybe_generate_qr_code();
			$$.maybe_generate_payment_timer();
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

					$input.attr( 'value', $$.data.strings_copied );
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
			var $qr_code = $( '.mcc_qr_code', $$.$div );

			if ( $$.data.qr_code_html === undefined )
				return $qr_code.remove();		// Kill any existing qr code.

			var $html = $( $$.data.qr_code_html );

			// If it does not exist, add it.
			if ( $qr_code.length < 1 )
			{
				// Add the HTML.
				$qr_code = $html;
				$qr_code.appendTo( $$.$online_pay_box );
			}
			else
			{
				// If it does exist, replace it.
				$qr_code.html( $html.html() );
			}

			// Generate a QR code?
			var qr_code = new QRCode( $qr_code[ 0 ],
			{
				text: $$.data.to,
				colorDark : "#000000",
				colorLight : "#ffffff",
				correctLevel : QRCode.CorrectLevel.H
			} );
		}

		/**
			@brief		Generate the payment timer.
			@since		2018-05-01 22:18:19
		**/
		$$.maybe_generate_payment_timer = function()
		{
			$$.payment_timer = $( $$.data.payment_timer_html );
			if ( $$.payment_timer === undefined )
				return;
			$$.payment_timer.appendTo( $$.$online_pay_box );

			var timeout = $$.data.timeout_hours * 60 * 60;
			$$.payment_timer.timeout_time = parseInt( $$.data.created_at ) + timeout;

			$$.payment_timer.$hours_minutes = $( '.hours_minutes', $$.payment_timer );

			// Fetch the page once a minute to see if it has been paid.
			$$.payment_timer.status_interval = setInterval( function()
			{
				$$.check_for_payment();
			}, 1000 * 15 );
			$$.check_for_payment();

			// Update the timer every second.
			$$.payment_timer.timeout_interval = setInterval( function()
			{
				$$.update_payment_timer();
			}, 1000 );
			$$.update_payment_timer();
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

			// Instructions div is now upgraded to version 2.05.
		}

		/**
			@brief		Update the payment timer countdown div.
			@since		2018-05-03 07:12:24
		**/
		$$.update_payment_timer = function()
		{
			var current_time = Math.round( ( new Date() ).getTime() / 1000 );
			var seconds_left = $$.payment_timer.timeout_time - current_time;

			if ( seconds_left < 1 )
			{
				clearInterval( $$.payment_timer.timeout_interval );
				$$.payment_timer.update_status();
			}

			// Convert to hours.
			var hours = Math.floor( seconds_left / 60 / 60 );
			if ( hours < 10 )
				hours = '0' + hours;

			var minutes = ( seconds_left - ( hours * 3600 ) ) / 60;
			minutes = Math.floor( minutes );
			if ( minutes < 10 )
				minutes = '0' + minutes;

			var seconds = ( seconds_left - ( hours * 3600 ) ) % 60;
			if ( seconds < 10 )
				seconds = '0' + seconds;

			var text = '';
			if ( hours > 0 )
				text += hours + ':';
			text += minutes + ':' + seconds;
			$$.payment_timer.$hours_minutes.html( text );
		}

		$$.init();
	}

	$( 'form.plainview_form_auto_tabs' ).plainview_form_auto_tabs();

	var $mycryptocheckout_checkout_data = $( '#mycryptocheckout_checkout_data' );
	if ( $mycryptocheckout_checkout_data.length < 1 )
		return;

	var mycryptocheckout_checkout_data = $mycryptocheckout_checkout_data.data( 'mycryptocheckout_checkout_data' );
	mycryptocheckout_checkout_data = atob( mycryptocheckout_checkout_data );
	mycryptocheckout_checkout_data = jQuery.parseJSON( mycryptocheckout_checkout_data );
	mycryptocheckout_checkout_javascript( mycryptocheckout_checkout_data );
} );
