<?php

namespace plainview\sdk_mcc\wordpress\plugin_pack\actions;

/**
	@brief		Return an array of available classes for this plugin pack.
	@since		2014-11-13 20:26:07
**/
class get_plugin_classes
	extends \plainview\sdk_mcc\wordpress\actions\action
{
	/**
		@brief		Prefix that is assigned by the plugin pack.
		@since		2014-11-13 20:27:39
	**/
	public $__prefix;

	/**
		@brief		OUT: Array of plugin class names.
		@since		2014-11-13 20:27:54
	**/
	public $classes = [];

	/**
		@brief		Convenience method to add a plugin classname, or several, to the array.
		@since		2014-11-13 20:29:42
	**/
	public function add( $classname )
	{
		if ( ! is_array( $classname ) )
			$classname = [ $classname ];
		foreach( $classname as $class )
			$this->classes[ $class ] = $class;
	}

	/**
		@brief		Override, allowing the plugin pack to give us our prefix.
		@details	Else all installed plugin packs will use the same action name.
		@since		2014-11-13 20:42:00
	**/
	public function get_prefix()
	{
		return $this->__prefix;
	}
}
