<?php

namespace plainview\sdk_mcc\wordpress\object_stores;

/**
	@brief		The object is stored as a blog transient.
	@since		2016-01-02 01:19:06
**/
trait Blog_Transient
{
	use Transient;

	public static function delete()
	{
		delete_transient( static::store_key() );
	}

	public static function load_from_store( $key )
	{
		return get_transient( static::store_key(), false );
	}

	public function save()
	{
		set_transient( static::store_key(), $this, static::get_expiration() );
	}
}
