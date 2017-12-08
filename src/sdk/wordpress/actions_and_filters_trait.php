<?php

namespace plainview\sdk_mcc\wordpress;

/**
	@brief		Base class for the Plainview Wordpress SDK.
	@details	Provides a framework with which to build Wordpress modules.
	@author		Edward Plainview	edward@plainview.se
	@copyright	GPL v3
**/
trait actions_and_filters_trait
{
	/**
		@brief		Convenience function to add a Wordpress action.
		@details	Using almost the same parameters as add_action(), this method can be used if the action has the same base method name as the callback.

		If that is the case, then $callback can be skipped. Priority and parameters can also be skipped if you are using the same default values as Wordpress' add_action().

		Example:

		@code
			$this->add_action( 'plainview_enter_castle', 'action_plainview_enter_castle', 10, 1 );		// All parameters specified
			$this->add_action( 'plainview_enter_castle', 'action_plainview_enter_castle' );				// Priority and parameter count skipped (using Wordpress defaults)
			$this->add_action( 'plainview_enter_castle', 10, 1 );										// Calls $base->plainview_enter_castle
			$this->add_action( 'plainview_enter_castle' );												// Calls $base->plainview_enter_castle
			$this->add_action( 'plainview_enter_castle', null, 3 );										// Uses Wordpress default priority and three parameters.
		@endcode

		@param		string		$action			The name of the action to create.
		@param		mixed		$callback		Either the callback, or the priority, or nothing.
		@param		mixed		$priority		If $callback is specified, then this is the priority. Else this is the amount of parameters.
		@param		mixed		$parameters		Used only if callback and priority are specified.
		@since		20130505
	**/
	public function add_action( $action, $callback = null, $priority = null, $parameters = null )
	{
		return $this->add_remove_action_filter( [
			'action' => 'add',
			'args' => func_get_args(),
			'type' => 'action',
		] );
	}

	/**
		@brief		Convenience function to add a Wordpress filter.
		@details	Using almost the same parameters as add_filter(), this method can be used if the filter has the same base method name as the callback.

		If that is the case, then $callback can be skipped. Priority and parameters can also be skipped if you are using the same default values as Wordpress' add_filter().

		Example:

		@code
			$this->add_filter( 'plainview_enter_castle', 'filter_plainview_enter_castle', 10, 1 );		// All parameters specified
			$this->add_filter( 'plainview_enter_castle', 'filter_plainview_enter_castle' );				// Priority and parameter count skipped (using Wordpress defaults)
			$this->add_filter( 'plainview_enter_castle', 10, 1 );										// Calls $base->plainview_enter_castle
			$this->add_filter( 'plainview_enter_castle' );												// Calls $base->plainview_enter_castle
			$this->add_filter( 'plainview_enter_castle', null, 3 );										// Uses Wordpress default priority and three parameters.
		@endcode

		@param		string		$filter			The name of the filter to create.
		@param		mixed		$callback		Either the callback, or the priority, or nothing.
		@param		mixed		$priority		If $callback is specified, then this is the priority. Else this is the amount of parameters.
		@param		mixed		$parameters		Used only if callback and priority are specified.
		@since		20130505
	**/
	public function add_filter( $filter, $callback = null, $priority = null, $parameters = null )
	{
		return $this->add_remove_action_filter( [
			'action' => 'add',
			'args' => func_get_args(),
			'type' => 'filter',
		] );
	}

	/**
		@brief		Convenience method to add a shortwith with the same method name as the shortcode.
		@param		string		$shortcode		Name of the shortcode, which should be the same name as the method to be called in the base.
		@param		string		$callback		An optional callback method. If null the callback is assumed to have the same name as the shortcode itself.
		@since		20130505
	**/
	public function add_shortcode( $shortcode, $callback = null )
	{
		if ( $callback === null )
			$callback = $shortcode;
		return add_shortcode( $shortcode, array( $this, $callback ) );
	}

	/**
		@brief		Adds a Wordpress action or filter.
		@see		add_action
		@see		add_filter
		@since		20130505
	**/
	public function add_remove_action_filter( $options )
	{
		$options = (object) $options;
		// The add type is the first argument
		$thing = $options->args[ 0 ];
		// If the callback is not specified, then assume the same callback as the thing.
		if ( ! isset( $options->args[ 1 ] ) )
			$options->args[ 1 ] = $options->args[ 0 ];
		// Is the callback anything but a string? That means parameter 1 is the priority.
		if ( ! is_string( $options->args[ 1 ] ) )
			array_splice( $options->args, 1, 0, $thing );
		// * ... which is then turned into a self callback.
		if ( ! is_array( $options->args[ 1 ] ) )
			$options->args[ 1 ] = array( $this, $options->args[ 1 ] );
		// No parameter count set? Unset it to allow add_* to use the default Wordpress value.
		if ( isset( $options->args[ 3 ] ) && $options->args[ 3 ] === null )
			unset( $options->args[ 3 ] );
		// Is the priority set to null? Then use the Wordpress default.
		if ( isset( $options->args[ 2 ] ) && $options->args[ 2 ] === null )
			$options->args[ 2 ] = 10;

		$what_to_do = $options->action . '_' . $options->type;
		return call_user_func_array( $what_to_do, $options->args );
	}

	/**
		@brief		Convenience function to call apply_filters.
		@details	Has same parameters as apply filters. Will insert a null if there are no arguments.
		@since		20130416
	**/
	public static function filters()
	{
		$args = func_get_args();

		if ( count( $args ) < 2 )
			$args[] = null;

		return call_user_func_array( 'apply_filters', $args );
	}

	/**
		@brief		Remove an action.
		@see		add_action()
		@since		2017-10-02 00:55:03
	**/
	public function remove_action( $action, $callback = null, $priority = null, $parameters = null )
	{
		return $this->add_remove_action_filter( [
			'action' => 'remove',
			'args' => func_get_args(),
			'type' => 'action',
		] );
	}
	/**
		@brief		Remove a filter.
		@see		add_filter()
		@since		2017-10-02 00:55:03
	**/
	public function remove_filter( $filter, $callback = null, $priority = null, $parameters = null )
	{
		return $this->add_remove_action_filter( [
			'action' => 'remove',
			'args' => func_get_args(),
			'type' => 'filter',
		] );
	}
}
