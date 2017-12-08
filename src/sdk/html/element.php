<?php

namespace plainview\sdk_mcc\html;

/**
	@brief		HTML element trait for setting HTML elements and attributes.
	@details	Uses a combination of $this->tag and attributes to manipulate HTML elements.

	@par		Changelog

	- 20130729	has_attribute()
	- 20130702	content() added. toString() added.
	- 20130604	required() sets aria-required attribute also.
	- 20130524	New: get_boolean_attribute, set_boolean_attribute()
	- 20130514	New: clear_attributes()
	- 20130513	Element does no longer indent itself. css_class and css_style can take several arguments.
	- 20130509	New: clear_attribute()
	- 20130506	First version.

	@since		20130506
	@version	20130729
**/
trait element
{
	use attributes;

	/**
		@brief		Array of \\plainview\\sdk_mcc\\html\\attributes.
		@var		$attributes
		@since		20130506
	**/
	public $attributes = array();

	/**
		@brief		Content between the tags, if any.
		@var		$content
		@since		20130702
	**/
	public $content = '';

	/**
		@brief		Clones the attributes array.
		@since		20130510
	**/
	public function __clone()
	{
		$new_attributes = array();
		foreach( $this->attributes as $key => $attribute )
			$new_attributes[ $key ] = clone $attribute;
		$this->attributes = $new_attributes;
	}

	/**
		@brief		Converts the element to a string.
		@details	If the element is not self-closing, the content, if any, will be outputted between the tags.
		@return		string		This element as a string.
		@since		20130702
	**/
	public function toString()
	{
		if ( $this->self_closing() )
			return $this->open_tag();
		else
			return $this->open_tag() . $this->content . $this->close_tag();
	}

	/**
		@brief		Append a text to an attribute.
		@details	Text is appended with a space between.
		@param		string		$type		Type of attribute.
		@param		string		$text		Attribute text to append.
		@return		$this					Object chaining.
		@since		20130506
	**/
	public function append_attribute( $type, $text )
	{
		$this->attribute( $type )->add( $text );
		return $this;
	}

	/**
		@brief		Convenience function to (1) retrieve an attribute type or (2) append a value to it.
		@param		string		$type		Type of attribute.
		@param		string		$text		Attribute text.
		@return		mixed					The requested attribute.
		@see		append_attribute
		@since		20130506
	**/
	public function attribute( $type, $text = null )
	{
		if ( ! isset( $this->attributes[ $type ] ) )
			$this->attributes[ $type ] = new attribute( $type );
		if ( $text === null )
			return $this->attributes[ $type ];
		else
			return $this->append_attribute( $type, $text );
	}

	/**
		@brief		Clear an attribute.
		@param		string		$type		Type of attribute.
		@return		$this					Object chaining.
		@since		20130509
	**/
	public function clear_attribute( $type )
	{
		if ( isset( $this->attributes[ $type ] ) )
			unset( $this->attributes[ $type ] );
		return $this;
	}

	/**
		@brief		Clears all attributes.
		@return		$this		Object chaining.
		@since		20130514
	**/
	public function clear_attributes()
	{
		$this->attributes = array();
		return $this;
	}
	/**
		@brief		Output a string that closes the tag of this element.
		@return		string		The closed tag.
		@since		20130506
	**/
	public function close_tag()
	{
		if ( $this->self_closing() )
			return '';
		return sprintf( '</%s>', $this->tag );
	}

	/**
		@brief		Set the content of this element.
		@param		string		$content		Content to set.
		@return		$this						Object chaining.
		@since		20130703
	**/
	public function content( $content )
	{
		$this->content = $content;
		return $this;
	}

	/**
		@brief		Convenience function to add another CSS class to this element.
		@param		string		$css_class		A CSS class or classes to append to the element.
		@return		$this						Object chaining.
		@since		20130506
	**/
	public function css_class( $css_class )
	{
		$css_classes = func_get_args();
		foreach( $css_classes as $css_class )
			$this->append_attribute( 'class', $css_class );
		$this->attribute( 'class' )->separator( ' ' );
		return $this;
	}

