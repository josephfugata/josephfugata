<?php
	
	/* Function to declare PHP is too old ! */
	add_action( 'admin_notices', 'wpvr_show_php_too_old' );
	function wpvr_show_php_too_old() {
		if ( version_compare( PHP_VERSION, WPVR_REQUIRED_PHP_VERSION, '<' ) ) {
			$php_version = explode( '+', PHP_VERSION );
			?>
            <div class="error">
                <p>
                    <strong>WP Video Robot ERROR</strong><br/>
					<?php echo __( 'You are using PHP version ', WPVR_LANG ) . $php_version[0]; ?>.<br/>
					<?php printf( __( 'WP Video Robot needs version %s at least to work properly.', WPVR_LANG ), WPVR_REQUIRED_PHP_VERSION ); ?>
                    <br/>
					<?php echo __( 'Please upgrade PHP.', WPVR_LANG ); ?>
                </p>
            </div>
			<?php
		}
	}
	
	/* Function to show error message if cron not writable */
	add_action( 'admin_notices', 'wpvr_cron_file_permission_issue' );
	function wpvr_cron_file_permission_issue() {
		$f = WPVR_PATH . 'assets/php/cron.txt';
		if ( is_writable( $f ) === false ) {
			?>
            <div class="error">
                <p>
                    <strong>WP Video Robot ERROR</strong><br/>
					<?php echo __( 'The plugin cannot work automatically.', WPVR_LANG ); ?>
					<?php echo __( 'Please, make sure this file is writable :', WPVR_LANG ); ?>
                    <strong><?php echo $f; ?></strong><br/>
					<?php echo __( 'If you cannot do that, contact your host.', WPVR_LANG ); ?>

                </p>
            </div>
			<?php
		}
	}
	
	/* Function to show WPVR NOtices */
	add_action( 'admin_notices', 'wpvr_show_notices' );
	function wpvr_show_notices() {
		$wpvr_notices = get_option( 'wpvr_notices' );
		if ( isset( $_GET['action'] ) && $_GET['action'] == 'do-plugin-upgrade' ) {
			return false;
		}
		if ( $wpvr_notices == '' ) {
			return false;
		}
		//d( $wpvr_notices );
		foreach ( (array) $wpvr_notices as $notice ) {
			if ( ! isset( $notice['is_manual'] ) || $notice['is_manual'] === false ) {
				wpvr_render_notice( $notice );
			}
		}
	}
	
	/* Function to show WPVR NOtices */
	add_action( 'admin_notices', 'wpvr_show_multisite_notices' );
	function wpvr_show_multisite_notices() {
		if ( ! is_multisite() ) {
			return false;
		}
		$wpvr_notices = get_site_option( 'wpvr_notices' );
		if ( isset( $_GET['action'] ) && $_GET['action'] == 'do-plugin-upgrade' ) {
			return false;
		}
		if ( $wpvr_notices == '' ) {
			return false;
		}
		//d( $wpvr_notices );
		foreach ( (array) $wpvr_notices as $notice ) {
			if ( ! isset( $notice['is_manual'] ) || $notice['is_manual'] === false ) {
				wpvr_render_notice( $notice );
			}
		}
	}
	
	
	/* Function to show demo message */
	add_action( 'admin_notices', 'wpvr_show_demo_message' );
	function wpvr_show_demo_message() {
		if ( WPVR_IS_DEMO ) {
			global $current_user;
			$user_id = $current_user->ID;
			/* Check that the user hasn't already clicked to ignore the message */
			if ( ! get_user_meta( $user_id, 'wpvr_show_demo_notice' ) ) {
				global $wpvr_options;
				$hideLink = "?wpvr_show_demo_notice=0";
				foreach ( (array) $_GET as $key => $value ) {
					$hideLink .= "&$key=$value";
				}
				?>
                <div class="updated">
                    <div class="wpvr_demo_notice">
                        <a class="pull-right"
                           href="<?php echo $hideLink; ?>"><?php _e( 'Hide this notice', WPVR_LANG ); ?></a>

                        <strong>WELCOME TO THE LIVE DEMO OF WP VIDEO ROBOT
                            v<?php echo WPVR_VERSION; ?></strong><br/><br/>

                        <div class="wpvr_demo_notice_left">
                            <i class="fa fa-smile-o"></i>
                        </div>
                        <div class="wpvr_demo_notice_right">
                            Feel free to play around with the plugin sourcesand videos to get a feel of how things work.
                            <br/>
                            Don't forget to check this <a class="wpvr_notice_button"
                                                          href="<?php echo WPVR_SITE_URL; ?>">demo front-end</a>
                            to see how your imported videos are rendering with the
                            <a href="https://themeforest.net/item/sahifa-responsive-wordpress-news-magazine-newspaper-theme/2819356/?ref=pressaholic">Sahifa
                                Premium Theme</a>.
                            <br/><br/>
                            You can also check out our <a class="wpvr_notice_button"
                                                          href="<?php echo WPVR_DEMOS_URL; ?>"
                                                          title="Plugin Integrations">
                                integrations demos </a> with several popular video themes. The contents of this demo
                            site is reset once a week.
                        </div>
                    </div>

                    <div class="wpvr_clearfix"></div>
                </div>
				<?php
			}
		}
	}
	
	/* Display message to adapt old data */
	add_action( 'admin_notices', 'wpvr_adapt_old_data_reminder' );
	function wpvr_adapt_old_data_reminder() {
		global $wpdb;
		
		if ( isset( $_GET['adapt_old_data'] ) ) {
			return false;
		}
		$wpvr_actions_url = admin_url( 'admin.php?page=wpvr&adapt_old_data' );
		$wpvr_is_adapted  = get_option( 'wpvr_is_adapted' );
		
		if ( $wpvr_is_adapted != WPVR_VERSION ) {
   
			$sql
				= "
                    SELECT
                        count(*)
                    FROM
                        $wpdb->posts P
                    WHERE P.ID IN(
                        SELECT
                            P.ID
                        FROM
                            $wpdb->posts P
                            INNER JOIN $wpdb->postmeta M ON P.ID = M.post_id
                        WHERE
                            P.post_type IN ('" . WPVR_SOURCE_TYPE . "')
                            AND post_status != 'auto-draft'
                            AND M.meta_key IN ('wpvr_source_plugin_version' , 'wpvr_video_plugin_version' )
                            AND M.meta_value < '" . WPVR_VERSION . "'
                    )
                ";
			
			$count = $wpdb->get_var( $sql );
			
			// Clear all WPVR Notices
			update_option( 'wpvr_notices', array() );
			
			
			if ( $count != 0 ) {
				wpvr_render_notice( array(
					'title'   => __( 'WP Video Robot WARNING', WPVR_LANG ),
					'class'   => 'warning', //updated or warning or error
					'content' => '' .
					             __( 'Looks like you have some sources and videos from an older version of the plugin.', WPVR_LANG ) .
					             '<br/>' .
					             '<a href = "' . $wpvr_actions_url . '">' .
					             __( 'Click here to adapt them to this new version', WPVR_LANG ) . ' ( ' . WPVR_VERSION . ' )' .
					             '</a>'
				,
					'hidable' => false,
					'color'   => '#999',
					'icon'    => 'fa-info-circle',
				) );
				
				return false;
			}
			
			update_option( 'wpvr_is_adapted', WPVR_VERSION );
		}
	}