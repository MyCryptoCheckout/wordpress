<?php

namespace mycryptocheckout\api\v2;

/**
	@brief		Autosettlement handling.
	@since		2019-03-06 23:33:13
**/
class Autosettlements
	extends Component
{
	/**
		@brief		Send a new payment to the server.
		@since		2017-12-21 23:28:43
	**/
	public function test( Autosettlement $autosettlement )
	{
		$json = $this->api()->send_post_with_account( 'autosettlement/test', [ 'autosettlement' => $autosettlement ] );
		if ( ! isset( $json->result ) )
			throw new Exception( 'Invalid JSON from API.' );
		if ( $json->result !== 'ok' )
			throw new Exception( $json->message );
		return $json->message;
	}
}
