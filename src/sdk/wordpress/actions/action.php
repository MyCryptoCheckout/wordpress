<?php

namespace plainview\sdk_mcc\wordpress\actions;

/**
	@brief		Action handling class.
	@details	Uses objects to communicate and manipulate data.
	@since		2014-04-27 13:55:41
**/
class action
{
	use \plainview\sdk_mcc\traits\method_chaining;

	/**
		@brief		Can the action be regarded as finished?
		@detals		This variable is handled manually by whatever hooks are interested in telling other hooks to leave the action alone.
		@since		2014-04-27 14:00:35
	**/
	public $finished = false;

	/**
		@brief		The main constructor that sets up the action.
		@details	Calls _construct() when everything is set up, allowing the subclasses to do their own init without having to remember to call the parent constructor.
		@since		2014-04-27 13:59:10
	**/
	public function __construct()
	{
		call_user_func_array( [ $this, '_construct' ], func_get_args() );
	}

	/**
		@brief		Inheritable method that allows subclasses to init themselves without calling the parent classes.
		@since		2014-04-27 13:59:54
	**/
	public function _construct()
	{
	}

	/**
		@brief		Executes the action, sending it through all of Wordpress' registered hooks.
		@since		2014-04-27 13:57:00
	**/
	public function execute()
	{
		$action_name = $this->get_name();
		do_action( $action_name, $this );
		return $this;
	}

	/**
		@brief		Mark the action as finished.
		@since		2014-04-27 14:02:14
	**/
	public function finish( $finished = true )
	{
		return $this->set_boolean( 'finished', $finished );
	}

	/**
		@brief
		@since		2014-04-27 14:03:56
	**/
	public function get_name()
	{
		$class_name = get_class( $this );
		$class_name = preg_replace( '/.*\\\\/', '', $class_name );

		return sprintf( '%s%s%s',
			$this->get_prefix(),
			$class_name,
			$this->get_suffix()
		);
	}

	/**
		@brief		Get action prefix.
		@details	Optional prefix for all actions using this class. Suggest ending the prefix with an underscore.
		@since		2014-04-27 13:56:10
	**/
	public function get_prefix()
	{
		return '';
	}

	/**
		@brief		Get suffix prefix.
		@since		2014-04-27 13:56:10
	**/
	public function get_suffix()
	{
		return '';
	}

	/**
		@brief		Is the action finished? Should it be left alone?
		@since		2014-04-27 14:01:47
	**/
	public function is_finished()
	{
		return $this->finished;
	}
}
