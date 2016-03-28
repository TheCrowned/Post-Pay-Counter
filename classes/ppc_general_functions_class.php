<?php

/**
 * @author Stefano Ottolenghi
 * @copyright 2013
 * @package	PPC
 */

require_once( 'ppc_permissions_class.php' );

class PPC_general_functions {
    
    /**
     * Gets the cumulative settings for the requested user. Takes care to integrate general with current user/author ones.
     * 
     * IF GENERAL SETTINGS ARE REQUESTED: if class var has settings, return that, otherwise get_site_option (so if network ones are available, those are got, otherwise blog-specific) - THIS is to 
     * make sure that we have some settings to base our further checks on. THEN check whether network settings should be got or not.
     * IF USER SETTINGS ARE REQUESTED: if a valid user + a user who has specific settings + (current_user can see spcial settings) is requested, get its user_meta. Store user settings in global var as caching.
     * IF NOTHING OF THE PREVIOUS IS MATCHED: return general settings.  
     *
     * @access  public
     * @since   2.0
     * @param   int the desired user id
	 * @param 	bool whether can-see-other-users-personalized-settings permission should be checked
	 * @param	bool whether user specific settings should be completed with general ones for options which are not cusomized for that user
     * @return  array the requested settings
    */

    static function get_settings( $userid, $check_current_user_cap_special = FALSE, $complete_with_general = TRUE ) {
        global $ppc_global_settings;
        
		//GENERAL SETTINGS
        if( $userid == 'general' ) {
			
			//Retrieve from cache if available
			$cache = wp_cache_get( 'ppc_settings'.$userid );
            if( $cache !== false ) {
                $return = $cache;
            } else {
                /* MULTISITE stuff
				$temp_settings = get_site_option( $ppc_global_settings['general_options_name'] );
                if( ! $temp_settings ) {
                    $temp_settings = get_option( $ppc_global_settings['general_options_name'] );
                }
            
                if( $temp_settings['multisite_settings_rule'] == 1 ) {
                    $general_settings = get_site_option( $ppc_global_settings['general_options_name'] );;
                } else {*/
                    /*$general_settings = array();
                    foreach( $general_settings_options as $single ) {
                        $general_settings = array_merge( $general_settings, get_option( $single ) );
                    }*/
				
				//Fetch them from database if first request 
				$return = get_option( $ppc_global_settings['option_name'] );
				
                //}
            }
        
		//If a valid userid is given
        } else if( (int) $userid != 0 ) {
			global $current_user;
            $perm = new PPC_permissions();
			
			//If user shouldn't see other users personalized settings, set the userid to their own
            if( $check_current_user_cap_special == TRUE AND $current_user->ID != $userid AND ( ! $perm->can_see_countings_special_settings() ) )
                $userid = $current_user->ID;
			
			//Retrieve cached settings if available or from database if not
			$cache = wp_cache_get( 'ppc_settings_'.$userid );
            if( $cache !== false ) {
                $user_settings = $cache;
            } else {
				$user_settings = get_user_option( $ppc_global_settings['option_name'], $userid );
				
				//If no special settings for this user are available, get general ones
                if( $user_settings == false ) {
                    $user_settings = self::get_settings( 'general' );
                
				//If user has special settings, complete user settings with general ones if needed (i.e. add only-general settings to the return array of special user's settings)
				} else if( $complete_with_general ) {
					$general_settings = self::get_settings( 'general' );
					$user_settings = array_merge( $general_settings, $user_settings );
					/*foreach( $general_settings as $key => &$value ) {
						if( isset( $user_settings[$key] ) ) {
							$general_settings[$key] = $user_settings[$key];
						}
					}
					$user_settings = $general_settings;*/
				}
			}
			
			$return = $user_settings;
			
        } else {
            $return = self::get_settings( 'general' );
        }
        
        /**
		 * Filters retrieved settings before returning them.
		 *
		 * @since	2.518
		 * @param	$return array to be returned settings array
		 * @param	$userid string user id whose settings are being requested
		 * @param 	bool whether can-see-other-users-personalized-settings permission should be checked
		 * @param	bool whether user specific settings should be completed with general ones for options which are not cusomized for that user
		 */

        $return = apply_filters( 'ppc_settings', $return );
        $return = apply_filters( 'ppc_get_settings', $return, $userid, $check_current_user_cap_special, $complete_with_general );

        //Cache processed settings
		wp_cache_set( 'ppc_settings_'.$userid, $return );

		return $return;
    }
    
    /**
     * Gets non capitalized input.
     * 
     * Grants compatibility with PHP < 5.3.
     *
     * @access  public
     * @since   2.0.9
     * @param   $string string to lowercase
     * @return  string lowercased
    */
	
	static function lcfirst( $string ) {
        if( function_exists( 'lcfirst' ) )
            return lcfirst( $string );
        else
            return (string) ( strtolower( substr( $string, 0, 1 ) ).substr( $string, 1 ) );
    }
    
