<?php

/**
 * @author Stefano Ottolenghi
 * @copyright 2013
 */

require_once( 'ppc_options_fields_class.php' );

class PPC_meta_boxes {
    
    /**
     * Displays the metabox "PRO features" in the Options page (only if not pro version already) 
     *
     * @access  public
     * @since   2.0
    */
    
    static function meta_box_pro_features() { 
        $pro_features = array(
            __( 'Google Analytics' , 'ppc') => __( 'use your account on the world-leading website visits tracking system to pay writers per visit.' , 'ppc' ),
			__( 'PayPal', 'ppc') => __( 'pay your writers with PayPal directly through your blog.' , 'ppc' ),
            __( 'Mark as paid' , 'ppc') => __( 'keep track of your writers\' past payments by marking posts as paid and reviewing payments from a detailed history. Let the plugin keep track of how much each writer should be paid basing on past payments.' , 'ppc' ),
            __( 'Csv export', 'ppc') => __( 'download stats for offline consulting or storing.' , 'ppc' ),
            __( 'Shortcode', 'ppc' ) => __( 'put stats in public static pages and wherever suits your needs.', 'ppc' ),
			__( 'Stats in post editing page', 'ppc' ) => __( 'see post stats in edit page and exclude individual posts from stats.', 'ppc' )
        );
        
        printf( '<p>'.__( 'There are so many things you are missing by not running the PRO version of the Post Pay Counter! Remember that PRO features are always %1$sone click away%2$s!' , 'ppc'), '<a target="_blank" href="http://www.thecrowned.org/post-pay-counter-pro" title="Post Pay Counter PRO">', '</a>' ).':</p>';
        
        echo '<ul style="margin: 0 0 15px 2em;">';
        
		foreach( $pro_features as $key => $single )
            echo '<li style="list-style-type: square;"><strong>'.$key.'</strong>: '.$single.'</li>';
			
        echo '</ul>';
        
        printf( '<p>'.__( 'Something you would like is missing? Complete the %1$sfeatures survey%2$s and let us know what our priorities should be!', 'ppc'), '<a target="_blank" href="http://www.thecrowned.org/post-pay-counter-pro-features-survey" title="Post Pay Counter PRO - '.__( 'Features survey', 'ppc' ).'">', '</a>' ).'</p>';
    }
    
    /**
     * Displays the metabox "Support the author" in the Options page (only if not pro version already) 
     *
     * @access  public
     * @since   2.0
    */
    
    static function meta_box_support_the_fucking_author() {
        global $ppc_global_settings;
        
        echo '<p>'.__( 'If you like the Post Pay Counter, there are a couple of crucial things you can do to support its development' , 'ppc').':</p>';
        echo '<ul style="margin: 0 0 15px 2em; padding: 0">';
        echo '<li style="list-style-image: url(\''.$ppc_global_settings['folder_path'].'style/images/pro.png\');"><a target="_blank" href="http://www.thecrowned.org/post-pay-counter-pro" title="'.__( 'Go PRO' , 'ppc').'"><strong>'.__( 'Go PRO' , 'ppc').'</strong></a>. '.__( 'Try the PRO version: more functions, more stuff!' , 'ppc').'</li>';
        echo '<li style="list-style-image: url(\''.$ppc_global_settings['folder_path'].'style/images/paypal.png\');"><a target="_blank" href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=SM5Q9BVU4RT22" title="'.__( 'Donate money' , 'ppc').'"><strong>'.__( 'Donate money' , 'ppc').'</strong></a>. '.__( 'Plugins do not write themselves: they need time and effort, and I give all of that free of charge. Donations of every amount are absolutely welcome.' , 'ppc').'</li>';
        echo '<li style="list-style-image: url(\''.$ppc_global_settings['folder_path'].'style/images/amazon.png\');">'.sprintf( __( 'Give me something from my %1$sAmazon Wishlist%2$s.' , 'ppc'), '<a target="_blank" href="http://www.amazon.it/registry/wishlist/1JWAS1MWTLROQ" title="Amazon Wishlist">', '</a>' ).'</li>';
        echo '<li style="list-style-image: url(\''.$ppc_global_settings['folder_path'].'style/images/star.png\');">'.sprintf( __( 'Rate it in the %1$sWordpress Directory%3$s and share the %2$sofficial page%3$s.' , 'ppc'), '<a target="_blank" href="http://wordpress.org/extend/plugins/post-pay-counter/" title="Wordpress directory">', '<a target="_blank" href="http://www.thecrowned.org/wordpress-plugins/post-pay-counter" title="Official plugin page">', '</a>' ).'</li>';
        echo '<li style="list-style-image: url(\''.$ppc_global_settings['folder_path'].'style/images/write.png\');">'.__( 'Have a blog or write on some website? Write about the plugin and email me the review!' , 'ppc').'</li>';
        echo '</ul>';
    }
    
