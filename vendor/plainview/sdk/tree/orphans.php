<?php

namespace plainview\sdk_mcc\tree;

use \plainview\sdk_mcc\collections\collection;

/**
	@brief
	@since		20131208
**/
class orphans
	extends collection
	implements \Countable
{
	public $tree;

	public function __toString()
	{
		$r = '';
		foreach( $this->items as $id => $collection )
		{
			$r .= $id;
			foreach( $collection as $index => $orphan )
				$r .= ' ' . $index;
		}
		return $r;
	}

	/**
		@brief		Add an orphan.
		@since		20131208
	**/
	public function add( $orphan )
	{
		$parent_id = $orphan->get_parent_id();
		if ( ! $this->has( $parent_id ) )
			$this->set( $parent_id, new collection );
		$this->get( $parent_id )->set( $orphan->get_id(), $orphan );
	}

	public function count()
	{
		$r = 0;
		foreach( $this->items as $collection )
			$r += count( $collection );
		return $r;
	}

	/**
		@brief		Remove an orphan.
		@since		20131208
	**/
	public function remove( $orphan )
	{
		$parent_id = $orphan->get_parent_id();

		if ( ! $this->has( $parent_id ) )
			return;

		$collection = $this->get( $parent_id );
		$collection->forget( $orphan->get_id() );
		if ( $collection->is_empty() )
			$this->forget( $parent_id );
	}
}