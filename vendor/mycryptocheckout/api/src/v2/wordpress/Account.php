<?php

namespace mycryptocheckout\api\v2\wordpress;

use mycryptocheckout\api\v2\Exception;

/**
	@brief		This component handles the account.
	@since		2017-12-11 19:16:22
**/
class Account
	extends \mycryptocheckout\api\v2\Account
{
	/**
		@brief		The data key for storing the account retrieval key.
		@since		2017-12-22 00:22:36
	**/
	public static $account_retrieve_key = 'account_retrieve_key';

	/**
		@brief		Generate an Account_Data object that we send to the server during a retrieve_account request.
		@details	We override the method because we want to add public_listing.
		@since		2018-10-13 15:30:20
	**/
	public function generate_client_account_data()
	{
		$client_account_data = parent::generate_client_account_data();

		// List the site publically?
		$public_listing = MyCryptoCheckout()->get_site_option( 'public_listing' );
		if ( $public_listing )
		{
			if ( MULTISITE )
			{
				// Fetch the name of the parent blog.
				switch_to_blog( 1 );
				$public_listing = get_bloginfo( 'name' );
				restore_current_blog();
			}
			else
				$public_listing = get_bloginfo( 'name' );
			$client_account_data->public_listing = [
				'description' => $public_listing,
			];
		}

		return $client_account_data;
	}

	/**
		@brief		Is MCC available for payment?
		@return		True if avaiable, else an exception containing the reason why it is not.
		@since		2017-12-23 09:22:12
	**/
	public function is_available_for_payment()
	{
		parent::is_available_for_payment();

		// We need at least one wallet.
		$wallets = MyCryptoCheckout()->wallets()->enabled_on_this_site();
		if ( count( $wallets ) < 1 )
			throw new Exception( 'There are no currencies enabled on this site.' );
		return true;
	}

	/**
		@brief		Check that this account retrieval key is the one we sent to the server a few moments ago.
		@since		2018-10-13 12:49:04
	**/
	public function is_retrieve_key_valid( $retrieve_key )
	{
		$stored_value = $this->api()->get_data( static::$account_retrieve_key );
		if ( ! $stored_value )
			throw new Exception( 'No retrieve key is set. Not expecting an account retrieval.' );
		// Does it match the one we got?
		return ( $stored_value == $retrieve_key );
	}

	/**
		@brief		Send the Client_Account_Data object to the API server.
		@details	After the other thread receives the new account data, we have to clear the options in the cache, in order to load the new values.
		@since		2018-10-13 15:33:50
	**/
	public function send_client_account_data( \mycryptocheckout\api\v2\Client_Account_Data $client_account_data )
	{
		remove_filter( 'http_request_args', 'trp_strip_trpst_from_requests', 10, 2 );
		$result = parent::send_client_account_data( $client_account_data );
		wp_cache_flush();
		return $result;
	}

	/**
		@brief		Set the temporary retrieve_key used to send our Client_Account_Data to the server.
		@details	Put this value into temporary (1 minute) storage, enough for the API server to reply with the new account data.
		@since		2018-10-13 15:29:51
	**/
	public function set_retrieve_key( $retrieve_key )
	{
		$this->api()->save_data( static::$account_retrieve_key, $retrieve_key );
	}

}
