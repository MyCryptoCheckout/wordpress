<?php

namespace plainview\sdk_mcc\wordpress\plugin_pack;

use \Exception;
use \plainview\sdk_mcc\collections\collection;

/**
	@brief		A collection of plugin objects.
	@since		2014-05-07 22:54:56
**/
class plugins
	extends \plainview\sdk_mcc\collections\collection
{
	/**
		@brief		Do we need to save the plugins?
		@since		2023-05-03 22:37:30
	**/
	public $__need_to_save = false;
	/**
		@brief		The plugin pack container class.
		@since		2014-05-08 00:12:04
	**/
	public $__plugin_pack;

	/**
		@brief		Construct.
		@since		2014-05-08 00:10:17
	**/
	public function __construct( $pp )
	{
		$this->__plugin_pack = $pp;
	}

	/**
		@brief		Activate all loaded plugins.
		@since		2014-09-28 14:01:14
	**/
	public function activate()
	{
		foreach( $this->items as $plugin )
			if ( $plugin->is_loaded() )
				$plugin->activate();
		return $this;
	}

	/**
		@brief		Return an array of groups containing the grouped plugins.
		@since		2015-06-08 21:58:38
	**/
	public function by_groups()
	{
		$r = [];
		foreach( $this->items as $plugin )
		{
			$group = $plugin->get_comment( 'plugin_group' );
			if ( ! isset( $r[ $group ] ) )
				$r[ $group ] = [];
			$r[ $group ] []= $plugin;
		}
		ksort( $r );
		return $r;
	}

	/**
		@brief		Deactivate all loaded plugins.
		@since		2014-09-28 14:01:14
	**/
	public function deactivate()
	{
		foreach( $this->items as $plugin )
			if ( $plugin->is_loaded() )
				$plugin->deactivate();
		return $this;
	}

	/**
		@brief		Return a collection of plugins with the specified ID numbers.
		@since		2014-05-08 16:32:16
	**/
	public function from_ids( $ids )
	{
		$r = new collection;

		foreach( $this->items as $plugin )
			foreach( $ids as $id )
				if ( $plugin->get_id() == $id )
					$r->append( $plugin );

		return $r;
	}

	/**
		@brief		Load the populated plugins.
		@since		2014-05-08 00:05:38
	**/
	public function load()
	{
		foreach( $this->items as $classname => $plugin )
		{
			if ( $plugin->is_loaded() )
				continue;
			try
			{
				$plugin->load();
			}
			catch( Exception $e )
			{
				// This class no long exists or could not be loaded. Delete it.
				$this->forget( $classname );
				$this->need_to_save();
			}
		}
		return $this;
	}

	/**
		@brief		Resave our data if necessary.
		@since		2014-05-09 10:36:08
	**/
	public function maybe_save()
	{
		if ( $this->__need_to_save )
			return;
		return $this->save();
	}

	/**
		@brief		Set the flag stating that we need to resave our activated plugins at the first best chance.
		@since		2014-05-09 10:35:00
	**/
	public function need_to_save( $need_to_save = true )
	{
		$this->__need_to_save = $need_to_save;
		return $this;
	}

	/**
		@brief		Add one or more plugins to our list of plugins.
		@since		2014-05-08 00:04:13
	**/
	public function populate( $classnames )
	{
		if ( ! is_array( $classnames ) )
			$classnames = [ $classnames ];

		foreach( $classnames as $classname )
		{
			if ( $this->has( $classname ) )
				continue;
			$plugin = new plugin( $this );
			$plugin->set_classname( $classname );
			$this->set( $classname, $plugin );
		}
		return $this;
	}

	/**
		@brief		Return the plugin pack class that created us.
		@since		2014-05-08 16:20:04
	**/
	public function pp()
	{
		return $this->__plugin_pack;
	}

	/**
		@brief		Asks the plugin pack to save our data.
		@since		2014-05-09 10:36:33
	**/
	public function save()
	{
		$classnames = [];
		foreach( $this as $plugin )
			$classnames[] = $plugin->get_classname();
		// Make the classnames unique.
		$classnames = array_flip( $classnames );
		$classnames = array_flip( $classnames );
		$this->pp()->set_enabled_plugins( $classnames );
		return $this;
	}
}