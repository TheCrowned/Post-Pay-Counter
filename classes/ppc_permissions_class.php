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
	 * @param	 $args function args: the user is to check the permission against
	 * @return   bool whether user has permission
    */
	
    function __call( $permission, $args ) {
        global $current_user;
		
		if( isset( $args[0] ) )
			$user = $args[0];
		else
			$user = $current_user->ID;
        
        $settings = PPC_general_functions::get_settings( $user );
        
		//Admins override permissions, if they don't have any specific settings.
		if( $settings['admins_override_permissions'] AND $settings['userid'] == 'general' AND in_array( 'administrator', (array) $current_user->roles ) )
            return true;
        
        if( $settings[$permission] == 1 )
            return true;
        else
            return false;
    }
}
