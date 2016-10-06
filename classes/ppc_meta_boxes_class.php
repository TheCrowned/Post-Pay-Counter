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
            __( 'Google Analytics' , 'post-pay-counter') => __( 'use your account on the world-leading website visits tracking system to pay writers per visit.' , 'post-pay-counter' ),
            __( 'Google Adsense' , 'post-pay-counter') => __( 'share a percentage of your ads revenues with writers, depending on how much their posts earn.' , 'post-pay-counter' ),
			__( 'PayPal', 'post-pay-counter') => __( 'pay your writers with PayPal directly through your blog.' , 'post-pay-counter' ),
            __( 'Mark as paid' , 'post-pay-counter') => __( 'keep track of your writers\' past payments by marking posts as paid and reviewing payments from a detailed history. Let the plugin keep track of how much each writer should be paid basing on past payments.' , 'post-pay-counter' ),
            __( 'Csv export', 'post-pay-counter') => __( 'download stats for offline consulting or storing.' , 'post-pay-counter' ),
            __( 'Shortcode', 'post-pay-counter' ) => __( 'put stats in public static pages and wherever suits your needs.', 'post-pay-counter' ),
			__( 'Stats in post editing page', 'post-pay-counter' ) => __( 'see post stats in edit page and exclude individual posts from stats.', 'post-pay-counter' )
        );

        printf( '<p>'.__( 'There are so many things you are missing by not running the PRO version of the Post Pay Counter! Remember that PRO features are always %1$sone click away%2$s!' , 'post-pay-counter'), '<a target="_blank" href="http://postpaycounter.com/post-pay-counter-pro" title="Post Pay Counter PRO">', '</a>' ).':</p>';

        echo '<ul style="margin: 0 0 15px 2em;">';

		foreach( $pro_features as $key => $single )
            echo '<li style="list-style-type: square;"><strong>'.$key.'</strong>: '.$single.'</li>';

        echo '</ul>';

        printf( '<p>'.__( 'Something you would like is missing? Complete the %1$sfeatures survey%2$s and let us know what our priorities should be!', 'post-pay-counter'), '<a target="_blank" href="http://postpaycounter.com/post-pay-counter-pro/post-pay-counter-pro-features-survey" title="Post Pay Counter PRO - '.__( 'Features survey', 'post-pay-counter' ).'">', '</a>' ).'</p>';
    }

    /**
     * Displays the metabox "Support the author" in the Options page (only if not pro version already)
     *
     * @access  public
     * @since   2.0
     */
    static function meta_box_support_the_fucking_author() {
        global $ppc_global_settings;

        echo '<p>'.__( 'If you like the Post Pay Counter, there are a couple of crucial things you can do to support its development' , 'post-pay-counter').':</p>';
        echo '<ul style="margin: 0 0 15px 2em; padding: 0">';
        echo '<li style="list-style-image: url(\''.$ppc_global_settings['folder_path'].'style/images/pro.png\');"><a target="_blank" href="http://postpaycounter.com/post-pay-counter-pro?utm_source=users_site&utm_medium=options_support_author&utm_campaign=ppcp" title="'.__( 'Go PRO' , 'post-pay-counter').'"><strong>'.__( 'Go PRO' , 'post-pay-counter').'</strong></a>. '.__( 'Try the PRO version: more functions, more stuff!' , 'post-pay-counter').'</li>';
        echo '<li style="list-style-image: url(\''.$ppc_global_settings['folder_path'].'style/images/paypal.png\');"><a target="_blank" href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=SM5Q9BVU4RT22" title="'.__( 'Donate money' , 'post-pay-counter').'"><strong>'.__( 'Donate money' , 'post-pay-counter').'</strong></a>. '.__( 'Plugins do not write themselves: they need time and effort, and I give all of that free of charge. Donations of every amount are absolutely welcome.' , 'post-pay-counter').'</li>';
        echo '<li style="list-style-image: url(\''.$ppc_global_settings['folder_path'].'style/images/amazon.png\');">'.sprintf( __( 'Give me something from my %1$sAmazon Wishlist%2$s.' , 'post-pay-counter'), '<a target="_blank" href="http://www.amazon.it/registry/wishlist/1JWAS1MWTLROQ" title="Amazon Wishlist">', '</a>' ).'</li>';
        echo '<li style="list-style-image: url(\''.$ppc_global_settings['folder_path'].'style/images/star.png\');">'.sprintf( __( 'Rate it in the %1$sWordpress Directory%3$s and share the %2$sofficial page%3$s.' , 'post-pay-counter'), '<a target="_blank" href="http://wordpress.org/extend/plugins/post-pay-counter/" title="Wordpress directory">', '<a target="_blank" href="http://postpaycounter.com" title="Official plugin page">', '</a>' ).'</li>';
        echo '<li style="list-style-image: url(\''.$ppc_global_settings['folder_path'].'style/images/write.png\');">'.__( 'Have a blog or write on some website? Write about the plugin and email me the review!' , 'post-pay-counter').'</li>';
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

		//Performance tricks
        echo '<div class="ppc_section">';
        echo '<div class="ppc_title">'.__( 'Performance' , 'post-pay-counter').'</div>';
        echo '<div class="main">';
        echo '<p>'.__( 'Stats generation can become very slow on big sites. Uncheck all these options to get best performance.', 'post-pay-counter').'</p>';
        echo PPC_HTML_functions::echo_p_field( __( 'Display overall stats' , 'post-pay-counter'), $current_settings['display_overall_stats'], 'checkbox', 'display_overall_stats', __( 'Overall stats are displayed at the bottom of regular stats. They show all-time overall stats, from the first published post ever to the latest one. Although they are quite interesting, their processing may slow down the page loading on big sites.', 'post-pay-counter'), NULL, 'display_overall_stats' );
        echo PPC_HTML_functions::echo_p_field( __( 'Make post titles clickable in stats' , 'post-pay-counter'), $current_settings['stats_display_edit_post_link'], 'checkbox', 'stats_display_edit_post_link', __( 'Allows to make post titles links that point to the post editing page or, if the user doesn\'t have permission to edit, to the public post.', 'post-pay-counter'), NULL, 'stats_display_edit_post_link' );
        echo PPC_HTML_functions::echo_p_field( __( 'Display payment tooltips in stats' , 'post-pay-counter'), $current_settings['enable_stats_payments_tooltips'], 'checkbox', 'enable_stats_payments_tooltips', __( 'By default, tooltips are displayed on hover on payment amounts and show all the details of a payment.', 'post-pay-counter'), NULL, 'enable_stats_payments_tooltips' );
        echo PPC_HTML_functions::echo_p_field( __( 'Make super cautious space parsing when computing word count' , 'post-pay-counter'), $current_settings['counting_words_parse_spaces'], 'checkbox', 'counting_words_parse_spaces', __( 'This does an extra, super-cautious parsing of blank spaces when computing word count for word payment. This is usually unnedeed (turns all kind of blank spaces into normal spaces).', 'post-pay-counter'), NULL, 'counting_words_parse_spaces' );
        echo '</div>';
        echo '</div>';
        do_action( 'ppc_misc_settings_after_overall_stats', $current_settings ); //retro-compatibility
        do_action( 'ppc_misc_settings_after_performance', $current_settings );

        //Admin permissions
        echo '<div class="ppc_section">';
        echo '<div class="ppc_title">'.__( 'Admin permissions' , 'post-pay-counter').'</div>';
        echo '<div class="main">';
        echo PPC_HTML_functions::echo_p_field( __( 'Admins override all permissions' , 'post-pay-counter'), $current_settings['admins_override_permissions'], 'checkbox', 'admins_override_permissions', __( 'By default, admins are subject to the same permissions as normal users. Checking this will make admins capable of anything, unless you give them special permissions through the Personalize settings box.', 'post-pay-counter'), NULL, 'admins_override_permissions' );
        echo '</div>';
        echo '</div>';
        do_action( 'ppc_misc_settings_after_admin_permissions', $current_settings );

        //Post types to be included in countings
        echo '<div class="ppc_section">';
        echo '<div class="ppc_title">'.__( 'Allowed post types' , 'post-pay-counter').'</div>';
        echo '<div class="main">';
        echo '<p>'.__( 'Choose the post types you would like to be included in countings.', 'post-pay-counter').'</p>';

        $all_post_types = get_post_types();
        $allowed_post_types = $current_settings['counting_allowed_post_types'];

        foreach ( $all_post_types as $single ) {
            $checked = '';

            if( in_array( $single, $allowed_post_types ) )
                $checked = 'checked="checked"';

            echo '<input type="checkbox" name="post_type_'.$single.'" id="post_type_'.$single.'" value="'.$single.'" '.$checked.' />';
            echo '<label for="post_type_'.$single.'">'.ucfirst( $single ).'</label>';
            echo '<br />';

        }

        echo '</div>';
        echo '</div>';
        do_action( 'ppc_misc_settings_after_allowed_post_types', $current_settings );

        //User roles to be included in countings
        echo '<div class="ppc_section">';
        echo '<div class="ppc_title">'.__( 'Allowed user roles' , 'post-pay-counter').'</div>';
        echo '<div class="main">';
        echo '<p>'.__( 'Choose the user roles whose posts you would like to be included in countings.', 'post-pay-counter').'</p>';

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
        echo '<div class="ppc_section">';
        echo '<div class="ppc_title">'.__( 'Options page permissions' , 'post-pay-counter').'</div>';
        echo '<div class="main">';
        echo '<p>'.__( 'Choose the user roles who are allowed to view and edit plugin settings.' , 'post-pay-counter').'</p>';
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
        echo '<div class="ppc_section">';
        echo '<div class="ppc_title">'.__( 'Stats page permissions' , 'post-pay-counter').'</div>';
        echo '<div class="main">';
        echo '<p>'.__( 'Choose the user roles who are allowed to view the stats page.' , 'post-pay-counter').'</p>';
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
        echo '<div class="ppc_section">';
        echo '<div class="ppc_title">'.__( 'Default stats time range' , 'post-pay-counter').'</div>';
        echo '<div class="main">';
        echo '<p>'.__( 'When you open up the stats page, the time range here selected will be shown. This will be the default setting: you will still be able to change the time range the way you want it time to time.' , 'post-pay-counter').'</p>';
        echo PPC_HTML_functions::echo_p_field( __( 'Current week', 'post-pay-counter' ), $current_settings['default_stats_time_range_week'], 'radio', 'default_stats_time_range', __( 'Posts from the beginning of the week to the current day (week starts on Monday) will be displayed. You should select this if you usually pay your writers weekly.' , 'post-pay-counter'), 'default_stats_time_range_week', 'default_stats_time_range_week' );
        echo PPC_HTML_functions::echo_p_field( __( 'Current month', 'post-pay-counter' ), $current_settings['default_stats_time_range_month'], 'radio', 'default_stats_time_range', __( 'Posts from the beginning of the month to the current day will be displayed. You should select this if you usually pay your writers monthly.' , 'post-pay-counter'), 'default_stats_time_range_month', 'default_stats_time_range_month' );
        echo PPC_HTML_functions::echo_p_field( __( 'Current Year', 'post-pay-counter' ), $current_settings['default_stats_time_range_this_year'], 'radio', 'default_stats_time_range', __( 'Posts from the beginning of the current year will be displayed. You should select this if you usually pay your writers yearly.' , 'post-pay-counter'), 'default_stats_time_range_this_year', 'default_stats_time_range_this_year' );
        echo PPC_HTML_functions::echo_p_field( __( 'Last month', 'post-pay-counter' ), $current_settings['default_stats_time_range_last_month'], 'radio', 'default_stats_time_range', __( 'Posts from the beginning of last month to the end of it will be displayed. You should select this if you usually pay your writers monthly.' , 'post-pay-counter'), 'default_stats_time_range_last_month', 'default_stats_time_range_last_month' );
        echo PPC_HTML_functions::echo_p_field( __( 'All time', 'post-pay-counter' ), $current_settings['default_stats_time_range_all_time'], 'radio', 'default_stats_time_range_all_time', __( 'All posts satisfying Counting Settings criteria will be displayed.' , 'post-pay-counter'), 'default_stats_time_range_all_time', 'default_stats_time_range_all_time' );
        echo PPC_HTML_functions::echo_p_field( __( 'This custom number of days', 'post-pay-counter' ), $current_settings['default_stats_time_range_custom'], 'radio', 'default_stats_time_range', __( 'You can manually customize the time range for the posts that will be displayed. So, for example, if you set this to 365 days, in the stats page it will automatically be selected a time frame that goes from the current day to the previous 365 days.' , 'post-pay-counter'), 'default_stats_time_range_custom', 'default_stats_time_range_custom' );
        echo '<div id="default_stats_time_range_custom_content" class="ppc_section">';
        echo PPC_HTML_functions::echo_text_field( 'default_stats_time_range_custom_value', $current_settings['default_stats_time_range_custom_value'], __( 'Time range (days)' , 'post-pay-counter') );
        echo '</div>';
        echo '</div>';
        echo '</div>';

        do_action( 'ppc_misc_settings_after_default_time_range', $current_settings );
        ?>

        <div class="ppc_save_success" id="ppc_misc_settings_success"><?php _e( 'Settings were successfully updated.' , 'post-pay-counter'); ?></div>
        <div class="ppc_save_error" id="ppc_misc_settings_error"></div>
        <div class="ppc_save_settings">
            <img src="<?php echo $ppc_global_settings['folder_path'].'style/images/ajax-loader.gif'; ?>" title="<?php _e( 'Loading' , 'post-pay-counter'); ?>" alt="<?php _e( 'Loading' , 'post-pay-counter'); ?>" class="ppc_ajax_loader" id="ppc_misc_settings_ajax_loader" />
            <input type="submit" class="button-primary" name="ppc_save_misc_settings" id="ppc_save_misc_settings" value="<?php _e( 'Save options' , 'post-pay-counter') ?>" />
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

        echo '<p>'.__( 'Here you can define the criteria which post payments will be computed with.' , 'post-pay-counter').'</p>';
        echo '<form action="" id="ppc_counting_settings_form" method="post">';
		do_action( 'ppc_counting_settings_before_basic_payment', $current_settings );

        //Basic payment
        echo '<div class="ppc_section">';
        echo '<div class="ppc_title">'.__( 'Basic payment' , 'post-pay-counter').'</div>';
        echo '<div class="main">';
        echo PPC_HTML_functions::echo_p_field( __( 'Basic, assured payment' , 'post-pay-counter'), $current_settings['basic_payment'], 'checkbox', 'basic_payment', __( 'You may define a starting value for post payment. This means that each post will earn at least this amount, to which all the other credits will be added. In this way you can be sure that no post will be paid less than a certain amount, but that only valuable posts will make it to higher points.' , 'post-pay-counter'), NULL, 'basic_payment' );
        echo '</div>';
        echo '<div class="ppc_content" id="ppc_basic_payment_content">';
        echo PPC_HTML_functions::echo_text_field( 'basic_payment_value', $current_settings['basic_payment_value'], __( 'Basic payment fixed value' , 'post-pay-counter') );
        echo '<div class="ppc_title">'.__( 'Counting options' , 'post-pay-counter').'</div>';
        echo PPC_options_fields::echo_counting_type_display_dropdown( 'basic_payment', $current_settings['basic_payment_display_status'] );
        echo '</div>';
        echo '</div>';
        do_action( 'ppc_counting_settings_after_basic_payment', $current_settings );

        //Words payment
        echo '<div class="ppc_section">';
        echo '<div class="ppc_title">'.__( 'Words Payment' , 'post-pay-counter').'</div>';
        echo '<div class="main">';
        echo PPC_HTML_functions::echo_p_field( __( 'Words contribute to payment computation' , 'post-pay-counter'), $current_settings['counting_words'], 'checkbox', 'counting_words', __( 'You may define a post value basing on the number of words that make it up as well. The longer a post is, the more time is supposed to have taken the author to write it, the more it should be paid. You will be able to choose how much each word is worth.' , 'post-pay-counter'), NULL, 'counting_words' );
        echo '</div>';
        echo '<div class="ppc_content" id="ppc_counting_words_content">';
        echo '<div class="ppc_title">'.__( 'Counting system' , 'post-pay-counter').'</div>';
        echo PPC_options_fields::echo_payment_systems( 'words', __( 'words', 'post-pay-counter' ), array( 'counting_words_system_zonal' => $current_settings['counting_words_system_zonal'], 'counting_words_system_zonal_value' => $current_settings['counting_words_system_zonal_value'], 'counting_words_system_incremental' => $current_settings['counting_words_system_incremental'], 'counting_words_system_incremental_value' => $current_settings['counting_words_system_incremental_value'] ) );
        echo '<div class="ppc_title">'.__( 'Counting options' , 'post-pay-counter').'</div>';
        echo PPC_HTML_functions::echo_text_field( 'counting_words_threshold_max', $current_settings['counting_words_threshold_max'], __( 'Stop counting words after word # (0 = infinite)' , 'post-pay-counter') );
        echo PPC_options_fields::echo_counting_type_display_dropdown( 'counting_words', $current_settings['counting_words_display_status'] );
        echo PPC_HTML_functions::echo_p_field( 'Include post excerpt in words counting', $current_settings['counting_words_include_excerpt'], 'checkbox', 'counting_words_include_excerpt', __( 'Determines whether post excerpt text should be included in words counting.' , 'post-pay-counter') );
        echo '</div>';
        echo '</div>';
        do_action( 'ppc_counting_settings_after_words_payment', $current_settings );

        //Visits payment
        echo '<div class="ppc_section">';
        echo '<div class="ppc_title">'.__( 'Visits Payment' , 'post-pay-counter').'</div>';
        echo '<div class="main">';
        echo PPC_HTML_functions::echo_p_field( __( 'Visits contribute to payment computation' , 'post-pay-counter'), $current_settings['counting_visits'], 'checkbox', 'counting_visits', __( 'You may define a post value basing on the number of visits that it registers as well. The more people see a post, the more interesting the post is supposed to be, the more it should be paid. You will be able to choose how much each visit is worth.' , 'post-pay-counter'), NULL, 'counting_visits' );
        echo '</div>';
        echo '<div class="ppc_content" id="ppc_counting_visits_content">';
        echo '<p>'.sprintf( __( 'How to setup visits payment? Have a look at our %1$svisits tutorials%2$s.', 'post-pay-counter' ), '<a href="http://postpaycounter.com/tag/visits/?utm_source=users_site&utm_medium=options_box&utm_campaign=tutorials" title="Visits tutorials" target="_blank">', '</a>' ).'</p>';
        echo '<div class="ppc_title">'.__( 'Counting method' , 'post-pay-counter').'</div>';
        echo PPC_HTML_functions::echo_p_field( __( 'I have my own visit counter (postmeta)' , 'post-pay-counter'), $current_settings['counting_visits_postmeta'], 'radio', 'counting_visits_method', sprintf( __( 'If you already have some plugin counting visits, and you know the %1$spostmeta%2$s name it stores them into, you can use those data to compute payments. Activate this setting and put the postmeta in the field below.' , 'post-pay-counter'), '<em>', '</em>' ), 'counting_visits_postmeta', 'counting_visits_postmeta' );
        echo '<div id="counting_visits_postmeta_content" class="field_value">';
        echo PPC_HTML_functions::echo_text_field( 'counting_visits_postmeta_value', $current_settings['counting_visits_postmeta_value'], __( 'The postmeta holding the visits' , 'post-pay-counter'), 22 );
        echo '</div>';
		echo PPC_HTML_functions::echo_p_field( __( 'I have my own visit counter (callback)' , 'post-pay-counter'), $current_settings['counting_visits_callback'], 'radio', 'counting_visits_method', sprintf( __( 'If you already have some plugin counting visits, and it provides a PHP %1$scallback%2$s which accepts as input the $post WP object and outputs an integer number of visits, you can use those data to compute payments. If the callback is a class method, specify it in the form of %1$sclassname, methodname%2$s. This is NOT user-personalizable.' , 'post-pay-counter'), '<em>', '</em>' ), 'counting_visits_callback', 'counting_visits_callback' );
        echo '<div id="counting_visits_callback_content" class="field_value">';
        echo PPC_HTML_functions::echo_text_field( 'counting_visits_callback_value', $current_settings['counting_visits_callback_value'], __( 'Callback name' , 'post-pay-counter'), 22, 'classname, methodname' );
        echo '</div>';
        do_action( 'ppc_counting_settings_after_visits_counting_method', $current_settings );
        echo '<div class="ppc_title">'.__( 'Counting system' , 'post-pay-counter').'</div>';
        echo PPC_options_fields::echo_payment_systems( 'visits', __( 'visits', 'post-pay-counter' ), array( 'counting_visits_system_zonal' => $current_settings['counting_visits_system_zonal'], 'counting_visits_system_zonal_value' => $current_settings['counting_visits_system_zonal_value'], 'counting_visits_system_incremental' => $current_settings['counting_visits_system_incremental'], 'counting_visits_system_incremental_value' => $current_settings['counting_visits_system_incremental_value'] ) );
        echo '<div class="ppc_title">'.__( 'Counting options' , 'post-pay-counter').'</div>';
        echo PPC_HTML_functions::echo_text_field( 'counting_visits_threshold_max', $current_settings['counting_visits_threshold_max'], __( 'Stop counting visits after visit # (0 = infinite)' , 'post-pay-counter') );
        echo PPC_options_fields::echo_counting_type_display_dropdown( 'counting_visits', $current_settings['counting_visits_display_status'] );
        echo '</div>';
        echo '</div>';
        do_action( 'ppc_counting_settings_after_visits_payment', $current_settings );

        //Images payment
        echo '<div class="ppc_section">';
        echo '<div class="ppc_title">'.__( 'Images Payment' , 'post-pay-counter').'</div>';
        echo '<div class="main">';
        echo PPC_HTML_functions::echo_p_field( __( 'Images contribute to payment computation' , 'post-pay-counter'), $current_settings['counting_images'], 'checkbox', 'counting_images', sprintf( __( 'You may define a post value basing on the number of images it contains. Maybe more images make a post cleaerer to the readers, and should thus be paid something more. You will be able to choose: when you want the image counting to come in, meaning how many images are free of charge and after which one they should be paid; how much each image is worth; how many images at maximum should be paid (0 = no maximum, infinite). E.g. we have a post with 5 images, and the fields below are set like this: %s. The image payment would be 1.0 bacause image #3 and image #4 are counted.' , 'post-pay-counter'), '<em>2; 0.5; 4</em>' ), NULL, 'counting_images' );
        echo '</div>';
        echo '<div class="ppc_content" id="ppc_counting_images_content">';
        echo '<div class="ppc_title">'.__( 'Counting system' , 'post-pay-counter').'</div>';
        echo PPC_options_fields::echo_payment_systems( 'images', __( 'images', 'post-pay-counter' ), array( 'counting_images_system_zonal' => $current_settings['counting_images_system_zonal'], 'counting_images_system_zonal_value' => $current_settings['counting_images_system_zonal_value'], 'counting_images_system_incremental' => $current_settings['counting_images_system_incremental'], 'counting_images_system_incremental_value' => $current_settings['counting_images_system_incremental_value'] ) );
        echo '<div class="ppc_title">'.__( 'Counting options' , 'post-pay-counter').'</div>';
        echo PPC_HTML_functions::echo_text_field( 'counting_images_threshold_min', $current_settings['counting_images_threshold_min'], __( 'Start paying per image after image #' , 'post-pay-counter') );
        echo PPC_HTML_functions::echo_text_field( 'counting_images_threshold_max', $current_settings['counting_images_threshold_max'], __( 'Stop paying per image after image #' , 'post-pay-counter') );
        echo PPC_HTML_functions::echo_p_field( 'Include featured image in counting', $current_settings['counting_images_include_featured'], 'checkbox', 'counting_images_include_featured', __( 'Determines whether the featured image will be included in image counting.' , 'post-pay-counter') );
		echo PPC_HTML_functions::echo_p_field( 'Include gallery images in counting', $current_settings['counting_images_include_galleries'], 'checkbox', 'counting_images_include_galleries', __( 'Determines whether images in galleries should be included in image counting (may slow down stats page loading due to additional post content parsing).' , 'post-pay-counter') );
		echo PPC_options_fields::echo_counting_type_display_dropdown( 'counting_images', $current_settings['counting_images_display_status'] );
        echo '</div>';
        echo '</div>';
        do_action( 'ppc_counting_settings_after_images_payment', $current_settings );

        //Comments payment
        echo '<div class="ppc_section">';
        echo '<div class="ppc_title">'.__( 'Comments Payment' , 'post-pay-counter').'</div>';
        echo '<div class="main">';
        echo PPC_HTML_functions::echo_p_field( __( 'Comments contribute to payment computation' , 'post-pay-counter'), $current_settings['counting_comments'], 'checkbox', 'counting_comments', sprintf( __( 'You may define a post value basing on the number of comments it receives. You will be able to choose: when you want the comment counting to come in, meaning how many comments are free of charge and after which one they should be paid; how much each comment is worth; how many comments at maximum should be paid (0 = no maximum, infinite). E.g. we have a post with 30 comments, and the fields below are set like this: %s. The comment payment would be 2.5 bacause comments from #11 included to #25 included are counted.' , 'post-pay-counter'), '<em>10; 0.1; 25</em>' ), NULL, 'counting_comments' );
        echo '</div>';
        echo '<div class="ppc_content" id="ppc_counting_comments_content">';
        echo '<div class="ppc_title">'.__( 'Counting system' , 'post-pay-counter').'</div>';
        echo PPC_options_fields::echo_payment_systems( 'comments', __( 'comments', 'post-pay-counter' ), array( 'counting_comments_system_zonal' => $current_settings['counting_comments_system_zonal'], 'counting_comments_system_zonal_value' => $current_settings['counting_comments_system_zonal_value'], 'counting_comments_system_incremental' => $current_settings['counting_comments_system_incremental'], 'counting_comments_system_incremental_value' => $current_settings['counting_comments_system_incremental_value'] ) );
        echo '<div class="ppc_title">'.__( 'Counting options' , 'post-pay-counter').'</div>';
        echo PPC_HTML_functions::echo_text_field( 'counting_comments_threshold_min', $current_settings['counting_comments_threshold_min'], __( 'Start paying per comment after comment #' , 'post-pay-counter') );
        echo PPC_HTML_functions::echo_text_field( 'counting_comments_threshold_max', $current_settings['counting_comments_threshold_max'], __( 'Stop paying per comment after comment #' , 'post-pay-counter') );
        echo PPC_options_fields::echo_counting_type_display_dropdown( 'counting_comments', $current_settings['counting_comments_display_status'] );
        echo '</div>';
        echo '</div>';
        do_action( 'ppc_counting_settings_after_comments_payment', $current_settings );

        //Total payment
        echo '<div class="ppc_section">';
        echo '<div class="ppc_title">'.__( 'Total payment' , 'post-pay-counter').'</div>';
        echo '<div class="main">';
        echo PPC_HTML_functions::echo_text_field( 'counting_payment_total_threshold', $current_settings['counting_payment_total_threshold'], __( 'Set payment maximum (0 = infinite)' , 'post-pay-counter') );
        echo PPC_HTML_functions::echo_p_field( __( 'Pay only when the total payment threshold is reached' , 'post-pay-counter'), $current_settings['counting_payment_only_when_total_threshold'], 'checkbox', 'counting_payment_only_when_total_threshold', __( 'Check this if you want to pay items only when they reach the max payment threshold. Other items will appear grayed out.' , 'post-pay-counter'), 'counting_payment_only_when_total_threshold', 'counting_payment_only_when_total_threshold' );
        echo '</div>';
        echo '</div>';
        do_action( 'ppc_counting_settings_after_total_payment', $current_settings );

        //Misc
        echo '<div class="ppc_section">';
        echo '<div class="ppc_title">'.__( 'Miscellanea counting settings' , 'post-pay-counter').'</div>';
        echo '<div class="main">';
        echo PPC_HTML_functions::echo_p_field( __( 'Count pending revision posts', 'post-pay-counter' ), $current_settings['counting_allowed_post_statuses']['pending'], 'checkbox', 'counting_count_pending_revision_posts', __( 'While published posts are automatically counted, you can decide to include pending revision ones or not.' , 'post-pay-counter') );
        echo PPC_HTML_functions::echo_p_field( __( 'Count future scheduled posts', 'post-pay-counter' ), $current_settings['counting_allowed_post_statuses']['future'], 'checkbox', 'counting_count_future_scheduled_posts', __( 'While published posts are automatically counted, you can decide to include future planned ones or not.' , 'post-pay-counter') );
        echo PPC_HTML_functions::echo_p_field( __( 'Count private posts', 'post-pay-counter' ), $current_settings['counting_allowed_post_statuses']['private'], 'checkbox', 'counting_count_private_posts', __( 'While public published posts are automatically counted, you can decide to include private ones or not.' , 'post-pay-counter') );
        echo PPC_HTML_functions::echo_p_field( __( 'Exclude quoted content from word counting', 'post-pay-counter' ), $current_settings['counting_exclude_quotations'], 'checkbox', 'counting_exclude_quotations', sprintf( __( 'All the words contained into %1$sblockquote%2$s tags will not be taken into account when counting. Use this to prevent interviews and such stuff to be counted as normal words. Notice that words included in any HTML tag with class %1$sppc_exclude_words%2$s are automatically excluded from word counting (doesn\'t handle nested tags); see FAQ for more information.' , 'post-pay-counter'), '<em>', '</em>' ) );
        echo '</div>';
        echo '</div>';
        do_action( 'ppc_counting_settings_after_misc', $current_settings );

        echo '<div class="ppc_save_success" id="ppc_counting_settings_success">'.__( 'Settings were successfully updated.' , 'post-pay-counter').'</div>';
        echo '<div class="ppc_save_error" id="ppc_counting_settings_error"></div>';
        echo '<div class="ppc_save_settings">';
        echo '<img src="'.$ppc_global_settings['folder_path'].'style/images/ajax-loader.gif'.'" title="'.__( 'Loading' , 'post-pay-counter').'" alt="'.__( 'Loading' , 'post-pay-counter').'" class="ppc_ajax_loader" id="ppc_counting_settings_ajax_loader" />';
        echo '<input type="hidden" name="userid" value="'.$current_settings['userid'].'" />';
        echo '<input type="submit" class="button-primary" name="ppc_save_counting_settings" id="ppc_save_counting_settings" value="'.__( 'Save options' , 'post-pay-counter').'" />';
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
        echo '<p>'.__( 'Just a few fields to help you preventing users from seeing things they should not see. Administrators are subject to the same permissions; if you wish they did not, personalize their user settings.' , 'post-pay-counter').'</p>';
        echo PPC_HTML_functions::echo_p_field( __( 'Users can see other users\' general stats' , 'post-pay-counter'), $current_settings['can_see_others_general_stats'], 'checkbox', 'can_see_others_general_stats', __( 'If unchecked, users will only be able to see their stats in the general page. Other users\' names, posts and pay counts will not be displayed.' , 'post-pay-counter') );
        echo PPC_HTML_functions::echo_p_field( __( 'Users can see other users\' detailed stats' , 'post-pay-counter'), $current_settings['can_see_others_detailed_stats'], 'checkbox', 'can_see_others_detailed_stats', __( 'If unchecked, users will not be able to see other users\' detailed stats but will still able to see general ones. ' , 'post-pay-counter') );
        echo PPC_HTML_functions::echo_p_field( __( 'Let users know if other users have personalized settings' , 'post-pay-counter'), $current_settings['can_see_countings_special_settings'], 'checkbox', 'can_see_countings_special_settings', __( 'If you personalize settings by user, do not overlook this. If unchecked, users will not see personalized settings in countings, they will believe everybody is using their settings (or general settings). Anyway, users will see their own personalized settings, if they have them.' , 'post-pay-counter') );

		do_action( 'ppc_permissions_settings_after_default', $current_settings );
        ?>

        <div class="ppc_save_success" id="ppc_permissions_success"><?php _e( 'Settings were successfully updated.' , 'post-pay-counter'); ?></div>
        <div class="ppc_save_error" id="ppc_permissions_error"></div>
        <div class="ppc_save_settings">
            <img src="<?php echo $ppc_global_settings['folder_path'].'style/images/ajax-loader.gif'; ?>" title="<?php _e( 'Loading' , 'post-pay-counter'); ?>" alt="<?php _e( 'Loading' , 'post-pay-counter'); ?>" class="ppc_ajax_loader" id="ppc_permissions_ajax_loader" />
            <input type="hidden" name="userid" value="<?php echo $current_settings['userid']; ?>" />
            <input type="submit" class="button-primary" name="ppc_save_permissions" id="ppc_save_permissions" value="<?php _e( 'Save options' , 'post-pay-counter') ?>" />
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
        $userid = $current_settings['userid'];
        $current_settings = PPC_general_functions::get_settings( $current_settings['userid'], false, false );

        echo '<form action="" id="ppc_import_export_form" method="post">';
        echo '<p>'.sprintf( __( 'Have more than website but use the same settings? You can transfer settings from one installation of the plugin to another. All addons settings will be taken as well. It works both for general and for user personalized settings, depending on what page you are. If you want to export this website\'s settings, copy the code below. If you want to import another website\'s settings, paste its settings code in the field below and click %s. Once you import settings, it will not be possible to go back to the previous settings. Do not edit settings code unless you know what base64 and serialization are and are sure of what you are doing!', 'post-pay-counter' ), __( 'Import settings', 'post-pay-counter' ) ).'</p>';

        echo '<textarea onclick="this.focus();this.select()" style="width: 100%; height: 100px;" name="ppc_import_settings_content" id="ppc_import_settings_content">'.base64_encode( serialize( apply_filters( 'ppc_export_settings_content', $current_settings ) ) ).'</textarea>';
        echo '<div class="clear"></div>';
        echo '<br />';

        do_action( 'ppc_import_export_settings_after_default', $current_settings );
        ?>

        <div class="ppc_save_success" id="ppc_import_settings_success"><?php _e( 'Settings were successfully updated.' , 'post-pay-counter'); ?></div>
        <div class="ppc_save_error" id="ppc_import_settings_error"></div>
        <div class="ppc_save_settings">
            <img src="<?php echo $ppc_global_settings['folder_path'].'style/images/ajax-loader.gif'; ?>" title="<?php _e( 'Loading' , 'post-pay-counter'); ?>" alt="<?php _e( 'Loading' , 'post-pay-counter'); ?>" class="ppc_ajax_loader" id="ppc_import_settings_ajax_loader" />
            <input type="hidden" name="userid" id="ppc_import_settings_userid" value="<?php echo $userid; ?>" />
            <input type="submit" class="button-primary" name="ppc_import_settings" id="ppc_import_settings" value="<?php _e( 'Import settings' , 'post-pay-counter') ?>" />
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

    static function meta_box_personalize_settings( $post, $current_settings ) {
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
            echo '<p>'.__( 'The following users have different settings, click to edit them.' , 'post-pay-counter').'</p>';
            echo '<div>';

            $n = 0;
            foreach( $already_personalized->results as $single ) {
                if( $n % 2 == 0 )
                    echo '<span style="float: left; width: 50%;">';
                else
                    echo '<span style="float: right; width: 50%;">';

                echo '<a href="'.admin_url( $ppc_global_settings['options_menu_link'].'&amp;userid='.$single->ID ).'" title="'.__( 'View and edit special settings for user' , 'post-pay-counter').' \''.htmlspecialchars( $single->display_name ).'\'">'.$single->display_name.'</a>
                </span>';

                $n++;
            }

            echo '<div class="clear"></div>';
            echo '</div>';

        } else {
            echo '<p>'.__( 'No users have different settings. Learn how to personalize settings from the section below.' , 'post-pay-counter').'</p>';
        }

        /**
		 * Fires after the list of users who already have personalized settings.
		 *
		 * @since	2.518
		 */

        do_action( 'ppc_personalize_settings_box_after_already_personalized_users' );

        echo '<p><strong>'.__( 'Personalize single user settings' , 'post-pay-counter').'</strong><br />';
        echo __( 'Some people\'s posts are better than somebody others\'? You can adjust settings for each user, so that they will have different permissions and their posts will be paid differently.' , 'post-pay-counter').'</p>';
        echo '<p>'.__( 'First, select a user role. You will see all users from that role: clicking on one you will be headed to the settings page for that specific user.' , 'post-pay-counter').'</p>';
        echo '<div id="ppc_personalize_user_roles">';
        echo '<p><strong>'.__( 'User roles' , 'post-pay-counter').'</strong><br />';

        $n = 0;
        foreach( $wp_roles->role_names as $role => $role_name ) {
            if( $n % 2 == 0 )
                echo '<span style="float: left; width: 50%;">';
            else
                echo '<span style="float: right; width: 50%;">';

            echo '<a href="" title="'.$role_name.'" id="'.$role.'" class="ppc_personalize_roles">'.$role_name.'</a>';
            echo '</span>';

            $n++;
        }

        echo '</p>';
        echo '<div class="clear"></div>';
        echo '</div>';
        echo '<div id="ppc_personalize_users">';
        echo '<p style="margin-top: 0px;"><strong>'.__( 'Available users' , 'post-pay-counter').'</strong><br />';
        echo '<span id="ppc_users"></span>';
        echo '</p>';
        echo '</div>';
        echo '<div class="ppc_save_settings">';
        echo '<img src="'.$ppc_global_settings['folder_path'].'style/images/ajax-loader.gif'.'" title="'.__( 'Loading' , 'post-pay-counter').'" alt="'.__( 'Loading' , 'post-pay-counter').'" class="ppc_ajax_loader" id="ppc_personalize_settings_ajax_loader" />';
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

    static function meta_box_error_log( $post, $current_settings ) {
        global $ppc_global_settings;
        $current_settings = $current_settings['args'];

        $errors = get_option( $ppc_global_settings['option_errors'], array() );
        ?>

        <p><?php printf( __( 'Errors which may happen during the plugin execution are logged and showed here. If something is not working properly, please send this list along with your support request. The log is cleared every now and then, but you can empty it manually with the button below. If you do not want errors to be logged at all, see the %1$sFAQ%2$s.', 'post-pay-counter' ), '<a href="http://wordpress.org/plugins/post-pay-counter/faq/" title="'.__( 'Frequently asked questions' ).'">', '</a>' ); ?></p>
        <textarea readonly="readonly" onclick="this.focus();this.select()" style="width: 100%; height: 150px;" name="ppc_error_log" title="<?php _e( 'To copy the error log, click below then press Ctrl + C (PC) or Cmd + C (Mac).', 'post-pay-counter' ); ?>"><?php


        if( is_array( $errors) AND count( $errors ) > 0 ) {
            foreach( $errors as $error ) {
                echo date_i18n( get_option( 'date_format' ), $error['time'] ).' '.date( 'H:i:s', $error['time'] )."\n";
                echo $error['debug_message']."\n\n";
            }
        } else {
            _e( 'That\'s great, nothing has gone wrong so far!', 'post-pay-counter' );
        }

		?></textarea>

        <br />
        <input type="button" name="ppc_clear_error_log" id="ppc_clear_error_log" value="<?php _e( 'Clear error log', 'post-pay-counter' ); ?>" class="button-secondary" style="float: right; margin-top: 5px;" />

        <div class="ppc_save_success" id="ppc_error_log_success"><?php _e( 'Log was successfully cleared.' , 'post-pay-counter'); ?></div>
        <div class="ppc_save_error" id="ppc_error_log_error"></div>
        <div class="ppc_save_settings">
        <img src="<?php echo $ppc_global_settings['folder_path'].'style/images/ajax-loader.gif'; ?>" title="<?php _e( 'Loading' , 'post-pay-counter'); ?>" alt="<?php _e( 'Loading' , 'post-pay-counter'); ?>" class="ppc_ajax_loader" id="ppc_error_log_ajax_loader" />
        </div>
        <div class="clear"></div>

        <?php
    }

	/**
     * Displays the metabox "License" in the Options page
     *
     * @access  public
     * @since   2.511
     * @param   $post object WP post object
	 * @param	$current_settings plugin settings
     */
    static function meta_box_license( $post, $current_settings ) {
        global $ppc_global_settings;
        $current_settings = $current_settings['args'];

        //License cron check
        if( ! wp_next_scheduled( 'ppcp_cron_check_activation' ) )
			wp_schedule_event( time(), 'weekly2', 'ppcp_cron_check_activation' );
        ?>

        <p><?php printf( __( 'Whatever of our %1$splenty of addons%2$s you may have bought, this is the place to activate your license. Make sure you have already uploaded the addon files, activated it, and paste the license key you have received by email in the field below.', 'ppc' ), '<a target="_blank" href="'.admin_url( add_query_arg( array( 'page' => 'ppc-addons' ), 'admin.php' ) ).'" title="Post Pay Counter Addons">', '</a>' ); ?></p>
		<p><em><?php _e( 'To activate your license key, the following data will be sent to the activation page: license key, website URL, blog language, plugin version. Twice a month the plugin will call home to check that your license is genuine and valid without asking for permission.', 'ppc'); ?></em></p>
        <p>
        <input type="text" name="ppc_license_key" id="ppc_license_key" size="40" />
        <input type="button" name="ppc_license_key_submit" id="ppc_license_key_submit" value="<?php _e('Submit', 'ppc'); ?>" disabled="disabled" class="button-secondary" />
        </p>

		<div class="ppc_save_success" id="ppc_license_success"><?php _e( 'Your license was successfully activated. Reload this page and enjoy!', 'ppc'); ?></div>
        <div class="ppc_save_error" id="ppc_license_error"></div>
        <div class="ppc_save_settings">
        <img src="<?php echo $ppc_global_settings['folder_path'].'style/images/ajax-loader.gif'; ?>" title="<?php _e( 'Loading', 'ppc'); ?>" alt="<?php _e( 'Loading', 'ppc'); ?>" class="ppc_ajax_loader" id="ppc_license_ajax_loader" />
        </div>
        <div class="clear"></div>

        <div class="ppc_section">
        <div class="ppc_title"><?php _e( 'Your licenses' , 'ppc' ); ?></div>

        <?php
        $licenses = array();
        $licenses = apply_filters( 'ppcp_license_display_options_page', $licenses );

        if( count( $licenses ) == 0 ) {
        ?>

        <p><?php _e( 'No licenses to display.', 'ppc'); ?></p>

        <?php } else { ?>

        <p><?php _e( 'Your licenses status is listed below, along with their details. You may want to deactivate one to use it on other websites (but you won\'t be able to use its features on this site anymore).', 'ppc'); ?></p>
        <table class="widefat fixed">
        	<thead>
        		<tr>
        			<th style="width: 30%"><?php _e( 'Addon', 'ppc' ); ?></th>
        			<th><?php _e( 'Status', 'ppc' ); ?></th>
        			<th style="width: 15%;"><?php _ex( 'Exp. time', '(license) Expiration time', 'ppc' ); ?></th>
                    <th><?php _e( 'Deactivate', 'ppc' ); ?></th>
        		</tr>
        	</thead>
        	<tbody>

            <?php
            foreach( $licenses as $single ) {
                if( ! isset( $single['expiration'] ) )
					$status = '<span style="color: gray;">'.__( 'Unknown', 'ppc' ).'</span>';
				else if( $single['expiration'] - current_time( 'timestamp' ) < 0 )
                    $status = '<span style="color: red;">'.sprintf( __( 'Awfully sad - Expired | %1$sRenew%2$s', 'ppc' ), '<a target="_blank" href="'.$single['renewal_url'].'" title="'.__( 'Renew!', 'ppc' ).'">', '</a>' ).'</span>';
                else if( $single['expiration'] - current_time( 'timestamp' ) < 3888000 ) //1.5 months
                    $status = '<span style="color: orange;">'.sprintf( __( 'Anxious - In expiration | %1$sRenew%2$s', 'ppc' ), '<a target="_blank" href="'.$single['renewal_url'].'" title="'.__( 'Renew!', 'ppc' ).'">', '</a>' ).'</span>';
                else if( $single['expiration'] - current_time( 'timestamp' ) > 3888000 )
                    $status = '<span style="color: green;">'.__( 'Astonishingly happy', 'ppc' ).'</span>';

                if( ! $single['expiration'] )
                    $expiration = 'N.A.';
                else
                    $expiration = date_i18n( get_option( 'date_format' ), $single['expiration'] );
                ?>

                <tr>
                    <td><?php echo $single['name']; ?></td>
                    <td><?php echo $status ?></td>
                    <td><?php echo $expiration ?></td>
                    <td><input type="button" name="ppc_license_deactivate" accesskey="<?php echo $single['slug']; ?>" class="button-secondary ppc_license_deactivate" id="ppc_license_deactivate" value="<?php _e( 'Deactivate license', 'ppc'); ?>" /></td>
                </tr>

            <?php } ?>

            </tbody>
        </table>

        <?php } ?>

        </div>

        <?php
    }
}
