<?php

/**
 * @author Stefano Ottolenghi
 * @copyright 2013
 */

class PPC_update_class {
    
    /**
     * Walks through available blogs (maybe multisite) and calls the update procedure
     *
     * @access  public
     * @since   2.0.5
    */
    
    function update() {
        global $wpdb;
        
        if ( ! function_exists( 'is_plugin_active_for_network' ) )
            require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
        
		if( is_plugin_active_for_network( basename( dirname( dirname( __FILE__ ) ).'/post-pay-counter.php' ) ) ) {
			$blog_ids = $wpdb->get_col( "SELECT blog_id FROM ".$wpdb->blogs );
			
            foreach( $blog_ids as $blog_id ) {
				switch_to_blog( $blog_id );
				self::update_exec();
			}
            
			restore_current_blog();
			return;
		}
        
    	self::update_exec();
    }
    
    /**
     * Runs update procedure.
     * 
     * Also updates current version option and pages permissions.
     *
     * @access  public
     * @since   2.0.5
    */
    
    function update_exec() {
        global $ppc_global_settings;
        
        $general_settings = PPC_general_functions::get_settings( 'general' );
		
        switch( $ppc_global_settings['current_version'] ) {
            
            case "2.1":
            
        		/* 
        		 * Version 2.1.1 
        		 */
        		
        		//Fixed: installation added personalized user settings in place of general ones
        		if( $general_settings['userid'] != 'general' ) {
                    delete_option( $ppc_global_settings['option_name'] );
                    unset( $ppc_global_settings['general_settings'] );
                    
        			PPC_install_functions::ppc_install_procedure();
        		}
                
                break;
            
            case "2.1.1":
                
                /*
                 * Version 2.1.2
                 */
                 
                //Images & comments problems with counting systems - general and personalized settings update
                $general_settings['counting_images_system_incremental_value'] = $general_settings['counting_images_value'];
                $general_settings['counting_comments_system_incremental_value'] = $general_settings['counting_comments_value'];
                unset( $general_settings['counting_images_value'], $general_settings['counting_comments_value'] );
                if( ! update_option( $ppc_global_settings['option_name'], $general_settings ) ) {
                    return new WP_Error( 'ppc_update_general_settings_error', __( 'Error: could not update settings.', 'ppc' ) );
                }
                
                $args = array(
        			'meta_key' => $ppc_global_settings['option_name'],
                    'fields' => 'ids'
                );
        		$personalized_users = get_users( $args );
        		foreach( $personalized_users as $user ) {
        			$user_settings = PPC_general_functions::get_settings( $user ); 
        			
        			$user_settings['counting_images_system_incremental_value'] = $user_settings['counting_images_value'];
                    $user_settings['counting_comments_system_incremental_value'] = $user_settings['counting_comments_value'];
                    unset( $user_settings['counting_images_value'], $user_settings['counting_comments_value'] );
                    update_option( $ppc_global_settings['option_name'], $user_settings );
        			
        			if( ! update_user_option( $user, $ppc_global_settings['option_name'], $user_settings ) ) {
        				return new WP_Error( 'ppc_update_user_settings_error', __( 'Error: could not update settings.', 'ppc' ) );
        			}
        		}
                
                break;
        }
    		
		$general_settings = PPC_general_functions::get_settings( 'general' );
		
		PPC_general_functions::manage_cap_allowed_user_roles_plugin_pages( $general_settings['can_see_options_user_roles'], $general_settings['can_see_stats_user_roles'] );
		
        update_option( 'ppc_current_version', $ppc_global_settings['newest_version'] );
        
        //PRO gets deactivated as soon as PPC is deactivated - if it was active before, reactivate if now
        if( get_option( 'ppcp_active' ) == 1 ) {
            activate_plugin( 'post-pay-counter-pro/post-pay-counter-pro.php' );
        }
    }
}

?>