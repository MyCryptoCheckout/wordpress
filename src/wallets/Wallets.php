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
	use \plainview\sdk_mcc\wordpress\object_stores\Site_Option
	{
		save as trait_save;
	}

	/**
		@brief		Add this wallet to the collection.
		@since		2017-12-09 19:03:16
	**/
	public function add( $wallet )
	{
		$key = md5( $wallet->get_currency_id() . $wallet->get_address() . AUTH_SALT );
		return $this->set( $key, $wallet );
	}

	/**
		@brief		Return all wallets that are enabled on this site.
		@since		2017-12-10 19:13:02
	**/
	public function enabled_on_this_site()
	{
		$r = [];
		foreach( $this as $wallet )
			if ( $wallet->is_enabled_on_this_site() )
				$r []= $wallet;
		return $r;
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
		@brief		Before a save, sort the wallets.
		@since		2017-12-14 08:43:35
	**/
	public function save()
	{
		$currencies = MyCryptoCheckout()->currencies();
		$this->sortBy( function( $wallet )
		use ( $currencies )
		{
			$currency = $currencies->get( $wallet->get_currency_id() );
			return sprintf( '%s_%s', $currency->get_name(), $wallet->get_address() );
		} );
		$this->trait_save();
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
