<?php

namespace plainview\sdk_mcc\wordpress\tabs;

/**
	@brief		A nav tab.
	@since		2015-12-27 12:37:46
**/
class Nav_Tab
	extends tab
{
	public $tag = 'a';

	/**
		@brief		Return the link.
		@since		2015-12-27 13:13:20
	**/
	public function display_link()
	{
		$text = $this->display_name();

		if ( $this->count > 0 )
			$text .= sprintf( ' <span class="count">%s</span>', $this->count );

		return $text;
	}

	/**
		@brief		Open the tag.
		@since		2015-12-27 14:10:46
	**/
	public function open_tag()
	{
		$this->css_class( 'nav-tab' );

		if ( $this->current )
			$this->css_class( 'nav-tab-active' );

		$this->set_attribute( 'href', $this->url );

		$this->title( $this->title );

		return parent::open_tag();
	}
}
