<?php

namespace plainview\sdk_mcc\form2\inputs;

/**
	@brief		Input superclass.
	@author		Edward Plainview <edward@plainview.se>
	@copyright	GPL v3
	@version	20131015
**/
class input
{
	use \plainview\sdk_mcc\html\element;
	use \plainview\sdk_mcc\html\indentation;
	use traits\disabled;		// Most elements can be disabled, so instead of including it 500 times later, just include it once here.
	use traits\label;			// Same reason as _disabled.
	use traits\datalist;		// Same reason as _disabled.
	use traits\prefix;
	use traits\readonly;		// Same reason as _disabled.
	use traits\sort_order;		// Same reason as _disabled.
	use traits\validation;		// All subinputs will inherit the validation methods.

	public $container;

	public $description = '';

	/**
		@brief		Does this input have a description?
		@var		$has_description
	**/
	public $has_description = true;

	/**
		@brief		Does this input have a label?
		@var		$has_label
	**/
	public $has_label = true;

	public $label;

	public $self_closing = true;

	public $tag = 'input';

	public function __construct( $container, $name )
	{
		$this->container = $container;
		$this->set_attribute( 'name', $name );
		$id = get_class( $this ) . '_' . $name;
		$id = str_replace( '\\', '_', $id );
		$this->set_attribute( 'id', $id );
		if ( $this->has_description )
			$this->description = new description( $this );
		if ( $this->has_label )
			$this->label = new label( $this );
		if ( isset( $this->type ) )
			$this->set_attribute( 'type', $this->type );
		$this->_construct();
	}

	/**
		@brief		Displays the input as a string.
		@details	Converting a combination of label + input + description, wrapped in a div, is a several step process.

		First, we must use placeholders to hold the above elements during the wrapping and tabbing process.
		Otherwise any tabs in the input / description / label will be modified, which breaks especially breaks textareas.

		Then, after wrapping and retabbing is done, replace the unique placeholders with the actual elements.

		@return		string		The input + label + description as a string.
		@since		20130524
	**/
	public function __toString()
	{
		$random = \plainview\sdk_mcc\base::uuid();

		$placeholders = new \stdClass();
		$placeholders->label = $random . 'label';
		$placeholders->input = $random . 'input';
		$placeholders->description = $random . 'description';

		$div = $this->get_display_div();

		// Prepare the input string that will be displayed to the user.
		$o = new \stdClass;
		$o->indent = $this->indent();
		$o->input = $placeholders->input;
		$o->label = $placeholders->label;
		if ( $this->has_description )
			if ( ! $this->description->is_empty() )
				$o->description = $placeholders->description;
		$input_string = $this->assemble_input_string( $o );

		$r = sprintf( '%s%s%s',
			$div->open_tag(),
			"\n",
			$input_string
		);
		// Increase one tab
		$r = preg_replace( '/^\\t/m', "\t\t", $r );
		// Close the tag
		$r = $this->indent() . $r . "\n" . $this->indent() . $div->close_tag() . "\n";

		// Replace the placeholders with their corresponding functions.
		foreach( $placeholders as $type => $placeholder )
		{
			$function = 'display_' . $type;
			$r = str_replace( $placeholder, $this->$function(), $r );
		}

		return $r;
	}

	/**
		@brief		Overridable method for subclasses to use instead of having to override the parent constructor and remembering to parent::construct.
		@since		20130524
	**/
	public function _construct()
	{
	}

	/**
		@brief		Assemble an input string.
		@details

		Given an object containing the following parts, assemble them all into a complete label + input + descritption string.

		- @b indent			A string of tabs of a specific width.
		- @b label			Label div/string/placeholder.
		- @b input			Input div/string/placeholder.
		- @b [description]	Optional description div/string/placeholder.

		This method exists to give subclasses the flexibility to display their own order (checboxes and radios need to display their input before their label, for example).

		@param		object		$o

		@return		string		The assembled string to display to the user.
		@since		20130806
	**/
	public function assemble_input_string( $o )
	{
		$r = '';
		$r .= $o->indent . '<div class="label_container">' . $o->label . "</div>\n";
		$r .= $o->indent . '<div class="input_container">' . $o->input . "</div>\n";
		if ( isset( $o->description ) )
			$r .= $o->indent . '<div class="description_container">' . $o->description . "</div>\n";
		return $r;
	}

	/**
		@brief		Return the input's container.
		@return		object		The container in which this input is placed. Form or fieldset.
		@since		20130524
	**/
	public function container()
	{
		return $this->container;
	}

	/**
		@brief		Set the description for this input.
		@param		string		$text		The text to set as the description.
		@return		this		Object chaining.
		@since		20130524
	**/
	public function description( $text )
	{
		call_user_func_array( [ $this->description, 'label' ], func_get_args() );
		return $this;
	}

	/**
		@brief		Translate and set the description for this input.
		@deprecated	Since 20180207
		@param		string		$text		The text to translate and set as the description.
		@return		this		Object chaining.
		@since		20130524
	**/
	public function description_( $text )
	{
		call_user_func_array( array( $this->description, 'label_' ), func_get_args() );
		return $this;
	}

	/**
		@brief		Request that the description convert itself to a string.
		@return		string		The description as a string.
		@since		20130524
	**/
	public function display_description()
	{
		return $this->description;
	}

