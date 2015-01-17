<?php

/**
 * @author Stefano Ottolenghi
 * @copyright 2013
 */

require_once( 'ppc_save_options_class.php' );

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
            die( __( 'Error: Seems like AJAX request was not recognised as coming from the right page. Maybe hacking around..?' , 'ppc') );
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
        
        $args = array( 
            'orderby' => 'display_name', 
            'order' => 'ASC', 
            'role' => $_REQUEST['user_role'], 
            'count_total' => true, 
            'fields' => array( 
                'ID', 
                'display_name' 
            ) 
        );
        $args = apply_filters( 'ppc_personalize_fetch_users_args', $args );
        
        $users_to_show = new WP_User_Query( $args );
		if( $users_to_show->get_total() == 0 )
            die( __( 'No users found.' , 'ppc') );
        
        $n = 0;
        $html = '';
        echo '<table>';
        
        foreach( $users_to_show->results as $single ) {
            if( $n % 3 == 0 )
                $html .= '<tr>';
            
                $html .= '<td><a href="'.admin_url( $ppc_global_settings['options_menu_link'].'&amp;userid='.$single->ID ).'" title="'.$single->display_name.'">'.$single->display_name.'</a></td>';
            
			if( $n % 3 == 2 )
                $html .= '</tr>';
            
            echo apply_filters( 'ppc_html_personalize_list_print_user', $html );
            
            $html = '';
            $n++;
        }
        
        echo '</table>';
        exit;
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
        
        $to_import = unserialize( base64_decode( $_REQUEST['import_settings_content'] ) );
        
        if( is_array( $to_import ) AND isset( $to_import['userid'] ) ) {
            
            $update = PPC_save_options::update_settings( $to_import['userid'], $to_import );
            
            if( is_wp_error( $update ) )
                echo $update->get_error_message();
            else
                echo 'ok';
        
        } else {
            _e( 'What are you importing, cows?', 'ppc' );
        }
        
        exit;
    }
    
    /**
     * Clears error log (deletes wp_option).
     *
     * @access  public
     * @since   2.22
    */
    
    static function clear_error_log() {
        global $ppc_global_settings;
        self::ppc_check_ajax_referer( 'ppc_clear_error_log' );
        
        if( get_option( $ppc_global_settings['option_errors'] ) ) {
            if( ! delete_option( $ppc_global_settings['option_errors'] ) )
                die( __( 'Error: could not clear error log.', 'ppc' ) );
        }
        
        die( 'ok' );
    }
	
	/**
     * Dismisses a notification
     *
     * @access  public
     * @since   2.46
    */
    
    static function dismiss_notification() {
        global $ppc_global_settings;
        self::ppc_check_ajax_referer( 'ppc_dismiss_notification' );
        
        $dismissed = get_option( 'ppc_dismissed_notifications', array() );
        
		$dismissed[$_REQUEST['id']] = $_REQUEST['id'];
		if( ! update_option( 'ppc_dismissed_notifications', $dismissed ) )
			new PPC_Error( 'ppc_dismiss_notification', 'Could not dismiss notification.', array( 'id' => $_REQUEST['id'] ) );
    }
}
?>