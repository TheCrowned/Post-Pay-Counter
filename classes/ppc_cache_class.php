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
		$general_settings = PPC_general_functions::get_settings( 'general' );

		//$cache_salt = PPC_cache_functions::get_stats_incrementor();

		if( $general_settings['enable_post_stats_caching'] )
			return wp_cache_get( 'ppc_stats_post_ID-'.$post_id, 'ppc_stats' );
		else
			return false;
	}

	/**
	 * Clear stats cache for given post.
	 *
	 * @since 	2.720
	 * @param	$post_id int
	 * @return 	void
	 */
	static function clear_post_stats( $post_id ) {
		 wp_cache_delete( 'ppc_stats_post_ID-'.$post_id, 'ppc_stats');//-'.self::get_stats_incrementor() );
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
		$incrementor_key = 'ppc_stats_cache_incrementor';
		$incrementor_value = wp_cache_get( $incrementor_key );
	 
		if( $incrementor_value === false OR $refresh === true ) {
			$incrementor_value = time();
			wp_cache_set( $incrementor_key, $incrementor_value );
		}
	 
		return $incrementor_value;
	}
}
