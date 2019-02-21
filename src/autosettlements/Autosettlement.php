<?php

namespace mycryptocheckout\autosettlements;

use Exception;

/**
	@brief		An autosettlement setting.
	@since		2019-02-21 19:33:29
**/
class Autosettlement
	extends \mycryptocheckout\Collection
{
	/**
		@brief		Is this autosettlement enabled?
		@since		2019-02-21 19:51:38
	**/
	public $enabled = true;

	/**
		@brief		Return user-readable details about this autosettlement.
		@since		2019-02-21 19:35:12
	**/
	public function get_details()
	{
		$r = [];

		if ( ! $this->get_enabled() )
			$r []= __( 'This autosettlement is disabled.', 'mycryptocheckout' );

		return $r;
	}

	/**
		@brief		Return the enabled status of this autosettlement.
		@since		2019-02-21 19:54:51
	**/
	public function get_enabled()
	{
		return $this->enabled;
	}

	/**
		@brief		Return the type of autosettlement setting.
		@since		2019-02-21 19:37:59
	**/
	public function get_type()
	{
		return $this->type;
	}

	/**
		@brief		Set the enabled status of this autosettlement.
		@since		2019-02-21 19:54:51
	**/
	public function set_enabled( $status = true )
	{
		$this->enabled = $status;
		return $this;
	}

	/**
		@brief		Set the type of this autosettlement.
		@since		2019-02-21 19:54:51
	**/
	public function set_type( $type )
	{
		$this->type = $type;
		return $this;
	}

	/**
		@brief		Run the diagnostic tests for this autosettlement.
		@details	Try and communicate with the autosettlement servive.
		@throws		Exception
		@since		2019-02-21 20:29:01
	**/
	public function test()
	{
		throw new Exception( 'Unable to connect to api' );
	}
}
