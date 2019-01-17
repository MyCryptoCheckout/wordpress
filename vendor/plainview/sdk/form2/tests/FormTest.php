<?php

namespace plainview\sdk_mcc\form2\tests;

class FormTest extends TestCase
{
	/**
		@brief		Playing with the form attributes.
	**/
	public function testFormAttributes()
	{
		$url = 'http://plainview.se?parameter1=parameter2';
		$form = $this->form();
		$form->action( $url );
		$this->assertEquals( $url, $form->get_attribute( 'action' ) );

		$method = $form->get_attribute( 'method' );
		$this->assertEquals( $method, 'post' );
		$form->method( 'get' );
		$this->assertStringContains( 'get', $form->get_attribute( 'method' ) );

		$attribute = 'safe_text';
		$value = date( 'Y-m-d H:i:s' );
		$form->set_attribute( $attribute, $value );
		$this->assertEquals( $form->get_attribute( $attribute ), $value );

		$attribute = 'unsafe_text';
		$value = '<h1>unsafe</h2> & text';
		$form->set_attribute( $attribute, $value );
		$this->assertEquals( $form->get_attribute( $attribute ), $value );
		$this->assertStringContains( 'unsafe_text="', $form->open_tag() );
	}

	/**
		Tests that open and close tags work correctly.
	**/
	public function testTags()
	{
		$this->assertStringStartsWith( '<form', $this->form()->open_tag() );
		$this->assertStringEndsWith( 'form>', $this->form()->close_tag() );
	}
}
