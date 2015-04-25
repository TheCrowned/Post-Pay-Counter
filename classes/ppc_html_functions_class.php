<?php

/**
 * @author Stefano Ottolenghi
 * @copyright 2013
 */

require_once( 'ppc_permissions_class.php' );

class PPC_HTML_functions {
    
	/**
	 * Displays header logo and caption
	 *
	 * @access	public
	 * @since	2.36
	 */
	
	static function display_header_logo() {
		global $ppc_global_settings;
		?>
		
		<div id="ppc_logo">
			<img src="<?php echo $ppc_global_settings['folder_path'].'style/images/pengu-ins.png'; ?>" />
			<div id="ppc_logo_caption"><?php printf( __( 'A %1$spengu-ins%2$s production', 'ppc' ), '<a href="http://www.thecrowned.org/pengu-ins?utm_source=users_site&utm_medium=header_logo&utm_campaign=pengu-ins" title="Pengu-ins" target="_blank">', '</a>' ); ?></div>
		</div>
		
		<?php
	}
	
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
		?>
		
<form action="" method="post">
	<div id="ppc_stats_header">
		<div id="ppc_stats_header_datepicker">
			<h3>
        <?php echo sprintf( __( 'Showing stats from %1$s to %2$s' , 'ppc'), '<input type="text" name="tstart" id="post_pay_counter_time_start" class="mydatepicker" value="'.date( 'Y-m-d', $ppc_global_settings['stats_tstart'] ).'" accesskey="'.$ppc_global_settings['stats_tstart'].'" size="8" />', '<input type="text" name="tend" id="post_pay_counter_time_end" class="mydatepicker" value="'.date( 'Y-m-d', $ppc_global_settings['stats_tend'] ).'" accesskey="'.$ppc_global_settings['stats_tend'].'" size="8" />' ).' - "'.$current_page.'"'; 
		
