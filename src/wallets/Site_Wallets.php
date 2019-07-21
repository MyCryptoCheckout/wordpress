<?php

namespace mycryptocheckout\wallets;

/**
	@brief		Wallets stored in the site.
	@since		2019-07-16 21:11:50
**/
class Site_Wallets
	extends Wallets
{
	use \plainview\sdk_mcc\wordpress\object_stores\Site_Option
	{
		save as trait_save;
	}

	/**
		@brief		Return the container that stores this object.
		@since		2015-10-23 10:54:49
	**/
	public static function store_container()
	{
		return MyCryptoCheckout();
	}

	/**
		@brief		Return the storage key.
		@details	Key / ID.
		@since		2016-01-02 01:03:18
	**/
	public static function store_key()
	{
		return 'wallets';
	}
}
