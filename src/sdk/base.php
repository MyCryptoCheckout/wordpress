<?php

namespace plainview\sdk_mcc;

/**
	@brief			Collection of useful functions.

	@par			Versioning

	This base class contains the version of the SDK. Upon changing any part of the SDK, bump the version in here.

	@author			Edward Plainview		edward@plainview.se
	@copyright		GPL v3
**/
class base
{
	/**
		@brief		The instance of the base.
		@since		20130425
		@var		$instance
	**/
	protected static $instance = [];

	/**
		@brief		The version of this SDK file.
		@since		20130630
		@var		$sdk_version
	**/
	protected $sdk_version = 20180218;

	/**
		@brief		Constructor.
		@since		20130425
	**/
	public function __construct()
	{
		$classname = get_class( $this );
		self::$instance[ $classname ] = $this;
	}

	/**
		@brief		Insert an array into another.
		@details	Like array_splice but better, because it even inserts the new key.
		@param		array		$array		Array into which to insert the new array.
		@param		int			$position	Position into which to insert the new array.
		@param		array		$new_array	The new array which is to be inserted.
		@return		array					The complete array.
		@since		20130416
	**/
	public static function array_insert( $array, $position, $new_array )
	{
		$part1 = array_slice( $array, 0, $position, true );
		$part2 = array_slice( $array, $position, null, true );
		return $part1 + $new_array + $part2;
	}

	/**
		@brief		Sort an array of arrays using a specific key in the subarray as the sort key.
		@param		array		$array		An array of arrays.
		@param		string		$key		Key in subarray to use as sort key.
		@return		array					The array of arrays.
		@since		20130416
	**/
	public static function array_sort_subarrays( $array, $key )
	{
		// In order to be able to sort a bunch of objects, we have to extract the key and use it as a key in another array.
		// But we can't just use the key, since there could be duplicates, therefore we attach a random value.
		$sorted = array();

		$is_array = is_array( reset( $array ) );

		foreach( $array as $index => $item )
		{
			$item = (object) $item;
			do
			{
				$rand = rand(0, PHP_INT_MAX / 2);
				if ( is_int( $item->$key ) )
					$random_key = $rand + $item->$key;
				else
					$random_key = $item->$key . '-' . $rand;
			}
			while ( isset( $sorted[ $random_key ] ) );

			$sorted[ $random_key ] = array( 'key' => $index, 'value' => $item );
		}
		ksort( $sorted );

		// The array has been sorted, we want the original array again.
		$r = array();
		foreach( $sorted as $item )
		{
			$value = ( $is_array ? (array)$item[ 'value' ] : $item[ 'value' ] );
			$r[ $item['key'] ] = $item['value'];
		}

		return $r;
	}

	/**
		@brief		Rekey an array with the specified key/property of the array object.
		@param		$array		Array to rekey.
		@param		$key		Object key or array property to use as the new key.
		@return		array		Rearranged array.
		@since		20130416
	**/
	public static function array_rekey( $array, $key )
	{
		$r = [];
		foreach( $array as $value )
		{
			$object = (object)$value;
			$r[ $object->$key ] = $value;
		}
		return $r;
	}

	/**
		@brief		Build the complete current URL.
		@param		array		$SERVER		Optional _SERVER array to use, instead of the normal _SERVER array.
		@return		string		The complete URL, with http / https, port, etc.
		@since		20130604
	**/
	public static function current_url( $SERVER = null )
	{
		if ( $SERVER === null )
			$SERVER = $_SERVER;

		// Unable to current_url if we're running as a CLI.
		if ( ! isset( $SERVER[ 'SERVER_PORT' ] ) )
			return '';

		$ssl = false;
		if ( isset( $SERVER[ 'HTTPS' ] ) )
		{
			$value = self::strtolower( $SERVER[ 'HTTPS' ] );
			$ssl = (
				( $value != '' )
				&&
				( $value != 'off' )
			);
		} elseif( isset( $SERVER[ 'HTTP_X_FORWARDED_PROTO' ])
            && $SERVER[ 'HTTP_X_FORWARDED_PROTO' ] =='https' ) {
            $ssl = true;
        }

		$port = $SERVER[ 'SERVER_PORT' ];
		if ( $ssl && $port == 443 )
			$port = '';
		if ( ! $ssl && $port == 80 )
			$port = '';
		if ( $ssl && isset($SERVER[ 'HTTP_X_FORWARDED_PORT' ])
                && $SERVER[ 'HTTP_X_FORWARDED_PORT' ] == '443' )
            $port = '';
		if ( $port != '' )
			$port = ':' . $port;

		$url = $SERVER[ 'REQUEST_URI' ];

		return sprintf( '%s://%s%s%s',
			$ssl ? 'https' : 'http',
			$SERVER[ 'HTTP_HOST' ],
			$port,
			$url
		);
	}

