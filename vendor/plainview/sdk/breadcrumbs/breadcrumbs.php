<?php

namespace plainview\sdk_mcc\breadcrumbs;

/**
	@brief		Breadcrumb handling class.
	@details

	Produces a ul list of breadcrumbs: li items.

	@par		How to use breadcrumbs

	$bcs = new \plainview\sdk_mcc\breadcrumbs;

	$bcs->breadcrumb( 'start )
		->label( 'Start page' )
		->title( 'Go to the start page' )
		->url( 'http://plainview.se/start' );

	$bcs->breadcrumb( 'current' )
		->label( 'Your profile' )
		->title( 'You are here' )
		->url( 'http://plainview.se/profile' );

	$bcs->breadcrumb( 'current' )
		->css_class( 'current_page' );

	echo $bcs;

	@since		20130729
	@version	20130729
**/
class breadcrumbs
	implements \Countable
{
	use \plainview\sdk_mcc\html\element;

	public $tag = 'ul';

	/**
		@brief		The breadcrumb objects array.
		@since		20130729
		@var		$breadcrumbs
	**/
	public $breadcrumbs = [];

	/**
		@brief		Constructor.
		@since		20130729
	**/
	public function __construct()
	{
		$this->css_class( 'plainview breadcrumbs' );
		$this->_construct();
	}

	/**
		@brief		This method is called after completing construction.
		@details	This method exists so that one does not have to remember to parent::__construct() when overloading.
		@since		20130729
	**/
	public function _construct()
	{
	}

	/**
		@brief		Outputs the breadcrumbs HTML string.
		@since		20130729
	**/
	public function __toString()
	{
		$r = $this->open_tag();
		foreach( $this->breadcrumbs as $b )
			$r .= $b;
		$r .= $this->close_tag();
		return $r;
	}

	/**
		@brief		Retrieve an existing or create a new breadcrumb object.
		@detail		Use a unique ID to help retrieve breadcrumbs for later use. Or leave the ID empty to create a new breadcrumb with a random ID.
		@param		string		$id		The ID of the breadcrumb to create or retrieve.
		@since		20130729
	**/
	public function breadcrumb( $id = null )
	{
		if ( $id === null )
			$id = microtime();
		if ( isset( $this->breadcrumbs[ $id ] ) )
			return $this->breadcrumbs[ $id ];
		$this->breadcrumbs[ $id ] = $this->new_breadcrumb( $id );
		return $this->breadcrumbs[ $id ];
	}

	/**
		@brief		Return the count of breadcrumbs.
		@return		int		The count of breadcrumbs.
		@since		20130729
	**/
	public function count() : int
	{
		return count( $this->breadcrumbs );
	}

	/**
		@brief		Create a new breadcrumb with a specific ID.
		@details	This method exists so that subclasses can create their own types of breadcrumbs.
		@param		string		$id		ID of new breadcrumb.
		@since		20130729
	**/
	public function new_breadcrumb( $id )
	{
		return new breadcrumb( $id );
	}
}
