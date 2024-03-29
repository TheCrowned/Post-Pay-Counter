<?php

/**
 * Caching functions.
 *
 * @author Stefano Ottolenghi
 * @copyright 2013
 * @package	PPC
 * @since 2.720
 */
class PPC_cache_functions {

	// Little cache help
	public static $incrementor_value;

	/**
     * Clears cache of all settings-dependent objects.
     * Flushing general settings will result in flushing ALL users cache as well.
     * Flushing settings cache includes flushing countint types cache.
     *
     * @access  public
     * @since   2.720
     * @param   $userid string userid whose settings cache needs to be flushed
     */
	static function clear_settings( $userid = 'general' ) {
		wp_cache_delete( 'ppc_settings_'.$userid );
		wp_cache_delete( 'ppc_user_active_counting_types_list_post_'.$userid );
		wp_cache_delete( 'ppc_user_active_counting_types_list_author_'.$userid );
		wp_cache_delete( 'ppc_user_active_counting_types_details_post_'.$userid );
		wp_cache_delete( 'ppc_user_active_counting_types_details_author_'.$userid );

		if( ! is_numeric( $userid ) ) {
			global $wpdb;

			$wp_all_users = get_users( array( 'fields' => array( 'ID' ) ) );

			foreach( $wp_all_users as $user ) {
				self::clear_settings( $user->ID );
			}
		}
	}

	/**
	 * Retrieves post stats, if caching is enabled.
	 *
	 * @since	2.720
	 * @param	$post_id int
	 * @return	mixed cache content or false
	 */
	static function get_post_stats( $post_id ) {
		$cache_salt = PPC_cache_functions::get_stats_incrementor();
        return wp_cache_get( 'ppc_stats_post_ID-'.$post_id.'-'.$cache_salt, 'ppc_stats' );
	}

	/**
	 * Stores post stats, if caching is enabled.
	 *
	 * @since	2.720
	 * @param	$post_id int
	 * @return	mixed cache content or false
	 */
	static function set_post_stats( $post_id, $data ) {
		$cache_salt = PPC_cache_functions::get_stats_incrementor();
		wp_cache_set( 'ppc_stats_post_ID-'.$post_id.'-'.$cache_salt, $data, 'ppc_stats', 86400 );
	}

	/**
	 * Clear stats cache for given post.
	 *
	 * @since 	2.720
	 * @param	$post_id int
	 * @return 	void
	 */
	static function clear_post_stats( $post_id ) {
		 wp_cache_delete( 'ppc_stats_post_ID-'.$post_id.'-'.self::get_stats_incrementor(), 'ppc_stats' );
	}

	/**
	 * Clear all stats cache.
	 *
	 * @since	2.720
	 * @return	void
	 */
	static function clear_stats() {
		self::get_stats_incrementor( true );
	}

	/**
	 * Add actions for clearing post stats cache when needed for old versions of addons.
	 *
	 * @since	2.720
	 * @return 	void
	 */
	static function clear_post_stats_old_addons() {
		global $ppcp_global_settings;
        if( isset( $ppcp_global_settings['current_version'] ) AND version_compare( $ppcp_global_settings['current_version'], '1.7.2' ) ) {
			add_action( 'ppcp_updated_post_payment_history', array( 'PPC_cache_functions', 'clear_post_stats' ), 10, 1 );
		}

        global $ppcp_fb_global_settings;
        if( isset( $ppcp_fb_global_settings['current_version'] ) AND version_compare( $ppcp_fb_global_settings['current_version'], '1.4.1' ) ) {
			add_action( 'ppcp_fb_updated_post_data', array( 'PPC_cache_functions', 'clear_post_stats' ), 10, 1 );
		}
	}

	/**
	 * Gets (and updates) incrementor for invalidating stats cache group.
	 *
	 * See https://www.tollmanz.com/invalidation-schemes/ for info on how it works.
	 *
	 * @since	2.720
	 * @param	$refresh bool whether to refresh the incrementor
	 * @return 	string incrementor current value
	 */
	static function get_stats_incrementor( $refresh = false ) {
		if( self::$incrementor_value AND ! $refresh )
			return self::$incrementor_value;
        
		global $ppc_global_settings;
		$incrementor_key = $ppc_global_settings['option_stats_cache_incrementor'];
		$incrementor_value = get_option( $incrementor_key );

		if( $incrementor_value === false OR $refresh === true ) {
			$incrementor_value = time();
			update_option( $incrementor_key, $incrementor_value );
		}
        
		self::$incrementor_value = $incrementor_value;
		return $incrementor_value;
	}

	/**
	 * Get full cache snapshot if available.
	 *
	 * @since 	2.755
	 * @param 	$slug string cache slug (also file name)
	 * @return 	$cached_data array unserialized cache file content (whole of it!)
	 */
	static function get_full_stats( $slug ) {
		global $ppc_global_settings;

		$path = $ppc_global_settings['dir_path'].'cache/'.$slug;

		if( is_file( $path ) AND filesize( $path ) != 0 ) {
			$open = fopen( $path, "r" );

			$file_content = fread( $open, filesize( $path ) );
			if( $file_content !== false ) {
				$cached_data = unserialize( $file_content );
				PPC_counting_stuff::$settings = PPC_general_functions::get_settings( 'general' ); //put some settings there (hack!), since we never go through data2cash()
				return $cached_data;
			}
		}
	}

    /**
     * Retrieve cached stats snapshot, if available.
     * 
     * Cache snapshots can only be generated through WP-CLI command `wp ppc stats --cache-full`.
     * 
     * @since   2.770
     * @param   $time_start int
     * @param   $time_end int
     * @param   $author array|NULL
     * @return  stats_array|false
     */
    static function get_stats_snapshot( $time_start, $time_end, $author ) {
        // Disable cache snapshot with GET arg `no-cache`, or by hooking to this filter, or when creating snapshots through WP-CLI
        global $CLI_PPC_CACHE;
        if( isset( $CLI_PPC_CACHE ) OR apply_filters( 'ppc_cache_snapshots_default_noload', isset( $_GET['no-cache'] ) ) )
            return false;

        global $ppc_global_settings, $current_user;
        $perm = new PPC_permissions();

        // Build snapshot slug, used as cache filename
        $cache_slug = 'ppc_stats-tstart_'.$time_start.'-tend_'.$time_end;
		if( ! $perm->can_see_countings_special_settings() )
			$cache_slug .= '-as-user_'.$current_user->ID;
		if( is_array( $author ) )
			$cache_slug .= '-author_'.$author[0];

        // Load cached snapshot from file
        $path = $ppc_global_settings['dir_path'].'cache/'.$cache_slug;
		if( is_file( $path ) AND filesize( $path ) != 0 ) {
			$open = fopen( $path, "r" );
			$file_content = fread( $open, filesize( $path ) );
			if( $file_content !== false ) {
				$cached_data = unserialize( $file_content );
				PPC_counting_stuff::$settings = PPC_general_functions::get_settings( 'general' ); // put some settings there (hack!), since we never go through data2cash()
				set_transient( 'ppc_full_stats_snapshot_time', $cached_data['time'], 5 ); // for stats page header to know data is from cache, hacky
                return $cached_data;
			}
		}

        return false;
    }
}
