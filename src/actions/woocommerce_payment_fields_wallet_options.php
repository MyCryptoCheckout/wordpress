<?php

namespace mycryptocheckout\actions;

/**
	@brief		Modify the wallet options, adding or removing currencies.
	@since		2019-09-01 20:50:48
**/
class woocommerce_payment_fields_wallet_options
	extends action
{
	/**
		@brief		IN/OUT: The array of wallet options shown in the WC checkout form.
		@since		2019-09-01 20:51:10
	**/
	public $options;
}
