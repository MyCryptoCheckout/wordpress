<?php

namespace plainview\sdk_mcc\table;

/**
	@brief		A table row.
	@since		20130430
	@version	20130430
**/
class row
	implements \Countable
{
	use table_element;

	/**
		@brief		Array of cells.
		@var		$cells
	**/
	public $cells;

	/**
		@brief		Unique ID of this row.
		@var		$id
	**/
	public $id;

	/**
		@brief		Parent section.
		@var		$section
	**/
	public $section;

	/**
		@brief		Object / element tag.
		@var		$tag
	**/
	public $tag = 'tr';

	public function __construct( $section, $id = null )
	{
		if ( $id !== null )
		{
			$this->attribute( 'id' )->set( $id );
			$this->id = $id;
		}
		else
			$this->id = \plainview\sdk_mcc\base::uuid();
		$this->cells = array();
		$this->section = $section;
	}

	/**
		@brief		Return the row as an HTML string.
		@since		20130430
	**/
	public function __tostring()
	{
		if ( count( $this->cells ) < 1 )
			return '';

		$r = $this->indent();
		$r .= $this->open_tag() . "\n";
		foreach( $this->cells as $cell )
			$r .= $cell;
		$r .= $this->indent();
		$r .= $this->close_tag() . "\n";

		return $r;
	}

	/**
		@brief		Retrieve an existing cell or create a new one.
		@details	If the ID exists, the existing cell is returned.

		If not: if $cell is null, return false;

		If $cell is a cell, add is to the cell array and return it again.

		@param		string			$id			ID of cell to retrieve or create.
		@param		cell			$cell		Cell to add to the cell array.
		@return		cell						The table cell specified, or the newly-created cell.
		@since		20130430
	**/
	public function cell( $id = null , $cell = null )
	{
		if ( $id === null && $cell === null )
			return false;
		if ( ! isset( $this->cells[ $id ] ) )
		{
			// Does this cell not exist at all?
			if ( $cell === null )
				return false;

			// Cell does not exist, but we want to create one.
			$id = $cell->id;
			$this->cells[ $id ] = $cell;
		}
		return $this->cells[ $id ];
	}

	/**
		@brief		Return a count of cells in the row.
		@return		int		How many cells the row has.
		@since		20130803
	**/
	public function count()
	{
		return count( $this->cells );
	}

	public function indentation()
	{
		return $this->section->indentation() + 1;
	}

	/**
		@brief		Either retrieve an existing td cell or create a new one.
		@param		string		$id			The HTML ID of the cell.
		@return		td						The requested cell.
		@since		20130430
	**/
	public function td( $id = null )
	{
		return $this->cell( $id, new td( $this, $id ) );
	}

	/**
		@brief		Either retrieve an existing th cell or create a new one.
		@param		string		$id			The HTML ID of the cell.
		@return		th						The requested cell.
		@since		20130430
	**/
	public function th( $id = null )
	{
		return $this->cell( $id, new th( $this, $id ) );
	}
}
