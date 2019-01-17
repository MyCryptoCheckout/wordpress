<?php

namespace plainview\sdk_mcc\form2\inputs;

use \plainview\sdk_mcc\form2\inputs\data\file as data;

/**
	@brief		File input.
	@author		Edward Plainview <edward@plainview.se>
	@copyright	GPL v3
	@version	20131009
**/
class file
	extends input
{
	public $type = 'file';

	/**
		@brief		Set the accept attribute.
		@since		2016-06-21 16:20:46
	**/
	public function accept( $accept = '' )
	{
		if ( $accept == '' )
			$this->clear_attribute( 'accept' );
		else
			$this->set_attribute( 'accept', $accept );
		return $this;
	}

	/**
		@brief		File has no value.
		@since		20131009
	**/
	public function display_value()
	{
		return '';
	}

	/**
		@brief		Retrieves the file data.
		@since		20131009
	**/
	public function get_post_value()
	{
		$files = $_FILES;
		$name = $this->make_name();

		// No prefix?
		if ( strpos( $name, '['  ) === false )
		{
			if ( ! isset( $files[ $name ] ) )
				return null;
			else
				return new \plainview\sdk_mcc\form2\inputs\data\file( $_FILES[ $name ] );
		}
		else
		{
			// Prepare to split the name up into arrays.
			$name = preg_replace( '/\[/', '][', $name, 1 );
			$name = rtrim( $name, ']' );
			$names = explode( '][', $name );

			// Delve into the _FILES array.
			do
			{
				$name = array_shift( $names );

				if ( ! isset( $files[ $name ] ) )
					return null;

				$files = $files[ $name ];

				if ( data::array_keys_exist( $files ) )
				{
					$name = array_shift( $names );
					$file = array_shift( $names );
					$data = new data( data::extract_file_from_files( $files, $name, $this->get_attribute( 'name' ) ) );
					return $data;
				}
			} while ( count( $names ) > 1 );
			return null;
		}
	}

	/**
		@brief		Has no value.
		@since		2014-09-10 17:17:24
	**/
	public function get_value()
	{
		return '';
	}

	/**
		@brief		Do nothing.
		@since		20131009
	**/
	public function use_post_value()
	{
		return;
	}

}