	/**
		@brief		Convenience function to add another CSS style to this element.
		@param		string		$css_style		A CSS style string to append to this element.
		@return		$this						Object chaining.
		@since		20130506
	**/
	public function css_style( $css_style )
	{
		$css_styles = func_get_args();
		foreach( $css_styles as $css_style )
			$this->append_attribute( 'style', $css_style );
		$this->attribute( 'style' )->separator( '; ' );
		return $this;
	}

	/**
		@brief		Convenience method to set or get a data attribute.
		@since		2015-11-29 11:21:10
	**/
	public function data( $key, $value = null )
	{
		$key = 'data-' . $key;
		if ( $value === null )
			return $this->get_attribute( $key );
		else
			return $this->attribute( $key, $value );
	}

	/**
		@brief		Returns the value of an attribute.
		@param		string		$attribute			Type of attribute to retrieve.
		@return		mixed						Null, if the attribute does not exist, or the attribute value.
		@since		20130509
	**/
	public function get_attribute( $attribute )
	{
		if ( ! isset( $this->attributes[ $attribute ] ) )
			return null;
		return $this->attributes[ $attribute ]->value();
	}

	/**
		@brief		Return if an attribute is true.
		@param		string		$attribute		The name of the attribute to query.
		@return		bool		True, if the attribute is set to true.
		@since		20130524
	**/
	public function get_boolean_attribute( $attribute )
	{
		$value = $this->get_attribute( $attribute );
		return $value == true || $value == 'true';
	}

	/**
		@brief		Return the content.
		@return		string		The content.
		@since		20130703
	**/
	public function get_content()
	{
		return $this->content;
	}

	/**
		@brief		Return if an attribute is set.
		@param		string		$attribute		The name of the attribute to query.
		@return		bool		True, if the attribute is set to anything.
		@since		20130729
	**/
	public function has_attribute( $attribute )
	{
		return isset( $this->attributes[ $attribute ] );
	}

	/**
		@brief		Opens the tag of this object.
		@details	Will take care to include any attributes that have been set.
		@since		20130506
	**/
	public function open_tag()
	{
		$attributes = array();

		ksort( $this->attributes );
		foreach( $this->attributes as $key => $attribute )
		{
			$value = $attribute->value();
			$value = str_replace( '"', '\\"', $value );
			$attributes[] = sprintf( '%s="%s"', $key, $value );
		}

		if ( count( $attributes ) > 0 )
			$attributes = ' ' . implode( ' ', $attributes );
		else
			$attributes = '';

		$text = ( $this->self_closing() ? '<%s%s/>' : '<%s%s>' );

		return sprintf( $text, $this->tag, $attributes );
	}

	/**
		@brief		Is this element self-closing?
		@details	IMG and INPUT is self closing. DIV are not.
		@return		bool		True if the element closes itself and does not have any contents.
		@since		20130524
	**/
	public function self_closing()
	{
		if ( ! isset( $this->self_closing ) )
			return false;
		else
			return $this->self_closing;
	}

	/**
		@brief		Clears and resets an attribute with new text.
		@details	Usually getting the attribute and using its ->set() method is preferrable, but not when element chaining is required.

		Therefore this convenience method.

		@param		string		$type		Type of attribute.
		@param		string		$text		Attribute text to set.
		@return		$this					Object chaining.
		@since		20130506
	**/
	public function set_attribute( $type, $text )
	{
		// Convert boolean values to text.
		if ( is_bool( $text ) )
			$text = ( $text ? 'true' : 'false' );
		$this->attribute( $type )->set( $text );
		return $this;
	}

