<?php

namespace plainview\sdk_mcc\wordpress\tabs;

/**
	@brief		Uses nav tabs CSS.
	@since		2015-12-27 12:37:46
**/
class Nav_Tabs
	extends tabs
{
	public $tag = 'h2';

	public function _construct()
	{
		$this->css_class( 'nav-tab-wrapper' );
		$this->css_class( 'nav-tab-large' );
	}

	/**
		@brief		Create a specialized tab.
		@since		2015-12-27 12:50:06
	**/
	public function create_tab()
	{
		return new Nav_Tab( $this );
	}
}
