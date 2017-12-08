<?php

namespace plainview\sdk_mcc\wordpress\object_stores;

/**
	@brief		The object is stored as an option on the current blog.
	@since		2016-01-02 01:19:06
**/
trait Blog_Option
{
	use Option;

	public static function delete()
	{
		static::store_container()->delete_local_option( static::store_key() );
	}

	public static function load_from_store( $key )
	{
		return static::store_container()->get_local_option( static::store_key(), false );
	}

	public function save()
	{
		static::store_container()->update_local_option( static::store_key(), $this );
	}
}
