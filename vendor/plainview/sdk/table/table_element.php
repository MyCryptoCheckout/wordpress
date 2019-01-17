<?php

namespace plainview\sdk_mcc\table;

/**
	@brief		Helper trait to expand \\html\\element with table specific calls.
	@since		20130430
**/
trait table_element
{
	use \plainview\sdk_mcc\html\element;
	use \plainview\sdk_mcc\html\indentation;

	/**
		@brief		Text / contents of this object.
		@var		$text
		@since		20130430
	**/
	public $text = '';

	/**
		@brief		Find the main table class and as it to translate this string.
		@details	The base class' _() method is sprintf aware. Which means this method is sprintf aware.
		@param		string		$string		String to translate.
		@return		string					The sprintf'd, translated string.
		@since		20130509
	**/
	public function _( $string )
	{
		$table = $this;
		if ( isset( $this->table ) )
			$table = $this->table;
		if ( isset( $this->section ) )
			$table = $this->section->table;
		if ( isset( $this->row ) )
			$table = $this->row->section->table;
		return call_user_func_array( array( $table, '_' ), func_get_args() );
	}

	/**
		@brief		Convenience function to set colspan property.

		Should only be used on cells.

		@param		string		$colspan		How much the object should colspan.
		@return		$this
		@since		20130430
	**/
	public function colspan( $colspan )
	{
		$this->attribute( 'colspan' )->set( $colspan );
		return $this;
	}

	/**
		@brief		Convenience function to set header property.

		The header property of a td cell is an accessability feature that tells screen readers which th headers this cell is associated with.

		@param		string		$header		The ID or IDs (spaced) with which this cell is associated.
		@return		$this					The class itself.
		@since		20130430
	**/
	public function header( $header )
	{
		$this->attribute( 'header' )->set( $header );
		return $this;
	}

	/**
		@brief		Convenience function to set rowspan property.

		Should only be used on cells.

		@param		string		$rowspan		How much the object should rowspan.
		@return		$this
		@since		20130903
	**/
	public function rowspan( $rowspan )
	{
		$this->attribute( 'rowspan' )->set( $rowspan );
		return $this;
	}

	/**
		@brief		Sets the text of this object.
		@details	The text is the contents of this object, most often an HTML string.
		@param		string		$text		Text to set.
		@return		$this					The class itself.
		@since		20130430
	**/
	public function text( $text )
	{
		$this->text = $text;
		return $this;
	}

	/**
		@brief		Translate and set the text of this object.
		@param		string		$text		Text to translate and set.
		@return		$this					The class itself.
		@since		20130509
	**/
	public function text_( $text )
	{
		$text = call_user_func_array( array( $this, '_' ), func_get_args() );
		return $this->text( $text );
	}

	/**
		@brief		Sets the text of this object using sprintf.
		@details	The $text and all extra parameters is run through sprintf as convenience.
		@param		string		$text		Text to set via sprintf.
		@return		$this
		@see		text()
		@since		20130430
	**/
	public function textf( $text )
	{
		return $this->text( call_user_func_array( 'sprintf', func_get_args() ) );
	}

	/**
		@brief		Convenience function to set the hoverover title property.
		@param		string		$title		Title to set.
		@return		$this
		@since		20130430
	**/
	public function title( $title )
	{
		$this->attribute( 'title' )->set( $title );
		return $this;
	}

	/**
		@brief		Translate and set the title of this object.
		@param		string		$title		Title to translate and set.
		@return		$this					The class itself.
		@since		20130509
	**/
	public function title_( $title )
	{
		$title = call_user_func_array( array( $this, '_' ), func_get_args() );
		return $this->title( $title );
	}

	/**
		@brief		Set the title of this object using sprintf.
		@since		2015-05-18 21:41:50
	**/
	public function titlef( $text )
	{
		return $this->title( call_user_func_array( 'sprintf', func_get_args() ) );
	}
}
