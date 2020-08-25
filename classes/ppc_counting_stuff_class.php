<?php

/**
 * @author 		Stefano Ottolenghi
 * @copyright 	2013
 * @package		PPC
 */

class PPC_counting_stuff {

	/**
	 * Holds settings being used for current item (foreach). Allows not to pull settings every time.
	 */
	public static $settings;

	/**
	 * Holds being-processed-post active counting types.
	 */
	public static $current_active_counting_types_post;
	public static $current_active_counting_types_author;

	/**
	 * Holds user ID whose posts are currently being processed.
	 */
	public static $being_processed_author;

    /**
     * Switches through the possible counting systems and determines which one is active.
     *
     * @access  public
     * @since   2.0
     * @param   $counting_type string the counting type (words, visits, images, comments)
     * @return  array the current counting system data
     */
    static function get_current_counting_system( $counting_type ) {
        $counting_systems = apply_filters( 'ppc_counting_systems', array( 'zonal', 'incremental' ) );

        foreach( $counting_systems as $single ) {
            $system = 'counting_'.$counting_type.'_system_'.$single;
            $system_value = 'counting_'.$counting_type.'_system_'.$single.'_value';

            if( self::$settings[$system] ) {
                $return = array(
					'counting_system' => 'counting_system_'.$single,
					'counting_system_value' => self::$settings[$system_value]
				);
                break;
            }
        }

        return $return;
    }

    /**
     * Assigns proper countings and payment data to each post.
     *
     * @access  public
     * @since   2.0
     * @param   $data array grouped by author stats result
     * @param   $author array optional an array of user ids of whom stats should be taken
     * @return  array the posts array along with their counting & payment data
     */
    static function data2cash( $data, $author = NULL ) {
        global $ppc_global_settings;

        $processed_data = array();

        //Initializes counting types
        if( ! isset( $ppc_global_settings['counting_types_object'] ) OR ! is_a( $ppc_global_settings['counting_types_object'], 'PPC_counting_types' ) ) {
			$ppc_global_settings['counting_types_object'] = new PPC_counting_types();
			$ppc_global_settings['counting_types_object']->register_built_in_counting_types();
		}

        foreach( $data as $author_id => &$author_stats ) {
			self::$being_processed_author = $author_id;
			self::$settings = PPC_general_functions::get_settings( $author_id, TRUE );
			self::$current_active_counting_types_post = $ppc_global_settings['counting_types_object']->get_active_counting_types( 'post', $author_id );
			self::$current_active_counting_types_author = $ppc_global_settings['counting_types_object']->get_active_counting_types( 'author', $author_id );

			$data_arr = array();

			foreach( $author_stats as $single ) {

				//Skip posts with non allowed post status
				if( ! ( isset( $single->post_status, self::$settings['counting_allowed_post_statuses'] ) AND self::$settings['counting_allowed_post_statuses'][$single->post_status] ) )
					continue;

				//Use cached data if available
				$post_stats = PPC_cache_functions::get_post_stats( $single->ID );

				if( $post_stats !== false ) {
					$processed_data[$author_id][$single->ID] = $post_stats;

				} else {
					do_action( 'ppc_data2cash_single_before', $single );

					$post_countings = self::get_post_countings( $single );
					$post_payment = self::get_post_payment( $post_countings['normal_count'], $single );

					if( count( $post_countings['normal_count'] ) == 0 AND count( $post_payment['ppc_payment']['normal_payment'] ) == 0 ) continue;

					$single->ppc_count = $post_countings;
					$single->ppc_payment = $post_payment['ppc_payment'];
					$single->ppc_misc = apply_filters( 'ppc_stats_post_misc', $post_payment['ppc_misc'], $single->ID );

					$processed_data[$author_id][$single->ID] = apply_filters( 'ppc_post_counting_payment_data', $single, $author );

					//Cache post stats for one day
					PPC_cache_functions::set_post_stats( $single->ID, $processed_data[$author_id][$single->ID] );
				}
			}
        }

        do_action( 'ppc_data2cash_processed_data', $processed_data ); //@since 2.605

		if( empty( $processed_data ) ) {
			$error = new PPC_Error( 'ppc_empty_selection_after_all', __( 'Your query resulted in an empty result. Try to select a wider time range!', 'post-pay-counter' ), array(), false );
			return $error->return_error();
		}

        return $processed_data;
    }

