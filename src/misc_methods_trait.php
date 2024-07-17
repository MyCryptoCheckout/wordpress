<?php

namespace mycryptocheckout;

use Exception;

/**
	@brief		Vraious methods that didn't fit anywhere else.
	@since		2017-12-11 14:22:41
**/
trait misc_methods_trait
{
	/**
		@brief		Adjust the timestamp according to the time offset.
		@since		2017-12-27 16:09:02
	**/
	public function adjust_timestamp( $timestamp )
	{
		$gmt_offset = get_option( 'gmt_offset' );
		$timestamp += 3600 * $gmt_offset;
		return $timestamp;
	}

	/**
		@brief		Check that an order (post) has received a valid payment ID.
		@details	This is used as a safeguard in case MCC can't contact the API server.
		@since		2018-10-29 22:01:23
	**/
	public function check_for_valid_payment_id( $options )
	{
		$options = (object) $options;		// Easier to access.
		$options->blog_id = get_current_blog_id();
		// We want all our args in just one array, instead of getting the options split into several parameters (thanks Wordpress).
		$this->debug( 'Scheduling check_for_valid_payment_id: %s', $options );
		$options = json_encode( $options );
		// And we want to avoid the json from being unintentionally unencoded.
		$options = base64_encode( $options );
		wp_schedule_single_event( time() + ( 15 * MINUTE_IN_SECONDS ), 'mycryptocheckout_check_for_valid_payment_id', [ $options ] );
	}

	/**
		@brief		Convenience method to return a new collection.
		@since		2017-12-14 18:45:53
	**/
	public function collection( $items = [] )
	{
		return new Collection( $items );
	}

	/**
		@brief		Enqueue the MCC CSS.
		@since		2018-01-29 09:40:03
	**/
	public function enqueue_css()
	{
		wp_enqueue_style( 'mycryptocheckout', MyCryptoCheckout()->paths( 'url' ) . 'src/static/css/mycryptocheckout.css', array(), MYCRYPTOCHECKOUT_PLUGIN_VERSION, 'all' );
	}

	/**
		@brief		Enqueue the Web3 JS.
		@since		2012-06-25 18:24:00
	**/
	public function enqueue_web3_js()
	{
		wp_enqueue_script( 'mycryptocheckout-web3', MyCryptoCheckout()->paths( 'url' ) . 'src/static/js/web3.min.js', [ 'jquery' ], MYCRYPTOCHECKOUT_PLUGIN_VERSION );
		// wp_enqueue_script( 'mycryptocheckout-web3-sol', MyCryptoCheckout()->paths( 'url' ) . 'src/static/js/sol-web3/dist/index.js', [ 'jquery' ], MYCRYPTOCHECKOUT_PLUGIN_VERSION );
	}

	/**
		@brief		Enqueue the MCC JS.
		@since		2018-01-29 09:40:03
	**/
	public function enqueue_js()
	{
		wp_enqueue_script( 'mycryptocheckout', MyCryptoCheckout()->paths( 'url' ) . 'src/static/js/mycryptocheckout.min.js', [ 'jquery' ], MYCRYPTOCHECKOUT_PLUGIN_VERSION );
	}

	/**
		@brief		Return an instance of the license expired handler.
		@since		2019-11-15 21:07:57
	**/
	public function expired_license()
	{
		if ( isset( $this->__expired_license ) )
			return $this->__expired_license;

		$this->__expired_license = new Expired_License();
		return $this->__expired_license;
	}

	/**
		@brief		Return some default gateway strings that are useful here and there.
		@since		2018-01-02 18:28:52
	**/
	public function gateway_strings()
	{
		$r = $this->collection();
		$r->set( 'currency_selection_text', __( 'Please select a currency', 'mycryptocheckout' ) );
		$r->set( 'gateway_name', __( 'Cryptocurrency', 'mycryptocheckout' ) );
		$r->set( 'online_payment_instructions_description', __( 'Instructions for payment that will be shown on the purchase confirmation page. The following shortcodes are available: [AMOUNT], [CURRENCY], [TO]', 'mycryptocheckout' ) );
		$r->set( 'online_payment_instructions', $this->wpautop_file( 'online_payment_instructions' ) );
		$r->set( 'email_payment_instructions', $this->wpautop_file( 'email_payment_instructions' ) );
		$r->set( 'email_payment_instructions_description', __( 'Instructions for payment that will be added to the e-mail receipt. The following shortcodes are available: [AMOUNT], [CURRENCY], [TO]', 'mycryptocheckout' ) );
		return $r;
	}

