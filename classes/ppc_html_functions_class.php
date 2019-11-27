<?php

/**
 * @author Stefano Ottolenghi
 * @copyright 2013
 * @package	PPC
 */

require_once( 'ppc_permissions_class.php' );

class PPC_HTML_functions {

    /**
     * Shows header part for the stats page, including the form to adjust the time window
     *
     * @access  public
     * @since   2.0
     * @param   $current_page string current page title
     * @param   $page_permalink string current page permalink
     */
    static function show_stats_page_header( $current_page, $page_permalink ) {
        global $ppc_global_settings, $wp_roles;
        $perm = new PPC_permissions();
        $general_settings = PPC_general_functions::get_settings( 'general' );
		?>

<form action="" method="post">
	<div id="ppc_stats_header">
		<div id="ppc_stats_header_datepicker">
			<h3>

        <?php echo __( 'Showing posts for' , 'post-pay-counter').' ';
		$time_range_options = array(
			'this_month' => __( 'This Month',  'post-pay-counter' ),
			'last_month' => __( 'Last Month',  'post-pay-counter' ),
			'this_year' => __( 'This Year',  'post-pay-counter' ),
			'this_week' => __( 'This Week',  'post-pay-counter' ),
			'all_time' => __( 'All Time',  'post-pay-counter' ),
			'custom' => __( 'Custom',  'post-pay-counter' )
		);

		echo '<select name="ppc-time-range" id="ppc-time-range">';

		$_REQUEST = array_merge( $_GET, $_POST );
		foreach( $time_range_options as $key => $value ) {
			$checked = '';

			//Default select choice
			if( isset( $_REQUEST['ppc-time-range'] ) ) {
				if( $_REQUEST['ppc-time-range'] == $key )
					$checked = 'selected="selected"';
			} else {
				if( $general_settings['default_stats_time_range_week'] AND $key == 'this_week' )
					$checked = 'selected="selected"';
				else if( $general_settings['default_stats_time_range_month'] AND $key == 'this_month' )
					$checked = 'selected="selected"';
				else if( $general_settings['default_stats_time_range_last_month'] AND $key == 'last_month' )
					$checked = 'selected="selected"';
				else if( $general_settings['default_stats_time_range_this_year'] AND $key == 'this_year' )
					$checked = 'selected="selected"';
				else if( $general_settings['default_stats_time_range_all_time'] AND $key == 'all_time' )
					$checked = 'selected="selected"';
				else if( ( $general_settings['default_stats_time_range_custom'] OR $general_settings['default_stats_time_range_start_day'] ) AND $key == 'custom' )
					$checked = 'selected="selected"';
			}

			echo '<option value="'.$key.'" '.$checked.'>'.$value.'</option>';
		}

		echo '</select>';
		echo ' - "'.$current_page.'"';

		echo '<div id="ppc-time-range-custom" style="display: none; margin-top: 10px;">';
		echo sprintf( __( 'From %1$s to %2$s', 'post-pay-counter'), '<input type="text" name="tstart" id="post_pay_counter_time_start" class="mydatepicker" value="'.date( 'Y-m-d', $ppc_global_settings['stats_tstart'] ).'" accesskey="'.$ppc_global_settings['stats_tstart'].'" size="8" />', '<input type="text" name="tend" id="post_pay_counter_time_end" class="mydatepicker" value="'.date( 'Y-m-d', $ppc_global_settings['stats_tend'] ).'" accesskey="'.$ppc_global_settings['stats_tend'].'" size="8" />' );
		echo '</div>';

		//Display filter by user role field in general stats
		if( $ppc_global_settings['current_page'] == 'stats_general' AND $perm->can_see_others_general_stats() ) {
			echo '<div style="margin-top: 10px;">';
			echo __( 'Filter by user role', 'post-pay-counter' ). ' ';
			echo '<select name="role" id="ppc_stats_role">';
			echo '<option value="ppc_any">'.__( 'Any', 'post-pay-counter' ).'</option>';

			foreach( $wp_roles->role_names as $key => $value ) {
				if( ! in_array( $key, $general_settings['counting_allowed_user_roles'] ) ) continue; //skip non-allowed roles

				$checked = '';

				if( isset( $ppc_global_settings['stats_role'] ) AND $key == $ppc_global_settings['stats_role'] )
					$checked = 'selected="selected"';

				echo '<option value="'.$key.'" '.$checked.'>'.$value.'</option>';
			}

			echo '</select>';
			echo ' - '.__( 'User', 'post-pay-counter' ).' ';
			echo '<select name="author" id="ppc_stats_user">';
			echo '<option value="ppc_any">'.__( 'Any', 'post-pay-counter' ).'</option>';

			$all_users = get_users( array( 'orderby' => 'nicename', 'role__in' => $general_settings['counting_allowed_user_roles'], 'fields' => array( 'ID', 'display_name' ) ) );
			foreach( $all_users as $user ) {
				$checked = '';

				if( isset( $ppc_global_settings['stats_user'] ) AND $key == $ppc_global_settings['stats_user'] )
					$checked = 'selected="selected"';

				echo '<option value="'.$user->ID.'" '.$checked.' />'.$user->display_name.'</option>';
			}

			echo '</select>';
			echo '</div>';
		}

		/**
		 * Fires after the HTML display of "Showing stats from ... to ... - "General|User" - Role" in stats page heading.
		 *
		 * @since	2.49
		 * @param	string $current_page whether "General" or username of currently displayed author.
		 * @param	string $page_permalink page URL
		 */

		do_action( 'ppc_stats_after_time_range_fields', $current_page, $page_permalink );
		?>
			</h3>
		</div>

		<div id="ppc_stats_header_features">
			<span id="ppc_stats_header_links">
		<?php
		if( $ppc_global_settings['current_page'] == 'stats_detailed' ) {

			if( ! isset( $_REQUEST['ppc-time-range'] ) ) { ?>

				<a href="<?php echo admin_url( $ppc_global_settings['stats_menu_link'].'&amp;tstart='.$ppc_global_settings['stats_tstart'].'&amp;tend='.$ppc_global_settings['stats_tend'] ); ?>" title="<?php _e( 'Back to general' , 'post-pay-counter'); ?>"><?php _e( 'Back to general' , 'post-pay-counter'); ?></a>

			<?php } else { ?>

				<a href="<?php echo admin_url( $ppc_global_settings['stats_menu_link'].'&amp;tstart='.$ppc_global_settings['stats_tstart'].'&amp;tend='.$ppc_global_settings['stats_tend'].'&amp;ppc-time-range='.$_REQUEST['ppc-time-range'] ); ?>" title="<?php _e( 'Back to general' , 'post-pay-counter'); ?>"><?php _e( 'Back to general' , 'post-pay-counter'); ?></a>

			<?php }
		}

        do_action( 'ppc_stats_header_links', $page_permalink ); ?>

			</span>
			<input type="submit" class="button-secondary" name="post_pay_counter_submit" value="<?php echo __( 'Update view' , 'post-pay-counter'); ?>" />
			<br />
			<a href="<?php echo $page_permalink; ?>" title="<?php _e( 'Get current view permalink' , 'post-pay-counter'); ?>"><?php _e( 'Get current view permalink' , 'post-pay-counter'); ?></a>
		</div>

	</div>
</form>
<div class="clear"></div>
<hr class="ppc_hr_divider" />

		<?php
    }

