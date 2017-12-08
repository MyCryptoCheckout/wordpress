<?php

namespace plainview\sdk_mcc\table;

require_once( __DIR__ . '/table_element.php' );

/**
	@brief		Plainview XHTML table class.

	@details	Allows tables to be made created, modified and displayed efficiently.

	@par		Example 1

	@code
	$table = new table();
	$table->caption()->text( 'This is a caption' );
	$tr = $table->head()->row();
	$tr->th()->text( 'Name' );
	$tr->th()->text( 'Surname' );
	foreach( $names as $name )
	{
		$tr = $table->body()->row();
		$tr->td()->text( $name->first );
		$tr->td()->text( $name->last );

		// Or...
		$table->body()->row()				// Create a new row.
			->td()->text( $name->first )	// Create a td
			->row()							// Get the row back from the td
			->td()->text( $name->last )		// Create another td in the same row
	}
	@endcode

	@par		Example 2 - How about some styling?

	@code
	$tr->td()->text( $name->first )->css_style( 'font-weight: bold' );
	@endcode

	@par		Example 3 - How about some CSS classing?

	@code
	$tr->td()->text( $name->first )->css_class( 'align_center' )->css_style( 'font-size: 200%;' );
	@endcode

	@par		Example 4 - Reworking a cell

	@code
	$tr->td( 'first_name_1' )->text( $name->first );
	$tr->td( 'first_name_1' )->css_class( 'align_center' )->css_style( 'font-size: 200%;' );
	@endcode

	@par		Changelog

	- 20130803		count() added for table, sections and rows. Source split into separate files.
	- 20130801		empty() added.
	- 20130730		Random element IDs have been removed.
	- 20130527		Element UUID length extended from 4 to 8 to help prevent conflicts.
	- 20130513		Table self indents, instead of relying on html\\element.
	- 20130510		Sections do not display if they are empty.
	- 20130509		_() name_() and title_() added to aid in translation.
	- 20130507		Code: td() and th() can return existing cells.
	- 20130424		Cells are not padded anymore.
	- 20130410		Part of Plainview SDK.
	- 20130408		First release.

	@author			Edward Plainview <edward.plainview@sverigedemokraterna.se>
	@copyright		GPL v3
	@since			20130430
	@version		20130801
**/
class table
	implements \Countable
{
	use table_element;

	/**
		@brief		The body object.
		@var		$body
	**/
	public $body;

	/**
		@brief		The foot object.
		@var		$foot
	**/
	public $foot;

	/**
		@brief		The head object.
		@var		$head
	**/
	public $head;

	/**
		@brief		Object / element HTML tag.
		@var		$tag
	**/
	public $tag = 'table';

	public function __construct()
	{
		$this->caption = new caption( $this );
		$this->body = new body( $this );
		$this->foot = new foot( $this );
		$this->head = new head( $this );
	}

	/**
		@brief		Returns the table as an HTML string.
		@since		20130430
	**/
	public function __tostring()
	{
		$r = $this->indent();
		$r .= $this->open_tag() . "\n";
		$r .= $this->caption . $this->head . $this->foot . $this->body;
		$r .= $this->indent();
		$r .= $this->close_tag() . "\n";
		return $r;
	}

	/**
		@brief		Maybe translate a string. Sprintf aware.
		@details	Is overridden by subclasses to translate strings.

		In this parent class is only returns the sprintf'd arguments.

		@param		string		$string		String to translate.
		@return		string		Sprintf'd (yes) and translated (maybe) string.
		@since		20130509
	**/
	public function _( $string )
	{
		return call_user_func_array( 'sprintf', func_get_args() );
	}

	/**
		@brief		Return the body section.
		@return		body		The table section of the table.
		@since		20130430
	**/
	public function body()
	{
		return $this->body;
	}

	/**
		@brief		Return the caption object of the table.
		@return		table_caption		The table's caption.
		@since		20130430
	**/
	public function caption()
	{
		return $this->caption;
	}

	/**
		@brief		Return a count of body rows.
		@return		int		How many rows the body has.
		@since		20130803
	**/
	public function count()
	{
		return count( $this->body );
	}

	/**
		@brief		Return the foot section.
		@return		foot		The table section of the table.
		@since		20130430
	**/
	public function foot()
	{
		return $this->foot;
	}

	/**
		@brief		Return the head section.
		@return		head		The head section of the table.
		@since		20130430
	**/
	public function head()
	{
		return $this->head;
	}

	public function indentation()
	{
		return 0;
	}
}

