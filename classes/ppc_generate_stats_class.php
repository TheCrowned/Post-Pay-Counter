<?php

/**
 * @author Stefano Ottolenghi
 * @copyright 2013
 */

class PPC_generate_stats {
    
    /**
     * Produces stats by calling all needed methods. 
     * 
     * This is the highest-level method.
     *
     * @access  public
     * @since   2.0.2
     * @param   $time_start int the start time range timestamp
     * @param   $time_end int the end time range timestamp
     * @param   $author array optional an array of users for detailed stats
     * @return  array raw stats + formatted for output stats
     */
    
    static function produce_stats( $time_start, $time_end, $author = NULL ) {
        global $current_user;
        
        $perm = new PPC_permissions();
        
        //If general stats & CU can't see others' general, behave as if detailed for him
        if( ! is_array( $author ) AND ! $perm->can_see_others_general_stats() )
            $requested_posts = PPC_generate_stats::get_requested_posts( $time_start, $time_end, array( $current_user->ID ) );
        else
            $requested_posts = PPC_generate_stats::get_requested_posts( $time_start, $time_end, $author );
        
        if( is_wp_error( $requested_posts ) ) return $requested_posts;
        
        $cashed_requested_posts = PPC_counting_stuff::data2cash( $requested_posts, $author );
        if( is_wp_error( $cashed_requested_posts ) ) return $cashed_requested_posts;
        
        $grouped_by_author_stats = PPC_generate_stats::group_stats_by_author( $cashed_requested_posts );
        if( is_wp_error( $grouped_by_author_stats ) ) return $grouped_by_author_stats;
        
        $formatted_stats = PPC_generate_stats::format_stats_for_output( $grouped_by_author_stats, $author );
        
        unset( $requested_posts, $cashed_requested_posts ); //Hoping to free some memory
        return array( 'raw_stats' => $grouped_by_author_stats, 'formatted_stats' => $formatted_stats );
    }
    
    /**
     * Builds an array of posts to be counted given the timeframe, complete with their data.
     *
     * @access  public
     * @since   2.0
     * @param   $time_start int the start time range timestamp
     * @param   $time_end int the end time range timestamp
     * @param   $author array optional an array of users for detailed stats
     * @return  array the array of WP posts object to be counted
    */
    
    static function get_requested_posts( $time_start, $time_end, $author = NULL ) {
        global $current_user;
        
		if( is_array( $author ) )
			$settings = PPC_general_functions::get_settings( current( $author ), true );
		else
			$settings = PPC_general_functions::get_settings( 'general' );
		
        $args = array(
            'post_type' => $settings['counting_allowed_post_types'],
            'post_status' => array_keys( $settings['counting_allowed_post_statuses'], 1 ), //Only statuses with 1 as value are selected
            'date_query' => array(
                'after' => date( 'Y-m-d H:m:s', $time_start ),
                'before' => date( 'Y-m-d H:m:s', $time_end ),
                'inclusive' => true
            ),
            'orderby' => 'date',
            'order' => 'DESC',
            'posts_per_page' => -1,
            'ignore_sticky_posts' => 1,
            'suppress_filters' => false,
            'ppc_filter_user_roles' => 1
        );
        
        //If a user_id is provided, and is valid, posts only by that author are selected 
        if( is_array( $author ) )
            $args['author__in'] = $author;
        
        $args = apply_filters( 'ppc_get_requested_posts_args', $args );
        
        //Filter for allowed user roles if needed
        if( isset( $args['ppc_filter_user_roles'] ) AND $args['ppc_filter_user_roles'] ) 
            add_filter( 'posts_join', array( 'PPC_generate_stats', 'grp_filter_user_roles' ) );
        
        //Unset all custom params from WP_Query args
		if( isset( $args['ppc_filter_user_roles'] ) )
			unset( $args['ppc_filter_user_roles'] );
        
        $requested_posts = new WP_Query( $args );
		
        //Remove custom filters
        remove_filter( 'posts_join', array( 'PPC_generate_stats', 'grp_filter_user_roles' ) );
        
		do_action( 'ppc_got_requested_posts', $requested_posts );
		
        if( $requested_posts->found_posts == 0 ) {
            $error = new PPC_Error( 'empty_selection', __( 'Error: no posts were selected' , 'ppc'), $args, false );
            return $error->return_error();
        }
        
        return $requested_posts->posts;
    }
    
