<?php

namespace plainview\sdk_mcc\form2\bootstrap\bootstrap5\inputs;

/**
	@brief		Button input for Bootstrap5
	@since		2022-07-17 12:00:39
**/
class button
	extends \plainview\sdk_mcc\form2\inputs\button
{
	use icon_trait;

	/**
		@brief		Add the icon to the value.
		@since		2022-07-17 13:28:40
	**/
	public function display_value()
	{
		return $this->get_icon_html() . parent::display_value();

	}
}
