<?php

namespace plainview\sdk_mcc\wordpress\form2\inputs;

class button
	extends \plainview\sdk_mcc\form2\inputs\submit
{
	/**
		@brief		Convenience method function to first translate and then set the value.
		@param		string		$label		Label to translate and then set.
		@return		this		Object chaining.
		@since		20130524
	**/
	public function value_( $value )
	{
		$value = call_user_func_array( array( $this->container, '_' ), func_get_args() );
		return $this->set_value( $value );
	}
}
