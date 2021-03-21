<?php

namespace plainview\sdk_mcc\pagination;

use \plainview\sdk_mcc\base;
use \plainview\sdk_mcc\html\div;

/**
	@brief		Creates a pagination.
	@details

	Given some parameters, can generate HTML pagination with page links.

	The default is to make pagination that is compatible with Zurb's Foundation CSS (just add the containing UL around the output).

	@par		Usage

	Of a maximum of 186 pages, we are currently on page 77.

	$p = new \plainview\sdk_mcc\pagination\pagination;
	$p->page( 77 )
		->pages( 186 )
		->url( 'http://example.com/?p=%%PAGE%%' )
		->render();

	Let Pagination calculate the number of pages:

	$p = new \plainview\sdk_mcc\pagination\pagination;
	$p->page( 34 )
		->count( 5530 )
		->per_page( 186 )
		->url( 'http://example.com/?p=%%PAGE%%' )
		->render();

	This should show the use 5530 / 186 ~= 30 pages.

	After having run render() once, the pagination class itself can be called multiple times to display the same pagination: use __toString.

	@par		Widths

	A "width" of pages is how many pages to either side of the target to display.

	->width_first() is how many pages from page 1 to display
	->width_lower() current page's lower width
	->width_upper() current page's upper width
	->width_last() how many pages from max_pages to display

	The standard width is 2, meaning that for a max of 20 pages and current page 10:

	1 2 3 ... 8 9 10 11 12 .. 18 19 20

	Widths can be mixed:

	->width_first( 5 )
	->width_lower( 1 )
	->width_higher( 2 )
	->width_last( 0 )

	1 2 3 4 5 .. 9 10 11 12 .. 20

	Looks weird, though.

	@par		Overloading

	In order for the pagination to fit with your CSS of choice, overload the methods render_dots and render_page.

	@par		Changelog

	- 20130718 Initial version.

	@since		20130718
	@version	20130718
**/
class pagination
{
	use \plainview\sdk_mcc\html\element;
	use \plainview\sdk_mcc\traits\method_chaining;

	public $count = 100;
	public $page = 1;
	public $pages = null;
	public $per_page = 10;
	public $url = 'http://example.com/?p=%%PAGE%%';
	public $width = 2;
	public $width_first;
	public $width_last;
	public $width_lower;
	public $width_upper;

	public $render_output = '';

	public $tag = 'div';

	public function __construct()
	{
		$this->css_class( 'plainview_pagination' );
		$this->_construct();
		$this->width( $this->width );
	}

	public function __toString()
	{
		return $this->render_output;
	}

	/**
		@brief		Overloadable method for subclasses to use as constructor.
		@details

		Called after __construct() finished. This method allows for a clean _construct
		method in the subclasses, without having to remember to parent::__construct().

		@since		20130718
	**/
	public function _construct()
	{
	}

	/**
		@brief		Sets the item count.
		@param		int			$count			Number of items to paginate.
		@return		$this		Method chaining.
		@see		per_page()
		@since		20130718
	**/
	public function count( $count )
	{
		return $this->set_int( 'count', $count );
	}

	/**
		@brief		Calculate which pages to render.
		@details

		Returns an array of page numbers and strings "...".

		@return		array		Array of page numbers.
		@since		20130718
	**/
	public function get_pages_to_render()
	{
		$r = [];

		// Pagination looks like this: FIRST [DOTS] LOWER-CURRENT-UPPER [DOTS] LAST

		// How far away from 1 do we go?
		$first = min( 1 + $this->width_first, $this->pages );

		// The last part is the page count - width
		$last = min( $this->pages - $this->width_last, $this->pages );

		// The lower limit of the current page - width
		$lower = max( $first, $this->page - $this->width_lower );

		// The upper limit
		$upper = min( $this->page + $this->width_upper, $last );


		// FIRST
		for( $counter = 1; $counter <= $first ; $counter ++ )
			$r []= $counter;

		// FIRST [DOTS] LOWER
		if ( $counter < $lower )
		{
			$r[] = '...';
			$counter = $lower;
		}

		// LOWER...UPPER
		for( $counter = $counter; $counter <= $upper; $counter ++ )
			$r []= $counter;

		// UPPER [DOTS] LAST
		if ( $counter < $last )
		{
			$r[] = '...';
			$counter = $last;
		}

		// LAST
		for( $counter = $counter ; $counter <= $this->pages; $counter++ )
			$r []= $counter;

		///ddd( '1', $first, ' - ', $lower, $upper, ' - ', $last, $this->pages, $r );		exit;
		return $r;
	}

	/**
		@brief		Makes the URL for a page.
		@param		int			$page_number		Page number
		@return		string		Complete URL for a page.
		@see		url()
		@since		20130718
	**/
	public function make_url( $page_number )
	{
		return str_replace( '%%PAGE%%', $page_number, $this->url );
	}

	/**
		@brief		Sets the current page.
		@details

		This value is sanity checked during render() / __toString().

		@param		int			$page		Page number the user is currently viewing.
		@return		$this		Method chaining.
		@since		20130718
	**/
	public function page( $page )
	{
		return $this->set_int( 'page', $page );
	}

