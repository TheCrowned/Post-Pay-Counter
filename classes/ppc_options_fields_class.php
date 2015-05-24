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
        $disabled_html = '';
		if( $disabled ) {
			$disabled_html = ' disabled="disabled"';
		}
		
		$checked_html = '';
		if( $setting ) {
            $checked_html = ' checked="checked"';
        }
		
		return '<input type="radio" name="'.$name.'" value="'.$value.'" id="'.$id.'" '.$checked_html.$disabled_html.'/>';
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
        $disabled_html = '';
		if( $disabled ) {
			$disabled_html = ' disabled="disabled"';
		}
		
		$checked_html = '';
		if( $setting ) {
            $checked_html = ' checked="checked"';
        }
		
		return '<input type="checkbox" name="'.$name.'" value="'.$value.'" id="'.$id.'" '.$checked_html.$disabled_html.'/>';
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
        $html .= PPC_HTML_functions::echo_p_field( __( 'Use the zonal system' , 'ppc'), $settings['counting_'.$counting_type.'_system_zonal'], 'radio', 'counting_'.$counting_type.'_system', sprintf( __( 'With this system you can define up to how many zones of retribution you would like, so that from X %1$s to Y %1$s the same pay will be applied (eg. from 200 %1$s to 300 %1$s pay 2.00). It does not matter how many %1$s a post has, but only in what zone it fits in.' , 'ppc'), $counting_type_localized ), 'counting_'.$counting_type.'_system_zonal', 'counting_'.$counting_type.'_system_zonal' );
        $html .= '<div id="counting_'.$counting_type.'_system_zonal_content" class="field_value">';
        $html .= '<table style="border: none;">';
        $html .= '<thead>';
        $html .= '<tr>';
        $html .= '<th width="50%" align="left">'.ucfirst( $counting_type_localized ).'</th>';
        $html .= '<th width="50%" align="left">'.__( 'Payment' , 'ppc').'</th>';
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
        $html .= '<button id="counting_'.$counting_type.'_more_zones"><img src="'.$ppc_global_settings['folder_path'].'style/images/plus.png'.'" title="'.__( 'Add one more zone' , 'ppc').'" alt="'.__( 'Add one more zone' , 'ppc').'" width="10" /></button>';
        $html .= '<button id="counting_'.$counting_type.'_less_zones"><img src="'.$ppc_global_settings['folder_path'].'style/images/minus.png'.'" title="'.__( 'Delete last zone' , 'ppc').'" alt="'.__( 'Delete last zone' , 'ppc').'" width="10" /></button>';
        $html .= '</div>';
        $html .= '</div>';
                
        $html .= PPC_HTML_functions::echo_p_field( __( 'Use the incremental payment system' , 'ppc'), $settings['counting_'.$counting_type.'_system_incremental'], 'radio', 'counting_'.$counting_type.'_system', sprintf( __( 'With this system, every %1$s has a value: more %1$s => higher pay. Just think that the %1$s number will be multiplied for the incremental payment value, so that is a post has 300 %1$s and you set the incremental payment value to 0.01, the writer will be credited with 3.' , 'ppc'), $counting_type_localized ), 'counting_'.$counting_type.'_system_incremental', 'counting_'.$counting_type.'_system_incremental' );
        $html .= '<div id="counting_'.$counting_type.'_system_incremental_content" class="field_value">';
        $html .= PPC_HTML_functions::echo_text_field( 'counting_'.$counting_type.'_system_incremental_value', $settings['counting_'.$counting_type.'_system_incremental_value'], __( 'Incremental payment value' , 'ppc'), 15 );
        $html .= '</div>';
        $html .= '</div>';
        
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
     * @return  array the 2/3 possibilities along with their set values
    */
    
    static function get_radio_value( &$radio, $opt_1, $opt_2, $opt_3 = FALSE ) {
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
?>