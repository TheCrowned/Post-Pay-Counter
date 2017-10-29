<?php

require_once( 'ppc_save_options_class.php' );

/**
 * AJAX functions handler.
 * 
 * @package		PPC
 * @since 		2.0
 * @author 		Stefano Ottolenghi
 * @copyright 	2013
 */

class PPC_ajax_functions {
    
    /**
     * Checks whether the AJAX request is legitimate, if not displays an error that the requesting JS will display.
     *
     * @access  public
     * @since   2.0
     * @param   $nonce string the WP nonce  
    */
    
    static function ppc_check_ajax_referer( $nonce ) {
        if( ! check_ajax_referer( $nonce, false, false ) )
            die( __( 'Error: Seems like AJAX request was not recognised as coming from the right page. Maybe hacking around..?' , 'post-pay-counter') );
    }
    
    /**
     * Handles the AJAX request for the counting settings saving.
     *
     * @access  public
     * @since   2.0
     * @param   $nonce string the WP nonce  
    */
    
    static function save_counting_settings() {
        self::ppc_check_ajax_referer( 'ppc_save_counting_settings' );
        
        parse_str( $_REQUEST['form_data'], $settings );
        
        $save_settings = PPC_save_options::save_counting_settings( $settings );
        if( is_wp_error( $save_settings ) ) die( $save_settings->get_error_message() );
		
        die( 'ok' );
    }
    
    /**
     * Handles the AJAX request for the misc settings saving.
     *
     * @access  public
     * @since   2.0
     * @param   $nonce string the WP nonce  
    */
    
    static function save_misc_settings() {
        self::ppc_check_ajax_referer( 'ppc_save_misc_settings' );
        
        parse_str( $_REQUEST['form_data'], $settings );
        
        $save_settings = PPC_save_options::save_misc_settings( $settings );
        if( is_wp_error( $save_settings ) ) die( $save_settings->get_error_message() );
        
        die( 'ok' );
    }
    
    /**
     * Handles the AJAX request for the permissions saving.
     *
     * @access  public
     * @since   2.0
     * @param   $nonce string the WP nonce  
    */
    
    static function save_permissions() {
        self::ppc_check_ajax_referer( 'ppc_save_permissions' );
        
        parse_str( $_REQUEST['form_data'], $settings );
        
        $save_settings = PPC_save_options::save_permissions( $settings );
        if( is_wp_error( $save_settings ) ) die( $save_settings->get_error_message() );
        
        die( 'ok' );
    }
    
    /**
     * Fetches users to be personalized basing on the requested user role.
     *
     * @access  public
     * @since   2.0  
     */
    static function personalize_fetch_users_by_roles() {
        global $ppc_global_settings;
        self::ppc_check_ajax_referer( 'ppc_personalize_fetch_users_by_roles' );
        
        echo 'ok';
        $user_role = trim( $_REQUEST['user_role'] );
        
        $args = array( 
            'orderby' => 'display_name', 
            'order' => 'ASC', 
            'role' => $user_role,
            'count_total' => true, 
            'fields' => array( 
                'ID', 
                'display_name' 
            ) 
        );
		
		/**
		 * Filters user fetching (by role) for Personalize settings box.
		 * 
		 * This fetches the users list that is shown in the Options Personalize settings box when a user role is clicked.
		 *
		 * @since	2.0
		 * @param	array $args	WP_User_query args
		 */
		
        $args = apply_filters( 'ppc_personalize_fetch_users_args', $args );
        
        $users_to_show = new WP_User_Query( $args );
        
		if( $users_to_show->get_total() == 0 ) {
            _e( 'No users found.' , 'post-pay-counter');

		} else {
			$n = 0;
			$html = '';
			echo '<table>';
			
			foreach( $users_to_show->results as $single ) {
				if( $n % 3 == 0 )
					$html .= '<tr>';
				
				$html .= '<td><a href="'.admin_url( $ppc_global_settings['options_menu_link'].'&amp;userid='.$single->ID ).'" title="'.$single->display_name.'">'.$single->display_name.'</a></td>';
				
				if( $n % 3 == 2 )
					$html .= '</tr>';
				
				/**
				 * Filters user display in Personalize settings box.
				 * 
				 * This fires for every user that is displayed for the selected role.
				 *
				 * @since	2.0
				 * @param	string $html html code for the user list up to the current one
				 * @param	object $single WP_User current user data
				 */
				
				echo apply_filters( 'ppc_html_personalize_list_print_user', $html, $single );
				
				$html = '';
				$n++;
			}
			
			echo '</table>';
		}
        
        /**
		 * Allows to display html after the list of users from a user-role in the personalize settings box.
		 *
		 * @since	2.518
		 * @param	string $user_role user role selected
		 */
        
        do_action( 'ppc_personalize_users_role_list_end', $user_role );
        
        exit;
    }

