<?php

namespace plainview\sdk_mcc\tree;

/**
	@brief		Orphan node.
	@since		20131208
**/
class orphan
{
	public $node;
	public $parent_id;

	/**
		@brief		Retrieve the node's ID.
		@since		20131208
	**/
	public function get_id()
	{
		return $this->node->get_id();
	}

	/**
		@brief		Retrieve the node data for this orphan.
		@since		20131208
	**/
	public function get_node()
	{
		return $this->node;
	}

	/**
		@brief		Retrieve the ID of the orphan's parent.
		@since		20131208
	**/
	public function get_parent_id()
	{
		return $this->parent_id;
	}

	/**
		@brief		Set the node of the orphan.
		@since		20131208
	**/
	public function set_node( $new_node )
	{
		$this->node = $new_node;
		return $this;
	}

	/**
		@brief		Set the ID of this orphan's parent.
		@since		20131208
	**/
	public function set_parent_id( $new_parent_id )
	{
		$this->parent_id = $new_parent_id;
		return $this;
	}
}
