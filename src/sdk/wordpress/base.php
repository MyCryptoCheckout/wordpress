<?php
namespace plainview\sdk_mcc\wordpress;

/**
	@brief		Base class for the Plainview Wordpress SDK.
	@details	Provides a framework with which to build Wordpress modules.
	@author		Edward Plainview	edward@plainview.se
	@copyright	GPL v3
**/
class base
	extends \plainview\sdk_mcc\base
{
	use actions_and_filters_trait;

	/**
		@brief		Stores whether this blog is a network blog.
		@since		20130416
		@var		$is_network
	**/
	protected $is_network;

	/**
		@brief		Text domain of .PO translation.
		If left unset will be set to the base filename minus the .php
		@var		$language_domain
		@since		20130416
	**/
	protected $language_domain = '';

	/**
		@brief		Contains the paths to the plugin and other places of interest.

		The keys in the array are:

		__FILE__</br>
		name<br />
		filename<br />
		filename_from_plugin_directory<br />
		path_from_plugin_directory<br />
		path_from_base_directory<br />
		url<br />

		@since		20130416
		@var		$paths
	**/
	public $paths = [];

	/**
		@brief		The version of the plugin.
		@since		20130811
		@var		$plugin_version
	**/
	public $plugin_version = 20000101;

	/**
		@brief		Links to Wordpress' database object.
		@since		20130416
		@var		$wpdb
	**/
	protected $wpdb;

	/**
		@brief		Construct the class.
		@param		string		$filename		The __FILE__ special variable of the parent.
		@since		20130416
	**/
	public function __construct( $__FILE__ = null )
	{
		// If no filename was specified, try to get the parent's filename.
		if ( $__FILE__ === null )
		{
			$stacktrace = @debug_backtrace( false );
			$__FILE__ = $stacktrace[ 0 ][ 'file' ];
		}

		if ( ! defined( 'ABSPATH' ) )
		{
			// Was this run from the command line?
			if ( isset( $_SERVER[ 'argc'] ) )
			{
				$this->paths = [
					'__FILE__' => $__FILE__,
					'name' => get_class( $this ),
					'filename' => basename( $__FILE__ ),
				];
				$this->do_cli();
			}
			else
				wp_die( 'ABSPATH is not defined!' );
		}

		parent::__construct();

		global $wpdb;
		$this->wpdb = $wpdb;

		$this->is_network = MULTISITE;
		$this->is_multisite = MULTISITE;

		$this->submenu_pages = new \plainview\sdk_mcc\collections\collection;

		// Completely different path handling for Windows and then everything else. *sigh*
		if ( PHP_OS == 'WINNT' )
		{
			$wp_plugin_dir = str_replace( '/', DIRECTORY_SEPARATOR, WP_PLUGIN_DIR );
			$base_dir = dirname( dirname( WP_PLUGIN_DIR ) );

			$path_from_plugin_directory = dirname( str_replace( $wp_plugin_dir, '', $__FILE__ ) );
			$__FILE___from_plugin_directory = $path_from_plugin_directory . DIRECTORY_SEPARATOR . basename( $__FILE__ );

			$this->paths = [
				'__FILE__' => $__FILE__,
				'name' => get_class( $this ),
				'filename' => basename( $__FILE__ ),
				'filename_from_plugin_directory' => $__FILE___from_plugin_directory,
				'path_from_plugin_directory' => $path_from_plugin_directory,
				'path_from_base_directory' => str_replace( $base_dir, '', $wp_plugin_dir ) . $path_from_plugin_directory,
				'url' => plugins_url() . str_replace( DIRECTORY_SEPARATOR, '/', $path_from_plugin_directory ),
			];
		}
		else
		{
			// Everything else except Windows.
			$this->paths = [
				'__FILE__' => $__FILE__,
				'name' => get_class( $this ),
				'filename' => basename( $__FILE__ ),
				'filename_from_plugin_directory' => str_replace( WP_PLUGIN_DIR, '', $__FILE__ ),
				'path_from_plugin_directory' => str_replace( WP_PLUGIN_DIR, '', dirname( $__FILE__ ) ),
				'path_from_base_directory' => dirname( str_replace( ABSPATH, '', $__FILE__ ) ),
				'url' => plugins_url() . str_replace( WP_PLUGIN_DIR, '', dirname( $__FILE__ ) ),
			];
		}

		register_activation_hook( $this->paths( 'filename_from_plugin_directory' ),	[ $this, 'activate_internal' ] );
		register_deactivation_hook( $this->paths( 'filename_from_plugin_directory' ), [ $this, 'deactivate_internal' ] );

		$this->_construct();
	}

	/**
		@brief		Overloadable method called after __construct.
		@details

		A convenience method that is called after the base is constructed.

		This method has the advantage of not requiring neither parameters nor parent::.

		@since		20130722
	**/
	public function _construct()
	{
	}

	/**
		@brief		Overridable activation function.
		@see		activate_internal()
		@since		20130416
	**/
	public function activate()
	{
	}

	/**
		@brief		Internal activation function.

		Child plugins should override activate().

		@since		20130416
	**/
	public function activate_internal()
	{
		$this->register_options();
		$this->activate();
	}

	/**
		@brief		Queues a submenu page for adding later.
		@details	20151226 Switched to use menu_page object.
		@see		menu_page()
		@since		20130416
	**/
	public function add_submenu_page()
	{
		//			0			1			2			3			4				5
		// ( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function = '' )
		$args = func_get_args();
		$this->menu_page()
			->submenu( $args[ 4 ] )
			->callback( $args[ 5 ] )
			->capability( $args[ 3 ] )
			->menu_title( $args[ 2 ] )
			->page_title( $args[ 1 ] );
	}

	/**
		@brief		Flush the add_submenu_page cache.
		@details	Will first sort by key and then add the subpages.
		@since		20130416
	**/
	public function add_submenu_pages()
	{
		$this->menu_page()->add_all();
	}

	/**
		@brief		Shows the uninstall form.
		@since		20130416
	**/
	public function admin_uninstall()
	{
		$r = '';
		$form = $this->form2();
		$form->prefix( get_class( $this ) );

		$form->markup( 'uninstall_info' )
			->p( 'This page will remove all the plugin tables and settings from the database and then deactivate the plugin.' );

		$form->checkbox( 'sure' )
			->label( "Yes, I'm sure I want to remove all the plugin tables and settings." )
			->required();

		$form->primary_button( 'uninstall' )
			->value_( "Uninstall plugin" );

		if ( $form->is_posting() )
		{
			$form->post();
			if ( $form->input( 'uninstall' )->pressed() )
			{
				if ( $form->input( 'sure' )->get_post_value() != 'on' )
					$this->error_( 'You have to check the checkbox in order to uninstall the plugin.' );
				else
				{
					$this->uninstall_internal();
					$this->deactivate_me();
					if( is_network_admin() )
						$url ='ms-admin.php';
					else
						$url ='index.php';
					$this->message_( 'The plugin and all associated settings and database tables have been removed. Please %sfollow this link to complete the uninstallation procedure%s.',
						sprintf( '<a href="%s" title="%s">', $url, $this->_( 'This link will take you to the index page' ) ),
						'</a>' );
					return;
				}
			}
		}

		$r .= $form->open_tag();
		$r .= $form->display_form_table();
		$r .= $form->close_tag();
		echo $r;
	}

	/**
		@brief		Overridable deactivation function.
		@see		deactivate_internal()
		@since		20130416
	**/
	public function deactivate()
	{
	}

	/**
		@brief		Internal function that runs when deactivating the plugin.
		@since		20130416
	**/
	public function deactivate_internal()
	{
		$this->deactivate();
	}

	/**
		@brief		Deactivates the plugin.
		@since		20130416
	**/
	public function deactivate_me()
	{
		deactivate_plugins( [
			$this->paths( 'filename_from_plugin_directory' )
		] );
	}

	/**
		@brief		Display a warning about the function being deprecated.
		@since		2015-12-25 13:29:48
	**/
	public function deprecated_function( $message )
	{
		if ( ! defined( 'WP_DEBUG' ) )
			return;
		if ( ! WP_DEBUG )
			return;

		$args = func_get_args();
		$s = @ call_user_func_array( 'sprintf', $args );
		if ( $s == '' )
			$s = $message;

		$backtrace = debug_backtrace();
		array_shift( $backtrace );
		$caller = reset( $backtrace );

		echo sprintf( 'Deprecated in %s, function %s, line %s: %s', $caller[ 'class' ], $caller[ 'function' ], $caller[ 'line' ], $s );
	}

	/**
		@brief		Loads this plugin's language files.

		Reads the language data from the class's name domain as default.

		@param		$domain
					Optional domain.
		@since		20130416
	**/
	public function load_language( $domain = '' )
	{
		if ( $domain != '' )
			$this->language_domain = $domain;

		if ( $this->language_domain == '' )
			$this->language_domain = str_replace( '.php', '', $this->paths( 'filename' ) );

		// This will allow other plugins to load a custom language file.
		// The filter name will be similar to: ThreeWP_Broadcast_language_directory
		$filter_name = $this->language_domain . '_language_directory';
		$language_directory = $this->paths ( 'path_from_plugin_directory' ) . '/lang';
		$language_directory = $this->filters( $filter_name, $language_directory );

		load_plugin_textdomain( $this->language_domain, false, $language_directory );
	}

	/**
		@brief		Translate a string, if possible.

		Like Wordpress' internal _() method except this one automatically uses the plugin's domain.

		Can function like sprintf, if any %s are specified.

		@param		string		$string		String to translate. %s will require extra arguments to the method.
		@since		20130416
		@return		string					Translated string, or the untranslated string.
	**/
	public function _( $string )
	{
		$translated = __( $string, $this->language_domain );

		$args = func_get_args();
		// Remove the original string from the args.
		array_shift( $args );
		// Replace the original with the translated.
		array_unshift( $args, $translated );
		// Run it through sprintf.
		$string =  @ call_user_func_array( 'sprintf', $args );
		// Did sprintf fail? Then return the translated.
		if ( $string == '' )
			$string = $translated;
		else
			$translated = $string;

		return $translated;
	}

	/**
		@brief		Internal function to handle uninstallation (database removal) of module.
		@since		20130416
	**/
	public function uninstall_internal()
	{
		$this->deregister_options();
		$this->uninstall();
	}

	/**
		@brief		Overridable uninstall method.
		@since		20130416
	**/
	public function uninstall()
	{
	}

	// -------------------------------------------------------------------------------------------------
	// ----------------------------------------- USER
	// -------------------------------------------------------------------------------------------------

	/**
		@brief		Return the user's capabilities on this blog as an array.
		@since		2015-03-17 18:56:30
	**/
	public static function get_user_capabilities()
	{
		global $wpdb;
		$key = sprintf( '%scapabilities', $wpdb->prefix );
		$r = get_user_meta( get_current_user_id(), $key, true );

		if ( ! is_array( $r ) )
			$r = [];

		if ( is_super_admin() )
			$r[ 'super_admin' ] = true;

		return $r;
	}

	/**
		@brief		Returns the user's role as a string.
		@return					User's role as a string.
		@since		20130416
	**/
	public function get_user_role()
	{
		if ( function_exists( 'is_super_admin' ) && is_super_admin() )
			return 'super_admin';

		global $current_user;
		wp_get_current_user();

		if ( ! $current_user )
			return false;

		// We want the roles
		$roles = $this->roles_as_values();

		// Get the user's most powerful role.
		$max = 0;
		foreach( $current_user->roles as $role )
			if ( isset( $roles[ $role ] ) )
				$max = max( $max, $roles[ $role ] );

		$roles = array_flip( $roles );
		return $roles[ $max ];
	}
	/**
		@brief		Return an array containing role => value.
		@since		2014-04-13 13:08:29
	**/
	public function roles_as_values()
	{
		$roles = $this->roles_as_options();
		// And we want them numbered with the weakest at the top.
		$roles = array_reverse( $roles );
		$roles = array_keys( $roles );
		// And the key should be the name of the role
		$roles = array_flip( $roles );
		return $roles;
	}

	/**
		@brief		Returns the user roles as a select options array.
		@return		The user roles as a select options array.
		@since		20130416
	**/
	public function roles_as_options()
	{
		global $wp_roles;
		$roles = $wp_roles->get_names();
		if ( function_exists( 'is_super_admin' ) )
			$roles = array_merge( [ 'super_admin' => $this->_( 'Super admin' ) ], $roles );
		return $roles;
	}

	/**
		@brief		Checks whether the user's role is at least $role.
		@param		$role		Role as string.
		@return					True if role is at least $role.
		@since		20130416
	**/
	public function role_at_least( $role )
	{
		$user_role = $this->get_user_role();
		if ( ! $user_role )
			return false;

		// No role? Then assume the user is capable of whatever that is.
		if ( $role == '' )
			return true;

		if ( function_exists( 'is_super_admin' ) && is_super_admin() )
			return true;

		if ( $role == 'super_admin' )
			return false;

		$roles = $this->roles_as_values();
		$role_value = $roles[ $role ];

		// User role is
		$user_role = $this->get_user_role();
		$user_role_value = $roles[ $user_role ];

		return $user_role_value >= $role_value;
	}

	/**
		@brief		Does the user have any of these roles?
		@since		2015-03-17 18:57:33
	**/
	public static function user_has_roles( $roles )
	{
		if ( is_super_admin() )
			return true;

		if ( ! is_array( $roles ) )
			$roles = [ $roles ];
		$user_roles = static::get_user_capabilities();
		$user_roles = array_keys ( $user_roles );
		$intersect = array_intersect( $user_roles, $roles );
		return count( $intersect ) > 0;
	}

	/**
		@brief		Return the user_id of the current user.
		@return		int						The user's ID.
		@since		20130416
	**/
	public function user_id()
	{
		return get_current_user_id();
	}

	// -------------------------------------------------------------------------------------------------
	// ----------------------------------------- MESSAGES
	// -------------------------------------------------------------------------------------------------

	/**
		@brief		Displays a message.

		Autodetects HTML / text.

		@param		$type
					Type of message: error, warning, whatever. Free content.

		@param		$string
					The message to display.
		@since		20130416
	**/
	public function display_message( $type, $string )
	{
		// If this string has html codes, then output it as it.
		$stripped = strip_tags( $string );
		if ( strlen( $stripped ) == strlen( $string ) )
		{
			$string = explode("\n", $string);
			$string = implode( '</p><p>', $string);
		}
		echo '<div class="message_box '.$type.'">
			<p class="message_timestamp">'.$this->now().'</p>
			<p>'.$string.'</p></div>';
	}

	/**
		@brief		Return a message box of type 'info'.
		@since		2015-12-21 20:26:14
	**/
	public function error_message_box()
	{
		return new message_boxes\Error( $this );
	}

	/**
		@brief		Return a message box of type 'info'.
		@since		2015-12-21 20:26:14
	**/
	public function info_message_box()
	{
		return new message_boxes\Info( $this );
	}

	/**
		@brief		Displays an informational message.
		@param		string		$string		String to create into a message.
		@since		20130416
	**/
	public function message( $string )
	{
		$this->display_message( 'updated pv_message', $string );
	}

	/**
		@brief		Convenience function to translate and then create a message from a string and optional sprintf arguments.
		@param		string		$string		String to translate and create into a message.
		@param		string		$args		One or more arguments.
		@return		A translated message.
		@since		20130416
	**/
	public function message_( $string, $args = '' )
	{
		$args = func_get_args();
		$string = call_user_func_array( [ &$this, '_' ], $args );
		return $this->message( $string );
	}

	/**
		@brief		Displays an error message.
		@details	The only thing that makes it an error message is that the div has the class "error".
		@param		string		$string		String to create into a message.
		@since		20130416
	**/
	public function error( $string )
	{
		$this->display_message( 'error', $string );
	}

	/**
		@brief		Convenience function to translate and then create an error message from a string and optional sprintf arguments.
		@param		string		$string		String to translate and create into a message.
		@param		string		$args		One or more arguments.
		@return		A translated error message.
		@since		20130416
	**/
	public function error_( $string, $args = null )
	{
		$args = func_get_args();
		$string = call_user_func_array( [ &$this, '_' ], $args );
		return $this->error( $string );
	}

	// -------------------------------------------------------------------------------------------------
	// ----------------------------------------- OPTIONS
	// -------------------------------------------------------------------------------------------------

	/**
		Deletes a site option.

		If this is a network, the site option is preferred.

		@param		$option		Name of option to delete.
		@since		20130416
	**/
	public function delete_option( $option )
	{
		if ( $this->is_network )
			$this->delete_site_option( $option );
		else
			$this->delete_local_option( $option );
	}

	/**
		Deletes a local option.

		@param		$option		Name of option to delete.
		@since		20130416
	**/
	public function delete_local_option( $option )
	{
		$option = $this->fix_local_option_name( $option );
		delete_option( $option );
	}

	/**
		Deletes a site option.

		@param		$option		Name of option to delete.
		@since		20130416
	**/
	public function delete_site_option( $option )
	{
		$option = $this->fix_site_option_name( $option );
		delete_site_option( $option );
	}

	/**
		@brief		Removes all the options this plugin uses.
		@since		20130416
	**/
	public function deregister_options()
	{
		if ( isset( $this->options ) )
			foreach( $this->options as $option=>$value )
				$this->delete_option( $option );

		foreach( $this->local_options() as $option=>$value )
		{
			$option = $this->fix_local_option_name( $option );
			delete_option( $option );
		}

		if ( $this->is_network )
			foreach( $this->site_options() as $option=>$value )
			{
				$option = $this->fix_site_option_name( $option );
				delete_site_option( $option );
			}
		else
		{
			foreach( $this->site_options() as $option=>$value )
			{
				$option = $this->fix_local_option_name( $option );
				delete_option( $option, $value );
			}
		}
	}

	/**
		@brief		Gets the proper option name for a local option.
		@details	Does a 64 char length check and outputs an error in WP_DEBUG mode.
		@since		20131211
	**/
	public function fix_local_option_name( $option )
	{
		$max = 64;
		$name = $this->get_local_option_prefix() . '_' . $option;
		if ( defined( 'WP_DEBUG' ) && strlen( $name ) > $max )
		{
			$text = sprintf( '%s<code>%s</code>',
				substr( $name, 0, $max ),
				substr( $name, $max )
			);
			echo "Option $name is longer than $max characters.\n<br />";
		}
		return $name;
	}

	/**
		Normalizes the name of an option.

		Will prepend the class name in front, to make the options easily findable in the table.

		@param		$option		Option name to fix.
		@since		20130416
	**/
	public function fix_option_name( $option )
	{
		if ( $this->is_network )
			$name = $this->get_site_option_prefix() . '_' . $option;
		else
			$name = $this->get_local_option_prefix() . '_' . $option;
		return $name;
	}

	/**
		@brief		Gets the proper option name for a site option.
		@details	Does a 255 char length check and outputs an error in WP_DEBUG mode.
		@since		20131211
	**/
	public function fix_site_option_name( $option )
	{
		if ( ! $this->is_network )
			return $this->fix_local_option_name( $option );
		else
			$name = $this->get_site_option_prefix() . '_' . $option;
		$max = 255;
		if ( defined( 'WP_DEBUG' ) && strlen( $name ) > $max )
		{
			$text = sprintf( '%s<code>%s</code>',
				substr( $text, 0, $max ),
				substr( $text, $max )
			);
			echo "Option $text is longer than $max characters.\n<br />";
		}
		return $name;
	}

	/**
		@brief		Returns the prefix for local options.
		@since		20131211
	**/
	public function get_local_option_prefix()
	{
		return preg_replace( '/.*\\\\/', '', $this->paths( 'name' ) );
	}

	/**
		@brief		Returns the options prefix.
		@details

		Override this is you find that your options are a bit too long.
		@since		20130416
	**/
	public function get_option_prefix()
	{
		return $this->paths( 'name' );
	}

	/**
		@brief		Returns the prefix for site options.
		@since		20131211
	**/
	public function get_site_option_prefix()
	{
		return $this->get_option_prefix();
	}

	/**
		Get a site option.

		If this is a network, the site option is preferred.

		@param		$option		Name of option to get.
		@return					Value.
		@since		20130416
	**/
	public function get_option( $option, $default = 'no_default_value' )
	{
		if ( $this->is_network )
			return $this->get_site_option( $option, $default );
		else
			return $this->get_local_option( $option, $default );
	}

	/**
		Gets the value of a local option.

		@param		$option			Name of option to get.
		@param		$default		The default value if the option === false
		@return						Value.
		@since		20130416
	**/
	public function get_local_option( $option, $default = 'no_default_value' )
	{
		$fixed_option = $this->fix_local_option_name( $option );
		$value = get_option( $fixed_option, 'no_default_value' );
		if ( $value === 'no_default_value' )
		{
			$options = $this->local_options();
			if ( isset( $options[ $option ] ) )
				$default = $options[ $option ];
			else
				$default = false;
			return $default;
		}
		else
			return $value;
	}

	/**
		Gets the value of a site option.

		@param		$option		Name of option to get.
		@param		$default	The default value if the option === false
		@return					Value.
		@since		20130416
	**/
	public function get_site_option( $option, $default = 'no_default_value' )
	{
		$fixed_option = $this->fix_site_option_name( $option );
		$value = get_site_option( $fixed_option, 'no_default_value' );
		// No value returned?
		if ( $value === 'no_default_value' )
		{
			// Return the default from the options array.
			$options = $this->site_options();
			if ( isset( $options[ $option ] ) )
				$default = $options[ $option ];
			else
				$default = false;
			return $default;
		}
		else
			return $value;
	}

	/**
		@brief		Return an array of the local options.
		@since		2014-05-10 08:46:20
	**/
	public function local_options()
	{
		return [];
	}

	/**
		Registers all the options this plugin uses.
		@since		20130416
	**/
	public function register_options()
	{
		foreach( $this->local_options() as $option=>$value )
		{
			$option = $this->fix_local_option_name( $option );
			if ( get_option( $option ) === false )
				update_option( $option, $value );
		}

		if ( $this->is_network )
		{
			foreach( $this->site_options() as $option=>$value )
			{
				$option = $this->fix_site_option_name( $option );
				if ( get_site_option( $option ) === false )
					update_site_option( $option, $value );
			}
		}
		else
		{
			foreach( $this->site_options() as $option=>$value )
			{
				$option = $this->fix_local_option_name( $option );
				if ( get_option( $option ) === false)
					update_option( $option, $value );
			}
		}
	}

	/**
		@brief		Return an array of the site options.
		@since		2014-05-10 08:46:20
	**/
	public function site_options()
	{
		return [];
	}

	/**
		Updates a site option.

		If this is a network, the site option is preferred.

		@param		$option		Name of option to update.
		@param		$value		New value
		@since		20130416
	**/
	public function update_option( $option, $value )
	{
		if ( $this->is_network )
			$this->update_site_option( $option, $value );
		else
			$this->update_local_option( $option, $value );
	}

	/**
		Updates a local option.

		@param		option		Name of option to update.
		@param		$value		New value
		@since		20130416
	**/
	public function update_local_option( $option, $value )
	{
		$option = $this->fix_local_option_name( $option );
		update_option( $option, $value );
	}

	/**
		Updates a site option.

		@param		$option		Name of option to update.
		@param		$value		New value
		@since		20130416
	**/
	public function update_site_option( $option, $value )
	{
		$option = $this->fix_site_option_name( $option );
		update_site_option( $option, $value );
	}

	// -------------------------------------------------------------------------------------------------
	// ----------------------------------------- SQL
	// -------------------------------------------------------------------------------------------------

	/**
		Sends a query to wpdb and return the results.

		@param		$query		The SQL query.
		@param		$wpdb		An optional, other WPDB if the standard $wpdb isn't good enough for you.
		@return		array		The rows from the query.
		@since		20130416
	**/
	public function query( $query , $wpdb = null )
	{
		if ( $wpdb === null )
			$wpdb = $this->wpdb;
		$results = $wpdb->get_results( $query, 'ARRAY_A' );
		return (is_array( $results) ? $results : array());
	}

	/**
		Fire an SQL query and return the results only if there is one row result.

		@param		$query			The SQL query.
		@return						Either the row as an array, or false if more than one row.
		@since		20130416
	**/
	public function query_single( $query)
	{
		$results = $this->wpdb->get_results( $query, 'ARRAY_A' );
		if ( count( $results) != 1)
			return false;
		return $results[0];
	}

	/**
		Fire an SQL query and return the row ID of the inserted row.

		@param		$query		The SQL query.
		@return					The inserted ID.
		@since		20130416
	**/
	public function query_insert_id( $query)
	{
		$this->wpdb->query( $query);
		return $this->wpdb->insert_id;
	}

	/**
		Converts an object to a base64 encoded, serialized string, ready to be inserted into sql.

		@param		$object		An object.
		@return					Serialized, base64-encoded string.
		@since		20130416
	**/
	public function sql_encode( $object )
	{
		return base64_encode( serialize( $object) );
	}

	/**
		Converts a base64 encoded, serialized string back into an object.
		@param		$string			Serialized, base64-encoded string.
		@return						Object, if possible.
		@since		20130416
	**/
	public function sql_decode( $string )
	{
		return unserialize( base64_decode( $string) );
	}

	/**
		Returns whether a table exists.

		@param		$table_name		Table name to check for.
		@return						True if the table exists.
		@since		20130416
	**/
	public function sql_table_exists( $table_name )
	{
		$query = "SHOW TABLES LIKE '$table_name'";
		$result = $this->query( $query );
		return count( $result) > 0;
	}

	// -------------------------------------------------------------------------------------------------
	// ----------------------------------------- TOOLS
	// -------------------------------------------------------------------------------------------------

	/**
		@brief		Display the time ago as human-readable string.
		@param		$time_string	"2010-04-12 15:19"
		@param		$time			An optional timestamp to base time difference on, if not now.
		@return						"28 minutes ago"
		@since		20130416
	**/
	public static function ago( $time_string, $time = null)
	{
		if ( $time_string == '' )
			return '';
		if ( $time === null )
			$time = current_time( 'timestamp' );
		$diff = human_time_diff( strtotime( $time_string), $time );
		return '<span title="'.$time_string.'">' . sprintf( __( '%s ago' ), $diff) . '</span>';
	}

	/**
		@brief		Return an xgettext command line that will generate all strings necessary for translation.
		@details	Will collect keywords from the SDK and the subclass.
		@return		string		xgettext command line suggestion.
		@see		pot_files()
		@see		pot_keyswords()
		@since		20130505
	**/
	public function cli_pot()
	{
		$basedir = dirname( $this->paths(  '__FILE__' ) ) . '/';
		$files = array_merge( [
			basename( $this->paths( '__FILE__' ) ),									// subclass.php
			str_replace( $basedir, '', dirname( dirname( __FILE__ ) ) . '/*php' ),	// plainview/*php
			str_replace( $basedir, '', dirname( dirname( __FILE__ ) ) . '/form2/inputs/*php' ),
			str_replace( $basedir, '', dirname( dirname( __FILE__ ) ) . '/form2/inputs/traits/*php' ),
			str_replace( $basedir, '', dirname( __FILE__ ) . '/*php' ),				// plainview_sdk/wordpress/*.php
		], $this->pot_files() );

		$filename = preg_replace( '/\.php/', '.pot', $this->paths( '__FILE__' ) );

		$keywords = array_merge( [
			'_',
			'error_',
			'description_',	// form2
			'message_',
			'heading_',		// tabs
			'label_',		// form2
			'name_',		// tabs
			'option_',		// form2
			'p_',
			'text_',		// table
			'value_',		// form2
		], $this->pot_keywords() );

		$pot = dirname( $filename ) . '/lang/' . basename( $filename );

		$command = sprintf( 'xgettext -s -c --no-wrap -d %s -p lang -o "%s" --omit-header%s %s',
			get_class( $this ),
			$pot,
			$this->implode_html( $keywords, ' -k', '' ),
			implode( ' ', $files )
		);
		echo $command;
		echo "\n";
	}

	/**
		@brief		Handles command line arguments.
		@details	Using an array of long options, will call the respective method to handle the option.

		For example: `php Inherited_Class.php --pot` will call do_pot().
		@see		long_options
		@since		20130505
	**/
	public function do_cli()
	{
		$long_options = array_merge( [ 'pot' ], $this->long_options() );
		$options = (object) getopt( '', $long_options );

		foreach( $options as $option => $value )
		{
			$f = 'cli_' . $option;
			$this->$f( $options );
		}

		die();
	}

	/**
		@brief		Creates a form2 object.
		@see		form2
		@since		20130416
	**/
	public function form()
	{
		$form = new \plainview\sdk_mcc\wordpress\form2\form( $this );
		return $form;
	}

	/**
		@brief		Backwards compatibility alias for form.
		@return		\\plainview\\sdk_mcc\\form2\\form		A new form object.
		@since		20130509
	**/
	public function form2()
	{
		return $this->form();
	}

	/**
		@brief		Return the blog's time offset from GMT.
		@since		2014-07-08 10:07:30
	**/
	public function gmt_offset()
	{
		$blog_timestamp = current_time( 'timestamp' );
		return $blog_timestamp - time();
	}

	/**
		@brief		An array of command line options that this subclass can handle via do_LONGOPTION().
		@return		array		Array of long options that this subclass handles.
		@see		do_cli()
		@since		20130505
	**/
	public function long_options()
	{
		return [];
	}

	/**
		@brief		Load the PHPmailer object, if necessary.
		@since		2016-02-01 20:11:34
	**/
	public static function mail()
	{
		// This ensures that the PHPmailer class is loaded and ready.
		if ( ! class_exists( '\\PHPMailer' ) )
			require_once( ABSPATH . WPINC . '/class-phpmailer.php' );
		return parent::mail();
	}

	/**
		@brief		Return the menu_page object.
		@since		2015-12-26 19:04:58
	**/
	public function menu_page()
	{
		if ( ! isset( $this->__menu_page ) )
		{
			$this->__menu_page = new menu_page\Menu();
			$this->__menu_page->set_parent( $this );
		}
		return $this->__menu_page;
	}

	/**
		@brief		Create a nav tabs instance.
		@since		2015-12-28 00:05:11
	**/
	public function nav_tabs()
	{
		$tabs = new \plainview\sdk_mcc\wordpress\tabs\Nav_Tabs( $this );
		return $tabs;
	}

	/**
		@brief		Returns WP's current timestamp (corrected for UTC)
		@return		string		Current timestamp in MYSQL datetime format.
		@since		20130416
	**/
	public static function now()
	{
		return date( 'Y-m-d H:i:s', current_time( 'timestamp' ) );
	}

	/**
		@brief		wpautop's a string, using sprintf to replace arguments.
		@param		string		$string		String to wpautop.
		@param		mixed		$args		Optional arguments to sprintf.
		@return		The wpautop'd sprintf string.
		@since		20130416
	**/
	public function p( $string, $args = '' )
	{
		$args = func_get_args();
		$s2 = @ call_user_func_array( 'sprintf', $args );
		if ( $s2 == '' )
			$s2 = $string;
		return wpautop( $s2 );
	}

	/**
		@brief		Translate and wpautop a string, using sprintf to replace arguments.
		@deprecated	Since 20180207
		@param		string		$string		String to translate and then wpautop.
		@param		mixed		$args		Optional arguments to sprintf.
		@return		The translated, wpautop'd string.
		@since		20130416
	**/
	public function p_( $string, $args = '' )
	{
		$args = func_get_args();
		return wpautop( call_user_func_array( array( &$this, '_' ), $args ) );
	}

	/**
		@brief		Return the paths, or a path, as an object.
		@since		2014-09-28 00:16:17
	**/
	public function paths( $key = null )
	{
		$paths = (object)$this->paths;
		if ( $key === null )
			return $paths;
		return $paths->$key;
	}

	/**
		@brief		Return a list of files that are to be included when creating the .pot file.
		@return		array		List of files (including wildcards) that must be including when preparing the .pot file.
		@since		20130505
	**/
	public function pot_files()
	{
		return [];
	}

	/**
		@brief		Return a list of translation keywords used when creating the .pot file.
		@return		array		Array of translation keywords to use when creating the .pot file.
		@since		20130505
	**/
	public function pot_keywords()
	{
		return [];
	}

	/**
		@brief		Create a row actions object.
		@since		2015-12-21 23:24:18
	**/
	public function row_actions()
	{
		return new row_actions\Row( $this );
	}

	/**
		@brief		Sanitizes (slugs) a string.
		@param		string		$string		String to sanitize.
		@return		string					Sanitized string.
		@since		20130416
	**/
	public static function slug( $string )
	{
		return sanitize_title( $string );
	}

	/**
		@brief		Create a subsubsub tabs instance.
		@since		2015-12-28 00:05:11
	**/
	public function subsubsub_tabs()
	{
		$tabs = new \plainview\sdk_mcc\wordpress\tabs\Subsubsub_Tabs( $this );
		return $tabs;
	}

	/**
		@brief		Sanitizes the name of a tab.

		@param		string		$string		String to sanitize.
		@return		string					Sanitized string.
		@since		20130416
	**/
	public static function tab_slug( $string )
	{
		return self::slug( $string );
	}

	/**
		@brief		Creates a new table.
		@return		object		A new \\plainview\\sdk_mcc\\wordpress\\table object.
		@since		20130416
	**/
	public function table()
	{
		$table = new \plainview\sdk_mcc\wordpress\table\table( $this );
		$table->css_class( 'widefat' );
		return $table;
	}

	/**
		@brief		Create a tabs instance.
		@since		20130416
	**/
	public function tabs()
	{
		return $this->nav_tabs();
	}

	/**
		@brief		Returns the current time(), corrected for UTC and DST.
		@return		int		Current, corrected timestamp.
		@since		20130416
	**/
	public static function time()
	{
		return current_time( 'timestamp' );
	}

	/**
		@brief		Displays a time difference as a human-readable string.
		@param		$current		"2010-04-12 15:19" or a UNIX timestamp.
		@param		$reference		An optional timestamp to base time difference on, if not now.
		@param		$wrap			Wrap the real time in a span with a title?
		@return						"28 minutes"
		@since		20130810
	**/
	public static function time_to_string( $current, $reference = null, $wrap = false )
	{
		if ( $current == '' )
			return '';
		if ( ! is_int( $current ) )
			$current = strtotime( $current );
		if ( $reference === null )
			$reference = current_time( 'timestamp' );
		$diff = human_time_diff( $current, $reference );
		if ( $wrap )
			$diff = '<span title="'.$current.'">' . $diff . '</span>';
		return $diff;
	}

	/**
		@brief		Dies after sprinting the arguments.
		@since		2014-04-18 09:16:12
	**/
	public function wp_die( $message )
	{
		$args = func_get_args();
		$text =  call_user_func_array( 'sprintf', $args );
		if ( $text == '' )
			$text = $message;
		wp_die( $text );
	}

	/**
		@brief		Outputs the text in Wordpress admin's panel format.
		@details	To remember the correct parameter order: wrap THIS in THIS.
		@param		string		$title		H2 title to display.
		@param		string		$text		Text to display.
		@return		HTML wrapped HTML.
		@since		20130416
	**/
	public static function wrap( $text, $title )
	{
		echo "<h2>$title</h2>
			<div class=\"wrap\">
				$text
			</div>
		";
	}
}