    /**
     * Displays the metabox "Miscellanea" in the Options page  
     *
     * @access  public
     * @since   2.0
    */
    
    static function meta_box_misc_settings( $post, $current_settings ) {
        global $wp_roles, $ppc_global_settings;
        $current_settings = $current_settings['args'];
        
        echo '<form id="ppc_misc_settings_form" method="post">';
        
        //Post types to be included in countings
        echo '<div class="section">';
        echo '<div class="title">'.__( 'Allowed post types' , 'ppc').'</div>';
        echo '<div class="main">';
        echo '<p>'.__( 'Choose the post types you would like to be included in countings.', 'ppc').'</p>';
        
        $all_post_types = get_post_types();
        $allowed_post_types = $current_settings['counting_allowed_post_types'];
        
        foreach ( $all_post_types as $single ) {
            $checked = '';
            
            if( in_array( $single, $allowed_post_types ) ) {
                $checked = 'checked="checked"';
            }
                
            echo '<input type="checkbox" name="post_type_'.$single.'" id="post_type_'.$single.'" value="'.$single.'" '.$checked.' />';
            echo '<label for="post_type_'.$single.'">'.ucfirst( $single ).'</label>';
            echo '<br />';
            
        }
        
        echo '</div>';
        echo '</div>';
        do_action( 'ppc_misc_settings_after_allowed_post_types', $current_settings );
        
        //User roles to be included in countings
        echo '<div class="section">';
        echo '<div class="title">'.__( 'Allowed user roles' , 'ppc').'</div>';
        echo '<div class="main">';
        echo '<p>'.__( 'Choose the user roles whose posts you would like to be included in countings.', 'ppc').'</p>';
        
        foreach( $wp_roles->role_names as $key => $value ) {
            $checked = '';
            
            if( in_array( $key, $current_settings['counting_allowed_user_roles'] ) )
                $checked = 'checked="checked"';
            
            echo '<input type="checkbox" name="user_role_'.$key.'" id="user_role_'.$key.'" value="'.$key.'" '.$checked.' />';
            echo '<label for="user_role_'.$key.'">'.$value.'</label>';
            echo '<br />';
        }
        
        echo '</div>';
        echo '</div>';
        do_action( 'ppc_misc_settings_after_allowed_user_roles', $current_settings );
        
        //Plugin options page access permissions
        echo '<div class="section">';
        echo '<div class="title">'.__( 'Options page permissions' , 'ppc').'</div>';
        echo '<div class="main">';
        echo '<p>'.__( 'Choose the user roles who are allowed to view and edit plugin settings.' , 'ppc').'</p>';
        foreach( $wp_roles->role_names as $key => $value ) {
			$checked = '';
			
			if( in_array( $key, $current_settings['can_see_options_user_roles'] ) )
                $checked = ' checked="checked"';
            
            echo '<input type="checkbox" name="can_see_options_user_roles_'.$key.'" id="can_see_options_user_roles_'.$key.'" value="'.$key.'"'.@$checked.'>';
            echo '<label for="can_see_options_user_roles_'.$key.'">'.$value.'</label>';
            echo '<br />';
            
            unset( $checked );
        }
        
        echo '</div>';
        echo '</div>';
        do_action( 'ppc_misc_settings_after_options_allowed_user_roles', $current_settings );
        
        //Plugin stats page access permissions
        echo '<div class="section">';
        echo '<div class="title">'.__( 'Stats page permissions' , 'ppc').'</div>';
        echo '<div class="main">';
        echo '<p>'.__( 'Choose the user roles who are allowed to view the stats page.' , 'ppc').'</p>';
        foreach( $wp_roles->role_names as $key => $value ) {
            $checked = '';
			
            if( in_array( $key, $current_settings['can_see_stats_user_roles'] ) )
                $checked = ' checked="checked"';
            
            echo '<input type="checkbox" name="can_see_stats_user_roles_'.$key.'" id="can_see_stats_user_roles_'.$key.'" value="'.$key.'"'.@$checked.'>';
            echo '<label for="can_see_stats_user_roles_'.$key.'">'.$value.'</label>';
            echo '<br />';
        }
        
        echo '</div>';
        echo '</div>';
        do_action( 'ppc_misc_settings_after_stats_allowed_user_roles', $current_settings );
        
        //Default stats time range
        echo '<div class="section">';
        echo '<div class="title">'.__( 'Default stats time range' , 'ppc').'</div>';
        echo '<div class="main">';
        echo '<p>'.__( 'When you open up the stats page, the time range here selected will be shown. This will be the default setting: you will still be able to change the time range the way you want it time to time.' , 'ppc').'</p>';
        echo PPC_HTML_functions::echo_p_field( __( 'Current week', 'ppc' ), $current_settings['default_stats_time_range_week'], 'radio', 'default_stats_time_range', __( 'Posts from the beginning of the week to the current day (week starts on Monday) will be displayed. You should select this if you usually pay your writers weekly.' , 'ppc'), 'default_stats_time_range_week', 'default_stats_time_range_week' );
        echo PPC_HTML_functions::echo_p_field( __( 'Current month', 'ppc' ), $current_settings['default_stats_time_range_month'], 'radio', 'default_stats_time_range', __( 'Posts from the beginning of the month to the current day will be displayed. You should select this if you usually pay your writers monthly.' , 'ppc'), 'default_stats_time_range_month', 'default_stats_time_range_month' );
        echo PPC_HTML_functions::echo_p_field( __( 'This custom number of days', 'ppc' ), $current_settings['default_stats_time_range_custom'], 'radio', 'default_stats_time_range', __( 'You can manually customize the time range for the posts that will be displayed. So, for example, if you set this to 365 days, in the stats page it will automatically be selected a time frame that goes from the current day to the previous 365 days.' , 'ppc'), 'default_stats_time_range_custom', 'default_stats_time_range_custom' );
        echo '<div id="default_stats_time_range_custom_content" class="section">';
        echo PPC_HTML_functions::echo_text_field( 'default_stats_time_range_custom_value', $current_settings['default_stats_time_range_custom_value'], __( 'Time range (days)' , 'ppc') );
        echo '</div>';
        echo '</div>';
        echo '</div>';
        
        do_action( 'ppc_misc_settings_after_default_time_range', $current_settings );
        ?>
        
        <div class="ppc_save_success" id="ppc_misc_settings_success"><?php _e( 'Settings were successfully updated.' , 'ppc'); ?></div>
        <div class="ppc_save_error" id="ppc_misc_settings_error"></div>
        <div class="save_settings">
            <img src="<?php echo $ppc_global_settings['folder_path'].'style/images/ajax-loader.gif'; ?>" title="<?php _e( 'Loading' , 'ppc'); ?>" alt="<?php _e( 'Loading' , 'ppc'); ?>" class="ajax_loader" id="ppc_misc_settings_ajax_loader" />
            <input type="submit" class="button-primary" name="ppc_save_misc_settings" id="ppc_save_misc_settings" value="<?php _e( 'Save options' , 'ppc') ?>" />
        </div>
        <div class="clear"></div>
        </form>
    <?php }
    
