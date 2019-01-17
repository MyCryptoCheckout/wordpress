<?php

namespace plainview\sdk_mcc\wordpress\traits;

/**
	@brief		Add debug methods to the plugin.
	@details

	The main method is debug(), with is_debugging() being queried to ascertain whether the user is in debug mode.

	1. Add to your class:

		use \plainview\sdk_mcc\wordpress\debug;

	2. Add these rows to your site options:

		'debug' => false,									// Display debug information?
		'debug_ips' => '',									// List of IP addresses that can see debug information, when debug is enabled.
		'debug_to_browser' => false,						// Display debug info in the browser?
		'debug_to_file' => false,							// Save debug info to a file.

	3.1	In the settings form, display the inputs:

		$this->add_debug_settings_to_form( $form );

	3.2 In the settings form, save your settings.

		$this->save_debug_settings_from_form( $form );

	@since		2014-05-01 08:56:20
**/
trait debug
{
	/**
		@brief		Adds the debug settings to a form.
		@since		2014-05-01 08:57:39
	**/
	public function add_debug_settings_to_form( $form )
	{
		// We need this so that the options use the correct namespace.
		$instance = self::instance();

		$fs = $form->fieldset( 'fs_debug' );
		$fs->legend->label( 'Debugging' );

		// You are currently NOT in debug mode.
		$not = $this->_( 'not' );

		$fs->markup( 'debug_info' )
			->p( "According to the settings below, you are currently%s in debug mode. Don't forget to reload this page after saving the settings.", $this->debugging() ? '' : " <strong>$not</strong>" );

		$debug = $fs->checkbox( 'debug' )
			->description( 'Show debugging information in various places.' )
			->label( 'Enable debugging' )
			->checked( $instance->get_site_option( 'debug', false ) );

		$fs->checkbox( 'debug_to_browser' )
			->description( 'Show the debugging information in the browser.' )
			->label( 'Show debug in the browser' )
			->checked( $instance->get_site_option( 'debug_to_browser', false ) );

		$debug_to_file = $fs->checkbox( 'debug_to_file' )
			->label( 'Save debug to file' )
			->checked( $instance->get_site_option( 'debug_to_file', false ) );

		// We need to set the description unescaped due to the link.
		$filename = $this->get_debug_filename();
		$basename = basename( $filename );
		$filename = sprintf( '<a href="%s">%s</a>',
			$this->paths( 'url' ) . '/' . $basename,
			$basename
		);
		$description = $this->_( 'The debug data will be saved to the file %s. This link is distributable.', $filename );
		$debug_to_file->description
			->get_label()
			->content = $description;

		$fs->checkbox( 'delete_debug_file' )
			->description( 'Delete the contents of the debug file now after saving the settings.' )
			->label( 'Delete debug file' )
			->checked( false );

		$fs->textarea( 'debug_ips' )
			->description( 'Only show debugging info to specific IP addresses. Use spaces between IPs. You can also specify part of an IP address. Your address is %s', $_SERVER[ 'REMOTE_ADDR' ] )
			->label( 'Debug IPs' )
			->rows( 5, 16 )
			->trim()
			->value( $instance->get_site_option( 'debug_ips', '' ) );
	}

