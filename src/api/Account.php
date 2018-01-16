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
		@brief		The transient key for storing the account retrieval key.
		@since		2017-12-22 00:22:36
	**/
	public static $account_retrieve_transient_key = 'mycryptocheckout_account_retrieve_key';

	/**
		@brief		Constructor.
		@since		2017-12-12 11:04:04
	**/
	public function __construct()
	{
		$this->load_data();
	}

	/**
		@brief		Get the domain key.
		@since		2017-12-12 11:18:05
	**/
	public function get_domain_key()
	{
		return $this->data->domain_key;
	}

	/**
		@brief		Return the date the license expires.
		@return		The time() the license expires, else false.
		@since		2017-12-27 17:26:28
	**/
	public function get_license_valid_until()
	{
		return $this->data->license_valid_until;
	}

	/**
		@brief		Return the payments left.
		@since		2017-12-23 09:03:56
	**/
	public function get_payments_left()
	{
		if ( ! $this->is_valid() )
			return 0;
		return $this->data->payments_left;
	}

	/**
		@brief		Return the payments left as a more descriptive text.
		@since		2018-01-02 00:53:25
	**/
	public function get_payments_left_text()
	{
		if ( $this->has_license() )
			return 'Unlimited';
		else
			return $this->get_payments_left();
	}

	/**
		@brief		Return the payments used.
		@since		2017-12-23 09:03:56
	**/
	public function get_payments_used()
	{
		return intval( $this->data->payments_used );
	}

	/**
		@brief		Convenience method to return a physical exchange rate.
		@since		2017-12-14 17:11:13
	**/
	public function get_physical_exchange_rate( $currency )
	{
		if ( isset( $this->data->physical_exchange_rates->rates->$currency ) )
			return $this->data->physical_exchange_rates->rates->$currency;
		else
			return false;
	}

	/**
		@brief		Convenience method to return a virtual exchange rate.
		@since		2017-12-14 17:11:13
	**/
	public function get_virtual_exchange_rate( $currency )
	{
		if ( isset( $this->data->virtual_exchange_rates->rates->$currency ) )
			return $this->data->virtual_exchange_rates->rates->$currency;
		else
			return false;
	}

	/**
		@brief		Does the account have any payments left this month?
		@since		2017-12-23 08:59:11
	**/
	public function has_payments_left()
	{
		return $this->get_payments_left() > 0;
	}

	/**
		@brief		Is this account licensed?
		@since		2018-01-02 00:54:48
	**/
	public function has_license()
	{
		return $this->data->license_valid;
	}

	/**
		@brief		Is MCC available for payment?
		@return		True if avaiable, else an exception containing the reason why it is not.
		@since		2017-12-23 09:22:12
	**/
	public function is_available_for_payment()
	{
		if ( isset( $this->data->locked ) )
			throw new Exception( 'The account is locked, probably due to a payment not being able to be send to the API server. The account will unlock upon next contact with the API server.' );

		// The account needs payments available.
		if ( ! $this->has_payments_left() )
			throw new Exception( 'Your account does not have any payments left this month. Either wait until next month or purchase an unlimited license using the link on your MyCryptoCheckout settings account page.' );

		// We need at least one wallet.
		$wallets = MyCryptoCheckout()->wallets()->enabled_on_this_site();
		if ( count( $wallets ) < 1 )
			throw new Exception( 'There are no currencies enabled on this site.' );
	}

	/**
		@brief		Return whether this payment amount for this currency has not been used.
		@since		2018-01-06 08:54:49
	**/
	public function is_payment_amount_available( $currency_id, $amount )
	{
		if ( ! isset( $this->data->payment_amounts ) )
			$this->data->payment_amounts = (object)[];
		$pa = $this->data->payment_amounts;
		if ( ! isset( $pa->$currency_id ) )
			$pa->$currency_id = (object)[];
		$r = ! isset( $pa->$currency_id->$amount );
		return $r;
	}

	/**
		@brief		Is this account data valid?
		@since		2017-12-12 11:15:12
	**/
	public function is_valid()
	{
		return isset( $this->data->domain_key );
	}

	/**
		@brief		Lock the account from sending anything new to the API.
		@since		2018-01-16 19:42:08
	**/
	public function lock()
	{
		$this->data->locked = true;
		return $this;
	}

	/**
		@brief		Load the data from the option.
		@since		2017-12-24 11:17:31
	**/
	public function load_data()
	{
		$this->data = (object)[];

		$data = MyCryptoCheckout()->get_site_option( static::$account_data_site_option_key );
		$data = json_decode( $data );
		if ( ! $data )
			$this->data = (object)[];
		else
			$this->data = $data;
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

	/**
		@brief		Save this new data.
		@since		2018-01-16 19:46:51
	**/
	public function save()
	{
		MyCryptoCheckout()->update_site_option( 'account_data', json_encode( $this->data ) );
		return $this;
	}

	/**
		@brief		Set the new data.
		@since		2018-01-16 20:02:38
	**/
	public function set_data( $data )
	{
		$this->data = $data;
		return $this;
	}

}
