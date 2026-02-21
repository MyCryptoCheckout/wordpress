<?php

namespace mycryptocheckout;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
    @brief      Security / hardening related functions.
    @since      2025-12-26 10:06:47
**/
trait security_trait
{
    /**
        @brief      Add the security related inputs to the settings form.
        @since      2025-12-26 10:07:37
    **/
    public function add_security_inputs_to_form( $fs )
    {
        $fs->checkbox( 'security_block_rogue_admins' )
            ->checked( $this->get_site_option( 'security_block_rogue_admins' ) )
            ->description( __( 'TOTAL FREEZE: Blocks the creation of ANY new administrators. You can still see the option in menus, but saving will fail. Uncheck this temporarily if you need to add a new admin. Recommended: ON', 'mycryptocheckout' ) )
            ->label( __( 'Freeze Admin Creation', 'mycryptocheckout' ) );

        $fs->checkbox( 'security_disable_application_passwords' )
            ->checked( $this->get_site_option( 'security_disable_application_passwords' ) )
            ->description( __( 'Disables application passwords, preventing hackers from accessing admin functions externally. Recommended: ON', 'mycryptocheckout' ) )
            ->label( __( 'Disable Application Passwords', 'mycryptocheckout' ) );

        $fs->checkbox( 'security_disable_file_editor' )
            ->checked( $this->get_site_option( 'security_disable_file_editor' ) )
            ->description( __( 'Disables the internal theme and plugin editors to prevent hackers from injecting code if they get into the dashboard. Recommended: ON', 'mycryptocheckout' ) )
            ->label( __( 'Disable File Editor', 'mycryptocheckout' ) );

        $fs->checkbox( 'security_disable_xmlrpc' )
            ->checked( $this->get_site_option( 'security_disable_xmlrpc' ) )
            ->description( __( 'Completely disables XML-RPC (xmlrpc.php) to block brute-force attacks and DDoS vectors. Recommended: ON', 'mycryptocheckout' ) )
            ->label( __( 'Disable XML-RPC', 'mycryptocheckout' ) );
    }

    /**
        @brief      init_security_trait
        @since      2025-12-26 10:09:55
    **/
    public function init_security_trait()
    {
        // DISABLE XML-RPC.
        if ( $this->get_site_option( 'security_disable_xmlrpc' ) ) {
            add_filter( 'xmlrpc_enabled', '__return_false' );
            remove_action( 'wp_head', 'rsd_link' );
            add_filter( 'wp_headers', function( $headers ) {
                unset( $headers['X-Pingback'] );
                return $headers;
            } );
        }

        // DISABLE APPLICATION PASSWORDS.
        if ( $this->get_site_option( 'security_disable_application_passwords' ) ) {
			add_filter( 'wp_is_application_passwords_available', '__return_false' );
        }

        // DISABLE FILE EDITOR.
        if ( $this->get_site_option( 'security_disable_file_editor' ) ) {
            if ( ! defined( 'DISALLOW_FILE_EDIT' ) ) {
                // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound -- Defining core constant for security option.
                define( 'DISALLOW_FILE_EDIT', true );
            }
        }

        // FREEZE ADMIN CREATION.
        if ( $this->get_site_option( 'security_block_rogue_admins', true ) ) {

            // LAYER 1: UI Validation (Nice User Experience)
            add_action( 'user_profile_update_errors', [ $this, 'security_validate_profile_update' ], 10, 3 );

            // LAYER 2: New User Registration Block
            add_action( 'user_register', [ $this, 'security_prevent_admin_register' ], 10, 1 );

            // LAYER 3: NUCLEAR INTERCEPTOR (The Best Way)
            // Using 'filters' allows us to stop the DB write BEFORE it happens.
            // Priority 1 ensures we run before other plugins.
            add_filter( 'update_user_metadata', [ $this, 'security_intercept_meta_update' ], 1, 5 );
            add_filter( 'add_user_metadata', [ $this, 'security_intercept_meta_add' ], 1, 5 );
        }

        // WALLET CHANGE EMAIL.
        add_action( 'updated_option', [ $this, 'mcc_security_monitor_wallets' ], 999, 1 );
    }

