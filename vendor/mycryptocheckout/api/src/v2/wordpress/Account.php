<?php

namespace mycryptocheckout\api\v2\wordpress;

/**
	@brief		This component handles the account.
	@since		2017-12-11 19:16:22
**/
class Account
	extends \mycryptocheckout\api\v2\Account
{
	/**
		@brief		The transient key for storing the account retrieval key.
		@since		2017-12-22 00:22:36
	**/
	public static $account_retrieve_transient_key = 'mycryptocheckout_account_retrieve_key';

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
		@brief		Send the Client_Account_Data object to the API server.
		@details	This is very complicated due to how Wordpress caches transient values.
					After the other thread receives the new account data, we have to clear the options in the cache, in order to load the new values.
		@since		2018-10-13 15:33:50
	**/
	public function send_client_account_data( \mycryptocheckout\api\v2\Client_Account_Data $client_account_data )
	{
		$result = parent::send_client_account_data( $client_account_data );
		// Clear the option caches, since the options are modified in another thread (due to the api server communicating with back with us).
		$option_key = MyCryptoCheckout()->fix_option_name( static::$account_data_site_option_key );
		$cache_key = get_current_network_id() . ':' . $option_key;
		wp_cache_delete( $cache_key, 'site-options' );
		wp_cache_delete( $option_key, 'options' );
		return $result;
	}

	/**
		@brief		Set the temporary retrieve_key used to send our Client_Account_Data to the server.
		@details	Put this value into temporary (1 minute) storage, enough for the API server to reply with the new account data.
		@since		2018-10-13 15:29:51
	**/
	public function set_retrieve_key( $retrieve_key )
	{
		set_site_transient( static::$account_retrieve_transient_key, $retrieve_key, 60 );
	}

}