    /**
     * Gets the link to the stats page of the requested author with the proper start and end time
     *
     * @access  public
     * @since   2.0
     * @param   $author_id int the author id
     * @return  string the link to their stats
    */
    
    static function get_the_author_link( $author_id ) {
        global $ppc_global_settings;
        
        return apply_filters( 'ppc_get_author_link', admin_url( $ppc_global_settings['stats_menu_link'].'&amp;author='.$author_id.'&amp;tstart='.$ppc_global_settings['stats_tstart'].'&amp;tend='.$ppc_global_settings['stats_tend'] ) );
    }
    
    /**
     * Makes sure each user role has or has not the requested capability to see options and stats pages. 
     * 
     * Called when updating settings and updating/installing.
     *
     * @access  public
     * @since   2.0.4
     * @param   $allowed_user_roles_options_page array user roles allowed to see plugin options
     * @param   $allowed_user_roles_stats_page array user roles allowed to see plugin stats
    */
    
    static function manage_cap_allowed_user_roles_plugin_pages( $allowed_user_roles_options_page, $allowed_user_roles_stats_page ) {
        global $wp_roles, $ppc_global_settings;
        
        if ( ! isset( $wp_roles ) )
            $wp_roles = new WP_Roles();
		
        $wp_roles_to_use = array();
        foreach( $wp_roles->role_names as $key => $value ) {
            $wp_roles_to_use[] = $key;
        }
        
        $allowed_user_roles_stats_page_add_cap       = array_intersect( $allowed_user_roles_stats_page, $wp_roles_to_use );
        $allowed_user_roles_stats_page_remove_cap    = array_diff( $wp_roles_to_use, $allowed_user_roles_stats_page );
        $allowed_user_roles_options_page_add_cap     = array_intersect( $allowed_user_roles_options_page, $wp_roles_to_use );
        $allowed_user_roles_options_page_remove_cap  = array_diff( $wp_roles_to_use, $allowed_user_roles_options_page );
        
        foreach( $allowed_user_roles_options_page_add_cap as $single ) {
            $current_role = get_role( self::lcfirst( $single ) );
            
            if( is_object( $current_role ) AND ! $current_role->has_cap( $ppc_global_settings['cap_manage_options'] ) )
                $current_role->add_cap( $ppc_global_settings['cap_manage_options'] );
        }
        
        foreach( $allowed_user_roles_options_page_remove_cap as $single ) {
            $current_role = get_role( self::lcfirst( $single ) );
            
            if( is_object( $current_role ) AND $current_role->has_cap( $ppc_global_settings['cap_manage_options'] ) )
                $current_role->remove_cap( $ppc_global_settings['cap_manage_options'] );
        }
        
        foreach( $allowed_user_roles_stats_page_add_cap as $single ) {
            $current_role = get_role( self::lcfirst( $single ) );
            
            if( is_object( $current_role ) AND ! $current_role->has_cap( $ppc_global_settings['cap_access_stats'] ) )
                $current_role->add_cap( $ppc_global_settings['cap_access_stats'] );
        }
        
        foreach( $allowed_user_roles_stats_page_remove_cap as $single ) {
            $current_role = get_role( self::lcfirst( $single ) );
            
            if( is_object( $current_role ) AND $current_role->has_cap( $ppc_global_settings['cap_access_stats'] ) )
                $current_role->remove_cap( $ppc_global_settings['cap_access_stats'] );
        }
    }
    
    /**
     * Defines default stats time range depending on chosen settings.
     * 
     * Stores settings in plugin's global var.
     *
     * @access  public
     * @since   2.1
     * @param   $settings array plugin settings
    */
    
    static function get_default_stats_time_range( $settings ) {
        global $ppc_global_settings;
        
        if( $settings['default_stats_time_range_week'] == 1 )
            $ppc_global_settings['stats_tstart'] = strtotime( '00:00:00' ) - ( ( date( 'N' )-1 )*24*60*60 );
        else if( $settings['default_stats_time_range_month'] == 1 )
            $ppc_global_settings['stats_tstart'] = strtotime( '00:00:00' ) - ( ( date( 'j' )-1 )*24*60*60 );
        else if( $settings['default_stats_time_range_custom'] == 1 )
            $ppc_global_settings['stats_tstart'] = strtotime( '00:00:00' ) - ( $settings['default_stats_time_range_custom_value']*24*60*60 );
        
        $ppc_global_settings['stats_tend'] = ( strtotime( '23:59:59' ) ); 
    }
	
	/**
	 * Formats payments for output.
	 *
	 * @access	public
	 * @since	2.40
	 * @param	$payment string payment to be formatted
	 * @return 	string formatted payment
	 */
	
	static function format_payment( $payment ) {
		return apply_filters( 'ppc_format_payment', $payment );
	}
}
?>
