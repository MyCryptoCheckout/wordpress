<?php

namespace mycryptocheckout;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
	@brief		Class for handling expired license notifications and dismissals.
	@since		2019-11-15 21:09:24
**/
class Expired_License
{
	/**
		@brief		Add a notification for this ID.
		@since		2019-11-15 21:11:27
	**/
	public function add( $notification_id )
	{
		$dismissals = $this->dismissals();

		// Do we know about it already?
		if ( isset( $dismissals[ $notification_id ] ) )
			return;

		$dismissals[ $notification_id ] = false;
		$this->save_dismissals( $dismissals );
	}

	/**
		@brief		Dismiss a notification.
		@since		2019-11-15 21:10:51
	**/
	public function dismiss( $notification_id )
	{
	}

	/**
		@brief		Return an array of the dismissals.
		@since		2019-11-15 21:12:24
	**/
	public function dismissals()
	{
		return MyCryptoCheckout()->get_site_option( 'expired_license_nag_dismissals' );
	}

	/**
		@brief		Return the dismissal key for this notification ID.
		@since		2019-11-15 22:13:09
	**/
	public function get_dismissal_key( $notification_id )
	{
		$key = md5( AUTH_SALT . $notification_id . 'mcc_dismiss_notification' );
		$key = substr( $key, 0, 8 );
		return $key;
	}

	/**
		@brief		Check the URL for any dismissal actions.
		@since		2019-11-15 22:07:43
	**/
	public function maybe_dismiss()
	{
		$dismissals = $this->dismissals();
		$save = false;

		foreach( $dismissals as $dismissal => $dismissed )
		{
			if ( $dismissed )
				continue;
			$key = $this->get_dismissal_key( $dismissal );
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Custom verification using hashed key derived from AUTH_SALT.
			if ( ! isset( $_GET[ 'mcc_dismiss_notification' ] ) )
				continue;

			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Custom verification using hashed key.
			if ( sanitize_text_field( wp_unslash( $_GET[ 'mcc_dismiss_notification' ] ) ) != $key )
				continue;
			$dismissals[ $dismissal ] = time();
			$save = true;
		}

		if ( $save )
			$this->save_dismissals( $dismissals );
	}

	/**
		@brief		Save the dismissals option.
		@since		2019-11-15 21:15:02
	**/
	public function save_dismissals( $dismissals )
	{
		return MyCryptoCheckout()->update_site_option( 'expired_license_nag_dismissals', $dismissals );
	}

	/**
		@brief		Show all necessary expired license notifications to the admin.
		@since		2019-11-15 21:10:17
	**/
	public function show()
	{
		$this->maybe_dismiss();

		$dismissals = $this->dismissals();

		foreach( $dismissals as $dismissal => $dismissed )
		{
			if ( $dismissed )
				continue;
			$key = $this->get_dismissal_key( $dismissal );
			add_action( 'admin_notices', function() use ( $key )
			{
				$class = 'notice notice-warning';
				$message = sprintf( 'Your MyCryptoCheckout license has expired! If you wish to renew it, please visit your <a href="options-general.php?page=mycryptocheckout">account settings</a>. Or you can <a href="%s">dismiss this notice</a>.',
					esc_url( add_query_arg( 'mcc_dismiss_notification', $key ) )
				);

				// Translators: css class, 2 = message string
				printf( '<div class="%1$s"><p>%2$s</p></div>',
					esc_attr( $class ),
					wp_kses_post( $message )
				);
			} );
		}
	}
}
