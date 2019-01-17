<?php

namespace plainview\sdk_mcc\wordpress\tabs;

/**
	@brief		Actual tab that tabs contains.

	@par		Changelog

	- 20130902	heading_prefix(), heading_suffix(), name_prefix(), name_suffix(), name(), id(). Uses method_chaining trait.
	- 20130505	New: parameters()
	- 20130503	Initial release

	@since		20130503
	@version	20130902
**/
class tab
{
	use \plainview\sdk_mcc\traits\method_chaining;
	use \plainview\sdk_mcc\html\element;
	use \plainview\sdk_mcc\traits\sort_order;

	/**
		@brief		Tab callback function.
		@details	An array of (class, function_name) or just a function name.
					The default callback is the ID of the tab.
		@see		tabs::tab
		@since		20130503
		@var		$callback
	**/
	public $callback;

	/**
		@brief		Optional count to be displayed after the tab name. Default is no count.
		@since		20130503
		@var		$count
	**/
	public $count = '';

	/**
		@brief		The current / active status of the tab.
		@since		2015-12-27 13:08:44
	**/
	public $current = false;

	/**
		@brief		Optional heading to display as the page heading instead of the tab name.
		@since		20130503
		@var		$heading
	**/
	public $heading;

	/**
		@brief		Prefix that is displayed before displaying the page heading.
		@since		20130503
		@var		$heading_prefix
	**/
	public $heading_prefix;

	/**
		@brief		Suffix that is displayed after displaying the page heading.
		@since		20130503
		@var		$heading_suffix
	**/
	public $heading_suffix;

	/**
		@brief		The ID of the tab.
		@since		20130503
		@var		$id
	**/
	public $id;

	/**
		@brief		Displayed name of tab.
		@since		20130503
		@var		$name
	**/
	public $name;

	/**
		@brief		Prefix that is displayed before displaying the tab name.
		@since		20130503
		@var		$name_prefix
	**/
	public $name_prefix;

	/**
		@brief		Suffix that is displayed after displaying the tab name.
		@since		20130503
		@var		$name_suffix
	**/
	public $name_suffix;

	/**
		@brief		An optional array of parameters to send to the callback.
		@since		20130505
		@var		$parameters
	**/
	public $parameters = array();

	/**
		@brief		The HTML element tag.
		@since		2015-07-07 19:33:38
	**/
	public $tag = 'li';

	/**
		@brief		The \\plainview\\sdk_mcc\\wordpress\\tabs\\tabs object this tab belongs to.
		@since		20130503
		@var		$tabs
	**/
	public $tabs;

	/**
		@brief		The HTML title associated with the tab name.
		@since		20130503
		@var		$title
	**/
	public $title;

	/**
		@brief		The URL for this tab.
		@since		2015-12-27 13:09:54
	**/
	public $url;

	public function __construct( $tabs )
	{
		$this->tabs = $tabs;
		$this->name_prefix( $tabs->tab_name_prefix );
		$this->name_suffix( $tabs->tab_name_suffix );
		$this->heading_prefix( $tabs->tab_heading_prefix );
		$this->heading_suffix( $tabs->tab_heading_suffix );
		return $this;
	}

	/**
		@brief		Sets the callback for this tab.
		@details	Either a class + function combination or just the function.
		@param		mixed		$callback		A class or function name.
		@param		string		$function		If $callback is a class, this is the method within the class to be called.
		@return		object						This tab.
		@since		20130503
	**/
	public function callback( $callback, $function = '' )
	{
		if ( $function != '' )
			$callback = array( $callback, $function );
		$this->callback = $callback;
		return $this;
	}

	/**
		@brief		Convenience function to call a method of the base object.
		@param		string		$method		Name of method to call.
		@return		this					Method chaining.
		@since		20130503
	**/
	public function callback_this( $method )
	{
		// Retrieve the class object that called this.
		$trace = debug_backtrace();
		array_shift( $trace );
		$trace = reset( $trace );
		$class = $trace[ 'object' ];
		return $this->callback( $class, $method );
	}

	/**
		@brief		Set the current / active status of the tab.
		@since		2015-12-27 13:09:13
	**/
	public function current( $current )
	{
		return $this->set_key( 'current', $current );
	}

	public function get_heading()
	{
		$name = ( $this->heading != '' ? $this->heading : $this->name );
		return $this->heading_prefix . $name . $this->heading_suffix;
	}

