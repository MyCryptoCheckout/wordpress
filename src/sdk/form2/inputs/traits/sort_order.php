<?php

namespace plainview\sdk_mcc\form2\inputs\traits;

/**
	@brief		Apply sorting methods. The inputs are sorted by order and then label.
	@since		2015-12-25 16:04:02
**/
trait sort_order
{
	use \plainview\sdk_mcc\traits\sort_order;

	/**
		@brief		Method to sort the inputs, if possible.
		@details	Only container inputs can have their inputs sorted.
		@since		2015-12-25 16:05:47
	**/
	public function sort_inputs()
	{
		if ( ! isset( $this->inputs ) )
			return;

		uasort( $this->inputs, function( $a, $b )
		{
			$label = ( isset( $a->label ) ? $a->label->content : $a->get_attribute( 'name' ) );
			$a = $a->get_sort_order() . ' ' . $label;

			$label = ( isset( $b->label ) ? $b->label->content : $b->get_attribute( 'name' ) );
			$b = $b->get_sort_order() . ' ' . $label;

			return strcmp( $a, $b );
		} );
	}

}
