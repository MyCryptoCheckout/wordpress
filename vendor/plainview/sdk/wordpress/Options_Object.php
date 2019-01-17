<?php

namespace plainview\sdk_mcc\wordpress;

/**
	@brief		An object that stores itself in the (site) options table.
	@details	Override the container() and get_option_name() methods and you're good to go.

	Use the load() and save() methods to update the object in the db.

	@since		2015-10-24 08:10:05
**/
class Options_Object
	extends \plainview\sdk_mcc\collections\collection
{
	/**
		@brief		Return the Wordpress SDK base container class.
		@since		2015-10-22 23:00:14
	**/
	public static function container()
	{
		throw new Exception( 'Please override the container method.' );
	}

	/**
		@brief		Delete the object completely.
		@since		2015-10-23 10:54:49
	**/
	public static function delete()
	{
		static::container()->delete_option( static::get_option_name() );
	}

	/**
		@brief		Return the name of the option in which we are stored.
		@since		2015-10-22 23:02:42
	**/
	public static function get_option_name()
	{
		throw new Exception( 'Please override the get_option_name method.' );
	}

	/**
		@brief		Load the object from the options table.
		@since		2015-10-22 22:16:03
	**/
	public static function load()
	{
		$container = static::container();
		$option = static::get_option_name();
		$__option = '__' . $option;

		// Does the object already exist in the container cache?
		if ( isset( $container->$__option ) )
			return $container->$__option;

		// Try to load the object from the database.
		$r = $container->get_site_option( $option, '' );

		$r = maybe_unserialize( $r );
		if ( ! is_object( $r ) )
		{
			// Maybe it is base64 encoded?
			$r = @ base64_decode( $r );
			$r = maybe_unserialize( $r );

			if ( ! is_object( $r ) )
				$r = new static();
		}

		// Save to the cache.
		$container->$__option = $r;

		return $r;
	}

	/**
		@brief		Save the object to the database.
		@since		2015-10-22 23:04:21
	**/
	public function save()
	{
		$data = serialize( $this );
		$this->container()->update_site_option( $this->get_option_name(), $data );
	}
}
