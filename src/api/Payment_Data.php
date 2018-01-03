<?php

namespace mycryptocheckout\api;

/**
	@brief		A smart handler for the data property of the Payment object.
	@details	Allows the data property to be handled as a smart object.
	@since		2017-12-30 19:45:35
**/
class Payment_Data
{
	/**
		@brief		The payment class.
		@since		2017-12-30 19:46:17
	**/
	public function __construct( $payment )
	{
		$this->payment = $payment;
	}

	/**
		@brief		Load the current data.
		@since		2017-12-30 19:47:20
	**/
	public function load()
	{
		$r = json_decode( $this->payment->data );
		if ( ! $r )
			$r = (object)[];
		return $r;
	}

	/**
		@brief		Save the array into the data object.
		@since		2017-12-30 19:48:25
	**/
	public function save( $array )
	{
		$this->payment->data = json_encode( $array );
		return $this;
	}

	/**
		@brief		Set the data.
		@since		2017-12-30 19:47:54
	**/
	public function set( $key, $value )
	{
		$r = $this->load();
		$r->$key = $value;
		$this->save( $r );
	}
}