    /**
     * Filters get_requested_posts query for allowed user roles.
     *
     * @access  public
     * @since   2.24
     * @param   $join string the sql join
     * @return  string the sql join
     */
    
    static function grp_filter_user_roles( $join ) {
        global $wpdb;
        
        $settings = PPC_general_functions::get_settings( 'general' );
        
        $join .= 'INNER JOIN '.$wpdb->usermeta.'
                    ON '.$wpdb->usermeta.'.user_id = '.$wpdb->posts.'.post_author
                    AND '.$wpdb->usermeta.'.meta_key = "'.$wpdb->get_blog_prefix().'capabilities" 
                    AND '.$wpdb->usermeta.'.meta_value REGEXP ("'.implode( '|', $settings['counting_allowed_user_roles'] ).'")';
        
        return $join;
    }
    
    /**
     * Groups posts array by their authors and computes authors total (count+payment)
     *
     * @access  public
     * @since   2.0
     * @param   $data array the counting data
     * @return  array the counting data, grouped by author id
     */
    
    static function group_stats_by_author( $data ) {
        $sorted_array = array();
        
        foreach( $data as $post_id => $single ) {
            $sorted_array[$single->post_author][$post_id] = $single;
            $user_settings = PPC_general_functions::get_settings( $single->post_author, true );
            
            //Written posts count
            if( ! isset( $sorted_array[$single->post_author]['total']['ppc_misc']['posts'] ) )
                $sorted_array[$single->post_author]['total']['ppc_misc']['posts'] = 1;
            else
                $sorted_array[$single->post_author]['total']['ppc_misc']['posts']++;
            
            //Don't include in general stats count posts below threshold
            if( $user_settings['counting_payment_only_when_total_threshold'] ) {
                if( $single->ppc_misc['exceed_threshold'] == false )
                    continue;
            }
            
            //Compute total countings
            foreach( $single->ppc_count['normal_count']['real'] as $what => $value ) {
                //Avoid notices of non isset index
    			if( ! isset( $sorted_array[$single->post_author]['total']['ppc_count']['normal_count']['real'][$what] ) ) {
    				$sorted_array[$single->post_author]['total']['ppc_count']['normal_count']['real'][$what] = $single->ppc_count['normal_count']['real'][$what];
                    $sorted_array[$single->post_author]['total']['ppc_count']['normal_count']['to_count'][$what] = $single->ppc_count['normal_count']['to_count'][$what];
    			} else {
    				$sorted_array[$single->post_author]['total']['ppc_count']['normal_count']['real'][$what] += $single->ppc_count['normal_count']['real'][$what];
                    $sorted_array[$single->post_author]['total']['ppc_count']['normal_count']['to_count'][$what] += $single->ppc_count['normal_count']['to_count'][$what];
    			}
            }
            
            //Compute total payment
            foreach( $single->ppc_payment['normal_payment'] as $what => $value ) {
                //Avoid notices of non isset index
    			if( ! isset( $sorted_array[$single->post_author]['total']['ppc_payment']['normal_payment'][$what] ) )
    				$sorted_array[$single->post_author]['total']['ppc_payment']['normal_payment'][$what] = $value;
    			else
    				$sorted_array[$single->post_author]['total']['ppc_payment']['normal_payment'][$what] += $value;
            }
            
            $sorted_array[$single->post_author] = apply_filters( 'ppc_sort_stats_by_author_foreach_post', $sorted_array[$single->post_author], $single );
        }
        
        foreach( $sorted_array as $author => &$stats ) {
            $user_settings = PPC_general_functions::get_settings( $author, true );
            
            //Check total threshold
            if( $user_settings['counting_payment_total_threshold'] != 0 ) {
                if( $stats['total']['ppc_payment']['normal_payment']['total'] > $stats['total']['ppc_misc']['posts'] * $user_settings['counting_payment_total_threshold'] )
                    $stats['total']['ppc_payment']['normal_payment']['total'] = $stats['total']['ppc_misc']['posts'] * $user_settings['counting_payment_total_threshold'];
            }
            
            //Get tooltip
            //if( isset( $stats['total']['normal_payment'] ) ) { //prevents notice when all counting types are disabled and post stats are requested            
                $stats['total']['ppc_misc']['tooltip_normal_payment'] = PPC_counting_stuff::build_payment_details_tooltip( $stats['total']['ppc_count']['normal_count']['to_count'], $stats['total']['ppc_payment']['normal_payment'] );
            //}
                        
            $stats = apply_filters( 'ppc_sort_stats_by_author_foreach_author', $stats, $author, $user_settings );
            
            //unset( $stats );
        }
        
        return apply_filters( 'ppc_generated_raw_stats', $sorted_array );
    }
    