    /**
        @brief      Save the security settings from the form.
        @since      2025-12-26 10:08:47
    **/
    public function save_security_inputs( $form )
    {
        $this->update_site_option( 'security_block_rogue_admins', $form->input( 'security_block_rogue_admins' )->is_checked() );
        $this->update_site_option( 'security_disable_application_passwords', $form->input( 'security_disable_application_passwords' )->is_checked() );
        $this->update_site_option( 'security_disable_file_editor', $form->input( 'security_disable_file_editor' )->is_checked() );
        $this->update_site_option( 'security_disable_xmlrpc', $form->input( 'security_disable_xmlrpc' )->is_checked() );
    }

    /**
     * @brief   LAYER 1: UI Validation.
     */
    public function security_validate_profile_update( &$errors, $update, &$user )
    {
        if ( isset( $_POST['role'] ) && $_POST['role'] === 'administrator' ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Hooked into user_profile_update_errors; WP Core verifies nonce.
            $current_roles = isset( $user->roles ) ? (array) $user->roles : [];
            if ( ! in_array( 'administrator', $current_roles ) ) {
                $errors->add( 'mcc_security_error', __( 'SECURITY BLOCK: Administrator creation is currently FROZEN by MyCryptoCheckout settings.', 'mycryptocheckout' ) );
            }
        }
    }

    /**
     * @brief   LAYER 2: Block NEW user registrations if they attempt to be an Admin.
     */
    public function security_prevent_admin_register( $user_id )
    {
        $user = get_userdata( $user_id );
        if ( ! $user ) return;

        if ( in_array( 'administrator', (array) $user->roles ) ) {
            $this->security_kill_process( $user, 'Blocked new Administrator registration.' );
        }
    }

    /**
     * @brief   LAYER 3: Intercept UPDATE requests.
     */
    public function security_intercept_meta_update( $check, $object_id, $meta_key, $meta_value, $prev_value )
    {
        // If another plugin already blocked it, respect that.
        if ( $check !== null ) return $check;

        $this->security_analyze_meta( $object_id, $meta_key, $meta_value, 'update' );

        return null; // Return null to allow WordPress to proceed if we didn't die.
    }

    /**
     * @brief   LAYER 3: Intercept ADD requests.
     */
    public function security_intercept_meta_add( $check, $object_id, $meta_key, $meta_value, $unique )
    {
        if ( $check !== null ) return $check;

        $this->security_analyze_meta( $object_id, $meta_key, $meta_value, 'add' );

        return null;
    }

    /**
     * @brief   Core logic to detect capability injection.
     */
    private function security_analyze_meta( $user_id, $key, $value, $context )
    {
        // 1. Broad Check: Does the key contain "capabilities"?
        if ( strpos( $key, 'capabilities' ) === false ) {
            return;
        }

        // 2. Nuclear Detection: Serialize the value and check for the string "administrator".
        $serialized_check = is_string( $value ) ? $value : serialize( $value );

        if ( strpos( $serialized_check, 'administrator' ) !== false ) {

            // Force cache clear to ensure we are checking the REAL current user state
            clean_user_cache( $user_id );
            $user = get_userdata( $user_id );

            // Allow existing admins to modify themselves, but BLOCK new admins.
            $is_already_admin = ( $user && in_array( 'administrator', (array) $user->roles ) );

            if ( ! $is_already_admin ) {
                 // Create temp object for logging if user is null (new registration)
                 $user_obj = $user ?: (object) [ 'ID' => $user_id ];
                 $this->security_kill_process( $user_obj, "Blocked illegal capability injection ($context)." );
            }
        }
    }

