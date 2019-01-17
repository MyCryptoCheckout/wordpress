<?php

namespace plainview\sdk_mcc\wordpress\object_stores;

/**
	@brief		The object is stored as a post.
	@since		2017-03-22 13:09:50
**/
trait Post
{
	use Store;

	/**
		@brief		Modified keys.
		@since		2017-03-22 15:15:59
	**/
	public $__modified_keys = [];

	/**
		@brief		The ID of this post, if any.
		@since		2017-03-22 14:28:20
	**/
	public $id;

	/**
		@brief		An object of post meta data.
		@since		2017-03-22 13:24:34
	**/
	public $meta;

	/**
		@brief		The post data as retrieved by get_post.
		@since		2017-03-22 13:21:13
	**/
	public $post;

	/**
		@brief		Constructor.
		@since		2017-03-22 14:27:26
	**/
	public function __construct()
	{
		$this->meta = (object)[];
		$this->post = new \WP_Post( (object)[] );
		foreach( $this->post as $key => $value )
			$this->$key = $value;

		$this->load_meta();

		$this->clear_modified_keys();

		$this->_construct();
	}

	/**
		@brief		Allow subclasses to construct.
		@since		2017-03-22 20:11:25
	**/
	public function _construct()
	{
	}

	/**
		@brief		Allow subclasses to override the loading of the meta.
		@since		2017-03-22 13:42:29
	**/
	public function after_load_meta()
	{
		return $this;
	}

	/**
		@brief		Allow special handling after save.
		@since		2017-03-22 15:02:27
	**/
	public function after_save()
	{
	}

	/**
		@brief		Called before loading meta.
		@details	Return true else load_meta() won't complete.
		@since		2017-03-22 15:05:05
	**/
	public function before_load_meta()
	{
		return true;
	}

	/**
		@brief		Called after saving meta.
		@since		2017-03-22 15:03:49
	**/
	public function after_save_meta()
	{
	}

	/**
		@brief		Allow special handling before save.
		@details	Return true else save() won't complete.
		@since		2017-03-22 15:02:51
	**/
	public function before_save()
	{
		return true;
	}

	/**
		@brief		Called before saving meta.
		@details	Return true or save_meta won't complete.
		@since		2017-03-22 15:03:59
	**/
	public function before_save_meta()
	{
		return true;
	}

	/**
		@brief		Clear the modified keys array.
		@since		2017-03-22 15:17:39
	**/
	public function clear_modified_keys()
	{
		$this->__modified_keys = [];
		return $this;
	}

	public function delete()
	{
		wp_delete_post( $this->id );
	}

	/**
		@brief		Return the post status.
		@since		2017-03-22 13:20:07
	**/
	public function get_post_status()
	{
		return $this->post_status;
	}

	/**
		@brief		Return the post type of this object.
		@since		2017-03-22 13:12:19
	**/
	public static function get_post_type()
	{
		throw new Exception( 'Please override the get_post_type method.' );
	}

	public static function load_from_store( $key )
	{
		$post = get_post( $key );
		if ( ! $post )
			return false;

		if ( $post->post_type != static::get_post_type() )
			return false;

		$r = new static();
		$r->id = $post->ID;		// Convenience, since it's easier to write id than ID.
		$r->post = $post;

		// Insert all of the original values of the post as properties.
		foreach( $post as $key => $value )
			$r->$key = $value;

		$r->load_meta();

		return $r;
	}

	/**
		@brief		Return an array of meta keys we use.
		@details	If you're using a superclass, put this in there.
		@since		2017-03-22 13:26:38
	**/
	public function get_meta_keys()
	{
		return [];
	}

	/**
		@brief		Load the meta from the database.
		@since		2017-03-22 13:35:34
	**/
	public function load_meta()
	{
		// The meta is an object.
		if ( ! is_object( $this->meta ) )
			$this->meta = (object)[];

		if ( ! $this->before_load_meta() )
			return $this;

		$meta = get_post_meta( $this->id );
		foreach( $this->get_meta_keys() as $key )
		{
			if ( ! isset( $meta[ $key ] ) )
				$this->meta->$key = null;
			else
				$this->meta->$key = reset( $meta[ $key ] );		// I've never encountered meta with several keys.
		}

		return $this->after_load_meta();
	}

	/**
		@brief		Keep track of all modified keys.
		@since		2017-03-22 15:15:41
	**/
	public function set( $key, $value )
	{
		$this->__modified_keys[ $key ] = $key;
		$this->$key = $value;
		return $this;
	}

	/**
		@brief		Save this post together with its meta.
		@since		2017-03-24 18:36:00
	**/
	public function save()
	{
		if ( ! $this->before_save() )
			return $this;

		$this->set( 'post_type', static::get_post_type() );
		$this->set( 'post_status', $this->get_post_status() );

		$new_data = [];

		if ( $this->id )
			$new_data[ 'ID' ] = $this->id;

		foreach( $this->__modified_keys as $key )
			$new_data[ $key ] = $this->$key;

		if ( count( $new_data ) > 0 )
		{
			if ( ! $this->id )
			{
				$this->id = wp_insert_post( $new_data );
				$this->post = get_post( $this->id );
			}
			else
			{
				wp_update_post( $new_data );
			}
		}

		$this->save_meta();

		$this->after_save();

		$this->clear_modified_keys();

		return $this;
	}

	/**
		@brief		Save the meta data.
		@since		2017-03-22 13:27:27
	**/
	public function save_meta()
	{
		if ( ! $this->before_save_meta() )
			return $this;

		// Update the meta values.
		foreach( $this->get_meta_keys() as $key )
			update_post_meta( $this->id, $key, $this->meta->$key );

		$this->after_save_meta();

		return $this;
	}

	/**
		@brief		Set the value of a meta key.
		@since		2017-03-24 18:17:16
	**/
	public function set_meta( $meta_key, $meta_value )
	{
		$this->meta->$meta_key = $meta_value;
		return $this;
	}
}
