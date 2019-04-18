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
	public $stripslashes = false;
	public $trim = false;
	public $type = 'text';
	public $uppercase = false;

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
		@brief		Set the pattern attribute.
		@since		2018-11-15 15:36:55
	**/
	public function pattern( $pattern )
	{
		$this->set_attribute( 'pattern', $pattern );
		return $this;
	}

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
		@brief		Run a stripslashes on the value.
		@param		bool		$value		True to stripslash the value.
		@since		2019-04-04 19:49:06
	**/
	public function stripslashes( $value = true )
	{
		$this->stripslashes = $value;
		return $this->add_value_filter( 'stripslashes' );
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
		@param		bool		$value		The value to lowercase.
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
		@param		bool		$value		The value to plaintext.
		@since		20130814
	**/
	public function value_filter_plaintext( $value )
	{
		if ( $this->plaintext )
			$value = strip_tags( $value );
		return $value;
	}

	/**
		@brief		Filter the value by stripslashing it.
		@param		bool		$value		The value to stripslash.
		@since		2019-04-04 19:49:28
	**/
	public function value_filter_stripslashes( $value )
	{
		if ( $this->stripslashes )
			$value = stripslashes( $value );
		return $value;
	}

	/**
		@brief		Filter the value by trimming it.
		@param		bool		$value		The value to uppercase.
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
