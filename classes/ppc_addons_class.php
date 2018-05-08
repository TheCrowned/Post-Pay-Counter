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
	static function on_load_addons_page_enqueue() {
        global $ppc_global_settings;

        wp_enqueue_style( 'ppc_addons_style', $ppc_global_settings['folder_path'].'style/ppc_addons_style.css', array( 'wp-admin' ) );
        self::add_addons_list();
	}

	/**
	 * Adds addons list to db.
	 *
	 * @ccess	public
	 * @since 	2.610
	 */
	static function add_addons_list() {
		$addons = array(
			'data' => array(
				'Addons Premium Bundle' => array(
					'description' => 'Our best addons at discounted price. All the payment tracking features to save time and pay authors easily!',
					'image' => 'https://postpaycounter.com/ppcp/features/images/addons-premium-bundle.png',
					'link' => 'https://postpaycounter.com/addons-premium-bundle',
					'campaign' => 'ppc_premium_bundle'
				),
				'Post Pay Counter PRO' => array(
					'description' => 'Keep track of past payments, integrate with PayPal, Analytics and Adsense, and much more!',
					'image' => 'https://postpaycounter.com/ppcp/features/images/ppcp.png',
					'link' => 'https://postpaycounter.com/post-pay-counter-pro',
					'campaign' => 'ppcp'
				),
				'Facebook' => array(
					'description' => 'Pay writers basing on the number of Facebook shares, likes and comments their articles receive.',
					'image' => 'https://postpaycounter.com/ppcp_fb/features/images/stats.png',
					'link' => 'https://postpaycounter.com/facebook-pay-per-social-interactions-shares-likes-and-comments',
					'campaign' => 'ppcp_fb'
				),
				'BuddyPress' => array(
					'description' => 'Displays stats, payment history and PayPal email field in BuddyPress Member page.',
					'image' => 'https://postpaycounter.com/ppc_buddypress/features/images/buddypress-stats_crop.png',
					'link' => 'https://postpaycounter.com/buddypress',
					'campaign' => 'ppc_buddypress'
				),
				'Payment Request' => array(
					'description' => 'Allows authors to request payment when they reach a certain threshold.',
					'image' => 'https://postpaycounter.com/ppcp_rp/features/images/payment-request.png',
					'link' => 'https://postpaycounter.com/request-payment-require-authors-reach-threshold-request-paid/',
					'campaign' => 'ppc_rp'
				),
				'Referral Visits' => array(
					'description' => 'Pay authors for the visitors they bring to your site.',
					'image' => 'https://postpaycounter.com/wp-content/uploads/2018/02/ppc-referral-2.png',
					'link' => 'https://postpaycounter.com/referral-visits-pay-users-visitors-bring-site/',
					'campaign' => 'ppc_ref_vis'
				),
				'Author Payment Bonus' => array(
					'description' => 'Award a bonus to writers before paying: personally tweak the payroll, giving authors a little reward.',
					'image' => 'https://postpaycounter.com/ppc_apb/features/images/payment_confirm_crop.png',
					'link' => 'https://postpaycounter.com/author-payment-bonus-manually-change-the-total-payout-to-authors/',
					'campaign' => 'ppc_apb'
				),
				'Publisher bonus' => array(
					'description' => 'Set up an author rewarding system in which users (proof-readers) earn bonus by publishing posts.',
					'image' => 'https://postpaycounter.com/ppcp_pb/features/images/metabox.png',
					'link' => 'https://postpaycounter.com/publisher-bonus-editor-rewarding-system',
					'campaign' => 'ppcp_pb'
				),
				'User Roles Custom Settings' => array(
					'description' => 'Allows to set custom settings for each user role that apply to all users belonging to it.',
					'image' => 'https://postpaycounter.com/ppc_urcs/features/images/personalize_settings_box.jpg',
					'link' => 'https://postpaycounter.com/user-roles-custom-settings',
					'campaign' => 'ppc_urcs'
				),
				'Custom Email Notifications' => array(
					'description' => 'Allows to customize payment email notifications sent by the PRO version when users are paid.',
					'image' => 'https://postpaycounter.com/ppc_cen/features/images/custom-email-notifications.png',
					'link' => 'https://postpaycounter.com/custom-email-notifications/',
					'campaign' => 'ppc_cen'
				),
				'Category Custom Settings' => array(
					'description' => 'Allows to set custom settings for each category that apply to all posts belonging to it.',
					'image' => 'https://postpaycounter.com/ppc_ccs/features/images/category-custom-settings.png',
					'link' => 'https://postpaycounter.com/category-custom-settings',
					'campaign' => 'ppc_ccs'
				),
				'Pay Per Character' => array(
					'description' => 'Allows to pay writers depending on how many characters their posts are made of.',
					'image' => 'https://postpaycounter.com/ppc_ppc/features/images/stats.png',
					'link' => 'https://postpaycounter.com/pay-per-character',
					'campaign' => 'ppc_ppc'
				),
				'Author Basic Payment' => array(
					'description' => 'Allows to award authors a fixed fee for each payment.',
					'image' => 'https://postpaycounter.com/ppc_abp/features/images/stats.png',
					'link' => 'https://postpaycounter.com/author-basic-payment',
					'campaign' => 'ppc_abp'
				),
				'Stop Words' => array(
					'description' => 'Allows to specify a list of stop words that should not be counted when computing posts word count.',
					'image' => 'https://postpaycounter.com/ppcp_sw/features/images/stopwords.png',
					'link' => 'https://postpaycounter.com/stop-words-exclude-certain-words',
					'campaign' => 'ppcp_sw'
				),
				'Shortcode Stripper' => array(
					'description' => 'Allows to exclude text enclosed by shortcodes from words payment.',
					'image' => 'https://postpaycounter.com/ppc/addons/shortcode.jpg',
					'link' => 'https://postpaycounter.com/shortcode-stripper-exclude-shortcodes-from-words-payment/',
					'campaign' => 'ppc_shortcode_stripper'
				)
			),
			'time' => current_time( 'timestamp' ) + 3600*48
		);

		foreach( $addons['data'] as $title => &$info )
			$info['link'] .= '?utm_source=users_site&utm_medium=addons_list&utm_campaign='.$info['campaign']; //referral

		if( ! get_option( 'ppc_addons_list' ) )
			add_option( 'ppc_addons_list', $addons, '', 'no' );
		else
			update_option( 'ppc_addons_list', $addons );
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
				&nbsp;&mdash;&nbsp;<a href="https://postpaycounter.com/addons?utm_source=users_site&utm_medium=addons_list&utm_campaign=ppc_addons" class="button-primary" title="<?php _e( 'Browse All Extensions', 'post-pay-counter' ); ?>" target="_blank"><?php _e( 'Browse All Extensions', 'post-pay-counter' ); ?></a>
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
	   $cache = maybe_unserialize( get_option( 'ppc_addons_list' ) );

		/*if ( $cache === false OR $cache['time'] < current_time( 'timestamp' ) ) {
			$feed = wp_remote_get( 'https://postpaycounter.com/ppcp/features/ppcp_spit_html.php?addons_list', array( 'timeout' => 10 ) );

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
		}*/

		//We stopped pulling addons list from remote server since version 2.518
		if( is_string( $cache['data'] ) ) {
			return $cache['data'];
		} else if( is_array( $cache['data'] ) ) {
			$return = '';

			foreach( $cache['data'] as $title => $info ) {
				$return .= '<div class="ppc_addon">
				<h3 class="ppc_addon_title">'.$title.'</h3>
				<a href="'.$info['link'].'" title="'.$title.'" target="_blank"><img src="'.$info['image'].'" class="attachment-showcase wp-post-image" alt="" title="'.$title.'" /></a>
				<p>'.$info['description'].'</p>
				<a target="_blank" href="'.$info['link'].'" title="'.$title.'" class="button-secondary">Get this Add On</a>
				</div>';
			}

			return $return;
		}

	}
}