    /**
     * Displays the metabox "Counting settings" in the Options page  
     *
     * @access  public
     * @since   2.0
     * @param   object WP post object
     * @param   array plugin settings
    */
    
    static function meta_box_counting_settings( $post, $current_settings ) {
        global $wp_roles, $ppc_global_settings;
        $current_settings = $current_settings['args'];
        
        echo '<p>'.__( 'Here you can define the criteria which post payments will be computed with.' , 'ppc').'</p>';
        echo '<form action="" id="ppc_counting_settings_form" method="post">';
        
        //Basic payment
        echo '<div class="section">';
        echo '<div class="title">'.__( 'Basic payment' , 'ppc').'</div>';
        echo '<div class="main">';
        echo PPC_HTML_functions::echo_p_field( __( 'Basic, assured payment' , 'ppc'), $current_settings['basic_payment'], 'checkbox', 'basic_payment', __( 'You may define a starting value for post payment. This means that each post will earn at least this amount, to which all the other credits will be added. In this way you can be sure that no post will be paid less than a certain amount, but that only valuable posts will make it to higher points.' , 'ppc'), NULL, 'basic_payment' );
        echo '</div>';
        echo '<div class="content" id="ppc_basic_payment_content">';
        echo PPC_HTML_functions::echo_text_field( 'basic_payment_value', $current_settings['basic_payment_value'], __( 'Basic payment fixed value' , 'ppc') );
        echo '</div>';
        echo '</div>';
        do_action( 'ppc_counting_settings_after_basic_payment', $current_settings );
        
        //Words payment
        echo '<div class="section">';
        echo '<div class="title">'.__( 'Payment on word counting' , 'ppc').'</div>';
        echo '<div class="main">';
        echo PPC_HTML_functions::echo_p_field( __( 'Words contribute to payment computation' , 'ppc'), $current_settings['counting_words'], 'checkbox', 'counting_words', __( 'You may define a post value basing on the number of words that make it up as well. The longer a post is, the more time is supposed to have taken the author to write it, the more it should be paid. You will be able to choose how much each word is worth.' , 'ppc'), NULL, 'counting_words' );
        echo '</div>';
        echo '<div class="content" id="ppc_counting_words_content">';
        echo '<div class="title">'.__( 'Counting system' , 'ppc').'</div>';
        echo PPC_options_fields::echo_payment_systems( 'words', __( 'words', 'ppc' ), array( 'counting_words_system_zonal' => $current_settings['counting_words_system_zonal'], 'counting_words_system_zonal_value' => $current_settings['counting_words_system_zonal_value'], 'counting_words_system_incremental' => $current_settings['counting_words_system_incremental'], 'counting_words_system_incremental_value' => $current_settings['counting_words_system_incremental_value'] ) );
        echo '<div class="title">'.__( 'Counting options' , 'ppc').'</div>';
        echo PPC_HTML_functions::echo_text_field( 'counting_words_threshold_max', $current_settings['counting_words_threshold_max'], __( 'Stop counting words after word # (0 = infinite)' , 'ppc') );
        echo '</div>';
        echo '</div>';
        do_action( 'ppc_counting_settings_after_words_payment', $current_settings );
        
        //Visits payment
        echo '<div class="section">';
        echo '<div class="title">'.__( 'Payment on visit counting' , 'ppc').'</div>';
        echo '<div class="main">';
        echo PPC_HTML_functions::echo_p_field( __( 'Visits contribute to payment computation' , 'ppc'), $current_settings['counting_visits'], 'checkbox', 'counting_visits', __( 'You may define a post value basing on the number of visits that it registers as well. The more people see a post, the more interesting the post is supposed to be, the more it should be paid. You will be able to choose how much each visit is worth.' , 'ppc'), NULL, 'counting_visits' );
        echo '</div>';
        echo '<div class="content" id="ppc_counting_visits_content">';
        echo '<div class="title">'.__( 'Counting method' , 'ppc').'</div>';
        echo PPC_HTML_functions::echo_p_field( __( 'I have my own visit counter' , 'ppc'), $current_settings['counting_visits_postmeta'], 'radio', 'counting_visits_method', sprintf( __( 'If you already have some plugin counting visits, and you know the %1$spostmeta%2$s name it stores them into, you can use those data to compute payments. Activate this setting and put the postmeta in the field below.' , 'ppc'), '<em>', '</em>' ), 'counting_visits_postmeta', 'counting_visits_postmeta' );
        echo '<div id="counting_visits_postmeta_content" class="field_value">';
        echo PPC_HTML_functions::echo_text_field( 'counting_visits_postmeta_value', $current_settings['counting_visits_postmeta_value'], __( 'The postmeta holding the visits' , 'ppc') );
        echo '</div>';
        do_action( 'ppc_counting_settings_after_visits_counting_method', $current_settings );
        echo '<div class="title">'.__( 'Counting system' , 'ppc').'</div>';
        echo PPC_options_fields::echo_payment_systems( 'visits', __( 'visits', 'ppc' ), array( 'counting_visits_system_zonal' => $current_settings['counting_visits_system_zonal'], 'counting_visits_system_zonal_value' => $current_settings['counting_visits_system_zonal_value'], 'counting_visits_system_incremental' => $current_settings['counting_visits_system_incremental'], 'counting_visits_system_incremental_value' => $current_settings['counting_visits_system_incremental_value'] ) );
        echo '<div class="title">'.__( 'Counting options' , 'ppc').'</div>';
        echo PPC_HTML_functions::echo_text_field( 'counting_visits_threshold_max', $current_settings['counting_visits_threshold_max'], __( 'Stop counting visits after visit # (0 = infinite)' , 'ppc') );
        echo '</div>';
        echo '</div>';
        do_action( 'ppc_counting_settings_after_visits_payment', $current_settings );
        
        //Images payment
        echo '<div class="section">';
        echo '<div class="title">'.__( 'Payment on images counting' , 'ppc').'</div>';
        echo '<div class="main">';
        echo PPC_HTML_functions::echo_p_field( __( 'Images contribute to payment computation' , 'ppc'), $current_settings['counting_images'], 'checkbox', 'counting_images', sprintf( __( 'You may define a post value basing on the number of images it contains. Maybe more images make a post cleaerer to the readers, and should thus be paid something more. You will be able to choose: when you want the image counting to come in, meaning how many images are free of charge and after which one they should be paid; how much each image is worth; how many images at maximum should be paid (0 = no maximum, infinite). E.g. we have a post with 5 images, and the fields below are set like this: %s. The image payment would be 1.0 bacause image #3 and image #4 are counted.' , 'ppc'), '<em>2; 0.5; 4</em>' ), NULL, 'counting_images' );
        echo '</div>';
        echo '<div class="content" id="ppc_counting_images_content">';
        echo '<div class="title">'.__( 'Counting system' , 'ppc').'</div>';
        echo PPC_options_fields::echo_payment_systems( 'images', __( 'images', 'ppc' ), array( 'counting_images_system_zonal' => $current_settings['counting_images_system_zonal'], 'counting_images_system_zonal_value' => $current_settings['counting_images_system_zonal_value'], 'counting_images_system_incremental' => $current_settings['counting_images_system_incremental'], 'counting_images_system_incremental_value' => $current_settings['counting_images_system_incremental_value'] ) );
        echo '<div class="title">'.__( 'Counting options' , 'ppc').'</div>';
        echo PPC_HTML_functions::echo_text_field( 'counting_images_threshold_min', $current_settings['counting_images_threshold_min'], __( 'Start paying per image after image #' , 'ppc') );
        echo PPC_HTML_functions::echo_text_field( 'counting_images_threshold_max', $current_settings['counting_images_threshold_max'], __( 'Stop paying per image after image #' , 'ppc') );
        echo PPC_HTML_functions::echo_p_field( 'Include featured image in counting', $current_settings['counting_images_include_featured'], 'checkbox', 'counting_images_include_featured', __( 'Determines whether the featured image will be included in image counting.' , 'ppc') );
        echo '</div>';
        echo '</div>';
        do_action( 'ppc_counting_settings_after_images_payment', $current_settings );
        
        //Comments payment
        echo '<div class="section">';
        echo '<div class="title">'.__( 'Payment on comments counting' , 'ppc').'</div>';
        echo '<div class="main">';
        echo PPC_HTML_functions::echo_p_field( __( 'Comments contribute to payment computation' , 'ppc'), $current_settings['counting_comments'], 'checkbox', 'counting_comments', sprintf( __( 'You may define a post value basing on the number of comments it receives. You will be able to choose: when you want the comment counting to come in, meaning how many comments are free of charge and after which one they should be paid; how much each comment is worth; how many comments at maximum should be paid (0 = no maximum, infinite). E.g. we have a post with 30 comments, and the fields below are set like this: %s. The comment payment would be 2.5 bacause comments from #11 included to #25 included are counted.' , 'ppc'), '<em>10; 0.1; 25</em>' ), NULL, 'counting_comments' );
        echo '</div>';
        echo '<div class="content" id="ppc_counting_comments_content">';
        echo '<div class="title">'.__( 'Counting system' , 'ppc').'</div>';
        echo PPC_options_fields::echo_payment_systems( 'comments', __( 'comments', 'ppc' ), array( 'counting_comments_system_zonal' => $current_settings['counting_comments_system_zonal'], 'counting_comments_system_zonal_value' => $current_settings['counting_comments_system_zonal_value'], 'counting_comments_system_incremental' => $current_settings['counting_comments_system_incremental'], 'counting_comments_system_incremental_value' => $current_settings['counting_comments_system_incremental_value'] ) );
        echo '<div class="title">'.__( 'Counting options' , 'ppc').'</div>';
        echo PPC_HTML_functions::echo_text_field( 'counting_comments_threshold_min', $current_settings['counting_comments_threshold_min'], __( 'Start paying per comment after comment #' , 'ppc') );
        echo PPC_HTML_functions::echo_text_field( 'counting_comments_threshold_max', $current_settings['counting_comments_threshold_max'], __( 'Stop paying per comment after comment #' , 'ppc') );
        echo '</div>';
        echo '</div>';
        do_action( 'ppc_counting_settings_after_comments_payment', $current_settings );
        
        //Total payment
        echo '<div class="section">';
        echo '<div class="title">'.__( 'Total payment' , 'ppc').'</div>';
        echo '<div class="main">';
        echo PPC_HTML_functions::echo_text_field( 'counting_payment_total_threshold', $current_settings['counting_payment_total_threshold'], __( 'Set payment maximum (0 = infinite)' , 'ppc') );
        echo PPC_HTML_functions::echo_p_field( __( 'Pay only when the total payment threshold is reached' , 'ppc'), $current_settings['counting_payment_only_when_total_threshold'], 'checkbox', 'counting_payment_only_when_total_threshold', __( 'Check this if you want to pay items only when they reach the max payment threshold. Other items will appear grayed out.' , 'ppc'), 'counting_payment_only_when_total_threshold', 'counting_payment_only_when_total_threshold' );
        echo '</div>';
        echo '</div>';
        do_action( 'ppc_counting_settings_after_total_payment', $current_settings );
        
        //Misc
        echo '<div class="section">';
        echo '<div class="title">'.__( 'Miscellanea counting settings' , 'ppc').'</div>';
        echo '<div class="main">';
        echo PPC_HTML_functions::echo_p_field( __( 'Count pending revision posts', 'ppc' ), $current_settings['counting_allowed_post_statuses']['pending'], 'checkbox', 'counting_count_pending_revision_posts', __( 'While published posts are automatically counted, you can decide to include pending revision ones or not.' , 'ppc') );
        echo PPC_HTML_functions::echo_p_field( __( 'Count future scheduled posts', 'ppc' ), $current_settings['counting_allowed_post_statuses']['future'], 'checkbox', 'counting_count_future_scheduled_posts', __( 'While published posts are automatically counted, you can decide to include future planned ones or not.' , 'ppc') );
        echo PPC_HTML_functions::echo_p_field( __( 'Count private posts', 'ppc' ), $current_settings['counting_allowed_post_statuses']['private'], 'checkbox', 'counting_count_private_posts', __( 'While public published posts are automatically counted, you can decide to include private ones or not.' , 'ppc') );
        echo PPC_HTML_functions::echo_p_field( __( 'Exclude quoted content from word counting', 'ppc' ), $current_settings['counting_exclude_quotations'], 'checkbox', 'counting_exclude_quotations', sprintf( __( 'All the words contained into %1$sblockquote%2$s tags will not be taken into account when counting. Use this to prevent interviews and such stuff to be counted as normal words.' , 'ppc'), '<em>', '</em>' ) );
        echo '</div>';
        echo '</div>';
        do_action( 'ppc_counting_settings_after_misc', $current_settings );
        
        echo '<div class="ppc_save_success" id="ppc_counting_settings_success">'.__( 'Settings were successfully updated.' , 'ppc').'</div>';
        echo '<div class="ppc_save_error" id="ppc_counting_settings_error"></div>';
        echo '<div class="save_settings">';
        echo '<img src="'.$ppc_global_settings['folder_path'].'style/images/ajax-loader.gif'.'" title="'.__( 'Loading' , 'ppc').'" alt="'.__( 'Loading' , 'ppc').'" class="ajax_loader" id="ppc_counting_settings_ajax_loader" />';
        echo '<input type="hidden" name="userid" value="'.$current_settings['userid'].'" />';
        echo '<input type="submit" class="button-primary" name="ppc_save_counting_settings" id="ppc_save_counting_settings" value="'.__( 'Save options' , 'ppc').'" />';
        echo '</div>';
        echo '<div class="clear"></div>';
        echo '</form>';
   }
    
