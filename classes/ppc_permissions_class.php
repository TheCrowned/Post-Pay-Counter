<?php

/**
 * @author Stefano Ottolenghi
 * @copyright 2013
 */

include_once( 'ppc_general_functions_class.php' );

class PPC_permissions {

    /**
     * Checks whether the requested permission is available for the given user ID.
     *
     * @access   public
     * @since    2.0
     * @param    $permission string the permission to check
     * @param        $args function args: the user is to check the permission against
     * @return   bool whether user has permission
     */
    function __call( $permission, $args ) {
        global $current_user, $ppc_global_settings;

        if( isset( $args[0] ) )
            $user = $args[0];
        else
            $user = $current_user->ID;

        $settings = PPC_general_functions::get_settings( $user );

        //Admins override permissions, unless they have that permission turned off
        if( $settings['admins_override_permissions'] AND current_user_can( $ppc_global_settings['cap_manage_options'] ) ) {
            $user_only_settings = PPC_general_functions::get_settings( $user, false, false );
            if ( ! ( $user_only_settings['userid'] != 'general' AND isset( $user_only_settings[$permission] ) AND ! $user_only_settings[$permission] ) )  // if user does not have this specific permission turned off
                return true;
        }

        return (bool) $settings[$permission];
    }
}
