<?php

namespace plainview\sdk_mcc\wordpress\traits;

/**
	@brief		Methods for easier use of Zurb's Foundation CSS.
	@details

	Handles alert boxes, similar to wordpress\base message() and error().

	@since		20130723
**/
trait foundation
{
	// -------------------------------------------------------------------------------------------------
	// ----------------------------------------- MESSAGES
	// -------------------------------------------------------------------------------------------------

	/**
		@brief		Displays an error message in foundation format.
		@param		string		$string		String to create into a message.
		@since		20130723
	**/
	public function foundation_error_message( $string )
	{
		return $this->foundation_message( 'alert', $string );
	}

	/**
		Displays a message in foundation format.

		Autodetects HTML / text.

		@param		$type
					Type of message: alert, secondary, success, other string.

		@param		$string
					The message to display.
		@since		20130723
	**/
	public function foundation_message($type, $string)
	{
		$string = wpautop( $string );
		return '
		<div data-alert class="'.$type.' alert-box">
			<p class="message_timestamp">'.$this->now().'</p>
			'.$string.'
		</div>';
	}

	/**
		@brief		Displays a secondary message in foundation format.
		@param		string		$string		String to create into a message.
		@since		20130723
	**/
	public function foundation_secondary_message( $string )
	{
		return $this->foundation_message( 'secondary', $string );
	}

	/**
		@brief		Displays an informational message in foundation format.
		@param		string		$string		String to create into a message.
		@since		20130723
	**/
	public function foundation_success_message( $string )
	{
		return $this->foundation_message( 'success', $string );
	}
}
