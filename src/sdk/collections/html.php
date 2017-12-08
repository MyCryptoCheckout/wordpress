<?php

namespace plainview\sdk_mcc\collections;

/**
	@brief		A collection of strings that are outputted with wpautop.
	@details	The main method is append(). get() and set() aren't really used.
	@since		2014-05-04 13:10:54
**/
class html
	extends collection
{
	/**
		@brief		Converts all of the items to a string.
		@since		2014-05-04 13:09:23
	**/
	public function __toString()
	{
		$r = implode( "\n", $this->items );
		$r = \plainview\sdk_mcc\base::wpautop( $r );
		return $r;
	}

	/**
		@brief		Appends a new html string to the collection.
		@since		2014-05-04 13:08:18
	**/
	public function append( $item )
	{
		$args = func_get_args();
		$text = @ call_user_func_array( 'sprintf', $args );
		if ( $text == '' )
			$text = $args[ 0 ];
		return parent::append( $text );
	}

	/**
		@brief		Convenience function to add a newline.
		@since		2015-11-06 17:41:46
	**/
	public function newline()
	{
		$this->append( '' );
	}
}
