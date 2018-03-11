<?php

namespace mycryptocheckout\currencies;

/**
	@brief		The smart currency collection.
	@since		2017-12-09 19:59:15
**/
class Currencies
	extends \mycryptocheckout\Collection
{
	/**
		@brief		Add a currency.
		@since		2017-12-09 20:03:03
	**/
	public function add( $currency )
	{
		$this->set( $currency->get_id(), $currency );
		return $this;
	}

	/**
		@brief		Add all of the currencies to the Plainview Form select input.
		@details	Group the currencies nicely.
		@since		2018-02-23 15:21:12
	**/
	public function add_to_select_options( $select )
	{
		$groups = Groups::load( $this );
		$groups->sort_now();
		$this->sort_by( function( $item )
		{
			return $item->get_id() . '_' . $item->get_name();
		} );
		foreach( $groups as $group )
		{
			// Add the group itself.
			$optgroup = $select->optgroup( $group->name )
				->label( $group->name );
			foreach( $this as $currency_id => $currency )
			{
				if ( $currency->get_group()->name != $group->name )
					continue;
				$optgroup->opt( $currency_id, sprintf( '%s %s', $currency_id, $currency->get_name() ) );
			}
		}
	}

	/**
		@brief		Return all of the currencies as an array for a select options class.
		@since		2017-12-09 19:59:39
	**/
	public function as_options()
	{
		$r = [];
		foreach( $this as $currency )
			$r[ $currency->get_id() ] = sprintf( '%s (%s)', $currency->get_name(), $currency->get_id() );
		return array_flip( $r );
	}
}
