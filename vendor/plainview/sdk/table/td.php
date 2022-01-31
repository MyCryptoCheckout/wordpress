<?php

namespace plainview\sdk_mcc\table;

/**
	@brief		Cell of type TD.
	@since		20130430
**/
class td
	extends cell
{
	public $tag = 'td';

	/**
		@brief		Set the cells ID.
		@details	This is to allow td to instead set the header, instead of the id.
		@since		2021-10-17 22:36:56
	**/
	public function construct_id( $id )
	{
		$this->id = $id;
		$this->attribute( 'headers' )->set( $this->id );
	}
}