	/**
		@brief		Creates a form2 object.
		@return		\\plainview\\sdk_mcc\\form2\\form		A new form object.
		@since		2015-12-25 17:21:03
	**/
	public function form()
	{
		if ( ! class_exists( '\\plainview\\sdk_mcc\\form2\\form' ) )
			require_once( dirname( __FILE__ ) . '/form2/form.php' );

		if ( ! class_exists( '\\plainview\\sdk_mcc\\wordpress\\form2\\form' ) )
			require_once( 'form2.php' );

		$form = new \plainview\sdk_mcc\wordpress\form2\form();
		return $form;
	}

	/**
		@brief		Backwards compatibility alias for form.
		@see		form()
		@since		20130509
	**/
	public function form2()
	{
		return $this->form();
	}

	/**
		@brief		Convenience function to wrap a string in h1 tags.
		@param		string		$string		The string to wrap.
		@return		string					The wrapped string.
		@see		open_close_tag()
		@since		20130416
	**/
	public static function h1( $string )
	{
		return self::open_close_tag( $string, 'h1' );
	}

	/**
		@brief		Convenience function to wrap a string in h2 tags.
		@param		string		$string		The string to wrap.
		@return		string					The wrapped string.
		@see		open_close_tag()
		@since		20130416
	**/
	public static function h2( $string )
	{
		return self::open_close_tag( $string, 'h2' );
	}

	/**
		@brief		Convenience function to wrap a string in h3 tags.
		@param		string		$string		The string to wrap.
		@return		string					The wrapped string.
		@see		open_close_tag()
		@since		20130416
	**/
	public static function h3( $string )
	{
		return self::open_close_tag( $string, 'h3' );
	}

	/**
		@brief		Implode an array in an HTML-friendly way.
		@details	Used to implode arrays using HTML tags before, between and after the array. Good for lists.
		@param		string		$prefix		li
		@param		string		$suffix		/li
		@param		array		$array		The array of strings to implode.
		@return		string					The imploded string.
		@since		20130416
	**/
	public static function implode_html( $array, $prefix = '<li>', $suffix = '</li>' )
	{
		return $prefix . implode( $suffix . $prefix, $array ) . $suffix;
	}

	/**
		@brief		Return the instance of this object class.
		@return		base		The instance of this object class.
		@since		20130425
	**/
	public static function instance()
	{
		$classname = get_called_class();
		if ( ! isset( self::$instance[ $classname ] ) )
			return false;
		return self::$instance[ $classname ];
	}

	/**
		@brief		Check en e-mail address for validity.
		@param		string		$address		Address to check.
		@param		boolean		$check_mx		Check for a valid MX?
		@return		boolean		True, if the e-mail address is valid.
		@since		20130416
	**/
	public static function is_email( $address, $check_mx = true )
	{
		if ( $address == '' )
			return false;

		if ( filter_var( $address, FILTER_VALIDATE_EMAIL ) != $address )
			return false;

		// If no need to check the MX, and we've gotten this far, then it's ok.
		if ( $check_mx == false )
			return true;

		// Check the DNS record.
		$host = preg_replace( '/.*@/', '', $address );
		if ( ! checkdnsrr( $host, 'MX' ) )
			return false;

		return true;
	}

	/**
		@brief		Creates a mail object.
		@return		\\plainview\\sdk_mcc\\mail\\mail		A new PHPmailer object.
		@since		20130430
	**/
	public static function mail()
	{
		$mail = new \plainview\sdk_mcc\mail\mail();
		$mail->CharSet = 'UTF-8';
		return $mail;
	}

	/**
		@brief		Merge two objects.
		@details	The objects can even be arrays, since they're automatically converted into objects.
		@param		mixed		$base		An array or object into which to append the new properties.
		@param		mixed		$new		New properties to append to $base.
		@return		object					The expanded $base object.
		@since		20130416
	**/
	public static function merge_objects( $base, $new )
	{
		$base = clone (object)$base;
		foreach( (array)$new as $key => $value )
			$base->$key = $value;
		return $base;
	}

	/**
		@brief		Returns the number corrected into the min and max values.
		@param		int		$number		Number to adjust.
		@param		int		$min		Minimum value.
		@param		int		$max		Maximum value.
		@return		int					The corrected $number.
		@since		20130416
	**/
	public static function minmax( $number, $min, $max )
	{
		$number = min( $max, $number );
		$number = max( $min, $number );
		return $number;
	}

