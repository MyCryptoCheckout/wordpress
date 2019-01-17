<?php

namespace plainview\sdk_mcc\wordpress\tabs;

/**
	@brief		A subsubsub tab.
	@since		2015-12-27 12:37:46
**/
class Subsubsub_Tab
	extends tab
{
	/**
		@brief		Return the link.
		@since		2015-12-27 13:13:20
	**/
	public function display_link()
	{
		$r = new \plainview\sdk_mcc\html\div();

		$r->tag = 'a';

		if ( $this->current )
			$r->css_class( 'current' );

		$r->set_attribute( 'href', $this->url );

		$text = $this->display_name();

		if ( $this->count > 0 )
			$text .= sprintf( ' <span class="count">%s</span>', $this->count );

		$r->title( $this->title );

		$r->content( $text );

		return $r . '';
	}
}
