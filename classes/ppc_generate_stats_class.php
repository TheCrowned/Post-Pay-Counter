<?php

/**
 * Stats generation.

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
     * @param	$format bool whether output stats should include formatted stats
     * @return  array raw stats + formatted for output stats
     */
    static function produce_stats( $time_start, $time_end, $author = NULL, $format = true ) {
        global $current_user, $ppc_global_settings;

		$return = array();
        $perm = new PPC_permissions();

		//If there are full-cache stats available, use them. These can be generated only through WP-CLI
		if( ! is_array( $author ) AND ! $perm->can_see_others_general_stats() )
			$cache_slug = 'ppc_stats-tstart_'.$time_start.'-tend_'.$time_end.'-author_'.$current_user->ID.'-as-user'.$current_user->ID;
		else if( is_array( $author ) )
			$cache_slug = 'ppc_stats-tstart_'.$time_start.'-tend_'.$time_end.'-author_'.$author[0].'-as-user_'.$author[0];
		else
			$cache_slug = 'ppc_stats-tstart_'.$time_start.'-tend_'.$time_end.'-as-user_'.$current_user->ID;

		if( apply_filters( 'ppc_cache_full_stats_always_show', isset( $_GET['cache-full'] ) ) ) {
			$cached_data = PPC_cache_functions::get_full_stats( $cache_slug );

			if( is_array( $cached_data ) ) {
				set_transient( 'ppc_full_stats_snapshot_time', $cached_data['time'], 60 );
				return $cached_data['stats'];
			}
		}

		//If general stats & CU can't see others' general, behave as if detailed for him
		if( ! is_array( $author ) AND ! $perm->can_see_others_general_stats() )
			$requested_posts = PPC_generate_stats::get_requested_posts( $time_start, $time_end, array( $current_user->ID ) );
		else
			$requested_posts = PPC_generate_stats::get_requested_posts( $time_start, $time_end, $author );

		if( is_wp_error( $requested_posts ) ) return $requested_posts;

		$stats = PPC_generate_stats::group_stats_by_author( $requested_posts );
		if( is_wp_error( $stats ) ) return $stats;

		$stats = PPC_counting_stuff::data2cash( $stats, $author );
		if( is_wp_error( $stats ) ) return $stats;

		$stats = PPC_generate_stats::calculate_total_stats( $stats );
		if( is_wp_error( $stats ) ) return $stats;

		$return['raw_stats'] = $stats;

		if( $format ) {
			$formatted_stats = PPC_generate_stats::format_stats_for_output( $stats, $author );
			if( is_wp_error( $formatted_stats ) ) return $formatted_stats;

			$return['formatted_stats'] = $formatted_stats;
		}

        return $return;
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
		$general_settings = PPC_general_functions::get_settings( 'general' );

        self::$grp_args = array(
            'post_type' => $general_settings['counting_allowed_post_types'],
            'post_status' => array( 'publish', 'pending', 'future', 'private' ),
            'date_query' => array(
                'after' => date( 'Y-m-d H:i:s', $time_start ),
                'before' => date( 'Y-m-d H:i:s', $time_end ),
                'inclusive' => true
            ),
            'orderby' => 'date',
            'order' => 'DESC',
            'posts_per_page' => -1,
            'ignore_sticky_posts' => 1,
            'suppress_filters' => false,
            'ppc_filter_user_roles' => 1,
			'ppc_allowed_user_roles' => $general_settings['counting_allowed_user_roles']
        );

        //If a user_id is provided, and is valid, posts only by that author are selected
        if( is_array( $author ) )
            self::$grp_args['author__in'] = $author;

        self::$grp_args = apply_filters( 'ppc_get_requested_posts_args', self::$grp_args );

        //Filter for allowed user roles if needed
        if( isset( self::$grp_args['ppc_filter_user_roles'] ) AND self::$grp_args['ppc_filter_user_roles'] )
            add_filter( 'posts_join', array( 'PPC_generate_stats', 'grp_filter_user_roles' ), 10, 2 );

        $requested_posts = new WP_Query( self::$grp_args );

        //Remove custom filters
        remove_filter( 'posts_join', array( 'PPC_generate_stats', 'grp_filter_user_roles' ) );

		do_action( 'ppc_got_requested_posts', $requested_posts );

        if( $requested_posts->found_posts == 0 ) {
            $error = new PPC_Error( 'ppc_empty_selection', __( 'Your query resulted in an empty result. Try to select a wider time range!' , 'post-pay-counter' ), self::$grp_args, false );
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
     * Groups posts array by their authors.
     *
     * @access  public
     * @since   2.519
     * @param   $data array the counting data
     * @return  array the counting data, grouped by author id
     */
    static function group_stats_by_author( $data ) {
        $sorted_array = array();
        foreach( $data as $post_id => $single )
            $sorted_array[$single->post_author][$post_id] = $single;

        return apply_filters( 'ppc_grouped_by_author_stats', $sorted_array );
    }

    /**
     * Computes authors total (count+payment)
     *
     * @access  public
     * @since   2.519
     * @param   $data array the counting data grouped by author
     * @return  array the counting data, with totals
     */
    static function calculate_total_stats( $data ) {
        global $ppc_global_settings;

        $general_settings = PPC_general_functions::get_settings( 'general' );

        foreach( $data as $author_id => $author_stats ) {
			$user_settings = PPC_general_functions::get_settings( $author_id, true );

			//Make sure stats arrays always exist in a complete form, even though empty
			//if( ! isset( $author_stats['total']['ppc_payment']['normal_payment'] ) )
				$data[$author_id]['total']['ppc_payment']['normal_payment'] = array();

			//if( ! isset( $author_stats['total']['ppc_count']['normal_count'] ) )
				$data[$author_id]['total']['ppc_count']['normal_count'] = array();

			foreach( $author_stats as $post_id => $single ) {

				//Written posts count
				if( ! isset( $data[$author_id]['total']['ppc_misc']['posts'] ) )
					$data[$author_id]['total']['ppc_misc']['posts'] = 1;
				else
					$data[$author_id]['total']['ppc_misc']['posts']++;

				//Don't include in general stats count posts below threshold
				if( $user_settings['counting_payment_only_when_total_threshold'] ) {
					if( $single->ppc_misc['exceed_threshold'] == false )
						continue;
				}

				//Compute total countings
				foreach( $single->ppc_count['normal_count'] as $what => $value ) {
					//Avoid notices of non isset index
					if( ! isset( $data[$author_id]['total']['ppc_count']['normal_count'][$what] ) ) {
						$data[$author_id]['total']['ppc_count']['normal_count'][$what]['real'] = $single->ppc_count['normal_count'][$what]['real'];
						$data[$author_id]['total']['ppc_count']['normal_count'][$what]['to_count'] = $single->ppc_count['normal_count'][$what]['to_count'];
					} else {
						$data[$author_id]['total']['ppc_count']['normal_count'][$what]['real'] += $single->ppc_count['normal_count'][$what]['real'];
						$data[$author_id]['total']['ppc_count']['normal_count'][$what]['to_count'] += $single->ppc_count['normal_count'][$what]['to_count'];
					}
				}

				//Compute total payment
				foreach( $single->ppc_payment['normal_payment'] as $what => $value ) {
					//Avoid notices of non isset index
					if( ! isset( $data[$author_id]['total']['ppc_payment']['normal_payment'][$what] ) )
						$data[$author_id]['total']['ppc_payment']['normal_payment'][$what] = $value;
					else
						$data[$author_id]['total']['ppc_payment']['normal_payment'][$what] += $value;
				}

				$data[$author_id] = apply_filters( 'ppc_sort_stats_by_author_foreach_post', $data[$author_id], $single );
			}
		}

		//Add all users to stats so that author payment criteria may be applied even with no written posts
		if( $ppc_global_settings['current_page'] == 'stats_general' AND $general_settings['stats_show_all_users'] ) {
			$all_users = get_users( array( 'fields' => array( 'ID' ), 'number' => -1 ) );

			foreach( $all_users as $user ) {
				$ID = $user->ID;

				if( isset( $data[$ID] ) ) continue; //already in stats, don't override!

				//Set up empty total record
				$data[$ID]['total'] = array(
					'ppc_count' => array(
						'normal_count' => array( )
					),
					'ppc_payment' => array(
						'normal_payment' => array( 'total' => 0 )
					),
					'ppc_misc' => array( 'posts' => 0 ),
				);

				$data[$ID]['total']['ppc_misc']['posts'] = 0;
			}
		}

		//AUTHOR COUNTING TYPES
        foreach( $data as $author => &$stats ) {
            $user_settings = PPC_general_functions::get_settings( $author, true );

			$author_counting_types = $ppc_global_settings['counting_types_object']->get_active_counting_types( 'author', $author );
			foreach( $author_counting_types as $id => $single_counting ) {
				//Counting
				$counting_type_count = 0;
				if( ! isset( $single_counting['payment_only'] ) OR $single_counting['payment_only'] == false ) {
					$counting_type_count = call_user_func( $single_counting['count_callback'], $stats, $author, $data );

					//The 'aux' index was added later to author counting types to allow them to store more complex counting data.
					//For example, Publisher Bonus stores here visits/words data so that it can calculate a bonus for them with its class payment method.
					if( ! isset( $counting_type_count['aux'] ) )
						$counting_type_count['aux'] = array();

					$stats['total']['ppc_count']['normal_count'][$id] = $counting_type_count;
				}

				//Payment
				$counting_type_payment = call_user_func( $single_counting['payment_callback'], $counting_type_count, $author );
				$stats['total']['ppc_payment']['normal_payment'][$id] = $counting_type_payment;

				if( isset( $stats['total']['ppc_payment']['normal_payment']['total'] ) )
					$stats['total']['ppc_payment']['normal_payment']['total'] += $counting_type_payment;
				else
					$stats['total']['ppc_payment']['normal_payment']['total'] = $counting_type_payment;
			}

            //Check total threshold
            if( $user_settings['counting_payment_total_threshold'] != 0 AND isset( $stats['total']['ppc_payment']['normal_payment']['total'] ) ) {
                if( $stats['total']['ppc_payment']['normal_payment']['total'] > $stats['total']['ppc_misc']['posts'] * $user_settings['counting_payment_total_threshold'] )
                    $stats['total']['ppc_payment']['normal_payment']['total'] = $stats['total']['ppc_misc']['posts'] * $user_settings['counting_payment_total_threshold'];
            }

			//Build payment tooltips
			if( isset( $stats['total']['ppc_payment']['normal_payment'] ) AND isset( $stats['total']['ppc_count']['normal_count'] ) AND ! empty( $stats['total']['ppc_payment']['normal_payment'] ) AND ! empty( $stats['total']['ppc_count']['normal_count'] ) ) {
				$active_counting_types_merge = array_merge( $ppc_global_settings['counting_types_object']->get_all_counting_types( 'author' ), $ppc_global_settings['counting_types_object']->get_all_counting_types( 'post' ) );
				$stats['total']['ppc_misc']['tooltip_normal_payment'] = PPC_counting_stuff::build_payment_details_tooltip( $stats['total']['ppc_count']['normal_count'], $stats['total']['ppc_payment']['normal_payment'], $active_counting_types_merge );
				$stats['total']['ppc_misc'] = apply_filters( 'ppc_stats_author_misc', $stats['total']['ppc_misc'], $author, $stats );
			}

			$stats = apply_filters( 'ppc_sort_stats_by_author_foreach_author', $stats, $author );
			//print_r($stats['total']);
		}

        return apply_filters( 'ppc_generated_raw_stats', $data );
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
			foreach( $data as $author_id_foreach => $author_stats_foreach ) { $author_id = $author_id_foreach; $author_stats = $author_stats_foreach; } //list alternative
			$user_settings = PPC_general_functions::get_settings( $author_id, TRUE );

			//if( empty( $author_stats ) ) return;
			$post_stats = current( $author_stats );

			$counting_types = $ppc_global_settings['counting_types_object']->get_all_counting_types( 'post' );

            $formatted_stats['cols']['post_id'] = __( 'ID' , 'post-pay-counter');
            $formatted_stats['cols']['post_title'] = __( 'Title', 'post-pay-counter');
            $formatted_stats['cols']['post_type'] = __( 'Type', 'post-pay-counter');
            $formatted_stats['cols']['post_status'] = __( 'Status', 'post-pay-counter');
            $formatted_stats['cols']['post_publication_date'] = __( 'Pub. Date', 'post-pay-counter');

            $data_merge = array_merge( $post_stats->ppc_count['normal_count'], $post_stats->ppc_payment['normal_payment'] ); //get counting types from a random post

			/*
            // BUG: if random post has different counting types (for example because of category custom settings, then the whole thing is screwed up)
            // It doesnt work even if in the whole page there is just on different post, because then on line 357 we use this var to foreach cnt types
            */

			unset( $data_merge['total'] );

			//Add column labels for counting types
			self::get_detailed_stats_columns( $formatted_stats['cols'], $data_merge );

            foreach( $author_stats as $key => $post ) {
                if( $key === 'total' ) continue; //Skip author's total

                $post_date = explode( ' ', $post->post_date );

                $formatted_stats['stats'][$author_id][$post->ID]['post_id'] = $post->ID;
                $formatted_stats['stats'][$author_id][$post->ID]['post_title'] = $post->post_title;
                $formatted_stats['stats'][$author_id][$post->ID]['post_type'] = $post->post_type;
                $formatted_stats['stats'][$author_id][$post->ID]['post_status'] = $post->post_status;

                $formatted_stats['stats'][$author_id][$post->ID]['post_publication_date'] = $post_date[0];

				$data_merge = array_merge( $post->ppc_count['normal_count'], $post->ppc_payment['normal_payment'] ); //get counting types for this post

				//Add column labels for counting types, if new ones are there
				self::get_detailed_stats_columns( $formatted_stats['cols'], $data_merge );

				foreach( $data_merge as $id => $value ) { //foreach counting types in $post->ppc_* vars
					if( isset( $counting_types[$id] ) ) {

						if( isset( $counting_types[$id]['display_status_index'] ) AND isset( $user_settings[$counting_types[$id]['display_status_index']] ) ) //check display setting per user
							$display = $user_settings[$counting_types[$id]['display_status_index']];
						else
							$display = $counting_types[$id]['display'];

						switch( $display ) {
							case 'both':
								$formatted_stats['stats'][$author_id][$post->ID]['post_'.$id] = $post->ppc_count['normal_count'][$id]['to_count'].' ('.PPC_general_functions::format_payment( sprintf( '%.2f', $post->ppc_payment['normal_payment'][$id] ) ).')';
								break;

							case 'count':
								$formatted_stats['stats'][$author_id][$post->ID]['post_'.$id] = $post->ppc_count['normal_count'][$id]['to_count'];
								break;

							case 'payment':
								$formatted_stats['stats'][$author_id][$post->ID]['post_'.$id] = PPC_general_functions::format_payment( $post->ppc_payment['normal_payment'][$id] );
								break;

							case 'none':
							case 'tooltip':
								//nothing to display here
								break;
						}
					}
				}

				if( ! $user_settings['hide_column_total_payment'] )
					$formatted_stats['stats'][$author_id][$post->ID]['post_total_payment'] = PPC_general_functions::format_payment( $post->ppc_payment['normal_payment']['total'] );

                $formatted_stats['stats'][$author_id][$post->ID] = apply_filters( 'ppc_author_stats_format_stats_after_each_default', $formatted_stats['stats'][$author_id][$post->ID], $author_id, $post );
            }

			//Cols bottom, so that Payment cols are always at the end
			if( ! $user_settings['hide_column_total_payment'] )
				$formatted_stats['cols']['post_total_payment'] = __( 'Total Pay', 'post-pay-counter');

			$formatted_stats['cols'] = apply_filters( 'ppc_author_stats_format_stats_after_cols_default', $formatted_stats['cols'] );

        } else {
			$cols_info = array(
				'counting_types' => array()
			); //holds info about columns. We build cols list after stats taking all unique cnt types enabled across all users. A user may have some counting types unabled, so we can't know before the end all the possible cols we may need

            foreach( $data as $author_id => $posts ) {
                if( ! isset( $posts['total']['ppc_payment']['normal_payment'] ) OR empty( $posts['total']['ppc_payment']['normal_payment'] ) ) continue; //user with no counting types enabled

				$author_data = get_userdata( $author_id );
				$user_settings = PPC_general_functions::get_settings( $author_id, TRUE );
                $post_counting_types = $ppc_global_settings['counting_types_object']->get_all_counting_types( 'post' );
				$author_counting_types = $ppc_global_settings['counting_types_object']->get_all_counting_types( 'author' );
				$counting_types = array_merge( $post_counting_types, $author_counting_types );

                $formatted_stats['stats'][$author_id]['author_id'] = $author_id;
                $formatted_stats['stats'][$author_id]['author_name'] = $author_data->display_name;
                $formatted_stats['stats'][$author_id]['author_written_posts'] = (int) $posts['total']['ppc_misc']['posts'];

                $data_merge = array_merge( $posts['total']['ppc_count']['normal_count'], $posts['total']['ppc_payment']['normal_payment'] );

				foreach( $data_merge as $id => $value ) {
					if( isset( $counting_types[$id] ) ) {

						if( isset( $counting_types[$id]['display_status_index'] ) AND isset( $user_settings[$counting_types[$id]['display_status_index']] ) ) //check display setting per user
							$display = $user_settings[$counting_types[$id]['display_status_index']];
						else
							$display = $counting_types[$id]['display'];

						switch( $display ) {
							case 'both':
								$formatted_stats['stats'][$author_id]['author_'.$id] = $posts['total']['ppc_count']['normal_count'][$id]['to_count'].' ('.PPC_general_functions::format_payment( $posts['total']['ppc_payment']['normal_payment'][$id] ).')';
								break;

							case 'count':
								$formatted_stats['stats'][$author_id]['author_'.$id] = $posts['total']['ppc_count']['normal_count'][$id]['to_count'];
								break;

							case 'payment':
								$formatted_stats['stats'][$author_id]['author_'.$id] = PPC_general_functions::format_payment( $posts['total']['ppc_payment']['normal_payment'][$id] );
								break;

							case 'none':
							case 'tooltip':
								//nothing to display here
								break;
						}

						if( ! isset( $cols['counting_types'][$id] ) )
							$cols_info['counting_types'][$id] = $counting_types[$id];
					}
				}

				if( ! $user_settings['hide_column_total_payment'] )
					$formatted_stats['stats'][$author_id]['author_total_payment'] = PPC_general_functions::format_payment( $posts['total']['ppc_payment']['normal_payment']['total'] );

                $formatted_stats['stats'][$author_id] = apply_filters( 'ppc_general_stats_format_stats_after_each_default', $formatted_stats['stats'][$author_id], $author_id, $posts );
            }

			if( count( $formatted_stats['stats'] ) == 0 ) {
				$error = new PPC_Error( 'no_author_with_total_payment', sprintf( __( 'No posts reach the threshold. Check your settings at %1$s', 'ppc' ), '<em>'.__( 'Options', 'post-pay-counter' ).' > '.__( 'Counting Settings', 'post-pay-counter' ).' > '.__( 'Total Payment', 'post-pay-counter' ).' > '.__( 'Pay only when the total payment threshold is reached', 'post-pay-counter' ).'.</em>' ), false );
				return $error->return_error();
			}

			//COLUMNS
			$formatted_stats['cols']['author_id'] = __( 'Author ID' , 'post-pay-counter');
            $formatted_stats['cols']['author_name'] = __( 'Author Name' , 'post-pay-counter');
            $formatted_stats['cols']['author_written_posts'] = __( 'Written posts' , 'post-pay-counter');

			foreach( $cols_info['counting_types'] as $id => $cnt_type ) {
				switch( $cnt_type['display'] ) {
					case 'none':
					case 'tooltip':
						//nothing to display here
						break;

					default:
						$formatted_stats['cols']['author_'.$id] = $cnt_type['label'];
						break;
				}
			}

			if( ! $user_settings['hide_column_total_payment'] )
				$formatted_stats['cols']['author_total_payment'] = __( 'Total payment' , 'post-pay-counter');

			$formatted_stats['cols'] = apply_filters( 'ppc_general_stats_format_stats_after_cols_default', $formatted_stats['cols'] );

        }

        return apply_filters( 'ppc_formatted_stats', $formatted_stats );
    }

	/**
	 * Builds detailed stats columns array incrementally.
	 *
	 * Since each post can have different cnt types enabled (for example
	 * because of Category custom settings), every post must be able to
	 * contribute to the table columns.
	 *
	 * @since 	2.710
	 * @param 	&$columns array current columns
	 * @param	$maybe_add array columns current post contributes
	 * @return	void
	 */
    static function get_detailed_stats_columns( &$columns, $maybe_add ) {
		global $ppc_global_settings;

		$cols = array();
		$counting_types = $ppc_global_settings['counting_types_object']->get_all_counting_types( 'post' );

		foreach( $maybe_add as $id => $value ) {
			if( isset( $counting_types[$id] ) ) {
				switch( $counting_types[$id]['display'] ) {
					case 'none':
					case 'tooltip':
						//nothing to display here
						break;

					default:
						$cols['post_'.$id] = $counting_types[$id]['label'];
						break;
				}
			}
		}

		$columns = array_merge( $columns, $cols );
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
            'total_payment' => 0,
            'payment' => array(),
			'count' => array()
        );

        foreach( $stats as $single ) {
            //Posts total count
			$overall_stats['posts'] += $single['total']['ppc_misc']['posts'];

			//Total payment
			$overall_stats['total_payment'] += $single['total']['ppc_payment']['normal_payment']['total'];

			//Total counts
			if( isset( $single['total'] ) AND isset( $single['total']['ppc_count'] ) AND isset( $single['total']['ppc_count']['normal_count'] ) ) {
				foreach( $single['total']['ppc_count']['normal_count'] as $key => $data ) {
					if( ! isset( $overall_stats['count'][$key] ) )
						$overall_stats['count'][$key] = $data['to_count'];
					else
						$overall_stats['count'][$key] += $data['to_count'];
				}
			}

			//Total payments
			if( isset( $single['total'] ) AND isset( $single['total']['ppc_payment'] ) AND isset( $single['total']['ppc_payment']['normal_payment'] ) ) {
				foreach( $single['total']['ppc_payment']['normal_payment'] as $key => $data ) {
					if( $key == 'total' ) continue; //skip total payment

					if( ! isset( $overall_stats['payment'][$key] ) )
						$overall_stats['payment'][$key] = $data;
					else
						$overall_stats['payment'][$key] += $data;
				}
			}
        }

        return apply_filters( 'ppc_overall_stats', $overall_stats, $stats );
    }
}
