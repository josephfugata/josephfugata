<?php
	
	global $wpvr_options, $wpvr_cron_token;
	
	$security_data       = wpvr_get_total_fetched_videos_per_run();
	$security_warning    = false;
	$problematic_sources = "";
	foreach ( (array) $security_data as $id => $data ) {
		if ( $data['warning'] === true ) {
			$editLink            = get_edit_post_link( $id );
			$security_warning    = true;
			$problematic_sources .= "
                <li>
                    <strong>" . $data["source_name"] . " </strong> :
                    " . $data["wanted_videos"] . " wanted videos with " . $data["sub_sources"] . " subsource(s).
                    
                    --- <a target ='_blank' href='" . $editLink . "'> Edit this source </a>
                </li>
            ";
		}
	}

?>

<!-- Security Warning -->
<?php if ( $security_warning === true ) { ?>
    <!-- WIDE DASHBOARD WIDGET -->
    <div id="" class="postbox wide">
        <h3 class="hndle"><span> <?php _e( 'Important Notice', WPVR_LANG ); ?> </span></h3>

        <div class="inside">
            <div class="wpvr_wide_notice_icon pull-left">
                <i class="fa fa-warning"></i>
            </div>
            <div class="pull-left">
				<?php echo __( 'You are asking the plugin to fetch and import too many wanted videos on each run.', WPVR_LANG ); ?>
                <br/>
				<?php echo __( 'That will probably decrease your site performances each time the plugin works.', WPVR_LANG ); ?>
                <br/>
				<?php echo __( 'Here is a list of the problematic sources :', WPVR_LANG ); ?>
                <br/><br/>
				
				<?php echo $problematic_sources; ?>
            </div>
            <div class="wpvr_clearfix"></div>

        </div>
    </div>
    <!-- WIDE DASHBOARD WIDGET -->
<?php } ?>

