<?php

/**
 * @author Stefano Ottolenghi
 * @copyright 2013
 */

//Uninstall must have been triggered by WordPress
if( ! defined( 'WP_UNINSTALL_PLUGIN' ) )
    exit;

global $wpdb;

function ppc_uninstall_procedure() {
    if( get_option( 'ppc_current_version' ) )
        delete_option( 'ppc_current_version' );
    
    if( get_option( 'ppc_settings' ) )
        delete_option( 'ppc_settings' );
        
	$all_users = get_users( 'fields=ID' );
	foreach( $all_users as $user_id ) {
		delete_user_option( $user_id, 'ppc_settings' );
	}

if( get_option( 'ppc_dismissed_notifications' ) )
        delete_option( 'ppc_dismissed_notifications' );
}

//If working on a multisite blog, get all blog ids, foreach them and call the uninstall procedure on each of them
if( function_exists( 'is_multisite' ) AND is_multisite() ) {
    global $wpdb;
	
	$blog_ids = $wpdb->get_col( 'SELECT blog_id FROM '.$wpdb->blogs );
    foreach( $blog_ids as $blog_id ) {
		switch_to_blog( $blog_id );
        ppc_uninstall_procedure();
	}
    
	restore_current_blog();
	return;
}

ppc_uninstall_procedure();
?>