    /**
     * Fetches users to be personalized basing on the requested user role.
     *
     * @access  public
     * @since   2.710
     */
    static function stats_get_users_by_role() {
        global $ppc_global_settings;
        self::ppc_check_ajax_referer( 'ppc_stats_get_users_by_role' );
        
        $user_role = trim( $_REQUEST['user_role'] );
        
        $args = array( 
            'orderby' => 'display_name', 
            'order' => 'ASC', 
            'role' => $user_role,
            'count_total' => true, 
            'fields' => array( 
                'ID', 
                'display_name' 
            ) 
        );
		
		/**
		 * Filters user fetching (by role) for stats select.
		 * 
		 * This fetches the users list that is shown in the Stats User dropdown when a user role is selected.
		 *
		 * @since	2.710
		 * @param	array $args	WP_User_query args
		 */
		
        $args = apply_filters( 'ppc_stats_get_users_args', $args );
        
        $users_to_show = new WP_User_Query( $args );
        $html = '<option value="ppc_any">'.__( 'Any', 'post-pay-counter' ).'</option>';
        
		if( $users_to_show->get_total() != 0 ) {

			foreach( $users_to_show->results as $single )
				$html .= '<option value="'.$single->ID.'">'.$single->display_name.'</option>';
				
		}
        
        wp_send_json_success( array(
			'html' => $html
		) );
    }
    
    /**
     * If a valid user is given, their special settings are deleted.
     *
     * @access  public
     * @since   2.0  
     */
    
    static function vaporize_user_settings() {
        global $ppc_global_settings;
        self::ppc_check_ajax_referer( 'ppc_vaporize_user_settings' );
        
        $user_id = (int) $_REQUEST['user_id'];
        
        if( is_int( $user_id ) ) {
            delete_user_option( $user_id, $ppc_global_settings['option_name'] );

			PPC_general_functions::clear_settings_cache( $user_id );
            
			/**
			 * Fires after a user's personalized settings have been deleted.
			 *
			 * @since 	2.0
			 * @param	int $user_id user id whose settings have been deleted.
			 */
			
            do_action( 'ppc_deleted_user_settings', $user_id );
            
            die( 'ok' );
        }
    }
    
    /**
     * Imports a valid serialized and base64_encoded array of PPC settings.
     *
     * @access  public
     * @since   2.1.3
     */
    static function import_settings() {
        global $ppc_global_settings;
        self::ppc_check_ajax_referer( 'ppc_import_settings' );
        
        $to_import = json_decode( base64_decode( $_REQUEST['import_settings_content'] ), true );
	
        if( $to_import != NULL AND is_array( $to_import ) AND isset( $to_import['userid'] ) ) {

            $to_import['userid'] = $_REQUEST['userid'];
            $update = PPC_save_options::update_settings( $to_import['userid'], $to_import );
            
            if( is_wp_error( $update ) )
				wp_send_json_error( array(
					'message' => $update->get_error_message()
				) );
            else
                wp_send_json_success();
        
        } else {
			wp_send_json_error( array(
				'message' => __( 'What are you importing, cows?', 'post-pay-counter' )
			) );
        }
    }
    
    /**
     * Clears error log.
	 *
	 * Empties error log wp_option, if it exists (doesn't delete not to lose autload=no).
     *
     * @access  public
     * @since   2.22
    */
    
    static function clear_error_log() {
        global $ppc_global_settings;
        self::ppc_check_ajax_referer( 'ppc_clear_error_log' );
        
        if( get_option( $ppc_global_settings['option_errors'] ) ) {
            if( ! update_option( $ppc_global_settings['option_errors'], array() ) )
                die( __( 'Error: could not clear error log.', 'post-pay-counter' ) );
        }
        
        die( 'ok' );
    }
	
	/**
     * Dismisses a notification.
	 *
	 * Adds notification ID to wp_option list of dismissed ones.
     *
     * @access  public
     * @since   2.46
    */
    
    static function dismiss_notification() {
        global $ppc_global_settings;
        self::ppc_check_ajax_referer( 'ppc_dismiss_notification' );
        
        if( ! $dismissed = get_option( 'ppc_dismissed_notifications' ) )
			$dismissed = array();
        
		$dismissed[$_REQUEST['id']] = $_REQUEST['id'];
		update_option( 'ppc_dismissed_notifications', $dismissed );
		
		die( 'ok' );
    }
	
	/**
     * Cares about AJAX license activation
     *
     * @access  public
     * @since   2.511
     */
	
    static function license_activate() {
        PPC_ajax_functions::ppc_check_ajax_referer( 'ppc_license_key_activate' );
        
        $license = '';
        $license = apply_filters( 'ppcp_license_activate_object', $license, $_REQUEST['license_key'] );
        
        if( ! ( is_a( $license, 'PPC_license' ) OR is_a( $license, 'PPCP_license' ) ) )
            die( __( 'Make sure the license you are trying to activate is related to an addon that is both installed and active.', 'post-pay-counter' ).' '.__( 'You may be activating an old PRO version: in this case, try to use the other License status box you can find in the Options page. You may have to scroll the page to find it.', 'post-pay-counter' ).'<br />Error: License object is not a PPCP_license instance.' );
        
        $license_activate = $license->activate( $_REQUEST['license_key'] );
        if( is_wp_error( $license_activate ) ) die( $license_activate->get_error_message() );
		
        die( 'ok' );
    }
    
	/**
     * Cares about AJAX license deactivation
     *
     * @access  public
     * @since   2.511
     */
	
    static function license_deactivate() {
        PPC_ajax_functions::ppc_check_ajax_referer( 'ppc_license_key_deactivate' );
        
        $license = '';
        $license = apply_filters( 'ppcp_license_deactivate_object', $license, $_REQUEST['plugin_slug'] );
        
        if( ! ( is_a( $license, 'PPC_license' ) OR is_a( $license, 'PPCP_license' ) ) )
            die( 'License object is not a PPCP_license instance.' );
        
        $license_deactivate = $license->deactivate();
        if( is_wp_error( $license_deactivate ) ) die( $license_deactivate->get_error_message() );
		
        die( 'ok' );
    }
}
