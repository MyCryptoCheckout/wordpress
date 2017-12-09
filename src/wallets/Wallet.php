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
	public $currency = '';

	/**
		@brief		Is the wallet enabled at all?
		@since		2017-12-09 09:06:47
	**/
	public $enabled = true;

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
	public function get_currency()
	{
		return $this->currency;
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

		if ( ! $this->network )
		{
			if ( count( $this->sites ) < 1 )
				$r []= __( 'Not available on any sites.', 'mycryptocheckout' );
			else
			{
				$sites = [];
				foreach( $this->sites as $site_id )
				{
					$name = get_site_option( $site_id, 'blogname' );
					$sites []= sprintf( '%s (%d)', $name, $site_id );
				}
				$r []= sprintf(
					__( 'Available only on %s', 'mycryptocheckout' ),
					implode( ', ', $sites )
				);
			}
		}

		return $r;
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
}
