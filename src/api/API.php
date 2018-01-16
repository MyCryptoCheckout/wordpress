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
		@brief		Return the API url.
		@since		2017-12-21 23:31:39
	**/
	public static function get_api_url()
	{
		if ( defined( 'MYCRYPTOCHECKOUT_API_URL' ) )
			return MYCRYPTOCHECKOUT_API_URL;
		else
			return static::$api_url;
	}

	/**
		@brief		Get purchase URL.
		@details	Return the URL used for buying subscriptions.
		@since		2017-12-27 17:10:56
	**/
	public function get_purchase_url()
	{
		$url = 'https://mycryptocheckout.com/pricing/';
		return add_query_arg( [
			'domain' => base64_encode( MyCryptoCheckout()->get_server_name() ),
		], $url );
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
		@brief		Parse a wp_remote_ reponse.
		@since		2017-12-21 23:34:30
	**/
	public function parse_response( $r )
	{
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

		return $json;
	}

	/**
		@brief		Return the payments component.
		@since		2017-12-11 14:05:32
	**/
	public function payments()
	{
		if ( isset( $this->__payments ) )
			return $this->__payments;

		$this->__payments = new Payments( $this );
		return $this->__payments;
	}

	/**
		@brief		Process this json data that was received from the API server.
		@since		2017-12-11 14:15:03
	**/
	public function process_messages( $json )
	{
		// This must be an array.
		if ( ! is_array( $json->messages ) )
			return MyCryptoCheckout()->debug( 'JSON does not contain a messages array.' );

		// First check for a retrieve_account message.
		foreach( $json->messages as $message )
		{
			if ( $message->type != 'retrieve_account' )
				continue;
			$transient_value = get_site_transient( Account::$account_retrieve_transient_key );
			if ( ! $transient_value )
				throw new Exception( 'No retrieve key is set. Not expecting an account retrieval.' );
			// Does it match the one we got?
			if ( $transient_value != $message->retrieve_key )
				throw new Exception( sprintf( 'Retrieve keys do not match. Expecting %s but got %s.', $transient_value, $message->retrieve_key ) );
			// Everything looks good to go.
			$new_account_data = (object) (array) $message->account;
			MyCryptoCheckout()->debug( 'Setting new account data: %s', json_encode( $new_account_data ) );
			MyCryptoCheckout()->api()->account()->set_data( $new_account_data )
				->save();
		}

		$account = $this->account();
		if ( ! $account->is_valid() )
			return MyCryptoCheckout()->debug( 'No account data found. Ignoring messages.' );

		// Check that the domain key matches ours.
		if ( $account->get_domain_key() != $json->mycryptocheckout )
			return MyCryptoCheckout()->debug( 'Invalid domain key. Received %s', $json->mycryptocheckout );

		// Handle the messages, one by one.
		foreach( $json->messages as $message )
		{
			MyCryptoCheckout()->debug( '(%d) Processing a %s message: %s', get_current_blog_id(), $message->type, json_encode( $message ) );
			switch( $message->type )
			{
				case 'retrieve_account':
					// Already handled above.
				break;
				case 'cancel_payment':
					// This payment timed out and was cancelled.
					do_action( 'mycryptocheckout_cancel_payment', $message->payment );
				break;
				case 'payment_complete':
					// Send out info about this completed payment.
					do_action( 'mycryptocheckout_payment_complete', $message->payment );
				break;
				case 'update_account':
					$new_account_data = (object) (array) $message->account;
					MyCryptoCheckout()->update_site_option( 'account_data', json_encode( $new_account_data ) );
				break;
			}
		}
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

	/**
		@brief		Send a POST but include our account data.
		@since		2017-12-22 00:18:48
	**/
	public function send_post_with_account( $url, $data )
	{
		$account = $this->account();
		// Merge the domain key.
		$data[ 'domain' ] = MyCryptoCheckout()->get_server_name();
		$data[ 'domain_key' ] = $account->get_domain_key();
		return $this->send_post( $url, $data );
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
