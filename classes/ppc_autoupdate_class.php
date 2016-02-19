<?php

/**
 * Auto update
 *
 * Handles addons autoupdate check and details pulling.
 *
 * @package     PPC
 * @copyright   2013
 * @author 		WP Tuts
 * @since		2.511
 */

class PPC_auto_update {
    /**
     * The plugin current version
     * @var string
     */
    public $current_version;

    /**
     * The plugin remote update path
     * @var string
     */
    public $update_path;

    /**
     * Plugin Slug (plugin_directory/plugin_file.php)
     * @var string
     */
    public $plugin_slug;

    /**
     * Plugin name (plugin_file)
     * @var string
     */
    public $slug;
    
    /**
     * Holds addon activation key option name.
     */ 
	
	//public $activation_key_name;

    /**
     * Initialize a new instance of the WordPress Auto-Update class
     * @param string $current_version
     * @param string $update_path
     * @param string $plugin_slug
     */
    function __construct( $current_version, $update_path, $plugin_slug, $activation_key_name ) {
        // Set the class public variables
        $this->current_version = $current_version;
        $this->update_path = $update_path;
        $this->plugin_slug = $plugin_slug;
        $this->activation_key = get_option( $activation_key_name );
		$this->activation_key = $this->activation_key['activation_key'];
        
        list($t1, $t2) = explode('/', $plugin_slug);
        $this->slug = str_replace('.php', '', $t2);

        // define the alternative API for updating checking
        add_filter('pre_set_site_transient_update_plugins', array(&$this, 'check_update'));

        // Define the alternative response for information checking
        add_filter('plugins_api', array(&$this, 'check_info'), 10, 3);
        
        //Enqueue on WP cron event
        add_action( 'wp_update_plugins', array( $this, 'check_update' ) );
    }

    /**
     * Add our self-hosted autoupdate plugin to the filter transient
     *
     * @param $transient
     * @return object $ transient
     */
    public function check_update($transient = array()) {
        if (empty($transient))
            $transient = get_site_transient( 'update_plugins' );
        
        // Get the remote version
        $remote_version = $this->getRemote_version();
        
        // If a newer version is available, add the update
        if (version_compare($this->current_version, $remote_version, '<')) {
            
            //Get information and download url
            $information = $this->getRemote_information();
            
			if( ! is_object( $information ) ) return;
			
            $obj = new stdClass();
            $obj->slug = $this->slug;
            $obj->new_version = $remote_version;
            $obj->url = $this->update_path;
            $obj->package = $information->download_link;
            $transient->response[$this->plugin_slug] = $obj;
        }
        return $transient;
    }

    /**
     * Add our self-hosted description to the filter
     *
     * @param boolean $false
     * @param array $action
     * @param object $arg
     * @return bool|object
     */
    public function check_info($false, $action, $arg) {
        if (isset($arg->slug) AND $arg->slug === $this->slug) {
            $information = $this->getRemote_information();
            return $information;
        }
        
		/** 
		* Return variable $false instead of explicitly returning boolean FALSE
		* wordpress passes FALSE here by default
		*/
		return $false; 
    }

    /**
     * Return the remote version
     * @return string $remote_version
     */
    public function getRemote_version() {
		global $ppc_global_settings;
		
        $request = wp_remote_post($this->update_path, apply_filters( 'ppcp_autoupdate_get_remote_version_args', array(
            'timeout' => 10,
            'body' => array(
                'action' => 'version', 
                'activation_key' => $this->activation_key,
                'ppc_version' => $ppc_global_settings['current_version'],
                'addon_version' => $this->current_version
            )
        ) ) );
		
        if (!is_wp_error($request) || wp_remote_retrieve_response_code($request) === 200) {
            return $request['body'];
        } else {
            new PPC_Error( 'ppcp_get_remote_version_error', 'Could not get latest version from update server.', array(
                'response_code' => wp_remote_retrieve_response_code($request),
                'request' => $request
            ), false );
        }
    }

    /**
     * Get information about the remote version
     * @return bool|object
     */
    public function getRemote_information() {
		global $ppc_global_settings;
		
        $request = wp_remote_post($this->update_path, apply_filters( 'ppcp_autoupdate_get_remote_information_args', array(
            'timeout' => 10,
            'body' => array(
                'action' => 'info', 
                'activation_key' => $this->activation_key,
                'ppc_version' => $ppc_global_settings['current_version'],
                'addon_version' => $this->current_version
            )
        ) ) );
        if (!is_wp_error($request) || wp_remote_retrieve_response_code($request) === 200) {
			return unserialize($request['body']);
        } else {
            new PPC_Error( 'ppcp_get_remote_information_error', 'Could not get latest version information from update server.', array(
                'response_code' => wp_remote_retrieve_response_code($request),
                'request' => $request
            ) );
        }
    }
}
?>