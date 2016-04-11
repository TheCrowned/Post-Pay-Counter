<?php

/**
 * Licnse functions
 *
 * Handles all addons license requests.
 *
 * @package     PPC
 * @copyright   2014
 * @author 		Stefano Ottolenghi
 * @since       2.511
*/

class PPC_license {
    
	/**
     * Holds current local license activation status.
     */ 
	
	public $local_status;
	
    /**
     * Holds URL for remote license features (activation, deactivation, regular license check).
     */ 
	
	public $remote_URL;
    
    /**
     * Holds addon version.
     */ 
	
	public $addon_version;
    
    /**
     * Holds addon activation key option name.
     */ 
	
	public $activation_key_name;
	
    /**
     * Assigns class vars (see class vars comments for details).
     * 
     * @access  public
     * @since   2.511
     * 
     * @param   $check_local_status_function array
     * @param   $remote_URL string
     * @param   $addon_version string
     * @param   $activation_key string
     */
    
    public function __construct( $check_local_status_function, $remote_URL, $addon_version, $activation_key_name ) {
        $local_status = call_user_func( $check_local_status_function );
        if( ! is_bool( $local_status ) ) {
            $error = new PPC_Error( 'ppcp_not_bool_license_status', 'License status function must return a boolean value.', array(
                'check_local_status_function' => $check_local_status_function
            ) );
            return $error->return_error();
        }
        
        $this->local_status = $local_status;
        $this->remote_URL = $remote_URL;
        $this->addon_version = $addon_version;
        $this->activation_key_name = $activation_key_name;
    } 
    
    /**
     * Calls home for license stuff.
     *
     * @access  public
     * @since   2.511
	 * @param	$parameters array http request parameters
     * @return 	array request result details
     */
	 
    function license_request( $parameters ) {
        global $ppc_global_settings;
        
        //Send website url, language, current plugin version along
        $parameters['website'] = site_url();
		$parameters['language'] = get_bloginfo( 'language' );
        $parameters['PPC_version'] = $ppc_global_settings['current_version'];
        $parameters['addon_version'] = $this->addon_version;
        
        $parameters = apply_filters( 'ppcp_license_request_parameters', $parameters );
        
        $headers['Accept'] = 'text/html';
        
        $request = wp_remote_post( $this->remote_URL, array( 
            'timeout' => 10,
            'body' => $parameters,
            'headers' => $headers 
        ) );

        if( is_wp_error( $request ) ) {
            $error = new PPC_Error( 'ppcp_license_request_connection_error', sprintf( __( 'Error', 'ppcp').': '.$request->get_error_message().'. '.__( '%1$sWhy does this happen?%2$s', 'ppcp' ), '<a href="http://postpaycounter.com/license-activation-fails-with-timeout/">', '</a>'), array(
                'request_url' => $this->remote_URL,
                'parameters' => $parameters 
            ) );
            return $error->return_error();
            
        } else if( $request['response']['code'] != 200 ) {
            $error = new PPC_Error( 'ppcp_license_request_reponse_error', __( 'Error', 'ppcp').': '.$request['response']['code'].' - '.$request['response']['message'], array(
                'request_url' => $this->remote_URL,
                'parameters' => $parameters 
            ) );
            return $error->return_error();
            
        } else if( ! isset( $request['headers']['ppcp_activation_status'] ) ) {
            $error = new PPC_Error( 'ppcp_license_request_remote_error', __( 'Error: something went wrong on the remote server. This should be reported.', 'ppcp' ), array(
                'request_url' => $this->remote_URL,
                'parameters' => $parameters 
            ) );
            return $error->return_error();
            
        } else if( $request['headers']['ppcp_activation_status'] == 'false' ) {
            $error = new PPC_Error( 'ppcp_license_request_activation_error', $request['headers']['ppcp_activation_error'], array(
                'request_url' => $this->remote_URL,
                'parameters' => $parameters 
            ) );
            return $error->return_error();
		}
        
		return $request;
    }
	
    /**
     * Cares about license activation
     *
     * @access  public
     * @since   2.511
     * 
     * @param   $license key string license key
     */
	
    function activate( $license_key ) {
        if( $this->local_status ) {
            $error = new PPC_Error( 'ppcp_already_pro', __( 'You already have a license for this addon active.', 'ppc' ), array(
                'remote_url' => $this->remote_URL
            ) );
            return $error->return_error();
        }
        
        $request = $this->license_request( array( 
			'license_key' => $license_key
		) );
		
        if( is_wp_error( $request ) ) return $request;
        
        //Try to add option first, and then to update it if doesn't work
		if( ! update_option( $this->activation_key_name, maybe_unserialize( $request['headers']['ppcp_activation_details'] ) ) ) {
			$error = new PPC_Error( 'ppcp_license_activation_error', __( 'Error: could not store license details.', 'ppc' ), array(
				'meta_name' => $this->activation_key_name,
				'meta_value' => get_option( $this->activation_key_name ),
				'license_key' => $license_key,
				'activation_key' => maybe_unserialize( $request['headers']['ppcp_activation_details'] )
			) );
			return $error->return_error();
        }
    }
    
	/**
     * Deactivates current license key for this website.
     *
     * @access  public
     * @since   2.511
     */
	 
    function deactivate() {
        if( ! $this->local_status ) {
            $error = new PPC_Error( 'ppcp_not_pro', __( 'You don\'t have a license for this addon active', 'ppc' ), array(
                'remote_url' => $this->remote_URL
            ) );
            return $error->return_error();
        }
        
		$request = $this->license_request( array( 
			'license_deactivate' => true, 
			'activation_key' => get_option( $this->activation_key_name )
		) );
		
        if( is_wp_error( $request ) ) return $request;
        
        delete_option( $this->activation_key_name );
    }
    
    /**
     * Checks whether stored activation key is valid. 
     * 
     * Scheduled event.
     * 
     * @access  public
     * @since   2.511
     */
	 
    function check_activation() {
        if( ! $this->local_status ) {
            $error = new PPC_Error( 'ppcp_not_pro', __( 'You don\'t have a license for this addon active', 'ppc' ), array(
                'remote_url' => $this->remote_URL
            ) );
            return $error->return_error();
        }
        
		$request = $this->license_request( array( 
			'activation_key' => get_option( $this->activation_key_name )
		) );
		
        if( is_wp_error( $request ) AND $request->get_error_code() == 'ppcp_license_request_activation_error' )
			delete_option( $this->activation_key_name );
        else
			update_option( $this->activation_key_name, maybe_unserialize( $request['headers']['ppcp_activation_details'] ) );
    }
}
