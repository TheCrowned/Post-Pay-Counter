<?php

/**
 * Holds counting types related functions.
 * 
 * @package     PPC
 * @author      Stefano Ottolenghi
 * @copyright   2014
 * @since       2.40
 */

class PPC_counting_types {
    
	/**
	 * Holds registered counting types, regardless of their status. An array with post-counting types (under the index [post]) and author-counting types (under [author]).
     * Use register_counting_type to add a new one.
	 */
	
	public static $counting_types;
    
    /**
	 * Holds active counting types, both general and user-personalized.
	 */
	
	public static $active_counting_types;
	public static $user_counting_types;
	
	/**
	 * Holds general visits callback function (mixed: string/array).
	 */
	 
	public static $visits_callback_function;
    
    /**
     * Initiliazes class var.
     * 
     * @access  public
     * @since   2.40
     */ 
    
    public function __construct() {
        self::$counting_types = array(
            'post' => array(),
            'author' => array()
        );
		
		//Stores visits callback function into class variable as caching
		self::$visits_callback_function = self::get_visits_callback_function();
    }
    
    /**
     * Registers a counting type.
     * 
     * Words, visits, comments, images are examples of built-in counting types.
     * 
     * @access  public
     * @since   2.40
     * @param   $parameters array containing   
     *              id - unique identifier
     *              label - to be displayed label
     *              apply_to - whether post or author counting type (or both)
     *              settings_status_index - (optional) the name of the settings index of the plugin settings to check the counting-enabled-status against. 
     *                                      If not given, counting type will be disabled by default.
     *              count_callback - (optional) method to count "how many of the counting_type there are (eg. how many words, visits...)". 
     *                               Needs to return an array in the form array( 'to_count' => int, 'real' => int ).
     *                               Can (should) be a class method, in which case an array is needed (eg array( 'classname', 'static_method' ) or array( $object, 'method' ) ).
     *                               Will receive the $post WP_Post_Object as parameter if apply_to = post, will receive author stats and author id if apply_to = author.
     *                               If no count_callback is given, a dummy method will assign 1 as value of the count.
     *              display - (optional) what you want to be displayed in the stats, possible values are 'count', 'payment', 'both', 'tooltip', 'none'. Default to 'both'. 
     * 				display_status_index - (optional) the name of the settings index of the plugin settings containing a display value. This is used to provide different display values to different users. If not given, the above 'display' will be used.
     *              payment_callback - method to compute payment of the counted "how many". Will receive the counting output array as parameter as well as the post ID or author ID.
     *              other_params - (optional) can contain whatever you want. Maybe you want to add custom checks to a counting type: then you can use this to register it with custom args and then hook to actions in the method and use this parameter.
     *              				# not_to_pay (bool) exclude counting from payments.
     */ 
    function register_counting_type( $parameters ) {
        //Check everything needed has been given
        if( ! isset( $parameters['id'] ) OR ! isset( $parameters['label'] ) OR ! isset( $parameters['apply_to'] ) OR ! isset( $parameters['payment_callback'] ) ) {
            trigger_error( 'ID, label, apply_to (post|author), payment_callback parameters must be provided when registering a counting type.', E_USER_WARNING );
            return;
        }

        $counting_type_arr = array(
        	'id' => $parameters['id'],
            'label' => $parameters['label'],
            'payment_callback' => $parameters['payment_callback'],
        	'apply_to' => $parameters['apply_to'],
        	'other_params' => array()
        );
        
        if( isset( $parameters['payment_only'] ) )
            $counting_type_arr['payment_only'] = $parameters['payment_only'];
        
        if( isset( $parameters['settings_status_index'] ) )
            $counting_type_arr['settings_status_index'] = $parameters['settings_status_index'];

		//If no display choice is made, assign 'both'
        if( ! isset( $parameters['display'] ) )
            $counting_type_arr['display'] = 'both';
        else
            $counting_type_arr['display'] = $parameters['display'];
        
        if( isset( $parameters['display_status_index'] ) AND ! empty( $parameters['display_status_index'] ) ) {
            $counting_type_arr['display_status_index'] = $parameters['display_status_index'];

			$general_settings = PPC_general_functions::get_settings( 'general' );
			if( isset( $general_settings[$parameters['display_status_index']] ) ) //Adjust display arg depending on settings
				$counting_type_arr['display'] = $general_settings[$parameters['display_status_index']];
		}
        
        if( isset( $parameters['count_callback'] ) )
            $counting_type_arr['count_callback'] = $parameters['count_callback'];
        
        if( isset( $parameters['other_params'] ) )
        	$counting_type_arr['other_params'] = $parameters['other_params'];
        
        do_action( 'ppc_registering_counting_type', $counting_type_arr );
        
        //Counting types are stored in the global var 
        if( $parameters['apply_to'] == 'both' ) {
            self::$counting_types['post'][$parameters['id']] = apply_filters( 'ppc_define_counting_type', $counting_type_arr );
            self::$counting_types['author'][$parameters['id']] = apply_filters( 'ppc_define_counting_type', $counting_type_arr );
        } else {
            self::$counting_types[$parameters['apply_to']][$parameters['id']] = apply_filters( 'ppc_define_counting_type', $counting_type_arr );
        }
    }
    
