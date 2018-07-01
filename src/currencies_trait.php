<?php

namespace mycryptocheckout;

/**
	@brief		Currency related information.
	@since		2017-12-09 09:16:44
**/
trait currencies_trait
{
	/**
		@brief		Return the currencies collection.
		@since		2017-12-09 20:02:05
	**/
	public function currencies()
	{
		if ( isset( $this->__currencies ) )
			return $this->__currencies;

		$this->__currencies = new currencies\Currencies();

		$action = $this->new_action( 'get_currencies' );
		$action->currencies = $this->__currencies;
		$action->execute();

		return $this->__currencies;
	}

	/**
		@brief		Initialize the trait.
		@since		2018-03-11 21:51:56
	**/
	public function init_currencies_trait()
	{
		$this->add_action( 'mycryptocheckout_get_currencies', 5 );		// Before everyone else.
		$this->add_action( 'mycryptocheckout_use_wallet' );
	}

	/**
		@brief		Load our currencies from the account data.
		@since		2018-03-11 21:44:32
	**/
	public function mycryptocheckout_get_currencies( $action )
	{
		$currencies = $action->currencies;		// Convenience

		$account = $account = $this->api()->account();
		foreach( $account->get_currency_data() as $currency_id => $currency_data )
		{
			// Needed for testing.
			if ( isset( $currency_data->beta ) )
				continue;

			$currency = new \mycryptocheckout\currencies\Currency();
			$currency->set_id( $currency_id );
			$currency->set_name( $currency_data->name );

			if ( isset( $currency_data->address_length ) )
				$currency->set_address_length( $currency_data->address_length );

			if ( isset( $currency_data->decimal_precision ) )
				$currency->set_decimal_precision( $currency_data->decimal_precision );

			if ( isset( $currency_data->group ) )
			{
				$group = new \mycryptocheckout\currencies\Group();
				$group->name = $currency_data->group;
				$currency->set_group( $group );
			}

			// Anything that is a supports gets put in.
			foreach( $currency_data as $key => $data )
				if ( strpos( $key, 'supports' ) === 0 )
					$currency->$key = $data;

			$currencies->add( $currency );
		}
	}

	/**
		@brief		mycryptocheckout_use_wallet
		@since		2018-07-01 14:25:15
	**/
	public function mycryptocheckout_use_wallet( $action )
	{
		// Since the currency can't hook anything itself, we have to do it for it.
		$action->currency->use_wallet( $action );
	}
}