	/**
		@brief		Return the complete link HTML.
		@since		2015-12-27 13:12:24
	**/
	public function display_link()
	{
	}

	public function display_name()
	{
		return $this->name_prefix . $this->name . $this->name_suffix;
	}

	/**
		@brief		Set the page heading for this tab.
		@details	Optionally display this heading instead of the tab name as the page heading.
		@param		string		$heading		The page heading to set.
		@return		object						This tab.
		@since		20130503
	**/
	public function heading( $heading )
	{
		return $this->set_key( 'heading', $heading );
	}

	/**
		@brief		Translate and set the page heading for this tab.
		@details	Almost the same as heading(), except the string is translated first.
		@param		string		$heading		The page heading to translate and set.
		@return		this					Method chaining.
		@see		heading()
		@since		20130503
	**/
	public function heading_( $heading )
	{
		return $this->heading( call_user_func_array( [ $this->tabs->base, '_' ], func_get_args() ) );
	}

	/**
		@brief		Set the tab prefix in the tab list.
		@param		string		$heading_prefix		Prefix to set.
		@return		this					Method chaining.
		@since		20130902
	**/
	public function heading_prefix( $heading_prefix )
	{
		return $this->set_key( 'heading_prefix', $heading_prefix );
	}

	/**
		@brief		Set the tab suffix in the tab list.
		@param		string		$heading_suffix		Suffix to set.
		@return		this					Method chaining.
		@since		20130902
	**/
	public function heading_suffix( $heading_suffix )
	{
		return $this->set_key( 'heading_suffix', $heading_suffix );
	}

	/**
		@brief		Set the tab's ID.
		@param		string		$id		The tab's new id.
		@return		this					Method chaining.
		@since		20130902
	**/
	public function id( $id )
	{
		return $this->set_key( 'id', $id );
	}

	/**
		@brief		Set the name of this tab.
		@details	The name is displays in the tab list and as the page heading, if no specific page heading is set.
		@param		string		$name		The new name of the tab.
		@return		this					Method chaining.
		@since		20130503
	**/
	public function name( $name )
	{
		return $this->set_key( 'name', $name );
	}

	/**
		@brief		Set the tab prefix in the tab list.
		@param		string		$name_prefix		Prefix to set.
		@return		this					Method chaining.
		@since		20130902
	**/
	public function name_prefix( $name_prefix )
	{
		return $this->set_key( 'name_prefix', $name_prefix );
	}

	/**
		@brief		Set the tab suffix in the tab list.
		@param		string		$name_suffix		Suffix to set.
		@return		this					Method chaining.
		@since		20130902
	**/
	public function name_suffix( $name_suffix )
	{
		return $this->set_key( 'name_suffix', $name_suffix );
	}

	/**
		@brief		Translate and set the name of this tab.
		@param		string		$name		String to translate and set as the name.
		@return		this					Method chaining.
		@see		name()
		@since		20130503
	**/
	public function name_( $name )
	{
		return $this->name( call_user_func_array( array( $this->tabs->base, '_' ), func_get_args() ) );
	}

	/**
		@brief		Set the parameters for the tab's callback.
		@details	All parameters used in this method are also sent directly to the callback.
		@return		this					Method chaining.
		@since		20130505
	**/
	public function parameters()
	{
		$this->parameters = func_get_args();
		return $this;
	}

	/**
		@brief		Return the tab object this tab belongs to.
		@return		tabs		The tabs object.
		@since		20130902
	**/
	public function tabs()
	{
		return $this->tabs;
	}

	/**
		@brief		Set the HTML title of the page name in the tab list.
		@param		string		$title		Title to set.
		@return		this					Method chaining.
		@since		20130503
	**/
	public function title( $title )
	{
		return $this->set_key( 'title', $title );
	}

	/**
		@brief		Translate and set the HTML title of the page name.
		@param		string		$title		Title to translate and set.
		@return		this					Method chaining.
		@see		title()
		@since		20130503
	**/
	public function title_( $title )
	{
		return $this->title( call_user_func_array( array( $this->tabs->base, '_' ), func_get_args() ) );
	}

	/**
		@brief		Set the url.
		@since		2015-12-27 13:09:13
	**/
	public function url( $url )
	{
		return $this->set_key( 'url', $url );
	}
}