	/**
		@brief		Convenience function to force attribute to a boolean.
		@details	If the boolean is neither true nor false, use the default value. If the default is left as null, the attribute is removed.
		@param		string		$type		Type of attribute.
		@param		string		$boolean	Boolean value to set boolean.
		@param		string		$default	Default value is $boolean isn't really a boolean. If null the attribute is cleared.
		@return		$this					Object chaining.
		@since		20130516
	**/
	public function set_boolean_attribute( $type, $boolean, $default = null )
	{
		if ( ! is_bool( $boolean ) )
			$boolean = $default;
		if ( $boolean === true )
			return $this->set_attribute( $type, true );
		else
			return $this->clear_attribute( $type );
	}

	/**
		@brief		Convenience function to append a CSS style.
		@param		string		$style		The CSS style to append to the style attribute.
		@return		$this					Object chaining.
		@see		css_style
		@since		20130524
	**/
	public function style( $style )
	{
		return $this->css_style( $style );
	}
}

/**
	@brief		Global attributes common to all XHTML elements.
	@details	These are put separate for legibility.

	@see		http://www.w3.org/community/webed/wiki/HTML/Attributes/_Global
	@since		20130524
**/
trait attributes
{
	public function accesskey( $accesskey )
	{
		return $this->set_attribute( 'accesskey', $accesskey );
	}

	public function contextmenu( $contextmenu )
	{
		return $this->set_attribute( 'contextmenu', $contextmenu );
	}

	public function contenteditable( $contenteditable = true )
	{
		return $this->set_boolean_attribute( 'contenteditable', $contenteditable );
	}

	public function dir( $dir )
	{
		return $this->set_attribute( 'dir', $dir );
	}

	public function draggable( $draggable = true )
	{
		return $this->set_boolean_attribute( 'draggable', $draggable );
	}

	/**
		@brief		Return the name attribute of this element.
		@details	Convenience function.
		@return		string		The name attribute of this element.
		@since		20130524
	**/
	public function get_name()
	{
		return $this->get_attribute( 'name' );
	}

	public function hidden( $hidden = 'hidden' )
	{
		return $this->set_attribute( 'hidden', $hidden );
	}

	public function id( $id )
	{
		return $this->set_attribute( 'id', $id );
	}

	/**
		@brief		Queries whether this element has the "contenteditable" attribute set.
		@return		bool		True, if this element has the "contenteditable" attribute set.
		@since		20130524
	**/
	public function is_contenteditable()
	{
		return $this->get_boolean_attribute( 'contenteditable' );
	}

	/**
		@brief		Queries whether this element has the "draggable" attribute set.
		@return		bool		True, if this element has the "draggable" attribute set.
		@since		20130524
	**/
	public function is_draggable()
	{
		return $this->get_boolean_attribute( 'draggable' );
	}

	/**
		@brief		Queries whether this element has the "hidden" attribute set.
		@return		bool		True, if this element has the "hidden" attribute set.
		@since		20130524
	**/
	public function is_hidden()
	{
		return $this->get_attribute( 'hidden' ) == 'hidden';
	}

	/**
		@brief		Queries whether this element has the "required" attribute set.
		@return		bool		True, if this element has the "required" attribute set.
		@since		20130524
	**/
	public function is_required()
	{
		return $this->get_boolean_attribute( 'required' );
	}

	public function lang( $lang )
	{
		return $this->set_attribute( 'lang', $lang );
	}

	public function name( $name )
	{
		return $this->set_attribute( 'name', $name );
	}

	public function required( $required = true )
	{
		$this->set_boolean_attribute( 'aria-required', $required );
		return $this->set_boolean_attribute( 'required', $required );
	}

	public function spellcheck( $spellcheck = true )
	{
		return $this->set_boolean_attribute( 'spellcheck', $spellcheck );
	}

	public function tabindex( $tabindex )
	{
		return $this->set_attribute( 'tabindex', $tabindex );
	}

	public function title( $title )
	{
		return $this->set_attribute( 'title', $title );
	}

	public function translate( $translate = 'yes' )
	{
		return $this->set_attribute( 'translate', $translate );
	}
}
