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
	}

	/**
		@brief		Load our currencies.
		@since		2018-03-11 21:44:32
	**/
	public function mycryptocheckout_get_currencies( $action )
	{
		$currencies = $action->currencies;		// Convenience

		$namespace = get_class( $currencies );
		$namespace = substr( $namespace, 0, strrpos( $namespace, "\\" ) );

		// The main and ERC20 tokens are handled separately due to the different namespace.

		// Main currencies.
		foreach( [
			'BCH',
			'BTC',
			'DASH',
			'ETH',
			'LTC',
		] as $blockchain )
		{
			$class = $namespace . '\\' . $blockchain;
			$currencies->add( new $class() );
		}

		// ERC20 tokens.
		foreach( [
			'BAT',
			'BNT',
			'DGD',
			'EOS',
			'FUN',
			'GNT',
			'ICX',
			'KNC',
			'MKR',
			'OMG',
			'PPT',
			'QASH',
			'QTUM',
			'REP',
			'SNT',
			'STAKE',
			'TRX',
			'ZRX',
		] as $token )
		{
			$class = $namespace . '\\erc20\\' . $token;
			$currencies->add( new $class() );
		}
	}
}
