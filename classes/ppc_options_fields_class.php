<?php

/**
 * @author Stefano Ottolenghi
 * @copyright 2013
 */

class PPC_options_fields {
    
    /**
     * Generates a maybe checked radio field
     *
     * @access  public
     * @since   2.0
     * @param   $setting int current setting value (either 0 or 1)
     * @param   $name string field name
     * @param   $value string field value
     * @param   $id string field id
	 * @param 	$disabled bool whether field should be disabled
     * @return  string the html of the radio field
     */
    
    static function generate_radio_field( $setting, $name, $value, $id, $disabled ) {
		return '<input type="radio" name="'.$name.'" value="'.$value.'" id="'.$id.'" '.checked( 1, $setting, false ).disabled( true, $disabled, false ).'/>';
    }
    
    /**
     * Generates a maybe checked checkbox field
     *
     * @access  public
     * @since   2.0
     * @param   $setting int current setting value (either 0 or 1)
     * @param   $name string field name
     * @param   $id string field id
	 * @param 	$disabled bool whether field should be disabled
     * @return  string the html of the checkbox field
     */
            
    static function generate_checkbox_field( $setting, $name, $value, $id, $disabled ) {
		return '<input type="checkbox" name="'.$name.'" value="'.$value.'" id="'.$id.'" '.checked( 1, $setting, false ).disabled( true, $disabled, false ).'/>';
    }
    
    /**
     * Generates payment systems fields: zonal (2 to 10 zones) and incremental
     *
     * @access  public
     * @since   2.0
     * @param   $counting_type string what counting are we talking about (words, visits, images, comments)
     * @param   $settings array current settings
     * @return  string the html of the payment systems fields
     */
    
