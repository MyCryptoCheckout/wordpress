<?php

namespace mycryptocheckout\api;

/**
	@brief		Liason between Wordpress and the API.
	@since		2018-10-08 19:51:25
**/
class API
	extends v2\API
{
	/**
		@brief		Return data from storage.
		@since		2018-10-08 20:14:21
	**/
	public function get_data( $key, $default = false )
	{
		return $this->get_site_option( $key );
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
		@brief		Create a new account component.
		@since		2018-10-08 19:52:37
	**/
	public function new_account()
	{
		return new Account( $this );
	}

	/**
		@brief		Save this data.
		@since		2018-10-08 19:54:38
	**/
	public function save_data( $key, $data )
	{
		$this->update_site_option( $key, $data );
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