	/**
		@brief		Display the input itself.
		@return		string		The input as HTML.
		@since		20130524
	**/
	public function display_input()
	{
		$input = clone( $this );

		$input->set_attribute( 'id', $input->make_id() );
		$name = $input->make_name();
		$input->set_attribute( 'name', $name );

		if ( $input->has_description )
			if ( $this->description->label->content != '' )
				$input->set_attribute( 'aria-describedby', $input->description->get_attribute( 'id' ) );

		$input->css_class( isset( $this->type ) ? $this->type : $this->tag );

		$input->css_class( 'input_' . $input->get_name() );

		if ( $input->is_required() )
			$input->css_class( 'required' );

		if ( $this->requires_validation() && $this->form()->is_posting() )
		{
			if ( ! $this->validates() )
				$input->css_class( 'does_not_validate' );
			else
				$input->css_class( 'validates' );
		}

		// Is the POST variable set?
		if ( $input->form()->post_is_set() )
		{
			// Retrieve the post value.
			$value = $input->get_value();
			if ( $value != '' )
			{
				$value = \plainview\sdk_mcc\form2\form::unfilter_text( $value );
				$input->value( $value );
			}
			else
				$this->clear_attribute( 'value' );
		}

		// Allow subclasses the chance to modify themselves in case displaying isn't straightforward.
		$input->prepare_to_display();

		return $input->open_tag() . $input->display_value() . $input->close_tag();
	}

	/**
		@brief		Display the input's label.
		@return		string		The label as HTML.
		@since		20130524
	**/
	public function display_label()
	{
		return $this->get_label()->toString();
	}

	/**
		@brief		Return the form object.
		@return		form		The form object.
		@since		20130524
	**/
	public function form()
	{
		return $this->container->form();
	}

	/**
		@brief		Return an input container div.
		@return		string		A div HTML element with lots of helpful classes set.
		@since		20130815
	**/
	public function get_display_div()
	{
		$r = new \plainview\sdk_mcc\html\div();
		$r->css_class( 'form_item' )
			->css_class( 'form_item_' . $this->get_name() )
			->css_class( 'form_item_' . $this->make_id() );
		if ( isset( $this->type ) )
			$r->css_class( 'form_item_' . $this->type );
		if ( isset( $this->tag ) )
			$r->css_class( 'form_item_' . $this->tag );

		// Get all the css classes for this input and add them to the div
		$r->css_class( $this->get_attribute( 'class' ) );

		// It would be a good idea if the container could include information about the status of the input.
		if ( $this->has_validation_errors() )
			$r->css_class( 'does_not_validate' );
		if ( $this->is_required() )
			$r->css_class( 'required' );

		// Hidden?
		if ( $this->is_hidden() )
			$r->hidden();

		return $r;
	}

	/**
		@brief		Returns the input's ID.
		@return		string		ID of input.
		@since		20130723
	**/
	public function get_id()
	{
		return $this->get_attribute( 'id' );
	}

	public function indentation()
	{
		return $this->container->indentation() + 1;
	}

	/**
		@brief		Make a unique ID for this input.
		@return		string		A unique string fit for use as the HTML ID attribute.
		@since		20130524
	**/
	public function make_id()
	{
		if ( $this->has_attribute( 'id' ) )
			return $this->get_attribute( 'id' );
		$id = $this->make_name();
		$id = \plainview\sdk_mcc\base::strtolower( $id );
		$id = preg_replace( '/[\[|\]]/', '_', $id );
		return $id;
	}

	/**
		@brief		Make the form name for this input.
		@details	Takes the prefixes into account when making the name.
		@return		string		A form name for this input.
		@since		20130524
	**/
	public function make_name()
	{
		$name = $this->get_attribute( 'name' );
		$names = array_merge( $this->get_prefixes(), [ $name ] );

		// The first prefix does NOT have brackets. The rest do. *sigh*
		$r = array_shift( $names );
		while ( count( $names ) > 0 )
			$r .= '[' . array_shift( $names ) . ']';

		return $r;
	}

	public function prepare_to_display()
	{
	}

	/**
		@brief		Set the title of this input.
		@since		2018-02-07 11:54:49
	**/
	public function set_title( $title )
	{
		$title = \plainview\sdk_mcc\form2\form::filter_text( $title );
		return $this->set_unfiltered_title( $title );
	}

	/**
		@brief		Set the title of this input without filtering it.
		@since		2018-02-07 11:54:49
	**/
	public function set_unfiltered_title( $title )
	{
		$this->label->title( $title );
		$this->set_attribute( 'title', $title );
		return $this;
	}

	/**
		@brief		Set the title attribute.
		@details	Also sets the title attribute of the label.
		@return		this		Object chaining.
		@since		20131001
	**/
	public function title( $text )
	{
		$result = @call_user_func_array( 'sprintf' , func_get_args() );
		if ( $result == '' )
			$result = $text;
		return $this->set_title( $result );
	}

	/**
		@brief		Convenience method to translate the title before setting it.
		@return		this		Object chaining.
		@since		20130524
	**/
	public function title_( $title )
	{
		$title = call_user_func_array( array( $this->container, '_' ), func_get_args() );
		return $this->title( $title );
	}

	/**
		@brief		Set the title of this input without filtering it.
		@since		2018-02-07 12:18:04
	**/
	public function unfiltered_title( $text )
	{
		$result = @call_user_func_array( 'sprintf' , func_get_args() );
		if ( $result == '' )
			$result = $text;
		return $this->set_unfiltered_title( $result );
	}
}
