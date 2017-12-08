<?php

namespace plainview\sdk_mcc\form2\inputs\traits;

/**
	@brief		Trait for inputs that contain options: select, radios, etc.
	@author		Edward Plainview <edward@plainview.se>
	@copyright	GPL v3
	@version	20130524
**/
trait options
{
	/**
		@brief		Return or create an option.
		@details

		If using only $p1 the existing option will be returned.

		If using both $p1 and $p2, then $p1 is the label and $p2 is the value.

		The parameter order is due to restrictions in xgettext, used in translations. If uses only the first parameter of a keyword, in this case option_(). So the design decision is to consistently use the label as the first parameter when creating options.

		For example:

		@code
			// Create the select
			$form->select( 'best_friend' )
				->label( 'Your best friend' )
				->title( 'Click on your best friend' );

			// This will create the option 'eric'.
			$form->select( 'best_friend' )->option( 'Eric the Half-A-Bee', 'eric' );

			// This will return the option 'eric'.
			$eric = $form->select( 'best_friend' )->option( 'eric' );
		@endcode

		@param		string		$p1		Either the label of the new option, if used with $p2, or the value if used to retrieve an existing option.
		@param		string		$p2		The value of the new option.
		@param		option		The newly-created option.
		@since		20130524
	**/
	public function option( $p1, $p2 = null )
	{
		if ( $p2 === null )
			return $this->get_option( $p1 );
		$o = new \stdClass();
		$o->container = $this->container;
		$o->id = $this->get_id() . '_' . $p2;
		$o->container_name = $this->get_attribute( 'name' );
		$o->name = $p2;
		$o->label = $p1;
		$o->value = $p2;
		$option = $this->new_option( $o );
		return $this->add_option( $option );
	}

	/**
		@brief		Convenience method to create an option using sprintf.
		@details

		Instead of sprintf'ing a string and then sending it to option(), this convenience method can be used.

		The first parameter is the string, the following parameters are parameters to be passed to sprintf, and the last parameter is the actual value.

		@param		string		$p1		The string to be passed to sprintf
		@param		mixed		$p2		Either replacement values for sprintf, or the value of the option.
		@see		option()
		@see		option_()
		@since		20130524
	**/
	public function optionf( $p1, $p2 )
	{
		$args = func_get_args();
		// The last argument is the actual value. Save it and pop it off the end else sprintf will become grumpy.
		$value = end( $args );
		array_pop( $args );
		$label = call_user_func_array( 'sprintf', $args );
		return $this->option( $label, $value );
	}

	/**
		@brief		Translate and create an option.
		@details

		Strings are sprintf'd before translation. If the string has any sprintf keywords like %s or %d, then insert the replacement values as extra parameters between the $label and the $value parameters.

		@code
			$form->select( 'best_friend' )
				->option_( 'Friend %s: Eric', $friend_number, 'eric' );
		@endcode

		@param		string		$label		The string to translate.
		@param		string		$key		THe option's value.
		@since		20130524
	**/
	public function option_( $label, $value )
	{
		$args = func_get_args();
		// The last argument is the actual value. Save it and pop it off the end else _() will become grumpy.
		$value = end( $args );
		array_pop( $args );
		$label = call_user_func_array( array( $this->container, '_' ), $args );
		return $this->option( $label, $value );
	}

	/**
		@brief		Add several options as once.
		@details

		The array should use the format ( label => value ) to be consistent with option().

		@see		option()
		@since		20130524
	**/
	public function options( $array )
	{
		foreach( $array as $label => $key )
			$this->option( $label, $key );
		return $this;
	}

	public function options_to_inputs()
	{
		$r = '';
		$name = $this->_name;
		$class = preg_replace( '/.*\\\\/', '', get_class( $this ) );
		$r .= '<div class="' . $class . ' ' . $name . '">';
		foreach( $this->get_options() as $index => $option )
		{
			$option_value = $option->get_attribute( 'value' );

			$o = new \stdClass();
			$o->container = $this->container;
			$o->id = $name . '_' . $index;
			// Checkboxes use name_id, but radios must have the same name for all.
			$o->name = $name . '_' . $index;
			// Here's for the radios.
			$o->container_name = $name;
			$o->value = $option_value;
			$input = $this->new_option( $o );
			$input->check( $option->is_checked() );
			$input->disabled( $this->is_disabled() );
			if ( $this->is_required() )
				$input->required();

			// Point the label to the new input.
			$input->label( $option->label->content );
			$input->label->set_input( $input );

			$input->prefix = $this->prefix;

			// Divs to make them separate lines.
			$class = preg_replace( '/.*\\\\/', '', get_class( $input ) );
			$r .= $this->indent() . '<div class="'. $class .'">' . $input->display_input() . ' ' . $input->label . "</div>\n";
		}
		$r .= '</div>';
		return $r;
	}

	public function use_post_value()
	{
		// Unset the checked status of all inputs.
		foreach( $this->get_options() as $option )
			$option->check( false );
		$value = $this->get_post_value();
		$this->value( $value );
	}

	/**
		@brief		Set the value (check) one or more of the internal checkboxes.
		@details	To set several values at once, use more method paramters.
		@param		string		$value		Value to set.
		@param		string		$value2		Optional value to set.
		@return		this		Object chaining.
		@since		20130524
	**/
	public function value( $value, $value2 = null )
	{
		$values = func_get_args();
		foreach ( $values as $value )
			foreach( $this->get_options() as $option )
			{
				if ( $option->get_value() == $value )
					$option->check();
			}
		return $this;
	}

}

