<?php

namespace plainview\sdk_mcc\wordpress\menu_page;

/**
	@brief		Standard menu attributes.
	@since		2015-12-26 19:25:38
**/
trait Attributes
{
	/**
		@brief		The callback. Either a function or an array.
		@since		2015-12-26 20:12:17
	**/
	public $callback;

	/**
		@brief		The capability.
		@details	The default is for those who can edit posts.
		@since		2015-12-26 19:27:00
	**/
	public $capability = 'edit_posts';

	/**
		@brief		The menu slug.
		@since		2015-12-26 19:26:48
	**/
	public $menu_slug;

	/**
		@brief		The label in the menu.
		@since		2015-12-26 19:25:48
	**/
	public $menu_title;

	/**
		@brief		The page title.
		@since		2015-12-26 20:11:51
	**/
	public $page_title;

	/**
		@brief		Set the callback.
		@since		2015-12-26 20:12:43
	**/
	public function callback( $callback )
	{
		$this->callback = $callback;
		return $this;
	}

	/**
		@brief		Set the callback back to this class.
		@since		2015-12-26 20:13:15
	**/
	public function callback_this( $method )
	{
		// Retrieve the class object that called this.
		$trace = debug_backtrace();
		array_shift( $trace );
		$trace = reset( $trace );
		$class = $trace[ 'object' ];
		return $this->callback( [ $class, $method ] );
	}

	/**
		@brief		Set the capability.
		@since		2015-12-26 20:13:50
	**/
	public function capability( $capability )
	{
		$this->capability = $capability;
		return $this;
	}

	/**
		@brief		Set the menu slug.
		@since		2015-12-26 20:40:39
	**/
	public function menu_slug( $menu_slug )
	{
		$this->menu_slug = $menu_slug;
		return $this;
	}

	/**
		@brief		Set the menu title.
		@since		2015-12-26 20:13:50
	**/
	public function menu_title( $menu_title )
	{
		$this->menu_title = $menu_title;
		return $this;
	}

	/**
		@brief		Set the page title.
		@since		2015-12-26 20:13:50
	**/
	public function page_title( $page_title )
	{
		$this->page_title = $page_title;
		return $this;
	}
}
