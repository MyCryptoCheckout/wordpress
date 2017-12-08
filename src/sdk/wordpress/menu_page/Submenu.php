<?php

namespace plainview\sdk_mcc\wordpress\menu_page;

class Submenu
{
	use Attributes;
	use \plainview\sdk_mcc\traits\sort_order;

	/**
		@brief		The class that created us.
		@since		2015-12-26 20:19:06
	**/
	public $parent;

	/**
		@brief		Constructor.
		@since		2015-12-26 20:18:46
	**/
	public function __construct( $parent )
	{
		$this->parent = $parent;
	}

	/**
		@brief		Add the submenu to the menu.
		@since		2015-12-26 20:18:13
	**/
	public function add()
	{
		add_submenu_page(
			$this->parent->menu_slug,
			$this->page_title,
			$this->menu_title,
			$this->capability,
			$this->menu_slug,
			$this->callback
		);
		return $this;
	}
}
