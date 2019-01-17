<?php

namespace plainview\sdk_mcc\wordpress\tabs;

/**
	@brief		Uses subsubsub tabs.
	@since		2015-12-27 12:37:46
**/
class Subsubsub_Tabs
	extends tabs
{
	public function _construct()
	{
		$this->css_class( 'subsubsub' );
		$this->css_class( 'plainview_sdk_tabs' );
	}

	/**
		@brief		Create a specialized tab.
		@since		2015-12-27 12:50:06
	**/
	public function create_tab()
	{
		return new Subsubsub_Tab( $this );
	}

	/**
		@brief		The separator is a line.
		@since		2015-12-27 13:11:27
	**/
	public function get_separator()
	{
		return '&nbsp;|&nbsp;';
	}
}
