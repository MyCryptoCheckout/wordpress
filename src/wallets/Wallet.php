<?php

namespace mycryptocheckout\wallets;

/**
	@brief		A wallet.
	@since		2017-12-09 09:02:26
**/
class Wallet
	extends \mycryptocheckout\Collection
{
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
		@brief		Is the wallet available on all sites on the network?
		@since		2017-12-09 09:06:16
	**/
	public $network = true;

	/**
		@brief		On which sites is the wallet available?
		@details	This is only taken into account when $network is false.
		@since		2017-12-09 09:07:04
	**/
	public $sites = [];

	/**
		@brief		How many times the wallet has been used for payment.
		@since		2017-12-14 18:38:42
	**/
	public $times_used = 0;

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

		if ( $this->get( 'preselected' ) )
			$r []= __( 'Selected as default', 'mycryptocheckout' );

		if ( ! $this->network )
		{
			if ( count( $this->sites ) < 1 )
				$r []= __( 'Not available on any sites.', 'mycryptocheckout' );
			else
			{
				$sites = [];
				foreach( $this->sites as $site_id )
				{
					$name = get_blog_option( $site_id, 'blogname' );
					$sites []= sprintf( '%s (%d)', $name, $site_id );
				}
				$r []= sprintf(
					// This wallet is available on SITE1, SITE2, SITE3
					__( 'Available on %s', 'mycryptocheckout' ),
					implode( ', ', $sites )
				);
			}
		}

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
		@brief		Convenience method that returns whether this wallet is enabled on the current site.
		@since		2017-12-10 19:14:14
	**/
	public function is_enabled_on_this_site()
	{
		if ( ! $this->enabled )
			return false;
		if ( $this->network )
			return true;
		if ( in_array( get_current_blog_id(), $this->sites ) )
			return true;
		return false;
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
