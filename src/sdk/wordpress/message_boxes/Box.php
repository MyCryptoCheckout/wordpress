<?php

namespace plainview\sdk_mcc\wordpress\message_boxes;

/**
	@brief		The base message class.
	@since		2015-12-21 20:29:00
**/
class Box
{
	/**
		@brief		The parent class that created this box.
		@since		2015-12-21 20:32:24
	**/
	public $parent;

	/**
		@brief		Constructor.
		@since		2015-12-21 20:32:07
	**/
	public function __construct( $parent )
	{
		$this->parent = $parent;
	}

	/**
		@brief		Return a message box with this error message.
		@since		2015-12-21 20:33:24
	**/
	public function _()
	{
		$args = func_get_args();
		$string = @ call_user_func_array( [ $this->parent, '_' ], $args );
		if ( $string == '' )
			$string = $args[ 0 ];
		return $this->get_box( $string );
	}

	/**
		@brief		Return the message box HTML.
		@since		2015-12-21 20:35:02
	**/
	public function get_box( $string )
	{
		$timestamp = '<p class="message_timestamp">' . $this->parent->now() . "</p>\n";
		$content = wpautop( $timestamp . $string );
		return sprintf( '<div class="%s">%s</div>', $this->get_css_class(), $content );
	}

	/**
		@brief		Return the CSS class string for this type of message box.
		@since		2015-12-21 20:29:18
	**/
	public function get_css_class()
	{
		// Updated is the default.
		return 'updated notice is-dismissible';
	}
}
