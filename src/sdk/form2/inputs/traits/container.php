<?php

namespace plainview\sdk_mcc\form2\inputs\traits;

/**
	@brief		Input containing more inputs.
	@details

	Forms and fieldsets are containers.

	Contains methods for generating inputs.

	@author		Edward Plainview <edward@plainview.se>
	@copyright	GPL v3
**/
/**
	@brief		A container of inputs.
	@details	A form or a fieldset count as input containers.
**/
trait container
{
	use \plainview\sdk_mcc\html\indentation;

	/**
		@brief		Inputs in this container.
		@var		$inputs
		@since		20130524
	**/
	public $inputs = array();

	/**
		@brief		Has this container been validated?
		@var		$validated
		@since		20130524
	**/
	public $validated = false;

	/**
		@brief		Used to create inputs.
		@details

		Instead of calling ->input( 'select', 'testname' ) or something, the SDK allows devs to call ->select( 'testname' ) directly.
		@param		string		$name		Name of the unknown method.
		@param		array		$arguments	Arguments used
		@return		The retrieved or created input.
		@since		20130524
	**/
	public function __call( $name, $arguments )
	{
		$form = $this->form();
		if ( ! $form->is_input_type_registered ( $name ) )
			return false;
		// If it was called with a null ID, put a null in there.
		if ( count( $arguments ) < 1 )
			array_push( $arguments, null );
		// Second parameter for input() is the type of input.
		array_push( $arguments, $name );
		return call_user_func_array( array( $this, 'input' ), $arguments );
	}

	public function __toString()
	{
		$container = clone( $this );
		$r = $container->__toString_before_container();
		$r .= $container->indent() . $container->open_tag() . "\n";
		$r .= $container->__toString_before_inputs();
		foreach( $container->inputs as $input )
			$r .= $input;
		$r .= $container->__toString_after_inputs();
		$r .= $container->indent() . $container->close_tag() . "\n";
		$r .= $container->__toString_after_container();
		return $r;
	}

	/**
		@brief		Allow subclasses to housekeep before displaying the container.
		@return		string		Empty string.
		@since		20130805
	**/
	public function __toString_after_container()
	{
		return '';
	}

	/**
		@brief		Allow subclasses to housekeep after displaying the inputs.
		@return		string		Empty string.
		@since		20130524
	**/
	public function __toString_after_inputs()
	{
		return '';
	}

	/**
		@brief		Allow subclasses to housekeep after displaying the container.
		@return		string		Empty string.
		@since		20130805
	**/
	public function __toString_before_container()
	{
		return '';
	}

	/**
		@brief		Allow subclasses to housekeep before displaying the inputs.
		@return		string		Empty string.
		@since		20130524
	**/
	public function __toString_before_inputs()
	{
		return '';
	}

	/**
		@brief		Translate a string for an input.
		@details	Is called by an input and calls the form's _() method.
		@param		string		$string		String to translate.
		@return		string		Translated string.
		@since		20130524
	**/
	public function _( $string )
	{
		if ( isset( $this->container ) )
			$form = $this->container;
		else
			$form = $this;
		return call_user_func_array( array( $form, '_' ), func_get_args() );
	}

	/**
		@brief		Add an input.
		@param		input		$input		Input to add to the inputs array,
		@return		this		Object chaining.
		@since		20130524
	**/
	public function add_input( $input )
	{
		$input->form = $this->form();
		$input->container = $this;
		$name = $input->get_attribute( 'name' );
		$this->inputs[ $name ] = $input;
		return $this;
	}

	/**
		@brief		Return a list of all validation errors from all of the inputs.
		@details	Will recurse into containers.
		@return		array		An array of all validation errors found.
		@since		20130524
	**/
	public function get_validation_errors()
	{
		$r = [];
		foreach( $this->inputs as $input )
			foreach( $input->get_validation_errors() as $error )
				$r[] = $error;
		return $r;
	}

	/**
		@brief		Create a hidden input.
		@param		string		$name		The name of the input to create.
		@return		hidden		The newly-created hidden input.
		@since		20130524
	**/
	public function hidden_input( $name )
	{
		return $this->input( $name, 'hidden' );
	}

	/**
		@brief		Retrieve or create an input.
		@details	When trying to retrieve an input will recurse into all inputs that are containers.

		Names may not include points.

		@param		string		$name		Name of input to retrieve / create.
		@param		string		$type		The type of input to create.
		@return		input		The found or created input.
		@since		20130524
	**/
	public function input( $name, $type = null )
	{
		if ( isset( $this->inputs[ $name ] ) )
			return $this->inputs[ $name ];
		else
		{
			foreach( $this->inputs as $container )
			{
				if ( ! method_exists( $container, 'input' ) )
					continue;
				$r = $container->input( $name );
				if ( $r !== false )
					return $r;
			}
		}
		if ( $type === null )
			return false;
		$form = $this->form();
		$input_type = $form->get_input_type( $type );
		$type = $input_type->class;
		$name = str_replace( '.', '_', $name );
		$input = new $type( $this, $name );
		$this->add_input( $input );
		return $input;
	}

	/**
		@brief		Return a list of all inputs of this container and any subcontainers.
		@return		collection		A collection of inputs.
		@since		20131015
	**/
	public function inputs()
	{
		$r = new \plainview\sdk_mcc\collections\collection;
		foreach( $this->inputs as $input )
		{
			$r->append( $input );
			if ( method_exists( $input, 'inputs' ) )
			{
				foreach( $input->inputs() as $contained_input )
					$r->append( $contained_input );
			}
		}
		return $r;
	}

	/**
		@brief		Asks the inputs to use their post values.
		@return		this		Object chaining.
		@since		20130524
	**/
	public function use_post_value()
	{
		foreach( $this->inputs as $input )
			$input->use_post_value();
		return $this;
	}

	/**
		@brief		Validates all of the inputs.
		@return		this		Object chaining.
		@since		20130524
	**/
	public function validate()
	{
		foreach( $this->inputs as $input )
			$input->validates();
		$this->has_validated = true;
		return $this;
	}
}
