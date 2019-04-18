<?php

namespace mycryptocheckout\traits;

/**
	@brief		Provide labelling functions for items / objects.
	@since		2019-04-18 22:49:26
**/
trait label_for_item
{

	/**
		@brief		The label / description of the wallet.
		@since		2019-04-18 22:45:32
	**/
	public $label = '';

	/**
		@brief		Return the label of the wallet.
		@since		2019-04-18 22:45:19
	**/
	public function get_label()
	{
		if ( ! isset( $this->label ) )
			$this->label = '';
		return $this->label;
	}

	/**
		@brief		Save the label / description for this wallet.
		@since		2019-04-18 22:46:03
	**/
	public function set_label( $label )
	{
		$this->label = $label;
		return $this;
	}
}