	/**
		@brief		Generate a javascript object containing information for the checkout JS to build the QR code and all that.
		@since		2018-04-25 15:47:05
	**/
	public function generate_checkout_js()
	{
		$action = $this->new_action( 'generate_checkout_javascript_data' );
		$action->data = $this->collection();
		$action->execute();

		return $action->render();
	}

	/**
		@brief		Get the options
		@since		2018-01-05 19:58:46
	**/
	public function get_checkout_wallet_options( $options )
	{
		$options = array_merge( [
			'as_html' => false,
			'show_amount' => false,
		], $options );
		$options = (object) $options;

		$currencies = $this->currencies();
		$wallet_options = [];
		$wallets = $this->wallets()->enabled_on_this_site();

		$selected = true;

		foreach( $wallets as $wallet )
		{
			$currency_id = $wallet->get_currency_id();
			$currency = $currencies->get( $currency_id );
			// Currency is gone? Ignore.
			if ( ! $currency )
				continue;
			if ( $options->show_amount )
			{
				$amount = $currency->normalize_amount( $options->amount );
				$amount = $this->markup_amount( [
					'amount' => $amount,
					'currency_id' => $currency_id,
				] );
				$cryptocurrency_amount = $currency->convert( $options->original_currency, $amount );
				$cryptocurrency_amount = $currency->find_next_available_amount( $cryptocurrency_amount );
				$cryptocurrency_amount = $cryptocurrency_amount . ' ';
			}
			else
			{
				$cryptocurrency_amount = '';
			}

			if ( $options->as_html )
			{
				$value = sprintf( '<option value="%s"%s>%s (%s%s)</option>',
					$currency_id,
					( $selected ? ' selected="selected"' : '' ),
					$currency->get_name(),
					$cryptocurrency_amount,
					$currency_id
				);
				// Select the first.
				if ( $selected )
					$selected = false;
			}
			else
				$value = sprintf( '%s (%s%s)',
					$currency->get_name(),
					$cryptocurrency_amount,
					$currency_id
				);
			$wallet_options[ $currency_id ] = $value;
		}

		if ( $options->as_html )
			$wallet_options = implode( "\n", $wallet_options );

		return $wallet_options;
	}

	/**
		@brief		Return the local, global, or disk option.
		@since		2018-04-26 22:19:04
	**/
	public function get_local_global_file_option( $key )
	{
		$value = $this->get_local_option( $key );
		if ( $value == '' )
			$value = $this->get_global_file_option( $key );
		return $value;
	}

	/**
		@brief		Convenience method to return a global option or its data from disk.
		@since		2018-04-26 22:19:04
	**/
	public function get_global_file_option( $key )
	{
		if ( $this->is_network )
			$value = $this->get_site_option( $key );
		else
			$value = '';
		if ( $value == '' )
			$value = $this->get_static_file( $key );
		return $value;
	}

	/**
		@brief		Return the shortest possible name of this server.
		@since		2017-12-11 14:23:01
	**/
	public function get_client_url()
	{
		if ( defined( 'MYCRYPTOCHECKOUT_CLIENT_URL' ) )
			return MYCRYPTOCHECKOUT_CLIENT_URL;
		if ( ! $this->is_network )
			$server_name = get_bloginfo( 'url' );
		else
			// The server name is the name of the first blog.
			$server_name = get_blog_option( 1, 'siteurl' );

		return $server_name;
	}

	/**
		@brief		Return a collection of sites ordered by site 1, and then the rest alphabetically.
		@since		2017-12-30 22:28:15
	**/
	public function get_sorted_sites()
	{
		$r = [];
		$sites = get_sites();
		$blog_name = get_blog_option( 1, 'blogname' );
		$unsorted[ 0 ] = [ 1, $blog_name ];

		// Ignore blog 1, since we have already handled it.
		array_shift( $sites );
		foreach( $sites as $site )
		{
			$blog_id = $site->blog_id;
			$blog_name = get_blog_option( $blog_id, 'blogname' );
			$unsorted[ $blog_name ] = [ $blog_id, $blog_name ];
		}

		ksort( $unsorted );

		$r = [];
		foreach( $unsorted as $unsorted_site )
			$r[ $unsorted_site[ 0 ] ] = $unsorted_site[ 1 ];

		return $r;
	}

