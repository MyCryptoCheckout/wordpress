<?php

namespace plainview\sdk_mcc\tree;

/**
	@brief		Node in a tree.
	@since		20131208
**/
class node
	implements \Countable
{
	public $id;
	public $data;
	public $depth = 0;
	public $parent;
	public $subnodes;

	/**
		@brief		The tree object.
		@since		20140106
	**/
	public $tree;

	public function __construct( $parent = null )
	{
		$this->parent = $parent;
		$this->subnodes = new \plainview\sdk_mcc\collections\collection;
	}

	public function __toString()
	{
		$r = '';
		$r .= str_pad( '', $this->depth ) . $this->id . "\n";
		foreach( $this->subnodes as $subnode )
			$r .= $subnode;
		return $r;
	}

	/**
		@brief
		@since		20131209
	**/
	public function add( $node )
	{
		$node->set_parent( $this );
		$this->subnodes->set( $node->id, $node );
		return $this;
	}

	public function count()
	{
		$r = 1;
		foreach( $this->subnodes as $subnode )
			$r += count( $subnode );
		return $r;
	}

	/**
		@brief		Retrieve the node's data.
		@since		20131209
	**/
	public function get_data()
	{
		return $this->data;
	}

	/**
		@brief		Retrieve the depth in the tree.
		@since		20131209
	**/
	public function get_depth()
	{
		return $this->depth;
	}

	/**
		@brief		Retrieve the node's ID.
		@since		20131209
	**/
	public function get_id()
	{
		return $this->id;
	}

	/**
		@brief		Retrieve the parent node.
		@since		20131209
	**/
	public function get_parent()
	{
		return $this->parent;
	}

	/**
		@brief		Set the node's data.
		@since		20131209
	**/
	public function set_data( $new_data )
	{
		$this->data = $new_data;
		return $this;
	}

	/**
		@brief		Set the parent count for this node.
		@since		20131209
	**/
	public function set_depth( $new_depth )
	{
		$this->depth = $new_depth;
		return $this;
	}

	/**
		@brief		Set the ID of this node.
		@since		20131209
	**/
	public function set_id( $new_id )
	{
		$this->id = $new_id;
		return $this;
	}

	/**
		@brief		Set the node's parent node.
		@since		20131209
	**/
	public function set_parent( $new_parent )
	{
		$this->parent = $new_parent;
		$this->depth = $this->parent->get_depth() + 1;
		return $this;
	}

	/**
		@brief		Set the node's tree object.
		@since		20140106
	**/
	public function set_tree( $tree )
	{
		$this->tree = $tree;
		return $this;
	}

	/**
		@brief		Sort this node's subnodes with a specific function.
		@since		20131209
	**/
	public function sort_by( $function )
	{
		$this->subnodes->sortBy( $function );
		foreach( $this->subnodes as $subnode )
			$subnode->sort_by( $function );
	}
}
