<?php

/**
 * @author      Stefano Ottolenghi
 * @copyright   2023
 * @package     PPC
 */

global $ppc_wp_slimstat_include_status, $ppc_visits_trackers;
add_action( 'init', 'ppc_wp_slimstat_include' );
function ppc_wp_slimstat_include() {
	global $ppc_wp_slimstat_include_status;
    $ppc_wp_slimstat_include_status = false;
    $path = WP_PLUGIN_DIR . '/wp-slimstat/admin/view/wp-slimstat-db.php';
    if ( is_file( $path ) )
		$ppc_wp_slimstat_include_status = @include_once( $path );
}

add_action( 'init', 'ppc_define_visits_trackers' );
function ppc_define_visits_trackers() {
    global $ppc_visits_trackers;
    $ppc_visits_trackers = apply_filters( 'ppc_visits_trackers', array(
        'WordPress plugins' => array(
            'post-views-counter' => array(
                'name' => 'Post Views Counter',
                'callback' => 'ppc_get_post_views_counter_views',
             ),
            'slimstat-analytics' => array(
                'name' => 'Slimstat Analytics',
                'callback' => 'ppc_wp_get_slimstat_views',
            ),
            'wp-postviews' => array(
                'name' => 'WP-PostViews',
                'callback' => 'ppc_get_wp_postviews_views',
             ),
            'active-analytics' => array(
                'name' => 'Active Analytics',
                'callback' => 'ppc_get_active_analytics_views',
             ),
            'wordpress-popular-posts' => array(
                'name' => 'WordPress Popular Posts',
                'callback' => 'ppc_get_wordpress_popular_posts_views',
             ),
            'top-10' => array(
                'name' => 'Top 10',
                'callback' => 'ppc_get_top_10_posts_views',
             ),
        ),
    ) );
}

function ppc_wp_get_slimstat_views( $post ) {
    global $ppc_wp_slimstat_include_status;
    if( ! ( $ppc_wp_slimstat_include_status AND ppc_is_plugin_active( 'wp-slimstat/wp-slimstat.php' ) ) )
        return ppc_default_visits_callback( $post );
    $filters = 'content_id equals ' . $post->ID;
    wp_slimstat_db::init( $filters );
    $post_views = wp_slimstat_db::count_records( 'id', '', false );
	return $post_views;
}

function ppc_get_wp_postviews_views( $post ) {
    if( ! ppc_is_plugin_active( 'wp-postviews/wp-postviews.php' ) )
        return ppc_default_visits_callback( $post );
    $post_views = (int) get_post_meta( $post->ID, 'views', true );
    //if( ! $post_views )
      //  $post_views = 0;
    return $post_views;
}

function ppc_get_post_views_counter_views( $post ) {
    if( ! ppc_is_plugin_active( 'post-views-counter/post-views-counter.php' ) )
        return ppc_default_visits_callback( $post );
    $post_views = pvc_get_post_views( $post->ID );
    return $post_views;
}

function ppc_get_wordpress_popular_posts_views( $post ) {
    if( ! ppc_is_plugin_active( 'wordpress-popular-posts/wordpress-popular-posts.php' ) )
        return ppc_default_visits_callback( $post );
    $post_views = wpp_get_views( $post->ID, 'all', false );
    return $post_views;
}

function ppc_get_active_analytics_views( $post ) {
    if( ! ppc_is_plugin_active( 'active-analytics/active-analytics.php' ) )
        return ppc_default_visits_callback( $post );
    $postmeta_name = get_option( 'wpaa_pageviews_key' );
    $post_views = (int) get_post_meta( $post->ID, $postmeta_name, true );
    return $post_views;
}

function ppc_get_top_10_posts_views( $post ) {
    if( ! ppc_is_plugin_active( 'top-10/top-10.php' ) )
        return ppc_default_visits_callback( $post );
    $post_views = (int) get_tptn_post_count_only( $post->ID );
    return $post_views;
}

function ppc_default_visits_callback( $post ) {
    return -1;
}
