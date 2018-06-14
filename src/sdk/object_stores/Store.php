<?php

namespace plainview\sdk_mcc\object_stores;

use \Exception;

/**
	@brief		Main class for handling stores.
	@since		2016-01-02 01:00:09
**/
trait Store
{
	/**
		@brief		Delete the object completely.
		@since		2015-10-23 10:54:49
	**/
	public function delete()
	{
		throw new Exception( 'Please override the delete_from_store method.' );
	}

	/**
		@brief		Return the name of the property that should be used as the cached object.
		@since		2017-10-15 20:40:40
	**/
	public static function get_cache_property_key()
	{
		$key = static::store_key();
		// We use the class in case two classes have the same key.
		$class = str_replace( '\\', '', __CLASS__ );
		return '__' . $class . $key;
	}

	/**
		@brief		Load the object from the store.
		@since		2015-10-22 22:16:03
	**/
	public static function load()
	{
		// Conv
		$container = static::store_container();

		$__key = static::get_cache_property_key();

		// Does the object already exist in the container cache?
		if ( isset( $container->$__key ) )
			return $container->$__key;

		// Try to load the object from the store.
		$key = static::store_key();
		$r = static::load_from_store( $key );

		$r = maybe_unserialize( $r );
		if ( ! is_object( $r ) )
		{
			// Backwards compatability: The Options_Object used to base64 encode the object.
			$r = base64_decode( $r );
			$r = maybe_unserialize( $r );
			// If there is still no object, just create a new one.
			if ( ! is_object( $r ) )
				$r = new static();
		}

		// Save to the cache.
		$container->$__key = $r;

		return $r;
	}

	/**
		@brief		Internal method to try to load the object from the store.
		@since		2016-01-02 01:15:39
	**/
	public static function load_from_store( $key )
	{
		throw new Exception( 'Please override the load_from_store method.' );
	}

	/**
		@brief		For a reload of the object from disk.
		@details	This can be used either the first time the store is loaded or by forcing it to be reloaded once cached.
		@since		2017-10-15 20:44:29
	**/
	public static function reload()
	{
		$container = static::store_container();
		$__key = static::get_cache_property_key();
		if ( isset( $container->$__key ) )
			unset( $container->$__key );
		return static::load();
	}

	/**
		@brief		Save the object to the store.
		@since		2016-01-02 01:29:20
	**/
	public function save()
	{
		throw new Exception( 'Please override the store_key method.' );
	}

	/**
		@brief		Return the container that stores this object.
		@since		2015-10-23 10:54:49
	**/
	public static function store_container()
	{
		throw new Exception( 'Please override the store_container method.' );
	}

	/**
		@brief		Return the storage key.
		@details	Key / ID.
		@since		2016-01-02 01:03:18
	**/
	public static function store_key()
	{
		throw new Exception( 'Please override the store_key method.' );
	}

}
