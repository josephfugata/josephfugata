<?php
	
	add_action( 'wp_ajax_nopriv_delete_all_fillers', 'wpvr_delete_all_fillers_ajax_function' );
	add_action( 'wp_ajax_delete_all_fillers', 'wpvr_delete_all_fillers_ajax_function' );
	function wpvr_delete_all_fillers_ajax_function() {
		update_option( 'wpvr_fillers', '' );
		echo wpvr_get_json_response( 'done' );
		wpvr_die();
	}
	
	
	add_action( 'wp_ajax_nopriv_run_fillers', 'wpvr_run_fillers_ajax_function' );
	add_action( 'wp_ajax_run_fillers', 'wpvr_run_fillers_ajax_function' );
	function wpvr_run_fillers_ajax_function() {
		
		
		$db = wpvr_execute_dataFillers_on_existing_videos( false );
		
		// print_r( $db );
		
		$message = sprintf(
			__( '%s videos found and processed.', WPVR_LANG ) . '<br/>' .
			__( '%s video fields updated' , WPVR_LANG) .' ' . __('in %s seconds.', WPVR_LANG ) . '',
			'<strong>' . $db['videos'] . '</strong> ',
			'<strong>' . $db['count']['insert'] . '</strong> ',
			'<strong>' . round($db['exec_time'], 3) . '</strong> '
		);
		
		echo wpvr_get_json_response( $db , 1, $message );
		wpvr_die();
	}
	
	
	add_action( 'wp_ajax_nopriv_remove_filler', 'wpvr_remove_filler_ajax_function' );
	add_action( 'wp_ajax_remove_filler', 'wpvr_remove_filler_ajax_function' );
	function wpvr_remove_filler_ajax_function() {
		$wpvr_fillers = get_option( 'wpvr_fillers' );
		
		unset( $wpvr_fillers[ $_POST['k'] ] );
		update_option( 'wpvr_fillers', $wpvr_fillers );
		echo wpvr_get_json_response( 'done' );
		wpvr_die();
	}
	
	add_action( 'wp_ajax_nopriv_add_filler', 'wpvr_add_filler_ajax_function' );
	add_action( 'wp_ajax_add_filler', 'wpvr_add_filler_ajax_function' );
	function wpvr_add_filler_ajax_function() {
		$wpvr_fillers = wpvr_get_dataFillers();
		if ( $wpvr_fillers == '' ) {
			$wpvr_fillers = array();
		}
		if ( $_POST['filler_from'] == 'custom_data' ) {
			$wpvr_fillers[] = array(
				'from'        => 'custom_data',
				'from_custom' => trim( $_POST['filler_from_custom'] ),
				'to'          => trim( $_POST['filler_to'] ),
			);
		} else {
			$wpvr_fillers[] = array(
				'from' => trim( $_POST['filler_from'] ),
				'to'   => trim( $_POST['filler_to'] ),
			);
		}
		
		update_option( 'wpvr_fillers', $wpvr_fillers );
		echo wpvr_get_json_response( 'done' );
		wpvr_die();
	}
	
	add_action( 'wp_ajax_nopriv_show_fillers', 'wpvr_show_fillers_ajax_function' );
	add_action( 'wp_ajax_show_fillers', 'wpvr_show_fillers_ajax_function' );
	function wpvr_show_fillers_ajax_function() {
		global $wpvr_filler_data;
		ob_start();
		$wpvr_fillers = wpvr_get_dataFillers( true );
		// d( $wpvr_fillers );
		krsort( $wpvr_fillers );
		// d( $wpvr_fillers );
		if ( $wpvr_fillers == '' || count( $wpvr_fillers ) == 0 ) {
			?>
            <div class="wpvr_manage_noResults">
                <i class="fa fa-frown-o"></i><br/>
				<?php echo __( 'No filler found.', WPVR_LANG ); ?>
            </div>
			
			<?php
			
			$output = ob_get_contents();
			ob_end_clean();
			
			echo wpvr_get_json_response( $output, 0, '', 0 );
			
			return false;
		}
		?>
        <div class="wpvr_filler_actions">

            <button
                    type="button"
                    id="wpvr_filler_run_old"
                    class="wpvr_button pull-right wpvr_bulk_process_btn"
                    data-init_action="prepare_existing_videos"
                    data-progress_message="percentage"
                    data-single_action="partially_execute_fillers"
                    data-single_args='<?php echo htmlspecialchars( json_encode( array() ), ENT_QUOTES, 'UTF-8' ); ?>'
                    data-buffer="100"
                    data-confirm_title="<?php echo __( 'Update existing videos', WPVR_LANG ); ?>"
                    data-confirm_message="<?php echo __( 'Run fillers on existing videos ? This may take some time.', WPVR_LANG ); ?>"
                    data-finish_message="<?php echo __( '%s items processed.', WPVR_LANG ); ?>"
                    data-finish_title="<?php echo __( 'Work Completed!', WPVR_LANG ); ?>"
                    data-counter_callback="wpvr_render_dataFiller_execution_counters"
            >
                <i class="fa fa-bolt"></i>
		        <?php _e( 'RUN FILLERS ON EXISTING VIDEOS', WPVR_LANG ); ?>
            </button>

            <button
                    type="button"
                    id="wpvr_filler_delete_all"
                    class="wpvr_button wpvr_black_button pull-left"
                    is_demo="<?php echo WPVR_IS_DEMO ? 1 : 0; ?>"
            >
                <i class="fa fa-close"></i>
				<?php _e( 'DELETE ALL FILLERS', WPVR_LANG ); ?>
            </button>
            <div class="wpvr_clearfix"></div>
            <br/>
        </div>
		
        <div class="wpvr_filler_loading" style="display:none;">
           <?php echo wpvr_render_loading_message(); ?>
        </div>
		
		<?php
		$countFillers = 0;
		foreach ( (array) $wpvr_fillers as $k => $filler ) {
			$countFillers ++;
			if ( $filler['from'] == 'custom_data' ) {
				$from = '"' . $filler['from_custom'] . '"';
			} else {
				$from = $wpvr_filler_data[ $filler['from'] ];
			}
			?>
            <li class="filler" k="<?php echo $k; ?>">
                <div class="pull-left">
                    <span class="filler_source"><?php echo $from ?></span>
                    <i class="filler_arrow fa fa-long-arrow-right"></i>
                    <span class="filler_target"><?php echo $filler['to']; ?></span>
                </div>


                <button
                        type="button"
                        id=""
                        class="wpvr_button wpvr_red_button pull-right wpvr_filler_remove"
                        title="Remove this filler"
                        url="<?php echo WPVR_FILLERS_URL; ?>"
                        k="<?php echo $k; ?>"
                >
                    <i class="fa fa-remove"></i>
                </button>
                <div class="wpvr_clearfix"></div>
            </li>
			
			<?php
		}
		?>
        <div class="wpvr_clearfix"></div> <?php
		
		$output = ob_get_contents();
		ob_end_clean();
		echo wpvr_get_json_response( $output, 1, '', $countFillers );
		wpvr_die();
	}
	