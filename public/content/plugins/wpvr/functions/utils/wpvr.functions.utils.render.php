<?php
	
	function wpvr_render_source_actions( $post_id = '' ) {
		$o = array( 'metrics' => '', 'test' => '', 'run' => '', 'save' => '', 'trash' => '', 'clone' => '' );
		
		$o['save'] .= '<button id="wpvr_save_source_btn" class="wpvr_wide_button wpvr_source_actions_btn wpvr_button wpvr_black_button">';
		$o['save'] .= '<i class="wpvr_button_icon fa fa-save"></i>';
		$o['save'] .= '<span>' . __( 'Save Source', WPVR_LANG ) . '</span>';
		$o['save'] .= '</button>';
		
		
		if ( $post_id == '' ) {
			$o['test'] = '<div class="wpvr_no_actions">' . __( 'Start by saving your source', WPVR_LANG ) . '</div>';
			
			return $o;
		}
		
		$testLink  = admin_url( 'admin.php?page=wpvr&test_sources&ids=' . $post_id );
		$runLink   = admin_url( 'admin.php?page=wpvr&run_sources&ids=' . $post_id );
		$cloneLink = admin_url( 'admin.php?page=wpvr&clone_source=' . $post_id );
		$trashLink = wpvr_get_post_links( $post_id, 'trash' );
		
		$o['test'] .= '<button ready="1" url="' . $testLink . '" id="wpvr_metabox_test" class="wpvr_source_actions_btn wpvr_button wpvr_metabox_button test">';
		$o['test'] .= '<i class="wpvr_button_icon fa fa-eye"></i>';
		$o['test'] .= '<span>' . __( 'Test Source', WPVR_LANG ) . '</span>';
		$o['test'] .= '</button>';
		
		$o['run'] .= '<button ready="1" url="' . $runLink . '" id="wpvr_metabox_run" class="wpvr_source_actions_btn wpvr_button wpvr_metabox_button run">';
		$o['run'] .= '<i class="wpvr_button_icon fa fa-bolt"></i>';
		$o['run'] .= '<span>' . __( 'Run Source', WPVR_LANG ) . '</span>';
		$o['run'] .= '</button>';
		
		$o['clone'] .= '<button url="' . $cloneLink . '" id="wpvr_metabox_clone" class="wpvr_source_actions_btn wpvr_button wpvr_metabox_button clone">';
		$o['clone'] .= '<i class="wpvr_button_icon fa fa-copy"></i>';
		$o['clone'] .= '<span>' . __( 'Clone Source', WPVR_LANG ) . '</span>';
		$o['clone'] .= '</button>';
		
		
		$o['trash'] .= '<button url="' . $trashLink
		               . '" id="wpvr_trash_source_btn" class="wpvr_source_actions_btn wpvr_wide_button wpvr_button wpvr_red_button wpvr_metabox_button trash sameWindow">';
		$o['trash'] .= '<i class="wpvr_button_icon fa fa-trash-o"></i>';
		$o['trash'] .= '<span>' . __( 'Trash Source', WPVR_LANG ) . '</span>';
		$o['trash'] .= '</button>';
		
		return $o;
	}
	
	function render_source_insights( $insights, $class = '' ) {
		
		//Reorder insights
		$insights = wpvr_reorder_items( $insights );
		
		?>
		<?php foreach ( (array) $insights as $insight ) { ?>
			
			
			<div
				class="wpvr_source_insights_item pull-left <?php echo $class; ?>"
				title="<?php echo $insight['title']; ?>"
			>
				<span class="wpvr_source_insights_item_icon">
					<i class="fa <?php echo $insight['icon']; ?>"></i>
				</span>
				<span class="wpvr_source_insights_item_value">
					<?php echo $insight['value']; ?>
				</span>
			</div>
		
		
		<?php } ?>
		<div class="wpvr_clearfix"></div>
		
		<?php
	}
	
	function wpvr_async_draw_stress_graph_by_day( $date, $hex_color = false ) {
		
		if ( $hex_color === false ) {
			$hex_color = wpvr_generate_colors( false );
		}
		
		$chart_id = 'wpvr_chart_stress_graph_' . rand( 0, 1000000 );
		
		$today = new DateTime();
		
		$is_today = $today->format( 'l' ) === $date->format( 'l' ) ? '(' . __( 'Today', WPVR_LANG ) . ')' : '';
		// d( $is_today );
		?>
		<!-- DAY STRESS GRAPH -->
		<div
			class="wpvr_async_graph postbox wide"
			day="<?php echo strtolower( $date->format( 'l' ) ); ?>"
			daylabel="<?php echo( $date->format( 'Y-m-d' ) ); ?>"
			daytime="<?php echo( $date->format( 'c' ) ); ?>"
			hex_color="<?php echo $hex_color; ?>"
			chart_id="<?php echo $chart_id; ?>"
			chart_n="<?php echo $date->format( 'N' ); ?>"
		>
			<h3 class="hndle">
					<span>
						<?php //echo ucfirst( $date->format( 'l' ) ) . ' ' . __( 'Stress Forecast', WPVR_LANG ) . ' ' . $is_today; ?>
						
						<?php echo sprintf(
							__( 'Stress Forecast for %s', WPVR_LANG ),
							__( ucfirst( $date->format( 'l' ) ), WPVR_LANG )
						); ?>
						<?php echo $is_today; ?>
					</span>
				
				<span class="forecast_timezone">
                        <?php echo wpvr_get_timezone_name( wpvr_get_timezone() ); ?>
                    </span>
			
			</h3>
			
			<div class=" inside">
				<div class="wpvr_graph_wrapper" style="">
					<?php echo wpvr_render_loading_message(); ?>
				</div>
			</div>
		</div>
		<?php
	}
	
	function wpvr_async_get_schedule_stress( $date = '' ) {
		return apply_filters( 'wpvr_extend_schedule_stress', false, $date );
	}
	
	function wpvr_get_schedule_stress( $day = '' ) {
		global $wpvr_options, $wpvr_stress, $wpvr_days;
		//new dBug( $wpvr_days );
		
		if ( $day == '' ) {
			$day_name = $wpvr_days[ strtolower( date( 'N' ) ) ];
		} else {
			$day_num  = strtolower( date( 'N', strtotime( $day ) ) );
			$day_name = $wpvr_days[ $day_num ];
		}
		
		//new dBug( $day_name );
		
		global $wpvr_hours, $wpvr_hours_us, $wpvr_options;
		$wpvr_hours_formatted = $wpvr_options['timeFormat'] == 'standard' ? $wpvr_hours : $wpvr_hours_us;
		
		//_d( $wpvr_hours_formatted );
		
		$stress_per_hour = array();
		foreach ( (array) $wpvr_hours_formatted as $h => $hour_formatted ) {
			$stress_per_hour[ $h ] = array(
				'max'     => $wpvr_stress['max'],
				'stress'  => 0,
				'count'   => 0,
				'wanted'  => 0,
				'hour'    => $hour_formatted,
				'sources' => array(),
			);
		}
		
		
		$sources = wpvr_get_sources( array(
			'status'       => 'on',
			'bypass_cache' => 'true',
		) );
		$sources = wpvr_multiplicate_sources( $sources );
		foreach ( (array) $sources as $source ) {
			//new dBug($source);
			
			
			$wantedVideos  = ( $source->wantedVideosBool == 'default' ) ? $wpvr_options['wantedVideos'] : $source->wantedVideos;
			$getTags       = ( $source->getVideoTags == 'default' ) ? $wpvr_options['getTags'] : ( ( $source->getVideoTags == 'on' ) ? true : false );
			$getStats      = ( $source->getVideoStats == 'default' ) ? $wpvr_options['getStats'] : ( ( $source->getVideoStats == 'on' ) ? true : false );
			$onlyNewVideos = ( $source->onlyNewVideos == 'default' ) ? $wpvr_options['onlyNewVideos'] : ( ( $source->onlyNewVideos == 'on' ) ? true : false );
			
			
			$source_stress = 0;
			if ( $getTags ) {
				$source_stress += $wantedVideos * $wpvr_stress['getTags'];
			}
			if ( $getStats ) {
				$source_stress += $wantedVideos * $wpvr_stress['getStats'];
			}
			if ( $onlyNewVideos ) {
				$source_stress += $wantedVideos * $wpvr_stress['onlyNewVideos'];
			}
			$source_stress = $source_stress * $wpvr_stress['wantedVideos'] * $wpvr_stress['base'];
			
			if ( $source->schedule == 'hourly' ) {
				foreach ( (array) $stress_per_hour as $hour => $value ) {
					$myhour    = explode( 'H', $hour );
					$isWorking = wpvr_is_working_hour( $myhour[0] );
					
					if ( $isWorking ) {
						$stress_per_hour[ $hour ]['stress'] += $source_stress;
						$stress_per_hour[ $hour ]['count'] ++;
						$stress_per_hour[ $hour ]['wanted']    += $wantedVideos;
						$stress_per_hour[ $hour ]['sources'][] = $source->id;
					}
				}
			} elseif ( $source->schedule == 'daily' ) {
				$myhour    = explode( 'H', $source->scheduleTime );
				$isWorking = wpvr_is_working_hour( $myhour[0] );
				
				if ( $isWorking ) {
					$stress_per_hour[ $source->scheduleTime ]['stress'] += $source_stress;
					$stress_per_hour[ $source->scheduleTime ]['count'] ++;
					$stress_per_hour[ $source->scheduleTime ]['wanted']    += $wantedVideos;
					$stress_per_hour[ $source->scheduleTime ]['sources'][] = $source->id;
				}
			} elseif ( $source->schedule == 'weekly' ) {
				// _d( $day_name );
				// _d( $source->scheduleDay );
				
				if ( strtolower( $day_name ) == $source->scheduleDay ) {
					
					$myhour    = explode( 'H', $source->scheduleTime );
					$isWorking = wpvr_is_working_hour( $myhour[0] );
					
					// _d( $source->name );
					// _d( $wantedVideos  );
					// d( $isWorking );
					// echo '<hr/>';
					
					if ( $isWorking ) {
						$stress_per_hour[ $source->scheduleTime ]['stress'] += $source_stress;
						$stress_per_hour[ $source->scheduleTime ]['count'] ++;
						$stress_per_hour[ $source->scheduleTime ]['wanted']    += $wantedVideos;
						$stress_per_hour[ $source->scheduleTime ]['sources'][] = $source->id;
					}
				}
			}
		}
		
		return ( $stress_per_hour );
	}
	
	function wpvr_render_add_unwanted_button( $post_id ) {
		global $wpvr_unwanted_ids, $wpvr_unwanted;
		$video_id      = get_post_meta( $post_id, 'wpvr_video_id', true );
		$video_service = get_post_meta( $post_id, 'wpvr_video_service', true );
		//d( $wpvr_unwanted_ids );
		//d( $wpvr_unwanted_ids[$video_service] );
		if ( $video_id == '' || $post_id == '' ) {
			return '';
		}
		if ( isset( $wpvr_unwanted_ids[ $video_service ][ $video_id ] ) ) {
			$action = 'remove';
			$icon   = 'fa-undo';
			$label  = __( 'Remove from Unwanted', WPVR_LANG );
			$class  = "wpvr_black_button";
		} else {
			$action = 'add';
			$icon   = 'fa-ban';
			$label  = __( 'Add to Unwanted', WPVR_LANG );
			$class  = "wpvr_red_button";
			
		}
		
		$unwanted_button
			= '
                <button
					url = "' . WPVR_ACTIONS_URL . '"
					class=" ' . $class . ' wpvr_button wpvr_full_width wpvr_single_unwanted wpvr_source_actions_btn"
					post_id="' . $post_id . '"
					action="' . $action . '"
				>
					<i class="fa ' . $icon . '" iclass="' . $icon . '"></i>
					<span>' . $label . '</span>
				</button>
			';
		
		return $unwanted_button;
	}
	
	function wpvr_render_copy_button( $target ) {
		
		?>
		<button
			class="wpvr_copy_btn wpvr_button wpvr_black_button pull-right"
			data-clipboard-target="#<?php echo $target; ?>"
			done=""
		>
			<i class="wpvr_green fa fa-check"></i>
			<i class="wpvr_black fa fa-copy"></i>
			<span class="wpvr_black"><?php echo __( 'COPY', WPVR_LANG ); ?></span>
			<span class="wpvr_green"><?php echo __( 'COPIED !', WPVR_LANG ); ?></span>
		</button>
		<?php
		
		
	}