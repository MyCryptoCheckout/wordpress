<?php

namespace mycryptocheckout\wallets;

/**
	@brief		A wallet.
	@since		2017-12-09 09:02:26
**/
#[\AllowDynamicProperties]
class Wallet
	extends \mycryptocheckout\Collection
{
	use \mycryptocheckout\traits\network_available;
	use \mycryptocheckout\traits\label_for_item;

	/**
		@brief		The wallet's address.
		@since		2017-12-09 09:05:51
	**/
	public $address = '';

	/**
		@brief		Which currency the wallet belongs to.
		@see		MyCryptoCheckout\currencies_trait
		@since		2017-12-09 09:06:07
	**/
	public $currency_id = '';

	/**
		@brief		How many confirmations for payments to be considered complete.
		@since		2017-12-22 19:26:23
	**/
	public $confirmations = 1;

	/**
		@brief		Is the wallet enabled at all?
		@since		2017-12-09 09:06:47
	**/
	public $enabled = true;

	/**
		@brief		When the wallet was last used for payment.
		@details	Unix time.
		@since		2017-12-14 18:38:13
	**/
	public $last_used = 0;

	/**
		@brief		The order in which to display this wallet.
		@since		2018-10-17 19:10:17
	**/
	public $order = 99;

	/**
		@brief		How many times the wallet has been used for payment.
		@since		2017-12-14 18:38:42
	**/
	public $times_used = 0;

	/**
		@brief		Apply this wallet's data to the Payment.
		@since		2018-09-20 21:02:03
	**/
	public function apply_to_payment( $payment )
	{
		$payment->confirmations = $this->confirmations;
		$payment->to = $this->get_address();

		// Find this wallet in the user's wallets.
		$currencies = MyCryptoCheckout()->currencies();
		$currency = $currencies->get( $this->currency_id );
		$wallets = MyCryptoCheckout()->wallets();
		foreach( $wallets as $wallet )
		{
			if ( $wallet != $this )
				continue;
			// We have found ourself!
			if ( $currency->supports( 'monero_private_view_key' ) )
			{
				$monero_private_view_key = $this->get( 'monero_private_view_key' );
				if ( $monero_private_view_key != '' )
				{
					$payment->data()->set( 'monero_private_view_key', $this->get( 'monero_private_view_key' ) );
				}
			}

			// Only set the circa amount if there is a pub key.
			$key = $wallet->get( 'btc_hd_public_key' );
			$key = trim( $key );
			if ( strlen( $key ) > 16 )		// At least 10 chars of key
			{
				$circa_amount = $this->get( 'circa_amount' );
				if ( $circa_amount > 0 )
					$payment->data()->set( 'circa_amount', $circa_amount );
			}
		}
	}

	/**
		@brief		Return the address.
		@since		2017-12-09 18:50:18
	**/
	public function get_address()
	{
		return $this->address;
	}

	/**
		@brief		Return the currency.
		@since		2017-12-09 18:50:18
	**/
	public function get_currency_id()
	{
		return $this->currency_id;
	}

	/**
		@brief		Describe this wallet a little.
		@details	Used in the wallets overview.
		@return		An array of strings.
		@since		2017-12-09 18:50:18
	**/
	public function get_details()
	{
		$r = [];

		if ( ! $this->enabled )
			$r []= __( 'This wallet is disabled.', 'mycryptocheckout' );

		if ( $this->label != '' )
			$r []= $this->label;

		$r = $this->get_network_details( $r );

		if ( $this->confirmations > 1 )
			$r []= sprintf(
				// Used 123 times
				__( '%d confirmations', 'mycryptocheckout' ),
				$this->confirmations
			);

		if ( $this->last_used > 0 )
			$r []= sprintf(
				// Used 123 times
				__( 'Last used %s', 'mycryptocheckout' ),
				( MyCryptoCheckout()->local_datetime( $this->last_used ) )
			);

		if ( $this->times_used > 0 )
			$r []= sprintf(
				// Used 123 times
				__( 'Used %d times', 'mycryptocheckout' ),
				$this->times_used
			);

		return $r;
	}

	/**
		@brief		Return the order of this wallet.
		@since		2018-10-17 19:12:57
	**/
	public function get_order()
	{
		if ( ! isset( $this->order ) )
			$this->order = 0;
		return $this->order;
	}

	/**
		@brief		Set the enabled status of this wallet.
		@since		2017-12-09 19:22:26
	**/
	public function set_enabled( $status = true )
	{
		$this->enabled = $status;
		return $this;
	}

	/**
		@brief		Set the order / priority for this wallet.
		@details	0 comes before 10.
		@since		2018-10-17 19:08:04
	**/
	public function set_order( $order = 99 )
	{
		$this->order = $order;
		return $this;
	}

	/**
		@brief		Touch the uses of this wallet.
		@since		2017-12-27 11:54:28
	**/
	public function use_it()
	{
		$this->last_used = time();
		$this->times_used++;

		$action = MyCryptoCheckout()->new_action( 'use_wallet' );
		$action->currencies = MyCryptoCheckout()->currencies();
		$action->currency = $action->currencies->get( $this->currency_id );
		$action->wallets = MyCryptoCheckout()->wallets();
		foreach( $action->wallets as $wallet )
			if ( $wallet == $this )
				$action->wallet = $wallet;
		$action->execute();
	}
}
