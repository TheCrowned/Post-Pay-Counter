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
        <p><?php echo $this->current_notification['text']; ?> <a href="" class="ppc_dismiss_notification" accesskey="<?php echo $this->current_notification['id']; ?>" title="<?php _e( 'Dismiss', 'ppc' ); ?>"><?php _e( 'Dismiss', 'ppc' ); ?></a></p>
    </div>
    	
    	<script type="text/javascript">
    	//<![CDATA[
    	jQuery(document).ready( function($) {
    		$('.ppc_dismiss_notification').on('click', function(e) {
                e.preventDefault();
                
                var clicked = $(this);
                var data = {
                    action: "ppc_dismiss_notification",
                    id: clicked.attr("accesskey"),
                    _ajax_nonce: "<?php echo wp_create_nonce( 'ppc_dismiss_notification' ); ?>"
                };
                
                $.post(ajaxurl, data, function(response) {
                    if(response.indexOf('ok') < 0) {
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
		if ( false === ( $cache = get_transient( 'ppc_notifications_list' ) ) ) {
			$feed = wp_remote_get( 'http://thecrowned.org/ppcp/features/ppcp_spit_html.php?notifications_list', array( 'timeout' => 10 ) );
            
			if ( ! is_wp_error( $feed ) ) {
				if ( isset( $feed['body'] ) && strlen( $feed['body'] ) > 0 ) {
					$cache = maybe_unserialize( wp_remote_retrieve_body( $feed ) );
					set_transient( 'ppc_notifications_list', $cache, 3600 );
				}
			} else {
				$cache = '<div class="error"><p>' . __( 'There was an error retrieving the notifications list from the server. Please try again later.', 'ppc' ) . '</div>';
			}
		}
		
		return $cache;
	}
}
?>