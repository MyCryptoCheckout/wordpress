<?php

namespace plainview\sdk_mcc\wordpress\row_actions;

class Row
	extends \plainview\sdk_mcc\collections\Collection
{
	/**
		@brief		The optional main action which is displayed on the first line.
		@since		2015-12-21 23:56:41
	**/
	public $main = null;

	/**
		@brief		The class that created this row.
		@since		2015-12-21 23:27:27
	**/
	public $parent;

	/**
		@brief		The base URL which the urls all work with.
		@since		2015-12-21 23:41:41
	**/
	public $url = null;

	/**
		@brief		Create the row.
		@since		2015-12-21 23:27:15
	**/
	public function __construct( $parent )
	{
		$this->parent = $parent;
	}

	/**
		@brief		Output this row as a string.
		@since		2015-12-21 23:32:03
	**/
	public function __toString()
	{
		$sorted = $this->sort_by( function( $action )
		{
			return $action->get_sort_order() . ' ' . $action->text;
		} );

		$actions = [];
		foreach( $sorted as $action )
			$actions []= $action . '';

		// Put separators between.
		$actions = implode( ' | ', $actions );

		$r = '';

		if ( $this->main !== null )
			$r .= $this->main;

		$r .= sprintf( '<div class="row-actions">%s</div>', $actions );

		return $r;
	}

	/**
		@brief		Create a new action.
		@since		2015-12-21 23:26:23
	**/
	public function action( $id )
	{
		if ( ! $this->has( $id ) )
		{
			$action = new Action( $this );
			$this->set( $id, $action );
		}

		return $this->get( $id );
	}

	/**
		@brief		Return the main action.
		@since		2015-12-21 23:57:17
	**/
	public function main()
	{
		if ( ! is_object( $this->main ) )
			$this->main = new Main( $this );
		return $this->main;
	}

	/**
		@brief		Set the url.
		@details	If the url is an array, we will run add_query_arg on it.
		@since		2015-12-21 23:42:05
	**/
	public function url( $url )
	{
		if ( is_array( $url ) )
			$url = add_query_arg( $url );
		$this->url = $url;
		return $this;
	}
}