    /**
     * Retrieves countings for the given post.
     *
     * @access  public
     * @since   2.0
     * @param   $post object a WP posts
     * @return  array the posts array along with their counting data
     */
    static function get_post_countings( $post ) {
        global $ppc_global_settings;

        $ppc_count = array(
            'normal_count' => array()
        );

        foreach( self::$current_active_counting_types_post as $id => $single_counting ) {
            if( ! isset( $single_counting['payment_only'] ) OR $single_counting['payment_only'] == false ) {
            	$counting_type_count = call_user_func( $single_counting['count_callback'], $post );
				$ppc_count['normal_count'][$id] = $counting_type_count;
			}
        }

        return apply_filters( 'ppc_get_post_countings', $ppc_count, $post );
    }

	/**
     * Determines to_count number for a given counting type and post.
     * Keeps track of thresholds. 'to_count' holds the to be paid value (thresholded) while 'real' the real value.
     *
     * @access  public
     * @since   2.27
	 * @param	$real_counting int the real (without thresholds) counting number for the given counting type
	 * @param	$threshold_min int lower threshold value
	 * @param	$threshold_max int upper threshold value
	 * @param	$what string counting type
     * @return  array the counting data (real + to_count)
     */

    static function get_post_counting( $real_counting, $threshold_min, $threshold_max, $what ) {
        $post_counting = array(
            'real' => (int) $real_counting,
            'to_count' => 0
        );

        //Set max alllowed number
        $allowed = $threshold_max - $threshold_min;

        //If lower threshold is not met, set count to 0
        if( $real_counting <= $threshold_min ) {
            $post_counting['to_count'] = 0;
        } else {

            //If both upper and lower thresholds are 0, then no limit
            if( $allowed == 0 ) {
                $post_counting['to_count'] = $post_counting['real'];

            //If there's no upper threshold but lower threshold is set (ie. (max-min)<0), set count to count-min
            } else if( $allowed < 0 AND $real_counting > $allowed ) {
                $post_counting['to_count'] = $real_counting - $threshold_min;

            //If count exceeds upper threshold, set count to max-min
            } else if( $allowed > 0 AND $real_counting > $allowed ) {
                $post_counting['to_count'] = $allowed;

            //If count lies between thresholds, set it to the count-min
            } else if( $allowed > 0 AND $real_counting <= $allowed ) {
                $post_counting['to_count'] = $real_counting - $threshold_min;
            }
        }

        return apply_filters( 'ppc_counted_post_'.$what, $post_counting );
    }

	/**
     * Determines the number of images for a given post.
	 *
     * @access  public
     * @since   2.27
     * @param   object the WP post object
     * @return  int images number
     */

    static function count_post_images( $post ) {
        //Maybe include gallery images
		if( self::$settings['counting_images_include_galleries'] ) {
			$gallery_images = get_post_galleries( $post, true );

			if( ! empty( $gallery_images ) ) {
				foreach( $gallery_images as $single )
					$post->post_content .= $single;
			}
		}

		$post_images = (int) preg_match_all( '/<img[^>]*>/', $post->post_content, $array );

		//Maybe include featured image in counting
        if( self::$settings['counting_images_include_featured'] ) {
            if( has_post_thumbnail( $post->ID ) )
                ++$post_images;
        }

        $post_images = self::get_post_counting( (int) $post_images, self::$settings['counting_images_threshold_min'], self::$settings['counting_images_threshold_max'], 'images' );

        return apply_filters( 'ppc_counted_post_images', $post_images, $post->ID );
    }

    /**
     * Determines the number of comments for a given post.
	 *
     * @access  public
     * @since   2.40
     * @param   object the WP post object
     * @return  int comments number
     */

    static function count_post_comments( $post ) {
        $post_comments = self::get_post_counting( (int) $post->comment_count, self::$settings['counting_comments_threshold_min'], self::$settings['counting_comments_threshold_max'], 'comments' );

        return apply_filters( 'ppc_counted_post_comments', $post_comments, $post->ID );
    }

	/**
     * Determines the number of effective words for a given post content.
     *
     * Trims blockquotes if requested; strip HTML tags (keeping their content). The regex basically reduces all kind of white spaces to one " " and trims punctuation. Apostrophes count as spaces. Keep track of thresholds. 'to_count' holds the to be paid value (threshold) while 'real' the real value.
     *
     * @access  public
     * @since   2.27
     * @param   $post object|string the WP post object or a text string
     * @return  array the words data
     */

