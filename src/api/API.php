<?php

namespace mycryptocheckout\api;

/**
	@brief		The main API controller.
	@details	This controls the various components used.
				For debugging purposes, you may create some defines:
				- MYCRYPTOCHECKOUT_API_URL is the API URL to call.
				- MYCRYPTOCHECKOUT_API_NOSSLVERIFY to disable SSL verification of the API server.
	@since		2017-12-11 14:00:57
**/
class API
{
	/**
		@brief		The API server address.
		@since		2017-12-11 14:07:24
	**/
	public static $api_url = 'https://api.mycryptocheckout.com/v1/';

	/**
		@brief		Should the components work with their caches?
		@details	Disable this to always request fresh info from the api server, and drastically slow down your site.
		@since		2017-12-11 19:22:15
	**/
	public static $caching = true;

	/**
		@brief		Return the account component.
		@since		2017-12-11 14:03:50
	**/
	public function account()
	{
		if ( isset( $this->__account ) )
			return $this->__account;

		$this->__account = new Account( $this );
		return $this->__account;
	}

	/**
		@brief		Process this json data that was received from the API server.
		@since		2017-12-11 14:15:03
	**/
	public function process_notifications( $json )
	{
		// This must be an array.
		if ( ! is_array( $json->notifications ) )
			return MyCryptoCheckout()->debug( 'JSON does not contain a notifications array.' );

		// First check for a retrieve_account notification.
		foreach( $json->notifications as $notification )
		{
			if ( $notification->type != 'retrieve_account' )
				continue;
			// Does the retrieve key match?
			$existing_account_data = MyCryptoCheckout()->get_site_option( 'account_data' );
			$existing_account_data = json_decode( $existing_account_data );
			// Do we have any account data set?
			if ( ! $existing_account_data )
				throw new Exception( 'No existing account data set.' );
			// Is the retrieve key set?
			if ( ! isset( $existing_account_data->retrieve_key ) )
				throw new Exception( 'No retrieve key is set. Not expecting an account retrieval.' );
			// Does it match the one we got?
			if ( $existing_account_data->retrieve_key != $notification->retrieve_key )
				throw new Exception( 'Retrieve keys do not match. Expecting %s but got %s.', $existing_account_data->retrieve_key, $notification->retrieve_key );
			// Everything looks good to go.
			$new_account_data = (object) (array) $notification;
			unset( $new_account_data->type );
			unset( $new_account_data->retrieve_key );
			MyCryptoCheckout()->debug( 'Setting new account data: %s', $new_account_data );
			MyCryptoCheckout()->update_site_option( 'account_data', json_encode( $new_account_data ) );
		}

		// Retrieve the account data.
		$account_data = MyCryptoCheckout()->get_site_option( 'account_data' );
		$account_data = json_decode( $account_data );
		if ( ! $account_data )
			return MyCryptoCheckout()->debug( 'No account data found. Ignoring notifications.' );

		// Check that the domain key matches ours.
		if ( $account_data->domain_key != $json->mycryptocheckout )
			return MyCryptoCheckout()->debug( 'Invalid domain key. Received %s', $json->mycryptocheckout );

		// Handle the notifications, one by one.
		foreach( $json->notifications as $notification )
		{
			switch( $notification->type )
			{
				case 'retrieve_account':
					// Already handled above.
					break;
			}
		}
	}

	/**
		@brief		Send a GET request to the api server.
		@since		2017-12-11 20:12:04
	**/
	public function send_get( $request_url )
	{
		if ( defined( 'MYCRYPTOCHECKOUT_API_URL' ) )
			$api_url = MYCRYPTOCHECKOUT_API_URL;
		else
			$api_url = static::$api_url;
		$url = sprintf( '%s%s', $api_url, $request_url );

		$r = wp_remote_get( $url, [
			'sslverify' => ( ! defined( 'MYCRYPTOCHECKOUT_API_NOSSLVERIFY' ) ),
			'timeout' => 30,
		] );

		if ( ! $r )
			throw new Exception( 'Unable to communicate with the API server.' );

		if ( is_wp_error( $r ) )
		{
			$e = reset( $r->errors );
			throw new Exception( reset( $e ) );
		}

		$data = wp_remote_retrieve_body( $r );
		$json = json_decode( $data );
		if ( ! is_object( $json ) )
			throw new Exception( 'Did not receive a JSON reply from the API server.' );

		return $r;
	}

	/**
		@brief		Return the transactions component.
		@since		2017-12-11 14:05:32
	**/
	public function transactions()
	{
		if ( isset( $this->__transactions ) )
			return $this->__transactions;

		$this->__transactions = new Transactions( $this );
		return $this->__transactions;
	}
}