	/**
		@brief		Output a string if in debug mode.
		@since		20140220
	*/
	public function debug( $string )
	{
		if ( ! $this->debugging() )
			return;

		// Convert the non-string arguments into lovely code blocks.
		$args = func_get_args();
		foreach( $args as $index => $arg )
		{
			$export = false;
			$export |= is_array( $arg );
			$export |= is_object( $arg );
			if ( $export )
				$args[ $index ] = sprintf( '<pre><code>%s</code></pre>', htmlspecialchars( var_export( $arg, true ) ) );
		}

		// Put all of the arguments into one string.
		$text = @ call_user_func_array( 'sprintf', $args );
		if ( $text == '' )
			$text = $string;

		// We want the name of the class.
		$class_name = get_called_class();
		// But without the namespace
		$class_name = preg_replace( '/.*\\\/', '', $class_name );

		$microtime = substr( microtime( true ), 11 );
		if ( strlen( $microtime ) < 4 )
			$microtime = '0' . $microtime;

		// Assemble the debug text.
		$data = [
			'timestamp' => date( 'Y-m-d H:i:s' ) . '.' . $microtime,
			'class_name' => $this->get_debug_class_name( $class_name ),
			'text' => $text . "<br/>\n",
		];
		$data = (object) $data;
		// Allow other classes to modify the text. Or decide not to log it at all.
		$filter = $class_name . '_debug_text';
		$data = apply_filters( $filter, $data );
		// Empty text = do not log.
		if ( $data->text == '' )
			return;

		$text = sprintf( "%s %s: %s", $data->timestamp, $data->class_name, $data->text );

		$plugin = self::instance();

		if ( $this->debugging_to_browser() )
		{
			echo $text;
			if ( ob_get_contents() )
				ob_flush();
		}

		if ( $this->debugging_to_file() )
		{
			$filename = $this->get_debug_filename();
			file_put_contents( $filename, $text, FILE_APPEND );
		}
	}

	/**
		@brief		Is Broadcast in debug mode?
		@since		20140220
	*/
	public function debugging()
	{
		// We need this so that the options use the correct namespace.
		$plugin = self::instance();

		$debugging = $plugin->get_site_option( 'debug', false );
		if ( ! $debugging )
			return false;

		// Debugging is enabled. Now check if we should show it to this user.
		$ips = $plugin->get_site_option( 'debug_ips', '' );
		// Empty = no limits.
		if ( $ips == '' )
			return true;

		$ips = str_replace( "\r", '', $ips );
		$lines = explode( "\n", $ips );
		$lines = array_filter( $lines );
		foreach( $lines as $line )
			if ( strpos( $_SERVER[ 'REMOTE_ADDR' ], trim( $line ) ) !== false )
				return true;

		// No match = not debugging for this user.
		return false;
	}

	/**
		@brief		Are we debugging to the browser?
		@since		2015-10-03 16:56:50
	**/
	public function debugging_to_browser()
	{
		if ( ! $this->debugging() )
			return false;

		$plugin = self::instance();
		return $plugin->get_site_option( 'debug_to_browser', false );
	}

	/**
		@brief		Are we debugging to a file?
		@since		2015-10-03 16:57:42
	**/
	public function debugging_to_file()
	{
		if ( ! $this->debugging() )
			return false;

		$plugin = self::instance();
		return $plugin->get_site_option( 'debug_to_file', false );
	}

	/**
		@brief		Return the filename of the debug file.
		@since		2015-07-25 13:45:32
	**/
	public function get_debug_filename()
	{
		$hash = md5( $this->paths( '__FILE__' ) . AUTH_KEY );
		$hash = substr( $hash, 0, 8 );
		return $this->paths( '__FILE__' ) . ".$hash.debug.html";
	}

	/**
		@brief		Modify the debug class name, if necessary.
		@since		2017-10-28 18:11:53
	**/
	public function get_debug_class_name( $class_name )
	{
		return $class_name;
	}

	/**
		@brief		Saves the debug settings from the form.
		@since		2014-05-01 08:58:22
	**/
	public function save_debug_settings_from_form( $form )
	{
		// We need this so that the options use the correct namespace.
		$instance = self::instance();

		$instance->update_site_option( 'debug', $form->input( 'debug' )->is_checked() );
		$instance->update_site_option( 'debug_to_browser', $form->input( 'debug_to_browser' )->is_checked() );
		$instance->update_site_option( 'debug_to_file', $form->input( 'debug_to_file' )->is_checked() );
		$instance->update_site_option( 'debug_ips', $form->input( 'debug_ips' )->get_filtered_post_value() );

		if ( $form->input( 'delete_debug_file' )->is_checked() )
		{
			$filename = $this->get_debug_filename();
			if ( is_writeable( $filename ) )
				unlink( $filename );
		}
	}
}
