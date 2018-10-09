<?php

namespace mycryptocheckout\api;

/**
	@brief		This component handles the account.
	@since		2017-12-11 19:16:22
**/
class Account
	extends v2\Component
{
	/**
		@brief		The transient key for storing the account retrieval key.
		@since		2017-12-22 00:22:36
	**/
	public static $account_retrieve_transient_key = 'mycryptocheckout_account_retrieve_key';

	/**
		@brief		Retrieve the account information from the server.
		@since		2017-12-11 19:18:29
	**/
	public function retrieve()
	{
		try
		{
			// Set a retrieve key so we know that the retrieve_account data is ours.
			$retrieve_key = hash( 'md5', microtime() . AUTH_SALT . rand( 0, PHP_INT_MAX ) );
			set_site_transient( static::$account_retrieve_transient_key, $retrieve_key, 60 );
			$result = MyCryptoCheckout()->api()->send_post( 'account/retrieve',
				[
					'domain' => base64_encode( MyCryptoCheckout()->get_server_name() ),
					'plugin_version' => MYCRYPTOCHECKOUT_PLUGIN_VERSION,
					'retrieve_key' => $retrieve_key,
				] );
			if ( ! $result )
				throw new Exception( 'No valid answer from the API server.' );

			// Clear the option caches, since the options are modified in another thread (due to the api server communicating with back with us).
			$option_key = MyCryptoCheckout()->fix_option_name( static::$account_data_site_option_key );
			$cache_key = get_current_network_id() . ':' . $option_key;
			wp_cache_delete( $cache_key, 'site-options' );
			wp_cache_delete( $option_key, 'options' );
			$this->load_data();
			MyCryptoCheckout()->debug( 'Account updated from server.' );
			if ( ! $this->is_valid() )
				throw new Exception( 'Unable to retrieve new account data.' );
			if ( ! $this->get_domain_key() )
				throw new Exception( 'New account data does not contain the domain key.' );
			return true;
		}
		catch ( Exception $e )
		{
			MyCryptoCheckout()->debug( 'WARNING: Unable to retrieve our account details: %s', $e->get_message() );
			return false;
		}
	}
}