    /**
     * Shows HTML stats.
     *
     * @access  public
     * @since   2.0
     * @param   $formatted_stats array formatted stats
     * @param   $raw_stats array ordered-by-author stats
     * @param   $author array optional whether detailed stats
     */
    static function get_html_stats( $formatted_stats, $raw_stats, $author = NULL ) {
        ?>

<table class="widefat fixed" id="ppc_stats_table">
	<thead>
		<tr>

		<?php
        foreach( $formatted_stats['cols'] as $col_id => $value ) { //cols work the same both for general and user
            ?>

			<th scope="col"><?php echo $value; ?></th>

			<?php
			if( is_array( $author ) )
				do_action( 'ppc_general_stats_html_cols_after_'.$col_id );
			else
				do_action( 'ppc_author_stats_html_cols_after_default'.$col_id );
        }

        if( is_array( $author ) )
            do_action( 'ppc_general_stats_html_cols_after_default' );
        else
            do_action( 'ppc_author_stats_html_cols_after_default' );
		?>

		</tr>
	</thead>

	<tfoot>
        <tr>

		<?php
        foreach( $formatted_stats['cols'] as $col_id => $value ) {
            ?>

			<th scope="col"><?php echo $value; ?></th>

			<?php
			if( is_array( $author ) )
				do_action( 'ppc_general_stats_html_cols_after_'.$col_id );
			else
				do_action( 'ppc_author_stats_html_cols_after_default'.$col_id );
        }

        if( is_array( $author ) )
            do_action( 'ppc_author_stats_html_cols_after_default' );
        else
            do_action( 'ppc_general_stats_html_cols_after_default' );
        ?>

        </tr>
	</tfoot>

	<tbody>

		<?php
		echo self::get_html_stats_tbody( $formatted_stats, $raw_stats, $author, "html", true, "echo" );
        ?>

	</tbody>
</table>

		<?php
    }

