<?php

namespace plainview\sdk_mcc\wordpress\form2\inputs;

/**
	@brief		A textarea that uses the Wordpress tinymce'd textarea.
	@since		2015-11-05 14:24:52
**/
class wp_editor
	extends \plainview\sdk_mcc\form2\inputs\textarea
{
	/**
		@brief		The default options for the wp editor.
		@see		http://codex.wordpress.org/Function_Reference/wp_editor
		@since		2015-11-05 14:30:29
	**/
	public $wp_editor_options = [
	];

	/**
		@brief		The input itself must be overridden.
		@since		2015-11-05 14:25:33
	**/
	public function display_input()
	{
		// Assign the name to the options.
		$options = array_merge( [
			'textarea_name' => $this->get_name(),
		], $this->wp_editor_options );

		ob_start();

		wp_editor( $this->get_value(), $this->get_id(), $options );

		$r = ob_get_contents();
		ob_end_clean();
		return $r;
	}

	/**
		@brief		Set an editor option.
		@since		2015-11-05 14:33:21
	**/
	public function set_option( $key, $value )
	{
		$this->wp_editor_options[ $key ] = $value;
		return $this;
	}
}
