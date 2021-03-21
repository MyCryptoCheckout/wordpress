<?php

namespace plainview\sdk_mcc\wordpress\mail;

/**
	@brief		Wrapper for PHPMailer.
	@details	Provides a more Wordpress-friendly, logical interface to PHPMailer.
	@since		2021-01-20 12:52:33
**/
class mail
	extends \PHPMailer\PHPMailer\PHPMailer
{
	use \plainview\sdk_mcc\mail\mail_trait;
}
