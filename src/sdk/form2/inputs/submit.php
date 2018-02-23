<?php

namespace plainview\sdk_mcc\form2\inputs;

/**
	@brief		Submit button.
	@author		Edward Plainview <edward@plainview.se>
	@copyright	GPL v3
	@version	20130524
**/
class submit
	extends button
{
	public $type = 'submit';

	/**
		@brief		Was this submit button pressed?
		@details	Call after $form->post().
		@return		bool		True if the button was pressed.
		@since		20130524
	**/
	public function pressed()
	{
		$value = $this->form()->get_post_value( $this->make_name() );
		return $this->get_post_value() !== null;
	}
}
