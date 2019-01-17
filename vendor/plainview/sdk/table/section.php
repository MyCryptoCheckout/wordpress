<?php

namespace plainview\sdk_mcc\table;

/**
	@brief		A table section: the thead or tbody.
	@since		20130430
	@version	20130430
**/
class section
	implements \Countable
{
	use table_element;

	/**
		@brief		Array of rows.
		@var		$rows
	**/
	public $rows;

	/**
		@brief		Parent table.
		@var		$table
	**/
	public $table;

	/**
		@brief		Object / element HTML tag.
		@var		$tag
	**/
	public $tag = 'section';

	public function __construct( $table )
	{
		$this->table = $table;
		$this->rows = array();
	}

	/**
		@brief		Returns the section as an HTML string.
		@since		20130430
	**/
	public function __tostring()
	{
		if ( $this->text == '' && count( $this->rows ) < 1 )
			return '';

		$r = $this->indent();
		$r .= $this->open_tag() . "\n";

		if ( $this->text != '' )
			$r .= $this->text;

		foreach( $this->rows as $row )
			$r .= $row;
		$r .= $this->indent();
		$r .= $this->close_tag() . "\n";
		return $r;
	}

	/**
		@brief		Return a count of rows.
		@return		int		How many rows this section has.
		@since		20130803
	**/
	public function count()
	{
		return count( $this->rows );
	}

	public function indentation()
	{
		return $this->table->indentation() + 1;
	}

	/**
		@brief		Does this section have any rows?
		@return		bool		True if the section is empty.
		@since		20130801
	**/
	public function is_empty()
	{
		return count( $this->rows ) < 1;
	}

	/**
		@brief		Retrieve an existing or create a new row, with an optional id.
		@details	Call with no ID to create a new row. Call with an ID that does not exist and a new row will be created

		Call with an ID that has previously been created and it will return the requested row.

		@param		string		$id		The ID (attribute) of this row.
		@return		row		The existing or newly created row.
		@since		20130430
	**/
	public function row( $id = null )
	{
		if ( $id === null || ! isset( $this->rows[ $id ] ) )
		{
			$row = new row( $this, $id );
			$id = $row->id;
			$this->rows[ $id ] = $row;
		}
		return $this->rows[ $id ];
	}
}
