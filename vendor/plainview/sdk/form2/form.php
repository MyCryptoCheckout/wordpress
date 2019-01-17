<?php

namespace plainview\sdk_mcc\form2;

/**
	@brief		HTML5/XHTML form manipulation class.
	@details

	Provides form generation, manipulation, _POST handling and validation.

	All values and labels are stored filtered and can be displayed directly.

	Examples
	--------

	@par		Generate a form with a text input

	@code
		$form = new \\plainview\\sdk_mcc\\form2\\form();
		// Add a text input
		$form->text( 'username' )
			->label( 'Your username' );
		// And display the form. Start() opens the form tag and stop()...
		echo $form->start() . $form . $form->stop();
	@endcode

	@par		Add more attributes to the above text field

	@code
		// Using the same name will find the existing input
		$form->text( 'username' )
			->description( 'You should have received your username by now.' )
			->maxlength( 64 )
			->size( 40 )
			->title( "Hovering won't help you." );
	@endcode

	@par		How about a select input?

	@code
		$form->select( 'age_group' )
			->description( 'Which group do you identify with most?' )
			->label( 'Age group' )
			->option( '0 to 10 year olds', '0_10' )
			->option( '11 to 37 year olds', '11-37' )
			->option( 'Other', 'other' )
			->value( 'other' );		// Default value is other.
	@endcode

	@par		Add a submit button

	@code
		$form->submit( 'login' )
			->value( 'Log me in' )
			->title( 'Will log you into the system, using only your username!' );
	@endcode

	@par		Handle the submit button

	@code
		// Is there anything in the _POST array?
		if ( $form->is_posting() )
		{
			// Ask the form to retrieve the form values.
			$form->post();
			if ( $form->input( 'login' )->pressed() )
				echo "The login button was pressed!";
		}
	@endcode

	@par		Add validation

	@code
		$form->text( 'username' )
			->required();
	@endcode

	And when the form is posted:

	@code
		if ( $form->validates() )
		{
			echo "Form validates!";
		}
		else
		{
			$errors = $form->get_validation_errors();
			foreach ( $errors as $error )
				echo $error->get_label();
		}
	@endcode


	Changelog
	---------

	- 20140508	form version removed.
			Fixed containers validation. Added test.
	- 20140311	datetime and datetime-local inputs now properly return a correct datetime.
	- 20140218	display of an input includes the type and tag as css classes.
	- 20140121      select + multiple: get_post_value when nothing is selected now returns an empty array instead of null.
	- 20131112	unfilter_text does even more unfiltering.
	- 20131109	select input __toString gets its own display div (with hidden support)
	- 20131015	container trait gets inputs()
	- 20131009	enctype fix. Added file input.
	- 20131004	markup ignores post values - will no longer disappear.
	- 20131001	input->title also sets the title of the label.
	- 20130929	description->is_empty(), label->is_empty() \n
				Input div container inherits the input's css classes.
	- 20130925	Input datetime_local changed to datetimelocal.
	- 20130910	Select option uses traits more verbosely.
	- 20130820	Fixed number input validation and translation strings. \n
				Fieldset subclasses can report their inputs.
	- 20130819	Validation errors now have the input as the container. Radios required() fixed.
	- 20130815	input->get_display_div.
	- 20130814	Value filters are functions, not closures, which enables serializing.
	- 20130807	Added text plaintext() filter.
	- 20130806	Radios and checkboxes are fieldsets. \n
				Inputs are described using aria-describedby.
	- 20130805	Added Radios text. Fixed radios names. \n
				Added $form_version.
	- 20130718	Added unit testing. Just run phpunit in this directory. \n
				validates() automatically runs validate() if necessary. \n
				set_post_value().
	- 20130701	Hidden input is created using hidden_input(). \n
				Duplicate traits removed from inputs. \n
				Text now allows for minlength.
	- 20130606	Hidden inputs no longer have labels. \n
				Form default action is the current URL and default method is POST.
	- 20130604	Errors can __tostring() themselves. \n
				is_posting() automatically calls post(). \n
				validate_required() has better checking.
	- 20130524	Initial version

	@author		Edward Plainview <edward@plainview.se>
	@copyright	GPL v3
**/
class form
{
	use \plainview\sdk_mcc\html\element;
	use inputs\traits\validation;
	use inputs\traits\container
	{
		inputs\traits\container::get_validation_errors insteadof inputs\traits\validation;
		inputs\traits\container::validate insteadof inputs\traits\validation;
	}
	use inputs\traits\prefix;
	use inputs\traits\sort_order;

