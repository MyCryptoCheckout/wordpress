<?php

namespace plainview\sdk_mcc\wordpress\tabs;

use \Exception;

/**
	@brief		Handles creation of tabs in the Wordpress admin panel.

	@par		Changelog

	- 20131006	get_is()
	- 20130902	tab_heading_*fix, tab_name_*fix
	- 20130810	The current tab's link is cleaned.
	- 20130809	Countable.
	- 20130530	get() and get_key() added.
	- 20130506	output() changed to render()
	- 20130503	Initial release

	@author		Edward Plainview	edward@plainview.se
	@since		20130503
	@version	20131006
**/

abstract class tabs
	implements \Countable
{
	use \plainview\sdk_mcc\traits\method_chaining;
	use \plainview\sdk_mcc\html\element;

	/**
		@brief		\\plainview\\sdk_mcc\\wordpress\\base object that created these tabs.
		@since		20130503
		@var		$base
	**/
	public $base;

	/**
		@brief		The ID of the default tab, if none is selected in the _GET.
		@details	If no default tab is set, the first added tab is assumed to be the default.
		@since		20130503
		@since		20130503
		@var		$default_tab
	**/
	public $default_tab = '';

	/**
		@brief		Display the selected tab?
		@since		20130503
		@var		$display_tab
	**/
	public $display_tab = true;

	/**
		@brief		Display the name / heading of the selected tab?
		@since		20130503
		@var		$display_tab_name
	**/
	public $display_tab_name = true;

	/**
		@brief		_GET variable to use. The default is the actual _GET.
		@since		20130503
		@since		20130503
		@var		$get
	**/
	public $get;

	/**
		@brief		Which key in the _GET variable contains the ID of the current tab.
		@since		20130503
		@since		20130503
		@var		$get_key
	**/
	public $get_key = 'tab';

	/**
		@brief		The default prefix of the displayed tab name.
		@details	The tab inherits this value upon creation.
		@since		20130503
		@var		$tab_heading_prefix
	**/
	public $tab_heading_prefix = '<h1>';

	/**
		@brief		The default suffix of the displayed tab name.
		@details	The tab inherits this value upon creation.
		@since		20130503
		@var		$tab_heading_suffix
	**/
	public $tab_heading_suffix = '</h1>';

	/**
		@brief		The default prefix of the displayed tab name.
		@details	The tab inherits this value upon creation.
		@since		20130503
		@var		$tab_name_prefix
	**/
	public $tab_name_prefix = '';

	/**
		@brief		The default suffix of the displayed tab name.
		@details	The tab inherits this value upon creation.
		@since		20130503
		@var		$tab_name_suffix
	**/
	public $tab_name_suffix = '';

	/**
		@brief		Collection of \\plainview\\sdk_mcc\\wordpress\\tabs\\tab objects.
		@since		20130503
		@var		$tabs
	**/
	public $tabs;

	/**
		@brief		The HTML element tag.
		@since		2015-07-07 19:29:43
	**/
	public $tag = 'ul';

	/**
		@brief		Array of _GET keys to preserve when creating tab links.
		@since		20130503
		@var		$valid_get_keys
	**/
	public $valid_get_keys = array( 'page' );

	public function __construct( $base )
	{
		$this->base = $base;
		$this->tabs = new \plainview\sdk_mcc\collections\Collection();
		$this->_construct();
	}

	/**
		@brief		Overridable constructor for the subclasses.
		@since		2015-12-27 12:42:15
	**/
	public function _construct()
	{
	}

	public function __toString()
	{
		return $this->render();
	}

	/**
		@brief		Return how many tabs are registered.
		@return		int		The count of tabs registered.
		@since		20130809
	**/
	public function count()
	{
		return count( $this->tabs );
	}

	/**
		@brief		Create a tab.
		@since		2015-12-27 12:48:51
	**/
	public abstract function create_tab();

	/**
		@brief		Sets the current tab.
		@param		string		$id				ID of tab to make the default.
		@return		tabs						Object chaining.
		@since		20130503
	**/
	public function default_tab( $id )
	{
		$this->default_tab = $id;
		return $this;
	}

	/**
		@brief		Sets the _GET array.
		@param		array		$get			The new _GET array from which to get the current tab.
		@return		tabs						Object chaining.
		@since		20130530
	**/
	public function get( $get )
	{
		$this->get = $get;
		return $this;
	}

	/**
		@brief		Is the _GET tab key equal to this value?
		@param		string		$value		Value to check
		@return		bool		True if the get key is equal to the value.
		@since		20131006
	**/
	public function get_is( $value )
	{
		if ( $this->get === null )
			$get = $_GET;
		else
			$get = $this->get;

		if ( ! isset( $get[ $this->get_key ] ) )
			return false;
		return ( $get[ $this->get_key ] == $value );
	}

	/**
		@brief		Sets the get key.
		@param		string		$get_key		New key for the _GET array.
		@return		tabs						Object chaining.
		@since		20130530
	**/
	public function get_key( $get_key )
	{
		$this->get_key = $get_key;
		return $this;
	}

	/**
		@brief		Return the separator between tabs, if any.
		@since		2015-12-27 13:11:01
	**/
	public function get_separator()
	{
		return '';
	}

	/**
		@brief		Return the tabs.
		@details	Although the tabs can be displayed using __toString, this method allows for finding and catching exceptions, which isn't allowed in __toString.
		@return		string		The tabs as a string.
		@since		20130503
	**/
	public function render()
	{
		if ( $this->get === null )
			$this->get = $_GET;
		$get = $this->get;					// Conv
		$get_key = $this->get_key;			// Conv

		// No tabs? Do nothing.
		if ( count( $this->tabs ) < 1 )
			return '';

		// Sort the tabs.
		$sorted = $this->tabs->sort_by( function( $tab )
		{
			return $tab->get_sort_order() . $tab->name;
		} );

		// Check that the default exists.
		if ( ! $this->tabs->has( $this->default_tab ) )
			$this->default_tab = key( $sorted->to_array() );

		// Select the default tab if none is selected.
		if ( ! isset( $get[ $get_key ] ) )
		{
			if ( $this->default_tab == '' )
				$this->default_tab = key( $sorted->to_array() );
			$get[ $get_key ] = $this->default_tab;
		}
		$selected = $get[ $get_key ];

		$r = '<div class="wrap">';

		if ( $this->display_tab_name )
			$r .= $this->tab( $selected )
				->get_heading();

		if ( count( $this->tabs ) > 1 )
		{
			// Step 1: display all the tabs
			$r .= $this->open_tag();
			$original_link = $_SERVER['REQUEST_URI'];

			foreach( $get as $key => $value )
				if ( ! in_array( $key, $this->valid_get_keys ) )
					$original_link = remove_query_arg( $key, $original_link );

			$counter = 1;

			foreach( $sorted as $tab_id => $tab )
			{
				// Make the link.
				// If we're already on that tab, just return the current url.
				if ( $get[ $get_key ] == $tab_id )
					$url = add_query_arg( $get_key, $tab_id, $original_link );
				else
				{
					if ( $tab_id == $this->default_tab )
						$url = remove_query_arg( $get_key, $original_link );
					else
						$url = add_query_arg( $get_key, $tab_id, $original_link );
				}
				$tab->url( $url );

				if ( $tab_id == $selected )
					$tab->current( true );

				$tab->css_class( 'tab_' . $tab_id );

				$r .= $tab->open_tag();
				$r .= $tab->display_link();
				$r .= $tab->close_tag();

				if ( $counter < count( $this->tabs ) )
					$r .= $this->get_separator();

				$counter++;
			}

			$r .= $this->close_tag();

			$r .= '<div class="clear" />';
		}

		// Step 2: maybe display the tab itself
		if ( $this->display_tab )
		{
			$tab = $this->tab( $selected );

			$r .= '<div class="tab_contents">';

			ob_start();

			try
			{
				call_user_func_array( $tab->callback, $tab->parameters );
			}
			catch ( Exception $e )
			{
				echo 'Exception: ' . $e->getMessage() . $e->getTraceAsString();
			}

			$r .= ob_get_clean();

			$r .= '</div><!-- tab_contents -->';
		}

		$r .= '</div>';	// wrap

		return $r;
	}

	/**
		@brief		Creates a new tab / retrieves an existing tab.
		@param		string		$id		ID of tab to create / retrieve.
		@return		tab					Tab object.
		@since		20130503
	**/
	public function tab( $id )
	{
		if ( $id == '' )
			return false;
		if ( $this->tabs->has( $id ) )
			return $this->tabs->get( $id );
		$tab = $this->create_tab();
		$tab->id( $id );
		$tab->callback_this( $id );		// Usually the tab's callback is the same as the ID.
		$this->tabs->set( $id, $tab );
		return $tab;
	}

	/**
		@brief		Sets the page heading prefix for all tabs.
		@param		string		$tab_heading_prefix		The new tab heading prefix.
		@return		this								Method chaining.
		@since		20130902
	**/
	public function tab_heading_prefix( $tab_heading_prefix )
	{
		return $this->set_key( 'tab_heading_prefix', $tab_heading_prefix );
	}

	/**
		@brief		Sets the page heading suffix for all tabs.
		@param		string		$tab_heading_suffix		The new tab heading suffix.
		@return		this								Method chaining.
		@since		20130902
	**/
	public function tab_heading_suffix( $tab_heading_suffix )
	{
		return $this->set_key( 'tab_heading_suffix', $tab_heading_suffix );
	}

	/**
		@brief		Sets the tab name prefix for all tabs.
		@param		string		$tab_name_prefix		The new tab name prefix.
		@return		this								Method chaining.
		@since		20130902
	**/
	public function tab_name_prefix( $tab_name_prefix )
	{
		return $this->set_key( 'tab_name_prefix', $tab_name_prefix );
	}

	/**
		@brief		Sets the tab name suffix for all tabs.
		@param		string		$tab_name_suffix		The new tab name suffix.
		@return		this								Method chaining.
		@since		20130902
	**/
	public function tab_name_suffix( $tab_name_suffix )
	{
		return $this->set_key( 'tab_name_suffix', $tab_name_suffix );
	}
}
