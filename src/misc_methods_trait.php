<?php

namespace mycryptocheckout;

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
		wp_enqueue_style( 'mycryptocheckout', MyCryptoCheckout()->paths( 'url' ) . '/src/static/css/mycryptocheckout.css', $this->plugin_version );
	}

	/**
		@brief		Enqueue the MCC JS.
		@since		2018-01-29 09:40:03
	**/
	public function enqueue_js()
	{
		wp_enqueue_script( 'mycryptocheckout', MyCryptoCheckout()->paths( 'url' ) . '/src/static/js/mycryptocheckout.js', [ 'jquery' ], $this->plugin_version );
	}

	/**
		@brief		Return some default gateway strings that are useful here and there.
		@since		2018-01-02 18:28:52
	**/
	public function gateway_strings()
	{
		$r = $this->collection();
		$r->set( 'currency_selection_text', __( 'Please select the currency with which you wish to pay', 'mycryptocheckout' ) );
		$r->set( 'gateway_name', __( 'Cryptocurrency', 'mycryptocheckout' ) );
		$r->set( 'online_payment_instructions_description', __( 'Instructions for payment that will be shown on the purchase confirmation page. The following shortcodes are available: [AMOUNT], [CURRENCY], [TO]', 'mycryptocheckout' ) );
		$r->set( 'online_payment_instructions', $this->wpautop_file( 'online_payment_instructions' ) );
		$r->set( 'email_payment_instructions', $this->wpautop_file( 'email_payment_instructions' ) );
		$r->set( 'email_payment_instructions_description', __( 'Instructions for payment that will be added to the e-mail receipt. The following shortcodes are available: [AMOUNT], [CURRENCY], [TO]', 'mycryptocheckout' ) );
		return $r;
	}

	/**
		@brief		Get the options
		@since		2018-01-05 19:58:46
	**/
	public function get_checkout_wallet_options( $options )
	{
		$preselected = $this->wallets()->get_preselected_currency_ids();
		$preselected = reset( $preselected );
		$options = array_merge( [
			'as_html' => false,
			'default' => $preselected,
		], $options );
		$options = (object) $options;

		$currencies = $this->currencies();
		$wallet_options = [];
		$wallets = $this->wallets()->enabled_on_this_site();

		foreach( $wallets as $wallet )
		{
			$currency_id = $wallet->get_currency_id();
			$currency = $currencies->get( $currency_id );
			$amount = $this->markup_amount( $options->amount );
			$cryptocurrency_amount = $currency->convert( $options->original_currency, $amount );
			$cryptocurrency_amount = $currency->find_next_available_amount( $cryptocurrency_amount );

			if ( $options->as_html )
				$value = sprintf( '<option value="%s"%s>%s (%s %s)</option>',
					$currency_id,
					( $currency_id == $preselected ? ' selected="selected"' : '' ),
					$currency->get_name(),
					$cryptocurrency_amount,
					$currency_id
				);
			else
				$value = sprintf( '%s (%s %s)',
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
		@brief		Return the shortest possible name of this server.
		@since		2017-12-11 14:23:01
	**/
	public function get_server_name()
	{
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
		@brief		Increase a floating point number with this precision.
		@details	Use strings for the calculations. Thanks floating point!
		@since		2018-01-06 13:35:37
	**/
	public static function increase_floating_point_number( $number, $precision )
	{
		$decimal = strpos( $number, '.');
		if ( $decimal === false )
		{
			// No decimals = easy increase.
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
	public static function markup_amount( $amount )
	{
		$marked_up_amount = $amount;

		$markup_amount = MyCryptoCheckout()->get_site_option( 'markup_amount' );
		$marked_up_amount += $markup_amount;

		$markup_percent = MyCryptoCheckout()->get_site_option( 'markup_percent' );
		$marked_up_amount = $marked_up_amount * ( 1 + ( $markup_percent / 100 ) );

		$action = MyCryptoCheckout()->new_action( 'markup_amount' );
		$action->markup_amount = $markup_amount;
		$action->markup_percent = $markup_percent;
		$action->marked_up_amount = $marked_up_amount;
		$action->original_amount = $amount;
		$action->execute();

		return $action->marked_up_amount;
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
				@brief		The Wallets collection in which all wallet info is stored.
				@see		Wallets()
				@since		2017-12-09 09:15:52
			**/
			'wallets' => false,

		], parent::site_options() );
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
		$file = __DIR__ . '/static/texts/' . $key . '.txt';
		$text = file_get_contents( $file );
		return wpautop( $text );
	}
}
