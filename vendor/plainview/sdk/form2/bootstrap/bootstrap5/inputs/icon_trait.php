<?php

namespace plainview\sdk_mcc\form2\bootstrap\bootstrap5\inputs;

/**
	@brief		Use nice Fontawesome icons.
	@since		2022-07-17 13:27:18
**/
trait icon_trait
{
	/**
		@brief		The Fontawesome icon for this input.
		@since		2022-07-17 12:48:27
	**/
	public $icon;

	/**
		@brief		Return the icon for this input.
		@since		2022-07-17 12:49:43
	**/
	public function get_icon()
	{
		return $this->icon;
	}

	/**
		@brief		Return the icon html, if any.
		@since		2022-07-17 12:46:28
	**/
	public function get_icon_html()
	{
		$the_icon = $this->get_icon();
		if ( ! $the_icon )
			return;
		return sprintf( '<i class="%s"></i> ', $the_icon );
	}

	/**
		@brief		Set the icon for this input.
		@see		get_icon()
		@see		get_icon_html()
		@see		set_icon()
		@since		2022-07-17 12:46:12
	**/
	public function icon( $icon )
	{
		return $this->set_icon( $icon );
	}

	/**
		@brief		Set the icon for this input.
		@since		2022-07-17 13:32:17
	**/
	public function set_icon( $icon )
	{
		$this->icon = $icon;
		return $this;
	}
}
