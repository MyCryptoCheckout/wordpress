<?php

namespace plainview\sdk_mcc\wordpress\plugin_pack;

use ReflectionClass;

/**
	@brief		A plugin pack plugin.
	@since		2014-05-07 22:55:19
**/
class plugin
	extends \plainview\sdk_mcc\collections\collection
{
	/**
		@brief		Constructor.
		@since		2014-05-08 00:27:21
	**/
	public function __construct( $plugins )
	{
		$this->set( 'plugins', $plugins );
	}

	/**
		@brief		Activate the plugin.
		@since		2014-04-05 21:05:12
	**/
	public function activate()
	{
		// TODO: convert this to paths() when the SDK is updated everywhere.
		$filename = $this->get( 'class' )->paths[ 'filename_from_plugin_directory' ];
		$hook = sprintf( 'activate_%s', plugin_basename( $filename ) );
		do_action( $hook );
	}

	/**
		@brief		Deactivate the plugin.
		@since		2014-04-05 21:05:12
	**/
	public function deactivate()
	{
		// TODO: convert this to paths() when the SDK is updated everywhere.
		$filename = $this->get( 'class' )->paths[ 'filename_from_plugin_directory' ];
		$hook = sprintf( 'deactivate_%s', plugin_basename( $filename ) );
		do_action( $hook );
	}

	/**
		@brief		Returns the class name of the plugin we handle.
		@since		2014-05-08 00:31:38
	**/
	public function get_classname()
	{
		return $this->get( 'classname' );
	}

	/**
		@brief		Return the content of the plugin.
		@since		2014-04-06 11:01:06
	**/
	public function get_comment( $key = '' )
	{
		if ( $this->has( 'comment' ) )
		{
			$comment = $this->get( 'comment' );
			if ( $key == '' )
				return $comment;
			if ( isset( $comment->$key ) )
				return $comment->$key;
			return '';
		}

		$text = $this->get_file_contents();
		// Strip off everything after the first comment to save memory in the tokenizer.
		$text = preg_replace( '/\*\*\\/.*/s', '', $text );
		// Terminate the comment.
		$text .= '**/';

		$comments = array_filter( token_get_all( $text ), function( $entry )
		{
			return $entry[0] == T_DOC_COMMENT;
		});
		$comment = array_shift( $comments );
		$comment = $comment[ 1 ];

		$current_key = '';
		$lines = explode( "\n", $comment );
		$r = [];

		// Parse the comment into its various headings.
		foreach( $lines as $line )
		{
			$line = trim( $line );
			if ( $line == '/**' )
				continue;
			if ( $line == '**/' )
				continue;

			if ( ( strlen( $line ) > 0 ) && ( $line[ 0 ] == '@' ) )
			{
				$current_key = preg_replace( '/@([a-zA-Z_0-9]*).*/', '\1', $line );
				$text = preg_replace( '/@[a-zA-Z_0-9]*[\t]*+/', '', $line );
				if ( $text == '' )
					continue;
			}
			else
				$text = $line;

			if ( ! isset( $r[ $current_key ] ) )
			{
				if ( $text != '' )
					$r[ $current_key ] = $text;
			}
			else
				$r[ $current_key ] .= "\n" . $text;
		}

		$this->set( 'comment', (object)$r );
		return $this->get_comment( $key );
	}

	/**
		@brief		Return the plugin description.
		@since		2014-04-05 20:44:58
	**/
	public function get_brief_description()
	{
		return $this->get_comment( 'brief' );
	}

	/**
		@brief		Retrieves the file contents of the plugin.
		@since		2014-05-08 16:26:36
	**/
	public function get_file_contents()
	{
		return file_get_contents( $this->get_filename() );
	}

	/**
		@brief		Returns the filename in which this plugin is defined.
		@since		2014-05-08 00:31:17
	**/
	public function get_filename()
	{
		$rc = new ReflectionClass( $this->get_classname() );
		return $rc->getFileName();
	}

	/**
		@brief		Return a 16 character hash of the classname.
		@since		2014-05-08 16:18:02
	**/
	public function get_id()
	{
		$id = md5( $this->get( 'classname' ) );
		$id = substr( $id, 0, 16 );
		return $id;
	}

	/**
		@brief		Returns the "name" of the class, after fixing it up and removing underscores and prefixes.
		@since		2014-05-08 16:19:04
	**/
	public function get_name()
	{
		if ( $this->has( 'name' ) )
			return $this->get( 'name' );
		$name = $this->get( 'plugins' )->pp()->get_plugin_classname( $this->get( 'classname' ) );
		$this->set( 'name', $name );
		return $this->get_name();
	}

	/**
		@brief		Is this plugin loaded?
		@since		2014-05-08 16:15:32
	**/
	public function is_loaded()
	{
		return $this->has( 'class' );
	}

	/**
		@brief		Loads the class into memory.
		@since		2014-05-08 00:29:40
	**/
	public function load()
	{
		$classname = $this->get( 'classname' );
		$parameter = null;
		$reflection = new \ReflectionClass( $classname );
		if ( $reflection->isSubClassOf( '\\plainview\\sdk_mcc\\wordpress\\base' ) )
			$parameter = $reflection->getFilename();
		$class = new $classname( $parameter );
		return $this->set( 'class', $class );
	}

	/**
		@brief		Return an instance of the plugin.
		@since		2014-05-08 16:35:12
	**/
	public function plugin()
	{
		if ( ! $this->is_loaded() )
			$this->load();
		return $this->get( 'class' );
	}

	/**
		@brief		Sets our class name.
		@since		2014-05-08 00:28:19
	**/
	public function set_classname( $classname )
	{
		return $this->set( 'classname', $classname );
	}

	/**
		@brief		Ask the plugin to uninstall itself.
		@since		2014-07-16 13:31:16
	**/
	public function uninstall()
	{
		$this->plugin()->uninstall_internal();
	}
}
