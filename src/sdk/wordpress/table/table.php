<?php

namespace plainview\sdk_mcc\wordpress\table;

/**
	@brief			Extends the table class by using the Wordpress base class to translate table object names and titles.

	@par			Changelog

	- 20131019		top() added.
	- 20131015		Added bulk_actions();
	- 20130509		Complete rework moving all of the translation to the parent table class. Only _() is overridden.
	- 20130507		Code: td() and th() can return existing cells.
	- 20130506		New: trait has title_().
					Code: Renamed wordpress_table_object to wordpress_table_element.

	@author			Edward Plainview		edward@plainview.se
	@copyright		GPL v3
	@since			20130430
	@version		20131019
**/
class table
	extends \plainview\sdk_mcc\table\table
{
	use \plainview\sdk_mcc\traits\method_chaining;

	/**
		@brief		The \\plainview\\sdk_mcc\\table\\wordpress\\base object that created this class.
	**/
	public $base;

	public function __construct( $base )
	{
		parent::__construct();
		$this->base = $base;
		$this->css_class( 'plainview_sdk_table' );
	}

	/**
		@brief		Use the base's _() method to translate this string. sprintf aware.
		@param		string		$string		String to translate.
		@return		string					Translated string.
	**/
	public function _( $string )
	{
		return call_user_func_array( array( $this->base, '_' ), func_get_args() );
	}

	public function __toString()
	{
		$r = '';
		if ( isset( $this->top ) )
			$r .= $this->top;
		$r .= parent::__toString();

		return $r;
	}

	/**
		@brief		Create the top tablenav row.
		@since		20131019
	**/
	public function top()
	{
		if ( ! isset( $this->top ) )
			$this->top = new top\top;
		return $this->top;
	}

	/**
		@brief		Create the bulk actions in the top of the table.
		@since		20131015
	**/
	public function bulk_actions()
	{
		$top = $this->top();
		if ( ! $top->left->has( 'bulk_actions' ) )
			$top->left->put( 'bulk_actions', new top\bulkactions( $this )  );
		return $top->left->get( 'bulk_actions' );
	}
}
