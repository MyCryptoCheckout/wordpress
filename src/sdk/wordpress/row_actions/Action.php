<?php

namespace plainview\sdk_mcc\wordpress\row_actions;

class Action
{
	use \plainview\sdk_mcc\traits\sort_order;

	/**
		@brief		Which row is our parent.
		@since		2015-12-21 23:31:48
	**/
	public $parent;

	/**
		@brief		The visible text of the action.
		@see		_()
		@see		text()
		@since		2015-12-21 23:40:53
	**/
	public $text = '';

	/**
		@brief		The visible text of the action.
		@see		title()
		@see		title_()
		@since		2015-12-21 23:39:31
	**/
	public $title = '';

	/**
		@brief		The URL of the action.
		@see		url()
		@since		2015-12-21 23:39:47
	**/
	public $url = '';

	/**
		@brief		Constructor.
		@since		2015-12-21 23:31:33
	**/
	public function __construct( $parent )
	{
		$this->parent = $parent;
	}

	/**
		@brief		Convert to a string.
		@since		2015-12-21 23:34:00
	**/
	public function __toString()
	{
		$r = sprintf( '<a href="%s" title="%s">%s</a>', $this->url, $this->title, $this->text );
		return $r;
	}

	/**
		@brief		Sets the translated title. Attempts to translate the title first before setting it.
		@since		2015-12-21 23:42:21
	**/
	public function _( $string )
	{
		$args = func_get_args();
		$text = call_user_func_array( [ $this->parent->parent, '_' ], $args );
		if ( $text == '' )
			$text = $string;
		$this->text( $text );
		return $this;
	}

	/**
		@brief		Return an array of the properties that we use.
		@since		2015-12-22 14:31:21
	**/
	public static function properties()
	{
		return [ 'text', 'title', 'url' ];
	}

	/**
		@brief		Copy the properties from another action.
		@since		2015-12-22 14:30:11
	**/
	public function same_as( $id )
	{
		if ( ! $this->parent->has( $id ) )
			return;
		$action = $this->parent->get( $id );

		foreach( static::properties() as $key )
			$this->$key = $action->$key;

		return $this;
	}

	/**
		@brief		Sets the visible text of the action.
		@since		2015-12-21 23:44:05
	**/
	public function text( $text )
	{
		$this->text = $text;
		return $this;
	}

	/**
		@brief		Set the title of the URL.
		@since		2015-12-21 23:38:40
	**/
	public function title( $title )
	{
		$this->title = $title;
		return $this;
	}

	/**
		@brief		Sets the translated title. Attempts to translate the title first before setting it.
		@since		2015-12-21 23:42:21
	**/
	public function title_( $string )
	{
		$args = func_get_args();
		$title = call_user_func_array( [ $this->parent->parent, '_' ], $args );
		if ( $title == '' )
			$title = $string;
		$this->title( $title );
		return $this;
	}

	/**
		@brief		Set the url.
		@details	If the url is an array, we will run add_query_arg on it.
		@since		2015-12-21 23:42:05
	**/
	public function url( $url )
	{
		if ( is_array( $url ) )
		{
			// We have to handle add_query_arg differently depending on whether there is a base url or not.
			if ( $this->parent->url == '' )
				$url = add_query_arg( $url );
			else
				$url = add_query_arg( $url, $this->parent->url );
		}
		$this->url = $url;
		return $this;
	}
}
