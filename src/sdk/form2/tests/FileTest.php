<?php

namespace plainview\sdk_mcc\form2\tests;

/**
	@brief		Test the file input.
	@since		2016-06-21 16:22:57
**/
class FileTest extends TestCase
{
	/**
		@brief		Test the accept handling.
		@since		2016-06-21 16:21:41
	**/
	public function testAccept()
	{
		$form = $this->form();
		$file = $form->file( 'test_file' );

		// Set an accept for all images.
		$accept = 'image/*';
		$file->accept( $accept );
		$this->assertEquals( $accept, $file->get_attribute( 'accept' ) );

		// Clear the accept.
		$file->accept();
		$this->assertEquals( '', $file->get_attribute( 'accept' ) );
	}
}
