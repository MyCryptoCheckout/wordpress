<?php

namespace plainview\sdk_mcc\wordpress\object_stores;

/**
	@brief		The object is stored as a site option.
	@since		2016-01-02 01:19:06
**/
trait Site_Option
{
	use Option;

	public static function delete()
	{
		static::store_container()->delete_site_option( static::store_key() );
	}

	public static function load_from_store( $key )
	{
		return static::store_container()->get_site_option( static::store_key(), false );
	}

	public function save()
	{
		static::store_container()->update_site_option( static::store_key(), $this );
	}
}
