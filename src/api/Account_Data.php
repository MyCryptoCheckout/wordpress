<?php

namespace mycryptocheckout\api;

/**
	@brief		The handler for the account data.
	@details	An interface between the stored account_data site option and the programmer.
	@since		2017-12-11 19:30:08
**/
class Account_Data
	extends \mycryptocheckout\Collection
{
	/**
		@brief		The json data we have loaded.
		@since		2017-12-12 11:14:28
	**/
	public $data = [];

	/**
		@brief		Get the domain key.
		@since		2017-12-12 11:18:05
	**/
	public function get_domain_key()
	{
		return $this->data->domain_key;
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
		@brief		Return the amount of purchases left.
		@since		2017-12-12 11:32:47
	**/
	public function get_purchases_left()
	{
		return $this->data->purchases_left;
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
		@brief		Load the data from the json object.
		@since		2017-12-11 19:31:05
	**/
	public function load( $json )
	{
		$data = json_decode( $json );
		if ( ! $data )
			return;
		$this->data = $data;
		foreach( $data as $key => $value )
			$this->set( $key, $value );
		return $this;
	}

	/**
		@brief		Is this account data valid?
		@since		2017-12-12 11:15:12
	**/
	public function is_valid()
	{
		return isset( $this->data->domain_key );
	}
}
