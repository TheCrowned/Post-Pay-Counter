<?php
/**
 * Update
 *
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
	    'display_overall_stats' => 1,
	    'counting_visits_callback_value' => '',
	    'admins_override_permissions' => 0,
	    'basic_payment_display_status' => 'tooltip',
	    'counting_words_display_status' => 'count',
	    'counting_words_include_excerpt' => 0,
	    'counting_words_legacy' => 1,
	    'counting_visits_display_status' => 'count',
	    'counting_images_display_status' => 'count',
	    'counting_comments_display_status' => 'count',
	    'stats_display_edit_post_link' => 0,
	    'counting_words_parse_spaces' => 0,
	    'enable_stats_payments_tooltips' => 1,
	    'default_stats_time_range_last_month' => 0,
	    'default_stats_time_range_this_year' => 0,
	    'default_stats_time_range_all_time' => 0,
	    'default_stats_time_range_start_day' => 0,
	    'default_stats_time_range_start_day_value' => '1605-11-05',
	    'enable_post_stats_caching' => 1,
	    'payment_display_round_digits' => 2,
	    'save_stats_order' => 1,
	    'hide_column_total_payment' => 0,
	    'counting_words_global_threshold' => 0,
	    'counting_visits_global_threshold' => 0,
	    'counting_comments_global_threshold' => 0,
	    'counting_images_global_threshold' => 0,
	    'counting_words_exclude_pre' => 0,
	    'counting_words_exclude_captions' => 0,
	    'counting_words_apply_shortcodes' => 0,
	    'counting_visits_display_percentage' => 100,
	    'stats_show_all_users' => 0,
        );

        foreach( $new_settings as $setting => $value ) {
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

	//Migrate visits trackers
	if( ! isset( $general_settings['counting_visits_tracker'] ) ) {
	    if( $general_settings['counting_visits_callback'] == 'ppc_wp_slimstat_views' ) {
		$general_settings['counting_visits_tracker'] = 'slimstat-analytics';
		$general_settings['counting_visits_ppc_supported_tracker'] = 1;
		$general_settings['counting_visits_callback'] = 0;
	    }
	    if( $general_settings['counting_visits_callback'] == 'ppc_pvc_views' ) {
		$general_settings['counting_visits_tracker'] = 'post-views-counter';
		$general_settings['counting_visits_ppc_supported_tracker'] = 1;
		$general_settings['counting_visits_callback'] = 0;
	    }
	    if( $general_settings['counting_visits_postmeta'] == 'views' ) {
		$general_settings['counting_visits_tracker'] = 'wp-postviews';
		$general_settings['counting_visits_ppc_supported_tracker'] = 1;
		$general_settings['counting_visits_postmeta'] = 0;
	    }
	    if( isset( $general_settings['counting_visits_google_analytics'] ) AND $general_settings['counting_visits_google_analytics'] ) {
		$general_settings['counting_visits_tracker'] = 'google-universal-analytics';
		$general_settings['counting_visits_ppc_supported_tracker'] = 1;
		$general_settings['counting_visits_callback'] = 0;
	    }
	    if( isset( $general_settings['counting_visits_matomo'] ) AND $general_settings['counting_visits_matomo'] ) {
		$general_settings['counting_visits_tracker'] = 'matomo-analytics';
		$general_settings['counting_visits_ppc_supported_tracker'] = 1;
		$general_settings['counting_visits_callback'] = 0;
	    }
	    if( isset( $general_settings['counting_visits_plausible'] ) AND $general_settings['counting_visits_plausible'] ) {
		$general_settings['counting_visits_tracker'] = 'plausible-analytics';
		$general_settings['counting_visits_ppc_supported_tracker'] = 1;
		$general_settings['counting_visits_callback'] = 0;
	    }
	}

        if( $general_settings != $general_settings_old ) {
            if( ! update_option( $ppc_global_settings['option_name'], $general_settings ) ) {
                $error = new PPC_Error( 'ppcp_update_settings_error', __( 'Error: could not update settings.', 'post-pay-counter' ), array(
                    'count_before' => count( $general_settings_old ),
                    'count_after' => count( $general_settings ),
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

	//License cron check
        if( ! wp_next_scheduled( 'ppcp_cron_check_activation' ) )
	    wp_schedule_event( time(), 'weekly2', 'ppcp_cron_check_activation' );

	PPC_general_functions::manage_cap_allowed_user_roles_plugin_pages( $general_settings['can_see_options_user_roles'], $general_settings['can_see_stats_user_roles'] );

	//Insert default addons list
        PPC_addons::add_addons_list();

        //Delete old errors wp_option, moved to a file
        $old_errors = get_option( $ppc_global_settings['option_errors'] );
        if( $old_errors !== false AND ! empty( $old_errors ) )
	    file_put_contents( $ppc_global_settings['file_errors'], serialize( $old_errors ) );

	delete_option( $ppc_global_settings['option_errors'] );

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

	//Clear settings cache
        PPC_cache_functions::clear_settings( 'general' );
        PPC_cache_functions::clear_stats();
    }
}
