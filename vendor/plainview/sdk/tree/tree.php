<?php

namespace plainview\sdk_mcc\tree;

/**
	@brief		Tree of nodes containing unique IDs and data.
	@since		20131208
**/
class tree
	implements \Countable
{
	/**
		@brief		Collection of nodes.
		@since		20131209
	**/
	public $nodes;

	/**
		@brief		Collection of orphan objects that have been added before the parent.
		@since		20131209
	**/
	public $orphans;

	/**
		@brief		The root node. Contains no data itself.
		@since		20131209
	**/
	public $root;

	public function __construct()
	{
		$this->root = $this->new_node();
		$this->root->set_tree( $this );
		$this->nodes = new \plainview\sdk_mcc\collections\collection;
		$this->orphans = new orphans;
		$this->orphans->tree = $this;
	}

	public function __toString()
	{
		$r = '';
		$r .= $this->root;
		return $r;
	}

	/**
		@brief		Add data, with a unique ID, to the tree. Optionally specify the ID of the parent node.
		@since		20131209
	**/
	public function add( $id, $data, $parent_id = null )
	{
		// Does this node already exist? Update the data.
		if ( $this->nodes->has( $id ) )
		{
			$this->nodes->get( $id )->data = $data;;
			return $this;
		}

		$node = $this->new_node();
		$node->set_tree( $this );
		$node->set_id( $id );
		$node->set_data( $data );

		if ( $parent_id === null )
		{
			// Add the node
			$this->root->add( $node );
			$this->nodes->set( $node->id, $node );
			// Add any orphans that might exist for this parent node.
			$this->add_orphans( $node->id );
		}
		else
		{
			// Does the parent exist?
			$parent = $this->node( $parent_id );
			if ( $parent !== null )
			{
				// Parent exists. Add it and then any other orphans that have this node as the parent.
				$parent->add( $node );
				$this->nodes->set( $node->id, $node );
				$this->add_orphans( $node->id );
			}
			else
			{
				// No. Add the node as an orphan, ready to be automatically added when the parent shows up.
				$orphan = new orphan;
				$orphan->set_node ( $node );
				$orphan->set_parent_id( $parent_id );
				$this->orphans->add( $orphan );
			}
		}
	}

	/**
		@brief
		@since		20131209
	**/
	public function add_orphans( $parent_id )
	{
		// Do any of the orphans have this node as a parent?
		if ( ! $this->orphans->has( $parent_id ) )
			return;

		foreach( $this->orphans->get( $parent_id ) as $orphan )
		{
			$this->orphans->remove( $orphan );
			$this->add( $orphan->get_id(), $orphan->get_node()->get_data(), $parent_id );
		}
	}

	/**
		@brief		Count the number of nodes in the tree.
		@since		20131209
	**/
	public function count()
	{
		// -1 because root isn't really a node.
		return count( $this->root ) - 1;
	}

	/**
		@brief		Create a new node.
		@details	Can be overridden by child classes.
		@since		20131209
	**/
	public function new_node()
	{
		return new node;
	}

	/**
		@brief		Retrieve the node with this ID.
		@return		The node object, or null if the ID does not exist.
		@since		20131209
	**/
	public function node( $id )
	{
		if ( ! $this->nodes->has( $id ) )
			return null;
		return $this->nodes->get( $id );
	}

	/**
		@brief		Sort the whole tree using the node ID.
		@since		20131209
	**/
	public function sort()
	{
		$this->sort_by( function( $node )
		{
			return $node->id;
		} );
	}

	/**
		@brief		Sort the tree using the specified sorting function.
		@since		20131209
	**/
	public function sort_by( $function )
	{
		$this->root->sort_by( $function );
	}
}