    /**
     * Displays the metabox "Permissions" in the Options page  
     *
     * @access  public
     * @since   2.0
     * @param   object WP post object
     * @param   array plugin settings
    */
    
    static function meta_box_permissions( $post, $current_settings ) {
        global $ppc_global_settings;
        $current_settings = $current_settings['args'];
        
        echo '<form action="" id="ppc_permissions_form" method="post">';
        echo '<p>'.__( 'Just a few fields to help you preventing users from seeing things they should not see. Administrators are subject to the same permissions; if you wish they did not, personalize their user settings.' , 'ppc').'</p>';
        echo PPC_HTML_functions::echo_p_field( __( 'Users can see other users\' general stats' , 'ppc'), $current_settings['can_see_others_general_stats'], 'checkbox', 'can_see_others_general_stats', __( 'If unchecked, users will only be able to see their stats in the general page. Other users\' names, posts and pay counts will not be displayed.' , 'ppc') );
        echo PPC_HTML_functions::echo_p_field( __( 'Users can see other users\' detailed stats' , 'ppc'), $current_settings['can_see_others_detailed_stats'], 'checkbox', 'can_see_others_detailed_stats', __( 'If unchecked, users will not be able to see other users\' detailed stats but will still able to see general ones. ' , 'ppc') );
        echo PPC_HTML_functions::echo_p_field( __( 'Let users know if other users have personalized settings' , 'ppc'), $current_settings['can_see_countings_special_settings'], 'checkbox', 'can_see_countings_special_settings', __( 'If you personalize settings by user, do not overlook this. If unchecked, users will not see personalized settings in countings, they will believe everybody is using their settings (or general settings). Anyway, users will see their own personalized settings, if they have them.' , 'ppc') );
        
		do_action( 'ppc_permissions_settings_after_default', $current_settings );
        ?>
        
        <div class="ppc_save_success" id="ppc_permissions_success"><?php _e( 'Settings were successfully updated.' , 'ppc'); ?></div>
        <div class="ppc_save_error" id="ppc_permissions_error"></div>
        <div class="save_settings">
            <img src="<?php echo $ppc_global_settings['folder_path'].'style/images/ajax-loader.gif'; ?>" title="<?php _e( 'Loading' , 'ppc'); ?>" alt="<?php _e( 'Loading' , 'ppc'); ?>" class="ajax_loader" id="ppc_permissions_ajax_loader" />
            <input type="hidden" name="userid" value="<?php echo $current_settings['userid']; ?>" />
            <input type="submit" class="button-primary" name="ppc_save_permissions" id="ppc_save_permissions" value="<?php _e( 'Save options' , 'ppc') ?>" />
        </div>
        <div class="clear"></div>
        </form>
    <?php }
    
