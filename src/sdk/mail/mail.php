<?php

namespace plainview\sdk_mcc\mail;

/**
	@brief		Wrapper for PHPMailer.
	@details	Provides a more Wordpress-friendly, logical interface to PHPMailer.

	@par		Changelog

	- 201305015	New: attach()

	@author		Edward Plainview		edward@plainview.se
	@version	20130515
**/
class mail
	extends \PHPMailer
{
	use mail_trait;
}
