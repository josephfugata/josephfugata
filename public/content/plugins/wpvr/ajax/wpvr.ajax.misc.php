<?php
	
	add_action( 'wp_ajax_nopriv_wpvr_render_overview', 'wpvr_render_async_overview' );
	add_action( 'wp_ajax_wpvr_render_overview', 'wpvr_render_async_overview' );
	function wpvr_render_async_overview() {
		
		
		ob_start();
		include( WPVR_PATH . '/includes/wpvr.dashboard.overview.php' );
		$overview = ob_get_contents();
		ob_end_clean();
		$json = array(
			'status' => 1,
			'html'   => ( $overview ),
		);
		echo wpvr_get_json_response( $json, 1 );
		wpvr_die();
	}
	
	add_action( 'wp_ajax_nopriv_wpvr_load_recent_activity_chart', 'wpvr_load_recent_activity_chart_callback' );
	add_action( 'wp_ajax_wpvr_load_recent_activity_chart', 'wpvr_load_recent_activity_chart_callback' );
	function wpvr_load_recent_activity_chart_callback() {
		
		
		ob_start();
		echo wpvr_render_activity_chart( wpvr_get_recent_activity_data() );
		$overview = ob_get_contents();
		ob_end_clean();
		$json = array(
			'status' => 1,
			'html'   => ( $overview ),
		);
		echo wpvr_get_json_response( $json, 1 );
		wpvr_die();
	}
	
	
	//get_video_wizzard_form
	//@Deprecated
	// add_action( 'wp_ajax_nopriv_get_video_wizzard_form' , 'get_video_wizzard_form_ajax_function' );
	// add_action( 'wp_ajax_get_video_wizzard_form' , 'get_video_wizzard_form_ajax_function' );
	function get_video_wizzard_form_ajax_function() {
		
		$wizzard_form = wpvr_render_wizzard_form();
		echo WPVR_JS . wpvr_json_encode( $wizzard_form ) . WPVR_JS;
		
		wpvr_die();
	}
	
	add_action( 'wp_ajax_nopriv_wpvr_source_toggle_state', 'wpvr_source_toggle_state_ajax_function' );
	add_action( 'wp_ajax_wpvr_source_toggle_state', 'wpvr_source_toggle_state_ajax_function' );
	function wpvr_source_toggle_state_ajax_function() {
		if ( isset( $_POST['ids'] ) ) {
			$source_ids = explode( ',', $_POST['ids'] );
		} else {
			return false;
		}
		if ( isset( $_POST['status'] ) ) {
			$source_status = $_POST['status'];
		} else {
			return false;
		}
		foreach ( (array) $source_ids as $id ) {
			update_post_meta( $id, 'wpvr_source_status', $source_status );
		}
		
		echo wpvr_get_json_response( null, 1, 'Done' );
		
		wpvr_die();
	}
	
	add_action( 'wp_ajax_nopriv_wpvr_system_info', 'wpvr_system_info_ajax_function' );
	add_action( 'wp_ajax_wpvr_system_info', 'wpvr_system_info_ajax_function' );
	function wpvr_system_info_ajax_function() {
		
		ob_start();
		
		$info      = wpvr_get_system_info();
		$sys_infos = $info['sys'];
		
		if ( isset( $_POST['do_export'] ) ) {
			$token = bin2hex( openssl_random_pseudo_bytes( 16 ) );
			$file  = "tmp_export_{$token}__system_info__";
			file_put_contents( WPVR_TMP_PATH . $file, wpvr_render_system_info( $info ) );
			echo wpvr_get_json_response( site_url( '/wpvr_export/' ) . $file, 1, 'export file generated.' );
			wpvr_die();
		}
		$left_columns = $right_columns = '';
		$i            = 0;
		foreach ( (array) $sys_infos as $sys ) {
			$i ++;
			
			if ( ! is_bool( $sys['value'] ) ) {
				$value = $sys['value'];
			} elseif ( $sys['value'] ) {
				$value = "TRUE";
			} else {
				$value = "FALSE";
			}
			
			if ( $sys['status'] == 'good' ) {
				$icon = 'check';
			} elseif ( $sys['status'] == 'bad' ) {
				$icon = 'ban';
			} else {
				$icon = 'cog';
			}
			
			$line
				= '
                <div class="wpvr_syst_info ' . $sys['status'] . '">
                    <i class="fa fa-' . $icon . '"></i>
                    <strong>' . $sys['label'] . '</strong> : ' . $sys['value'] . '
                </div>
            ';
			
			if ( $i <= count( $sys_infos ) / 2 ) {
				$left_columns .= $line;
			} else {
				$right_columns .= $line;
			}
			
		}
		
		?>
        <table class="wpvr_sys_info_table">
            <tr>
                <td><?php echo $left_columns; ?></td>
                <td><?php echo $right_columns; ?></td>
            </tr>
        </table>
		<?php
		
		$output = ob_get_contents();
		ob_end_clean();
		echo wpvr_get_json_response( $output, 1 );
		
		wpvr_die();
	}
	
	add_action( 'wp_ajax_nopriv_wpvr_reset_options', 'wpvr_reset_options_ajax_function' );
	add_action( 'wp_ajax_wpvr_reset_options', 'wpvr_reset_options_ajax_function' );
	function wpvr_reset_options_ajax_function() {
		global $wpvr_default_options, $wpvr_default_tokens;
		update_option( 'wpvr_options', $wpvr_default_options );
		update_option( 'wpvr_tokens', $wpvr_default_tokens );
		echo wpvr_get_json_response( null, 1, 'Options Reset.' );
		wpvr_die();
	}
	
	add_action( 'wp_ajax_nopriv_wpvr_export_options', 'wpvr_export_options_ajax_function' );
	add_action( 'wp_ajax_wpvr_export_options', 'wpvr_export_options_ajax_function' );
	function wpvr_export_options_ajax_function() {
		global $wpvr_options, $wpvr_tokens;
		
		$wpvr_options['tokens'] = $wpvr_tokens;
		
		//wpvr_remove_tmp_files();
		$token        = bin2hex( openssl_random_pseudo_bytes( 16 ) );
		$json_options = array(
			'data'    => $wpvr_options,
			'version' => WPVR_VERSION,
			'type'    => 'options',
		);
		$file         = "tmp_export_" . $token . '__options__';
		file_put_contents( WPVR_TMP_PATH . $file, wpvr_json_encode( $json_options ) );
		$export_url = site_url( '/wpvr_export/' ) . $file;
		
		
		?>
        <iframe id="wpvr_iframe" src="" style="display:none; visibility:hidden;"></iframe>
        <script>
            jQuery('#wpvr_iframe').attr('src', "<?php echo $export_url; ?>");
        </script>
		<?php
		wpvr_die();
	}
	
	add_action( 'wp_ajax_nopriv_wpvr_clear_token', 'wpvr_clear_token_ajax_function' );
	add_action( 'wp_ajax_wpvr_clear_token', 'wpvr_clear_token_ajax_function' );
	function wpvr_clear_token_ajax_function() {
		global $wpvr_tokens;
		$wpvr_tokens[ $_POST['service'] ] = array(
			'access_token'  => '',
			'refresh_token' => '',
		);
		update_option( 'wpvr_tokens', $wpvr_tokens );
		echo wpvr_get_json_response( null, 1, 'Token Reset.' );
		wpvr_die();
	}
	
	add_action( 'wp_ajax_nopriv_wpvr_update_wakeUpHours', 'wpvr_update_wakeUpHours_ajax_function' );
	add_action( 'wp_ajax_wpvr_update_wakeUpHours', 'wpvr_update_wakeUpHours_ajax_function' );
	function wpvr_update_wakeUpHours_ajax_function() {
		?>
		<?php $workingHours = wpvr_make_interval( $_POST['start'], $_POST['end'] ); ?>
		<?php foreach ( (array) $workingHours as $wh => $state ) { ?>
			<?php if ( $state === true ) { ?>
                <div title="AUTORUN ON" class="wpvr_wh is_working"><?php echo $wh . 'H'; ?></div>
			<?php } else { ?>
                <div title="AUTORUN OFF" class="wpvr_wh"><?php echo $wh . 'H'; ?></div>
			<?php } ?>
		<?php } ?>
        <div class="wpvr_clearfix"></div>
		<?php
		
		//echo wpvr_get_json_response( null , 1 , 'Token Reset.' );
		wpvr_die();
	}
	
	add_action( 'wp_ajax_nopriv_wpvr_render_async_stress_graph', 'wpvr_render_async_stress_graph_ajax_function' );
	add_action( 'wp_ajax_wpvr_render_async_stress_graph', 'wpvr_render_async_stress_graph_ajax_function' );
	function wpvr_render_async_stress_graph_ajax_function() {
		
		$date = new Datetime( $_POST['datetime'] );
		
		$stress_data = false;
		//Extending Stress Data
		$stress_data = apply_filters( 'wpvr_extend_schedule_stress', $stress_data, $date );
		
		if ( $stress_data === false ) {
			$stress_data = wpvr_get_schedule_stress( $_POST['day'] );
		}
		
		$json = array(
			'html' => wpvr_render_stress_chart( $stress_data ),
		);
		echo wpvr_get_json_response( $json, 1 );
		wpvr_die();
	}
	
	
	add_action( 'wp_ajax_nopriv_test_add_single_video', 'wpvr_test_add_single_video_ajax_function' );
	add_action( 'wp_ajax_test_add_single_video', 'wpvr_test_add_single_video_ajax_function' );
	function wpvr_test_add_single_video_ajax_function() {
		
		$video_id      = $_POST['video_id'];
		$wpvr_imported = get_option( 'wpvr_imported' );
		
		if ( isset( $_POST['is_deferred'] ) && $_POST['is_deferred'] == '1' ) {
			$wpvr_deferred = get_option( 'wpvr_deferred' );
			foreach ( (array) $wpvr_deferred as $k => $deferred_video ) {
				if ( $deferred_video['id'] == $video_id ) {
					$video                = $deferred_video;
					$video['origin']      = 'by MANUAL DEFER';
					$video['owner']       = get_current_user_id();
					$video['is_deferred'] = true;
					unset( $wpvr_deferred[ $k ] );
					break;
				}
			}
			update_option( 'wpvr_deferred', $wpvr_deferred );
		} else {
			if ( ! isset( $_POST['session'] ) || $_POST['session'] == '' ) {
				//echo "LOST TESTING TMP SESSION.";
				echo wpvr_get_json_response( null, 0, 'LOST TESTING TMP SESSION.' );
				
				return false;
			} else {
				$session = $_POST['session'];
			}
			
			
			if ( ! isset( $_SESSION['wpvr_tmp_results'][ $session ][ $video_id ] ) ) {
				echo wpvr_get_json_response( null, 0, 'NO VIDEO THERE' );
				
				return false;
			} else {
				$video = $_SESSION['wpvr_tmp_results'][ $session ][ $video_id ];
				unset( $_SESSION['wpvr_tmp_results'][ $session ][ $video_id ] );
				$video['origin'] = "by TEST";
				$video['owner']  = get_current_user_id();
			}
		}
		
		$post_id = wpvr_add_video( $video, $wpvr_imported, $allowDuplicates = true );
		if ( $post_id != false ) {
			/* Added with no message */
			echo wpvr_get_json_response( array(
				'title'     => $video['title'],
				'post_id'   => $post_id,
				'edit_link' => get_edit_post_link( $post_id ),
				'view_link' => get_permalink( $post_id ),
			) );
		} else {
			echo wpvr_get_json_response( $video, - 1, 'VIDEO NOT ADDED' );
		}
		
		wpvr_die();
	}
	
	add_action( 'wp_ajax_nopriv_use_helper', 'wpvr_use_helper_ajax_function' );
	add_action( 'wp_ajax_use_helper', 'wpvr_use_helper_ajax_function' );
	function wpvr_use_helper_ajax_function() {
		global $wpvr_vs;
		
		$helper_result = false;
		$service       = $_POST['service'];
		
		if ( ! isset( $wpvr_vs[ $service ] ) ) {
			echo wpvr_get_json_response( null, 0, 'Helper ERROR' );
			
			return false;
		}
		
		
		//_d( $_POST );
		if ( $_POST['helper_type'] == 'channel' ) {
			if ( isset( $wpvr_vs[ $service ]['get_channel_id'] ) ) {
				$helper_result = $wpvr_vs[ $service ]['get_channel_id']( $_POST['helper_value'] );
			}
		} elseif ( $_POST['helper_type'] == 'searchByChannel' ) {
			if ( isset( $wpvr_vs[ $service ]['get_channel_id'] ) ) {
				$helper_result = $wpvr_vs[ $service ]['get_channel_id']( $_POST['helper_value'] );
			}
		} elseif ( $_POST['helper_type'] == 'page' ) {
			if ( isset( $wpvr_vs[ $service ]['get_page_id'] ) ) {
				$helper_result = $wpvr_vs[ $service ]['get_page_id']( $_POST['helper_value'] );
			}
		} elseif ( $_POST['helper_type'] == 'user' ) {
			if ( isset( $wpvr_vs[ $service ]['get_user_id'] ) ) {
				$helper_result = $wpvr_vs[ $service ]['get_user_id']( $_POST['helper_value'] );
			}
		}
		// _d( $helper_result );
		
		if ( $helper_result === false ) {
			echo wpvr_get_json_response( null, 0, 'Helper Action Function not defined.' );
		} elseif ( $helper_result['status'] === false ) {
			echo wpvr_get_json_response( null, 0, $helper_result['msg'] );
		} else {
			echo wpvr_get_json_response( $helper_result['data'], 1, "Helper Result Returned." );
		}
		wpvr_die();
	}
	
	add_action( 'wp_ajax_nopriv_reset_activation', 'wpvr_reset_activation_ajax_function' );
	add_action( 'wp_ajax_reset_activation', 'wpvr_reset_activation_ajax_function' );
	function wpvr_reset_activation_ajax_function() {
		wpvr_set_activation( 'wpvr', array() );
		echo wpvr_get_json_response( null, 1, 'Reset completed.' );
		wpvr_die();
	}
	
	add_action( 'wp_ajax_nopriv_cancel_activation', 'wpvr_cancel_activation_ajax_function' );
	add_action( 'wp_ajax_cancel_activation', 'wpvr_cancel_activation_ajax_function' );
	function wpvr_cancel_activation_ajax_function() {
		$act = wpvr_get_activation( 'wpvr' );
		if ( $act === false ) {
			echo wpvr_get_json_response( null, 0, 'No activation found.' );
		}
		$api = wpvr_capi_cancel_activation( $act['act_code'] );
		wpvr_set_activation( 'wpvr', array() );
		echo wpvr_get_json_response( null, 1, 'Reset completed.' );
		wpvr_die();
	}
	
	add_action( 'wp_ajax_nopriv_activate_copy', 'wpvr_activate_copy_ajax_function' );
	add_action( 'wp_ajax_activate_copy', 'wpvr_activate_copy_ajax_function' );
	function wpvr_activate_copy_ajax_function() {
		
		global $wpvr_remote_ip, $WPVR_SERVER;
		
		$code  = $_POST['code'];
		$email = $_POST['email'];
		$id    = $_POST['id'];
		
		$act = wpvr_get_activation( 'wpvr' );
		//_d( $act );
		$api = wpvr_capi_activate(
			'wpvr',
			$_POST['code'],
			$_POST['is_envato'] == 1 ? 'envato' : 'local',
			$_POST['email'],
			$new_domain = $act['act_domain'],
			$new_url = $act['act_url'],
			$new_ip = $act['act_ip'],
			$new_cinfos = '',
			$new_version = $act['act_version']
		);
		//_d( $api );return false;
		
		
		if ( $api['status'] == 0 || $api['status'] == '2' ) {
			echo wpvr_get_json_response( null, 0, '' . $api['msg'] );
		} else {
			$now     = new Datetime();
			$new_act = array(
				'act_status'  => 1,
				'act_product' => 'wpvr',
				'act_id'      => $api['data'],
				'act_email'   => $_POST['email'],
				'act_code'    => $_POST['code'],
				'act_date'    => $now->format( 'Y-m-d H:i:s' ),
				'buy_date'    => '',
				'buy_user'    => '',
				'buy_license' => '',
				'act_addons'  => array(),
				'act_url'     => WPVR_SITE_URL,
				'act_domain'  => $WPVR_SERVER['HTTP_HOST'],
				'act_version' => WPVR_VERSION,
				'act_cinfos'  => '',
				'act_ip'      => $wpvr_remote_ip,
			);
			wpvr_set_activation( 'wpvr', $new_act );
			echo wpvr_get_json_response( $api['data'], 1, 'Thanks for purchasing WP Video Robot :)' );
		}
		wpvr_die();
	}
	
	add_action( 'wp_ajax_nopriv_import_sample_sources', 'wpvr_import_sample_sources_ajax_function' );
	add_action( 'wp_ajax_import_sample_sources', 'wpvr_import_sample_sources_ajax_function' );
	function wpvr_import_sample_sources_ajax_function() {
		global $wpvr_vs;
		$done = array(
			'total' => 0,
			'count' => array(),
		);
		foreach ( (array) $wpvr_vs as $vs ) {
			$done['count'][ $vs['id'] ] = 0;
			$json_file                  = WPVR_PATH . 'assets/json/' . $vs['id'] . '.json';
			$json                       = (array) wpvr_json_decode( file_get_contents( $json_file ) );
			if ( ! isset( $json['version'] ) || ! isset( $json['data'] ) || ! isset( $json['type'] ) || $json['type'] != 'sources' ) {
				$done['detail'][ $vs['id'] ] = 'Invalid JSON file.';
				continue;
			}
			
			$sources       = $json['data'];
			$done['total'] += count( $sources );
			foreach ( (array) $sources as $source ) {
				$s = wpvr_import_source( $source, true );
				$done['count'][ $vs['id'] ] ++;
			}
			//break;
		}
		echo wpvr_get_json_response(
			$done['count'],
			1,
			$done['total'] . ' ' . __( 'sample sources added', WPVR_LANG ) . '.'
		);
		wpvr_die();
	}
	
	add_action( 'wp_ajax_nopriv_save_addon_options', 'wpvr_save_addon_options_ajax_function' );
	add_action( 'wp_ajax_save_addon_options', 'wpvr_save_addon_options_ajax_function' );
	function wpvr_save_addon_options_ajax_function() {
		global $wpvr_addons;
		if ( ! isset( $_POST['id'] ) || ! isset( $wpvr_addons[ $_POST['id'] ] ) ) {
			echo wpvr_get_json_response( null, 0, 'Undefined addon ID. Exit!' );
			wpvr_die();
		}
		$addon_id = $_POST['id'];
		$ca       = $wpvr_addons[ $addon_id ];
		$tab      = isset( $_POST['tab'] ) ? $_POST['tab'] : '_main';
		$slot     = wpvr_get_addon_options( $addon_id );
		
		
		$old_options = $slot;
		$new_options = $slot;
		
		foreach ( (array) $ca['options'] as $name => $option ) {
			
			if ( ! isset( $option['tab'] ) ) {
				$option['tab'] = '_main';
			}
			if ( $tab == $option['tab'] ) {
				if ( $option['type'] == 'multiselect' ) {
					
					if ( ! isset( $_POST[ $name ] ) || $_POST[ $name ] == array() ) {
						unset( $new_options[ $name ] );
					} else {
						$new_options[ $name ] = json_decode( stripslashes( $_POST[ $name ] ) );
					}
				} elseif ( $option['type'] == 'switch' ) {
					if ( isset( $option['tab'] ) && $option['tab'] != '' && isset( $_POST[ $name ] ) ) {
						$new_options[ $name ] = $_POST[ $name ] == '@true' ? true : false;
					}
				} else {
					if ( isset( $_POST[ $name ] ) ) {
						$new_options[ $name ] = $_POST[ $name ];
					}
					// $new_options[ $name ] = $slot[ $name ] ;
				}
			}
		}
		
		
		do_action( 'wpvr_event_addon_options_saved', $addon_id, $new_options, $old_options );
		$out = apply_filters( 'wpvr_event_addon_options_saved', array(), $addon_id, $new_options, $old_options );
		
		update_option( $ca['infos']['slot_name'], $new_options );
		echo wpvr_get_json_response( $out, 1, 'Addon Options Saved.' );
		
		wpvr_die();
	}
	
	add_action( 'wp_ajax_nopriv_reset_addon_options', 'wpvr_reset_addon_options_ajax_function' );
	add_action( 'wp_ajax_reset_addon_options', 'wpvr_reset_addon_options_ajax_function' );
	function wpvr_reset_addon_options_ajax_function() {
		global $wpvr_addons;
		if ( ! isset( $_POST['id'] ) || ! isset( $wpvr_addons[ $_POST['id'] ] ) ) {
			echo wpvr_get_json_response( null, 0, 'Undefined addon ID. Exit!' );
			wpvr_die();
		}
		$ca = $wpvr_addons[ $_POST['id'] ];
		update_option( $ca['infos']['slot_name'], $ca['defaults'] );
		wpvr_die();
	}
	
	add_action( 'wp_ajax_nopriv_test_add_unwanted_single_video', 'wpvr_test_add_unwanted_single_video_ajax_function' );
	add_action( 'wp_ajax_test_add_unwanted_single_video', 'wpvr_test_add_unwanted_single_video_ajax_function' );
	function wpvr_test_add_unwanted_single_video_ajax_function() {
		$video_id = $_POST['video_id'];
		
		$wpvr_imported = get_option( 'wpvr_imported' );
		
		if ( isset( $_GET['is_deferred'] ) ) {
			$wpvr_deferred = get_option( 'wpvr_deferred' );
			foreach ( (array) $wpvr_deferred as $k => $deferred_video ) {
				if ( $deferred_video['id'] == $video_id ) {
					$video           = $deferred_video;
					$video['origin'] = 'by MANUAL DEFER';
					$video['owner']  = get_current_user_id();
					unset( $wpvr_deferred[ $k ] );
					break;
				}
			}
			update_option( 'wpvr_deferred', $wpvr_deferred );
		} else {
			if ( ! isset( $_POST['session'] ) || $_POST['session'] == '' ) {
				echo "LOST TESTING TMP SESSION.";
				
				return false;
			} else {
				$session = $_POST['session'];
			}
			if ( ! isset( $_SESSION['wpvr_tmp_results'][ $session ][ $video_id ] ) ) {
				echo "NO VIDEO THERE !";
				
				return false;
			} else {
				$video = $_SESSION['wpvr_tmp_results'][ $session ][ $video_id ];
				unset( $_SESSION['wpvr_tmp_results'][ $session ][ $video_id ] );
				$video['origin'] = "by TEST";
				$video['owner']  = get_current_user_id();
			}
		}
		
		if ( isset( $_POST['scope'] ) && $_POST['scope'] == 'source' ) {
			wpvr_add_video_unwanted( $video, $_POST['source_id'] );
		} else {
			wpvr_add_video_unwanted( $video, false );
		}
		
		// global $wpvr_unwanted, $wpvr_unwanted_ids;
		//
		// if ( ! isset( $wpvr_unwanted_ids[ $video['service'] ][ $video['id'] ] ) ) {
		// 	$wpvr_unwanted[]                                        = $video;
		// 	$wpvr_unwanted_ids[ $video['service'] ][ $video['id'] ] = 'unwanted';
		// }
		//
		//
		// update_option( 'wpvr_unwanted', $wpvr_unwanted );
		// update_option( 'wpvr_unwanted_ids', $wpvr_unwanted_ids );
		echo wpvr_get_json_response( $video );
		wpvr_die();
	}
	
	
	add_action( 'wp_ajax_nopriv_test_remove_deferred_videos', 'wpvr_test_remove_deferred_videos_ajax_function' );
	add_action( 'wp_ajax_test_remove_deferred_videos', 'wpvr_test_remove_deferred_videos_ajax_function' );
	function wpvr_test_remove_deferred_videos_ajax_function() {
		if ( ! isset( $_POST['videos'] ) ) {
			echo "NOTHING SELECTED";
			
			return false;
		}
		$count             = 0;
		$wpvr_deferred     = get_option( 'wpvr_deferred' );
		$wpvr_deferred_ids = get_option( 'wpvr_deferred_ids' );
		foreach ( (array) $wpvr_deferred as $k => $vid ) {
			if ( in_array( $vid['id'], $_POST['videos'] ) ) {
				$count ++;
				unset( $wpvr_deferred[ $k ] );
				unset( $wpvr_deferred_ids[ $vid['service'] ][ $vid['id'] ] );
			}
		}
		update_option( 'wpvr_deferred', $wpvr_deferred );
		update_option( 'wpvr_deferred_ids', $wpvr_deferred_ids );
		
		$imported      = wpvr_update_imported_videos();
		$wpvr_imported = get_option( 'wpvr_imported' );
		
		echo wpvr_get_json_response(
			$count,
			1,
			$count . '/' . count( $_POST['videos'] ) . ' ' . __( 'videos removed from deferred.', WPVR_LANG )
		);
		wpvr_die();
	}
	
	add_action( 'wp_ajax_nopriv_test_remove_unwanted_videos', 'wpvr_test_remove_unwanted_videos_ajax_function' );
	add_action( 'wp_ajax_test_remove_unwanted_videos', 'wpvr_test_remove_unwanted_videos_ajax_function' );
	function wpvr_test_remove_unwanted_videos_ajax_function() {
		
		
		if ( ! isset( $_POST['videos'] ) ) {
			return false;
		}
		
		$global_scope = $source_scope = array();
		foreach ( (array) $_POST['videos'] as $video ) {
			
			if ( $video['scope'] == 'global' ) {
				$global_scope[ $video['video_id'] ] = $video;
			} else {
				if ( ! isset( $source_scope[ $video['source_id'] ] ) ) {
					$source_scope[ $video['source_id'] ] = array();
				}
				$source_scope[ $video['source_id'] ][ $video['video_id'] ] = $video;
			}
			
			
		}
		
		
		$global_count = wpvr_remove_global_unwanted_video( $global_scope );
		$source_count = wpvr_remove_source_unwanted_video( $source_scope );
		
		// _d( $global_count );
		
		echo wpvr_get_json_response(
			$source_count + $global_count,
			1,
			$source_count + $global_count . '/' . count( $_POST['videos'] ) . ' ' . __( 'videos removed from unwanted.', WPVR_LANG )
		);
		wpvr_die();
	}
	
	
	add_action( 'wp_ajax_nopriv_reset_addon_licenses', 'wpvr_reset_addon_licenses_ajax_function' );
	add_action( 'wp_ajax_reset_addon_licenses', 'wpvr_reset_addon_licenses_ajax_function' );
	function wpvr_reset_addon_licenses_ajax_function() {
		$wpvr_act = get_option( 'wpvr_activations' );
		foreach ( (array) $wpvr_act as $id => $act ) {
			if ( $id != 'wpvr' ) {
				unset( $wpvr_act[ $id ] );
			}
		}
		update_option( 'wpvr_activations', $wpvr_act );
		echo wpvr_get_json_response( null, 1, __( 'All addons licenses have been reset.', WPVR_LANG ) );
		wpvr_die();
	}
	
	
	add_action( 'wp_ajax_nopriv_reset_single_addon_license', 'wpvr_reset_single_addon_license_ajax_function' );
	add_action( 'wp_ajax_reset_single_addon_license', 'wpvr_reset_single_addon_license_ajax_function' );
	function wpvr_reset_single_addon_license_ajax_function() {
		global $wpvr_empty_activation;
		wpvr_set_activation( $_POST['slug'], $wpvr_empty_activation );
		echo wpvr_get_json_response( $_POST['slug'], 1, 'License reset.' );
		wpvr_die();
	}
	
	add_action( 'wp_ajax_nopriv_register_addon_licenses', 'wpvr_register_addon_licenses_ajax_function' );
	add_action( 'wp_ajax_register_addon_licenses', 'wpvr_register_addon_licenses_ajax_function' );
	function wpvr_register_addon_licenses_ajax_function() {
		
		global $wpvr_addons;
		$items = wpvr_json_decode( str_replace( "\\", "", $_POST['items'] ) );
		
		
		foreach ( (array) $items as $item ) {
			
			$addon   = $wpvr_addons[ $item->slug ];
			$product = $addon['infos']['title'] . ' v.' . $addon['infos']['version'];
			
			$api = wpvr_capi_activate(
				$item->slug,
				$item->code,
				'store',
				$_POST['email'],
				$_POST['domain'],
				$_POST['url'],
				$_POST['ip'],
				$new_cinfos = '',
				$item->version
			);
			
			if ( $api['status'] == 0 ) {
				//echo wpvr_get_json_response( null , 0 , '' . $api[ 'msg' ] );
				$data[ $item->slug ] = array(
					'status'  => 0,
					'msg'     => $api['msg'],
					'data'    => null,
					'product' => $product,
				);
			} else {
				$now = new Datetime();
				if ( $api['data'] != null ) {
					//_d( $api['data'] );
					$new_act = array(
						'act_status'  => 1,
						'act_product' => $item->slug,
						'act_id'      => $api['data']->id,
						'act_email'   => $_POST['email'],
						'act_code'    => $item->code,
						'act_date'    => $now->format( 'Y-m-d H:i:s' ),
						'buy_date'    => $api['data']->buy_date,
						'buy_user'    => $api['data']->buy_user,
						'buy_license' => $api['data']->buy_license,
						'buy_expires' => $api['data']->buy_expires,
						'act_addons'  => array(),
						'act_url'     => $_POST['url'],
						'act_domain'  => $_POST['domain'],
						'act_version' => $item->version,
						'act_cinfos'  => '',
						'act_ip'      => $_POST['ip'],
					);
					//_d( $new_act );return false;
					wpvr_set_activation( $item->slug, $new_act );
					$data[ $item->slug ] = array(
						'status'  => 1,
						'msg'     => 'Successfully activated.',
						'data'    => $new_act,
						'product' => $product,
					);
				} else {
					$data[ $item->slug ] = array(
						'status'  => 2,
						'msg'     => 'Already activated.',
						'data'    => null,
						'product' => $product,
					);
				}
				
			}
			//return false;
		}
		
		echo wpvr_get_json_response( $data, 1, '', count( $data ) );
		wpvr_die();
	}
	
	add_action( 'wp_ajax_nopriv_dismiss_dialog_notice', 'wpvr_dismiss_dialog_notice_ajax_function' );
	add_action( 'wp_ajax_dismiss_dialog_notice', 'wpvr_dismiss_dialog_notice_ajax_function' );
	function wpvr_dismiss_dialog_notice_ajax_function() {
		global $current_user;
		$user_id = $current_user->ID;
		
		add_user_meta( $user_id, $_POST['notice_slug'], 'true', true );
		
		if ( isset( $_POST['has_voted'] ) ) {
			add_user_meta( $user_id, 'wpvr_user_has_voted', 1, true );
		}
		
		echo wpvr_get_json_response( 'ok', 1, 'Dismissed.' );
		wpvr_die();
	}
	
	add_action( 'wp_ajax_nopriv_add_remove_unwanted', 'wpvr_add_remove_unwanted_ajax_function' );
	add_action( 'wp_ajax_add_remove_unwanted', 'wpvr_add_remove_unwanted_ajax_function' );
	function wpvr_add_remove_unwanted_ajax_function() {
		global $wpvr_unwanted, $wpvr_unwanted_ids;
		$post_id = $_POST['post_id'];
		if ( $_POST['wpvr_action'] == 'add' ) {
			wpvr_unwant_videos( array( $post_id ) );
		} elseif ( $_POST['wpvr_action'] == 'remove' ) {
			wpvr_undo_unwant_videos( array( $post_id ) );
		}
		echo wpvr_get_json_response( null, 1 );
		wpvr_die();
	}
	
	
	add_action( 'wp_ajax_nopriv_wpvr_save_options', 'wpvr_save_options_ajax_function' );
	add_action( 'wp_ajax_wpvr_save_options', 'wpvr_save_options_ajax_function' );
	function wpvr_save_options_ajax_function() {
		global $wpvr_default_options, $wpvr_options;
		$new_options = array();
		
		_d( $_POST );
		
		foreach ( (array) $wpvr_default_options as $key => $default_value ) {
			if ( ! isset( $_POST[ $key ] ) ) {
				$new_options[ $key ] = $default_value;
				continue;
			}
			
			if ( $_POST[ $key ] == '@true' ) {
				$new_options[ $key ] = true;
			} elseif ( $_POST[ $key ] == '@false' ) {
				$new_options[ $key ] = false;
			} else {
				$new_options[ $key ] = $_POST[ $key ];
			}
		}
		$old_options = $wpvr_options;
		// _d( $new_options );
		update_option( 'wpvr_options', $new_options );
		$wpvr_options = $new_options;
		
		$args = apply_filters( 'wpvr_extend_saved_options', array(), $old_options, $new_options );
		echo wpvr_get_json_response( $args, 1, 'Options Saved' );
		wpvr_die();
	}
	
	add_action( 'wp_ajax_nopriv_load_activity_logs', 'wpvr_load_activity_logs_ajax_function' );
	add_action( 'wp_ajax_load_activity_logs', 'wpvr_load_activity_logs_ajax_function' );
	function wpvr_load_activity_logs_ajax_function() {
		global $wpvr_options;
		
		
		$page    = ( ! isset( $_POST['page'] ) || $_POST['page'] == 0 ) ? 1 : intval( $_POST['page'] );
		$period  = ( isset( $_POST['period'] ) && $_POST['period'] != '' ) ? $_POST['period'] : 'all';
		$type    = ( isset( $_POST['type'] ) && $_POST['type'] != '' ) ? $_POST['type'] : 'all';
		$sources = ( isset( $_POST['sources'] ) && $_POST['sources'] != '' ) ? json_decode( urldecode( $_POST['sources'] ), true ) : false;
		
		
		//_d( $page );
		$oLogs = wpvr_get_log_entries( array(
			'page'     => $page,
			'type'     => $type,
			'sources'  => $sources,
			'period'   => $period,
			'perpage'  => $wpvr_options['logsPerPage'],
			'timezone' => wpvr_get_timezone(),
		) );
		
		//_d( $oLogs );
		
		$next_page = $oLogs['page'] + 1 > $oLogs['pages'] ? 'end' : $oLogs['page'] + 1;
		// _d( $next_page );
		
		$items = array();
		foreach ( (array) $oLogs['items'] as $log ) {
			
			if ( $log['type'] == 'source' ) {
				$log_content = wpvr_render_activity_log_source_content( $log );
			} elseif ( $log['action'] == 'autoclean' ) {
				$log_content = wpvr_render_activity_log_autoclean_content( $log );
			} elseif ( $log['action'] == 'defer' ) {
				$log_content = wpvr_render_activity_log_defer_content( $log );
			} else {
				$log_content = wpvr_render_activity_log_video_content( $log );
			}
			
			$log_item = array(
				'type'       => 'blog_post',
				// 'position'       => $log['type'] == 'video' ? 'right' : 'left' ,
				'date'       => $log['time'],
				'local_date' => wpvr_get_time( $log['time'], false, false, 'output', true ),
				'title'      => wpvr_render_activity_log_title( $log ),
				'content'    => $log_content,
				'meta'       => wpvr_render_activity_log_meta( $log ),
				'owner'      => 'koko',
				'images'     => $log['icon'] != '' ? array( $log['icon'] ) : array(),
			);
			
			if ( $type == 'all' ) {
				$log_item['position'] = $log['type'] == 'video' ? 'right' : 'left';
			}
			
			$items[] = $log_item;
			
		}
		
		// _d( $items );
		//print json_encode( $items );
		echo wpvr_get_json_response( array(
			'page'  => $next_page,
			'items' => wpvr_object_to_array( $items ),
		), 1, 'Activity logs returned.' );
		
		
		wpvr_die();
	}
	
	add_action( 'wp_ajax_nopriv_update_existing_videos', 'wpvr_update_existing_videos_ajax_function' );
	add_action( 'wp_ajax_update_existing_videos', 'wpvr_update_existing_videos_ajax_function' );
	function wpvr_update_existing_videos_ajax_function() {
		
		$db = wpvr_execute_dataFillers_on_existing_videos( 10 );
		print_r( $db );
		// echo wpvr_get_json_response( array(
		// 	'details' => $details ,
		// 	'count'   => $count ,
		// ) );
		wpvr_die();
	}
	
	add_action( 'wp_ajax_nopriv_reset_source_stats', 'wpvr_reset_source_stats_ajax_function' );
	add_action( 'wp_ajax_reset_source_stats', 'wpvr_reset_source_stats_ajax_function' );
	function wpvr_reset_source_stats_ajax_function() {
		
		$source_id = $_POST['source_id'];
		
		update_post_meta( $source_id, 'wpvr_source_count_test', 0 );
		update_post_meta( $source_id, 'wpvr_source_count_run', 0 );
		update_post_meta( $source_id, 'wpvr_source_count_success', 0 );
		update_post_meta( $source_id, 'wpvr_source_count_fail', 0 );
		update_post_meta( $source_id, 'wpvr_source_count_imported', 0 );
		
		echo wpvr_get_json_response( array(
			'msg' => ___( 'This source stats have been reset.', WPVR_LANG ) . '<br/>' . __( 'Reloading ...', WPVR_LANG ),
		) );
		
		wpvr_die();
	}
	
	
	add_action( 'wp_ajax_nopriv_load_source_metrics', 'wpvr_load_source_metrics_ajax_function' );
	add_action( 'wp_ajax_load_source_metrics', 'wpvr_load_source_metrics_ajax_function' );
	function wpvr_load_source_metrics_ajax_function() {
		$source_id      = $_POST['source_id'];
		$source_metrics = wpvr_render_source_metrics( $source_id );
		$source_name    = get_post_meta( $source_id, 'wpvr_source_name', true );
		echo wpvr_get_json_response( array(
			'charts' => $source_metrics,
			'name'   => $source_name,
		), 1, 'Metrics returned' );
		wpvr_die();
	}
	
	add_action( 'wp_ajax_nopriv_wpvr_clear_logs', 'wpvr_wpvr_clear_logs_ajax_function' );
	add_action( 'wp_ajax_wpvr_clear_logs', 'wpvr_wpvr_clear_logs_ajax_function' );
	function wpvr_wpvr_clear_logs_ajax_function() {
		wpvr_clear_all_logs();
		echo wpvr_get_json_response( true, 1, 'Logs Cleared!' );
		wpvr_die();
	}
	
	