<?php
/*
Plugin Name: Post Pay Counter - Publisher Bonus Days Exclude
Plugin URI: http://postpaycounter.com/disregard-shortcodes-when-computing-word-payment
Description: Strips shortcodes from content before counting its words.
Author: Stefano Ottolenghi
Version: 1.0
Author URI: http://www.thecrowned.org/
*/

//Just type in the days in which publishings should not be recorded. Write only the first three letters, the first must be uppercase. Separate days with a comma.
global $ppcp_pb_days_to_exclude;
$ppcp_pb_days_to_exclude = "Mon, Tue, Wed, Thu, Fri";

/**
 * Works by temporarily fooling WP into thinking there's just one shortcode (the one to be deleted) and use its methods to clean the post.
 *
 * @param WP_Post $post
 */
function ppcp_pb_exclude_days( $new_bonus, $post ) {
    global $ppcp_pb_days_to_exclude;

    $days = explode( ",", $ppcp_pb_days_to_exclude );

    if( in_array( $days, date( 'D' ) ) )
		return array();
	else
		return $new_bonus;
}

add_filter( 'ppcp_pb_set_post_publisher_before', 'ppcp_pb_exclude_days', 10, 2 );
