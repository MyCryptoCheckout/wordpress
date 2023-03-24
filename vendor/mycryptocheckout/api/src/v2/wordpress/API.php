<?php

namespace mycryptocheckout\api\v2\wordpress;

use mycryptocheckout\api\v2\Exception;

/**
	@brief		Liason between Wordpress and the API.
	@since		2018-10-08 19:51:25
**/
class API
	extends \mycryptocheckout\api\v2\API
{
	/**
		@brief		Output this string to the debug log.
		@details	Optimally, run the arguments through an sprintf.
		@since		2018-10-13 11:48:29
	**/
	public function debug( $string )
	{
		return call_user_func_array( [ MyCryptoCheckout(), 'debug' ], func_get_args() );
	}

	/**
		@brief		Delete this data from persistent storage.
		@since		2018-10-14 15:09:47
	**/
	public function delete_data( $key )
	{
		MyCryptoCheckout()->delete_site_option( $key );
		return $this;
	}

	/**
		@brief		Return data from storage.
		@since		2018-10-08 20:14:21
	**/
	public function get_data( $key, $default = false )
	{
		return MyCryptoCheckout()->get_site_option( $key );
	}

	/**
		@brief		Return the name of the server we are on.
		@details	We are the _client_ to the API.
		@since		2018-10-13 11:46:01
	**/
	public function get_client_url()
	{
		if ( defined( 'MYCRYPTOCHECKOUT_CLIENT_URL' ) )
			return MYCRYPTOCHECKOUT_CLIENT_URL;
		return MyCryptoCheckout()->get_client_url();
	}

	/**
		@brief		Return the standard WP Remote Get/Post args.
		@since		2017-12-21 23:32:33
	**/
	public function get_wp_remote_args( $args = [] )
	{
		$r = array_merge( [
			'sslverify' => ( ! defined( 'MYCRYPTOCHECKOUT_API_NOSSLVERIFY' ) ),
			'timeout' => 60,
		], $args );
		return $r;
	}

	/**
		@brief		Create a new Account component.
		@since		2018-10-08 19:52:37
	**/
	public function new_account()
	{
		$r = new Account( $this );
		// Allow other plugins to modify the account data.
		$r = apply_filters( 'mycryptocheckout_api_account', $r );
		return $r;
	}

	/**
		@brief		Create a new Payments component.
		@since		2018-10-08 19:53:34
	**/
	public function new_payments()
	{
		return new Payments( $this );
	}

	/**
		@brief		Save this data.
		@since		2018-10-08 19:54:38
	**/
	public function save_data( $key, $data )
	{
		MyCryptoCheckout()->update_site_option( $key, $data );
		return $this;
	}

	/**
		@brief		Send a GET request to the api server.
		@since		2017-12-11 20:12:04
	**/
	public function send_get( $url )
	{
		$url = sprintf( '%s%s', static::get_api_url(), $url );
		$r = wp_remote_get( $url, static::get_wp_remote_args() );
		return $this->parse_response( $r );
	}

	/**
		@brief		Send a POST url.
		@since		2017-12-21 23:33:36
	**/
	public function send_post( $url, $data )
	{
		$url = sprintf( '%s%s', static::get_api_url(), $url );
		$data = ( array ) $data ;		// Allow for CURL to serialize.
		$r = wp_remote_post( $url, static::get_wp_remote_args( [
			'body' => $data,
		] ) );
		return $this->parse_response( $r );
	}
}
