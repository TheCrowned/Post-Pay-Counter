<?php
/*
Plugin Name: Post Pay Counter
Plugin URI: http://postpaycounter.com
Description: Easily handle authors' payments on a multi-author blog by computing posts' remuneration basing on admin defined rules.
Author: Stefano Ottolenghi
Version: 2.517
Author URI: http://www.thecrowned.org/
Text Domain: post-pay-counter
*/

/** Copyright Stefano Ottolenghi 2013
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

//If trying to open this file out of wordpress, warn and exit
if( ! function_exists( 'add_action' ) )
    die( 'This file is not meant to be called directly.' );

require_once( 'classes/ppc_general_functions_class.php' );
require_once( 'classes/ppc_generate_stats_class.php' );
require_once( 'classes/ppc_counting_stuff_class.php' );
require_once( 'classes/ppc_html_functions_class.php' );
require_once( 'classes/ppc_options_fields_class.php' );
require_once( 'classes/ppc_ajax_functions_class.php' );
require_once( 'classes/ppc_install_functions_class.php' );
require_once( 'classes/ppc_permissions_class.php' );
require_once( 'classes/ppc_meta_boxes_class.php' );
require_once( 'classes/ppc_system_info_class.php' );
require_once( 'classes/ppc_error_class.php' );
require_once( 'classes/ppc_welcome_class.php' );
require_once( 'classes/ppc_addons_class.php' );
require_once( 'classes/ppc_notifications_class.php' );
require_once( 'classes/ppc_counting_types_class.php' );
require_once( 'classes/ppc_license_class.php' );
require_once( 'classes/ppc_autoupdate_class.php' );

define( 'PPC_DEBUG_SHOW', false );
define( 'PPC_DEBUG_LOG', true );

class post_pay_counter {
    public static $options_page_settings;
    
    function __construct() {
        global $ppc_global_settings;
        
        $ppc_global_settings['current_version'] = get_option( 'ppc_current_version' );
        $ppc_global_settings['newest_version'] = '2.517';
        $ppc_global_settings['option_name'] = 'ppc_settings';
        $ppc_global_settings['option_errors'] = 'ppc_errors';
		$ppc_global_settings['transient_error_deletion'] = 'ppc_error_daily_deletion';
		$ppc_global_settings['transient_activation_redirect'] = '_ppc_activation_redirect';
		$ppc_global_settings['transient_update_redirect'] = '_ppc_update_redirect';
        $ppc_global_settings['folder_path'] = plugins_url( '/', __FILE__ );
		$ppc_global_settings['dir_path'] = plugin_dir_path( __FILE__ );
		$ppc_global_settings['current_page'] = '';
        $ppc_global_settings['options_menu_link'] = 'admin.php?page=ppc-options';
        $ppc_global_settings['stats_menu_link'] = 'admin.php?page=ppc-stats';
        $ppc_global_settings['cap_manage_options'] = 'post_pay_counter_manage_options';
        $ppc_global_settings['cap_access_stats'] = 'post_pay_counter_access_stats';
        $ppc_global_settings['temp'] = array( 'settings' => array() );
        
        //Add left menu entries for both stats and options pages
        add_action( 'admin_menu', array( $this, 'admin_menus' ) );
        //add_action( 'network_admin_menu', array( $this, 'post_pay_counter_network_admin_menus' ) );
        
        //Hook for the install procedure
        register_activation_hook( __FILE__, array( 'PPC_install_functions', 'ppc_install' ) );
        
        //Hook on blog adding on multisite wp to install the plugin there either
        add_action( 'wpmu_new_blog', array( 'PPC_install_functions', 'ppc_new_blog_install' ), 10, 6);
        
		//Plugin update routine
		add_action( 'plugins_loaded', array( $this, 'maybe_update' ) );
		
		//Add custom times
		add_filter( 'cron_schedules', array( $this, 'cron_add_times' ) );
		
        //On load plugin pages
        add_action( 'load-toplevel_page_ppc-stats', array( $this, 'on_load_stats_page' ) );
		add_action( 'load-'.sanitize_title( apply_filters( "ppc_admin_menu_name", "Post Pay Counter" ) ).'_page_ppc-options', array( $this, 'on_load_options_page_get_settings' ), 1 );
        add_action( 'load-'.sanitize_title( apply_filters( "ppc_admin_menu_name", "Post Pay Counter" ) ).'_page_ppc-options', array( $this, 'on_load_options_page_enqueue' ), 2 );
		add_action( 'load-'.sanitize_title( apply_filters( "ppc_admin_menu_name", "Post Pay Counter" ) ).'_page_ppc-addons', array( 'PPC_addons', 'on_load_addons_page_enqueue' ) );
        //add_action( 'load-toplevel_page_post_pay_counter_show_network_stats', array( &$this, 'on_load_stats_page' ) );
        
        //Localization
        add_action( 'plugins_loaded', array( $this, 'load_localization' ) );
        
		//About screen
		add_action( 'admin_menu', array( 'PPC_welcome', 'add_pages' ) );
		add_action( 'admin_head', array( 'PPC_welcome', 'admin_head' ) );
        //add_action( 'admin_init', array( 'PPC_welcome', 'welcome' ) );
		add_action( 'load-dashboard_page_ppc-about', array( 'PPC_welcome', 'custom_css' ) );
		add_action( 'load-dashboard_page_ppc-changelog', array( 'PPC_welcome', 'custom_css' ) );
		
        //Custom links besides the usual "Edit" and "Deactivate"
        add_filter( 'plugin_action_links', array( $this, 'settings_meta_link' ), 10, 2 );
        add_filter( 'plugin_row_meta', array( $this, 'donate_meta_link' ), 10, 2 );
        
		//Counting types
		//add_filter( 'ppc_active_user_counting_types', array( 'PPC_counting_types', 'counting_type_visits_callback' ), 10, 2 );
		
        //Notifications
        add_action( 'admin_init', array( $this, 'load_notifications' ) );
		
        //Manage AJAX calls
        add_action( 'wp_ajax_ppc_save_counting_settings', array( 'PPC_ajax_functions', 'save_counting_settings' ) );
        add_action( 'wp_ajax_ppc_save_permissions', array( 'PPC_ajax_functions', 'save_permissions' ) );
        add_action( 'wp_ajax_ppc_save_misc_settings', array( 'PPC_ajax_functions', 'save_misc_settings' ) );
        add_action( 'wp_ajax_ppc_personalize_fetch_users_by_roles', array( 'PPC_ajax_functions', 'personalize_fetch_users_by_roles' ) );
        add_action( 'wp_ajax_ppc_vaporize_user_settings', array( 'PPC_ajax_functions', 'vaporize_user_settings' ) );
        add_action( 'wp_ajax_ppc_import_settings', array( 'PPC_ajax_functions', 'import_settings' ) );
        add_action( 'wp_ajax_ppc_clear_error_log', array( 'PPC_ajax_functions', 'clear_error_log' ) );
		add_action( 'wp_ajax_ppc_dismiss_notification', array( 'PPC_ajax_functions', 'dismiss_notification' ) );
		
		//License hooks
		add_action( 'wp_ajax_ppc_license_activate', array( 'PPC_ajax_functions', 'license_activate' ) );
        add_action( 'wp_ajax_ppc_license_deactivate', array( 'PPC_ajax_functions', 'license_deactivate' ) );
    }
	
	/**
	 * Adds "every two weeks" as schedule time (ppcp activation check).
     *
	 * @access  public
     * @since   2.511
     * @param 	$schedules array shedules already
	 * @return	array schedules
     */
	 
    function cron_add_times( $schedules ) {
        $schedules['weekly2'] = array(
            'interval' => 3600*24*7*2,
            'display' => __( 'Once every two weeks', 'ppcp')
        );
		
        return $schedules;
    }
	
    /**
     * Adds first level side menu "Post Pay Counter"
     *
     * @access  public
     * @since   2.0
     */
    
    function admin_menus() {
        global $ppc_global_settings;
        
        add_menu_page( 'Post Pay Counter', apply_filters( "ppc_admin_menu_name", "Post Pay Counter" ), $ppc_global_settings['cap_access_stats'], 'ppc-stats', array( $this, 'show_stats' ) );
        add_submenu_page( 'ppc-stats', 'Post Pay Counter Stats', __( 'Stats', 'post-pay-counter' ), $ppc_global_settings['cap_access_stats'], 'ppc-stats', array( $this, 'show_stats' ) );
        $ppc_global_settings['options_menu_slug'] = add_submenu_page( 'ppc-stats', 'Post Pay Counter Options', __( 'Options', 'post-pay-counter' ), $ppc_global_settings['cap_manage_options'], 'ppc-options', array( $this, 'show_options' ) );
        add_submenu_page( 'ppc-stats', 'Post Pay Counter System Info', __( 'System Info', 'post-pay-counter' ), $ppc_global_settings['cap_manage_options'], 'ppc-system-info', array( 'PPC_system_info', 'system_info' ) );
		add_submenu_page( 'ppc-stats', 'Post Pay Counter Addons', __( 'Addons', 'post-pay-counter' ), $ppc_global_settings['cap_manage_options'], 'ppc-addons', array( 'PPC_addons', 'addons_page' ) );
    }
    
    //Adds first level side menu (network admin)
    /*function post_pay_counter_network_admin_menus() {
        add_menu_page( 'Post Pay Counter', 'Post Pay Counter', 'post_pay_counter_access_stats', 'post_pay_counter_show_network_stats', array( $this, 'post_pay_counter_show_network_stats' ) );
        add_submenu_page( 'post_pay_counter_show_network_stats', 'Post Pay Counter Stats', 'Stats', 'post_pay_counter_access_stats', 'post_pay_counter_show_network_stats', array( $this, 'post_pay_counter_show_network_stats' ) );
        $ppc_global_settings['stats_menu_link'] = 'admin.php?page=post_pay_counter_show_network_stats';
        $ppc_global_settings['options_menu_slug'] = add_submenu_page( 'post_pay_counter_show_network_stats', 'Post Pay Counter Options', 'Options', 'post_pay_counter_manage_options', 'post_pay_counter_network_options', array( $this, 'post_pay_counter_network_options' ) );
        $ppc_global_settings['options_menu_link'] = 'admin.php?page=post_pay_counter_network_options';
    }*/
    
    /**
     * If current_version option is DIFFERENT from the latest release number, launch the update procedure.
     *
     * @access  public
     * @since   2.1.1
     */
    
    function maybe_update() {
        global $ppc_global_settings;
        
        if( $ppc_global_settings['current_version'] != $ppc_global_settings['newest_version'] ) {
            require_once( 'classes/ppc_update_class.php' );
            
            PPC_update_class::update();
            $ppc_global_settings['current_version'] = $ppc_global_settings['newest_version'];
            
			/**
			 * Fires after PPC has been updated to latest version.
			 * @since 2.1.1
			 */
			
            do_action( 'ppc_updated' );
			
			//Send to Welcome page
			wp_safe_redirect( admin_url( add_query_arg( array( 'page' => 'ppc-about' ), 'admin.php' ) ) );
			//set_transient( $ppc_global_settings['transient_update_redirect'], 'do it!', 3600 );
        }
    }
    
    /**
     * Reponsible of the datepicker's files, plugin's js and css loading in the stats page
     *
     * @access  public
     * @since   2.0
     */
    
    function on_load_stats_page() {
        global $ppc_global_settings;
        
		$general_settings = PPC_general_functions::get_settings( 'general' );
		
		//Initiliaze counting types
		$ppc_global_settings['counting_types_object'] = new PPC_counting_types();
		$ppc_global_settings['counting_types_object']->register_built_in_counting_types();
		
        $args = array(
            'post_type' => $general_settings['counting_allowed_post_types'],
			'posts_per_page' => 1,
            'orderby' => 'post_date',
            'order' => 'ASC'
        );
        $first_available_post = new WP_Query( $args );
		
		if( $first_available_post->found_posts == 0 )
            $first_available_post_time = current_time( 'timestamp' );
        else
            $first_available_post_time = strtotime( $first_available_post->posts[0]->post_date );
		
		$args = array(
            'post_type' => $general_settings['counting_allowed_post_types'],
			'posts_per_page' => 1,
            'orderby' => 'post_date',
            'order' => 'DESC'
        );
        $last_available_post = new WP_Query( $args );
		
		if( $last_available_post->found_posts == 0 )
            $last_available_post = current_time( 'timestamp' );
        else
            $last_available_post = strtotime( $last_available_post->posts[0]->post_date );
        
        wp_enqueue_script( 'jquery-ui-datepicker' );
        wp_enqueue_style( 'jquery.ui.theme', $ppc_global_settings['folder_path'].'style/ui-lightness/jquery-ui-1.8.15.custom.css' );
        wp_enqueue_style( 'ppc_header_style', $ppc_global_settings['folder_path'].'style/ppc_header_style.css', array( 'wp-admin' ) );
		wp_enqueue_style( 'ppc_stats_style', $ppc_global_settings['folder_path'].'style/ppc_stats_style.css' );
        wp_enqueue_script( 'ppc_stats_effects', $ppc_global_settings['folder_path'].'js/ppc_stats_effects.js', array( 'jquery' ) );
        wp_localize_script( 'ppc_stats_effects', 'ppc_stats_effects_vars', array(
            'datepicker_mindate' => date( 'Y-m-d', $first_available_post_time ),
            'datepicker_maxdate' => date( 'Y-m-d', $last_available_post )
        ) );
    } 
    
    /**
     * Loads metaboxes and tooltips js+css in the plugin options page and all the js and css needed, plus the strings js needs (nonces and localized text).
     *
     * @access  public
     * @since   2.0
     */
    
    function on_load_options_page_enqueue() {
        global $ppc_global_settings;
        wp_enqueue_script( 'post' );
		
        add_meta_box( 'ppc_counting_settings', __( 'Counting Settings', 'post-pay-counter' ), array( 'PPC_meta_boxes', 'meta_box_counting_settings' ), $ppc_global_settings['options_menu_slug'], 'normal', 'default', self::$options_page_settings );
        add_meta_box( 'ppc_permissions', __( 'Permissions', 'post-pay-counter' ), array( 'PPC_meta_boxes', 'meta_box_permissions' ), $ppc_global_settings['options_menu_slug'], 'normal', 'default', self::$options_page_settings );
        
        if( ! isset( $_GET['userid'] ) OR ( isset( $_GET['userid'] ) AND ! is_numeric( $_GET['userid'] ) ) ) {
            add_meta_box( 'ppc_personalize_settings', __( 'Personalize Settings', 'post-pay-counter' ), array( 'PPC_meta_boxes', 'meta_box_personalize_settings' ), $ppc_global_settings['options_menu_slug'], 'side', 'default', self::$options_page_settings );
            add_meta_box( 'ppc_license', __( 'License status', 'ppc' ), array( 'PPC_meta_boxes', 'meta_box_license' ), $ppc_global_settings['options_menu_slug'], 'side', 'default', post_pay_counter::$options_page_settings );
            add_meta_box( 'ppc_misc_settings', __( 'Miscellanea', 'post-pay-counter' ), array( 'PPC_meta_boxes', 'meta_box_misc_settings' ), $ppc_global_settings['options_menu_slug'], 'normal', 'default', self::$options_page_settings );
        }
        
        add_meta_box( 'ppc_import_export_settings', __( 'Import/Export Settings', 'post-pay-counter' ), array( 'PPC_meta_boxes', 'meta_box_import_export_settings' ), $ppc_global_settings['options_menu_slug'], 'side', 'default', self::$options_page_settings );
        add_meta_box( 'ppc_support_the_fucking_author', __( 'Support the author', 'post-pay-counter' ), array( 'PPC_meta_boxes', 'meta_box_support_the_fucking_author' ), $ppc_global_settings['options_menu_slug'], 'side' );
        add_meta_box( 'ppc_pro_features', __( 'Everything you\'re missing by not being PRO', 'post-pay-counter' ), array( 'PPC_meta_boxes', 'meta_box_pro_features' ), $ppc_global_settings['options_menu_slug'], 'side' );
        add_meta_box( 'ppc_error_log', __( 'Error log', 'post-pay-counter' ), array( 'PPC_meta_boxes', 'meta_box_error_log' ), $ppc_global_settings['options_menu_slug'], 'side', 'default', self::$options_page_settings );
        
        wp_enqueue_style( 'jquery.tooltip.theme', $ppc_global_settings['folder_path'].'style/tipTip.css' );
        wp_enqueue_style( 'ppc_header_style', $ppc_global_settings['folder_path'].'style/ppc_header_style.css', array( 'wp-admin' ) );
		wp_enqueue_style( 'ppc_options_style', $ppc_global_settings['folder_path'].'style/ppc_options_style.css', array( 'wp-admin' ) );
		wp_enqueue_style( 'ppc_options_style_old', $ppc_global_settings['folder_path'].'style/ppc_options_style_old.css', array( 'wp-admin' ) );
        wp_enqueue_script( 'jquery-tooltip-plugin', $ppc_global_settings['folder_path'].'js/jquery.tiptip.min.js', array( 'jquery' ) );
        wp_enqueue_script( 'ppc_options_ajax_stuff', $ppc_global_settings['folder_path'].'js/ppc_options_ajax_stuff.js', array( 'jquery' ) );
        wp_localize_script( 'ppc_options_ajax_stuff', 'ppc_options_ajax_stuff_vars', array(
            'nonce_ppc_save_counting_settings' => wp_create_nonce( 'ppc_save_counting_settings' ),
            'nonce_ppc_save_permissions' => wp_create_nonce( 'ppc_save_permissions' ),
            'nonce_ppc_save_misc_settings' => wp_create_nonce( 'ppc_save_misc_settings' ),
            'nonce_ppc_personalize_fetch_users_by_roles' => wp_create_nonce( 'ppc_personalize_fetch_users_by_roles' ),
            'nonce_ppc_vaporize_user_settings' => wp_create_nonce( 'ppc_vaporize_user_settings' ),
            'nonce_ppc_import_settings' => wp_create_nonce( 'ppc_import_settings' ),
            'nonce_ppc_clear_error_log' => wp_create_nonce( 'ppc_clear_error_log' ),
			'nonce_ppc_license_key_activate' => wp_create_nonce( 'ppc_license_key_activate' ),
            'nonce_ppc_license_key_deactivate' => wp_create_nonce( 'ppc_license_key_deactivate' ),
            'localized_ppc_license_deactivated' => __( 'Your license was successfully deactivated, you can now use it on other websites.', 'ppc'),
            'localized_license_deactivate_warning' => __( 'Are you sure you want to deactivate your license on this website? You will be able to unlock the addon features on another website, but you will not be able to use them on this one anymore.', 'ppc'),
            'localized_vaporize_user_success' => __( 'User\'s settings successfully deleted. You will be redirected to the general options page.' , 'ppc'),
            'ppc_options_url' => $ppc_global_settings['options_menu_link']
        ) );
		wp_enqueue_script( 'ppc_options_effects', $ppc_global_settings['folder_path'].'js/ppc_options_effects.js', array( 'jquery' ) );
		wp_localize_script( 'ppc_options_effects', 'ppc_options_effects_vars', array(
            'counting_words_current_zones_count' => count( self::$options_page_settings['counting_words_system_zonal_value'] ),
			'counting_visits_current_zones_count' => count( self::$options_page_settings['counting_visits_system_zonal_value'] ),
            'counting_images_current_zones_count' => count( self::$options_page_settings['counting_images_system_zonal_value'] ),
            'counting_comments_current_zones_count' => count( self::$options_page_settings['counting_comments_system_zonal_value'] ),
            'localized_too_many_zones' => __( 'No more than 10 zones are allowed.' , 'post-pay-counter'),
            'localized_too_few_zones' => __( 'No less than 2 zones are allowed.' , 'post-pay-counter'),
            'localized_need_threshold' => __( 'A payment threshold must first be set.' , 'post-pay-counter')
        ) );
        
		/**
		 * Fires on PPC options page load.
		 * 
		 * Equivalent to load-post-pay-counter_page_ppc-options but recommended, as fires after all PPC matters have been dealt with. 
		 * For example, if you hooked to load-post-pay-counter_page_ppc-options before PPC run its functions, the class variable $options_page_settings would not be set.
		 * 
		 * @since 	2.0
		 * @param	$_GET['userid'] string userid (querystring param)
		 */
		
        do_action( 'ppc_on_load_options_page', @$_GET['userid'] );
    }
    
    /**
     * Selects the correct settings for the Options page.
     * 
     * Acts depending on the given $_GET['userid']: general, trial or user-personalized. 
     * If a valid user id is asked which does not have any personalized settings, get general ones and set the userid field to the user's one, unsetting all only-general options.
     *
     * @access  public
     * @since   2.0
     */
   
    function on_load_options_page_get_settings() {
		//Numeric userid
		if( isset( $_GET['userid'] ) AND is_numeric( $_GET['userid'] ) ) {
			
			if( ! get_userdata( (int) $_GET['userid'] ) ) {
				echo '<strong>'.__( 'The requested user does not exist.' , 'post-pay-counter' ).'</strong>';
				return;
			}
			
			$settings = PPC_general_functions::get_settings( (int) $_GET['userid'], true );
			
			//User who never had personalized settings is being set, get rid of only-general settings
			if( $settings['userid'] == 'general' ) {
				$settings['userid'] = (int) $_GET['userid'];
			}
			
			/**
			 * Filters general settings on new user's custom settings.
			 * 
			 * When a user's settings are customized for the first time, general settings are taken and stripped of the only general ones (i.e. non-customizable options, such as the Miscellanea box).
			 * It's crucial that all non-personalizable settings indexes are unset before handling/saving the user's settings.
			 * 
			 *  ~ This was changed in 2.516, with only user settings different from general ones are stored. ~
			 *
			 * @since	2.0
			 * @param	$settings array PPC general settings
			 */
			
			//$settings = apply_filters( 'ppc_unset_only_general_settings_personalize_user', $settings );
		
		//General
		} else {
			$settings = PPC_general_functions::get_settings( 'general' );
		}
		
		/**
		 * Filters selected options page settings, final.
		 *
		 * They are stored in a class var and used throghout all the functions that need to know **what** settings we are displaying and using in the plugin options page.
		 *
		 * @since	2.0
		 * @param	$settings PPC options settings
		 */
		
		$settings = apply_filters( 'ppc_selected_options_settings', $settings );
		
		self::$options_page_settings = $settings; //store in class var
    }
    
	/**
     * Adds a simple WordPress notice to plugin's page
     * 
     * @access  public
     * @since   2.46
     */
    
    function load_notifications() {
    	if( ! current_user_can( 'manage_options' ) ) return;
        
        //Get notifications to be displayed
		$notifications = PPC_notifications::notifications_get_list();
		
		if( empty( $notifications ) or is_wp_error( $notifications ) ) return;
		
    	//Get array list of dismissed notifications for current user and convert it to array
    	$dismissed_notifications = get_option( 'ppc_dismissed_notifications', array() );
    	
		//if( count( $notifications ) <= count( $dismissed_notifications ) ) return;

		foreach( $notifications as $single ) {
			//Check if notification is not among dismissed ones
			if( in_array( $single['id'], $dismissed_notifications ) )
				continue;
    		
			//Check where notification should be shown
			switch( $single['display'] ) {
				case 'stats':
					if( strpos( $_SERVER['QUERY_STRING'], 'ppc-stats' ) === false )
						continue 2;
				
				case 'options':
					if( strpos( $_SERVER['QUERY_STRING'], 'ppc-options' ) === false )
						continue 2;
				
				case 'plugin':
					if( strpos( $_SERVER['QUERY_STRING'], 'ppc-' ) === false )
						continue 2;
			}

    		//Load notification
    		$notification = new PPC_notifications( $single );
			add_action( 'admin_notices', array( $notification, 'display_notification' ) );
    	}
    }
	
    /**
     * Loads localization files
     *
     * @access  public
     * @since   2.0
     */
    
    function load_localization() {
        load_plugin_textdomain( 'post-pay-counter', false, dirname( plugin_basename( __FILE__ ) ).'/lang/' );
    }
    
    /**
     * Shows the "Settings" link in the plugins list (under the title)
     *
     * @access  public
     * @since   2.0
     * @param   $links array links already in place
     * @param   $file string current plugin-file
     */
    
    function settings_meta_link( $links, $file ) {
        global $ppc_global_settings;
       
       if( $file == plugin_basename( __FILE__ ) )
            $links[] = '<a href="'.admin_url( $ppc_global_settings['options_menu_link'] ).'" title="'.__( 'Settings', 'post-pay-counter' ).'">'.__( 'Settings', 'post-pay-counter' ).'</a>';
     
        return $links;
    }
    
    /**
     * Shows the "Donate" and "Go PRO" links in the plugins list (under the description)
     *
     * @access  public
     * @since   2.0
     * @param   $links array links already in place
     * @param   $file string current plugin-file
     */
    
    function donate_meta_link( $links, $file ) {
       if( $file == plugin_basename( __FILE__ ) ) {
            $links[] = '<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=SM5Q9BVU4RT22" title="'.__( 'Donate', 'post-pay-counter' ).'">'.__( 'Donate', 'post-pay-counter' ).'</a>';
			$links[] = '<a href="http://postpaycounter.com/post-pay-counter-pro?utm_source=users_site&utm_medium=plugins_list&utm_campaign=ppcp" title="'.__( 'Go PRO', 'post-pay-counter' ).'">'.__( 'Go PRO', 'post-pay-counter' ).'</a>';
			$links[] = '<a href="http://postpaycounter.com/addons?utm_source=users_site&utm_medium=plugins_list&utm_campaign=ppc_addons" title="'.__( 'Addons', 'post-pay-counter' ).'">'.__( 'Addons', 'post-pay-counter' ).'</a>';
       }
     
        return $links;
    }
    
    /**
     * Shows the Options page
     *
     * @access  public
     * @since   2.0
     */
    
    function show_options() {
        global $ppc_global_settings;
        ?>
		
<div class="wrap">
	<div id="ppc_header">
		<div id="ppc_header_text">
			<div id="ppc_header_links">
			<?php 
			
			/**
			 * Filters installed version text displayed in upper-right section of the options page. 
			 * 
			 * @since	2.0
			 * @param	string installed version text (whole).
			 */
			 
			echo apply_filters( 'ppc_options_installed_version', __( 'Installed version' , 'post-pay-counter' ).': '.$ppc_global_settings['current_version'].' - <a href="http://postpaycounter.com/forums2/forum/post-pay-counter" title="'.__( 'Support', 'post-pay-counter' ).'" target="_blank">'.__( 'Support', 'post-pay-counter' ).'</a> - <a href="http://postpaycounter.com/category/tutorials" title="'.__( 'Tutorials', 'post-pay-counter' ).'" target="_blank">'.__( 'Tutorials', 'post-pay-counter' ).'</a>' ); 
			?>
			</div>
			<h2>Post Pay Counter - <?php _e( 'Options', 'post-pay-counter' ); ?></h2>
			<p><?php _e( 'The Post Pay Counter plugin is ready to make handling authors\' payments much, much easier, starting from... now! From this page you can set the plugin up, customizing each possible feature to best suit your needs. Options are divided into groups, and for each of the following boxes you will find details of all the features of the plugin and, for most of them, additional details and examples are available by clicking on the info icon on the right of them.', 'post-pay-counter' ); ?></p>
			<p><?php printf( __( 'Don\'t forget to take our %1$sfeatures survey%2$s to let us know what functions you\'d like to see in future releases of the plugin! Also, if you like this plugin, you may be interested in trying the shiny %3$sPRO version%2$s, containing a whole lot of useful features!', 'post-pay-counter' ), '<a href="http://postpaycounter.com/post-pay-counter-pro/post-pay-counter-pro-features-survey" title="'.__( 'Features survey', 'post-pay-counter' ).'" target="_blank">', '</a>', '<a href="http://postpaycounter.com/post-pay-counter-pro?utm_source=users_site&utm_medium=options_description&utm_campaign=ppcp" title="Post Pay Counter PRO" target="_blank">' ); ?></p>
		</div>
	</div>
	
		<?php
        if( is_numeric( self::$options_page_settings['userid'] ) ) {
            $userdata = get_userdata( self::$options_page_settings['userid'] );
			?>
			
	<p style="clear: both; text-transform: uppercase; font-size: x-small; margin-bottom: -3px; text-align: center;">
		<a href="<?php echo $ppc_global_settings['options_menu_link']; ?>" title="<?php _e( 'Go back to general settings' , 'post-pay-counter'); ?>" style="float: left; color: black; "><?php _e( 'Back to general' , 'post-pay-counter'); ?></a>
		<a href="#" id="vaporize_user_settings" accesskey="<?php echo self::$options_page_settings['userid']; ?>" title="<?php _e( 'Delete user\'s settings' , 'post-pay-counter'); ?>" style="float: right; color: red; "><?php _e( 'Delete user\'s settings' , 'post-pay-counter'); ?>'</a>
		<?php echo __( 'Currently editing user:' , 'post-pay-counter').' "'.$userdata->display_name.'"'; ?>
	</p>
			
			<?php
		}
        
		/**
		 * Fires before any metabox has been displayed in options page.
		 *
		 * @since	2.0
		 */
		
        do_action( 'ppc_html_options_before_boxes' );
        
        wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
        wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );
        ?>
		
	<div id="poststuff" class="metabox-holder has-right-sidebar">
		<div id="post-body" class="has-sidebar">
			<div id="post-body-content" class="has-sidebar-content">
        
		<?php
        do_meta_boxes( $ppc_global_settings['options_menu_slug'], 'normal', null );
        ?>
		
			</div>
		</div>
		<div id="side-info-column" class="inner-sidebar">
        
		<?php
        do_meta_boxes( $ppc_global_settings['options_menu_slug'], 'side', null );
        ?>
		
		</div>
	</div>
