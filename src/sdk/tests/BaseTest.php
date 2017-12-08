<?php

use \plainview\sdk_mcc\base;

class BaseTest
	extends \plainview\sdk_mcc\tests\TestCase
{
	/**
		@brief		Test the current_url function with a whole combination of weird settings.
		@since		2014-01-14 16:04:40
	**/
	public function test_current_url()
	{
		// Test a normal URL.
		$SERVER = [
			'HTTP_HOST' => 'subdomain.domain.com',
			'REQUEST_URI' => '/index.php',
			'SERVER_PORT' => 80,
		];
		$result = \plainview\sdk_mcc\base::current_url( $SERVER );
		$this->assertEquals( $result, 'http://subdomain.domain.com/index.php' );

		// With a different port.
		$SERVER [ 'SERVER_PORT' ] = 82;
		$result = \plainview\sdk_mcc\base::current_url( $SERVER );
		$this->assertEquals( $result, 'http://subdomain.domain.com:82/index.php' );

		// HTTPS
		$SERVER [ 'SERVER_PORT' ] = 443;
		$SERVER [ 'HTTPS' ] = 'on';
		$result = \plainview\sdk_mcc\base::current_url( $SERVER );
		$this->assertEquals( $result, 'https://subdomain.domain.com/index.php' );

		// HTTPS on a weird port.
		$SERVER [ 'SERVER_PORT' ] = 444;
		$result = \plainview\sdk_mcc\base::current_url( $SERVER );
		$this->assertEquals( $result, 'https://subdomain.domain.com:444/index.php' );

		// Normal HTTP with HTTPS set to off (thanks Microsoft IIS!)
		$SERVER [ 'SERVER_PORT' ] = 80;
		$SERVER [ 'HTTPS' ] = 'off';
		$result = \plainview\sdk_mcc\base::current_url( $SERVER );
		$this->assertEquals( $result, 'http://subdomain.domain.com/index.php' );

		// HTTPS on port 80
		$SERVER [ 'HTTPS' ] = 'on';
		$result = \plainview\sdk_mcc\base::current_url( $SERVER );
		$this->assertEquals( $result, 'https://subdomain.domain.com:80/index.php' );
	}
}
