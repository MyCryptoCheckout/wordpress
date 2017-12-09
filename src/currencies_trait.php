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
		$this->__currencies->load();
		return $this->__currencies;
	}
}