</div>
		
		<?php
    }
    
    /**
     * Shows the Stats page.
     *
     * @access  public
     * @since   2.0
     */
    
    function show_stats() {
        global $current_user, $ppc_global_settings, $wp_roles;
        $general_settings = PPC_general_functions::get_settings( 'general' );
        $perm = new PPC_permissions();
        
        PPC_general_functions::get_default_stats_time_range( $general_settings );
        
        //Merging _GET and _POST data due to the time range form in the stats page. Don't know whether the user is choosing the time frame from the form (POST) or arrived following a link (GET)
        $get_and_post = array_merge( $_GET, $_POST );
        
        //Validate time range values (start and end), if set. They must be isset, numeric and positive. If something's wrong, start and end time are taken from the default publication time range
        if( ( isset( $get_and_post['tstart'] ) AND ( ! is_numeric( $get_and_post['tstart'] ) OR $get_and_post['tstart'] < 0 ) )
        OR ( isset( $get_and_post['tend'] ) AND ( ! is_numeric( $get_and_post['tend'] ) OR $get_and_post['tend'] < 0 ) ) ) {
            $get_and_post['tstart'] = strtotime( $get_and_post['tstart'].' 00:00:01' );
            $get_and_post['tend']   = ( strtotime( $get_and_post['tend'].' 23:59:59' ) ); 
        } else if ( ! isset( $get_and_post['tstart'] ) OR ! isset( $get_and_post['tend'] ) ) {
            $get_and_post['tstart'] = $ppc_global_settings['stats_tstart'];
            $get_and_post['tend']   = $ppc_global_settings['stats_tend'];
        }
		
		//If empty role, or any role, or invalud role => get rid of role param
		if( isset( $get_and_post['role'] ) AND ( $get_and_post['role'] == 'ppc_any' OR $get_and_post['role'] == '' OR ! isset( $wp_roles->role_names[$get_and_post['role']] ) ) )
			unset( $get_and_post['role'] );
		
		/**
		 * Filters stats view parameters (time start, time end, role).
		 *
		 * @since 	2.0
		 * @param	array $get_and_post merged GET and POST data
		 */
		
        $get_and_post = apply_filters( 'ppc_stats_defined_parameters', $get_and_post );
		
		//Assign to global var
		$ppc_global_settings['stats_tstart'] = $get_and_post['tstart'];
        $ppc_global_settings['stats_tend'] = $get_and_post['tend'];
		
		if( isset( $get_and_post['role'] ) )
			$ppc_global_settings['stats_role'] = $get_and_post['role'];
        
		//If an author is given, put that in an array
        if( isset( $get_and_post['author'] ) AND is_numeric( $get_and_post['author'] ) AND $userdata = get_userdata( $get_and_post['author'] ) ) {
            $ppc_global_settings['current_page'] = 'stats_detailed';
			$author = array( $get_and_post['author'] );
        } else {
            $ppc_global_settings['current_page'] = 'stats_general';
			$author = NULL;
		}
        
		/**
		 * Fires before any HTML has been output in the stats page.
		 *
		 * @since	2.0
		 * @param	mixed $author author for which stats are displayed. If given, is the only index of an array, NULL means general stats are being requested.
		 */
		
        do_action( 'ppc_before_stats_html', $author );
        ?>
		
<div class="wrap">
	<h2>Post Pay Counter - <?php _e( 'Stats', 'post-pay-counter' ); ?></h2>
        
		<?php
        //AUTHOR STATS
        if( is_array( $author ) ) {
            echo PPC_HTML_functions::show_stats_page_header( $userdata->display_name, PPC_general_functions::get_the_author_link( $author[0] ) );
            
            if( ! $perm->can_see_others_detailed_stats() AND $current_user->ID != $author[0] ) return _e( 'You do not have sufficient permissions to access this page' );
            
            $stats = PPC_generate_stats::produce_stats( $ppc_global_settings['stats_tstart'], $ppc_global_settings['stats_tend'], $author );
            if( is_wp_error( $stats ) ) {
                echo $stats->get_error_message();
                return;
            }
            
			/**
			 * Fires before the *author* stats page form and table been output.
			 *
			 * @since	2.0
			 * @param	array $stats a PPC_generate_stats::produce_stats() result - current stats.
			 */
			
            do_action( 'ppc_html_stats_author_before_stats_form', $stats );
            ?>
			
	<form action="#" method="post" id="ppc_stats" accesskey="<?php echo $author[0]; //accesskey holds author id ?>"> 
            
			<?php
			echo PPC_HTML_functions::get_html_stats( $stats['formatted_stats'], $stats['raw_stats'], $author );
            
			/**
			 * Fires after the *author* stats page form and table been output.
			 *
			 * @since	2.0
			 */
			
            do_action( 'ppc_html_stats_author_after_stats_form' );
            
        //GENERAL STATS
        } else {
			$page_permalink = $ppc_global_settings['stats_menu_link'].'&amp;tstart='.$ppc_global_settings['stats_tstart'].'&amp;tend='.$ppc_global_settings['stats_tend'];
			
			//If filtered by user role, add filter to stats generation args and complete page permalink
			if( isset( $get_and_post['role'] ) ) {
				$page_permalink .= '&amp;role='.$ppc_global_settings['stats_role'];
				add_filter( 'ppc_get_requested_posts_args', array( 'PPC_generate_stats', 'filter_stats_by_user_role' ) );
			}
			
            echo PPC_HTML_functions::show_stats_page_header( __( 'General' , 'post-pay-counter'), admin_url( $page_permalink ) );
            
            $stats = PPC_generate_stats::produce_stats( $ppc_global_settings['stats_tstart'], $ppc_global_settings['stats_tend'] );
            if( is_wp_error( $stats ) ) {
                echo $stats->get_error_message();
                return;
            }
            
			/**
			 * Fires before the *general* stats page form and table been output.
			 *
			 * @since	2.0
			 * @param	array $stats a PPC_generate_stats::produce_stats() result - current stats.
			 */
			
            do_action( 'ppc_html_stats_general_before_stats_form', $stats );
            ?>
			
	<form action="#" method="post" id="ppc_stats">
            
			<?php
			echo PPC_HTML_functions::get_html_stats( $stats['formatted_stats'], $stats['raw_stats'] );
            
			/**
			 * Fires after the *general* stats page form and table been output.
			 *
			 * @since	2.0
			 */
			
            do_action( 'ppc_html_stats_general_after_stats_form' );
        }
        ?>
		
	</form>
	<div class="ppc_table_divider"></div>
		
		<?php
        if( $general_settings['display_overall_stats'] ) {
			$overall_stats = PPC_generate_stats::get_overall_stats( $stats['raw_stats'] );
			echo PPC_HTML_functions::print_overall_stats( $overall_stats );
			
			/**
			 * Fires after the overall stats table been output.
			 *
			 * @since	2.0
			 */
			
			do_action( 'ppc_html_stats_after_overall_stats' );
		}
        ?>
		
</div>
	
	<?php
    }
}

global $ppc_global_settings;
$ppc_global_settings = array();
new post_pay_counter();
?>
