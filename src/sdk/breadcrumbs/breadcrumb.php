<?php

namespace plainview\sdk_mcc\breadcrumbs;

/**
	@brief		A breadcrumb.
	@details

	Contains a link which has a label().

	@since		20130729
	@version	20130826
**/
class breadcrumb
{
	use \plainview\sdk_mcc\html\element;

	public $tag = 'li';

	/**
		@brief		The anchor / link.
		@since		20130729
		@var		$a
	**/
	public $a;

	/**
		@brief		Constructor.
		@since		20130729
	**/
	public function __construct( $id )
	{
		$this->a = new \plainview\sdk_mcc\html\div;
		$this->a->tag = 'a';
		$this->css_class( 'id_' . $id );
		$this->css_class( 'breadcrumb' );
		$this->_construct();
	}

	/**
		@brief		This method is called after completing construction.
		@details	This method exists so that one does not have to remember to parent::__construct() when overloading.
		@since		20130729
	**/
	public function _construct()
	{
	}

	public function __toString()
	{
		$b = clone( $this );
		$r = $b->open_tag();
		$r .= $b->a;
		$r .= $b->close_tag();
		return $r;
	}

	/**
		@brief		Set the breadcrumb's label.
		@param		string		$label		The new label for the breadcrumb.
		@return		$this		Method chaining.
		@since		20130729
	**/
	public function label( $label )
	{
		$label = htmlspecialchars( $label );
		$this->a->content = $label;
		return $this;
	}

	/**
		@brief		Set the breadcrumb's URL.
		@param		string		$url		The new URL for the breadcrumb.
		@return		$this		Method chaining.
		@since		20130729
	**/
	public function url( $url )
	{
		$this->a->set_attribute( 'href', $url );
		return $this;
	}
}