	/**
		@brief		Has the form handled the POST array?
		@see		is_posting()
		@var		$has_posted
		@since		20130723
	**/
	public $has_posted = false;

	/**
		@brief		Array of objects containing information about the available input types.
		@var		$input_types
		@since		20130524
	**/
	public $input_types = array();

	/**
		@brief		The _POST array with which to work.
		@see		post()
		@var		$post
		@since		20130524
	**/
	public $post = null;

	public $tag = 'form';

	public function __construct()
	{
		// Add the standard input types.
		$input_types = array(
			'button',
			'checkbox',
			'checkboxes',
			'datalist',
			'date',
			'datetime',
			'datetimelocal',
			'email',
			'fieldset',
			'file',
			'hidden',
			'markup',
			'meter',
			'month',
			'number',
			'password',
			'radio',
			'radios',
			'range',
			'search',
			'select',
			'submit',
			'tel',
			'time',
			'text',
			'textarea',
			'url',
			'week',
		);
		foreach( $input_types as $input_type )
		{
			$o = new \stdClass();
			$o->name = $input_type;
			$o->class = '\\plainview\\sdk_mcc\\form2\\inputs\\' . $input_type;
			$this->register_input_type( $o );
		}

		// action may not be empty
		$this->set_attribute( 'action', \plainview\sdk_mcc\base::current_url() );
		// default method is post
		$this->set_attribute( 'method', 'post' );
	}

	/**
		@brief		Provide subclasses a chance to translate strings.
		@param		string		$string		String to translate.
		@return		string		The translated, or untranslated, string.
		@since		20130524
	**/
	public function _(  $string )
	{
		return $string;
	}

	/**
		@brief		Set the action of this form.
		@param		string		$action		Any string.
		@return		this		Object chaining.
		@see		action()
		@see		enctype()
		@since		20130712
	**/
	public function action( $action )
	{
		return $this->set_attribute( 'action', $action );
	}

	/**
		@brief		Clear the stored POST array.
		@return		this		Object chaining.
		@since		20130524
	**/
	public function clear_post()
	{
		$this->post = null;
		$this->has_posted = true;
		return $this;
	}

	/**
		@brief		Set the encoding type of this form.
		@details	The default is 'application/x-www-form-urlencoded'.
		@param		string		$enctype		The encoding type to use.
		@return		this		Object chaining.
		@see		action()
		@see		method()
		@since		20130524
	**/
	public function enctype( $enctype )
	{
		$enctypes = array( 'application/x-www-form-urlencoded', 'multipart/form-data', 'text/plain' );
		if ( ! in_array( $enctype, $enctypes ) )
			$enctype = reset( $enctypes );
		return $this->set_attribute( 'enctype', $enctype );
	}

	/**
		@brief		Filter a string.
		@details	Makes the string safe to be displayed / saved.

		Currently just runs htmlspecialchars on it.

		@param		string		$text		Text to filter.
		@return		string		The filtered text string.
		@since		20130524
		@see		unfilter_text()
	**/
	public static function filter_text( $text )
	{
		$text = htmlspecialchars( $text );
		return $text;
	}

	/**
		@brief		Return the form object.
		@details	Exists as an override to the container trait.
		@return		this		This form object.
		@since		20130524
	**/
	public function form()
	{
		return $this;
	}

	/**
		@brief		Return an input type.
		@param		string		$name		Name of the input type. hidden or textarea or whatever.
		@return		mixed		The input type object specified, or false if it isn't registered.
		@since		20130524
	**/
	public function get_input_type( $name )
	{
		if ( ! $this->is_input_type_registered( $name ) )
			return false;
		return $this->input_types[ $name ];
	}

	/**
		@brief		Return the POST value for an input name.
		@details	The $name variable should be the complete name used in the form, with [] prefixes and all.

		This method will then search the form's POST variable for the input value.

		Will return null if the value is not set.

		@param		string		$name		The name of the input to fetch.
		@see		post()
		@see		set_post_value()
		@since		20130524
	**/
	public function get_post_value( $name )
	{
		// No prefix?
		if ( strpos( $name, '['  ) === false )
		{
			if ( ! isset( $this->post[ $name ] ) )
				return null;
			else
				return $this->post[ $name ];
		}
		else
		{
			// Prepare to split the name up into arrays.
			$name = preg_replace( '/\[/', '][', $name, 1 );
			$name = rtrim( $name, ']' );
			$names = explode( '][', $name );

			// Delve into the POST array.
			$post = $this->post;
			do
			{
				$name = array_shift( $names );
				if ( ! isset( $post[ $name ] ) )
					return null;
				$post = $post[ $name ];
			} while ( count( $names ) > 0 );
			return $post;
		}
	}

