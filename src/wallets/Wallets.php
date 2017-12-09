<?php

namespace mycryptocheckout\wallets;

/**
	@brief		A collection of wallets.
	@details	This handles all of the smart handling of wallets and is the object stored in the site options.
	@since		2017-12-09 09:02:26
**/
class Wallets
	extends \mycryptocheckout\Collection
{
	use \plainview\sdk_mcc\wordpress\object_stores\Site_Option;

	/**
		@brief		Add this wallet to the collection.
		@since		2017-12-09 19:03:16
	**/
	public function add( $wallet )
	{
		$key = md5( $wallet->currency . $wallet->address . AUTH_SALT );
		return $this->append( $wallet );
	}

	/**
		@brief		Create a new wallet.
		@details	Does not add it to the collection.
		@see		add()
		@since		2017-12-09 19:02:44
	**/
	public function new_wallet()
	{
		return new Wallet();
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
		return '';
	}
}