	/**
		@brief		Sets the maximum amount of pages.
		@details

		You can also use a combination of count() and per_page() to determine how many
		pages should be displayed in the pagination.

		@param		int			$pages		Count of pages.
		@return		$this		Method chaining.
		@see		count()
		@see		per_page()
		@since		20130718
	**/
	public function pages( $pages )
	{
		return $this->set_int( 'pages', $pages );
	}

	/**
		@brief		Returns the current page as 0-based.
		@return		int		The current page but 0-based.
		@since		20130718
	**/
	public function page_zero()
	{
		return $this->page - 1;
	}

	/**
		@brief		Set how many items are on a page.
		@param		int			$per_page		Number of items per page.
		@return		$this		Method chaining.
		@see		count()
		@since		20130718
	**/
	public function per_page( $per_page )
	{
		return $this->set_int( 'per_page', $per_page );
	}

	/**
		@brief		Do any caclutions prior to rendering.
		@return		$this		Method chaining.
		@see		render()
		@since		20130718
	**/
	public function prerender()
	{
		if ( $this->pages === null )
			$this->pages = ceil( intval( $this->count ) / intval( $this->per_page ) );
		return $this;
	}

	/**
		@brief		Renders the complete pagination HTML string.
		@return		string		The rendered pagination HTML.
		@see		prerender()
		@since		20130718
	**/
	public function render()
	{
		$this->prerender();
		$this->page = base::minmax( $this->page, 1, $this->pages );
		$this->render_output = $this->open_tag();
		$this->render_output .= $this->render_pages();
		$this->render_output .= $this->close_tag();
		return $this->render_output;
	}

	/**
		@brief		Renders a non-linked unspecified page.
		@details

		The "dots" means "...", which are used as filler items between actual page numbers.

		1 2 3 ... 7 8 9

		@return		string		Non-link HTML string.
		@since		20130718
	**/
	public function render_dots()
	{
		$page = new div;
		$page->tag = 'div';
		$page->css_class( 'unavailable' );
		$page->content = '&hellip;';
		return $page;
	}

	/**
		@brief		Renders a page link for a page number.
		@param		int			Page number
		@return		string		HTML page string.
		@since		20130718
	**/
	public function render_page( $page_number )
	{
		$page = new div;
		$page->tag = 'div';
		if( $page_number == $this->page )
			$page->css_class( 'current' );
		$page->content = sprintf( '<a href="%s">%s</a>',
			$this->make_url( $page_number ),
			$page_number
		);
		return $page;
	}

	/**
		@brief		Converts an array of page numbers to HTML.
		@return		string		HTML pagination string.
		@since		20130718
	**/
	public function render_pages()
	{
		$r = '';
		$pages_to_render = $this->get_pages_to_render();
		foreach( $pages_to_render as $page )
		{
			if ( $page == '...' )
				$r .= $this->render_dots();
			else
				$r .= $this->render_page( $page );
		}
		return $r;
	}

	/**
		@brief		Set the page URL.
		@details

		The string %%PAGE%% is replaced by the current page number.

		@param		string		$url		The full URL of the page.
		@return		$this				Method chaining.
		@since		20130718
	**/
	public function url( $url )
	{
		return $this->set_string( 'url', $url );
	}

	/**
		@brief		Set all widths simultaneously.
		@param		int		$width		Width (in pages) to set.
		@return		$this				Method chaining.
		@see		width_first()
		@see		width_last()
		@see		width_lower()
		@see		width_upper()
		@since		20130718
	**/
	public function width( $width )
	{
		$this->set_int( 'width', $width );
		$this->width_first = $this->width_lower = $this->width_upper = $this->width_last = $this->width;
		return $this;
	}

	/**
		@brief		Set the page width for page 1.
		@param		int		$width		Width (in pages) to set.
		@return		$this				Method chaining.
		@see		width_last()
		@see		width_lower()
		@see		width_upper()
		@see		widths()
		@since		20130718
	**/
	public function width_first( $width_first )
	{
		return $this->set_int( 'width_first', $width_first );
	}

	/**
		@brief		Set the page width for the last page.
		@param		int		$width		Width (in pages) to set.
		@return		$this				Method chaining.
		@see		width_first()
		@see		width_lower()
		@see		width_upper()
		@see		widths()
		@since		20130718
	**/
	public function width_last( $width_last )
	{
		return $this->set_int( 'width_last', $width_last );
	}

	/**
		@brief		Set the page width for the lower limit of the current page.
		@param		int		$width		Width (in pages) to set.
		@return		$this				Method chaining.
		@see		width_first()
		@see		width_last()
		@see		width_upper()
		@see		widths()
		@since		20130718
	**/
	public function width_lower( $width_lower )
	{
		return $this->set_int( 'width_lower', $width_lower );
	}

	/**
		@brief		Set the page width for the upper limit of the current page.
		@param		int		$width		Width (in pages) to set.
		@return		$this				Method chaining.
		@see		width_first()
		@see		width_last()
		@see		width_lower()
		@see		widths()
		@since		20130718
	**/
	public function width_upper( $width_upper )
	{
		return $this->set_int( 'width_upper', $width_upper );
	}
}
