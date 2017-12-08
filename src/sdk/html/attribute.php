<?php

namespace plainview\sdk_mcc\html;

use plainview\sdk_mcc\html\exceptions\InvalidKeyException;

/**
	@brief		Attribute handling class.
	@details	Used to more easily manipulate attributes with appendable values (css class, style).

	@par		Changelog

	- 20130514	separator() can now set the separator.
	- 20130506	Initial version.

	@since		20130506
	@version	20130718
**/
class attribute
{
	/**
		@brief		Type of attribute this is: class, style, title, etc.
		@var		$key
		@since		20130506
	**/
	public $key;

	/**
		@brief		Value separator. Style and CSS require something other than an empty string.
		@var		$separator
		@since		20130514
	**/
	public $separator = '';

	/**
		@brief		Array of values this attribute consists of.
		@details	The array is imploded before being used live.
		@var		$value
		@since		20130506
	**/
	public $value;

	public function __construct( $key )
	{
		if ( ! self::is_key_valid( $key ) )
			throw new InvalidKeyException( "Invalid key: $key" );
		$this->key = $key;
		$this->clear();
	}

	/**
		@brief		Add this value to the value array.
		@param		string		Value to add to the array.
		@return		$this		Object chaining.
		@since		20130506
	**/
	public function add( $value )
	{
		$this->value[ $value ] = $value;
		return $this;
	}

	/**
		@brief		Clears / removes all the values for this attribute.
		@return		$this		Object chaining.
		@since		20130506
	**/
	public function clear()
	{
		$this->value = array();
		return $this;
	}

	/**
		@brief		Check that a key is valid.
		@param		string		$key		Key to check for validity.
		@return		boolean True if the key is valid.
		@since		20130718
	**/
	public static function is_key_valid( $key )
	{
		if ( strpos( $key, ' ' ) !== false )
			return false;
		return true;
	}

	/**
		@brief		Remove a value.
		@param		string		Value to remove.
		@return		$this		Object chaining.
		@since		20130506
	**/
	public function remove( $value )
	{
		if ( isset( $this->value[ $value ] ) )
			unset( $this->value[ $value ] );
		return $this;
	}

	/**
		@brief		Get or set the implode separator for this type of attribute.
		@details	Assumes an empty separator most of the time, unless the attribute is a css class or style.
		@param		string			$separator		Separator to set, or null to get.
		@return		this|string						Implode separator or this class.
		@since		20130506
	**/
	public function separator( $separator = null )
	{
		if ( $separator === null )
			return $this->separator;
		$this->separator = $separator;
		return $this;
	}

	/**
		@brief		Clears and sets the value.
		@param		string		$value		New value to set.
		@return		$this		Object chaining.
		@since		20130506
	**/
	public function set( $value )
	{
		return $this->clear()->add( $value );
	}

	/**
		@brief		Return the value(s) as a string.
		@return		string		The value of the attribute.
		@since		20130506
	**/
	public function value()
	{
		return implode( $this->separator(), $this->value );
	}

}