    /**
     * Makes stats ready for output.
     * 
     * An array is setup containing the heading columns and the rows data. These will be shown on output of any format: html, csv, pdf...
     *
     * @access  public
     * @since   2.0
     * @param   $data array a group_stats_by_author result
     * @param   $author array optional whether detailed stats
     * @return  array the formatted stats
     */
    
    static function format_stats_for_output( $data, $author = NULL ) {
        $formatted_stats = array( 
            'cols' => array(), 
            'stats' => array() 
        );
        
        if( is_array( $author ) ) {
            list( $author_id, $author_stats ) = each( $data ); 
            $post_stats = current( $author_stats ); //get first post object from stats to determine which countings should be shown
			
            $formatted_stats['cols']['post_id'] = __( 'ID' , 'ppc');
            $formatted_stats['cols']['post_title'] = __( 'Title', 'ppc');
            $formatted_stats['cols']['post_type'] = __( 'Type', 'ppc');
            $formatted_stats['cols']['post_status'] = __( 'Status', 'ppc');
            $formatted_stats['cols']['post_publication_date'] = __( 'Pub. Date', 'ppc');
			
			if( isset( $post_stats->ppc_count['normal_count']['real']['words'] ) )
                $formatted_stats['cols']['post_words_count'] = __( 'Words', 'ppc');
            if( isset( $post_stats->ppc_count['normal_count']['real']['visits'] ) )
                $formatted_stats['cols']['post_visits_count'] = __( 'Visits', 'ppc');
            if( isset( $post_stats->ppc_count['normal_count']['real']['comments'] ) )
                $formatted_stats['cols']['post_comments_count'] = __( 'Comments', 'ppc');
            if( isset( $post_stats->ppc_count['normal_count']['real']['images'] ) )
                $formatted_stats['cols']['post_images_count'] = __( 'Imgs', 'ppc');
            
            $formatted_stats['cols']['post_total_payment'] = __( 'Total Pay', 'ppc');
            
            $formatted_stats['cols'] = apply_filters( 'ppc_author_stats_format_stats_after_cols_default', $formatted_stats['cols'] );
            
            foreach( $author_stats as $key => $post ) {
                if( $key === 'total' ) continue; //Skip author's total
                
                $post_date = explode( ' ', $post->post_date );
                
                $formatted_stats['stats'][$author_id][$post->ID]['post_id'] = $post->ID;
                $formatted_stats['stats'][$author_id][$post->ID]['post_title'] = $post->post_title;
                $formatted_stats['stats'][$author_id][$post->ID]['post_type'] = $post->post_type;
                $formatted_stats['stats'][$author_id][$post->ID]['post_status'] = $post->post_status;
                $formatted_stats['stats'][$author_id][$post->ID]['post_publication_date'] = $post_date[0];
                
                //if( isset( $post->ppc_count['normal_count']['real']['basic'] ) )
                    //$formatted_stats['stats'][$author_id][$post->ID]['post_basic_count'] = $post->ppc_count['normal_count']['real']['basic'];
				if( isset( $post->ppc_count['normal_count']['real']['words'] ) )
                    $formatted_stats['stats'][$author_id][$post->ID]['post_words_count'] = $post->ppc_count['normal_count']['real']['words'];
                if( isset( $post->ppc_count['normal_count']['real']['visits'] ) )
                    $formatted_stats['stats'][$author_id][$post->ID]['post_visits_count'] = $post->ppc_count['normal_count']['real']['visits'];
                if( isset( $post->ppc_count['normal_count']['real']['comments'] ) )
                    $formatted_stats['stats'][$author_id][$post->ID]['post_comments_count'] = $post->ppc_count['normal_count']['real']['comments'];
                if( isset( $post->ppc_count['normal_count']['real']['images'] ) )
                    $formatted_stats['stats'][$author_id][$post->ID]['post_images_count'] = $post->ppc_count['normal_count']['real']['images'];
                
                $formatted_stats['stats'][$author_id][$post->ID]['post_total_payment'] = $post->ppc_payment['normal_payment']['total'];
                
                $formatted_stats['stats'][$author_id][$post->ID] = apply_filters( 'ppc_author_stats_format_stats_after_each_default', $formatted_stats['stats'][$author_id][$post->ID], $author_id, $post );
            }
            
        } else {
            $formatted_stats['cols']['author_id'] = __( 'Author ID' , 'ppc');
            $formatted_stats['cols']['author_name'] = __( 'Author Name' , 'ppc');
            $formatted_stats['cols']['author_written_posts'] = __( 'Written posts' , 'ppc');
            $formatted_stats['cols']['author_total_payment'] = __( 'Total payment' , 'ppc');
            
            $formatted_stats['cols'] = apply_filters( 'ppc_general_stats_format_stats_after_cols_default', $formatted_stats['cols'] );
            
            foreach( $data as $author_id => $posts ) {
                $author_data = get_userdata( $author_id );
                
                $formatted_stats['stats'][$author_id]['author_id'] = $author_id;
                $formatted_stats['stats'][$author_id]['author_name'] = $author_data->display_name;
                $formatted_stats['stats'][$author_id]['author_written_posts'] = (int) $posts['total']['ppc_misc']['posts'];
                $formatted_stats['stats'][$author_id]['author_total_payment'] = $posts['total']['ppc_payment']['normal_payment']['total'];
                
                $formatted_stats['stats'][$author_id] = apply_filters( 'ppc_general_stats_format_stats_after_each_default', $formatted_stats['stats'][$author_id], $author_id, $posts );
            }
        }
        
        return apply_filters( 'ppc_formatted_stats', $formatted_stats );
    }
    
