<?php

namespace mycryptocheckout;

/**
	@brief		The randomizer class handles randomizing of cryptocurrency amounts.
	@since		2018-01-05 21:12:05
**/
class Randomizer
{
	/**
		@brief		The key of the SESSION variable for the randomization percent.
		@since		2018-01-05 21:13:53
	**/
	public static $session_key = 'mcc_randomization_percent';

	/**
		@brief		Clear the session key.
		@since		2018-01-05 21:24:51
	**/
	public function clear()
	{
		if ( ! isset( $_SESSION[ static::$session_key ] ) )
			return;
		unset( $_SESSION[ static::$session_key ] );
		return $this;
	}

	/**
		@brief		Generate a randomization percentage.
		@since		2018-01-05 21:15:23
	**/
	public function generate_percent()
	{
		$markup_randomization = MyCryptoCheckout()->get_site_option( 'markup_randomization' );
		$markup_randomization = $markup_randomization / 100;
		$random = rand( 0, 100 ) / 100;

		$random_percent = ( ( $markup_randomization * 2 ) * $random ) - $markup_randomization;

		$random_percent = 1 + $random_percent;

		$action = MyCryptoCheckout()->new_action( 'get_randomization_percent' );
		$action->randomization_percent = $random_percent;
		$action->execute();

		return $action->randomization_percent;
	}

	/**
		@brief		Return the randomization percent for this session.
		@since		2018-01-05 21:13:19
	**/
	public function get_percent()
	{
		if ( ! isset( $_SESSION[ static::$session_key ] ) )
			$_SESSION[ static::$session_key ] = $this->generate_percent();
		return $_SESSION[ static::$session_key ];
	}

	/**
		@brief		Modify this amount with the randonmization percentage.
		@since		2018-01-05 21:16:42
	**/
	public function modify( $amount )
	{
		$percent = $this->get_percent();
		$amount = $amount * $percent;
		return $amount;
	}
}