	/**
		@brief		Return the text of a static file.
		@since		2018-04-26 22:24:29
	**/
	public function get_static_file( $key )
	{
		$file = __DIR__ . '/static/texts/' . $key . '.txt';
		$text = file_get_contents( $file );
		$text = trim( $text );
		return $text;
	}

	/**
		@brief		Increase a floating point number with this precision.
		@details	Use strings for the calculations. Thanks floating point!
		@since		2018-01-06 13:35:37
	**/
	public static function increase_floating_point_number( $number, $precision )
	{
		// Remove the thousands separator if there is one.
		if ( strpos( $number, ',' ) !== false )
			if ( strpos( $number, '.' ) !== false )
				$number = str_replace( ',', '', $number );

		// Convert the number to a nice string.
		$number = number_format( $number, $precision, '.', '' );

		$decimal = strpos( $number, '.');
		if ( $decimal === false )
		{
			// No decimals = easy increase.
			if ( $precision == 0 )
			{
				$number++;
				$padded_precision = '';
			}
			else
				$padded_precision = str_pad( 1, $precision, '0', STR_PAD_LEFT );

			$number = $number . '.' . $padded_precision;
		}
		else
		{
			if ( $decimal === 0 )
			{
				$number = '0' . $number;
				$decimal++;
			}

			$power = pow( 10, $precision );
			$whole = substr( $number, 0, $decimal );

			$fraction = substr( $number, $decimal + 1, $precision );
			$fraction = str_pad( $fraction, $precision, '0' );

			$powered = false;
			if ( $fraction < $power )
			{
				$powered = true;
				$fraction = $power + $fraction;
			}

			$fraction += 1;

			if ( $powered )
			{
				$fraction = substr( $fraction, 1 );
				if ( $fraction == 0 )
					$whole++;
			}

			$number = sprintf( '%s.%s', $whole, $fraction );
		}

		$number = rtrim( $number, '0' );
		$number = rtrim( $number, '.' );

		return $number;
	}

	/**
		@brief		Init this trait.
		@since		2018-04-29 19:20:55
	**/
	public function init_misc_methods_trait()
	{
		$this->add_action( 'mycryptocheckout_check_for_valid_payment_id' );
		$this->add_action( 'mycryptocheckout_generate_checkout_javascript_data', 100 );
	}

	/**
		@brief		Return this timestamp in the blog's date time format.
		@since		2017-12-27 16:07:00
	**/
	public function local_date( $timestamp )
	{
		$date_format = get_option( 'date_format' );
		$timestamp = $this->adjust_timestamp( $timestamp );
		return date( $date_format, $timestamp );
	}

	/**
		@brief		Return this timestamp in the blog's date time format.
		@since		2017-12-27 16:07:00
	**/
	public function local_datetime( $timestamp )
	{
		return $this->local_date( $timestamp ) . ' ' . $this->local_time( $timestamp );
	}

	/**
		@brief		The local options.
		@since		2018-04-26 16:15:35
	**/
	public function local_options()
	{
		return array_merge( [
			/**
				@brief		Is the QR code enabled? true, false, auto = use global setting.
				@since		2018-04-26 16:15:56
			**/
			'qr_code_enabled' => 'auto',

			/**
				@brief		Override the global QR-code HTML with a custom value?
				@since		2018-04-26 16:15:56
			**/
			'qr_code_html' => '',
		], parent::local_options() );
	}

	/**
		@brief		Return this timestamp in the blog's date time format.
		@since		2017-12-27 16:07:00
	**/
	public function local_time( $timestamp )
	{
		$time_format = get_option( 'time_format' );
		$timestamp = $this->adjust_timestamp( $timestamp );
		return date( $time_format, $timestamp );
	}

	/**
		@brief		Calculate the final price of this purchase, with markup.
		@since		2017-12-14 17:00:15
	**/
	public static function markup_amount( $options )
	{
		$amount = $options[ 'amount' ];

		$marked_up_amount = $amount;
		$marked_up_amount = floatval( $marked_up_amount );

		$markup_amount = MyCryptoCheckout()->get_site_option( 'markup_amount' );
		$markup_amount = floatval( $markup_amount );
		$marked_up_amount += $markup_amount;

		$markup_percent = MyCryptoCheckout()->get_site_option( 'markup_percent' );
		$markup_percent = floatval( $markup_percent );
		$marked_up_amount = $marked_up_amount * ( 1 + ( $markup_percent / 100 ) );

		if ( strpos( $marked_up_amount, 'E' ) !== false )
		{
			// Convert from exponent to float string.
			$marked_up_amount = sprintf( '%.18f', $marked_up_amount );
			// And trim off the zeros.
			$marked_up_amount = rtrim( $marked_up_amount, '0' );
		}

		$action = MyCryptoCheckout()->new_action( 'markup_amount' );
		$action->currency_id = $options[ 'currency_id' ];
		$action->markup_amount = $markup_amount;
		$action->markup_percent = $markup_percent;
		$action->marked_up_amount = $marked_up_amount;
		$action->original_amount = $amount;
		$action->execute();

		return $action->marked_up_amount;
	}

