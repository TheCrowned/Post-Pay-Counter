<?php

/**
 * Notifications
 *
 * Contains all notifications functions.
 *
 * @package     PPC
 * @copyright   2014
 * @since		2.46
 * @author 		Stefano Ottolenghi
 */

class PPC_notifications {
    
    public $current_notification;
    
    public function __construct( $notification ) {
        $this->current_notification = $notification;
    }

    /**
     * Adds a simple WordPress pointer to plugin's menu
     * 
     * @access  public
     * @since   2.46
     */
     
    function display_notification() {
    	?>
    
<div id="<?php echo $this->current_notification['id']; ?>" class="updated fade ppc_notification">
	<p><?php echo $this->current_notification['text']; ?> <a href="" class="ppc_dismiss_notification" id="ppc_dismiss_<?php echo $this->current_notification['id']; ?>" accesskey="<?php echo $this->current_notification['id']; ?>" title="<?php _e( 'Dismiss', 'post-pay-counter' ); ?>"><?php _e( 'Dismiss', 'post-pay-counter' ); ?></a></p>
</div>
	
<script type="text/javascript">
//<![CDATA[
jQuery(document).ready( function($) {
	$('#ppc_dismiss_<?php echo $this->current_notification['id']; ?>').on('click', function(e) {
		e.preventDefault();
		
		var clicked = $(this);
		var data = {
			action: "ppc_dismiss_notification",
			id: clicked.attr("accesskey"),
			_ajax_nonce: "<?php echo wp_create_nonce( 'ppc_dismiss_notification' ); ?>"
		};
		
		$.post(ajaxurl, data, function(response) {
			if(response.indexOf('ok') >= 0) {
				clicked.closest('div').fadeOut();
			}
		});
	});
});
//]]>
</script>
        
        <?php
     }
	 
	 /**
	 * Notifications get list remote.
	 *
	 * @access	public
	 * @since 	2.46
	 */
	 
	static function notifications_get_list() {
		if ( false === ( $notifications = get_transient( 'ppc_notifications_list' ) ) ) {
			$feed = wp_remote_get( 'http://postpaycounter.com/ppcp/features/ppcp_spit_html.php?notifications_list', array( 'timeout' => 4 ) );
            
			if ( ! is_wp_error( $feed ) ) {
				if ( isset( $feed['body'] ) && strlen( $feed['body'] ) > 0 )
					$notifications = maybe_unserialize( wp_remote_retrieve_body( $feed ) );
			} else {
				$notifications = $feed;
				//new PPC_Error( "ppc_notifications_get_remote_error", $feed->get_error_message(), $feed->get_error_code() ); //log error
			}
			
			set_transient( 'ppc_notifications_list', $notifications, 3600*8 ); //log even if error to avoid making too many requests
		}
		
		return apply_filters( 'ppc_notifications_get_list', $notifications );
	}
}
