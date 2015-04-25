<?php

/**
 * Stats generation.
 *C:\Users\Stefano>phpdoc -d E:\htdocs\wordpress\wp-content\plugins\Post-Pay-Counter -t E:\phpdoc --ignore branches/
 * @package		PPC
 * @since		2.0
 * @author 		Stefano Ottolenghi
 * @copyright 	2013
 */

class PPC_generate_stats {
    
	/**
	 * @var 	array $grp_args holds get_requested_posts WP_Query args.
	 * @since	2.49
	 */
	
	public static $grp_args;
	
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
        global $current_user, $ppc_global_settings;
        
        $perm = new PPC_permissions();
		
        //If general stats & CU can't see others' general, behave as if detailed for him
        if( ! is_array( $author ) AND ! $perm->can_see_others_general_stats() )
            $requested_posts = PPC_generate_stats::get_requested_posts( $time_start, $time_end, array( $current_user->ID ) );
        else
            $requested_posts = PPC_generate_stats::get_requested_posts( $time_start, $time_end, $author );
        
        if( is_wp_error( $requested_posts ) ) return $requested_posts;
        
        $cashed_requested_posts = PPC_counting_stuff::data2cash( $requested_posts, $author );
        if( is_wp_error( $cashed_requested_posts ) ) return $cashed_requested_posts;
        
		if( empty( $cashed_requested_posts ) ) {
            $error = new PPC_Error( 'data2cash_empty', __( 'Error: no posts were selected' , 'ppc'), array(), false );
            return $error->return_error();
        }
		
        $grouped_by_author_stats = PPC_generate_stats::group_stats_by_author( $cashed_requested_posts );
        if( is_wp_error( $grouped_by_author_stats ) ) return $grouped_by_author_stats;
        
        $formatted_stats = PPC_generate_stats::format_stats_for_output( $grouped_by_author_stats, $author );
        if( is_wp_error( $formatted_stats ) ) return $formatted_stats;
		
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
        
		$general_settings = PPC_general_functions::get_settings( 'general' );
		
		if( is_array( $author ) )
			$settings = PPC_general_functions::get_settings( current( $author ), true );
		else
			$settings = $general_settings;
		
        self::$grp_args = array(
            'post_type' => $general_settings['counting_allowed_post_types'],
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
            'ppc_filter_user_roles' => 1,
			'ppc_allowed_user_roles' => $settings['counting_allowed_user_roles']
        );
        
        //If a user_id is provided, and is valid, posts only by that author are selected 
        if( is_array( $author ) )
            self::$grp_args['author__in'] = $author;
        
        self::$grp_args = apply_filters( 'ppc_get_requested_posts_args', self::$grp_args );
        
        //Filter for allowed user roles if needed
        if( isset( self::$grp_args['ppc_filter_user_roles'] ) AND self::$grp_args['ppc_filter_user_roles'] ) 
            add_filter( 'posts_join', array( 'PPC_generate_stats', 'grp_filter_user_roles' ) );
        
        $requested_posts = new WP_Query( self::$grp_args );
		
        //Remove custom filters
        remove_filter( 'posts_join', array( 'PPC_generate_stats', 'grp_filter_user_roles' ) );
        
		do_action( 'ppc_got_requested_posts', $requested_posts );
		
		//var_dump($requested_posts);
		
        if( $requested_posts->found_posts == 0 ) {
            $error = new PPC_Error( 'empty_selection', __( 'Error: no posts were selected' , 'ppc' ), self::$grp_args, false );
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
        
		$join .= 'INNER JOIN '.$wpdb->usermeta.'
                    ON '.$wpdb->usermeta.'.user_id = '.$wpdb->posts.'.post_author
                    AND '.$wpdb->usermeta.'.meta_key = "'.$wpdb->get_blog_prefix().'capabilities" 
                    AND '.$wpdb->usermeta.'.meta_value REGEXP ("'.implode( '|', self::$grp_args['ppc_allowed_user_roles'] ).'")';
        
        return $join;
    }
    
	/**
	 * Applies stats filter by user role.
	 *
	 * Hooks to ppc_get_requested_posts_args - PPC_generate_stats::get_requested_posts().
	 *
	 * @since	2.49
	 * @param	array $grp_args get_requested_posts WP_Query args
	 * @return	array get_requested_posts WP_Query args
	 */
	
