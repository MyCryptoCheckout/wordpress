<?php

/**
	Function to help debug: outputs var_dumps of all arguments.
**/

if ( ! function_exists( 'ddd' ) )
{
	function ddd()
	{
		// Convert the non-string arguments into lovely code blocks.
		$args = func_get_args();
		foreach( $args as $index => $arg )
		{
			$export = false;
			$export |= is_bool( $arg );
			$export |= is_array( $arg );
			$export |= is_object( $arg );
			if ( $export )
				$args[ $index ] = sprintf( '%s', var_dump( $arg, true ) );
		}

		$text = $args[ 0 ];
		if ( strpos( $text, '%' ) !== false )
		{
			// Put all of the arguments into one string.
			$text = @call_user_func_array( 'sprintf', $args );
			if ( $text == '' )
				$text = $args[ 0 ];
		}
		else
			$text = implode( "<br/>\n", $args );

		// Date class: string
		$text = sprintf( '<pre>%s %s%s</pre>', date( 'H:i:s' ), $text, "\n" );
		echo $text;
		ob_flush();
	}
}

error_reporting( E_ALL | E_STRICT );

require_once( __DIR__ . '/../vendor/autoload.php' );
