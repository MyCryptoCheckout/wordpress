<?php

namespace plainview\sdk_mcc\form2\inputs;

/**
	@brief		Textarea input.
	@author		Edward Plainview <edward@plainview.se>
	@copyright	GPL v3
	@version	20130524
**/
class textarea
	extends text
{
	public $self_closing = false;
	public $tag = 'textarea';

	/**
		@brief		Set the amount of cols (and optionally also rows) for this textarea.
		@param		int		$cols		Number of columns for this textarea.
		@param		int		$rows		Number of rows for this textarea.
		@return		this		Object chaining.
		@since		20130524
	**/
	public function cols( $cols, $rows = null )
	{
		if ( $rows !== null )
			$this->rows( $rows );
		return $this->set_attribute( 'cols', $cols );
	}

	public function display_value()
	{
		return $this->_value;
	}

	/**
		@brief		Prepares to display the textarea.
		@details	Removes the 'value' attribute, since the value is shown between the tags.
	**/
	public function prepare_to_display()
	{
		$this->_value = $this->get_attribute( 'value' );
		$this->clear_attribute( 'value' );
	}

	/**
		@brief		Set the amount of rows (and optionally also columns) for this textarea.
		@param		int		$rows		Number of rows for this textarea.
		@param		int		$cols		Number of columns for this textarea.
		@return		this		Object chaining.
		@since		20130524
	**/
	public function rows( $rows, $cols = null )
	{
		if ( $cols !== null )
			$this->cols( $cols );
		return $this->set_attribute( 'rows', $rows );
	}
}