    static function count_post_words( $post ) {
        $post_words = array(
            'real' => 0,
            'to_count' => 0
        );

		//Handle input parameter
        if( is_a( $post, 'WP_Post' ) )
			$post_content = apply_filters( 'ppc_count_post_words_post_content_start', $post->post_content, $post->ID );
		else if( is_string( $post ) )
			$post_content = apply_filters( 'ppc_count_post_words_post_content_start', $post );
		else
			return new WP_Error( 'ppc_invalid_argument', 'count_post_words only accepts a WP_post istance or a text string', array( $post ) );

		//Strip tags & content with class="ppc_exclude_words" (doesn't handle nested tags, ie <div class="ppc_exclude_posts">some content <div class="nested">nested content</div> this will already be counted</div>
		$purged_content = preg_replace( '/<([^>]*) [^>]*class=("|\')ppc_exclude_words("|\')[^>]*>(.*?)<\/\1>/s', '', $post_content );

        if( self::$settings['counting_exclude_quotations'] )
            $purged_content = preg_replace( '/<(blockquote|q)[^>]*>(.*?)<\/(blockquote|q)>/s', '', $purged_content );

        if( self::$settings['counting_words_exclude_pre'] )
            $purged_content = preg_replace( '/<(pre)[^>]*>(.*?)<\/(pre)>/s', '', $purged_content );

		if( ! has_shortcode( $post_content, 'ppc' ) AND self::$settings['counting_words_apply_shortcodes'] ) //avoid nested calls of functions due to ppc shortcode
			$purged_content = do_shortcode( $purged_content );

		if( self::$settings['counting_words_exclude_captions'] )
            $purged_content = preg_replace( '/<(figcaption)[^>]*>(.*?)<\/(figcaption)>/s', '', $purged_content );

		$purged_content = strip_tags( $purged_content );

		if( self::$settings['counting_words_parse_spaces'] )
			$purged_content = preg_replace( '/\'|&nbsp;|&#160;|\r|\n|\r\n|\s+/', ' ',  $purged_content );

		$purged_content = preg_replace( '/\.|,|:|;|\(|\)|"|\'/', '',  $purged_content );

		$purged_content = apply_filters( 'ppc_clean_post_content_word_count', trim( $purged_content ) ); //need to trim to remove final new lines

		$post_words['real'] = count( preg_split( '/\s+/', $purged_content, -1, PREG_SPLIT_NO_EMPTY ) );

		//Include excerpt text if needed
		if( self::$settings['counting_words_include_excerpt'] AND is_a( $post, 'WP_Post' ) AND ! empty( $post->post_excerpt ) ) {
			$excerpt_words = self::count_post_words( $post->post_excerpt );
			$post_words['real'] += $excerpt_words['real'];
		}

		$post_words['real'] = apply_filters( 'ppc_count_post_words', $post_words['real'], $post );

		if( self::$settings['counting_words_threshold_max'] > 0 AND $post_words['real'] > self::$settings['counting_words_threshold_max'] )
            $post_words['to_count'] = self::$settings['counting_words_threshold_max'];
        else
            $post_words['to_count'] = $post_words['real'];

        return apply_filters( 'ppc_counted_post_words', $post_words );
    }

    /**
     * Determines the number of visits for a given post.
     *
     * Keeps track of thresholds. 'to_count' holds the to be paid value (threshold) while 'real' the real value.
     *
     * @access  public
     * @since   2.27
     * @param   object the WP post object
     * @return  array the words data
    */

    static function count_post_visits( $post ) {
        $post_visits = array(
            'real' => 0,
            'to_count' => 0
        );

		if( self::$settings['counting_visits_callback'] ) {
			$visits_callback = apply_filters( 'ppc_counting_visits_callback', PPC_counting_types::get_visits_callback_function() );

			if( is_callable( $visits_callback ) )
				$post_visits['real'] = (int) call_user_func( $visits_callback, $post );
			else
				$post_visits['real'] = -1;

		} else {
			$visits_postmeta = apply_filters( 'ppc_counting_visits_postmeta', self::$settings['counting_visits_postmeta_value'] );
			$post_visits['real'] = (int) get_post_meta( $post->ID, $visits_postmeta, TRUE );
		}

		$post_visits['real'] = (int) ($post_visits['real']*self::$settings['counting_visits_display_percentage']/100); //we cannot do this in to_count or it would create issues for already paid posts if the percentage is changed
		$post_visits['to_count'] = $post_visits['real'];

        if( self::$settings['counting_visits_threshold_max'] > 0 AND $post_visits['to_count'] > self::$settings['counting_visits_threshold_max'] )
            $post_visits['to_count'] = self::$settings['counting_visits_threshold_max'];

        return apply_filters( 'ppc_counted_post_visits', $post_visits, $post->ID );
    }

