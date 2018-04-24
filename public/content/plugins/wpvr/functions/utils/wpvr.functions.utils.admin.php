<?php
	
	function wpvr_o( $var ) {
		new dBug( $var );
	}
	
	function wpvr_oo( $var ) {
		wpvrKint::$theme = 'aante-light';
		echo @wpvrKint::dump( $var );
	}
	
	function wpvr_ooo( $var ) {
		wpvrKint::$theme = 'aante-light';
		?>
        <div
                style="position: fixed;left: 0;top: 0;background: #FFF;padding: 1em;border: 5px solid red;z-index: 99999;max-height: 400px;min-width:350px;overflow-y: auto;">
			<?php @wpvrKint::dump( $var ); ?>
        </div>
		<?php
	}
	
	function wpvr_set_debug( $var = null, $append = false ) {
		
		$new = get_option( 'wpvr_debug' );
		if ( ! is_array( $new ) ) {
			$new = array();
		}
		if ( $append === false ) {
			$new = array( $var );
		} else {
			$new[] = $var;
		}
		
		update_option( 'wpvr_debug', $new );
	}
	
	function wpvr_get_debug( $var = null ) {
		
		$wpvr_debug = get_option( 'wpvr_debug' );
		d( $wpvr_debug );
	}
	
	function wpvr_reset_debug() {
		update_option( 'wpvr_debug', array() );
	}
	
	function wpvr_save_errors( $error ) {
		$errors = get_option( 'wpvr_errors' );
		if ( ! is_array( $errors ) ) {
			$errors = array();
		}
		if ( $error != '' ) {
			$errors[] = $error;
		}
		update_option( 'wpvr_errors', $errors );
	}
	
	function wpvr_object_to_array( $obj ) {
		if ( is_object( $obj ) ) {
			$obj = (array) $obj;
		}
		if ( is_array( $obj ) ) {
			$new = array();
			foreach ( $obj as $key => $val ) {
				$new[ $key ] = wpvr_object_to_array( $val );
			}
		} else {
			$new = $obj;
		}
		
		return $new;
	}
	
	function wpvr_reorder_items( $items, $ordering_key = 'order' ) {
		$new_items = array();
		$pivot     = array();
		
		foreach ( (array) $items as $item ) {
			$order = ! isset( $item[ $ordering_key ] ) ? 'x' : $item[ $ordering_key ];
			if ( ! isset( $pivot[ $order ] ) ) {
				$pivot[ $order ] = array();
			}
			$pivot[ $order ][] = $item;
		}
		ksort( $pivot );
		foreach ( (array) $pivot as $key => $key_items ) {
			$new_items = array_merge( $new_items, $key_items );
		}
		
		return $new_items;
	}
	
	function wpvr_d( $debug_response, $separator = false ) {
		ob_start();
		d( $debug_response );
		$output = ob_get_clean();
		
		return $separator . $output . $separator;
	}
	
	function wpvr_can_show_menu_links( $user_id = '' ) {
		global $wpvr_options, $user_ID;
		
		if ( $user_id == '' ) {
			$user_id = $user_ID;
		}
		$user       = new WP_User( $user_id );
		$user_roles = $user->roles;
		
		// d( $wpvr_options['showMenuFor'] );
		// d( $user_roles );
		
		$super_roles = array( 'administrator', 'superadmin' );
		foreach ( (array) $user_roles as $role ) {
			if ( in_array( $role, $super_roles ) ) {
				return true;
			}
		}
		if ( $wpvr_options['showMenuFor'] == array( 0 ) ) {
			return true;
		}
		
		if ( $wpvr_options['showMenuFor'] == null ) {
			return false;
		}
		foreach ( (array) $wpvr_options['showMenuFor'] as $role ) {
			if ( in_array( $role, $user_roles ) ) {
				return true;
			}
		}
		
		return false;
	}
	
	function wpvr_render_loading_message( $label = null ) {
		ob_start();
		if ( $label === null ) {
			$label = __( 'Loading ...', WPVR_LANG );
		}
		?>
        <div class="wpvr_center" style="color:#BBB;padding-top:20px;">
            <i class="fa fa-refresh fa-spin"></i><br/>
			<?php echo $label; ?>
        </div>
		
		<?php
		$output_string = ob_get_contents();
		ob_end_clean();
		
		return $output_string;
	}
	
	function wpvr_add_log_entry( $log = array() ) {
		global $wpdb;
		
		$log = wp_parse_args( $log, array(
			'time'      => date( 'Y-m-d H:i:s' ),
			'type'      => '',
			'icon'      => '',
			'action'    => '',
			'async'     => '1',
			'owner'     => 0,
			'exec_time' => 0,
			'data'      => array(),
		) );
		
		//Add Source last executed time
		if ( $log['type'] == 'source' ) {
			update_post_meta(
				$log['data']['source']['id'],
				'wpvr_source_last_executed_time',
				$log['time']
			);
		}
		
		
		$wpdb->insert(
			$wpdb->prefix . 'wpvr_logs',
			array(
				'time'      => $log['time'],
				'type'      => $log['type'],
				'action'    => $log['action'],
				'exec_time' => $log['exec_time'],
				'owner'     => $log['owner'],
				'async'     => $log['async'],
				'icon'      => $log['icon'],
				'data'      => wpvr_json_encode( $log['data'] ),
			)
		);
		
	}
	
	function wpvr_get_log_entries( $args = array(), $bypass_cache = false ) {
		
		global $wpdb;
		
		
		$args = wp_parse_args( $args, array(
			'page'     => 1,
			'timezone' => 'UTC',
			'nopaging' => false,
			'perpage'  => 25,
			'sources'  => false, // sources or videos
			'type'     => 'all', // sources or videos
			'period'   => 'all', // all | lastMonth | lastWeek | yesterday | today
		) );
		
		$cache_hash = md5( json_encode( $args ) );
		if (
			$bypass_cache !== true
			&& isset( $_SESSION['wpvr_cache'] )
			&& isset( $_SESSION['wpvr_cache'][ $cache_hash ] )
		) {
			//Get Data from WPVR Cache
			return $_SESSION['wpvr_cache'][ $cache_hash ];
		}
		
		$args['page'] = ( intval( $args['page'] ) < 1 ) ? 1 : intval( $args['page'] );
		$offset       = ( $args['page'] - 1 ) * $args['perpage'];
		
		
		$condition_type    = "";
		$condition_period  = "";
		$condition_sources = "";
		
		if ( $args['type'] != 'all' ) {
			$condition_type = " AND type = '{$args['type']}' ";
		}
		if ( $args['sources'] !== false && $args['sources'] != array( 0 ) ) {
			$source_conditions = array();
			foreach ( (array) $args['sources'] as $source_id ) {
				$source_conditions[]
					= "
				    data LIKE '{\"source\":{\"id\":\"{$source_id}\"%'
				    OR  data LIKE '{\"source_id\":\"{$source_id}\"%'
				";
			}
			
			$condition_sources = " AND ( " . implode( ' OR ', $source_conditions ) . " ) ";
		}
		//d( $condition_sources );
		if ( $args['period'] == 'today' ) {
			$dateA            = $dateB = date( 'Y-m-d' );
			$condition_period = " AND ( STR_TO_DATE(time, '%Y-%m-%d') = '" . $dateA . "' ) ";
		} elseif ( $args['period'] == 'yesterday' ) {
			$dateA            = $dateB = date( 'Y-m-d', strtotime( "-1 days" ) );
			$condition_period = " AND ( STR_TO_DATE(time, '%Y-%m-%d') = '" . $dateA . "' ) ";
		} elseif ( $args['period'] == 'lastWeek' ) {
			$dateB            = date( 'Y-m-d', strtotime( "-1 days" ) );
			$dateA            = date( 'Y-m-d', strtotime( "-1 weeks" ) );
			$condition_period = " AND STR_TO_DATE(time, '%Y-%m-%d') BETWEEN '" . $dateA . "' AND '" . $dateB . "' ";
		} elseif ( $args['period'] == 'lastWeekInclusive' ) {
			$dateB            = date( 'Y-m-d', strtotime( "-0 days" ) );
			$dateA            = date( 'Y-m-d', strtotime( "-1 weeks" ) );
			$condition_period = " AND STR_TO_DATE(time, '%Y-%m-%d') BETWEEN '" . $dateA . "' AND '" . $dateB . "' ";
		} elseif ( $args['period'] == 'lastMonth' ) {
			$dateB            = date( 'Y-m-d', strtotime( "-1 days" ) );
			$dateA            = date( 'Y-m-d', strtotime( "-1 months" ) );
			$condition_period = " AND STR_TO_DATE(time, '%Y-%m-%d') BETWEEN '" . $dateA . "' AND '" . $dateB . "' ";
		} elseif ( $args['period'] == 'lastMonthInclusive' ) {
			$dateB            = date( 'Y-m-d', strtotime( "-0 days" ) );
			$dateA            = date( 'Y-m-d', strtotime( "-1 months" ) );
			$condition_period = " AND STR_TO_DATE(time, '%Y-%m-%d') BETWEEN '" . $dateA . "' AND '" . $dateB . "' ";
		}
		
		$sql
			= "
			SELECT
				*,
				DATE_FORMAT( time , '%Y-%m-%d %H:00:00') as slot
			FROM
				{$wpdb->prefix}wpvr_logs
			WHERE
				1
				$condition_period
				$condition_type
                $condition_sources
			ORDER BY time DESC,type DESC
		";
		
		
		//d( $sql );
		
		if ( $args['nopaging'] === true ) {
			$sql_limit = $sql;
		} else {
			$sql_limit = $sql . " LIMIT $offset , {$args['perpage']} ";
		}
		
		$return = array(
			'total' => 0,
			'page'  => 1,
			'pages' => 0,
			'start' => 0,
			'end'   => 0,
			'count' => 0,
			'items' => array(),
		);
		
		
		$return['total'] = count( $wpdb->get_results( $sql ) );
		$db_logs         = $wpdb->get_results( $sql_limit, ARRAY_A );
		
		// d( $sql_limit );
		// d( $wpdb->last_error );
		
		$return['page']  = $args['page'];
		$return['pages'] = ceil( $return['total'] / $args['perpage'] );
		$return['start'] = $offset + 1;
		$return['end']   = min( $return['total'], $args['page'] * $args['perpage'] );
		$return['count'] = count( $db_logs );
		foreach ( (array) $db_logs as $db_log ) {
			$db_log['data'] = json_decode( $db_log['data'], ARRAY_A );
			
			$db_log['time_utc'] = $db_log['time'];
			$time               = new DateTime( $db_log['time'] );
			$time->setTimezone( new DateTimeZone( $args['timezone'] ) );
			$db_log['time']    = $time->format( 'Y-m-d H:i:s' );
			$return['items'][] = $db_log;
		}
		
		wpvr_cache_data( $return, $cache_hash );
		
		return $return;
		
	}
	
	function wpvr_clear_all_logs() {
		global $wpdb;
		
		$sql = "TRUNCATE TABLE {$wpdb->prefix}wpvr_logs";
		
		$wpdb->query( $sql );
		
	}
	
	function wpvr_render_activity_log_title( $log ) {
		ob_start();
		
		
		if ( $log['action'] == 'defer_add' ) {
			$log_icon  = 'plus-square';
			$log_title = ___( 'Deferred video added', true );
		} elseif ( $log['action'] == 'add' ) {
			$log_icon  = 'plus-square';
			$log_title = ___( 'Video Added', true );
		} elseif ( $log['action'] == 'defer' ) {
			$log_icon = 'inbox';
			
			$log_title = count( $log['data']['videos'] ) > 1 ? count( $log['data']['videos'] ) . ' ' . ___( 'Videos Deferred', true )
				: count( $log['data']['videos'] ) . ' ' . ___( 'Videos Deferred', true );
		} elseif ( $log['action'] == 'run' ) {
			$log_icon  = 'bolt';
			$log_title = ___( 'Source Ran ', true );
		} elseif ( $log['action'] == 'test' ) {
			$log_icon  = 'eye';
			$log_title = ___( 'Source Tested', true );
		} elseif ( $log['action'] == 'autoclean' ) {
			$log_icon  = 'ban';
			$log_title = ___( 'Video Checker AutoCleaning', true );
		} else {
			$log_icon = $log_title = '';
		}
		
		
		?>

        <span class="wpvr_logs_title">
            <i class="fa fa-<?php echo $log_icon; ?>"></i>
			<?php echo $log_title; ?>
        </span>
		<?php
		$output_string = ob_get_contents();
		ob_end_clean();
		
		return $output_string;
		
		
	}
	
	function wpvr_render_activity_log_meta( $log ) {
		ob_start();
		
		
		if ( $log['async'] == 0 ) {
			$log_async = '<i class="fa fa-user"></i> ' . ___( 'Regular', true );
		} else {
			$log_async = '<i class="fa fa-cubes"></i> ' . ___( 'Asynchronous', true );
			
		}
		if ( $log['owner'] == 0 ) {
			$log_owner = '<i class="fa fa-cog"></i> ' . ' <strong>' . ___( 'Autorun', true ) . '</strong>';
		} else {
			$user_info = get_userdata( $log['owner'] );
			$log_owner = '<i class="fa fa-user"></i> ' . " <strong>{$user_info->user_nicename}</strong>";
		}
		
		$log_exectime = '<i class="fa fa-clock-o"></i> <strong>' . $log['exec_time'] . '</strong> sec.';
		
		?>

        <div class="pull-left">
			<?php echo $log_async; ?> | <?php echo $log_owner; ?>
        </div>
        <div class="pull-right">
			<?php echo $log_exectime; ?>
        </div>
        <div class="wpvr_clearfix"></div>
		
		<?php
		$output_string = ob_get_contents();
		ob_end_clean();
		
		return $output_string;
		
		
	}
	
	function wpvr_render_activity_log_source_content( $log ) {
		ob_start();
		
		$metrics = array(
			array(
				'count' => $log['data']['count']['found'],
				'label' => ___( 'Found', true ),
			),
			array(
				'count' => $log['data']['count']['wanted'],
				'label' => ___( 'Wanted', true ),
			),
			array(
				'count' => $log['data']['count']['absolute'],
				'label' => ___( 'Scanned', true ),
			),
			array(
				'count' => $log['data']['count']['duplicates'],
				'label' => ___( 'Duplicates', true ),
			),
			array(
				'count' => $log['data']['count']['unwanted'],
				'label' => ___( 'Unwanted', true ),
			),
			array(
				'count' => $log['data']['count']['total'],
				'label' => ___( 'Total Found', true ),
			),
			array(
				'count' => $log['data']['count']['recalls'],
				'label' => ___( 'API Recalls', true ),
			),
		);
		
		global $wpvr_vs;
		
		$vs      = $wpvr_vs[ $log['data']['source']['service'] ];
		$vs_type = $vs['types'][ $log['data']['source']['type'] ];
		
		?>

        <div class="wpvr_logs_source">
            <div class="wpvr_source_head wpvr_logs_source">

                <div class="wpvr_source_title">
					<?php echo $log['data']['source']['name']; ?>
                </div>
                <div class="wpvr_clearfix"></div>

                <div class="wpvr_service_icon marginTop pull-left <?php echo $vs['id']; ?>">
					<?php echo strtoupper( $vs['label'] ); ?>
                </div>
                <div class="wpvr_service_icon_type pull-left">
					<?php echo wpvr_render_vs_source_type( $vs_type, $vs ); ?>
                </div>
                <button class="pull-right wpvr_button wpvr_black_button wpvr_logs_details_button closed"
                        data-id="<?php echo $log['id']; ?>">
                    <i class="fa fa-caret-down closed"></i>
                    <i class="fa fa-caret-up open"></i>
					<?php echo __( "Details", WPVR_LANG ); ?>
                </button>
                <div class="wpvr_clearfix"></div>
            </div>
            <div class="wpvr_clearfix"></div>
        </div>

        <div class="wpvr_logs_row wpvr_logs_details_content" style="display:none;" data-log="<?php echo $log['id']; ?>">
			<?php foreach ( (array) $metrics as $metric ) { ?>
                <div class="wpvr_logs_col">
                    <span class="count">
                        <?php echo $metric['count']; ?>
                    </span>
                    <span class="label">
                        <?php echo $metric['label']; ?>
                    </span>
                </div>
			
			<?php } ?>
            <div class="wpvr_clearfix"></div>
        </div>
		<?php
		$output_string = ob_get_contents();
		ob_end_clean();
		
		return $output_string;
		
		
	}
	
	function wpvr_render_activity_log_video_content( $log ) {
		ob_start();
		global $wpvr_vs;
		$vs = $wpvr_vs[ $log['data']['video_service'] ];
		
		$user_info   = get_userdata( $log['data']['post_author'] );
		$post_author = $user_info->display_name;
		
		$edit_link = get_edit_post_link( $log['data']['post_id'] );
		$view_link = get_the_permalink( $log['data']['post_id'] );
		
		?>
        <div class="wpvr_logs_source">
            <div class="wpvr_source_head wpvr_logs_source">
                <div class="wpvr_source_title">
					<?php echo $log['data']['video_title']; ?>
                </div>
                <div class="wpvr_clearfix"></div>
                <div class="wpvr_service_icon marginTop pull-left <?php echo $vs['id']; ?>">
					<?php echo strtoupper( $vs['label'] ); ?>
                </div>
                <div class="wpvr_service_icon marginTop pull-left">
					<?php echo $log['data']['video_id']; ?>
                </div>
                <button class="pull-right wpvr_button wpvr_black_button wpvr_logs_details_button closed"
                        data-id="<?php echo $log['id']; ?>">
                    <i class="fa fa-caret-down closed"></i>
                    <i class="fa fa-caret-up open"></i>
					<?php echo __( "Details", WPVR_LANG ); ?>
                </button>
                <div class="wpvr_clearfix"></div>

            </div>
            <div class="wpvr_clearfix"></div>
        </div>
        <div class="wpvr_logs_row wpvr_logs_details_content" style="display:none;" data-log="<?php echo $log['id']; ?>">
            <div class="wpvr_logs_wide_col">
                <strong>Post ID:</strong>
                <span>
                    #<?php echo $log['data']['post_id']; ?>
                    <a href="<?php echo $edit_link; ?>" title="Edit this video" target="_blank">Edit</a> |
                    <a href="<?php echo $view_link; ?>" title="View this video" target="_blank">View</a> |
                    <a
                            video_id="<?php echo $log['data']['video_id']; ?>"
                            post_id="<?php echo $log['data']['post_id']; ?>"
                            service="<?php echo $log['data']['video_service']; ?>"
                            class="wpvr_video_view"
                            href="#"
                            title="Preview this video"
                    >Preview</a>
                </span>
            </div>
            <div class="wpvr_logs_wide_col">
                <strong><?php echo __( "Post Type", WPVR_LANG ); ?>:</strong>
                <span><?php echo $log['data']['post_type']; ?></span>
            </div>
            <div class="wpvr_logs_wide_col">
                <strong><?php echo __( "Post Date", WPVR_LANG ); ?>:</strong>
                <span><?php echo $log['data']['post_date']; ?></span>
            </div>
            <div class="wpvr_logs_wide_col">
                <strong><?php echo __( "Post Author", WPVR_LANG ); ?>:</strong>
                <span><?php echo $post_author; ?></span>
            </div>
            <div class="wpvr_logs_wide_col">
                <strong><?php echo __( "Post Status", WPVR_LANG ); ?>:</strong>
                <span>
                    <?php echo $log['data']['post_status'] == 'publish' ? ___( 'Published', true ) : ucfirst( $log['data']['post_status'] ); ?>
					<?php if ( $log['data']['post_status'] != 'publish' ) { ?>
                        <a href="#"><?php echo __( "Publish Now", WPVR_LANG ); ?></a>
					<?php } ?>
                    </span>
            </div>
			<?php if ( isset( $log['data']['post_thumbnail'] ) ) { ?>
                <div class="wpvr_logs_wide_col">
                    <strong><?php echo __( "Post Thumbnail", WPVR_LANG ); ?>:</strong>
                    <span>
                    <?php echo $log['data']['post_thumbnail'] === false ? ___( 'Using external thumbnail' ) : ___( 'Downloaded video thumbnail' ); ?>
                </span>
                </div>
			<?php } ?>
			
			<?php if ( count( $log['data']['post_categories'] ) != 0 ) { ?>

                <div class="wpvr_logs_wide_col">
                    <strong><?php echo __( "Posted in", WPVR_LANG ); ?>:</strong>
                    <span><?php echo implode( ', ', $log['data']['post_categories'] ); ?></span>
                </div>
			<?php } ?>
			<?php if ( count( $log['data']['post_tags'] ) != 0 ) { ?>

                <div class="wpvr_logs_wide_col">
                    <strong><?php echo __( "Tagged with", WPVR_LANG ); ?>:</strong>
                    <span><?php echo implode( ', ', $log['data']['post_tags'] ); ?></span>
                </div>
			<?php } ?>
            <div class="wpvr_clearfix"></div>
        </div>
		<?php
		$output_string = ob_get_contents();
		ob_end_clean();
		
		return $output_string;
		
		
	}
	
	function wpvr_render_activity_log_defer_content( $log ) {
		ob_start();
		global $wpvr_vs;
		
		
		?>
        <div class="wpvr_logs_row scrollable">
			<?php foreach ( (array) $log['data']['videos'] as $video ) { ?>
				<?php if ( ! isset( $video['url'] ) ) {
					continue;
				} ?>
				<?php $vs = $wpvr_vs[ $video['service'] ]; ?>
                <div class="wpvr_logs_video">
                    <div class="wpvr_logs_video_head">
                        <div class="wpvr_logs_video_service"
                             style="text-transform:uppercase;background:<?php echo $vs['color']; ?> !important;">
							<?php echo $video['service']; ?>
                        </div>
                        <img src="<?php echo $video['icon']; ?>"/>
                    </div>
                    <div class="wpvr_logs_video_title">
                        <a href="<?php echo $video['url']; ?>" target="_blank">
							<?php echo $video['title']; ?>
                        </a>
                    </div>
                </div>
			<?php } ?>
            <div class="wpvr_clearfix"></div>
        </div>
		<?php
		$output_string = ob_get_contents();
		ob_end_clean();
		
		return $output_string;
		
		
	}
	
	function wpvr_render_activity_log_autoclean_content( $log ) {
		ob_start();
		
		$token = bin2hex( openssl_random_pseudo_bytes( 10 ) );
		
		?>
        <div class="wpvr_logs_subtitle">
			<?php echo $log['data']['msg']; ?>
        </div>
        <div class="wpvr_logs_row closed" id="<?php echo $token; ?>">
			<?php foreach ( (array) $log['data']['ids'] as $post_id ) { ?>
				<?php $thumb_url = get_the_post_thumbnail_url( $post_id, 'thumbnail' ); ?>
				<?php $post = get_post( $post_id ); ?>
                <div class="wpvr_logs_video">
                    <a href="<?php echo admin_url( 'post.php?post=' . $post_id . '&action=edit' ); ?>" target="_blank">
                        <div class="wpvr_logs_video_head">
                            <img style="width:100%;" src="<?php echo $thumb_url; ?>"/>
                        </div>
                        <div class="wpvr_logs_video_title">
							<?php echo $post->post_title; ?>
                        </div>
                </div>
                </a>
			<?php } ?>
            <div class="wpvr_clearfix"></div>
        </div>
        <p style="text-align:center;">
            <a href="#" class="wpvr_log_details_btn" data-token="<?php echo $token; ?>">Show All</a>
        </p>
		<?php
		$output_string = ob_get_contents();
		ob_end_clean();
		
		return $output_string;
		
		
	}
	
	function wpvr_debug_echo( $str, $kint = false ) {
		echo '<pre style="margin:10px 0; padding:10px;border:1px dashed #CCC;background: bisque;width: 90%;overflow-x: auto;">';
		if ( $kint ) {
			d( $str );
		} else {
			print_r( $str );
		}
		echo '</pre>';
	}
	
	function wpvr_get_system_info() {
		global $wpvr_act;
		//d( $wpvr_act );
		$php_version = explode( '+', PHP_VERSION );
		
		$meminfo = wpvr_get_meminfo();
		
		$curl_info = curl_version();
		$infos     = array(
			'wpvr_version'         => array(
				'label'  => __( 'WPVR Version', WPVR_LANG ),
				'value'  => WPVR_VERSION,
				'status' => '',
			),
			'wpvr_activation'      => array(
				'label'  => __( 'WPVR License Code', WPVR_LANG ),
				'value'  => WPVR_IS_DEMO ? ' ********* ' : $wpvr_act['act_code'],
				'status' => $wpvr_act['act_status'] == 1 ? 'good' : 'bad',
			),
			'wpvr_activation_date' => array(
				'label'  => __( 'WPVR Activation', WPVR_LANG ),
				'value'  => $wpvr_act['act_domain'],
				'status' => $wpvr_act['act_status'] == 1 ? 'good' : 'bad',
			),
			'server'               => array(
				'label'  => __( 'Server Software', WPVR_LANG ),
				'value'  => '<br/>' . $_SERVER['SERVER_SOFTWARE'],
				'status' => '',
			),
			'php_version'          => array(
				'label'  => __( 'PHP Version', WPVR_LANG ),
				'value'  => $php_version[0],
				'status' => version_compare( PHP_VERSION, WPVR_REQUIRED_PHP_VERSION, '>=' ) ? 'good' : 'bad',
			),
			
			'memory_available'   => array(
				'label'  => __( 'Memory Available', WPVR_LANG ),
				'value'  => $meminfo === false ? '#UNKOWN' : $meminfo['available'] . 'M',
				'status' => $meminfo['available'] > 128 ? 'good' : 'bad',
			),
			'memory_limit'       => array(
				'label'  => __( 'PHP Memory Limit', WPVR_LANG ),
				'value'  => ini_get( 'memory_limit' ),
				'status' => '',
			),
			'post_max_size'      => array(
				'label'  => __( 'Post Max Size', WPVR_LANG ),
				'value'  => ini_get( 'post_max_size' ),
				'status' => '',
			),
			'max_input_time '    => array(
				'label'  => __( 'Maximum Input Time', WPVR_LANG ),
				'value'  => ini_get( 'max_input_time' ),
				'status' => '',
			),
			'max_execution_time' => array(
				'label'  => __( 'Maximum Execution Time', WPVR_LANG ),
				'value'  => ini_get( 'max_execution_time' ),
				'status' => '',
			),
			'safe_mode'          => array(
				'label'  => __( 'PHP Safe Mode', WPVR_LANG ),
				'value'  => ini_get( 'safe_mode' ) ? 'ON' : 'OFF',
				'status' => ini_get( 'safe_mode' ) ? 'bad' : 'good',
			),
			'cURL_status'        => array(
				'label'  => __( 'Curl Status', WPVR_LANG ),
				'value'  => function_exists( 'curl_version' ) ? 'ON' : 'OFF',
				'status' => function_exists( 'curl_version' ) ? 'good' : 'bad',
			),
			
			'curl_version' => array(
				'label'  => __( 'Curl Version', WPVR_LANG ),
				'value'  => $curl_info['version'],
				'status' => version_compare( $curl_info['version'], WPVR_REQUIRED_CURL_VERSION, '>=' ) ? 'good' : 'bad',
			),
			
			'allow_url_fopen' => array(
				'label'  => __( 'Allow URL Fopen', WPVR_LANG ),
				'value'  => ini_get( 'allow_url_fopen' ) == '1' ? 'ON' : 'OFF',
				'status' => ini_get( 'allow_url_fopen' ) == '1' ? 'good' : 'bad',
			),
			'openssl_status'  => array(
				'label'  => __( 'OpenSSL Extension', WPVR_LANG ),
				'value'  => extension_loaded( 'openssl' ) ? 'ON' : 'OFF',
				'status' => extension_loaded( 'openssl' ) ? 'good' : 'bad',
			),
			'wpvr_folder'     => array(
				'label'  => __( 'Plugin Folder', WPVR_LANG ),
				'value'  => WPVR_PATH,
				'status' => '',
			),
			'folder_writable' => array(
				'label'  => __( 'Plugin Folder Writable', WPVR_LANG ),
				'value'  => ( is_writable( WPVR_PATH ) === true ) ? 'ON' : 'OFF',
				'status' => ( is_writable( WPVR_PATH ) === true ) ? 'good' : 'bad',
			),
			'multisite'       => array(
				'label'  => __( 'WordPress MultiSite', WPVR_LANG ),
				'value'  => is_multisite() ? __( 'Enabled', WPVR_LANG ) : __( 'Disabled', WPVR_LANG ),
				'status' => '',
			),
		
		);
		
		$act  = wpvr_get_act_data( 'wpvr' );
		$wpvr = array(
			
			'wpvr_url' => array(
				'label'  => __( 'Website URL', WPVR_LANG ),
				'value'  => WPVR_SITE_URL,
				'status' => '',
			),
			
			'wpvr_version' => array(
				'label'  => __( 'WPVR Version', WPVR_LANG ),
				'value'  => WPVR_VERSION,
				'status' => '',
			),
			
			'wpvr_act_status' => array(
				'label'  => __( 'WPVR Activation Status', WPVR_LANG ),
				'value'  => $act['act_status'],
				'status' => '',
			),
			
			'wpvr_act_code' => array(
				'label'  => __( 'WPVR Activation Code', WPVR_LANG ),
				'value'  => $act['act_code'],
				'status' => '',
			),
			
			'wpvr_act_date' => array(
				'label'  => __( 'WPVR Activation Date', WPVR_LANG ),
				'value'  => $act['act_date'],
				'status' => '',
			),
			
			'wpvr_act_id' => array(
				'label'  => __( 'WPVR Activation ID', WPVR_LANG ),
				'value'  => $act['act_id'],
				'status' => '',
			),
		
		);
		
		return array(
			'sys'  => $infos,
			'wpvr' => $wpvr,
		);
		
	}
	
	function wpvr_get_meminfo() {
		$data    = explode( "\n", @file_get_contents( "/proc/meminfo" ) );
		$meminfo = array();
		foreach ( (array) $data as $line ) {
			list( $key, $val ) = explode( ":", $line );
			
			$val             = str_replace( ' kB', '', trim( $val ) );
			$meminfo[ $key ] = ceil( $val / 1000 );
		}
		
		// Memory in Mo
		if ( ! isset( $meminfo['MemTotal'] ) ) {
			return false;
		}
		
		return array(
			'available' => $meminfo['MemTotal'],
		);
	}
	
	function wpvr_render_system_info( $info_blocks ) {
		$html = " WP Video Robot : SYSTEM INFORMATION \r\n";
		foreach ( (array) $info_blocks as $infos ) {
			$html .= "----------------------------------------------------------------- \r\n";
			foreach ( (array) $infos as $info ) {
				
				if ( is_bool( $info['value'] ) && $info['value'] === true ) {
					$info['value'] = "TRUE";
				} elseif ( is_bool( $info['value'] ) && $info['value'] === true ) {
					$info['value'] = "FALSE";
				}
				$html .= " - " . $info['label'] . " : " . $info['value'] . " \r\n";
			}
			$html .= "----------------------------------------------------------------- \r\n";
		}
		
		return $html;
	}
	
	function wpvr_get_customer_infos() {
		global $wpvr_options;
		$customer_infos = array(
			'purchase_code'    => $wpvr_options['purchaseCode'],
			'site_name'        => get_bloginfo( 'name' ),
			'site_url'         => get_bloginfo( 'url' ),
			'site_description' => get_bloginfo( 'description' ),
			'site_language'    => ( is_rtl() ) ? 'RTL' : 'LTR',
			'admnin_email'     => get_bloginfo( 'admin_email' ),
			'wp_version'       => get_bloginfo( 'version' ),
			'wp_url'           => get_bloginfo( 'wpurl' ),
			'wp_rtl'           => is_rtl(),
			'sources_stats'    => wpvr_sources_stats(),
			'videos_stats'     => wpvr_videos_stats(),
		);
		
		return ( base64_encode( wpvr_json_encode( $customer_infos ) ) );
	}
	
	function wpvr_get_user_by_id( $user_id, $bypass_cache = false ) {
		$cache_hash = md5( 'wpvr_' . $user_id );
		if (
			$bypass_cache !== true
			&& isset( $_SESSION['wpvr_cache'] )
			&& isset( $_SESSION['wpvr_cache'][ $cache_hash ] )
		) {
			//Get Data from WPVR Cache
			return $_SESSION['wpvr_cache'][ $cache_hash ];
		}
		
		$user = get_user_by( 'id', $user_id );
		wpvr_cache_data( $user, $cache_hash );
		
		return $user;
	}
	
	//@Unused
	function wpvr_get_sources_posting_to_a_category( $category_id ) {
		global $wpdb;
		$sql
			= "
		select
			P.ID
		from
			$wpdb->posts P
			left join $wpdb->postmeta M on P.ID = M.post_id
		WHERE
			P.post_type = 'wpvr_source'
			AND M.meta_key = 'wpvr_source_postCats'
			AND M.meta_value LIKE '%\"" . $category_id . "\"%'
		";
		
		$r   = $wpdb->get_results( $sql, ARRAY_A );
		$ids = array();
		foreach ( (array) $r as $id ) {
			$ids[] = intval( $id['ID'] );
		}
		
		return $ids;
	}
	
	//@Unused
	function wpvr_recursive_log_msgs( $log_msgs, $lineHTML ) {
		foreach ( (array) $log_msgs as $msg ) {
			if ( ! is_array( $msg ) ) {
				$lineHTML .= "<div class='wpvr_log_msgs'>" . $msg . "</div>";
			} else {
				$lineHTML .= "<div class='wpvr_log_msgs_rec'>";
				$lineHTML = wpvr_recursive_log_msgs( $msg, $lineHTML );
				$lineHTML .= "</div>";
			}
			
			return $lineHTML;
		}
	}
	
	function wpvr_die() {
		do_action( 'wpvr_before_die' );
		die();
	}