    /**
     * Displays the metabox "Import/Export Settings" in the Options page  
     *
     * @access  public
     * @since   2.2
     * @param   object WP post object
     * @param   array plugin settings
    */
    
    static function meta_box_import_export_settings( $post, $current_settings ) {
        global $ppc_global_settings;
        $current_settings = $current_settings['args'];
        
        echo '<form action="" id="ppc_import_export_form" method="post">';
        echo '<p>'.sprintf( __( 'Have more than website but use the same settings? You can transfer settings from one installation of the plugin to another. If you are also running the PRO version, its settings will be taken as well. It works both for general and for user personalized settings, depending on what page you are. If you want to export this website\'s settings, copy the code below. If you want to import another website\'s settings, paste its settings code in the field below and click %s. Once you import settings, it will not be possible to go back to the previous settings. Do not edit settings code unless you know what base64 and serialization are and are sure of what you are doing!', 'ppc' ), __( 'Import settings', 'ppc' ) ).'</p>';
        
        echo '<textarea onclick="this.focus();this.select()" style="width: 100%; height: 100px;" name="ppc_import_settings_content" id="ppc_import_settings_content">'.base64_encode( serialize( apply_filters( 'ppc_export_settings_content', $current_settings ) ) ).'</textarea>';
        echo '<div class="clear"></div>';
        echo '<br />';
        
        do_action( 'ppc_import_export_settings_after_default', $current_settings );
        ?>
        
        <div class="ppc_save_success" id="ppc_import_settings_success"><?php _e( 'Settings were successfully updated.' , 'ppc'); ?></div>
        <div class="ppc_save_error" id="ppc_import_settings_error"></div>
        <div class="save_settings">
            <img src="<?php echo $ppc_global_settings['folder_path'].'style/images/ajax-loader.gif'; ?>" title="<?php _e( 'Loading' , 'ppc'); ?>" alt="<?php _e( 'Loading' , 'ppc'); ?>" class="ajax_loader" id="ppc_import_settings_ajax_loader" />
            <input type="hidden" name="userid" value="<?php echo $current_settings['userid']; ?>" />
            <input type="submit" class="button-primary" name="ppc_import_settings" id="ppc_import_settings" value="<?php _e( 'Import settings' , 'ppc') ?>" />
        </div>
        <div class="clear"></div>
        </form>
    <?php }
    