    /**
     * Outputs 1 as count, acts as dummy counter.
	 *
     * @access  public
     * @since   2.40
     * @param   object the WP post object
     * @return  array ones data
     */

    static function dummy_counter( $post ) {
        return apply_filters( 'ppc_dummy_counter', array( 'to_count' => 1, 'real' => 1 ), $post->ID );
    }

    /**
     * Computes payment data for the given post. Checks payment threshold.
     *
     * @access  public
     * @since   2.0
     * @param   $post_countings array the post countings
	 * @param	$post WP_Post Object
     * @return  array the payment data
     */
    static function get_post_payment( $post_countings, $post ) {
        global $ppc_global_settings;

		$ppc_misc = array();
        $ppc_payment['normal_payment'] = self::get_countings_payment( $post_countings, $post->post_author );

		$counting_types = self::$current_active_counting_types_post;
        foreach( $counting_types as $id => $value ) {
            if( isset( $value['payment_only'] ) AND $value['payment_only'] == true ) {
                $counting_type_payment = call_user_func( $value['payment_callback'], $value, $post->ID );
                $ppc_payment['normal_payment'][$id] = $counting_type_payment;
            }
        }

		$ppc_payment['normal_payment']['total'] = array_sum( $ppc_payment['normal_payment'] );

		$ppc_payment = self::are_countings_above_thresholds( $ppc_payment, $post_countings, $post );

        $ppc_misc['exceed_threshold'] = false;
        if( self::$settings['counting_payment_total_threshold'] != 0 ) {
            if( $ppc_payment['normal_payment']['total'] > self::$settings['counting_payment_total_threshold'] ) {
                $ppc_payment['normal_payment']['total'] = self::$settings['counting_payment_total_threshold'];
                $ppc_misc['exceed_threshold'] = true;
            }
        }

        return apply_filters( 'ppc_get_post_payment', array( 'ppc_payment' => $ppc_payment, 'ppc_misc' => $ppc_misc ), $post_countings, $post );
    }

    /**
     * Checks whether post qualifies for payment with respect to thresholds for each counting type.
     *
     * @since	2.750
     * @param	$ppc_payment array
     * @param	$countings array
     * @param 	$post WP_Post Object
     * @return 	array ppc_payment
     */
    static function are_countings_above_thresholds( $ppc_payment, $countings, $post ) {

		foreach( $countings as $counting_type => $single ) {
			if( isset( PPC_counting_stuff::$settings['counting_'.$counting_type.'_global_threshold'] ) ) {
				if( $single['real'] < PPC_counting_stuff::$settings['counting_'.$counting_type.'_global_threshold'] )
					$ppc_payment['normal_payment']['total'] = 0;
			}
		}

		return $ppc_payment;
	}

    /**
     * Computes payment data for the given items.
     *
     * @access  public
     * @since   2.0
     * @param   $countings array the countings to be paid
     * @return  array the payment data
     */
    static function get_countings_payment( $countings, $author = 'general' ) {
        global $ppc_global_settings;

        $ppc_payment = array();

		$counting_types = array_merge( self::$current_active_counting_types_post, self::$current_active_counting_types_author );

		if( ! empty( $countings ) ) {
			foreach( $countings as $id => $value ) {
				if( isset( $counting_types[$id] ) ) {
					if( isset( $counting_types[$id]['payment_only'] ) AND $counting_types[$id]['payment_only'] == true ) continue; //these are dealt with in get_post_payment
					if( isset( $counting_types[$id]['other_params']['not_to_pay'] ) AND $counting_types[$id]['other_params']['not_to_pay'] ) continue;

					$counting_type_payment = call_user_func( $counting_types[$id]['payment_callback'], $value, $author );
					$ppc_payment[$id] = $counting_type_payment;
				}
			}
		}

		$ppc_payment = apply_filters( 'ppc_get_countings_payment', $ppc_payment, $countings );

        return $ppc_payment;
    }

