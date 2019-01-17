<?php

namespace plainview\sdk_mcc\wordpress\traits;

/**
	@brief			Wordpress-specific db_aware_object.
	@details		Uses \plainview\sdk_mcc\db_aware_object.
	@version		20130430
**/
trait db_aware_object
{
	use \plainview\sdk_mcc\traits\db_aware_object;

	public function __db_delete()
	{
		$id_key = static::id_key();
		$sql = sprintf( "DELETE FROM `%s` WHERE `%s` = '%s'", static::db_table(), $id_key, $this->$id_key );
		global $wpdb;
		$wpdb->query( $sql );
		return $this;
	}

	public function __db_update( $fields = null )
	{
		$id_key = static::id_key();

		if ( $fields === null )
			$fields = $this->get_field_data();

		if ( $this->$id_key === null )
			$this->db_insert( $fields );
		else
		{
			global $wpdb;
			$wpdb->update( static::db_table(), $fields, array( $id_key => $this->$id_key ) );
		}

		return $this;
	}

	public function __db_insert( $fields = null )
	{
		$id_key = static::id_key();

		if ( $fields === null )
			$fields = $this->get_field_data();

		global $wpdb;
		$wpdb->insert( static::db_table(), $fields );
		$this->$id_key = $wpdb->insert_id;
	}

	public static function __db_load( $id )
	{
		$array_requested = is_array( $id );

		// Make everything an array anyways.
		if ( ! $array_requested )
			$id = [ $id ];

		global $wpdb;
		$sql = sprintf( "SELECT * FROM `%s` WHERE `%s` IN ('%s')", static::db_table(), static::id_key(), implode( "','", $id ) );
		$result = $wpdb->get_results( $sql );

		if ( $array_requested )
			return static::sqls( $result );

		// Single ID requested.
		if ( count( $result ) != 1 )
			return false;
		$result = reset( $result );
		return static::sql( $result );
	}
}
