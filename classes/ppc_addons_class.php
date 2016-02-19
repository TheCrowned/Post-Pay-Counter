<?php
/**
 * Add-ons
 *
 * @package     PPC
 * @copyright   Stefano Ottolenghi 2014
 * @since       2.40
 */

class PPC_addons {

	/**
	 * Add-ons page enqueue styles.
	 *
	 * @access	public
	 * @since 	2.40
	 */
	
	function on_load_addons_page_enqueue() {
        global $ppc_global_settings;
        
        wp_enqueue_style( 'ppc_addons_style', $ppc_global_settings['folder_path'].'style/ppc_addons_style.css', array( 'wp-admin' ) );
	}

	/**
	 * Add-ons Page
	 *
	 * Renders the add-ons page content.
	 *
	 * @ccess	public	
	 * @since 	2.40
	 */
	 
	static function addons_page() {
		?>
		<div class="wrap" id="ppc_addons">
			<h2>
				<?php _e( 'Addons for Post Pay Counter', 'post-pay-counter' ); ?>
				&nbsp;&mdash;&nbsp;<a href="http://postpaycounter.com/addons?utm_source=users_site&utm_medium=addons_list&utm_campaign=ppc_addons" class="button-primary" title="<?php _e( 'Browse All Extensions', 'post-pay-counter' ); ?>" target="_blank"><?php _e( 'Browse All Extensions', 'post-pay-counter' ); ?></a>
			</h2>
			<p><?php _e( 'These addons add more features to Post Pay Counter.', 'post-pay-counter' ); ?></p>
			<?php echo self::addons_get_list(); ?>
		</div>
		<?php
		//echo ob_get_clean();
	}

	/**
	 * Add-ons get list remote.
	 *
	 * @access	public
	 * @since 	2.40
	 */
	 
	static function addons_get_list() {
	   $cache = get_option( 'ppc_addons_list' );
	   
		if ( $cache === false OR $cache['time'] < current_time() ) {
			$feed = wp_remote_get( 'http://postpaycounter.com/ppcp/features/ppcp_spit_html.php?addons_list', array( 'timeout' => 10 ) );
			
			if ( ! is_wp_error( $feed ) ) {
				if ( isset( $feed['body'] ) && strlen( $feed['body'] ) > 0 ) {
					$cache = array();
					$cache['data'] = wp_remote_retrieve_body( $feed );
					$cache['time'] = current_time() + 3600*48;
					
					update_option( 'ppc_addons_list', $cache );
				}
			} else {
				if( ! isset( $cache['data'] ) OR ! is_array( $cache['data'] ) )
					$cache['data'] = '<div class="error"><p>' . __( 'There was an error retrieving the extensions list from the server. Please try again later.', 'post-pay-counter' ) . '</div>';
			}
		}
		
		return $cache['data'];
	}
}
