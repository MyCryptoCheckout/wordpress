<?php

namespace mycryptocheckout\api\v2\wordpress;

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
		@brief		Return data from storage.
		@since		2018-10-08 20:14:21
	**/
	public function get_data( $key, $default = false )
	{
		return MyCryptoCheckout()->get_site_option( $key );
	}

	/**
		@brief		Return the name of the server we are on.
		@since		2018-10-13 11:46:01
	**/
	public function get_server_name()
	{
		return MyCryptoCheckout()->get_server_name();
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
		@brief		Check that this account retrieval key is the one we sent to the server a few moments ago.
		@since		2018-10-13 12:49:04
	**/
	public function is_retrieve_key_valid( $retrieve_key )
	{
		$transient_value = get_site_transient( Account::$account_retrieve_transient_key );
		if ( ! $transient_value )
			throw new Exception( 'No retrieve key is set. Not expecting an account retrieval.' );
		// Does it match the one we got?
		return ( $transient_value == $retrieve_key );
	}

	/**
		@brief		Create a new account component.
		@since		2018-10-08 19:52:37
	**/
	public function new_account()
	{
		return new Account( $this );
	}

	/**
		@brief		Create a new payments component.
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
		$r = wp_remote_post( $url, static::get_wp_remote_args( [
			'body' => $data,
		] ) );
		return $this->parse_response( $r );
	}
}