    static function echo_payment_systems( $counting_type, $counting_type_localized, $settings ) {
    	global $ppc_global_settings; 
                    
        $html = '<div class="payment_systems">';
        $html .= PPC_HTML_functions::echo_p_field( __( 'Use the zonal system' , 'post-pay-counter'), $settings['counting_'.$counting_type.'_system_zonal'], 'radio', 'counting_'.$counting_type.'_system', sprintf( __( 'With this system you can define up to how many zones of retribution you would like, so that from X %1$s to Y %1$s the same pay will be applied (eg. from 200 %1$s to 300 %1$s pay 2.00). It does not matter how many %1$s a post has, but only in what zone it fits in.' , 'post-pay-counter'), $counting_type_localized ), 'counting_'.$counting_type.'_system_zonal', 'counting_'.$counting_type.'_system_zonal' );
        $html .= '<div id="counting_'.$counting_type.'_system_zonal_content" class="field_value">';
        $html .= '<table style="border: none;">';
        $html .= '<thead>';
        $html .= '<tr>';
        $html .= '<th width="50%" align="left">'.ucfirst( $counting_type_localized ).'</th>';
        $html .= '<th width="50%" align="left">'.__( 'Payment' , 'post-pay-counter').'</th>';
        $html .= '</tr>';
        $html .= '</thead>';
        $html .= '<tbody>';
              
        $n = 0;
        $zones_count = count( $settings['counting_'.$counting_type.'_system_zonal_value'] );
        while( $n < $zones_count ) {
            $html .= '<tr>';
            $html .= '<td><input type="text" name="'.$counting_type.'_'.$n.'_zone_threshold" id="'.$counting_type.'_'.$n.'_zone_threshold" value="'.$settings['counting_'.$counting_type.'_system_zonal_value'][$n]['threshold'].'" /></td>';
            $html .= '<td><input type="text" name="'.$counting_type.'_'.$n.'_zone_payment" id="'.$counting_type.'_'.$n.'_zone_payment" value="'.sprintf( '%.2f', $settings['counting_'.$counting_type.'_system_zonal_value'][$n]['payment'] ).'" /></td>';
            $html .= '</tr>';
            ++$n;
        }
                        
        $html .= '</tbody>';
    	$html .= '</table>';
        $html .= '<div style="margin-left: 110px;">';
        $html .= '<button id="counting_'.$counting_type.'_more_zones"><img src="'.$ppc_global_settings['folder_path'].'style/images/plus.png'.'" title="'.__( 'Add one more zone' , 'post-pay-counter').'" alt="'.__( 'Add one more zone' , 'post-pay-counter').'" width="10" /></button>';
        $html .= '<button id="counting_'.$counting_type.'_less_zones"><img src="'.$ppc_global_settings['folder_path'].'style/images/minus.png'.'" title="'.__( 'Delete last zone' , 'post-pay-counter').'" alt="'.__( 'Delete last zone' , 'post-pay-counter').'" width="10" /></button>';
        $html .= '</div>';
        $html .= '</div>';
                
        $html .= PPC_HTML_functions::echo_p_field( __( 'Use the incremental payment system' , 'post-pay-counter'), $settings['counting_'.$counting_type.'_system_incremental'], 'radio', 'counting_'.$counting_type.'_system', sprintf( __( 'With this system, every %1$s has a value: more %1$s => higher pay. Just think that the %1$s number will be multiplied for the incremental payment value, so that is a post has 300 %1$s and you set the incremental payment value to 0.01, the writer will be credited with 3.' , 'post-pay-counter'), $counting_type_localized ), 'counting_'.$counting_type.'_system_incremental', 'counting_'.$counting_type.'_system_incremental' );
        $html .= '<div id="counting_'.$counting_type.'_system_incremental_content" class="field_value">';
        $html .= PPC_HTML_functions::echo_text_field( 'counting_'.$counting_type.'_system_incremental_value', $settings['counting_'.$counting_type.'_system_incremental_value'], __( 'Incremental payment value' , 'post-pay-counter'), 15 );
        $html .= '</div>';
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Generates counting type display options select menu,
     *
     * @access  public
     * @since   2.514
     * @param   $counting_type string what counting are we talking about (words, visits, images, comments, ...)
     * @param   $current_value string current setting for given counting type
     * @return  string the html of the select menu
     */
    
    static function echo_counting_type_display_dropdown( $counting_type, $current_value ) {
		global $ppc_global_settings;
		
		$html = '<p style="height: 18px;">';
		$html .= '<span class="ppc_tooltip">';
		$html .= '<img src="'.$ppc_global_settings['folder_path'].'style/images/info.png'.'" title="'.sprintf( __( 'This controls what is displayed in the stats page for this payment criteria. %1$s will only display the counting value (es. 50 words), %2$s only the payment value (es. $0.50), %3$s will display them both side to side (es. $0.50 (50)), %4$s will hide the whole column in stats and only display data in the tooltip of the payment, %5$s will hide everything of the payment altogether, preserving the payment value just as part of the total payment value.', 'post-pay-counter' ), '\''.__( 'Count', 'post-pay-counter' ).'\'', '\''.__( 'Payment', 'post-pay-counter' ).'\'', '\''.__( 'Both', 'post-pay-counter' ).'\'', '\''.__( 'Tooltip', 'post-pay-counter' ).'\'', '\''.__( 'None', 'post-pay-counter' ).'\'' ).'" class="ppc_tooltip_container" />';
		$html .= '</span>';
		$html .= __( 'Payment display status', 'post-pay-counter' );
		$html .= '<select name="'.$counting_type.'_display_status">';
		$html .= '<option value="both" '.selected( 'both', $current_value, false ).'>'.__( 'Both', 'post-pay-counter' ).'</option>';
		$html .= '<option value="count" '.selected( 'count', $current_value, false ).'>'.__( 'Count', 'post-pay-counter' ).'</option>';
		$html .= '<option value="payment" '.selected( 'payment', $current_value, false ).'>'.__( 'Payment', 'post-pay-counter' ).'</option>';
		$html .= '<option value="tooltip" '.selected( 'tooltip', $current_value, false ).'>'.__( 'Tooltip', 'post-pay-counter' ).'</option>';
		$html .= '<option value="none" '.selected( 'none', $current_value, false ).'>'.__( 'None', 'post-pay-counter' ).'</option>';
		$html .= '</select>';
		
		return $html;
	}
    
    /**
     * Checks whether the given value is set or not. Sets $checkbox to NULL so we'll later know what vars are still to be dealt with
     *
     * @access  public
     * @since   2.0
     * @param   $checkbox int checkbox value
     * @return  bool checkbox status
     */
    
    static function get_checkbox_value( &$checkbox ) {
        if( ! isset( $checkbox ) )
            return 0;
        else
            return 1;
		
		$checkbox = NULL;
    }
    
    /**
     * Gets a radio-set value. All three possibilities are set to zero in an array. Switch through the $radio and check which one was selected. The selected option has its value turned to 1 in the return array, while others are still 0. Set $radio to NULL so we'll later know what vars are still to be dealt with.
     *
     * @access  public
     * @since   2.0
     * @param   $radio string the value of the checked radio
     * @param   $opt_1 string the value of the first option
     * @param   $opt_2 string the value of the second option
     * @param   $opt_3 string optional the value of the third option
     * @param	$options_array array optional allows to handle N options
     * @return  array the 2/3 possibilities along with their set values
     */
    
    static function get_radio_value( &$radio, $opt_1, $opt_2, $opt_3 = FALSE, $options_array = FALSE ) {

		//New method: handles more than 3 options
		if( $options_array !== false ) {
			$return = array();
			foreach( $options_array as $single ) {
				if( $radio == $single )
					$return[$single] = 1;
				else
					$return[$single] = 0;
			}

			$radio = null;
			return $return;
		}

		//OLD STUFF for backwards compatibility
        $return = array(
            $opt_1 => 0,
            $opt_2 => 0,
        );
        
        if( $opt_3 )
            $return[$opt_3] = 0;
        
        switch( $radio ) {
            case $opt_1:
                $return[$opt_1] = 1;
                break;
                
            case $opt_2:
                $return[$opt_2] = 1;
                break;
                
            case $opt_3:
                $return[$opt_3] = 1;
                break;
        }
        
        $radio = null;
        return $return;
    }
}
