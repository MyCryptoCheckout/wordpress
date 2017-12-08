<?php

namespace plainview\sdk_mcc\html;

/**
	@brief		Indentation methods for complex elements (tables, forms, not single HTML elements).
	@details	Classes that use this trait need only override indentation().

	The default indent character / string is a tab.

	@since		20130513
	@version	20130513
**/
trait indentation
{
	/**
		@brief		Return a string of indent padding.
		@return		string		Indent string.
		@see		indent_only()
		@see		indentation()
		@since		20130513
	**/
	public function indent()
	{
		return $this->indent_string( $this->indentation() );
	}

	/**
		@brief		Return the string to be used as padding.
		@return		string		String that should be used as indent padding.
		@since		20130513
	**/
	public function indent_character()
	{
		return "\t";
	}

	/**
		@brief		Return an indent string of only a certain amount of tabs, instead of using indentation().
		@param		int		$count		How many steps to indent.
		@return		string		Indent string.
		@see		indent()
		@since		20130513
	**/
	public function indent_only( $count )
	{
		return $this->indent_string( $count );
	}

	/**
		@brief		Return the string with correct amount of indentation.
		@param		int		$count		How many steps of indentation to return.
		@return		string				String with which to indent.
		@since		20130513
	**/
	public function indent_string( $count )
	{
		$character = $this->indent_character();
		return str_pad( '', $count * strlen( $character ), $this->indent_character() );
	}

	/**
		@brief		How many steps to indent?
		@details	Must be overridden by the class.
		@return		int		How many steps to indent. Default is zero.
		@since		20130513
	**/
	public function indentation()
	{
		return 0;
	}
}

