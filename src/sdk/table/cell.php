<?php

namespace plainview\sdk_mcc\table;

/**
	@brief		A table cell.
	@details	This is a superclass for the td and th subclasses.
	@since		20130430
	@version	20130430
**/
class cell
{
	use table_element;

	/**
		@brief		Unique ID of this cell.
		@var		$id
	**/
	public $id;

	/**
		@brief		row object with which this cell was created.
		@var		$row
	**/
	public $row;

	public $tag = 'cell';

	public function __construct( $row, $id = null )
	{
		if ( $id !== null )
		{
			$this->id = $id;
			$this->attribute( 'id' )->set( $this->id );
		}
		else
			$this->id = \plainview\sdk_mcc\base::uuid();
		$this->row = $row;
	}

	public function __tostring()
	{
		return $this->indent() . $this->open_tag() . $this->text . $this->close_tag() . "\n";
	}

	public function indentation()
	{
		return $this->row->indentation() + 1;
	}

	/**
		@brief		Return the row of this cell.
		@details	Is used to continue the ->td()->row()->td() chain.
		@return		row		The table row this cell was created in.
		@since		20130430
	**/
	public function row()
	{
		return $this->row;
	}
}
