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
     * Holds addon activation key option name and value.
     */
	public $activation_key_name;
	public $activation_key;

    /**
     * Initialize a new instance of the WordPress Auto-Update class
     * @param string $current_version
     * @param string $update_path
     * @param string $plugin_slug
     */
    function __construct( $current_version, $update_path, $plugin_slug, $activation_key_name ) {
		//Only check every six hours
		$transient = get_site_transient( 'update_plugins' );
		if( ! is_object( $transient ) OR ! isset( $transient->last_checked ) OR ! isset( $transient->checked ) ) return;
		
		$checked_plugins = $transient->checked;
		if( $transient->last_checked > ( time() - 3600*6 ) AND isset( $checked_plugins[$plugin_slug] ) )
			return;
			
        // Set the class public variables
        $this->current_version = $current_version;
        $this->update_path = $update_path;
        $this->plugin_slug = $plugin_slug;
        $this->activation_key_name = $activation_key_name;
        $this->activation_key = get_option( $activation_key_name );
		$this->activation_key = $this->activation_key['activation_key'];

        list($t1, $t2) = explode('/', $plugin_slug);
        $this->slug = str_replace('.php', '', $t2);

        // define the alternative API for updating checking
        add_filter('pre_set_site_transient_update_plugins', array(&$this, 'check_update'));

        // Define the alternative response for information checking
        add_filter('plugins_api', array(&$this, 'check_info'), 10, 3);

        //Enqueue on WP cron event
        add_action( 'wp_update_plugins', array( &$this, 'check_update' ) );

		//Maybe display notice in plugin page if addon license is expired
        add_action( 'in_plugin_update_message-' . plugin_basename( $this->plugin_slug ), array( $this, 'plugin_row_license_missing' ), 10, 2 );
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

        $request = wp_remote_post( $this->update_path, apply_filters( 'ppcp_autoupdate_get_remote_version_args', array(
            'timeout' => 10,
            'body' => array(
                'action' => 'version',
                'activation_key' => $this->activation_key,
                'website' => site_url(),
				'language' => get_bloginfo( 'language' ),
				'PPC_version' => $ppc_global_settings['current_version'],
                'addon_version' => $this->current_version
            )
        ) ) );

        if ( ! is_wp_error($request) || wp_remote_retrieve_response_code( $request ) === 200 ) {
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

        $request = wp_remote_post( $this->update_path, apply_filters( 'ppcp_autoupdate_get_remote_information_args', array(
            'timeout' => 10,
            'body' => array(
                'action' => 'info',
                'website' => site_url(),
                'activation_key' => $this->activation_key,
				'language' => get_bloginfo( 'language' ),
				'PPC_version' => $ppc_global_settings['current_version'],
                'addon_version' => $this->current_version
            )
        ) ) );

        if ( ! is_wp_error( $request ) || wp_remote_retrieve_response_code( $request ) === 200 ) {
			return maybe_unserialize( $request['body'] );
        } else {
            new PPC_Error( 'ppcp_get_remote_information_error', 'Could not get latest version information from update server.', array(
                'response_code' => wp_remote_retrieve_response_code($request),
                'request' => $request
            ) );
        }
    }

    /**
	 * Displays message inline on plugin row that the license key is expired
	 *
	 * @access  public
	 * @since   2.602
	 * @return  void
	 * @from 	EDD
	 */
	public function plugin_row_license_missing( $plugin_data, $version_info ) {
		static $showed_imissing_key_message;

		$license = get_option( $this->activation_key_name );

		if( ( is_array( $license ) AND $license['expiration_time'] < current_time( 'timestamp' ) ) AND empty( $showed_imissing_key_message[ $this->plugin_slug ] ) ) {

			echo '&nbsp;<strong><a href="' . esc_url( admin_url( 'admin.php?page=ppc-options' ) ) . '">' . __( 'Enter a valid, non-expired license key for automatic updates.', 'post-pay-counter' ) . '</a></strong>';
			$showed_imissing_key_message[ $this->plugin_slug ] = true;
		}
	}
}
