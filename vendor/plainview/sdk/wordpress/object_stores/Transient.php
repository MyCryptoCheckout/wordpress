<?php

namespace plainview\sdk_mcc\wordpress\object_stores;

/**
	@brief		Master transient class.
	@since		2016-01-02 01:37:54
**/
trait Transient
{
	use Store;

	/**
		@brief		Return the expiration value for this transient.
		@since		2016-01-02 01:38:01
	**/
	public static function get_expiration()
	{
		return DAY_IN_SECONDS;
	}
}
