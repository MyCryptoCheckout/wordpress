<?php

namespace mycryptocheckout;

/**
	@brief		Security / hardening related functions.
	@since		2025-12-26 10:06:47
**/
trait security_trait
{
	/**
		@brief		Add the security related inputs to the settings form.
		@since		2025-12-26 10:07:37
	**/
	public function add_security_inputs_to_form( $fs )
	{
        $fs->checkbox( 'security_block_rogue_admins' )
            ->checked( $this->get_site_option( 'security_block_rogue_admins', true ) )
            ->description( __( 'TOTAL FREEZE: Blocks the creation of ANY new Administrators. Use this to prevent compromised accounts from creating backdoors. Uncheck this temporarily if you need to add a new admin.', 'mycryptocheckout' ) )
            ->label( __( 'Freeze Admin Creation', 'mycryptocheckout' ) );

        $fs->checkbox( 'security_disable_file_editor' )
            ->checked( $this->get_site_option( 'security_disable_file_editor', true ) )
            ->description( __( 'Disables the internal theme and plugin editors to prevent hackers from injecting code if they get into the dashboard.', 'mycryptocheckout' ) )
            ->label( __( 'Disable File Editor', 'mycryptocheckout' ) );

        $fs->checkbox( 'security_disable_xmlrpc' )
            ->checked( $this->get_site_option( 'security_disable_xmlrpc', true ) )
            ->description( __( 'Completely disables XML-RPC (xmlrpc.php) to block brute-force attacks and DDoS vectors.', 'mycryptocheckout' ) )
            ->label( __( 'Disable XML-RPC', 'mycryptocheckout' ) );
	}

	/**
		@brief		init_security_trait
		@since		2025-12-26 10:09:55
	**/
	public function init_security_trait()
	{
        // 1. DISABLE XML-RPC
        if ( $this->get_site_option( 'security_disable_xmlrpc', true ) ) {
        	ddd( 'disabling xmxmxm' );
            add_filter( 'xmlrpc_enabled', '__return_false' );
            remove_action( 'wp_head', 'rsd_link' );
            add_filter( 'wp_headers', function( $headers ) {
                unset( $headers['X-Pingback'] );
                return $headers;
            } );
        }

        // 2. DISABLE FILE EDITOR
        if ( $this->get_site_option( 'security_disable_file_editor', true ) ) {
            if ( ! defined( 'DISALLOW_FILE_EDIT' ) ) {
                define( 'DISALLOW_FILE_EDIT', true );
            }
        }

        // 3. FREEZE ADMIN CREATION (Rogue Admin Block)
        if ( $this->get_site_option( 'security_block_rogue_admins', true ) ) {
            // Block new registrations
            add_action( 'user_register', [ $this, 'security_prevent_admin_register' ], 10, 1 );
            // Block promotions
            add_action( 'set_user_role', [ $this, 'security_prevent_admin_promotion' ], 10, 3 );
        }
	}

	/**
		@brief		Save the security settings from the form.
		@since		2025-12-26 10:08:47
	**/
	public function save_security_inputs( $form )
	{
		$this->update_site_option( 'security_block_rogue_admins', $form->input( 'security_block_rogue_admins' )->is_checked() );
		$this->update_site_option( 'security_disable_file_editor', $form->input( 'security_disable_file_editor' )->is_checked() );
		$this->update_site_option( 'security_disable_xmlrpc', $form->input( 'security_disable_xmlrpc' )->is_checked() );
	}

	/**
     * @brief   Block NEW user registrations if they attempt to be an Admin.
     */
    public function security_prevent_admin_register( $user_id )
    {
        $user = get_userdata( $user_id );
        if ( in_array( 'administrator', (array) $user->roles ) ) {
            $this->security_kill_process( $user, 'Blocked new Administrator registration.' );
        }
    }

    /**
     * @brief   Block EXISTING users from being promoted to Admin.
     */
    public function security_prevent_admin_promotion( $user_id, $role, $old_roles )
    {
        $user = get_userdata( $user_id );
    	if ( $role === 'administrator' && ! in_array( 'administrator', $old_roles ) )
    		$this->security_kill_process( $user, 'Blocked promotion to Administrator.' );
    }

    /**
     * @brief   Helper to downgrade user, log security event, and stop execution.
     */
    private function security_kill_process( $user, $message )
    {
        // 1. Revert to Subscriber
        $user->set_role( 'subscriber' );

        // 2. Log to debug.log if enabled
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( "MCC SECURITY VIOLATION: $message User ID: " . $user->ID );
        }

        // 3. Kill the script
        wp_die(
            '<h1>Security Violation</h1>' .
            '<p>Administrator creation is currently <strong>FROZEN</strong> by MyCryptoCheckout security settings.</p>' .
            '<p>To add a new admin, please go to <em>Settings > MyCryptoCheckout</em> and uncheck "Freeze Admin Creation".</p>'
        );
    }
}
