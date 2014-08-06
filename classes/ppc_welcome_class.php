<?php

/**
 * @author      Stefano Ottolenghi
 * @copyright   2013
 * @since       2.34
 */

class PPC_welcome {
	
	/**
	 * Register Welcome dashboard pages (later hidden).
	 *
	 * @access public
	 * @since  2.34
	 */
    
	public static function add_pages() {
	   global $ppc_global_settings;
       
		//About Page
		add_dashboard_page(
			__( 'Welcome to Post Pay Counter', 'ppc' ),
			__( 'Welcome to Post Pay Counter', 'ppc' ),
			$ppc_global_settings['cap_manage_options'],
			'ppc-about',
			array( 'PPC_welcome', 'about_screen' )
		);
        
        //Changelog Page
		add_dashboard_page(
			__( 'Post Pay Counter Changelog', 'ppc' ),
			__( 'Post Pay Counter Changelog', 'ppc' ),
			$ppc_global_settings['cap_manage_options'],
			'ppc-changelog',
			array( 'PPC_welcome', 'changelog_screen' )
		);
	}

	/**
	 * Hide Welcome dashboard pages and inject custom css.
	 *
	 * @access public
	 * @since  2.34
	 */
    
	public static function admin_head() {
		remove_submenu_page( 'index.php', 'ppc-about' );
        remove_submenu_page( 'index.php', 'ppc-changelog' );
		?>
        
		<style type="text/css" media="screen">
		/*<![CDATA[*/
		
		.ppc-welcome-screenshots {
			float: right;
			margin-left: 10px!important;
            width: 400px;
		}
		/*]]>*/
		</style>
        
		<?php
	}

    /**
	 * Display navigation tabs and select current one.
	 *
	 * @access public
	 * @since  2.34
	 */
    