    /**
     * Gets currently active counting types for given user and context (post or author).
     * Active counting types for users are stored in a class var and retrieved if available when needed.
     * Generates a list, whereas get_user_counting_types takes the list and completes each counting type with its details.
     * 
     * @access  public
     * @since   2.40
     * @param   $what string whether post or author
     * @param   $userid int|string (optional) whose counting types?
     * @return  array counting types
     */ 
    
    function get_active_counting_types( $what, $userid = 'general' ) {
//		$userid = apply_filters( 'ppc_get_active_counting_types_settings_userid', $userid, $what );

        //Try to retrieve them from "cache"
        $cache = false; //wp_cache_get( 'ppc_user_active_counting_types_list_'.$what.'_'.$userid );
        //WARNING - Cannot cache this stuff, as it also depends on who VIEWS the stats - there's a true parameter in the $settings call below!

		//If no cache available, need to build the data first
        if( $cache === false ) {
			$settings = PPC_general_functions::get_settings( $userid, TRUE );
			
			//See which ones are active
			$active_user_counting_types = array();
			
			foreach( self::$counting_types[$what] as $id => $single ) {
				$counting_status = 0;
				if( isset( $single['settings_status_index'] ) AND isset( $settings[$single['settings_status_index']] ) AND $settings[$single['settings_status_index']] )
					$counting_status = 1;
				
				//If you haven't given 'settings_status_index', this is your chance - the filter - to enable the counting type depending on custom checks! 
				$counting_status = apply_filters( 'ppc_get_counting_type_status', $counting_status, $id, $userid );
				
				if( $counting_status == 1 )
					$active_user_counting_types[] = $id;
			}

			self::$active_counting_types[$userid][$what] = $active_user_counting_types; //needed for get_user_counting_types
			//wp_cache_set( 'ppc_user_active_counting_types_list_'.$what.'_'.$userid, $active_user_counting_types ); //Cache
		}
        
        return $this->get_user_counting_types( $what, $userid );
    }
    
    /**
     * Gets user counting types.
     * Checks which counting types are active for the given user and returns the array with all the details of them.
     * It basically interbreeds general counting types with a list of the user-active ones.
     * Takes the list generated by get_active_counting_types and completes each counting type with its details.
     * 
     * @access  public
     * @since   2.40
     * @param	$what
     * @param   $userid int userid
     * @return  array user counting types
     */ 
    
    function get_user_counting_types( $what, $userid ) {
		//Try to retrieve them from cache
		$cache = false; //wp_cache_get( 'ppc_user_active_counting_types_details_'.$what.'_'.$userid );
		//WARNING - Cannot cache this stuff, as it also depends on who VIEWS the stats
		if( $cache !== false )
			return $cache;
		
		$user_settings = PPC_general_functions::get_settings( $userid );
		
        $active_user_counting_types = array(); 
		foreach( self::$active_counting_types[$userid][$what]  as $single ) { //counting types
			$current_counting_type = self::$counting_types[$what][$single];
			
			//Change 'display' param depending on user settings, if any
			if( isset( $current_counting_type['display_status_index'] ) AND isset( $user_settings[$current_counting_type['display_status_index']] ) AND $user_settings[$current_counting_type['display_status_index']] )
				$current_counting_type['display'] = $user_settings[$current_counting_type['display_status_index']];
			
			$active_user_counting_types[$single] = $current_counting_type;
		}

        //wp_cache_set( 'ppc_user_active_counting_types_details_'.$what.'_'.$userid, $active_user_counting_types ); //Cache
        
        return apply_filters( 'ppc_active_user_counting_types', $active_user_counting_types, $userid, $what );
    }
    
