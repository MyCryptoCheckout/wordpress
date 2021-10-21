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
		@brief		Return the autosettlements component.
		@since		2019-03-06 23:34:51
	**/
	public function autosettlements()
	{
		if ( isset( $this->__autosettlements ) )
			return $this->__autosettlements;

		$this->__autosettlements = $this->new_autosettlements();
		return $this->__autosettlements;
	}

	/**
		@brief		Output this string to any debug log you might have.
		@details	Optimally, run the arguments through an sprintf.
		@since		2018-10-13 11:48:29
	**/
	public function debug( $string )
	{
	}

	/**
		@brief		Delete this data from persistent storage.
		@since		2018-10-14 15:09:47
	**/
	public abstract function delete_data( $key );

	/**
		@brief		Return the API url.
		@since		2017-12-21 23:31:39
	**/
	public static function get_api_url()
	{
		// Allow the URL to be overridden.
		if ( defined( 'MYCRYPTOCHECKOUT_API_URL' ) )
			return MYCRYPTOCHECKOUT_API_URL;
		else
			return static::$api_url;
	}

	/**
		@brief		Return data from persistent storage.
		@since		2018-10-08 20:14:21
	**/
	public abstract function get_data( $key, $default = false );

	/**
		@brief		Get purchase URL.
		@details	Return the URL used for buying licenses.
		@since		2017-12-27 17:10:56
	**/
	public function get_purchase_url()
	{
		if ( defined( 'MYCRYPTOCHECKOUT_PRICING_URL' ) )
			$url = MYCRYPTOCHECKOUT_PRICING_URL;
		else
			$url = 'https://mycryptocheckout.com/pricing/';
		return add_query_arg( [
			'domain' => base64_encode( $this->get_client_url() ),
		], $url );
	}

	/**
		@brief		Return the domain of the server we are on.
		@since		2018-10-13 11:46:01
	**/
	public abstract function get_client_url();

	/**
		@brief		Echo a json object.
		@since		2018-10-13 18:35:54
	**/
	public function json_reply( $data )
	{
		@header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
		echo json_encode( $data );
		exit;
	}

	/**
		@brief		Maybe process messages found in the _POST.
		@since		2018-10-13 18:33:44
	**/
	public function maybe_process_messages()
	{
		if ( isset( $_SERVER[ 'CONTENT_TYPE' ] ) )
			if ( $_SERVER[ 'CONTENT_TYPE' ] != 'application/json' )
				return;
		// Retrieve the body of the request.
		$json = file_get_contents('php://input');
		$json = json_decode( $json );
		if ( ! $json )
			return;

		if ( ! isset( $json->mycryptocheckout ) )
			return;

		try
		{
			$this->process_messages( $json );
			$this->json_reply( [ 'result' => 'ok' ] );
		}
		catch ( api\Exception $e )
		{
			$this->debug( 'API failure: %s', $e->get_message() );
			$this->json_reply( [ 'result' => 'fail', 'message' => $e->get_message() ] );
		}
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
		@brief		Create the autosettlements component.
		@since		2019-03-06 23:35:37
	**/
	public function new_autosettlements()
	{
		return new Autosettlements( $this );
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
			throw new Exception( sprintf( 'Did not receive a JSON reply from the API server: %s', htmlspecialchars( $data ) ) );

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
			return $this->debug( 'JSON does not contain a messages array.' );

		// First check for a retrieve_account message.
		foreach( $json->messages as $message )
		{
			if ( $message->type != 'retrieve_account' )
				continue;
			if ( ! $this->account()->is_retrieve_key_valid( $message->retrieve_key ) )
				throw new Exception( sprintf( 'Retrieve keys do not match: %s.', $message->retrieve_key ) );
			// Everything looks good to go.
			$new_account_data = (object) (array) $message->account;
			$this->debug( 'Setting new account data: %d bytes plain, %s bytes compressed',
				strlen( json_encode( $new_account_data ) ),
				strlen( $this->account()->compress( $new_account_data ) )
			);
			$this->account()->set_data( $new_account_data )
				->save();
		}

		$account = $this->account();
		if ( ! $account->is_valid() )
			return $this->debug( 'No account data found. Ignoring messages.' );

		// Check that the domain key matches ours.
		if ( $account->get_domain_key() != $json->mycryptocheckout )
			return $this->debug( 'Invalid domain key. Received %s', $json->mycryptocheckout );

		// Handle the messages, one by one.
		foreach( $json->messages as $message )
		{
			// complete_payment is more logical, but the server can't be updated.
			if ( $message->type == 'payment_complete' )
				$message->type = 'complete_payment';

			$this->debug( '(%d) Processing a %s message.', get_current_blog_id(), $message->type );

			if ( isset( $message->payment ) )
			{
				// Create a payment object that is used to transfer the completion / cancellation data from the server to whoever handles the message.
				$payment = $this->payments()->create_new( $message->payment );
				$payment->set_id( $message->payment->payment_id );		// This is already set during create_new(), but we want to be extra clear.
				if ( isset( $message->payment->transaction_id ) )		// Completions require this.
					$payment->set_transaction_id( $message->payment->transaction_id );	// This is already set during create_new(), but we want to be extra clear.
			}

			switch( $message->type )
			{
				case 'retrieve_account':
					// Already handled above.
				break;
				case 'cancel_payment':
					// Mark the local payment as canceled.
					$this->payments()->cancel_local( $payment );
					break;
				case 'complete_payment':
					// Mark the local payment as complete.
					$this->payments()->complete_local( $payment );
				break;
				case 'test_communication':
					$this->json_reply( [ 'result' => 'ok', 'message' => date( 'Y-m-d H:i:s' ) ] );
				break;
				case 'update_account':
					// Save our new account data.
					$new_account_data = (object) (array) $message->account;
					$this->save_data( 'account_data', json_encode( $new_account_data ) );
				break;
				default:
					throw new Exception( sprintf( 'Unknown message type: %s', $message->type ) );
					break;
			}
		}
	}

	/**
		@brief		Save this data to persisent storage.
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
		$data[ 'domain' ] = $this->get_client_url();
		$data[ 'domain_key' ] = $account->get_domain_key();
		return $this->send_post( $url, $data );
	}

	/**
		@brief		Test basic communication with the client in realtime.
		@since		2019-11-15 23:04:31
	**/
	public function test_communication()
	{
		return $this->send_post_with_account( 'test_communication', [] );
	}
}
