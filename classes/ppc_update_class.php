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
    
    static function update() {
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
     * Changed @ 2.3.1
     * 
     * Also updates current version option and pages permissions.
     *
     * @access  public
     * @since   2.0.5
    */
    
    static function update_exec() {
        global $ppc_global_settings;
        
        $general_settings = PPC_general_functions::get_settings( 'general' );
        $general_settings_old = $general_settings;
		
        //Fixed: installation added personalized user settings in place of general ones (v 2.1.1)
		if( $general_settings['userid'] != 'general' ) {
            delete_option( $ppc_global_settings['option_name'] );
            unset( $ppc_global_settings['general_settings'] );
            
			PPC_install_functions::ppc_install_procedure();
		}
        
        /**
         * Settings updates
         */
        
        $new_settings = array(  
            'general' => array(
				'display_overall_stats' => 1,
				'counting_visits_callback_value' => '',
				'admins_override_permissions' => 0,
				'basic_payment_display_status' => 'tooltip',
				'counting_words_display_status' => 'count',
				'counting_visits_display_status' => 'count',
				'counting_images_display_status' => 'count',
				'counting_comments_display_status' => 'count',
				'stats_display_edit_post_link' => 0,
				'counting_words_parse_spaces' => 0,
				'enable_stats_payments_tooltips' => 1
            ),

            //User settings only hold different-from-general-records nowadays
            'user' => array(
            )
        );
        
        foreach( $new_settings['general'] as $setting => $value ) {
            if( ! isset( $general_settings[$setting] ) )
                $general_settings[$setting] = $value;
        }
		
		foreach( $new_settings['user'] as $setting => $value ) {
            if( ! isset( $general_settings[$setting] ) )
                $general_settings[$setting] = $value;
        }
        
        //Count private posts
        if( ! isset( $general_settings['counting_allowed_post_statuses']['private'] ) )
            $general_settings['counting_allowed_post_statuses']['private'] = 0;
        
        //Images & comments problems with counting systems - general and personalized settings update (v 2.1.2)
        if( isset( $general_settings['counting_images_value'] ) OR isset( $general_settings['counting_comments_value'] ) ) {
            $general_settings['counting_images_system_incremental_value'] = $general_settings['counting_images_value'];
            $general_settings['counting_comments_system_incremental_value'] = $general_settings['counting_comments_value'];
            unset( $general_settings['counting_images_value'], $general_settings['counting_comments_value'] );
        }
        
        if( $general_settings != $general_settings_old ) {
            if( ! update_option( $ppc_global_settings['option_name'], $general_settings ) ) {
                $error = new PPC_Error( 'ppcp_update_settings_error', __( 'Error: could not update settings.', 'post-pay-counter' ), array(
                    'settings' => $general_settings,
                    'settings_start' => $general_settings_old
                ) );
                return $error->return_error();
            }
        }
        
        //User settings updates
        $args = array(
			'meta_key' => $ppc_global_settings['option_name'],
            'fields' => 'ids'
        );
		$personalized_users = get_users( $args );
        
		foreach( $personalized_users as $user ) {
			$user_settings = PPC_general_functions::get_settings( $user );
            $user_settings_old = $user_settings; 
			
			foreach( $new_settings['user'] as $setting => $value ) {
            if( ! isset( $user_settings[$setting] ) )
                $user_settings[$setting] = $value;
            }
            
            //Count private posts
            if( ! isset( $user_settings['counting_allowed_post_statuses']['private'] ) )
                $user_settings['counting_allowed_post_statuses']['private'] = 0;
            
            //Images & comments problems with counting systems - general and personalized settings update (v 2.1.2)
            if( isset( $user_settings['counting_images_value'] ) OR isset( $user_settings['counting_comments_value'] ) ) {
                $user_settings['counting_images_system_incremental_value'] = $user_settings['counting_images_value'];
                $user_settings['counting_comments_system_incremental_value'] = $user_settings['counting_comments_value'];
                unset( $user_settings['counting_images_value'], $user_settings['counting_comments_value'] );
    		}
   
            if( $user_settings != $user_settings_old ) {
    			if( ! update_user_option( $user, $ppc_global_settings['option_name'], $user_settings ) ) {
    				$error = new PPC_Error( 'ppc_update_user_settings_error', __( 'Error: could not update user\'s settings.', 'post-pay-counter' ), array(
                        'settings' => $user_settings,
                        'settings_start' => $user_settings_old
                    ) );
                    $error->return_error();
    			}
            }
		}
    		
		//$general_settings = PPC_general_functions::get_settings( 'general' );
		
		//License cron check
        if( ! wp_next_scheduled( 'ppcp_cron_check_activation' ) )
			wp_schedule_event( time(), 'weekly2', 'ppcp_cron_check_activation' );
		
		PPC_general_functions::manage_cap_allowed_user_roles_plugin_pages( $general_settings['can_see_options_user_roles'], $general_settings['can_see_stats_user_roles'] );
		
		//Insert default addons list
        $addons = array(
			'data' => array(
				'Post Pay Counter PRO' => array(
					'description' => 'Keep track of past payments, integrate with PayPal, Analytics and Adsense, and much more!',
					'image' => 'http://postpaycounter.com/ppcp/features/images/ppcp.png',
					'link' => 'http://postpaycounter.com/post-pay-counter-pro',
					'campaign' => 'ppcp'
				),
				'Facebook' => array(
					'description' => 'Pay writers basing on the number of Facebook shares, likes and comments their articles receive.',
					'image' => 'http://postpaycounter.com/ppcp_fb/features/images/stats.png',
					'link' => 'http://postpaycounter.com/facebook-pay-per-social-interactions-shares-likes-and-comments',
					'campaign' => 'ppcp_fb'
				),
				'Publisher bonus' => array(
					'description' => 'Set up an author rewarding system in which users (proof-readers) earn bonus by publishing posts.',
					'image' => 'http://postpaycounter.com/ppcp_pb/features/images/metabox.png',
					'link' => 'http://postpaycounter.com/publisher-bonus-editor-rewarding-system',
					'campaign' => 'ppcp_pb'
				),
				'User Roles Custom Settings' => array(
					'description' => 'Allows to set custom settings for each user role that apply to all users belonging to it.',
					'image' => 'http://postpaycounter.com/ppc_urcs/update/images/personalize_settings_box.jpg',
					'link' => 'http://postpaycounter.com/user-roles-custom-settings',
					'campaign' => 'ppc_urcs'
				),
				'Stop Words' => array(
					'description' => 'Allows to specify a list of stop words that should not be counted when computing posts word count.',
					'image' => 'http://postpaycounter.com/ppcp_sw/features/images/stopwords.png',
					'link' => 'http://postpaycounter.com/stop-words-exclude-certain-words',
					'campaign' => 'ppcp_sw'
				),
				'Shortcode Stripper' => array(
					'description' => 'Allows to exclude text enclosed by shortcodes from words payment.',
					'image' => 'http://postpaycounter.com/ppc/addons/shortcode.jpg',
					'link' => 'http://postpaycounter.com/shortcode-stripper-exclude-shortcodes-from-words-payment/',
					'campaign' => 'ppc_shortcode_stripper'
				)
			),
			'time' => current_time( 'timestamp' ) + 3600*48
		);
		
		foreach( $addons['data'] as $title => &$info ) 
			$info['link'] .= '?utm_source=users_site&utm_medium=addons_list&utm_campaign='.$info['campaign']; //referral

		if( ! get_option( 'ppc_addons_list' ) )
			add_option( 'ppc_addons_list', $addons, '', 'no' );
		else
			update_option( 'ppc_addons_list', $addons );
		
        update_option( 'ppc_current_version', $ppc_global_settings['newest_version'] );
        
        //PRO gets deactivated as soon as PPC is deactivated - if it was active before, reactivate if now
        if( get_option( 'ppcp_active' ) == 1 )
            activate_plugin( 'post-pay-counter-pro/post-pay-counter-pro.php' );
		
		if( get_option( 'ppcp_pb_active' ) == 1 )
            activate_plugin( 'ppcp-publisher-bonus/ppcp-publisher-bonus.php' );
		
		if( get_option( 'ppcp_fb_active' ) == 1 )
            activate_plugin( 'ppcp-facebook/ppcp-facebook.php' );
		
		if( get_option( 'ppcp_sw_active' ) == 1 )
            activate_plugin( 'ppcp-stopwords/ppcp-stopwords.php' );
            
	   if( get_option( 'ppc_urcs_active' ) == 1 )
            activate_plugin( 'ppc-user-roles-custom-settings/ppc-user-roles-custom-settings.php' );
    }
}