    /**
     * Displays the metabox "Personalize settings" in the Options page  
     *
     * @access  public
     * @since   2.0
     * @param   object WP post object
     * @param   array plugin settings
    */
    
    function meta_box_personalize_settings( $post, $current_settings ) {
        global $wpdb, $ppc_global_settings, $wp_roles;
        $current_settings = $current_settings['args'];
        
        $already_personalized = new WP_User_Query( array( 
            'meta_key' => $wpdb->prefix.$ppc_global_settings['option_name'],
            'meta_value' => '',
            'meta_compare' => '!=',
            'count_total' => true,
            'fields' => array( 
                'ID', 
                'display_name' 
            )
        ) );
        
        if( $already_personalized->total_users > 0 ) {
            echo '<p>'.__( 'The following users have different settings, click to edit them.' , 'ppc').'</p>';
            echo '<div>';
            
            $n = 0; 
            foreach( $already_personalized->results as $single ) {
                if( $n % 2 == 0 ) {
                    echo '<span style="float: left; width: 50%;">';
                } else {
                    echo '<span style="float: right; width: 50%;">';
                }
                
                echo '<a href="'.admin_url( $ppc_global_settings['options_menu_link'].'&amp;userid='.$single->ID ).'" title="'.__( 'View and edit special settings for user' , 'ppc').' \''.htmlspecialchars( $single->display_name ).'\'">'.$single->display_name.'</a>
                </span>';
                
                $n++;
            }
            
            echo '<div class="clear"></div>';
            echo '</div>';
            
        } else {
            echo '<p>'.__( 'No users have different settings. Learn how to personalize settings from the section below.' , 'ppc').'</p>';
        }
        
        echo '<p><strong>'.__( 'Personalize single user settings' , 'ppc').'</strong><br />';
        echo __( 'Some people\'s posts are better than somebody others\'? You can adjust settings for each user, so that they will have different permissions and their posts will be paid differently.' , 'ppc').'</p>';
        echo '<p>'.__( 'First, select a user role. You will see all users from that role: clicking on one you will be headed to the settings page for that specific user.' , 'ppc').'</p>';
        echo '<div id="ppc_personalize_user_roles">';
        echo '<p><strong>'.__( 'User roles' , 'ppc').'</strong><br />';
        
        $n = 0;
        foreach( $wp_roles->role_names as $role => $role_name ) {
            if( $n % 2 == 0 ) {
                echo '<span style="float: left; width: 50%;">';
            } else {
                echo '<span style="float: right; width: 50%;">';
            }
			
            echo '<a href="" title="'.$role_name.'" id="'.$role.'" class="ppc_personalize_roles">'.$role_name.'</a>';
            echo '</span>';
            
            $n++;
        }
        
        echo '</p>';
        echo '<div class="clear"></div>';
        echo '</div>';
        echo '<div style="height: 8em; overflow: auto; display: none;" id="ppc_personalize_users">';
        echo '<p><strong>'.__( 'Available users' , 'ppc').'</strong><br />';
        echo '<span id="ppc_users"></span>';
        echo '</p>';
        echo '</div>';
        echo '<div class="save_settings">';
        echo '<img src="'.$ppc_global_settings['folder_path'].'style/images/ajax-loader.gif'.'" title="'.__( 'Loading' , 'ppc').'" alt="'.__( 'Loading' , 'ppc').'" class="ajax_loader" id="ppc_personalize_settings_ajax_loader" />';
        echo '</div>';
        echo '<div class="clear"></div>';
    }
    
