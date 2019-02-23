<?php

namespace mycryptocheckout\traits;

/**
	@brief		Functions common to things that can be network enabled and on specific sites.
	@see		\mycryptocheckout\autosettlements\Autosettlement
	@see		\mycryptocheckout\wallets\Wallet
	@since		2019-02-22 21:40:44
**/
trait network_available
{
	/**
		@brief		Is this available on all sites on the network?
		@since		2019-02-22 19:34:15
	**/
	public $network = true;

	/**
		@brief		On which sites is this available?
		@details	This is only taken into account when $network is false.
		@since		2019-02-22 19:34:15
	**/
	public $sites = [];

	/**
		@brief		Add the network fields to the item editing form.
		@since		2019-02-22 21:43:20
	**/
	public function add_network_fields( $form )
	{
		$fs = $form->fieldset( 'fs_network' );
		// Fieldset legend
		$fs->legend->label( __( 'Network settings', 'mycryptocheckout' ) );

		$form->network_available = $fs->checkbox( 'network_available' )
			->checked( $this->get_network() )
			->description( __( 'Do you want this to be available on the whole network?', 'mycryptocheckout' ) )
			// Input label
			->label( __( 'Network available', 'mycryptocheckout' ) );

		$form->network_sites = $fs->select( 'site_ids' )
			->description( __( 'If not network enabled, on which sites should this be available.', 'mycryptocheckout' ) )
			// Input label
			->label( __( 'Sites', 'mycryptocheckout' ) )
			->multiple()
			->value( $this->get_sites() );

		foreach( MyCryptoCheckout()->get_sorted_sites() as $site_id => $site_name )
			$form->network_sites->opt( $site_id, $site_name );

		$form->network_sites->autosize();
	}

	/**
		@brief		Return whether this is available on the whole network.
		@since		2019-02-22 21:47:17
	**/
	public function get_network()
	{
		return $this->network;
	}

	/**
		@brief		Fill the details array with info about the network status of this item.
		@since		2019-02-23 10:43:02
	**/
	public function get_network_details( $details )
	{
		if ( ! $this->network )
		{
			if ( count( $this->sites ) < 1 )
				$details []= __( 'Not available on any sites.', 'mycryptocheckout' );
			else
			{
				$details []= sprintf(
					// This wallet is available on SITE1, SITE2, SITE3
					__( 'Available on %s', 'mycryptocheckout' ),
					implode( ', ', $this->get_site_names() )
				);
			}
		}
		return $details;
	}

	/**
		@brief		Return an array of sites that we are enabled on.
		@since		2019-02-22 20:31:55
	**/
	public function get_site_names()
	{
		$r = [];
		foreach( $this->get_sites() as $site_id )
		{
			$name = get_blog_option( $site_id, 'blogname' );
			$r [ $site_id ] = sprintf( '%s (%d)', $name, $site_id );
		}
		return $r;
	}

	/**
		@brief		Get the site IDs we are enabled on.
		@since		2019-02-22 20:31:02
	**/
	public function get_sites()
	{
		return $this->sites;
	}

	/**
		@brief		Maybe parse the network settings from the $form.
		@since		2019-02-22 21:48:11
	**/
	public function maybe_parse_network_form_post( $form )
	{
		if ( ! MyCryptoCheckout()->is_network )
			return;
		if( ! is_super_admin() )
			return;
		$this->set_network( $form->network_available->is_checked() );
		$this->set_sites( $form->network_sites->get_post_value() );
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
		@brief		Set the network availability.
		@since		2019-02-22 21:51:19
	**/
	public function set_network( $network )
	{
		$this->network = $network;
		return $this;
	}

	/**
		@brief		Set the sites that we are enabled on.
		@since		2019-02-22 20:30:43
	**/
	public function set_sites( $sites )
	{
		$this->sites = $sites;
		return $this;
	}
}