	/**
		@brief		Decide whether to return the HTML for this qrcode / payment timer / whatever.
		@since		2018-05-01 22:20:26
	**/
	public function maybe_enable_option_html( $key, $html_key )
	{
		$enabled = $this->get_local_option( $key );
		$html = $this->get_local_global_file_option( $html_key );
		switch( $enabled )
		{
			case 'disabled':
				$enabled = false;
			break;
			case 'enabled':
				$enabled = true;
			break;
		}

		if ( $this->is_network )
		{
			$enabled = $this->get_site_option( $key );
			switch( $enabled )
			{
				case 'disabled_all':
					$enabled = false;
				break;
				case 'enabled_all':
					$enabled = true;
					// Forcing enabled also forces the global html.
					$html = $this->get_global_file_option( $html_key );
				break;
				case 'default_disabled':
					if ( $enabled === 'auto' )
						$enabled = false;
				break;
				case 'default_enabled':
					if ( $enabled === 'auto' )
						$enabled = true;
				break;
			}
		}

		if ( ! $enabled )
			return false;

		return $html;
	}

	/**
		@brief		mycryptocheckout_check_for_valid_payment_id
		@since		2018-10-29 22:03:57
	**/
	public function mycryptocheckout_check_for_valid_payment_id( $args )
	{
		$args = base64_decode( $args );
		$args = json_decode( $args );
		// This might not be a multisite.
		if ( function_exists( 'switch_to_blog' ) )
			switch_to_blog( $args->blog_id );

		// Find the meta.
		$post_id = $args->post_id;

		try
		{
			$post = get_post( $post_id );
			if ( ! $post )
				throw new Exception( sprintf( 'Post %s does not exist.', $post_id ) );

			$payment_id = get_post_meta( $post_id, '_mcc_payment_id', true );
			// If there is no payment ID at ALL, then the payment was not created by us.
			if ( $payment_id === false )
				return;
			if ( $payment_id <= 1 )
			{
				$mail = $this->mail();
				$admin_email = get_option( 'admin_email' );
				$mail->to( $admin_email );
				$mail->from( $admin_email );
				$mail->subject( 'MyCryptoCheckout: Unable to contact the API server' );
				$url = sprintf( '<a href="%s">%s</a>', get_permalink( $post_id ), $post->post_title );
				$text = '';
				$text .= "Dear admin!\n";
				$text .= "\n";
				$text .= "MyCryptoCheckout was recently unable to contact the API server in order to retrieve a payment ID. The plugin will continue to attempt to contact the API. The gateway will be unable to process payments until it has re-established a connection.\n";
				$text .= "\n";
				$text .= "Please log in and try refreshing your MyCryptoCheckout account settings.\n";
				$text = wpautop( $text );
				$mail->html( $text );
				$mail->send();
				throw new Exception( sprintf( 'Admin %s e-mailed for post %s.', $admin_email, $post_id ) );
			}
			$this->debug( 'Valid payment ID check for order %s went just fine.', $post_id );
		}
		catch ( Exception $e )
		{
			$this->debug( 'Error while checking for valid payment ID: %s', $e->getMessage() );
		}

		// This might not be a multisite.
		if ( function_exists( 'switch_to_blog' ) )
			restore_current_blog();
	}

	/**
		@brief		mycryptocheckout_generate_checkout_javascript_data
		@since		2018-04-29 19:21:11
	**/
	public function mycryptocheckout_generate_checkout_javascript_data( $action )
	{
		$this->payment_timer_generate_checkout_javascript_data( $action );
		$this->qr_code_generate_checkout_javascript_data( $action );

		// ENS address. This requires finding the wallet that has this address and extracting the ENS address from it.
		$to = $action->data->get( 'to' );
		$wallets = $this->wallets();
		foreach( $wallets as $wallet )
		{
			if ( $wallet->get_address() != $to )
				continue;
			$ens_address = $wallet->get( 'ens_address' );
			if ( $ens_address != '' )
				$action->data->set( 'ens_address', $ens_address );
		}
	}