	/**
		@brief		Enclose in string in an HTML element tag.
		@details	Parameter order: enclose THIS in THIS
		@param		string		$string		String to wrap.
		@param		string		$tag		HTML element tag: h1, h2, h3, p, etc...
		@return		string					The wrapped string.
		@since		20130416
	**/
	public static function open_close_tag( $string, $tag )
	{
		return sprintf( '<%s>%s</%s>', $tag, $string, $tag );
	}

	/**
		@brief		Recursively removes a directory.
		@details	Assumes that all files in the directory, and the dir itself, are writeable.
		@param		string		$directory		Directory to remove.
		@since		20130416
	**/
	public static function rmdir( $directory )
	{
		$directory = rtrim( $directory, '/' );
		if ( $directory == '.' || $directory == '..' )
			return;
		if ( is_file( $directory ) )
			unlink ( $directory );
		else
		{
			$files = glob( $directory . '/*' );
			foreach( $files as $file )
				self::rmdir( $file );
			rmdir( $directory );
		}
	}

	/**
		@brief		Converts a string to an array of e-mail addresses.
		@param		string		$string		A multiline text-area.
		@param		bool		$mx			Check each e-mail address for valid MX?
		@return		array					An array of valid e-mail addresses. If no valid e-mail addresses are found, then the returned array is empty.
		@since		20130425
	**/
	public static function string_to_emails( $string, $mx = true )
	{
		$string = str_replace( array( "\r", "\n", "\t", ';', ',', ' ' ), "\n", $string );
		$lines = array_filter( explode( "\n", $string ) );
		$r = array();
		foreach( $lines as $line )
			if ( self::is_email( $line, $mx ) )
				$r[ $line ] = $line;
		ksort( $r );
		return $r;
	}

	/**
		@brief		Multibyte strtolower.
		@param		string		$string			String to lowercase.
		@return									Lowercased string.
		@since		20130416
	**/
	public static function strtolower( $string )
	{
		if ( function_exists( 'mb_strtolower' ) )
			return mb_strtolower( $string );
		else
		return strtolower( $string );
	}

	/**
		@brief		Multibyte strtoupper.
		@param		string		$string			String to uppercase.
		@return									Uppercased string.
		@since		20130416
	**/
	public static function strtoupper( $string )
	{
		if ( function_exists( 'mb_strtoupper' ) )
			return mb_strtoupper( $string );
		else
		return strtoupper( $string );
	}

	/**
		@brief		Create a new temporary directory in the system's temp dir, and return the name.
		@param		string		$subdirectory		Prefix of subdir.
		@return		string							Complete path to new directory.
		@since		20130515
	**/
	public static function temp_directory( $prefix = null )
	{
		if ( $prefix === null )
			$prefix = self::uuid( 8 );
		$prefix .= self::uuid( 8 );
		$r = sys_get_temp_dir() . '/' . $prefix;
		mkdir( $r );
		if ( ! is_readable( $r ) )
			return false;
		return $r;
	}

	/**
		@brief		Return the name to a temporary file name, optionally in a specific temp directory.
		@param		string		$prefix		Prefix of temporary file.
		@param		string		$temp_dir	Optional temporary directory in which to create the temp file.
		@return		string					Complete filename to a temporary file.
		@since		20130515
	**/
	public static function temp_file( $prefix = null, $temp_dir = null )
	{
		if ( $prefix === null )
			$prefix = 'plainview_sdk_base_';
		if ( $temp_dir !== null )
			$temp_dir = self::temp_directory( $temp_dir );
		else
			$temp_dir = sys_get_temp_dir();

		return tempnam( $temp_dir, $prefix );
	}

	/**
		@brief		Explode a text area to an array, cleaning the
		@details	Used to clean and filter textareas.
		@since		2015-04-15 22:29:14
	**/
	public static function textarea_to_array( $string )
	{
		$s = str_replace( "\r", '', $string );
		$lines = explode( "\n", $s );
		$lines = array_filter( $lines );
		return $lines;
	}

	/**
		@brief		Produce a random uuid.
		@param		int			$length		Length of ID to return.
		@return		string					An x-character long random ID.
		@since		20130506
	**/
	public static function uuid( $length = 64 )
	{
		$r = 'u';
		while( strlen( $r ) < $length )
			$r .= hash( 'sha512', microtime() . rand( 0, PHP_INT_MAX ) );
		return substr( $r, 0, $length );
	}

	/**
		@brief
		@since		2017-02-21 07:43:53
	**/
	public static function wpautop( $string )
	{
		if ( ! function_exists( 'wpautop' ) )
			require_once( 'wpautop.php' );

		return wpautop( $string );
	}
}
