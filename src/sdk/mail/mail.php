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
	/**
		@brief		If there was a sending error, it is placed here.
		@see		ErrorInfo
		@var		$error
		@since		20130423
	**/
	public $error = null;

	/**
		@brief		Changed after running send().
		@var		$send_ok
		@since		20130723
	**/
	public $send_ok = false;

	/**
		@brief		Internal function to handle similar add functions.
		@param		string		$type		Type of function to call.
		@param		string		$email		E-mail address to add.
		@param		string		$name		Optional name to add.
		@return		mail					This object.
		@see		cc
		@see		bcc
		@see		reply_to
		@see		to
		@since		20130423
	**/
	protected function add( $type, $email, $name = '' )
	{
		$function = 'Add' . $type;
		$this->$function( $email, $name );
		return $this;
	}

	/**
		@brief		Convenience function to add an attachment.
		@param		string		$path		The path to the file to attach.
		@param		string		$name		Optional name of the file, as visible in the mail.
		@return		mail					This object.
		@see		attachment
		@since		20130515
	**/
	public function attach( $path, $name = '' )
	{
		return call_user_func_array( array( $this, 'attachment' ), func_get_args() );
	}

	/**
		@brief		Add an attachment to the mail.
		@param		string		$path		The path to the file to attach.
		@param		string		$name		Optional name of the file, as visible in the mail.
		@return		mail					This object.
		@since		20130423
	**/
	public function attachment( $path, $name = '' )
	{
		$mime_type = \plainview\sdk_mcc\wordpress\base::mime_type ( $path );
		$this->AddAttachment( $path, $name, 'base64', $mime_type );
		return $this;
	}

	/**
		@brief		Add a CC address.
		@param		string		$email		E-mail address to add.
		@param		string		$name		Optional name to add.
		@return		mail					This object.
		@since		20130423
	**/
	public function cc( $email, $name = '' )
	{
		return $this->add( 'CC', $email, $name );
	}

	/**
		@brief		Add a BCC address.
		@param		string		$email		E-mail address to add.
		@param		string		$name		Optional name to add.
		@return		mail					This object.
		@since		20130423
	**/
	public function bcc( $email, $name = '' )
	{
		return $this->add( 'BCC', $email, $name );
	}

	/**
		@brief		Set the sender name and e-mail.
		@param		string		$email		E-mail address to add.
		@param		string		$name		Optional name to add.
		@return		mail					This object.
		@since		20130423
	**/
	public function from( $email, $name = '' )
	{
		$this->From = $email;
		$this->Sender = $email;
		if ( $name != '' )
			$this->FromName = $name;
		return $this;
	}

	/**
		@brief		Set the HTML message body.
		@param		string		$html		Body HTML to set.
		@return		mail					This object.
		@since		20130423
	**/
	public function html( $html )
	{
		$new_html = @call_user_func_array( 'sprintf', func_get_args() );
		if ( $new_html == '' )
			$new_html = $html;
		$this->MsgHTML( $new_html );
		return $this;
	}

	/**
		@brief		Add reply-to address.
		@param		string		$email		E-mail address to add.
		@param		string		$name		Optional name to add.
		@return		mail					This object.
		@since		20130423
	**/
	public function reply_to( $email, $name = '' )
	{
		return $this->add( 'ReplyTo', $email, $name );
	}

	/**
		@brief		Send the mail.
		@return		mail					This object.
		@see		send_ok					Use to check if the mail was sent correctly.
		@see		error
		@since		20130423
	**/
	public function send()
	{
		$this->send_ok = parent::Send();
		if ( ! $this->send_ok )
			$this->error = $this->ErrorInfo;
		return $this;
	}

	/**
		@brief		Was the mail sent correctly?
		@return		bool					True if the mail was sent correctly.
		@see		error					Error message is found here.
		@see		send
		@since		20130423
	**/
	public function send_ok()
	{
		return $this->send_ok;
	}

	/**
		@brief		Set the subject of the mail.
		@param		string		$subject	Subject to set.
		@return		mail					This object.
		@since		20130423
	**/
	public function subject( $subject )
	{
		$new_subject = @call_user_func_array( 'sprintf', func_get_args() );
		if ( $new_subject == '' )
			$new_subject = $subject;
		$this->Subject = $new_subject;
		return $this;
	}

	/**
		@brief		Set the body of the mail in plaintext format.
		@param		string		$text		Plaintext body to set.
		@return		mail					This object.
		@since		20130423
	**/
	public function text( $text )
	{
		$new_text = @call_user_func_array( 'sprintf', func_get_args() );
		if ( $new_text == '' )
			$new_text = $text;
		$this->Body = $new_text;
		return $this;
	}

	/**
		@brief		Add a to: address.
		@param		string		$email		E-mail address to add.
		@param		string		$name		Optional name to add.
		@return		mail					This object.
		@since		20130423
	**/
	public function to( $email, $name = '' )
	{
		return $this->add( 'Address', $email, $name );
	}
}

