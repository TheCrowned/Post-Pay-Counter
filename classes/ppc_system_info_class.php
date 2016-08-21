<?php

/**
 * System Info
 *
 * Used to get techincal data for debugging purposes.
 *
 * @package     PPC
 * @copyright   Copyright (c) 2013, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
*/

class PPC_system_info {

	/**
	 * System info
	 *
	 * Shows the system info panel which contains version data and debug info.
	 *
	 * @since 2.1
	 * @author Chris Christoff
	 * @adapted Stefano Ottolenghi
	 */

	static function system_info() {
		global $wpdb, $ppc_global_settings;

		if( isset( $_POST['ppc_download_sysinfo'] ) )
			self::system_info_download();

		?>

		<div class="wrap">
			<h2>Post Pay Counter - <?php _e( 'System Information', 'post-pay-counter' ) ?></h2>
			<form action="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=ppc-system-info&noheader=true' ), 'ppc_download_sysinfo' ) ); ?>" method="post" dir="ltr">
				<textarea readonly="readonly" onclick="this.focus();this.select()" style="font-family: monospace; width: 700px; height: 500px;" name="ppc-sysinfo" title="<?php _e( 'To copy the system info, click below then press Ctrl + C (PC) or Cmd + C (Mac).', 'post-pay-counter' ); ?>">
### Begin System Info ###

## Please include this information when posting support requests ##

<?php
        do_action( 'ppc_system_info_before' );
?>
Multisite:                <?php echo is_multisite() ? 'Yes' . "\n" : 'No' . "\n" ?>

SITE_URL:                 <?php echo site_url() . "\n"; ?>
HOME_URL:                 <?php echo home_url() . "\n"; ?>

PPC Version:              <?php echo $ppc_global_settings['current_version'] . "\n"; ?>
WordPress Version:        <?php echo get_bloginfo( 'version' ) . "\n"; ?>

PHP Version:              <?php echo PHP_VERSION . "\n"; ?>
Web Server Info:          <?php echo $_SERVER['SERVER_SOFTWARE'] . "\n"; ?>

PHP Memory Limit:         <?php echo ini_get( 'memory_limit' ) . "\n"; ?>
PHP Time Limit:           <?php echo ini_get( 'max_execution_time' ) . "\n"; ?>

WP_DEBUG:                 <?php echo defined( 'WP_DEBUG' ) ? WP_DEBUG ? 'Enabled' . "\n" : 'Disabled' . "\n" : 'Not set' . "\n" ?>

WP Table Prefix:          <?php echo "Length: ". strlen( $wpdb->prefix ); echo " Status:"; if ( strlen( $wpdb->prefix )>16 ) {echo " ERROR: Too Long";} else {echo " Acceptable";} echo "\n"; ?>

<?php
	$params = array(
		'body' => ''
	);

	$response = wp_remote_post( 'http://postpaycounter.com', $params );

	if ( ! is_wp_error( $response ) && $response['response']['code'] >= 200 && $response['response']['code'] < 300 ) {
		$WP_REMOTE_POST =  'wp_remote_post() works' . "\n";
	} else {
		$WP_REMOTE_POST =  'wp_remote_post() does not work' . "\n";
	}
	?>
WP Remote Post:           <?php echo $WP_REMOTE_POST; ?>

DISPLAY ERRORS:           <?php echo ( ini_get( 'display_errors' ) ) ? 'On (' . ini_get( 'display_errors' ) . ')' : 'N/A'; ?><?php echo "\n"; ?>
FSOCKOPEN:                <?php echo ( function_exists( 'fsockopen' ) ) ? 'Your server supports fsockopen.' : 'Your server does not support fsockopen.'; ?><?php echo "\n"; ?>
cURL:                     <?php echo ( function_exists( 'curl_init' ) ) ? 'Your server supports cURL.' : 'Your server does not support cURL.'; ?><?php echo "\n"; ?>

ACTIVE PLUGINS:
<?php
	$plugins = get_plugins();
	$active_plugins = get_option( 'active_plugins', array() );

	foreach ( $plugins as $plugin_path => $plugin ) {
		// If the plugin isn't active, don't show it.
		if ( ! in_array( $plugin_path, $active_plugins ) )
			continue;

		echo $plugin['Name'] . ': ' . $plugin['Version'] ."\n";
	}

	if ( is_multisite() ) :
	?>

NETWORK ACTIVE PLUGINS:
<?php
	$plugins = wp_get_active_network_plugins();
	$active_plugins = get_site_option( 'active_sitewide_plugins', array() );

	foreach ( $plugins as $plugin_path ) {
		$plugin_base = plugin_basename( $plugin_path );

		// If the plugin isn't active, don't show it.
		if ( ! array_key_exists( $plugin_base, $active_plugins ) )
			continue;

		$plugin = get_plugin_data( $plugin_path );

		echo $plugin['Name'] . ' :' . $plugin['Version'] ."\n";
	}

	endif;

	do_action( 'ppc_system_info_after' );
	?>

### End System Info ###</textarea>
				<p>
					<input type="submit" name="ppc_download_sysinfo" value="<?php _e( 'Download System Info File', 'post-pay-counter' ); ?>" class="button-primary" />
				</p>
			</form>
			</div>
		</div>
	<?php
	}

	/**
	 * Generates the System Info Download File
	 *
	 * @since 2.1
	 */

	static function system_info_download() {
		nocache_headers();

        check_admin_referer( 'ppc_download_sysinfo' );

		header( "Content-type: text/plain" );
		header( 'Content-Disposition: attachment; filename="ppc-system-info.txt"' );

		die( wp_strip_all_tags( $_POST['ppc-sysinfo'] ) );
	}
}