HTML5/XHTML form manipulation class.

Provides form generation, manipulation, _POST handling and validation.

All the form related files are documented, but here is a small taste of what the form class can do.

## Examples

### Example 1: Create and display a form

	$form = new \\plainview\\sdk_mcc\\form2\\form();
	// Add a text input
	$form->text( 'username' )
		->label( 'Your username' );
	// And display the form. Start() opens the form tag and stop()...
	echo $form->start() . $form . $form->stop();

### Example 2: Handle the _POST

	// Is there anything in the _POST array?
	if ( $form->is_posting() )
	{
		// Ask the form to retrieve the form values.
		$form->post();
		if ( $form->input( 'login' )->pressed() )
			echo "The login button was pressed!";
	}

### Example 3: Form validation.

	// Make the username input require at least 12 characters.
	$form->text( 'username' )
		->minlength( 12 )
		->required();

	if ( $form->is_posting() )
	{
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
	}
