<?php

namespace mycryptocheckout\currencies;

/**
	@brief		Manage the currency groups.
	@since		2018-02-23 15:26:33
**/
class Groups
	extends \mycryptocheckout\Collection
{
	/**
		@brief		Load all of the groups from this collection of currencies.
		@since		2018-02-23 15:27:40
	**/
	public static function load( $collection )
	{
		$r = new static();
		foreach( $collection as $currency_id => $currency )
		{
			$group = $currency->get_group();
			$r->set( $group->name, $group );
		}
		return $r;
	}

	/**
		@brief		Sort us by group sort order.
		@since		2018-02-23 15:31:11
	**/
	public function sort_now()
	{
		$this->sort_by( function( $item )
		{
			return $item->sort_order . '_' . $item->name;
		} );
	}
}
