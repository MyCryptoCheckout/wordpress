<?php

namespace mycryptocheckout\api;

/**
	@brief		This component handles the account.
	@since		2017-12-11 19:16:22
**/
class Account
	extends Component
{
	/**
		@brief		The site option name under which the account data is stored.
		@since		2017-12-11 20:09:11
	**/
	public static $account_data_site_option_key = 'account_data';

	/**
		@brief		Load the locally stored account data, using the Account_Data object.
		@since		2017-12-12 11:04:04
	**/
	public function get()
	{
		$account_data = MyCryptoCheckout()->get_site_option( static::$account_data_site_option_key );
		$r = new Account_Data();
		$r->load( $account_data );
		return $r;
	}

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
			$account_data = [
				'retrieve_key' => $retrieve_key,
			];
			$account_data = json_encode( $account_data );
			MyCryptoCheckout()->update_site_option( 'account_data', $account_data );

			$api_url = sprintf( 'account/retrieve/%s/%s',
				base64_encode( MyCryptoCheckout()->get_server_name() ),
				$retrieve_key
			);
			$result = $this->api->send_get( $api_url );
			if ( ! $result )
				throw new Exception( 'No valid answer from the API server.' );

			// Clear the option caches, since the options are modified in another thread (due to the api server communicating with back with us).
			$option_key = MyCryptoCheckout()->fix_option_name( static::$account_data_site_option_key );
			$cache_key = get_current_network_id() . ':' . $option_key;
			wp_cache_delete( $cache_key, 'site-options' );
			wp_cache_delete( $option_key, 'options' );
			$account_data = MyCryptoCheckout()->get_site_option( static::$account_data_site_option_key );
			$account_data = json_decode( $account_data );
			if ( ! $account_data )
				throw new Exception( 'Unable to retrieve new account data.' );
			if ( ! isset( $account_data->domain_key ) )
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
