<?php

namespace mycryptocheckout\actions;

/**
	@brief		Do something to a payment.
	@details	Used as a common parent class.
	@see		cancel_payment
	@see		complete_payment
	@since		2018-03-04 18:25:50
**/
class payment_action
	extends action
{
	/**
		@brief		How many times has this action been applied?
		@since		2018-03-04 18:26:14
	**/
	public $applied = 0;

	/**
		@brief		The Payment object to work with.
		@see		Payment
		@since		2018-03-04 18:26:00
	**/
	public $payment;
}
