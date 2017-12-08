<?php

namespace plainview\sdk_mcc\wordpress\table\top;

/**
	@brief			The

	@par			Changelog

	- 20140203		Does not display itself if there is nothing to display.
	- 20131019		Initial.

	@author			Edward Plainview		edward@plainview.se
	@copyright		GPL v3
	@since			20131019
**/
class top
{
	public $left;

	public function __construct()
	{
		$this->left = new \plainview\sdk_mcc\collections\collection;
	}

	public function __toString()
	{
		$div = new \plainview\sdk_mcc\html\div;
		$div->css_class( 'tablenav' );
		$div->css_class( 'top' );

		$size = 0;

		foreach( $this->left as $left )
		{
			$l = new \plainview\sdk_mcc\html\div;
			$l->css_class( 'alignleft' );
			$string = $left->__toString();
			$l->content = $string . '&nbsp;';
			$size += strlen( $string );
			$div->content .= $l;
		}

		// No real string to display? Don't display anything.
		if ( $size < 1 )
			return '';

		return $div . '';
	}
}
