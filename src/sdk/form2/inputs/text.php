<?php

namespace plainview\sdk_mcc\form2\inputs;

/**
	@brief		Text / textfield input.
	@details	Is the parent class of many text-related inputs.
	@author		Edward Plainview <edward@plainview.se>
	@copyright	GPL v3
	@version	20130807
**/
class text
	extends input
{
	use traits\minlength;
	use traits\maxlength;
	use traits\placeholder;
	use traits\size;
	use traits\value;

	public $plaintext = false;
	public $lowercase = false;
	public $trim = false;
	public $type = 'text';
	public $uppercase = false;

	/**
		@brief		Add a value filter that removes all tags from the string.
		@param		bool		$value		True to strip the string of tags.
		@since		20130807
	**/
	public function plaintext( $value = true )
	{
		$this->plaintext = $value;
		return $this->add_value_filter( 'plaintext' );
	}

	/**
		@brief		Require that this textfield's value be lowercased.
		@param		bool		$value		True to lowercase the value.
		@since		20130718
	**/
	public function lowercase( $value = true )
	{
		$this->lowercase = $value;
		return $this->add_value_filter( 'lowercase' );
	}

	/**
		@brief		Require that this textfield's value be trimmed when set.
		@param		bool		$value		True to trim the value when getting it from the POST.
		@since		20130712
	**/
	public function trim( $value = true )
	{
		$this->trim = $value;
		return $this->add_value_filter( [ $this, 'value_filter_trim' ] );
		return $this->add_value_filter( 'trim' );
	}

	/**
		@brief		Require that this textfield's value be uppercased.
		@param		bool		$value		True to uppercase the value.
		@since		20130718
	**/
	public function uppercase( $value = true )
	{
		$this->uppercase = $value;
		return $this->add_value_filter( 'uppercase' );
	}

	/**
		@brief		Filter the value to lowercase.
		@param		bool		$value		True to uppercase the value.
		@since		20130814
	**/
	public function value_filter_lowercase( $value )
	{
		if ( $this->lowercase )
			$value = mb_strtolower( $value, 'UTF-8' );
		return $value;
	}

	/**
		@brief		Filter the value to a plain text value.
		@param		bool		$value		True to uppercase the value.
		@since		20130814
	**/
	public function value_filter_plaintext( $value )
	{
		if ( $this->plaintext )
			$value = strip_tags( $value );
		return $value;
	}

	/**
		@brief		Filter the value by trimming it.
		@param		bool		$value		True to uppercase the value.
		@since		20130814
	**/
	public function value_filter_trim( $value )
	{
		if ( $this->trim )
			$value = trim( $value );
		return $value;
	}

	/**
		@brief		Filter the value to just uppercase.
		@param		bool		$value		True to uppercase the value.
		@since		20130814
	**/
	public function value_filter_uppercase( $value )
	{
		if ( $this->uppercase )
			$value = mb_strtoupper( $value, 'UTF-8' );
		return $value;
	}
}
