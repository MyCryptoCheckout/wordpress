<?php

namespace mycryptocheckout;

trait menu_trait
{
	/**
		@brief		Init!
		@since		2017-12-07 19:34:05
	**/
	public function init_menu_trait()
	{
		$this->add_action( 'admin_menu' );
	}

	/**
		@brief		Admin menu callback.
		@since		2017-12-07 19:35:46
	**/
	public function admin_menu()
	{
	}
}