    /**
     * Computes overall stats.
     *
     * @access  public
     * @since   2.0
     * @param   $data array a group_stats_by_author result
     * @return  array the overall stats
     */
    
    static function get_overall_stats( $stats ) {
        $overall_stats = array( 
            'posts' => 0, 
            'payment' => 0,
			'count_words' => 0,
			'count_visits' => 0,
			'count_images' => 0,
			'count_comments' => 0
        );
        
        foreach( $stats as $single ) { 
            //Posts total count
			$overall_stats['posts'] += $single['total']['ppc_misc']['posts'];
            
			//Normal payment total
			$overall_stats['payment'] += $single['total']['ppc_payment']['normal_payment']['total'];
			
			//Words total count
			if( isset( $single['total']['ppc_count']['normal_count']['to_count']['words'] ) )
				$overall_stats['count_words'] += $single['total']['ppc_count']['normal_count']['to_count']['words'];
			
			//Visits total count
			if( isset( $single['total']['ppc_count']['normal_count']['to_count']['visits'] ) )
				$overall_stats['count_visits'] += $single['total']['ppc_count']['normal_count']['to_count']['visits'];
			
			//Images total count
			if( isset( $single['total']['ppc_count']['normal_count']['to_count']['images'] ) )
				$overall_stats['count_images'] += $single['total']['ppc_count']['normal_count']['to_count']['images'];
			
			//Comments total count
			if( isset( $single['total']['ppc_count']['normal_count']['to_count']['comments'] ) )
				$overall_stats['count_comments'] += $single['total']['ppc_count']['normal_count']['to_count']['comments'];
        }
        
        return apply_filters( 'ppc_overall_stats', $overall_stats, $stats );
    }
}
?>