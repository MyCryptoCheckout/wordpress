<?php

namespace mycryptocheckout\api\v2;

/**
	@brief		Main API controller.
	@since		2018-10-08 19:17:59
**/
abstract class API
{
	/**
		@brief		The API server address.
		@since		2017-12-11 14:07:24
	**/
	public static $api_url = 'https://api.mycryptocheckout.com/v2/';

	/**
		@brief		Return the account component.
		@since		2017-12-11 14:03:50
	**/
	public function account()
	{
		if ( isset( $this->__account ) )
			return $this->__account;

		$this->__account = $this->new_account();
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
		@brief		Return data from storage.
		@since		2018-10-08 20:14:21
	**/
	public abstract function get_data( $key, $default = false );

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

		$this->__payments = $this->new_payments();
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
			// complete_payment is more logical, but the server can't be updated.
			if ( $message->type == 'payment_complete' )
				$message->type = 'complete_payment';

			MyCryptoCheckout()->debug( '(%d) Processing a %s message: %s', get_current_blog_id(), $message->type, json_encode( $message ) );

			switch( $message->type )
			{
				case 'retrieve_account':
					// Already handled above.
				break;
				case 'cancel_payment':
				case 'complete_payment':
					$action = MyCryptoCheckout()->new_action( $message->type );
					$action->payment = $message->payment;
					$action->execute();
					if ( $action->applied < 1 )
						throw new Exception( sprintf( 'Unable to apply %s for payment ID %s.', $message->type, json_encode( $message->payment ) ) );
					MyCryptoCheckout()->debug( '%s action applied %s times.', $message->type, $action->applied );
				break;
				case 'update_account':
					$new_account_data = (object) (array) $message->account;
					MyCryptoCheckout()->update_site_option( 'account_data', json_encode( $new_account_data ) );
				break;
				default:
					throw new Exception( sprintf( 'Unknown message type: %s', $message->type ) );
					break;
			}
		}
	}

	/**
		@brief		Save this data.
		@since		2018-10-08 19:30:01
	**/
	public abstract function save_data( $key, $data );

	/**
		@brief		Send this GET call to the API server.
		@since		2018-10-08 20:18:45
	**/
	public abstract function send_get( $url );

	/**
		@brief		Send this $data array to the API server in a POST call.
		@since		2018-10-08 20:18:45
	**/
	public abstract function send_post( $url, $data );

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
}