    /**
     * Builds the stats table body for html display.
     *
     * @since	2.503
     * @access	public
     * @param 	array $formatted_stats
     * @param 	array $raw_stats
     * @param 	string (optional) $author id
     * @param	string (optional) $filter_name filter name
     * @param	bool (optional) $format_payment whether to format payment (eg. add currency symbol)
     * @param	string (optional) $echo_or_return what to do with the output, if echo or return. If echoed, actions are fired as well
     * @return 	string html stats
     */
    static function get_html_stats_tbody( $formatted_stats, $raw_stats, $author = NULL, $filter_name = "html", $format_payment = true, $echo_or_return = "return" ) {
		global $current_user, $ppc_global_settings;
		$perm = new PPC_permissions();
		$html = "";

		if( is_array( $author ) ) {
			foreach( $formatted_stats['stats'] as $author => $author_stats ) {

				$user_settings = PPC_general_functions::get_settings( $author, true );
				$counting_types = $ppc_global_settings['counting_types_object']->get_all_counting_types( 'post' );

				//Handle sorting
				if( isset( $_REQUEST['orderby'] ) AND isset( $_REQUEST['order'] )
				AND ( $_REQUEST['order'] == 'desc' OR $_REQUEST['order'] == 'asc' )
				AND ! ( $_REQUEST['orderby'] == 'post_publication_date' AND $_REQUEST['order'] == 'desc' ) ) { //don't sort if post_publication_date desc, it's already sorted
					uasort( $author_stats, 'ppc_uasort_stats_sort' );
				}

				foreach( $author_stats as $post_id => $post_stats ) {
					$post = $raw_stats[$author][$post_id];

					$tr_opacity = '';
					if( $user_settings['counting_payment_only_when_total_threshold'] ) {
						if( $post->ppc_misc['exceed_threshold'] == false )
							$tr_opacity = ' style="opacity: 0.40;"';
					}

					$html .= '<tr'.$tr_opacity.'>';

					foreach( $post_stats as $field_name => $field_value ) {
						$maybe_skip = apply_filters( 'ppc_author_stats_'.$filter_name.'_skip_field', false, $field_name );
						if( $maybe_skip ) continue;

						$field_value = apply_filters( 'ppc_author_stats_'.$filter_name.'_each_field_value', $field_value, $field_name, $post );

						switch( $field_name ) {
							//Attach link to post title: if user can edit posts, attach edit link (faster), if not post permalink (slower)
							case 'post_title':

								if( $user_settings['stats_display_edit_post_link'] ) {
									$post_link = get_edit_post_link( $post->ID );
									if( $post_link == '' )
										$post_link = get_permalink( $post->ID );

									$field_value = '<a href="'.$post_link.'" title="'.esc_html( $post->post_title ).'">'.esc_html( $field_value ).'</a>';
								}

								break;

							case 'post_total_payment':
								$tooltip = PPC_counting_stuff::build_payment_details_tooltip( $post->ppc_count['normal_count'], $post->ppc_payment['normal_payment'], $counting_types );
								if( $format_payment )
									$field_value = '<abbr title="'.$tooltip.'" class="ppc_payment_column">'.PPC_general_functions::format_payment( $field_value ).'</abbr>';
								else
									$field_value = '<abbr title="'.$tooltip.'" class="ppc_payment_column">'.$field_value.'</abbr>';
								break;

							case 'post_words':
							case 'post_visits':
							case 'post_images':
							case 'post_comments':
								$count_field_value = substr( $field_name, 5, strlen( $field_name ) );
								if( $post->ppc_count['normal_count'][$count_field_value]['real'] != $post->ppc_count['normal_count'][$count_field_value]['to_count'] )
									$field_value = '<abbr title="'.sprintf( __( 'Total is %1$s. %2$s Displayed is what you\'ll be paid for.', 'post-pay-counter' ), $post->ppc_count['normal_count'][$count_field_value]['real'], '&#13;' ).'" class="ppc_count_column">'.$field_value.'</abbr>';

								break;

							//Terrible hack to localize at least some post statuses
							case 'post_status':
								if( $field_value == 'publish' )
									$field_value = __( 'Publish', 'post-pay-counter' );
								else if( $field_value == 'pending' )
									$field_value = __( 'Pending', 'post-pay-counter' );
								else if( $field_value == 'future' )
									$field_value = __( 'Future', 'post-pay-counter' );

								break;
						}

						$html .= '<td class="'.$field_name.'">'.$field_value.'</td>';
						$html = apply_filters( 'ppc_author_stats_'.$filter_name.'_after_'.$field_name, $html, $author, $formatted_stats, $post );
					}

					$html = apply_filters( 'ppc_author_stats_'.$filter_name.'_after_each_default_filter', $html, $author, $formatted_stats, $post );

					//Bit entangled due to retro-compatibility with PRO versions <= 1.5.8.3, when this function echoed directly (thus using actions and not filters)
					if( $echo_or_return == "echo" ) {
						echo $html;
						$html ="";
						do_action( 'ppc_author_stats_'.$filter_name.'_after_each_default', $author, $formatted_stats, $post );
					}

					$html .= '</tr>';
				}
			}

		} else {

			//Handle sorting
			if( isset( $_REQUEST['orderby'] ) AND isset( $_REQUEST['order'] ) AND ( $_REQUEST['order'] == 'desc' OR $_REQUEST['order'] == 'asc' ) ) {
				uasort( $formatted_stats['stats'], 'ppc_uasort_stats_sort' );
			}

			foreach( $formatted_stats['stats'] as $author => $author_stats ) {
				$html .= '<tr>';

				foreach( $formatted_stats['cols'] as $field_name => $label ) {
					$maybe_skip = apply_filters( 'ppc_general_stats_'.$filter_name.'_skip_field', false, $field_name );
					if( $maybe_skip ) continue;

					if( isset( $author_stats[$field_name] ) ) {
						$field_value = $author_stats[$field_name];
						$field_value = apply_filters( 'ppc_general_stats_'.$filter_name.'_each_field_value', $field_value, $field_name, $raw_stats[$author], $author );

						//Cases in which other stuff needs to be added to the output
						switch( $field_name ) {
							case 'author_name':
								if( ( $perm->can_see_others_detailed_stats() OR $author == $current_user->ID ) AND $filter_name == "html" )
									$field_value = '<a href="'.PPC_general_functions::get_the_author_link( $author ).'" title="'.__( 'Go to detailed view' , 'post-pay-counter').'">'.esc_html( $field_value ).'</a>';
								break;

							case 'author_total_payment':
								//Avoid tooltip non-isset notice
								if( isset( $raw_stats[$author]['total']['ppc_misc']['tooltip_normal_payment'] ) )
									$tooltip = $raw_stats[$author]['total']['ppc_misc']['tooltip_normal_payment'];
								else
									$tooltip = '';

								if( $format_payment )
									$field_value = '<abbr title="'.$tooltip.'" class="ppc_payment_column">'.PPC_general_functions::format_payment( $field_value ).'</abbr>';
								else
									$field_value = '<abbr title="'.$tooltip.'" class="ppc_payment_column">'.$field_value.'</abbr>';
								break;

							case 'author_words':
							case 'author_visits':
							case 'author_images':
							case 'author_comments':
								$count_field_name = substr($field_name, 7, strlen($field_name));
								if($raw_stats[$author]['total']['ppc_count']['normal_count'][$count_field_name]['real'] != $raw_stats[$author]['total']['ppc_count']['normal_count'][$count_field_name]['to_count'] )
									$field_value = '<abbr title="Total is '.$raw_stats[$author]['total']['ppc_count']['normal_count'][$count_field_name]['real'].'&#13;'.__( 'Displayed is what you\'ll be paid for.', 'post-pay-counter' ).'" class="ppc_count_column">'.$field_value.'</abbr>';
								break;
						}

						$html .= '<td class="'.$field_name.'">'.$field_value.'</td>';
						$html = apply_filters( 'ppc_general_stats_'.$filter_name.'_after_'.$field_name, $html, $author, $formatted_stats, $raw_stats );

					} else {
						$html .= '<td class="'.$field_name.'">'.apply_filters( 'ppc_general_stats_each_field_empty_value', 'N.A.', $field_name, $raw_stats[$author], $author ).'</td>';
					}
				}

				$html = apply_filters( 'ppc_general_stats_'.$filter_name.'_after_each_default_filter', $html, $author, $formatted_stats, $raw_stats );

				//Bit entangled due to retro-compatibility with PRO versions <= 1.5.8.3, when this function echoed directly (thus using actions and not filters)
				if( $echo_or_return == "echo" ) {
					echo $html;
					$html = "";
					do_action( 'ppc_general_stats_'.$filter_name.'_after_each_default', $author, $formatted_stats, $raw_stats );
				}

				$html .= '</tr>';
			}

		}

		return $html;
    }

