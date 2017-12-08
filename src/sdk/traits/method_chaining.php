<?php

namespace plainview\sdk_mcc\traits;

/**
	@brief		Set and get object properties using method chaining.
	@since		20130714
	@version	20130714
**/
trait method_chaining
{
	public function set_key( $key, $value )
	{
		$this->$key = $value;
		return $this;
	}

	public function set_boolean( $key, $value )
	{
		return $this->set_key( $key, $value === true );
	}

	public function set_int( $key, $value )
	{
		return $this->set_key( $key, intval( $value ) );
	}

	public function set_string( $key, $value )
	{
		return $this->set_key( $key, $value );
	}
}
