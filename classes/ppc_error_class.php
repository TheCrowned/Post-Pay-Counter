<?php

/**
 * Error handler
 *
 * Used to produce and maybe store errors. Relies on WP_Error object.
 *
 * @package     PPC
 * @copyright   2014
 * @author 		Stefano Ottolenghi
 */

//Time, in days, after which errors should be deleted
define( 'PPC_ERROR_PURGE_TIME', 30 );

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
     * @param   $data array (optional) Error data
     * @param   $log bool (optional) Whether error should be logged
    */
    function __construct( $code, $message, $data = array(), $log = true ) {
        global $ppc_global_settings;

        $error_details = array(
            'code' => $code,
            'message' => $message,
            'data' => $data,
            'time' => current_time( 'timestamp' )
        );

        //If debug or logging enabled, make up detailed error (only shown if requested)
        if( PPC_DEBUG_SHOW OR ( PPC_DEBUG_LOG AND $log ) )
            $error_details['debug_message'] = 'An error was thrown with code "'.$code.'", message "'.$message.'" and debug data "'.var_export( $data, true ).'".';

        if( PPC_DEBUG_SHOW )
            $error_details['output'] = $error_details['debug_message'];
        else
            $error_details['output'] = $error_details['message'];

        //If logging enabled, push error with others
        if( PPC_DEBUG_LOG AND $log ) {
            $errors = @file_get_contents( $ppc_global_settings['file_errors'] );

            if( $errors !== false )
				$errors = unserialize( $errors );
			else
				$errors = array();

            $errors[] = $error_details;

			//Get rid of old errors - only run once a day, ensure this through an option
			$daily_delete = get_option( $ppc_global_settings['option_error_deletion'], true );
			if( $daily_delete != false AND $daily_delete < current_time( 'timestamp' ) - 86400 ) {
				foreach( $errors as $key => $single ) {
					if( $single['time'] < ( current_time( 'timestamp' ) - PPC_ERROR_PURGE_TIME*24*60*60 ) )
						unset( $errors[$key] );
				}

				//See the record is not bigger than ~10MB
				if( strlen( serialize( $errors ) ) > 10000 )
					$errors = array( $error_details ); //only save latest error

				update_option( $ppc_global_settings['option_error_deletion'], current_time( 'timestamp' ) );
			}

			if( file_put_contents( $ppc_global_settings['file_errors'], serialize( $errors ) ) )
				$this->wp_error = new WP_Error( 'ppc_update_error_update', 'Could not update errors option.', 'post-pay-counter' );
        }

        $this->wp_error = new WP_Error( $error_details['code'], $error_details['output'], $data );
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

	/**
     * Retrieves an error from the error log, if found.
     * Several searching criteria available.
     *
     * @since   2.604
     * @access  public
     *
     * @param	$args array
     * @return  array|bool the error details, or bool false if not found
     */
    static function get_error( $args ) {
		global $ppc_global_settings;

		if( isset( $args['error_code'] ) AND ! empty( $args['error_code'] ) ) {
			$errors = file_get_contents( $ppc_global_settings['file_errors'] );

			if( $errors !== false )
				$errors = unserialize( $errors );
			else
				return false;

			$key = array_search( $args['error_code'], array_column( $errors, 'code' ) );

			if( is_int( $key ) )
				return $errors[$key];
		}

		return false;

	}
}