    /**
     * Shows HTML overall stats.
     *
     * @access  public
     * @since   2.0
     * @param   $overall_stats array overall stats
     */
    static function print_overall_stats( $overall_stats ) {
        global $ppc_global_settings;

		$general_settings = PPC_general_functions::get_settings( 'general' );
        $counting_types = array_merge( $ppc_global_settings['counting_types_object']->get_all_counting_types( 'author' ), $ppc_global_settings['counting_types_object']->get_all_counting_types( 'post' ) );
        ?>

<table class="widefat fixed">
	<tr>
		<td width="40%"><?php _e( 'Total displayed posts:', 'post-pay-counter' ); ?></td>
		<td align="left" width="10%"><?php echo $overall_stats['posts']; ?></td>
		<td width="35%"><?php _e( 'Total displayed payment:', 'post-pay-counter' ); ?></td>
		<td align="left" width="15%"><?php echo PPC_general_functions::format_payment( sprintf( '%.2f', $overall_stats['total_payment'] ) ); ?></td>
	</tr>

		<?php
		do_action( 'ppc_html_overall_stats', $overall_stats );
		?>

	<tr><td colspan="4"></td></tr>
	<tr><td colspan="4" style="text-align: center; font-size: smaller;"><strong><?php echo __( 'Totals', 'post-pay-counter' ); ?></strong></td></tr>

		<?php
		$n = 0;
		foreach( $overall_stats['payment'] as $id => $data ) {
			if( $n % 2 == 0 )
				echo '<tr>';

			if( isset( $counting_types[$id] ) ) {

				if( isset( $counting_types[$id]['display_status_index'] ) AND isset( $general_settings[$counting_types[$id]['display_status_index']] ) )
					$display = $general_settings[$counting_types[$id]['display_status_index']];
				else
					$display = $counting_types[$id]['display'];

				switch( $display ) {
					case 'both':
						$disp = $overall_stats['count'][$id].' ('.PPC_general_functions::format_payment( $overall_stats['payment'][$id] ).')';
						break;

					case 'count':
						$disp = $overall_stats['count'][$id];
						break;

					case 'payment':
					case 'none':
					case 'tooltip':
						$disp = PPC_general_functions::format_payment( $overall_stats['payment'][$id] );
						break;
				}
			}
		?>

		<td width="40%"><?php echo ucfirst( sprintf( '%s:', $counting_types[$id]['label'] ) ); ?></td>
		<td align="left" width="10%"><?php echo $disp; ?></td>

		<?php
			if( $n % 2 == 1 )
				echo '</tr>';

			++$n;
		}

		do_action( 'ppc_html_overall_stats_counts', $overall_stats );
		?>

</table>

	<?php
    }

