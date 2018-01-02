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
		@brief		Return some default gateway strings that are useful here and there.
		@since		2018-01-02 18:28:52
	**/
	public function gateway_strings()
	{
		$r = $this->collection();
		$r->set( 'currency_selection_text', __( 'Please select the currency with which you wish to pay', 'mycryptocheckout' ) );
		$r->set( 'gateway_name', __( 'Cryptocurrency', 'mycryptocheckout' ) );
		$r->set( 'payment_instructions', __( 'Please pay for your order by transferring [AMOUNT] [CURRENCY] from your [FROM] wallet to [TO].', 'mycryptocheckout' ) );
		$r->set( 'payment_instructions_description', __( 'Instructions for payment that will be added to the thank you page. The following shortcodes are available: [AMOUNT], [CURRENCY], [TO], [FROM]', 'mycryptocheckout' ) );
		$r->set( 'your_wallet_address_title_text', __( 'Your wallet address', 'mycryptocheckout' ) );
		$r->set( 'your_wallet_address_description_text', __( 'Your wallet address is used to track the payment.', 'mycryptocheckout' ) );
		return $r;
	}

	/**
		@brief		Retrieve the admin's email.
		@since		2017-12-25 09:03:56
	**/
	public function get_admin_email()
	{
		if ( ! $this->is_network )
			$admin_email = get_bloginfo( 'admin_email' );
		else
			// Use the admin email of blog 1.
			$admin_email = get_blog_option( 1, 'admin_email' );

		return $admin_email;
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
}
