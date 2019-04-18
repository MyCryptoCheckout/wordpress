<?php

namespace mycryptocheckout\autosettlements;

use Exception;

/**
	@brief		An autosettlement setting.
	@since		2019-02-21 19:33:29
**/
class Autosettlement
	extends \mycryptocheckout\Collection
{
	use \mycryptocheckout\traits\network_available;
	use \mycryptocheckout\traits\label_for_item;

	/**
		@brief		Is this autosettlement enabled?
		@since		2019-02-21 19:51:38
	**/
	public $enabled = true;

	/**
		@brief		Currencies we handle.
		@details	An array of symbols.
		@since		2019-02-22 19:34:15
	**/
	public $currencies = [];

	/**
		@brief		Are we applicable on this payment?
		@since		2019-02-23 11:03:03
	**/
	public function applies_to_payment( $payment )
	{
		if ( count( $this->get_currencies() ) < 1 )
			return true;
		return in_array( $payment->currency_id, $this->get_currencies() );
	}

	/**
		@brief		Apply this autosettlement to the payment.
		@since		2019-02-23 10:58:31
	**/
	public function apply_to_payment( $payment )
	{
		$data = $payment->data()->load();
		if ( ! isset( $data->autosettlements ) )
			$data->autosettlements = [];

		$autosettlement = [];
		$autosettlement[ 'type' ] = $this->get_type();

		$keys_to_copy = MyCryptoCheckout()->autosettlement_keys_to_payment( $this->get_type() );
		foreach( $keys_to_copy as $key_to_copy )
			$autosettlement[ $key_to_copy ] = $this->get( $key_to_copy );

		$data->autosettlements []= $autosettlement;

		MyCryptoCheckout()->debug( 'Adding autosettlements %s to payment %s', $data->autosettlements, $payment );

		$payment->data()->set( 'autosettlements', $data->autosettlements );
	}

	/**
		@brief		Return an array of currencies we handle.
		@since		2019-02-22 19:34:38
	**/
	public function get_currencies()
	{
		return $this->currencies;
	}

	/**
		@brief		Return user-readable details about this autosettlement.
		@since		2019-02-21 19:35:12
	**/
	public function get_details()
	{
		$r = [];

		if ( ! $this->get_enabled() )
			$r []= __( 'This autosettlement is disabled.', 'mycryptocheckout' );

		if ( $this->label != '' )
			$r []= $this->label;

		$r = $this->get_network_details( $r );

		if ( count( $this->get_currencies() ) < 1 )
			$r []= __( 'All currencies.', 'mycryptocheckout' );
		else
			$r []= sprintf(
				__( 'Currencies: %s.', 'mycryptocheckout' ),
				implode( ', ', $this->get_currencies() )
			);

		foreach( [
			'binance_settlement_currency',
			'bittrex_settlement_currency',
			] as $key )
		{
			$currency = $this->get( $key );
			if ( $currency != '' )
				$r []= sprintf(
					__( 'Settling to %s', 'mycryptocheckout' ),
					$currency
				);
		}

		return $r;
	}

	/**
		@brief		Return the enabled status of this autosettlement.
		@since		2019-02-21 19:54:51
	**/
	public function get_enabled()
	{
		return $this->enabled;
	}

	/**
		@brief		Return the type of autosettlement setting.
		@since		2019-02-21 19:37:59
	**/
	public function get_type()
	{
		return $this->type;
	}

	/**
		@brief		Set the currencies we handle.
		@details	Symbols only, please.
		@since		2019-02-22 19:33:12
	**/
	public function set_currencies( $currencies )
	{
		$this->currencies = $currencies;
	}

	/**
		@brief		Set the enabled status of this autosettlement.
		@since		2019-02-21 19:54:51
	**/
	public function set_enabled( $status = true )
	{
		$this->enabled = $status;
		return $this;
	}

	/**
		@brief		Set the type of this autosettlement.
		@since		2019-02-21 19:54:51
	**/
	public function set_type( $type )
	{
		$this->type = $type;
		return $this;
	}

	/**
		@brief		Run the diagnostic tests for this autosettlement.
		@details	Try and communicate with the autosettlement servive.
		@throws		Exception
		@since		2019-02-21 20:29:01
	**/
	public function test()
	{
		$autosettlement = new \mycryptocheckout\api\v2\Autosettlement();
		$autosettlement->set_type( $this->get_type() );
		foreach( $this as $key => $value )
			$autosettlement->$key = $value;
		return MyCryptoCheckout()->api()->autosettlements()->test( $autosettlement );
	}
}