    /**
     * Prints settings fields enclosing them in a <p>: a checkbox/radio in a floated-left span, the tooltip info on the right and the description in the middle.
     *
     * @access  public
     * @since   2.0
     * @param   $text string the field description
     * @param   $setting string the current setting value
     * @param   $field string the input type (checkbox or radio)
     * @param   $name string the field name
     * @param   $tooltip_description string optional the tooltip description
     * @param   $value string optional the field value (for radio)
     * @param   $id string optional the field id
     * @return  string the html
     */
    static function echo_p_field( $text, $setting, $field, $name, $tooltip_description = NULL, $value = NULL, $id = NULL, $disabled = false ) {
	   global $ppc_global_settings;

        $html = '<p style="height: 18px;">';

		if( is_string( $tooltip_description ) ) {
			$html .= '<span class="ppc_tooltip">';
			$html .= '<img src="'.$ppc_global_settings['folder_path'].'style/images/info.png'.'" title="'.$tooltip_description.'" class="ppc_tooltip_container" />';
			$html .= '</span>';
		}

        $html .= '<label>';
        $html .= '<span class="checkable_input">';

        if( $field == 'radio' )
            $html .= PPC_options_fields::generate_radio_field( $setting, $name, $value, $id, $disabled );
        else if( $field == 'checkbox' )
            $html .= PPC_options_fields::generate_checkbox_field( $setting, $name, $value, $id, $disabled );

        $html .= '</span>';
        $html .= $text;
        $html .= '</label>';
        $html .= '</p>';

        return apply_filters( 'settings_field_generation', $html );
    }

    /**
     * Prints settings fields enclosing them in a <p>: a checkbox/radio in a floated-left span, the tooltip info on the right and the description in the middle.
     *
     * @access  public
     * @since   2.0
     * @param   $field_name string the field name
     * @param   $field_value string the field value
     * @param   $label_text string the label text
     * @param   $size int optional the text field size
     * @return  string the html
     */
    static function echo_text_field( $field_name, $field_value, $label_text, $size = 15, $placeholder = '' ) {
        if( ! empty( $placeholder ) )
			$placeholder = ' placeholder="'.$placeholder.'"';

		$html = '<p>';
		$html .= '<input type="text" name="'.$field_name.'" id="'.$field_name.'" size="'.$size.'" value="'.$field_value.'" class="ppc_align_right"'.$placeholder.' />';
        $html .= '<label for="'.$field_name.'">'.$label_text.'</label>';
        $html .= '</p>';

        return apply_filters( 'text_field_generation', $html );
    }
}
