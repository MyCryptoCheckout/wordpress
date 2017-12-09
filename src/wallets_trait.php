<?php

namespace mycryptocheckout;

/**
	@brief		Anything with wallets goes here.
	@since		2017-12-09 18:46:22
**/
trait wallets_trait
{
	/**
		@brief		Return the Wallets object.
		@since		2017-12-09 18:46:36
	**/
	public function wallets()
	{
		if ( isset( $this->__wallets ) )
			return $this->__wallets;

		$this->__wallets = wallets\Wallets::load();
		return $this->__wallets;
	}
}