	/**
		@brief		Returns if this input type is registered.
		@param		string		$name		Name of input type.
		@return		bool		True if the input type is registered.
		@see		get_input_type()
		@see		register_input_type()
		@since		20130524
	**/
	public function is_input_type_registered( $name )
	{
		return isset( $this->input_types[ $name ] );
	}

	/**
		@brief		Is there data in the POST array?
		@details	Automatically calls post() with the given $post variable, if necessary.

		Necessary = the _POST contains data.

		@param		array		$post		Optional POST array to check. If not specified will use _POST.
		@return		this		Object chaining.
		@see		action()
		@see		enctype()
		@see		post()
		@since		20130524
	**/
	public function is_posting( array $post = null )
	{
		if ( $this->has_posted )
			return count( $this->post ) > 0;
		$post = ( $post === null ? $_POST : $post );
		$posting = count( $post ) > 0;
		if ( $posting )
			$this->post( $post );
		return $posting;
	}

	/**
		@brief		Set the method of this form.
		@param		string		$method		Method to set: either post (default) or get.
		@return		this		Object chaining.
		@see		action()
		@see		enctype()
		@since		20130524
	**/
	public function method( $method )
	{
		$methods = array( 'post', 'get' );
		if ( ! in_array( $method, $methods ) )
			$method = reset( $methods );
		return $this->set_attribute( 'method', $method );
	}

	/**
		@brief		Set the novalidate attribute of the form.
		@param		bool		$novalidate			True to not validate the form.
		@return		this		Object chaining.
		@since		20130524
	**/
	public function novalidate( $novalidate = true )
	{
		return $this->set_boolean_attribute( $novalidate );
	}

	/**
		@brief		Give the form a POST array with which to work.
		@details	Either leave empty to automatically use the $_POST, or give the method an array.
		@param		array		$post		POST array with which to work.
		@return		this		This.
		@since		2013
	**/
	function post( array $post = null )
	{
		$this->post = ( $post === null ? $_POST : $post );
		$this->use_post_value();
		$this->has_posted = true;
		return $this;
	}

	/**
		@brief		Return whether the internal POST property is set.
		@return		bool		True if the property is set.
		@see		clear_post()
		@see		post()
		@since		20130524
	**/
	function post_is_set()
	{
		return $this->post !== null;
	}

	/**
		@brief		Register an input type.
		@details	The $o object must contain:

		- @b name The name of the input type: hidden, text, textarea, number, etc.
		- @b class The string identifier of the class, including namespace. See the constructor for examples.

		@param		object		$o		Input type object.
		@return		this		Object chaining.
		@see		is_input_type_registered()
		@since		20130524
	**/
	public function register_input_type( $o )
	{
		$this->input_types[ $o->name ] = $o;
		return $this;
	}

	/**
		@brief		Set the POST value for an input name.
		@param		string		$name		The name of the input to set.
		@param		string		$value		The new value to set.
		@see		post()
		@see		get_post_value()
		@since		20130712
	**/
	public function set_post_value( $name, $value )
	{
		// No prefix?
		if ( strpos( $name, '['  ) === false )
		{
			$this->post[ $name ] = $value;
		}
		else
		{
			// Prepare to split the name up into arrays.
			$name = preg_replace( '/\[/', '][', $name, 1 );
			$name = rtrim( $name, ']' );
			$names = explode( '][', $name );

			$post = &$this->post;
			while( count( $names ) > 0 )
			{
				$name = array_shift( $names );
				if ( count( $names ) == 0 )
					break;
				if ( ! isset( $post[ $name ] ) )
					$post[ $name ] = [];
				$post = &$post[ $name ];
			}
			$post[ $name ] = $value;
		}
		return $this;
	}

	/**
		@brief		Remove filtering from text.
		@param		string		$text		String to unfilter.
		@return		string		Unfiltered string.
		@see		filter_text()
		@since		20130524
	**/
	public static function unfilter_text( $text )
	{
		$text = htmlspecialchars_decode( $text );
		$text = html_entity_decode( $text, ENT_QUOTES | ENT_HTML5 );
		return $text;
	}
}

