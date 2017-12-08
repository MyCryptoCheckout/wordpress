<?php

namespace plainview\sdk_mcc\traits;

/**
	@brief		Generic sorting priority methods.
	@details	It is up to the container to sort the items.
	@since		2015-12-26 14:21:38
**/
trait sort_order
{
	/**
		@brief		The sort order.
		@details	10 comes before 100.
		@since		2015-12-26 14:22:12
	**/
	public $sort_order = 50;

	/**
		@brief		Return the sort order.
		@since		2015-12-26 14:22:12
	**/
	public function get_sort_order()
	{
		return $this->sort_order;
	}

	/**
		@brief		Set the sort order.
		@since		2015-12-26 15:40:48
	**/
	public function set_sort_order( $sort_order )
	{
		$this->sort_order = $sort_order;
		return $this;
	}

	/**
		@brief		Convenience method to either get or set the sort order.
		@since		2015-12-25 16:04:44
	**/
	public function sort_order( $sort_order = null )
	{
		if ( $sort_order === null )
			return $this->get_sort_order();
		else
			return $this->set_sort_order( $sort_order );
	}
}