	public function print_tabs() {
		if( isset( $_GET['page'] ) )
            $selected =  (string) $_GET['page']; 
        else
            $selected = 'ppc-about';
		?>
        
		<h2 class="nav-tab-wrapper">
			<a class="nav-tab <?php if( $selected == 'ppc-about' ) echo 'nav-tab-active'; ?>" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'ppc-about' ), 'index.php' ) ) ); ?>">
				<?php _e( "Features", 'ppc' ); ?>
			</a>
			<a class="nav-tab <?php if( $selected == 'ppc-changelog' ) echo 'nav-tab-active';; ?>" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'ppc-changelog' ), 'index.php' ) ) ); ?>">
				<?php _e( 'Changelog', 'ppc' ); ?>
			</a>
		</h2>
		<?php
	}
    
	/**
	 * Render About Screen
	 *
	 * @access public
	 * @since  2.34
	 */
     
	public static function about_screen() {
        global $ppc_global_settings;        
		?>
        
		<div class="wrap about-wrap">
			<h1><?php printf( __( 'Welcome to Post Pay Counter Version %s', 'ppc' ), $ppc_global_settings['newest_version'] ); ?></h1>
			<div class="about-text"><?php _e( 'You got the latest release of Post Pay Counter, which is going to make handling authors\' payments much, much easier! The new menu on the left provides access to all the plugin features.', 'ppc' ) ?></div>
            
            <?php echo self::print_tabs(); ?>
            
			<div class="changelog">
				<h3><?php _e( 'A powerful payment manager', 'ppc' );?></h3>

				<div class="feature-section">
					<img src="<?php echo $ppc_global_settings['folder_path'].'style/images/screenshots/counting_settings.png'; ?>" class="ppc-welcome-screenshots"/>

					<h4><?php _e( 'Pay per post, word, visit, image and comment', 'ppc' );?></h4>
					<p><?php _e( 'Basically everything in a post can define its overall value. You can pay a fixed fee, add something depending on the number of words, maybe some more for visits, and then take care of images and comments as well. You can set a limit on the maximum number of each counting category and a total payment one too.', 'ppc' );?></p>

					<h4><?php _e( 'A zonal system, or an incremental one', 'ppc' );?></h4>
					<p><?php _e( 'Every time there is a number of something to be paid (a number of words, a number of visits...) you can choose whether you want to use an incremental system (e.g. each word is worth $0.01 => 100 words are worth $1.00) or a zonal one (e.g. between 100 and 200 words pay $1.20 => 190 words are worth $1.20) with as many zones you want.', 'ppc' );?></p>
                    
                    <h4><?php _e( 'Define what is paid and what is not','ppc' );?></h4>
					<p><?php _e( 'Include or exclude posts from stats depending on their post type (the plugin works with custom post types, yeah!), the user\'s role who wrote the it and its post status.', 'ppc' );?></p>
				</div>
			</div>

            <div class="changelog">
				<h3><?php _e( 'Stats page with calculations', 'ppc' );?></h3>

				<div class="feature-section">
					<img src="<?php echo $ppc_global_settings['folder_path'].'style/images/screenshots/stats.png'; ?>" class="ppc-welcome-screenshots"/>

					<h4><?php _e( 'The general view', 'ppc' );?></h4>
					<p><?php _e( 'General stats display all users in the same page, showing how much each of them should be paid basing on the number and value of their posts.', 'ppc' );?></p>

					<h4><?php _e( 'The detailed view', 'ppc' );?></h4>
					<p><?php _e( 'Detailed stats display an in-depth view for an author. You\'ll see how the total amount has been calculated, with details for each post (number of words, visits, images and comments).', 'ppc' );?></p>
                    
                    <h4><?php _e( 'Get old stats too', 'ppc' );?></h4>
					<p><?php _e( 'View posts calculations since the first written post, regardless of the plugin install date. A fancy date picker lets you shift between days and select the desired time range.', 'ppc' );?></p>
                    
                    <h4><?php _e( 'Overall stats', 'ppc' );?></h4>
					<p><?php _e( 'Fond of stats and numbers? At the bottom of regular stats an overall stats box displays your all-time overall stats, from the first post ever to the latest one.', 'ppc' );?></p>
				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Juicy additions', 'ppc' );?></h3>

				<div class="feature-section">
					<img src="<?php echo $ppc_global_settings['folder_path'].'style/images/screenshots/misc_settings.png'; ?>" class="ppc-welcome-screenshots"/>

					<h4><?php _e( 'Prevent users envy', 'ppc' );?></h4>
					<p><?php _e( 'Want users to be able to see only their own stats and not other users\' ones. Yes you can! There\'s a whole set of permission settings to define what users should and shouldn\'t see.', 'ppc' );?></p>
                    
                    <h4><?php _e( 'Personalize settings by user', 'ppc' );?></h4>
					<p><?php _e( 'Some users may deserve more or less than others: they can have custom settings for what concerns payments and permissions.', 'ppc' );?></p>
                    
                    <h4><?php _e( 'Import/Export settings','ppc' );?></h4>
					<p><?php _e( 'Exporting and importing settings makes duplicating settings easy! You can copy them from a user to another or even from different websites.', 'ppc' );?></p>
				</div>
			</div>
            
            <?php self::display_pro_features(); ?>
            
			<div class="return-to-dashboard">
				<a href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'ppc-options' ), 'admin.php' ) ) ); ?>"><?php _e( 'Go to Post Pay Counter Settings', 'ppc' ); ?></a>
			</div>
		</div>
		<?php
	}
    
    /**
	 * Render Changelog Screen
	 *
	 * @access public
	 * @since  2.34
	 */
     
	public static function changelog_screen() {
        global $ppc_global_settings;        
		?>
        
		<div class="wrap about-wrap">
			<h1><?php printf( __( 'Welcome to Post Pay Counter Version %s', 'ppc' ), $ppc_global_settings['newest_version'] ); ?></h1>
			<div class="about-text"><?php _e( 'You got the latest release of Post Pay Counter, which is going to make handling authors\' payments much, much easier! The new menu on the left provides access to all the plugin features.', 'ppc' ) ?></div>
            
            <?php echo self::print_tabs(); ?>
            
			<div class="changelog">
				<h3><?php _e( 'Version changes', 'ppc' );?></h3>

				<div class="feature-section">
					<?php echo self::parse_readme_changelog(); ?>
				</div>
			</div>

			<div class="return-to-dashboard">
				<a href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'ppc-options' ), 'admin.php' ) ) ); ?>"><?php _e( 'Go to Post Pay Counter Settings', 'ppc' ); ?></a>
			</div>
		</div>
		<?php
	}
    
    /**
     * Display PRO features.
     * 
     * @access  public
     * @since   2.34
     */
    
    public static function display_pro_features() {
        global $ppc_global_settings;
        ?>

        <div class="changelog">
				<h3><?php _e( 'The springboard for the PRO', 'ppc' );?></h3>

				<div class="feature-section">
					<img src="<?php echo $ppc_global_settings['folder_path'].'style/images/screenshots/pro_stats.png'; ?>" class="ppc-welcome-screenshots"/>

					<h4><?php _e( 'Analytics as visits source', 'ppc' );?></h4>
					<p><?php _e( 'Use your account on the world-leading website visits tracking system to pay writers basing on reliable visits data. Use pageviews or unique pageviews as you like and stop the counting after a certain number of days.' );?></p>
                    
                    <h4><?php _e( 'Pay with Paypal', 'ppc' );?></h4>
					<p><?php _e( 'Pay your writers directly from the stats page with Adaptive Payments.' );?></p>
                    
                    <h4><?php _e( 'Tons of other features', 'ppc' );?></h4>
					<p><?php printf( __( 'The %1$sPRO version%2$s includes a damn more lot of interesting features, among which but not only: payment manager and payment history to keep track of past transactions, award payment bonus to single posts, shortcode for stats, stats exporting... see? There\'s not enough space to list them all!', 'ppc' ), '<a href="http://www.thecrowned.org/post-pay-counter-pro" title="Post Pay Counter PRO">', '</a>' );?></p>
				</div>
			</div>

        <?php
    } 
    
    /**
	 * Parse the readme.txt file - extract changelog
	 *
	 * @access  public
     * @since   2.34
     * @author  Pippin Williamson (Easy Digital Downloads)
	 * @return  string $readme HTML formatted readme file
	 */
    
    public static function parse_readme_changelog() {
		global $ppc_global_settings;
        
        $file = $ppc_global_settings['dir_path'].'readme.txt';
        
        if ( ! file_exists( $file ) ) {
			$readme = '<p>' . __( 'No valid changelog was found.', 'ppc' ) . '</p>';
		} else {
			$readme = file_get_contents( $file );
			$readme = nl2br( esc_html( $readme ) );

			$readme = end( explode( '== Changelog ==', $readme ) );

			$readme = preg_replace( '/`(.*?)`/', '<code>\\1</code>', $readme );
			$readme = preg_replace( '/[\040]\*\*(.*?)\*\*/', ' <strong>\\1</strong>', $readme );
			$readme = preg_replace( '/[\040]\*(.*?)\*/', ' <em>\\1</em>', $readme );
			$readme = preg_replace( '/= (.*?) =/', '<h4>\\1</h4>', $readme );
			$readme = preg_replace( '/\[(.*?)\]\((.*?)\)/', '<a href="\\2">\\1</a>', $readme );
		}

		return $readme;
	}
    
	/**
	 * Sends user to the Welcome page on activation and to Changelog page on update.
	 *
	 * @access public
	 * @since  2.34
	 */
	
    public static function welcome() {
		global $ppc_global_settings;

		if( get_transient( $ppc_global_settings['transient_activation_redirect'] ) OR get_transient( $ppc_global_settings['transient_update_redirect'] ) ) {
            
            //Return if activating from network, or bulk
            if( is_network_admin() || isset( $_GET['activate-multi'] ) )
                return;
            
			wp_safe_redirect( admin_url( add_query_arg( array( 'page' => 'ppc-about' ), 'admin.php' ) ) );
        }
        
		//Delete redirect transients
        delete_transient( $ppc_global_settings['transient_activation_redirect'] );
        delete_transient( $ppc_global_settings['transient_update_redirect'] );		
	}
}
?>