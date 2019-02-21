<?php

namespace mycryptocheckout\autosettlements;

/**
	@brief		A collection of autosettlement settings.
	@since		2019-02-21 19:30:16
**/
class Autosettlements
	extends \mycryptocheckout\Collection
{
	use \plainview\sdk_mcc\wordpress\object_stores\Site_Option;

	/**
		@brief		Add this autosettlement.
		@since		2019-02-21 19:30:16
	**/
	public function add( $autosettlement )
	{
		$key = md5( microtime() );
		$this->set( $key, $autosettlement );
		return $key;
	}

	/**
		@brief		Return the types of autosettlements we offer, as a select input array.
		@since		2019-02-21 19:43:03
	**/
	public function get_types_as_options()
	{
		return [
			'bittrex' => 'Bittrex',
		];
	}

	/**
		@brief		Convenience method to return a new autosettlement object.
		@since		2019-02-21 19:55:58
	**/
	public function new_autosettlement()
	{
		return new Autosettlement();
	}

	/**
		@brief		Return the container that stores this object.
		@since		2019-02-21 19:43:03
	**/
	public static function store_container()
	{
		return MyCryptoCheckout();
	}

	/**
		@brief		Return the storage key.
		@details	Key / ID.
		@since		2019-02-21 19:43:03
	**/
	public static function store_key()
	{
		return 'autosettlements';
	}
}