    /**
     * Displays the metabox "Error log" in the Options page  
     *
     * @access  public
     * @since   2.21
     * @param   object WP post object
     * @param   array plugin settings
    */
    
    function meta_box_error_log( $post, $current_settings ) {
        global $ppc_global_settings;
        $current_settings = $current_settings['args'];
        
        $errors = get_option( $ppc_global_settings['option_errors'] );
        
        echo '<p>'.sprintf( __( 'Errors which may happen during the plugin execution are logged and showed here. If something is not working properly, please send this list along with your support request. The log is cleared every now and then, but you can empty it manually with the button below. If you do not want errors to be logged at all, see the %1$sFAQ%2$s.', 'ppc' ), '<a href="http://wordpress.org/plugins/post-pay-counter/faq/" title="'.__( 'Frequently asked questions' ).'">', '</a>' ).'</p>';
        echo '<textarea readonly="readonly" onclick="this.focus();this.select()" style="width: 100%; height: 150px;" name="ppc_error_log" title="'.__( 'To copy the error log, click below then press Ctrl + C (PC) or Cmd + C (Mac).', 'ppc' ).'">';
        
        if( is_array( $errors ) AND count( $errors ) > 0 ) {
            foreach( $errors as $error ) {
                echo date( 'Y-m-d H:m:s', $error['time'] )."\n";
                echo $error['debug_message']."\n\n";
            }
        } else {
            _e( 'That\'s great, nothing has gone wrong so far!', 'ppc' );
        }
        
        echo '</textarea>';
        ?>
        
        <br />
        <input type="button" name="ppc_clear_error_log" id="ppc_clear_error_log" value="<?php _e( 'Clear error log', 'ppc' ); ?>" class="button-secondary" style="float: right;" />
        
        <div class="ppc_save_success" id="ppc_error_log_success"><?php _e( 'Log was successfully cleared.' , 'ppc'); ?></div>
        <div class="ppc_save_error" id="ppc_error_log_error"></div>
        <div class="save_settings">
        <img src="<?php echo $ppc_global_settings['folder_path'].'style/images/ajax-loader.gif'; ?>" title="<?php _e( 'Loading' , 'ppc'); ?>" alt="<?php _e( 'Loading' , 'ppc'); ?>" class="ajax_loader" id="ppc_error_log_ajax_loader" />
        </div>
        <div class="clear"></div>
        
        <?php
    }
}
?>