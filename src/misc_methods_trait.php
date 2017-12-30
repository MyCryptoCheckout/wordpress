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
		@brief		Retrieve the admin's email.
		@since		2017-12-25 09:03:56
	**/
	public function get_admin_email()
	{
		if ( ! $this->is_network )
		{
			$admin_email = get_bloginfo( 'admin_email' );
		}
		else
		{
			// The server name is the name of the first blog.
			$admin_email = get_blog_details( 1, 'admin_email' );
		}

		return $admin_email;
	}

	/**
		@brief		Return the shortest possible name of this server.
		@since		2017-12-11 14:23:01
	**/
	public function get_server_name()
	{
		if ( ! $this->is_network )
		{
			$server_name = get_bloginfo( 'url' );
		}
		else
		{
			// The server name is the name of the first blog.
			$server_name = get_blog_details( 1, 'url' );
		}

		return $server_name;
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
