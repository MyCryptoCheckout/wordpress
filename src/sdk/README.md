Plainview SDK
=============

A toolkit of commonly used classes and functions, including Wordpress and Drupal SDKs.

The SDK contains:

* Base set of useful functions
* Drupal SDK - currently only a Drupalized db_aware_object class
* Form2 manipulation class for HTML5
* Pagination class
* Table manipulation class
* Wordpress SDK - fully fledged SDK
* XHTML element class
* Unit tests

Base
----

* base.php is a base class of [mostly] static functions
* traits\db_aware_object is a trait that your objects can use to update themselves in the database
* form.php is an obsolete form handling class. Use form2 instead.
* mail\mail.php is a wrapper for PHPMailer

Requirements
------------

* PHP v5.4 for traits support.

Standalone usage
----------------

Copy the files into a directory called plainview.

Require the autoloader. Assuming you have the SDK in a subdirectory called "plainview" (it must be called plainview but can be placed anywhere):

	require_once( 'plainview/autoload.php' );

After that the classes are loaded automatically. The SDK's function can now be accessed statically:

	if ( \plainview\sdk_mcc\base::is_email( 'test@test.com' ) )
		echo 'Valid e-mail address!';

Or by dynamically instantiating the base:

	class sdk_test extends \plainview\sdk_mcc\base
	{
	}

	$test = new sdk_test();
	if ( $test->is_email( 'test@test.com' ) )
		echo 'Valid e-mail address!';

	$instance = sdk_test::instance();


License
-------

GPL v3

Contact
-------

The author can be contacted at: edward@plainview.se
