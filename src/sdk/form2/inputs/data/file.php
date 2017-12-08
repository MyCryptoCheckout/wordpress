<?php

namespace plainview\sdk_mcc\form2\inputs\data;

/**
	@brief		File data object for easier file data handling.
	@details	Created by file inputs and returned by get_post_value().
	@author		Edward Plainview <edward@plainview.se>
	@copyright	GPL v3
	@version	20131009
**/
class file
{
	public static $array_keys = [ 'name', 'type', 'tmp_name', 'error', 'size' ];

	public $error;
	public $name;
	public $size;
	public $tmp_name;
	public $type;

	public function __construct( $array = [] )
	{
		foreach( self::$array_keys as $key )
			if ( isset( $array[ $key ] ) )
				$this->$key = $array[ $key ];
	}

	/**
		@brief		Does this array contain all relevant info about a file?
		@see		$array_keys
		@since		20131009
	**/
	public static function array_keys_exist( $array )
	{
		foreach( self::$array_keys as $key )
			if ( ! isset( $array[ $key ] ) )
				return false;
		return true;
	}

	/**
		@brief		Extract the file data from a prefixed _FILES array.
		@param		array		$files		The _FILES array
		@param		string		$prefix		The prefix just before the input name.
		@param		string		$name		The input name.
		@since		20131009
	**/
	public static function extract_file_from_files( $files, $prefix, $name )
	{
		$r = [];
		foreach( self::$array_keys as $key )
			$r[ $key ] = $files[ $key ][ $prefix ][ $name ];
		return $r;
	}
}
