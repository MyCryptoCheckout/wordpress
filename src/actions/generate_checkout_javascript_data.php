<?php

namespace mycryptocheckout\actions;

/**
	@brief		Mark a payment as completed.
	@since		2018-03-04 18:25:50
**/
class generate_checkout_javascript_data
	extends action
{
	/**
		@brief		IN/OUT: The collection that is converted to an object and passed to the checkout JS.
		@since		2018-04-25 15:59:47
	**/
	public $data;

	/**
		@brief		Render the data as a string.
		@since		2018-04-25 16:02:59
	**/
	public function render()
	{
		return sprintf( '<div id="mycryptocheckout_checkout_data" type="text/javascript" data-mycryptocheckout_checkout_data=%s></div>',
			base64_encode( json_encode( $this->data->to_array() ) )
		);
	}
}
