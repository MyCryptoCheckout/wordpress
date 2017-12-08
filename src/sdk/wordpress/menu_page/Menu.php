<?php

namespace plainview\sdk_mcc\wordpress\menu_page;

/**
	@brief		Class to handle the plugin's menu and submenus.
	@details	After creating, runt set_parent to set the creating class.
	@since		2015-12-26 19:06:47
**/
class Menu
	extends \plainview\sdk_mcc\collections\Collection
{
	use Attributes;

	/**
		@brief		The icon of the menu.
		@since		2015-12-26 20:22:42
	**/
	public $icon_url = '';

	/**
		@brief		The class that created us.
		@since		2015-12-26 19:09:22
	**/
	public $parent;

	/**
		@brief		The position of the menu.
		@since		2015-12-26 20:22:42
	**/
	public $position = null;

	/**
		@brief		Add this menu to the admin panel.
		@details	Will also sort and add the submenus.
		@since		2015-12-26 20:19:54
	**/
	public function add()
	{
		// add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position );
		add_menu_page(
			$this->page_title,
			$this->menu_title,
			$this->capability,
			$this->menu_slug,
			$this->callback,
			$this->icon_url,
			$this->position
		);
		return $this;
	}

	/**
		@brief		Add the menu and submenus to the admin panel.
		@since		2015-12-26 20:25:44
	**/
	public function add_all()
	{
		$this->add();
		$this->add_submenus();
		return $this;
	}

	/**
		@brief		Add just the submenus to the admin panel.
		@since		2015-12-26 20:26:07
	**/
	public function add_submenus()
	{
		// Sort all of the submenus.
		$sorted = $this->sort_by( function( $submenu )
		{
			return $submenu->get_sort_order() . $submenu->menu_title;
		} );

		foreach( $sorted as $submenu )
			$submenu->add();
		return $this;
	}

	/**
		@brief		Set the icon url.
		@since		2015-12-26 20:13:50
	**/
	public function icon_url( $icon_url )
	{
		$this->icon_url = $icon_url;
		return $this;
	}

	/**
		@brief		Set the position.
		@since		2015-12-26 20:13:50
	**/
	public function position( $position )
	{
		$this->position = $position;
		return $this;
	}

	/**
		@brief		Set the parent (class that created us).
		@since		2015-12-26 19:08:19
	**/
	public function set_parent( $parent )
	{
		$this->parent = $parent;
	}

	/**
		@brief		Create or return a submenu page.
		@since		2015-12-26 19:07:27
	**/
	public function submenu( $id )
	{
		if ( ! $this->has( $id ) )
		{
			$submenu = new Submenu( $this );
			$submenu->menu_slug( $id );
			$this->set( $id, $submenu );
		}

		return $this->get( $id );
	}
}