		//Display filter by user role field in general stats
		if( $ppc_global_settings['current_page'] == 'stats_general' ) {
			echo ' - '.__( 'Filter by user role', 'ppc' ). ' ';
			echo '<select name="role" id="ppc_stats_role">';
			echo '<option value="ppc_any" />'.__( 'Any', 'ppc' ).'</option>';
			foreach( $wp_roles->role_names as $key => $value ) {
				$checked = '';
				
				if( isset( $ppc_global_settings['stats_role'] ) AND $key == $ppc_global_settings['stats_role'] )
					$checked = 'selected="selected"';
				
				echo '<option value="'.$key.'" '.$checked.' />'.$value.'</option>';
			}
			echo '</select>';
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
				<a href="<?php echo admin_url( $ppc_global_settings['stats_menu_link'].'&amp;tstart='.$ppc_global_settings['stats_tstart'].'&amp;tend='.$ppc_global_settings['stats_tend'] ); ?>" title="<?php _e( 'Back to general' , 'ppc'); ?>"><?php _e( 'Back to general' , 'ppc'); ?></a>
        
        <?php do_action( 'ppc_stats_header_links', $page_permalink ); ?>
        
			</span>
			<input type="submit" class="button-secondary" name="post_pay_counter_submit" value="<?php echo __( 'Update view' , 'ppc'); ?>" />
			<br />
			<a href="<?php echo $page_permalink; ?>" title="<?php _e( 'Get current view permalink' , 'ppc'); ?>"><?php _e( 'Get current view permalink' , 'ppc'); ?></a>
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
        global $current_user, $ppc_global_settings;
        $perm = new PPC_permissions();
        ?>
		
<table class="widefat fixed" id="ppc_stats_table">
	<thead>
		<tr>
		
		<?php
        foreach( $formatted_stats['cols'] as $col_id => $value ) { //cols work the same both for general and user
            ?>
			
			<th scope="col"><?php echo $value; ?></th>
			
			<?php
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
        if( is_array( $author ) ) {
            list( $author, $author_stats ) = each( $formatted_stats['stats'] );
            $user_settings = PPC_general_functions::get_settings( $author, true );
                
            foreach( $author_stats as $post_id => $post_stats ) {
                $post = $raw_stats[$author][$post_id];
                
                $tr_opacity = '';
                if( $user_settings['counting_payment_only_when_total_threshold'] ) {
                    if( $post->ppc_misc['exceed_threshold'] == false )
                        $tr_opacity = ' style="opacity: 0.40;"';
                }
                
                echo '<tr'.$tr_opacity.'>';
                
                foreach( $post_stats as $field_name => $field_value ) {
                    
					switch( $field_name ) {
                        //Attach link to post title: if user can edit posts, attach edit link (faster), if not post permalink (slower)
						case 'post_title':
							$post_link = get_edit_post_link( $post->ID );
							if( $post_link == '' )
								$post_link = get_permalink( $post->ID );
								
                            $field_value = '<a href="'.$post_link.'" title="'.$post->post_title.'">'.$field_value.'</a>';
                            break;
                        
                        case 'post_total_payment':
                            $tooltip = PPC_counting_stuff::build_payment_details_tooltip( $post->ppc_count['normal_count'], $post->ppc_payment['normal_payment'] );
                            $field_value = '<abbr title="'.$tooltip.'" class="ppc_payment_column">'.$field_value.'</abbr>';
                            break;
                    }
                    
                    echo '<td class="'.$field_name.'">'.apply_filters( 'ppc_author_stats_html_each_field_value', $field_value, $field_name, $post ).'</td>';
                }
                
                do_action( 'ppc_author_stats_html_after_each_default', $author, $formatted_stats, $post );
                
                echo '</tr>';
            }
            
        } else {
            
            foreach( $formatted_stats['stats'] as $author => $author_stats ) {
                echo '<tr>';
                
				foreach( $formatted_stats['cols'] as $field_name => $label ) {
					if( isset( $author_stats[$field_name] ) ) {
						$field_value = $author_stats[$field_name];
						
						//Cases in which other stuff needs to be added to the output
						switch( $field_name ) {
							case 'author_name':
								if( $perm->can_see_others_detailed_stats() OR $author == $current_user->ID )
									$field_value = '<a href="'.PPC_general_functions::get_the_author_link( $author ).'" title="'.__( 'Go to detailed view' , 'ppc').'">'.$field_value.'</a>';
								
								break;
							
							case 'author_total_payment':
								$field_value = '<abbr title="'.$raw_stats[$author]['total']['ppc_misc']['tooltip_normal_payment'].'" class="ppc_payment_column">'.$field_value.'</abbr>';
								break;
						}
						
						echo '<td class="'.$field_name.'">'.apply_filters( 'ppc_general_stats_html_each_field_value', $field_value, $field_name, $raw_stats[$author] ).'</td>';
					
					} else {
						echo '<td class="'.$field_name.'">'.apply_filters( 'ppc_general_stats_html_each_field_empty_value', 'N.A.', $field_name, $raw_stats[$author] ).'</td>';
					}
                }
                
                do_action( 'ppc_general_stats_html_after_each_default', $author, $formatted_stats, $raw_stats );
                
                echo '</tr>';
            }
        }
        ?>
		
	</tbody>
</table>
		
		<?php
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
        ?>
		
<table class="widefat fixed">
	<tr>
		<td width="40%"><?php _e( 'Total displayed posts:', 'ppc' ); ?></td>
		<td align="left" width="10%"><?php echo $overall_stats['posts']; ?></td>
		<td width="35%"><?php _e( 'Total displayed payment:', 'ppc' ); ?></td>
		<td align="left" width="15%"><?php echo PPC_general_functions::format_payment( sprintf( '%.2f', $overall_stats['payment'] ) ); ?></td>
	</tr>
	
		<?php
		do_action( 'ppc_html_overall_stats', $overall_stats );
		?>
	
	<tr><td colspan="4"></td></tr>
	<tr><td colspan="4" style="text-align: center; font-size: smaller;"><strong><?php echo strtoupper( __( 'counts', 'ppc' ) ); ?></strong></td></tr>
	
		<?php
		$n = 0;
		foreach( $overall_stats['count'] as $single => $data ) {
			if( $n % 2 == 0 )
				echo '<tr>';
				
		?>
		
		<td width="40%"><?php printf( __( 'Total %s count:', 'ppc' ), $single ); ?></td>
		<td align="left" width="10%"><?php echo $data ?></td>
	
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
		
        $html = '<p style="height: 11px;">';
        
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
        $html .= '<label for="'.$field_name.'">'.$label_text.'</label>';
        $html .= '<input type="text" name="'.$field_name.'" id="'.$field_name.'" size="'.$size.'" value="'.$field_value.'" class="ppc_align_right"'.$placeholder.' />';
        $html .= '</p>';
        
        return apply_filters( 'text_field_generation', $html );
    }
}
?>