    /**
     * @brief   Helper to log security event and stop execution.
     */
    private function security_kill_process( $user, $message )
    {
        // Log to debug.log if enabled
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( "MCC SECURITY VIOLATION: $message User ID: " . ( isset($user->ID) ? $user->ID : 'Unknown' ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Logging security violations is intended behavior.
        }

        // Kill the script immediately.
        // Note: We do NOT need to revert the user here because we used a Filter Interceptor.
        // The malicious data was never written to the DB.
        wp_die(
            '<h1>Action Blocked: Security Lockdown</h1>' .
            '<p>The creation of new administrator accounts is currently <strong>disabled</strong> by your MyCryptoCheckout security settings.</p>' .
            '<p>To authorize this action, please go to <em>Settings > MyCryptoCheckout</em> and temporarily uncheck <strong>"Freeze Admin Creation"</strong>.</p>',
            'Security Violation',
            [ 'response' => 403 ]
        );
    }

    /**
     * Monitor Wallet Changes and Email Admin.
     *
     * @param string $option Option name.
     * @since 2026-01-03
     */
    public function mcc_security_monitor_wallets( $option ) {

       // Option Name Check.
        if ( 'MyCryptoCheckout_wallets' !== $option && 'mycryptocheckout_wallets' !== $option ) {
            return;
        }

        // Context Check: Is this a manual admin save.
        // We ensure we are in the admin area, on the specific settings page.
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- We are reading global state to determine context, not processing user input for a DB write.
        $is_mcc_page = ( is_admin() && isset( $_GET['page'] ) && 'mycryptocheckout' === $_GET['page'] );

        if ( ! $is_mcc_page ) {
            return; // Ignore automated background updates.
        }

        // Input Field Check.
        // We scan raw POST data to see if wallet inputs were actually submitted.
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- We are purely detecting presence of keys for a notification trigger, not processing data.
        $post_data_string = isset( $_POST ) ? wp_json_encode( $_POST ) : '';

        // Check for specific input names used by the settings form.
        $has_wallet_input = ( strpos( $post_data_string, 'wallet_address' ) !== false );
        $has_hd_input     = ( strpos( $post_data_string, 'btc_hd_public_key' ) !== false );

        if ( ! $has_wallet_input && ! $has_hd_input ) {
            return;
        }

        // Rate Limiting (Prevent double-send on a single save).
        $transient_key = 'mcc_security_alert_' . md5( $option );
        if ( get_transient( $transient_key ) ) {
            return;
        }
        set_transient( $transient_key, true, 300 );

        // Send Notification Email.
        $to      = get_option( 'admin_email' );
        /* translators: %s: The site name. */
        $subject = sprintf( __( '[%s] Wallet Settings Updated', 'mycryptocheckout' ), get_bloginfo( 'name' ) );
        $headers = [ 'Content-Type: text/plain; charset=UTF-8' ];

        $current_user = wp_get_current_user();
        $user_login   = $current_user->exists() ? sanitize_user( $current_user->user_login ) : 'Unknown/System';
        $timestamp    = current_time( 'mysql' );

        $raw_ip  = filter_input( INPUT_SERVER, 'REMOTE_ADDR', FILTER_VALIDATE_IP );
        $user_ip = $raw_ip ? $raw_ip : 'Unknown/Hidden';

        /* translators: 1: The timestamp, 2: The username, 3: The user IP address. */
        $message = sprintf(
            __( "Just a quick notification that the MyCryptoCheckout wallet addresses were manually updated via the WordPress Dashboard.\n\nTime: %1\$s\nUser: %2\$s\nIP:   %3\$s\n\n", 'mycryptocheckout' ),
            $timestamp,
            $user_login,
            $user_ip
        );

        $message .= __( "If you made this change, you can safely ignore this email.\n", 'mycryptocheckout' );
        $message .= __( "If you do not recognize this activity, please verify your wallet addresses immediately:\n", 'mycryptocheckout' );
        $message .= admin_url( 'options-general.php?page=mycryptocheckout&tab=currencies' );

        wp_mail( $to, $subject, $message, $headers );
    }
}