	static function filter_stats_by_user_role( $grp_args ) {
		global $ppc_global_settings;
		
		if( isset( $grp_args['ppc_allowed_user_roles'] ) )
			$grp_args['ppc_allowed_user_roles'] = array( $ppc_global_settings['stats_role'] );
		
		return $grp_args;
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
        global $ppc_global_settings;
		
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
            foreach( $single->ppc_count['normal_count'] as $what => $value ) {
                //Avoid notices of non isset index
    			if( ! isset( $sorted_array[$single->post_author]['total']['ppc_count']['normal_count'][$what] ) ) {
    				$sorted_array[$single->post_author]['total']['ppc_count']['normal_count'][$what]['real'] = $single->ppc_count['normal_count'][$what]['real'];
                    $sorted_array[$single->post_author]['total']['ppc_count']['normal_count'][$what]['to_count'] = $single->ppc_count['normal_count'][$what]['to_count'];
    			} else {
    				$sorted_array[$single->post_author]['total']['ppc_count']['normal_count'][$what]['real'] += $single->ppc_count['normal_count'][$what]['real'];
                    $sorted_array[$single->post_author]['total']['ppc_count']['normal_count'][$what]['to_count'] += $single->ppc_count['normal_count'][$what]['to_count'];
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
            
			$author_counting_types = $ppc_global_settings['counting_types_object']->get_active_counting_types( 'author', $author );
			foreach( $author_counting_types as $id => $single_counting ) {
				//Counting
				$counting_type_count = 0;
				if( ! isset( $single_counting['payment_only'] ) OR $single_counting['payment_only'] == false ) {
					$counting_type_count = call_user_func( $single_counting['count_callback'], $stats, $author );
					$stats['total']['ppc_count']['normal_count'][$id] = $counting_type_count;
				}
				
				//Payment
				$counting_type_payment = call_user_func( $single_counting['payment_callback'], $counting_type_count );
				$stats['total']['ppc_payment']['normal_payment'][$id] = $counting_type_payment;
				$stats['total']['ppc_payment']['normal_payment']['total'] += $counting_type_payment;
			}
			
            //Check total threshold
            if( $user_settings['counting_payment_total_threshold'] != 0 ) {
                if( $stats['total']['ppc_payment']['normal_payment']['total'] > $stats['total']['ppc_misc']['posts'] * $user_settings['counting_payment_total_threshold'] )
                    $stats['total']['ppc_payment']['normal_payment']['total'] = $stats['total']['ppc_misc']['posts'] * $user_settings['counting_payment_total_threshold'];
            }
            
            $stats = apply_filters( 'ppc_sort_stats_by_author_foreach_author', $stats, $author );
        }
		
		//Build payment tooltip
		foreach( $sorted_array as $author => &$stats ) {
			$stats['total']['ppc_misc']['tooltip_normal_payment'] = PPC_counting_stuff::build_payment_details_tooltip( $stats['total']['ppc_count']['normal_count'], $stats['total']['ppc_payment']['normal_payment'] );
			$stats['total']['ppc_misc'] = apply_filters( 'ppc_stats_author_misc', $stats['total']['ppc_misc'], $author, $stats );
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
        global $ppc_global_settings;
		
		$formatted_stats = array( 
            'cols' => array(), 
            'stats' => array() 
        );
        
        if( is_array( $author ) ) {
            list( $author_id, $author_stats ) = each( $data ); 
			
			//if( empty( $author_stats ) ) return;
			
            $post_stats = current( $author_stats ); //get first post object from stats to determine which countings should be shown
			$counting_types = $ppc_global_settings['counting_types_object']->get_active_counting_types( 'post', $author_id );
			
            $formatted_stats['cols']['post_id'] = __( 'ID' , 'ppc');
            $formatted_stats['cols']['post_title'] = __( 'Title', 'ppc');
            $formatted_stats['cols']['post_type'] = __( 'Type', 'ppc');
            $formatted_stats['cols']['post_status'] = __( 'Status', 'ppc');
            $formatted_stats['cols']['post_publication_date'] = __( 'Pub. Date', 'ppc');
			
			foreach( $post_stats->ppc_payment['normal_payment'] as $id => $value ) {
				if( $id == 'total' ) continue;
				
				if( isset( $counting_types[$id] ) ) {
					switch( $counting_types[$id]['display'] ) {
						case 'none':
							//nothing to display here
							break;
						
						default:
							$formatted_stats['cols']['post_'.$id] = $counting_types[$id]['label'];
							break;
					}
				}	
			}
            
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
                
				foreach( $post->ppc_payment['normal_payment'] as $id => $value ) {
					if( isset( $counting_types[$id] ) ) {
						switch( $counting_types[$id]['display'] ) {
							case 'both':
								$formatted_stats['stats'][$author_id][$post->ID]['post_'.$id] = $post->ppc_count['normal_count'][$id]['real'].' ('.PPC_general_functions::format_payment( sprintf( '%.2f', $value ) ).')';
								break;
							
							case 'count':
								$formatted_stats['stats'][$author_id][$post->ID]['post_'.$id] = $post->ppc_count['normal_count'][$id]['real'];
								break;
							
							case 'payment':
								$formatted_stats['stats'][$author_id][$post->ID]['post_'.$id] = PPC_general_functions::format_payment( sprintf( '%.2f', $value ) );
								break;
							
							case 'none':
								//nothing to display here
								break;
						}
					}
				}
                
                $formatted_stats['stats'][$author_id][$post->ID]['post_total_payment'] = PPC_general_functions::format_payment( sprintf( '%.2f', $post->ppc_payment['normal_payment']['total'] ) );
                
                $formatted_stats['stats'][$author_id][$post->ID] = apply_filters( 'ppc_author_stats_format_stats_after_each_default', $formatted_stats['stats'][$author_id][$post->ID], $author_id, $post );
            }
            
        } else {
			$cols_info = array(); //holds info about columns. We build cols list after stats taking all unique cnt types enabled across all users. A user may have some counting types unabled, so we can't know before the end all the possible cols we may need
			
            foreach( $data as $author_id => $posts ) {
                if( ! isset( $posts['total']['ppc_payment']['normal_payment'] ) OR empty ( $posts['total']['ppc_payment']['normal_payment'] ) ) continue; //user with no counting types enabled
				
				$author_data = get_userdata( $author_id );
                $post_counting_types = $ppc_global_settings['counting_types_object']->get_active_counting_types( 'post', $author_id );
				$author_counting_types = $ppc_global_settings['counting_types_object']->get_active_counting_types( 'author', $author_id );
				$counting_types = array_merge( $post_counting_types, $author_counting_types );
				
                $formatted_stats['stats'][$author_id]['author_id'] = $author_id;
                $formatted_stats['stats'][$author_id]['author_name'] = $author_data->display_name;
                $formatted_stats['stats'][$author_id]['author_written_posts'] = (int) $posts['total']['ppc_misc']['posts'];
                
				foreach( $posts['total']['ppc_payment']['normal_payment'] as $id => $value ) {
					if( isset( $counting_types[$id] ) ) {
						switch( $counting_types[$id]['display'] ) {
							case 'both':
								$formatted_stats['stats'][$author_id]['author_'.$id] = $posts['total']['ppc_count']['normal_count'][$id]['real'].' ('.PPC_general_functions::format_payment( sprintf( '%.2f', $value ) ).')';
								break;
							
							case 'count':
								$formatted_stats['stats'][$author_id]['author_'.$id] = $posts['total']['ppc_count']['normal_count'][$id]['real'];
								break;
							
							case 'payment':
								$formatted_stats['stats'][$author_id]['author_'.$id] = PPC_general_functions::format_payment( sprintf( '%.2f', $value ) );
								break;
							
							case 'none':
								//nothing to display here
								break;
						}
						
						if( ! isset( $cols['counting_types'][$id] ) )
							$cols_info['counting_types'][$id] = $counting_types[$id];
					}
				}
				
				//Keep track of maximum cols count - !! bugged !! if different cnt types are enabled, but in the same number, they are not all displayed
				/*$current_count = count( $formatted_stats['stats'][$author_id] );
				if( empty( $cols_info ) OR $cols_info['count'] < $current_count ) {
					$cols_info['count'] = $current_count;
					$cols_info['author_id'] = $author_id;
					$cols_info['counting_types'] = $counting_types;
				}
				
				unset( $current_count );*/
				
				$formatted_stats['stats'][$author_id]['author_total_payment'] = PPC_general_functions::format_payment( sprintf( '%.2f', $posts['total']['ppc_payment']['normal_payment']['total'] ) );
                
                $formatted_stats['stats'][$author_id] = apply_filters( 'ppc_general_stats_format_stats_after_each_default', $formatted_stats['stats'][$author_id], $author_id, $posts );
            }
			
			if( count( $formatted_stats['stats'] ) == 0 ) {
				$error = new PPC_Error( 'no_author_with_total_payment', 'No posts reach the threshold.' );
				return $error->return_error();
			}
			
			//COLUMNS
			$formatted_stats['cols']['author_id'] = __( 'Author ID' , 'ppc');
            $formatted_stats['cols']['author_name'] = __( 'Author Name' , 'ppc');
            $formatted_stats['cols']['author_written_posts'] = __( 'Written posts' , 'ppc');
            
			foreach( $cols_info['counting_types'] as $id => $cnt_type ) {
				switch( $cnt_type['display'] ) {
					case 'none':
						//nothing to display here
						break;
					
					default:
						$formatted_stats['cols']['author_'.$id] = $cnt_type['label'];
						break;
				}
			}
			
			$formatted_stats['cols']['author_total_payment'] = __( 'Total payment' , 'ppc');
			$formatted_stats['cols'] = apply_filters( 'ppc_general_stats_format_stats_after_cols_default', $formatted_stats['cols'] );
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
			'count' => array()
        );
        
        foreach( $stats as $single ) { 
            //Posts total count
			$overall_stats['posts'] += $single['total']['ppc_misc']['posts'];
            
			//Total payment
			$overall_stats['payment'] += $single['total']['ppc_payment']['normal_payment']['total'];
			
			//Total counts
			if( isset( $single['total'] ) AND isset( $single['total']['ppc_count'] ) AND isset( $single['total']['ppc_count']['normal_count'] ) ) {
				foreach( $single['total']['ppc_count']['normal_count'] as $single => $data ) {
					if( ! isset( $overall_stats['count'][$single] ) )
						$overall_stats['count'][$single] = $data['to_count'];
					else
						$overall_stats['count'][$single] += $data['to_count'];
				}
			}
			
			//Total payments
			/*foreach( $single['total']['ppc_count']['normal_count'] as $single => $data ) {
				if( ! isset( $overall_stats['count_'.$single] ) )
					$overall_stats['payment_'.$single] = $data;
				else
					$overall_stats['payment_'.$single] += $data;
			}*/
        }
        
        return apply_filters( 'ppc_overall_stats', $overall_stats, $stats );
    }
}
?>