<div id="" class="postbox">
    <h3 class="hndle"><span> <?php echo __( 'Automation', WPVR_LANG ); ?> </span></h3>

    <div class="inside pull-left wpvr_automation_panel_left">
		
		<?php
			
			$autoRunMode = $wpvr_options['autoRunMode'] === true ? '<span class="ok">' . __( 'ON', WPVR_LANG ) . '</span>' : '<span class="ko">' . __( 'OFF', WPVR_LANG ) . '</span>';
			
			$workHours = wpvr_get_working_hours_formatted();
			
			$cronUsed = '<span class="ok">' . __( 'Real Cron Service', WPVR_LANG ) . '</span>';
			
			if ( ! is_multisite() ) {
				$cron_data_file = WPVR_PATH . "assets/php/cron.txt";
			} else {
				$site_id        = get_current_blog_id();
				$cron_data_file = WPVR_PATH . "assets/php/cron_" . $site_id . ".txt";
			}
			
			$cron_data = wpvr_object_to_array( @wpvr_json_decode( @file_get_contents( $cron_data_file ) ) );
			
			if ( ! isset( $cron_data['last_exec'] ) ) {
				$cron_data['last_exec'] = '';
			}
			if ( ! isset( $cron_data['first_exec'] ) ) {
				$cron_data['first_exec'] = '';
			}
			if ( ! isset( $cron_data['total_exec'] ) ) {
				$cron_data['total_exec'] = '';
			}
			
			$date_a = new Datetime( $cron_data['last_exec'] );
			$date_b = new Datetime( 'now' );
			
			$human_delay = human_time_diff( $date_b->format( 'U' ), $date_a->format( 'U' ) );
			$in          = $date_b->diff( $date_a );
			$delay       = $in->days * 86400 + $in->h * 3600 + $in->i * 60 + $in->s;
			
			if ( $cron_data['last_exec'] == '' ) {
				$delay_msg = '<span class="ko">' . __( 'CRON never worked!', WPVR_LANG ) . '</span>';
			} elseif ( $delay <= 600 ) {
				$delay_msg = '<span class="ok">' . __( 'CRON is working!', WPVR_LANG ) . '</span>';
			} else {
				$delay_msg = '
                                                <span class="ko">' . __( 'CRON stopped', WPVR_LANG ) . '
                                                    ' . strtolower( sprintf( __( '%s ago' ), $human_delay  ) ). '
                                                </span>
                                        ';
			}
		
		?>
        <div class="wpvr_automation_status">

            <h4 class="wpvr_automation_title">
		        <?php echo strtoupper( __( "Automation status", WPVR_LANG )) ; ?>
            </h4>
            <li>
				<?php _e( 'Cron status', WPVR_LANG ); ?> : <?php echo $delay_msg; ?>
            </li>

            <li>
				<?php _e( 'AutoRun mode is', WPVR_LANG ); ?> : <?php echo $autoRunMode; ?>
            </li>
            <li>
				<?php _e( 'WP Video Robot is allowed to work', WPVR_LANG ); ?> :
                <span> <?php echo $workHours; ?></span>
            </li>
            <li>
				<?php _e( 'First Execution', WPVR_LANG ); ?> :
                <span>
                                            <?php echo $cron_data['first_exec'] != '' ? $cron_data['first_exec'] : ___( 'Never executed' ); ?>
                                        </span>
            </li>
            <li>
				<?php _e( 'Last Execution', WPVR_LANG ); ?> :
                <span>
                                            <?php echo $cron_data['last_exec'] != '' ? $cron_data['last_exec'] : ___( 'Never executed' ); ?>
                                        </span>
            </li>
			<?php if ( $cron_data['total_exec'] != '' && $cron_data['total_exec'] != 0 ) { ?>
                <li>
					<?php _e( 'Cron executed', WPVR_LANG ); ?>
                    <span> <?php echo wpvr_numberK( $cron_data['total_exec'] ); ?></span>
					<?php _e( 'times', WPVR_LANG ); ?>.
                </li>
			<?php } ?>


            <a
                    href="<?php echo admin_url( 'admin.php?page=wpvr-options&section=automation' ); ?>"
                    id="wpvr_configure_cron"
                    class="pull-left wpvr_button wpvr_small"
            >
                <i class="wpvr_button_icon fa fa-gears"></i>
				<?php echo __( 'CONFIGURE AUTOMATION', WPVR_LANG ); ?>
            </a>
            <a
                    href="<?php echo wpvr_get_cron_url( '?debug' ); ?>"
                    target="_blank"
                    id="wpvr_trigger_cron"
                    class="pull-left wpvr_button wpvr_small wpvr_trigger_cron wpvr_black_button"
            >
                <i class="wpvr_button_icon fa fa-paw"></i>
				<?php echo __( 'MANUALLY TRIGGER CRON', WPVR_LANG ); ?>

            </a>

            <div class="wpvr_clearfix"></div>
        </div>


    </div>

    <div class="inside pull-left wpvr_automation_panel_right">

        <div class="wpvr_activity_chart_wrap">
            <h4 class="wpvr_automation_title">
                <?php echo wpvr_strtoupper( __( "Latest AUTORUN Executions", WPVR_LANG )) ; ?>
            </h4>
            <div
                    class="wpvr_ajax_deferred_load"
                    data-action="wpvr_load_recent_activity_chart"
                    data-delay="1500"
            >
	            <?php echo wpvr_render_loading_message(); ?>
             
            </div>
            
        </div>

    </div>
    <div class="wpvr_clearfix"></div>
</div>


<?php
	$today = new DateTime();
	
	wpvr_async_draw_stress_graph_by_day( $today );
	$today->modify( '+1 day' );
	
	wpvr_async_draw_stress_graph_by_day( $today );
	$today->modify( '+1 day' );
	
	wpvr_async_draw_stress_graph_by_day( $today );
	$today->modify( '+1 day' );
	
	wpvr_async_draw_stress_graph_by_day( $today );
	$today->modify( '+1 day' );
	
	wpvr_async_draw_stress_graph_by_day( $today );
	$today->modify( '+1 day' );
	
	wpvr_async_draw_stress_graph_by_day( $today );
	$today->modify( '+1 day' );
	
	wpvr_async_draw_stress_graph_by_day( $today );
?>
</div>