	/**
		@brief		Generate a new action.
		@details	Convenience method so that other plugins don't have to use the whole namespace for the class' actions.
		@since		2017-09-27 13:20:01
	**/
	public function new_action( $action_name )
	{
		$called_class = get_called_class();
		// Strip off the class name.
		$namespace = preg_replace( '/(.*)\\\\.*/', '\1', $called_class );
		$classname = $namespace  . '\\actions\\' . $action_name;
		return new $classname();
	}

	/**
		@brief		Site options.
		@since		2017-12-09 09:18:21
	**/
	public function site_options()
	{
		return array_merge( [
			/**
				@brief		The account data used to communicate with the api.
				@details	Json encoded object. Use with $this->api()->account()->get().
				@since		2017-12-11 19:27:46
			**/
			'account_data' => '',

			/**
				@brief		The account retrieval key used to assure us that only the official server can send us account updates.
				@since		2018-10-15 11:01:19
			**/
			'account_retrieve_key' => '',

			/**
				@brief		Dismissals for the expired licenses nag.
				@since		2019-11-15 21:06:15
			**/
			'expired_license_nag_dismissals' => [],

			/**
				@brief		Fixed amount markup of products for using MyCryptoCheckout as the payment.
				@since		2017-12-14 16:50:25
			**/
			'markup_amount' => 0,

			/**
				@brief		Percentage markup of products for using MyCryptoCheckout as the payment.
				@since		2017-12-14 16:50:25
			**/
			'markup_percent' => 0,

			/**
				@brief		Enable the timer on the checkout page?
				@since		2018-04-23 16:29:12
			**/
			'payment_timer_enabled' => true,

			/**
				@brief		User's HTML div for the payment timer.
				@since		2018-04-23 16:29:12
			**/
			'payment_timer_html' => '',

			/**
				@brief		List the webshop on the public MCC webshop directly?
				@since		2018-10-13 10:54:41
			**/
			'public_listing' => false,

			/**
				@brief		The status of the QR code globally.
				@since		2018-04-26 22:09:12
			**/
			'qr_code_enabled' => 'default_enabled',

			/**
				@brief		The Wallets collection in which all wallet info is stored.
				@see		Wallets()
				@since		2017-12-09 09:15:52
			**/
			'wallets' => false,

		], parent::site_options() );
	}

	/**
		@brief		Save this local option if it differs from the disk option.
		@since		2018-04-29 18:38:39
	**/
	public function update_global_disk_option( $form, $key )
	{
		$form_value = $form->input( $key )->get_post_value();
		$form_value = stripslashes( $form_value );
		// Remove the DOS newlines.
		$form_value = str_replace( "\r", '' , $form_value );
		$form_value = trim( $form_value );
		// If this is the same value as the global or file, save it as nothing.
		if ( $form_value == $this->get_static_file( $key ) )
			$form_value = '';
		$this->update_site_option( $key, $form_value );
	}

	/**
		@brief		Save this local option if it differs from the global option.
		@since		2018-04-29 18:38:39
	**/
	public function update_local_global_disk_option( $form, $key )
	{
		$form_value = $form->input( $key )->get_post_value();
		$form_value = stripslashes( $form_value );
		// Remove the DOS newlines.
		$form_value = str_replace( "\r", '' , $form_value );
		$form_value = trim( $form_value );
		// If this is the same value as the global or file, save it as nothing.
		if ( $form_value == $this->get_global_file_option( $key ) )
			$form_value = '';
		$this->update_local_option( $key, $form_value );
	}

	/**
		@brief		Return an X ago string.
		@since		2018-01-03 22:15:39
	**/
	public static function wordpress_ago( $time )
	{
		$ago = sprintf( __( '%s ago' ), human_time_diff( $time ) );
		$text = sprintf( '<span title="%s">%s</span>',
			MyCryptoCheckout()->local_datetime( $time ),
			$ago
		);
		return $text;
	}

	/**
		@brief		Return the contents of a text file using wpautop.
		@since		2018-01-28 09:39:00
	**/
	public function wpautop_file( $key )
	{
		$text = $this->get_static_file( $key );
		return wpautop( $text );
	}
}
