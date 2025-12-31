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
            ->checked( $this->get_site_option( 'security_block_rogue_admins', true ) )
            ->description( __( 'TOTAL FREEZE: Blocks the creation of ANY new administrators. You can still see the option in menus, but saving will fail. Uncheck this temporarily if you need to add a new admin. Recommended: ON', 'mycryptocheckout' ) )
            ->label( __( 'Freeze Admin Creation', 'mycryptocheckout' ) );

        $fs->checkbox( 'security_disable_file_editor' )
            ->checked( $this->get_site_option( 'security_disable_file_editor', true ) )
            ->description( __( 'Disables the internal theme and plugin editors to prevent hackers from injecting code if they get into the dashboard. Recommended: ON', 'mycryptocheckout' ) )
            ->label( __( 'Disable File Editor', 'mycryptocheckout' ) );

        $fs->checkbox( 'security_disable_xmlrpc' )
            ->checked( $this->get_site_option( 'security_disable_xmlrpc', true ) )
            ->description( __( 'Completely disables XML-RPC (xmlrpc.php) to block brute-force attacks and DDoS vectors. Recommended: ON', 'mycryptocheckout' ) )
            ->label( __( 'Disable XML-RPC', 'mycryptocheckout' ) );
    }

    /**
        @brief      init_security_trait
        @since      2025-12-26 10:09:55
    **/
    public function init_security_trait()
    {
        // 1. DISABLE XML-RPC
        if ( $this->get_site_option( 'security_disable_xmlrpc', false ) ) {
            add_filter( 'xmlrpc_enabled', '__return_false' );
            remove_action( 'wp_head', 'rsd_link' );
            add_filter( 'wp_headers', function( $headers ) {
                unset( $headers['X-Pingback'] );
                return $headers;
            } );
        }

        // 2. DISABLE FILE EDITOR
        if ( $this->get_site_option( 'security_disable_file_editor', false ) ) {
            if ( ! defined( 'DISALLOW_FILE_EDIT' ) ) {
                define( 'DISALLOW_FILE_EDIT', true );
            }
        }

        // 3. FREEZE ADMIN CREATION (Rogue Admin Block)
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
    }

    /**
        @brief      Save the security settings from the form.
        @since      2025-12-26 10:08:47
    **/
    public function save_security_inputs( $form )
    {
        $this->update_site_option( 'security_block_rogue_admins', $form->input( 'security_block_rogue_admins' )->is_checked() );
        $this->update_site_option( 'security_disable_file_editor', $form->input( 'security_disable_file_editor' )->is_checked() );
        $this->update_site_option( 'security_disable_xmlrpc', $form->input( 'security_disable_xmlrpc' )->is_checked() );
    }

    /**
     * @brief   LAYER 1: UI Validation.
     */
    public function security_validate_profile_update( &$errors, $update, &$user )
    {
        if ( isset( $_POST['role'] ) && $_POST['role'] === 'administrator' ) {
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
            error_log( "MCC SECURITY VIOLATION: $message User ID: " . ( isset($user->ID) ? $user->ID : 'Unknown' ) );
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
}