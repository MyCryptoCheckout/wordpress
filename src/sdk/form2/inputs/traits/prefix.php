<?php

namespace plainview\sdk_mcc\form2\inputs\traits;

/**
	@brief		Manipulate the name prefix.
	@author		Edward Plainview <edward@plainview.se>
	@copyright	GPL v3
	@version	20130929
**/
trait prefix
{
	public $prefix = array();

	/**
		@brief		Append a / several prefixes.
		@details	All arguments to this method are discovered and appended.
		@return		this		Object chaining.
		@see		prefix()
		@see		prepend_prefix()
		@since		20130524
	**/
	public function append_prefix( $prefix )
	{
		foreach( func_get_args() as $arg )
			if ( is_array( $arg ) )
				foreach( $arg as $prefix )
					$this->append_prefix( $prefix );
			else
				$this->prefix[ $arg ] = $arg;
		return $this;
	}

	/**
		@brief		Return the prefix(es) of this object.
		@return		array		An array of prefix strings.
		@since		20130929
	**/
	public function get_prefix()
	{
		return $this->prefix;
	}

	/**
		@brief		Return the prefix(es) of this object and the parent object.
		@details	The order is the parent's prefixes first, then this object's.
		@return		array		An array of prefix strings.
		@since		20130929
	**/
	public function get_prefixes()
	{
		$r = [];
		// Only the form has no container, but we have to check nonetheless.
		if ( isset( $this->container ) )
			$r += $this->container->get_prefixes();
		$r += $this->get_prefix();
		return $r;
	}

	/**
		@brief		Does this input have any prefixes set?
		@return		bool		True if the input has prefixes.
		@since		20130718
	**/
	public function has_prefix()
	{
		return count( $this->prefix ) > 0;
	}

	/**
		@brief		Set the prefix(es) for this input.
		@details	Clears the prefixes before setting them.
		@return		this		Object chaining.
		@see		append_prefix()
		@see		prepend_prefix()
		@since		20130524
	**/
	public function prefix( $prefix )
	{
		$this->prefix = [];
		foreach( func_get_args() as $prefix )
			$this->append_prefix( $prefix );
		return $this;
	}

	/**
		@brief		Prepend a / several prefixes.
		@details	All arguments to this method are discovered and prepended to the beginning of the current prefixes.
		@return		this		Object chaining.
		@see		append_prefix()
		@see		prefix()
		@since		20130524
	**/
	public function prepend_prefix( $prefix )
	{
		foreach( func_get_args() as $arg )
			array_unshift( $this->prefix, $arg );
		return $this;
	}
}