    /**
     * Builds tooltip holding payment details.
     *
     * @access  public
     * @since   2.0.2
     * @param   $countings array PPC count
     * @param   $payment array PPC payment
     * @return  string tooltip
     */
    static function build_payment_details_tooltip( $countings, $payment, $counting_types = array() ) {
        $tooltip = '';

		if( ! self::$settings['enable_stats_payments_tooltips'] )
			return $tooltip;

        if( ! empty( $payment ) ) {
			foreach( $payment as $id => $value ) {
				if( $id == 'total' ) continue;
				if( ! isset( $counting_types[$id] ) ) continue; //skip unactive counting types

				if( $counting_types[$id]['display'] == 'none' ) continue; //hides to-be-hidden counting types

				//Countings with only payment
				if( isset( $counting_types[$id]['payment_only'] ) AND $counting_types[$id]['payment_only'] ) {
					$tooltip .= ucfirst( $id ).': '.PPC_general_functions::format_payment( $value ).'
';

				//Countings with count and payment
				} else {
					if( is_numeric( $countings[$id]['to_count'] ) )
						$countings[$id]['to_count'] = round( $countings[$id]['to_count'], 3 );

					$tooltip .= ucfirst( $id ).': '.$countings[$id]['to_count'].' => '.PPC_general_functions::format_payment( $value ).'
';
				}
			}
		}

        return apply_filters( 'ppc_payment_details_tooltip', $tooltip, $countings, $payment );
    }

    /**
     * Computes basic payment.
     *
     * @access  public
     * @since   2.0
     * @param   $basic int how many basics to pay
     * @return  float the payment data
    */

    static function basic_payment( $basic ) {
        $basic_payment = self::$settings['basic_payment_value']*$basic['to_count'];
		return apply_filters( 'ppc_basic_payment_value', $basic_payment );
    }

    /**
     * Computes words payment.
     *
     * @access  public
     * @since   2.0
     * @param   $post_words int post words count
     * @return  array the payment data
    */

    static function words_payment( $post_words ) {
        $words_counting_system_data = self::get_current_counting_system( 'words' );
        $counting_system = $words_counting_system_data['counting_system'];
        return apply_filters( 'ppc_words_payment_value', self::$counting_system( $post_words['to_count'], $words_counting_system_data['counting_system_value'] ) );
    }

    /**
     * Computes visits payment.
     *
     * @access  public
     * @since   2.0
     * @param   $post_visits int post visits count
     * @return  array the payment data
    */

    static function visits_payment( $post_visits ) {
        $visits_counting_system_data = self::get_current_counting_system( 'visits' );
        $counting_system = $visits_counting_system_data['counting_system'];
        return apply_filters( 'ppc_visits_payment_value', self::$counting_system( $post_visits['to_count'], $visits_counting_system_data['counting_system_value'] ) );
    }

    /**
     * Computes images payment.
     *
     * @access  public
     * @since   2.0
     * @param   $post_images int post images count
     * @return  array the payment data
    */

    static function images_payment( $post_images ) {
        $images_counting_system_data = self::get_current_counting_system( 'images' );
        $counting_system = $images_counting_system_data['counting_system'];
        return apply_filters( 'ppc_images_payment_value', self::$counting_system( $post_images['to_count'], $images_counting_system_data['counting_system_value'] ) );
    }

    /**
     * Computes comments payment.
     *
     * @access  public
     * @since   2.0
     * @param   $post_comments int post comments count
     * @return  array the payment data
    */

    static function comments_payment( $post_comments ) {
        $comments_counting_system_data = self::get_current_counting_system( 'comments' );
        $counting_system = $comments_counting_system_data['counting_system'];
        return apply_filters( 'ppc_comments_payment_value', self::$counting_system( $post_comments['to_count'], $comments_counting_system_data['counting_system_value'] ) );
    }

    /**
     * Cycles through set zones, finds the one that suites each post counting and sets it as payment.
     *
     * @access  public
     * @since   2.0
     * @param   $post_counting int post count
     * @param   $counting_system_value array the zonal system settings for this counting type
     * @return  float the payment for the given counting
    */

    static function counting_system_zonal( $post_counting, $counting_system_value ) {
        //Immediately return 0 if counting < than first zone
		if( $post_counting < $counting_system_value[0]['threshold'] ) return 0;

        $n = 0;
        $zones_count = count( $counting_system_value );
        while( $n < $zones_count ) {
            if( $post_counting >= $counting_system_value[$n]['threshold'] ) {   //Counting is > than current zone, that's interesting...
				if( $n == ( $zones_count - 1 )									//There are no more zones, so this must be the one!
				OR $post_counting < $counting_system_value[$n+1]['threshold'] ) //Counting is < than next zone, so this is the right one...
					return $counting_system_value[$n]['payment'];
            }
            ++$n;
        }
    }

    /**
     * Multiplies each post counting by the set incremental payment.
     *
     * @access  public
     * @since   2.0
     * @param   $post_counting int post count
     * @param   $counting_system_value array the incremental system settings for this counting type
     * @return  float the payment for the given counting
    */

    static function counting_system_incremental( $post_counting, $counting_system_value ) {
        return $payment = $post_counting * $counting_system_value;
    }
}