	/**
     * Gets all registered counting types.
     * 
     * @access  public
     * @since   2.40
     * @param   $what string whether post or author
     * @return  array counting types
     */ 
	
	function get_all_counting_types( $what ) {
		return self::$counting_types[$what];
	}
	
    /**
     * Registers plugin built-in counting types.
     * 
     * @access  public
     * @since   2.40
     */ 
    
    function register_built_in_counting_types() {
        $built_in_counting_types = array();
        
        $built_in_counting_types[] = array(
            'id' => 'basic',
            'label' => __( 'Basic', 'post-pay-counter' ),
            'apply_to' => 'post',
            'settings_status_index' => 'basic_payment',
            'display' => 'tooltip',
            'display_status_index' => 'basic_payment_display_status',
            'count_callback' => array( 'PPC_counting_stuff', 'dummy_counter' ),
            'payment_callback' => array( 'PPC_counting_stuff', 'basic_payment' )
        );
    
        $built_in_counting_types[] = array(
            'id' => 'words',
            'label' => __( 'Words', 'post-pay-counter' ),
            'apply_to' => 'post',
            'settings_status_index' => 'counting_words',
            'display' => 'count',
            'display_status_index' => 'counting_words_display_status',
            'count_callback' => array( 'PPC_counting_stuff', 'count_post_words' ),
            'payment_callback' => array( 'PPC_counting_stuff', 'words_payment' )
        );
        
    
        $built_in_counting_types[] = array(
            'id' => 'visits',
            'label' => __( 'Visits', 'post-pay-counter' ),
            'apply_to' => 'post',
            'settings_status_index' => 'counting_visits',
            'display' => 'count',
            'display_status_index' => 'counting_visits_display_status',
            'count_callback' => array( 'PPC_counting_stuff', 'count_post_visits' ),
            'payment_callback' => array( 'PPC_counting_stuff', 'visits_payment' )
        );
    
        $built_in_counting_types[] = array(
            'id' => 'images',
            'label' => __( 'Images', 'post-pay-counter' ),
            'apply_to' => 'post',
            'settings_status_index' => 'counting_images',
            'display' => 'count',
            'display_status_index' => 'counting_images_display_status',
            'count_callback' => array( 'PPC_counting_stuff', 'count_post_images' ),
            'payment_callback' => array( 'PPC_counting_stuff', 'images_payment' )
        );
    
        $built_in_counting_types[] = array(
            'id' => 'comments',
            'label' => __( 'Comments', 'post-pay-counter' ),
            'apply_to' => 'post',
            'settings_status_index' => 'counting_comments',
            'display' => 'count',
            'display_status_index' => 'counting_comments_display_status',
            'count_callback' => array( 'PPC_counting_stuff', 'count_post_comments' ),
            'payment_callback' => array( 'PPC_counting_stuff', 'comments_payment' )
        );
        
        foreach( $built_in_counting_types as $single )
            $this->register_counting_type( $single );
        
        do_action( 'ppc_registered_built_in_counting_types' );
    }
	
	/*static function counting_type_visits_callback( $counting_types, $userid ) {
		if( isset( $counting_types['post']['visits'] ) ) {
			$user_settings = PPC_general_functions::get_settings( $userid );
			
			if( $user_settings['counting_visits_callback'] )
				$counting_types['post']['visits']['count_callback'] = self::get_visits_callback_function();
		}
		
		return $counting_types;
	}*/
	
	/**
	 * Builds visits callback function, parsing stored one if available.
	 *
	 * @access 	public
	 * @since	1.4.7
	 * @param	(optional) callback to be parsed
	 * @return 	mixed (string/array) visits count callback
	 */
	
	static function get_visits_callback_function( $callback = '' ) {
		//If cache is available, return that
		if( ! empty( self::$visits_callback_function ) )
			return self::$visits_callback_function;
		
		$general_settings = PPC_general_functions::get_settings( 'general' );
		
		//Allow function to work with input - if none, fallback on settings (and cache it)
		if( empty( $callback ) ) {
			if ( empty( $general_settings['counting_visits_callback_value'] ) )
				return false;
			else
				$callback = $general_settings['counting_visits_callback_value'];
		}
			
				
		$explode = explode( ',', $callback );
		
		if( count( $explode ) == 2 ) //if callback is in the form classname, methodname
			$count_callback = array( trim( $explode[0] ), trim( $explode[1] ) );
		else 
			$count_callback = $callback;
		
		return $count_callback;
	}
 }
