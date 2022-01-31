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
		if ( $id === null )
			$id = \plainview\sdk_mcc\base::uuid();
		$this->construct_id( $id );
		$this->row = $row;
	}

	public function __tostring()
	{
		return $this->indent() . $this->open_tag() . $this->text . $this->close_tag() . "\n";
	}

	/**
		@brief		Set the cells ID.
		@details	This is to allow td to instead set the header, instead of the id.
		@since		2021-10-17 22:36:56
	**/
	public function construct_id( $id )
	{
		$this->id = $id;
		$this->attribute( 'id' )->set( $this->id );
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
