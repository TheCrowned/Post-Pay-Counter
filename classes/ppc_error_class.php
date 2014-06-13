<?php

/**
 * Error handler
 *
 * Used to produce and maybe store errors. Relies on WP_Error object.
 *
 * @package     PPCP
 * @copyright   2014
 * @author 		Stefano Ottolenghi
 */

class PPC_Error {
    
    private $wp_error;
    
    /**
     * Handles an error.
     * 
     * @since   2.21
     * @access  public
     * 
     * @param   $code string Error code
     * @param   $message string Error message
     * @param   $data mixed (optional) Error data
     * @param   $log bool (optional) Whether error should be logged
    */
    
    function __construct( $code, $message, $data = array(), $log = true ) {
        global $ppc_global_settings;
        
        $error_details = array(
            'code' => $code,
            'message' => $message,
            'data' => $data,
            'time' => time()
        );
        
        //If debug or logging enabled, make up detailed error (only shown if requested)
        if( PPC_DEBUG_SHOW OR ( PPC_DEBUG_LOG AND $log ) ) {
            $error_details['debug_message'] = 'An error was thrown with code "'.$code.'", message "'.$message.'" and debug data "'.var_export( $data, true ).'".';
        }
        
        if( PPC_DEBUG_SHOW ) {
            $error_details['output'] = $error_details['debug_message'];
        } else {
            $error_details['output'] = $error_details['message'];
        }
        
        //If logging enabled, push error with others
        if( PPC_DEBUG_LOG AND $log ) {
            $errors_already = get_option( $ppc_global_settings['option_errors'], array() );
            $errors = $errors_already;
            $errors[] = $error_details;
            
            if( ! $errors_already ) {
                if( add_option( $ppc_global_settings['option_errors'], $errors, '', 'no' ) ) {
                    $this->wp_error = new WP_Error( 'ppc_update_error', 'Could not update errors option.', 'ppc' );
                }
            } else {
                if( update_option( $ppc_global_settings['option_errors'], $errors ) ) {
                    $this->wp_error = new WP_Error( 'ppc_update_error', 'Could not update errors option.', 'ppc' );
                }
            }
        }
        
        $this->wp_error = new WP_Error( $error_details['code'], $error_details['output'] );
    }
    
    /**
     * Returns the error stored in the class var.
     * 
     * @since   2.21
     * @access  public
     * 
     * @return  object WP_Error with current error details
    */
    
    function return_error() {
        return $this->wp_error;
    }
    
}