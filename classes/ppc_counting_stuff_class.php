<?php

/**
 * @author Stefano Ottolenghi
 * @copyright 2013
 */

class PPC_counting_stuff {
    
	/**
	 * Holds settings being used for current item (foreach). Allows not to pull settings every time.
	 */
	
	public static $settings;
    
    /**
     * Switches through the possible counting systems and determines which one is active. 
     * 
     * Populates the class variable holding the payment value of the current system so that the methods which do the countings can rely on it without having to determine it every time.
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
     * @param   $data array an array of WP posts
     * @param   $author array optional an array of user ids of whom stats should be taken
     * @return  array the posts array along with their counting & payment data  
    */
    
    static function data2cash( $data, $author = NULL ) {
        $processed_data = array();
        
        foreach( $data as $single ) {
            self::$settings = PPC_general_functions::get_settings( $single->post_author, TRUE );
            
            $single->ppc_count = self::get_post_countings( $single );
            
            $post_payment = self::get_post_payment( $single->ppc_count['normal_count']['to_count'] );
            $single->ppc_payment = $post_payment['ppc_payment'];
            $single->ppc_misc = $post_payment['ppc_misc'];
            
            $processed_data[$single->ID] = apply_filters( 'ppc_post_counting_payment_data', $single, $author );
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
        $ppc_count = array(
            'normal_count' => array(
                'real' => array(),
                'to_count' => array()
            )
        );
        
        if( self::$settings['basic_payment'] ) {
            $ppc_count['normal_count']['real']['basic'] = 1;
            $ppc_count['normal_count']['to_count']['basic'] = 1;
        }
        
        if( self::$settings['counting_words'] ) {
            $words = self::count_post_words( $post );
            $ppc_count['normal_count']['real']['words'] = $words['real'];
            $ppc_count['normal_count']['to_count']['words'] = $words['to_count'];
        }
        
        if( self::$settings['counting_visits'] ) {
            $visits = self::get_post_visits( $post );
            $ppc_count['normal_count']['real']['visits'] = $visits['real'];
            $ppc_count['normal_count']['to_count']['visits'] = $visits['to_count'];
        }
        
        if( self::$settings['counting_images'] ) {
            $images = self::get_post_counting( $post, self::count_post_images( $post ), self::$settings['counting_images_threshold_min'], self::$settings['counting_images_threshold_max'], 'images' );
            $ppc_count['normal_count']['real']['images'] = $images['real'];
            $ppc_count['normal_count']['to_count']['images'] = $images['to_count'];
        }
        
        if( self::$settings['counting_comments'] ) {
            $comments = self::get_post_counting( $post, $post->comment_count, self::$settings['counting_comments_threshold_min'], self::$settings['counting_comments_threshold_max'], 'comments' );
            $ppc_count['normal_count']['real']['comments'] = $comments['real'];
            $ppc_count['normal_count']['to_count']['comments'] = $comments['to_count'];
        }
        
        return apply_filters( 'ppc_get_post_countings', $ppc_count, $post );
    }
    
	/**
     * Determines the number counting numbers (i.e. real & to_count) for a given counting type.
     * 
     * Keeps track of thresholds. 'to_count' holds the to be paid value (thresholded) while 'real' the real value.
     *
     * @access  public
     * @since   2.27
     * @param   $post object the WP post object
	 * @param	$real_counting int the real (without thresholds) counting number for the given counting type
	 * @param	$threshold_min int lower threshold value
	 * @param	$threshold_max int upper threshold value
	 * @param	$what string counting type
     * @return  array the counting data (real + to_count)
     */
    
    static function get_post_counting( $post, $real_counting, $threshold_min, $threshold_max, $what ) {
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
        $post_images = (int) preg_match_all( '/<img[^>]*>/', $post->post_content, $array );
        
		//Maybe include features image in counting
        if( self::$settings['counting_images_include_featured'] ) {
            if( has_post_thumbnail( $post->ID ) )
                ++$post_images;
        }
        
        return apply_filters( 'ppc_counted_post_images', $post_images, $post->ID );
    }
	
	/**
     * Determines the number of effective words for a given post content.
     * 
     * Trims blockquotes if requested; strip HTML tags (keeping their content). The regex basically reduces all kind of white spaces to one " " and trims punctuation. Apostrophes count as spaces. Keep track of thresholds. 'to_count' holds the to be paid value (threshold) while 'real' the real value.
     *
     * @access  public
     * @since   2.27
     * @param   $post object the WP post object
     * @return  array the words data
     */
    
    static function count_post_words( $post ) {
        $post_words = array( 
            'real' => 0, 
            'to_count' => 0 
        );
        
        if( self::$settings['counting_exclude_quotations'] )
            $post->post_content = preg_replace( '/<(blockquote|q)>.*<\/(blockquote|q)>/s', '', $post->post_content );
        
		$post_words['real'] = (int) preg_match_all( '/\S+\s|\s\S+/', apply_filters( 'ppc_clean_post_content_word_count', preg_replace( '/[.(),;:!?%#$¿"_+=\\/-]+/', '', preg_replace( '/\'&nbsp;|&#160;|\r|\n|\r\n|\s+/', ' ', strip_tags( $post->post_content ) ) ) ), $arr );
        
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
    
    static function get_post_visits( $post ) {
        $post_visits = array( 
            'real' => 0, 
            'to_count' => 0 
        );
        
        $visits_postmeta = apply_filters( 'ppc_counting_visits_postmeta', self::$settings['counting_visits_postmeta_value'] );
        
        $post_visits['real'] = (int) get_post_meta( $post->ID, $visits_postmeta, TRUE );
        
        if( self::$settings['counting_visits_threshold_max'] > 0 AND $post_visits['real'] > self::$settings['counting_visits_threshold_max'] )
            $post_visits['to_count'] = self::$settings['counting_visits_threshold_max'];
        else
            $post_visits['to_count'] = $post_visits['real'];
        
        return apply_filters( 'ppc_counted_post_visits', $post_visits );
    }
	
    /**
     * Computes payment data for the given post. Checks payment threshold.
     *
     * @access  public
     * @since   2.0
     * @param   $post_countings array the post countings
     * @return  array the payment data  
    */
    
    static function get_post_payment( $post_countings ) {
        $ppc_misc = array();
        $ppc_payment['normal_payment'] = self::get_countings_payment( $post_countings );
        
        $ppc_misc['exceed_threshold'] = false;
        if( self::$settings['counting_payment_total_threshold'] != 0 ) {
            if( $ppc_payment['normal_payment']['total'] > self::$settings['counting_payment_total_threshold'] ) {
                $ppc_payment['normal_payment']['total'] = self::$settings['counting_payment_total_threshold'];
                $ppc_misc['exceed_threshold'] = true;
            }
        }
        
        $ppc_misc['tooltip_normal_payment'] = self::build_payment_details_tooltip( $post_countings, $ppc_payment['normal_payment'] );
        
        return apply_filters( 'ppc_get_post_payment', array( 'ppc_payment' => $ppc_payment, 'ppc_misc' => $ppc_misc ) );
    }
    
    /**
     * Computes payment data for the given items.
     *
     * @access  public
     * @since   2.0
     * @param   $countings array the countings to be paid
     * @return  array the payment data  
    */
    
    static function get_countings_payment( $countings ) {
        $ppc_payment = array();
        
        //Basic payment
        if( self::$settings['basic_payment'] ) {
            $basic_pay = self::basic_payment( $countings['basic'] );
            $ppc_payment['basic'] = $basic_pay;
        }
        
        //Words payment
        if( self::$settings['counting_words'] ) {
            $words_pay = self::words_payment( $countings['words'] );
            $ppc_payment['words'] = $words_pay;
        }
        
        //Visits payment
        if( self::$settings['counting_visits'] ) {
            $visits_pay = self::visits_payment( $countings['visits'] );
            $ppc_payment['visits'] = $visits_pay;
        }
        
        //Images payment
        if( self::$settings['counting_images'] ) {
            $images_pay = self::images_payment( $countings['images'] );
            $ppc_payment['images'] = $images_pay;
        }
        
        //Comments payment
        if( self::$settings['counting_comments'] ) {
            $comments_pay = self::comments_payment( $countings['comments'] );
            $ppc_payment['comments'] = $comments_pay;
        }
        
		$ppc_payment = apply_filters( 'ppc_get_countings_payment', $ppc_payment, $countings );
        $ppc_payment['total'] = array_sum( $ppc_payment );
        
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
    
    static function build_payment_details_tooltip( $countings, $payment ) {
        $tooltip = '';
        
        if( isset( $countings['basic'] ) )
            $tooltip .= __( 'Basic payment' , 'ppc').': '.$countings['basic'].' => '.sprintf( '%.2f', $payment['basic'] ).'&#13;';

        if( isset( $countings['words'] ) )
            $tooltip .= __( 'Words payment' , 'ppc').': '.$countings['words'].' => '.sprintf( '%.2f', $payment['words'] ).'&#13;';
        
        if( isset( $countings['visits'] ) )
            $tooltip .= __( 'Visits payment' , 'ppc').': '.$countings['visits'].' => '.sprintf( '%.2f', $payment['visits'] ).'&#13;';
        
        if( isset( $countings['images'] ) )
            $tooltip .=  __( 'Images payment' , 'ppc').': '.$countings['images'].' => '.sprintf( '%.2f', $payment['images'] ).'&#13;';
        
        if( isset( $countings['comments'] ) )
            $tooltip .= __( 'Comments payment' , 'ppc').': '.$countings['comments'].' => '.sprintf( '%.2f', $payment['comments'] ).'&#13;';
        
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
        return apply_filters( 'ppc_basic_payment_value', $basic_payment = self::$settings['basic_payment_value']*$basic );
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
        return apply_filters( 'ppc_words_payment_value', self::$words_counting_system_data['counting_system']( $post_words, $words_counting_system_data['counting_system_value'] ) );
    }
    
    /**
     * Computes visits payment.
     *
     * @access  public
     * @since   2.0
     * @param   $post_words int post visits count
     * @return  array the payment data  
    */
    
    static function visits_payment( $post_visits ) {
        $visits_counting_system_data = self::get_current_counting_system( 'visits' );
        return apply_filters( 'ppc_visits_payment_value', self::$visits_counting_system_data['counting_system']( $post_visits, $visits_counting_system_data['counting_system_value'] ) );
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
        return apply_filters( 'ppc_images_payment_value', self::$images_counting_system_data['counting_system']( $post_images, $images_counting_system_data['counting_system_value'] ) );
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
        return apply_filters( 'ppc_comments_payment_value', self::$comments_counting_system_data['counting_system']( $post_comments, $comments_counting_system_data['counting_system_value'] ) );
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
?>