<?php

namespace mycryptocheckout\currencies;

/**
	@brief		The smart currency collection.
	@since		2017-12-09 19:59:15
**/
class Currencies
	extends \mycryptocheckout\Collection
{
	/**
		@brief		Add a currency.
		@since		2017-12-09 20:03:03
	**/
	public function add( $currency )
	{
		$this->set( $currency->get_id(), $currency );
		return $this;
	}

	/**
		@brief		Load all available currencies.
		@since		2017-12-09 20:02:56
	**/
	public function load()
	{
		$this->add( new BCH() );
		$this->add( new BTC() );
		$this->add( new ETH() );
		$this->add( new LTC() );
	}

	/**
		@brief		Return all of the currencies as an array for a select options class.
		@since		2017-12-09 19:59:39
	**/
	public function as_options()
	{
		$r = [];
		foreach( $this as $currency )
			$r[ $currency->get_id() ] = $currency->get_name();
		return array_flip( $r );
	}
}