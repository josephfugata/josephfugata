<?php
	
	//Should be deprecated and replaced by wpvr_parse_args
	function wpvr_extend( $params, $params_def, $strict = false ) {
		foreach ( (array) $params_def as $key => $val ) {
			if ( ! isset( $params[ $key ] ) ) {
				
				$params[ $key ] = $val;
				
			} elseif ( $strict === false && $params[ $key ] == "" && ! is_bool( $params[ $key ] ) ) {
				$params[ $key ] = $val;
				
			} elseif ( isset( $params[ $key ] ) && is_bool( $params[ $key ] ) ) {
			
			
			}
		}
		
		return $params;
	}
	
	function wpvr_doWork() {
		global $wpvr_options;
		$doWork   = false;
		$now      = new DateTime();
		$hour_now = $now->format( 'H' );
		if ( $wpvr_options['autoRunMode'] === false ) {
			//echo "AUTORUN MODE DISABLED ! ";
			return false;
		}
		if ( $wpvr_options['wakeUpHours'] ) {
			$wuhA = $wpvr_options['wakeUpHoursA'];
			$wuhB = $wpvr_options['wakeUpHoursB'];
			if ( $wuhA == 'empty' || $wuhB == 'empty' ) {
				$doWork = true;
			} else {
				$doWork = ( $hour_now >= $wuhA && $hour_now <= $wuhB );
			}
		} else {
			$doWork = true;
		}
		
		return $doWork;
	}
	
	function wpvr_capi_init() {
		if ( isset( $_GET['capi'] ) ) {
			if ( isset( $_POST['action'] ) ) {
				wpvr_capi_do( $_POST['action'], $_POST );
			} else {
				echo "SILENCE IS GOLDEN.";
			}
			exit;
		}
	}
	
	function wpvr_capi_do( $action, $_post ) {
		$r = array(
			'status' => false,
			'msg'    => '',
			'data'   => null,
		);
		
		if ( $action == 'add_notice' ) {
			if ( ! isset( $_post['notice'] ) ) {
				$r['status'] = false;
				$r['msg']    = 'Notice variable missing. EXIT...';
				echo wpvr_json_encode( $r );
			}
			$notice = (array) wpvr_json_decode( base64_decode( $_post['notice'] ) );
			$slug   = wpvr_add_notice( $notice );
			if ( $slug != false ) {
				$r['status'] = true;
				$r['msg']    = 'Notice Added (slug = ' . $slug . '). DONE...';
				$r['data']   = $slug;
				echo wpvr_json_encode( $r );
			} else {
				$r['status'] = false;
				$r['msg']    = 'Error adding the notice. EXIT...';
				echo wpvr_json_encode( $r );
			}
			
			return false;
		}
		
		if ( $action == 'get_activation' ) {
			
			$act = wpvr_get_activation( $_post['slug'] );
			
			echo wpvr_json_encode( array(
				'status' => $act['act_status'],
				'msg'    => 'Activation returned.',
				'data'   => $act,
			) );
			
			return false;
		}
		
		if ( $action == 'reset_activation' ) {
			
			wpvr_set_activation( $_post['slug'], array() );
			echo wpvr_json_encode( array(
				'status' => 1,
				'msg'    => 'Reset Completed.',
				'data'   => null,
			) );
			
			return false;
		}
		
		if ( $action == 'reload_addons' ) {
			update_option( 'wpvr_addons_list', '' );
			$r['status'] = true;
			$r['msg']    = 'ADDONS LIST RESET ...';
			echo wpvr_json_encode( $r );
			
			return false;
		}
		
	}
	
	function wpvr_get_act_data( $slug = 'wpvr' ) {
		global $wpvr_empty_activation;
		$wpvr_acts = get_option( 'wpvr_activations' );
		if ( ! array( $wpvr_acts ) ) {
			$wpvr_acts = array();
		}
		if ( ! isset( $wpvr_acts[ $slug ] ) ) {
			$wpvr_acts[ $slug ] = $wpvr_empty_activation;
		}
		
		if ( ! isset( $wpvr_acts[ $slug ]['buy_expires'] ) ) {
			$now                               = new Datetime();
			$wpvr_acts[ $slug ]['buy_expires'] = $now->format( 'Y-m-d H:i:s' );
		}
		
		if ( $wpvr_acts[ $slug ] != '' ) {
			return array(
				'act_status'  => $wpvr_acts[ $slug ]['act_status'],
				'act_id'      => $wpvr_acts[ $slug ]['act_id'],
				'act_email'   => $wpvr_acts[ $slug ]['act_email'],
				'act_code'    => $wpvr_acts[ $slug ]['act_code'],
				'act_date'    => $wpvr_acts[ $slug ]['act_date'],
				'buy_date'    => $wpvr_acts[ $slug ]['buy_date'],
				'buy_user'    => $wpvr_acts[ $slug ]['buy_user'],
				'buy_license' => $wpvr_acts[ $slug ]['buy_license'],
				'act_addons'  => $wpvr_acts[ $slug ]['act_addons'],
				'buy_expires' => $wpvr_acts[ $slug ]['buy_expires'],
			);
		}
	}
	
	function wpvr_set_act_data( $slug = 'wpvr', $new_data ) {
		$wpvr_acts = get_option( 'wpvr_activations' );
		if ( ! array( $wpvr_acts ) ) {
			$wpvr_acts = array();
		}
		$wpvr_acts[ $slug ] = $new_data;
		update_option( 'wpvr_activations', $wpvr_acts );
	}
	
	function wpvr_refresh_act_data( $slug = 'wpvr', $do_refresh = false ) {
		global $WPVR_SERVER;
		$act = wpvr_get_act_data( $slug );
		$url = wpvr_capi_build_query( WPVR_API_REQ_URL, array(
			'api_key'         => WPVR_API_REQ_KEY,
			'action'          => 'check_license',
			'products_slugs'  => $slug,
			'act_id'          => $act['act_id'], //921
			'encrypt_results' => 1,
			'only_results'    => 1,
			'origin'          => $WPVR_SERVER['HTTP_HOST'],
		) );
		
		$response = wpvr_capi_remote_get( $url, false );
		//d( $response );
		
		if ( $response['status'] != 200 ) {
			echo "CAPI Unreachable !";
			
			return false;
		}
		$fresh_license           = wpvr_json_decode( base64_decode( $response['data'] ), true );
		$fresh_license           = wpvr_object_to_array( $fresh_license );
		$new_data                = $act;
		$new_data['act_status']  = $fresh_license['state'];
		$new_data['act_id']      = $fresh_license['id'];
		$new_data['act_email']   = $fresh_license['act_email'];
		$new_data['act_code']    = $fresh_license['act_code'];
		$new_data['act_date']    = $fresh_license['act_date'];
		$new_data['buy_date']    = $fresh_license['buy_date'];
		$new_data['buy_user']    = $fresh_license['buy_user'];
		$new_data['buy_license'] = 'inactive';
		$new_data['act_addons']  = array();
		$new_data['buy_expires'] = $fresh_license['buy_expires'];
		if ( $do_refresh ) {
			wpvr_set_act_data( $slug, $new_data );
		}
		
		return $new_data;
	}
	
	function wpvr_license_is_expired( $slug ) {
		$new    = wpvr_refresh_act_data( $slug, true );
		$now    = new Datetime();
		$expire = new Datetime( $new['buy_expires'] );
		
		return ( $now > $expire );
	}
	
	function wpvr_set_activation( $product_slug = '', $act = array() ) {
		global $wpvr_empty_activation;
		$act              = wpvr_extend( $act, $wpvr_empty_activation );
		$wpvr_activations = get_option( 'wpvr_activations' );
		if ( ! array( $wpvr_activations ) ) {
			$wpvr_activations = array();
		}
		
		$wpvr_activations[ $product_slug ] = $act;
		
		update_option( 'wpvr_activations', $wpvr_activations );
		
		
	}
	
	function wpvr_is_free_addon( $product_slug = '' ) {
		global $wpvr_addons;
		if (
			isset( $wpvr_addons[ $product_slug ]['infos']['free_addon'] )
			&& $wpvr_addons[ $product_slug ]['infos']['free_addon'] === true
		) {
			return true;
		} else {
			return false;
		}
		
		
	}
	
	function wpvr_get_multisite_activation( $product_slug = '', $_blog_id = null, $first_only = false ) {
		global $wpvr_empty_activation, $wpvr_addons;
		
		
		$blogs = get_sites( array() );
		//d( $blogs );
		$returned_activations   = array();
		$first_valid_activation = false;
		foreach ( (array) $blogs as $blog ) {
			// d( $blog );
			// $blog_id = 0 ;
			$blog_id = $blog->blog_id;
			
			if ( $_blog_id != null && $_blog_id != $blog_id ) {
				continue;
			}
			
			$wpvr_activations = get_blog_option( $blog_id, 'wpvr_activations' );
			
			//if( $product_slug == 'wpvr-fbvs' ){
			//	d( $wpvr_activations[ $product_slug ] );
			//}
			
			if ( $wpvr_activations != false ) {
				
				if ( $product_slug == '' ) {
					$returned_activations[ $blog_id ] = $wpvr_activations;
				} elseif ( isset( $wpvr_activations[ $product_slug ] ) ) {
					
					$returned_activations[ $blog_id ] = $wpvr_activations[ $product_slug ];
					if ( $wpvr_activations[ $product_slug ]['act_status'] == 1 ) {
						$first_valid_activation = $wpvr_activations[ $product_slug ];
					}
				} else {
					$returned_activations[ $blog_id ] = $wpvr_empty_activation;
				}
				
				//if( $first_only ) break;
				
			}
			
			
			//d( $blog['path'] );
			
			//d( $old_activations );
		}
		
		//d( $returned_activations );
		if ( count( $returned_activations ) == 0 ) {
			return false;
		}
		
		if ( $first_only ) {
			//return array_pop( $returned_activations );
			return $first_valid_activation;
		}
		
		return $returned_activations;
	}
	
	function wpvr_get_activation( $product_slug = '' ) {
		global $wpvr_empty_activation, $wpvr_addons;
		
		$wpvr_activations = get_option( 'wpvr_activations' );
		$old_activation   = get_option( 'wpvr_activation' );
		
		if ( $product_slug == '' ) {
			return $wpvr_activations;
		}
		if ( ! array( $wpvr_activations ) ) {
			$wpvr_activations = array();
		}
		
		if ( ! isset( $wpvr_activations[ $product_slug ] ) ) {
			if ( $product_slug == 'wpvr' && is_array( $old_activation ) ) {
				$wpvr_activations[ $product_slug ] = $old_activation;
			} else {
				$wpvr_activations[ $product_slug ] = $wpvr_empty_activation;
			}
		}
		
		return $wpvr_activations[ $product_slug ];
		
	}
	
	function wpvr_reset_on_activation() {
		global $wpvr_imported;
		
		//reset tables
		update_option( 'wpvr_deferred', array() );
		update_option( 'wpvr_deferred_ids', array() );
		update_option( 'wpvr_imported', array() );
		
		//Update IMPORTED
		wpvr_update_imported_videos();
		$wpvr_imported = get_option( 'wpvr_imported' );
		
	}
	
	function wpvr_is_source_screen( $post_id = null ) {
		if ( $post_id === null ) {
			$post_id = wpvr_get_current_post_id();
		}
		
		if (
			( $post_id == '' || get_post_type( $post_id ) != WPVR_SOURCE_TYPE )
			&& (
				$post_id != ''
				|| ! isset( $_GET['post_type'] )
				|| $_GET['post_type'] != WPVR_SOURCE_TYPE
			)
		) {
			return false;
		} else {
			return true;
		}
	}
	
	function wpvr_cache_data( $data, $cache_key = null ) {
		if ( $cache_key == null ) {
			$cache_key = md5( json_encode( $data ) );
		}
		
		if ( ! isset( $_SESSION['wpvr_cache'] ) ) {
			$_SESSION['wpvr_cache'] = array();
		}
		
		$_SESSION['wpvr_cache'][ $cache_key ] = $data;
		
	}
	
	function wpvr_get_current_post_id( $single = true ) {
		global $pagenow;
		
		
		if ( ! in_array( $pagenow, array( 'post-new.php', 'post.php' ) ) ) {
			return false;
		}
		
		// Getting post_id from $_GET
		if ( isset( $_GET['post'] ) ) {
			if ( is_array( $_GET['post'] ) ) {
				return $single === true ? false : $_GET['post'];
			} else {
				return intval( $_GET['post'] );
			}
		}
		
		// Getting post_id from $_POST
		if ( isset( $_POST['post_ID'] ) ) {
			if ( is_array( $_POST['post_ID'] ) ) {
				return $single === true ? false : $_POST['post_ID'];
			} else {
				return intval( $_POST['post_ID'] );
			}
		}
		
		
		return '';
	}
	
	function wpvr_autoClean_should_work() {
		global $wpvr_options;
		
		if ( $wpvr_options['autoCleanSchedule'] == 'hourly' ) {
			return true;
		}
		
		$nowTime = wpvr_get_time( 'now', false, true, true, true );
		
		if ( $wpvr_options['autoCleanSchedule'] == 'daily' ) {
			//Check now hour (tz) / sch hour (tz)
			$x = explode( 'H', $wpvr_options['autoCleanScheduleTime'] );
			
			return $x[0] == $nowTime->format( 'H' );
		}
		
		if ( $wpvr_options['autoCleanSchedule'] == 'weekly' ) {
			//Check now hour (tz) / sch hour (tz)
			$x       = explode( 'H', $wpvr_options['autoCleanScheduleTime'] );
			$hour_ok = $x[0] == $nowTime->format( 'H' );
			
			//Check now day (tz) / sch day (tz)
			$day_ok = strtolower( $nowTime->format( 'l' ) ) == $wpvr_options['autoCleanScheduleDay'];
			
			return $day_ok && $hour_ok;
		}
		
		
		return false;
	}
	
	function wpvr_is_working_hour( $hour ) {
		global $wpvr_options;
		$wh = $wpvr_options['wakeUpHours'];
		
		if ( $wh === false ) {
			return true;
		}
		
		$whA = $wpvr_options['wakeUpHoursA'];
		$whB = $wpvr_options['wakeUpHoursB'];
		
		$whArray = wpvr_make_interval( $whA, $whB, true );
		if ( isset( $whArray[ $hour ] ) ) {
			return $whArray[ $hour ];
		} else {
			return array();
		}
	}
	
	function wpvr_remove_tmp_files() {
		$dirHandle = opendir( WPVR_TMP_PATH );
		while ( $file = readdir( $dirHandle ) ) {
			if ( ! is_dir( $file ) ) {
				unlink( WPVR_TMP_PATH . "$file" );
			}
		}
		closedir( $dirHandle );
	}
	
	function wpvr_add_multiple_post_meta( $post_id, $new_meta = array(), $only_new = false, $sql_only = false, $old_metas = false ) {
		global $wpdb;
		
		if ( count( $new_meta ) == 0 ) {
			return false;
		}
		
		
		$db_done    = $sql_only === true ? array() : false;
		$sql_insert = array();
		$sql_delete = array();
		
		
		if ( $old_metas === false ) {
			$old_metas = get_post_meta( $post_id );
		}
		
		
		foreach ( (array) $new_meta as $meta_key => $meta_value ) {
			
			if ( $meta_key === '' ) {
				continue;
			}
			
			$_meta_key   = $meta_key;
			$_meta_value = $meta_value;
			$meta_key    = wpvr_strip_html_bad_tags( $meta_key );
			$meta_value  = wpvr_strip_html_bad_tags( maybe_serialize( $meta_value ) );
			
			
			//Do nothing metakey already exists and metavalue has the same value
			if (
				isset( $old_metas[ $_meta_key ] )
				&& isset( $old_metas[ $_meta_key ][0] )
				&& $old_metas[ $_meta_key ][0] == $_meta_value
			) {
				continue;
			}
			
			// d( $_meta_value );
			
			//Remove Old Meta Key from DB if using only_new
			if (
				$only_new === true
				&& isset( $old_metas[ $_meta_key ] )
			) {
				$sql_delete[ $_meta_key ] = " ( '{$post_id}' , '{$meta_key}' ) ";
			}
			
			$sql_insert[ $_meta_key ] = " ('{$post_id}', '{$meta_key}' , '{$meta_value}' ) ";
		}
		
		
		if ( $only_new === true ) {
			if ( count( $sql_delete ) != 0 ) {
				$sql_delete = "DELETE FROM  {$wpdb->postmeta} WHERE (post_id , meta_key) IN ( " . implode( ", ", $sql_delete ) . " )";
				
				if ( $sql_only === false ) {
					$db_done = $wpdb->query( $sql_delete );
				} else {
					$db_done[] = $sql_delete;
				}
			}
		}
		
		if ( count( $sql_insert ) != 0 ) {
			$sql_insert = "INSERT INTO $wpdb->postmeta (post_id , meta_key , meta_value) VALUES " . "\n\t" . implode( ",\n\t", $sql_insert ) . " ";
			if ( $sql_only === false ) {
				$db_done = $wpdb->query( $sql_insert );
			} else {
				$db_done[] = $sql_insert;
			}
		}
		
		return $db_done;
	}
	
	function wpvr_get_total_fetched_videos_per_run() {
		global $wpvr_options;
		
		$sources = wpvr_get_sources( array( 'status' => 'on' ) );
		$sources = wpvr_multiplicate_sources( $sources );
		$data    = array();
		//new dBug( $sources );
		
		foreach ( (array) $sources as $source ) {
			if ( ! isset( $data[ $source->id ] ) ) {
				$data[ $source->id ] = array(
					'source_name'   => $source->name,
					'wanted_videos' => 0,
					'sub_sources'   => 0,
					'warning'       => false,
				);
			}
			$wantedVideos                         = ( $source->wantedVideosBool == 'default' ) ? $wpvr_options['wantedVideos'] : $source->wantedVideos;
			$data[ $source->id ]['wanted_videos'] += $wantedVideos;
			$data[ $source->id ]['sub_sources'] ++;
			
			if ( $data[ $source->id ]['wanted_videos'] > WPVR_SECURITY_WANTED_VIDEOS ) {
				$data[ $source->id ]['warning'] = true;
			}
			
		}
		
		return $data;
	}
	
	function wpvr_async_balance_items( $items, $buffer, $push_only = false ) {
		$k        = $j = 0;
		$balanced = array( 0 => array(), );
		foreach ( (array ) $items as $item_id => $item ) {
			if ( $k >= $buffer ) {
				$k = 0;
				$j ++;
				$balanced[ $j ] = array();
			}
			if ( $push_only === false ) {
				$balanced[ $j ][ $item_id ] = $item;
			} else {
				$balanced[ $j ][] = $item;
			}
			$k ++;
		}
		
		return $balanced;
	}
	
	function wpvr_render_video_filters( $filter, $GET ) {
		ob_start();
		
		global $wpvr_options;
		
		//SERVICES Video Filters
		if ( $filter == 'services' ) {
			global $wpvr_vs;
			$services_options = array();
			foreach ( (array) $wpvr_vs as $value => $vs ) {
				// d( $vs );
				if ( isset( $vs['skipThis'] ) ) {
					continue;
				}
				$services_options[ $vs['id'] ] = $vs['label'];
			}
			
			$value = isset( $GET['video_service'] ) ? json_decode( urldecode( stripslashes( $GET['video_service'] ) ), true ) : array();
			?>
            <div
                    class="wpvr_filter_dropdown <?php echo count( $value ) == 0 || $value == array( '' ) ? '' : 'active'; ?> wpvr_tipso noborder"
                    title="<?php echo __( 'Filter by video service', WPVR_LANG ); ?>"
            >
				
				<?php echo wpvr_render_dropdown( array(
					'name'        => "video_service",
					'placeholder' => ___( 'All services', false ) . ' ...',
					'options'     => $services_options,
					'maxItems'    => 25,
					'wrap_class'  => 'wpvr_filter_dropdown_wrap ',
					'value'       => $value,
				) ); ?>

            </div>
			<?php return ob_get_clean();
		}
		
		//CATEGORIES Video Filters
		if ( $filter == 'categories' ) {
			$value = isset( $GET['video_cats'] ) ? json_decode( urldecode( stripslashes( $GET['video_cats'] ) ), true ) : array();
			?>
            <div
                    class="wpvr_filter_dropdown <?php echo count( $value ) == 0 || $value == array( '' ) ? '' : 'active'; ?> wpvr_tipso noborder"
                    title="<?php echo __( 'Filter by video category', WPVR_LANG ); ?>"
            >
				<?php echo wpvr_render_dropdown( array(
					'name'        => "video_cats",
					'placeholder' => ___( 'All categories', false ) . ' ...',
					'options'     => wpvr_get_categories( true ),
					'maxItems'    => 25,
					'wrap_class'  => 'wpvr_filter_dropdown_wrap ',
					'value'       => $value,
				) ); ?>

            </div>
			<?php return ob_get_clean();
		}
		
		//WPVR ONLY  Video Filters
		if ( $filter == 'wpvr_only' ) {
			
			$value = isset( $GET['wpvr_only'] ) ? json_decode( urldecode( stripslashes( $GET['wpvr_only'] ) ), true ) : array();
			
			?>
            <div
                    class="wpvr_filter_dropdown <?php echo count( $value ) == 0 || $value == array( '' ) ? '' : 'active'; ?> wpvr_tipso noborder"
                    title="<?php echo __( 'Filter by video type', WPVR_LANG ); ?>"
            >
				<?php echo wpvr_render_dropdown( array(
					'name'        => "wpvr_only",
					'placeholder' => ___( 'All videos', false ) . ' ...',
					'options'     => array(
						'1'  => ___( 'WPVR Videos Only', false ),
						'-1' => ___( 'Non WPVR Videos Only', false ),
					),
					'maxItems'    => 1,
					'wrap_class'  => 'wpvr_filter_dropdown_wrap ',
					'value'       => $value,
				) ); ?>

            </div>
			<?php return ob_get_clean();
			
		}
		
		//SOURCES Video Filters
		if ( $filter == 'sources' ) {
			$value = isset( $GET['video_source'] ) ? json_decode( urldecode( stripslashes( $GET['video_source'] ) ), true ) : array();
			?>
            <div
                    class="wpvr_filter_dropdown <?php echo count( $value ) == 0 || $value == array( '' ) ? '' : 'active'; ?> wpvr_tipso noborder"
                    title="<?php echo __( 'Filter by sources', WPVR_LANG ); ?>"
            >
				<?php echo wpvr_render_dropdown( array(
					'name'        => "video_source",
					'placeholder' => ___( 'All sources', false ) . ' ...',
					'options'     => wpvr_get_sources_options(),
					'maxItems'    => 25,
					'wrap_class'  => 'wpvr_filter_dropdown_wrap ',
					'value'       => $value,
				) ); ?>

            </div>
			<?php return ob_get_clean();
		}
		
		//AUTHORS Video Filters
		if ( $filter == 'authors' ) {
			$authors = wpvr_get_users( array(
				'key'      => 'user_id',
				'restrict' => $wpvr_options['restrictVideos'],
				'name'     => 'full_name',
				'order'    => 'ASC',
			) );
			$value   = isset( $GET['video_author'] ) ? json_decode( urldecode( stripslashes( $GET['video_author'] ) ), true ) : array();
			?>
            <div
                    class="wpvr_filter_dropdown <?php echo count( $value ) == 0 || $value == array( '' ) ? '' : 'active'; ?> wpvr_tipso noborder"
                    title="<?php echo __( 'Filter by author', WPVR_LANG ); ?>"
            >
				<?php echo wpvr_render_dropdown( array(
					'name'        => "video_author",
					'placeholder' => ___( 'All authors', false ) . ' ...',
					'options'     => $authors,
					'maxItems'    => 25,
					'wrap_class'  => 'wpvr_filter_dropdown_wrap ',
					'value'       => $value,
				) ); ?>

            </div>
			<?php return ob_get_clean();
		}
		
		//IDS Video Filters
		if ( $filter == 'ids' ) {
			$value = isset( $GET['video_ids'] ) && ! empty( $_GET['video_ids'] ) ? $GET['video_ids'] : false;
			//d( $value );
			?>
            <div
                    class="wpvr_filter_input <?php echo $value === false ? '' : 'active'; ?> wpvr_tipso noborder"
                    title="<?php echo __( 'Filter by video ID', WPVR_LANG ); ?>"
            >
                <input
                        type="text"
                        name="video_ids"
                        class="wpvr_filter_dropdown_input"
                        placeholder="<?php echo __( 'All video ids', WPVR_LANG ) . ' ...'; ?>"
                        value="<?php echo $value; ?>"
                />

            </div>
			<?php return ob_get_clean();
		}
	}
	
	function wpvr_render_source_filters( $filter, $GET ) {
		global $wpvr_vs;
		ob_start();
		
		//Getting TYPES Source Filters
		if ( $filter == 'types' ) {
			$typesArray = array();
			foreach ( (array) $wpvr_vs as $vs ) {
				foreach ( (array) $vs['types'] as $vs_type ) {
					if ( $vs_type['global_id'] == 'group_' ) {
						$label = 'Group';
					} else {
						$label = ucfirst( $vs_type['global_id'] );
					}
					$typesArray[ $vs_type['global_id'] ] = $label;
				}
			}
			
			$value = isset( $GET['source_type'] ) ? json_decode( urldecode( stripslashes( $GET['source_type'] ) ), true ) : array();
			?>
            <div
                    class="wpvr_filter_dropdown <?php echo count( $value ) == 0 || $value == array( '' ) ? '' : 'active'; ?> wpvr_tipso noborder"
                    title="<?php echo __( 'Filter by source type', WPVR_LANG ); ?>"
            >
				<?php echo wpvr_render_dropdown( array(
					'name'        => "source_type",
					'placeholder' => ___( 'Show all types', false ) . ' ...',
					'options'     => $typesArray,
					'maxItems'    => 25,
					'wrap_class'  => 'wpvr_filter_dropdown_wrap ',
					'value'       => $value,
				) ); ?>

            </div>
			<?php return ob_get_clean();
		}
		
		
		//Getting SERVICES Source Filters
		if ( $filter == 'services' ) {
			global $wpvr_vs;
			$services_options = array();
			foreach ( (array) $wpvr_vs as $value => $vs ) {
				// d( $vs );
				if ( isset( $vs['skipThis'] ) ) {
					continue;
				}
				$services_options[ $vs['id'] ] = $vs['label'];
			}
			
			$value = isset( $GET['source_service'] ) ? json_decode( urldecode( stripslashes( $GET['source_service'] ) ), true ) : array();
			?>
            <div
                    class="wpvr_filter_dropdown <?php echo count( $value ) == 0 || $value == array( '' ) ? '' : 'active'; ?> wpvr_tipso noborder"
                    title="<?php echo __( 'Filter by video service', WPVR_LANG ); ?>"
            >
				<?php echo wpvr_render_dropdown( array(
					'name'        => "source_service",
					'placeholder' => ___( 'Show all services', false ) . ' ...',
					'options'     => $services_options,
					'maxItems'    => 25,
					'wrap_class'  => 'wpvr_filter_dropdown_wrap ',
					'value'       => $value,
				) ); ?>

            </div>
			<?php return ob_get_clean();
		}
		
		//Getting FOLDERS Source Filters
		if ( $filter == 'folders' ) {
			$value = isset( $GET['source_folder'] ) ? json_decode( urldecode( stripslashes( $GET['source_folder'] ) ), true ) : array();
			?>
            <div
                    class="wpvr_filter_dropdown <?php echo count( $value ) == 0 || $value == array( '' ) ? '' : 'active'; ?> wpvr_tipso noborder"
                    title="<?php echo __( 'Filter by source folder', WPVR_LANG ); ?>"
            >
				<?php echo wpvr_render_dropdown( array(
					'name'        => "source_folder",
					'placeholder' => ___( 'Show all folders', false ) . ' ...',
					'options'     => wpvr_get_folders_simple(),
					'maxItems'    => 25,
					'wrap_class'  => 'wpvr_filter_dropdown_wrap ',
					'value'       => $value,
				) ); ?>

            </div>
			<?php return ob_get_clean();
		}
		
		//Getting STATUSES Source Filters
		if ( $filter == 'status' ) {
			$value = isset( $GET['source_status'] ) ? json_decode( urldecode( stripslashes( $GET['source_status'] ) ), true ) : array();
			?>
            <div
                    class="wpvr_filter_dropdown <?php echo count( $value ) == 0 || $value == array( '' ) ? '' : 'active'; ?> wpvr_tipso noborder"
                    title="<?php echo __( 'Filter by source status', WPVR_LANG ); ?>"
            >
				<?php echo wpvr_render_dropdown( array(
					'name'        => "source_status",
					'placeholder' => ___( 'Show all sources', false ) . ' ...',
					'options'     => array(
						'on'  => ___( 'Active sources only', false ),
						'off' => ___( 'Inactive sources only', false ),
					),
					'maxItems'    => 1,
					'wrap_class'  => 'wpvr_filter_dropdown_wrap ',
					'value'       => $value,
				) ); ?>

            </div>
			<?php return ob_get_clean();
		}
		
		//Getting CATEGORIES Source Filters
		if ( $filter == 'categories' ) {
			$value = isset( $GET['source_cats'] ) ? json_decode( urldecode( stripslashes( $GET['source_cats'] ) ), true ) : array();
			?>
            <div
                    class="wpvr_filter_dropdown <?php echo count( $value ) == 0 || $value == array( '' ) ? '' : 'active'; ?> wpvr_tipso noborder"
                    title="<?php echo __( 'Filter by posting category', WPVR_LANG ); ?>"
            >
				<?php echo wpvr_render_dropdown( array(
					'name'        => "source_cats",
					'placeholder' => ___( 'Show all categories', false ) . ' ...',
					'options'     => wpvr_get_categories( true ),
					'maxItems'    => 25,
					'wrap_class'  => 'wpvr_filter_dropdown_wrap ',
					'value'       => $value,
				) ); ?>

            </div>
			<?php return ob_get_clean();
		}
		
		//Getting AUTHORS Source Filters
		if ( $filter == 'authors' ) {
			global $wpvr_options;
			$value   = isset( $GET['source_author'] ) ? json_decode( urldecode( stripslashes( $GET['source_author'] ) ), true ) : array();
			$authors = wpvr_get_users( array(
				'key'      => 'user_id',
				'restrict' => $wpvr_options['restrictVideos'],
				'name'     => 'full_name',
				'order'    => 'ASC',
			) );
			?>
            <div
                    class="wpvr_filter_dropdown <?php echo count( $value ) == 0 || $value == array( '' ) ? '' : 'active'; ?> wpvr_tipso noborder"
                    title="<?php echo __( 'Filter by posting author', WPVR_LANG ); ?>"
            >
				<?php echo wpvr_render_dropdown( array(
					'name'        => "source_author",
					'placeholder' => ___( 'Show all posting authors', false ) . ' ...',
					'options'     => $authors,
					'maxItems'    => 25,
					'wrap_class'  => 'wpvr_filter_dropdown_wrap ',
					'value'       => $value,
				) ); ?>

            </div>
			<?php return ob_get_clean();
			
		}
		
		return '';
	}
	
	function wpvr_import_sample_sources( $service ) {
		$sample_file = WPVR_PATH . 'assets/json/' . $service . '.json';
		if ( ! file_exists( $sample_file ) ) {
			return wpvr_get_json_response( null, 0, __( 'Could not fine the service sample file.', WPVR_LANG ) );
		}
		$json = (array) json_decode( file_get_contents( $sample_file ) );
		if ( ! isset( $json['version'] ) || ! isset( $json['data'] ) || ! isset( $json['type'] ) || $json['type'] != 'sources' ) {
			return wpvr_get_json_response( 0, 0, 'Could not import sample sources.', 0 );
		}
		if ( count( $json['data'] ) == 0 ) {
			return wpvr_get_json_response( 0, 0, 'No sample source found.', 0 );
		}
		$count   = 0;
		$sources = $json['data'];
		$total   = count( $sources );
		d( $sources );
		foreach ( (array) $sources as $source ) {
			wpvr_import_source( $source, true );
			$count ++;
		}
		
		
		return wpvr_get_json_response( null, 1,
			$count . '/' . $total . ' ' . ___( 'Sample sources imported successfully.', true ),
			$total
		);
	}
	
	function wpvr_get_cron_url( $query = '' ) {
		global $wpvr_cron_token;
		
		return get_home_url( null, '/' . WPVR_CRON_ENDPOINT . '/' . $wpvr_cron_token . '/' . $query );
	}
	
	//@Unused
	function wpvr_set_activation_overwritten( $product_slug = '', $activation ) {
		$wpvr_activations                  = get_option( 'wpvr_activations' );
		$wpvr_activations[ $product_slug ] = $activation;
		update_option( 'wpvr_activations', $wpvr_activations );
	}
	
	function wpvr_run_multiple_db_queries( $queries = array(), $fetch_results = false ) {
		global $wpdb;
		
		$mysqli = $wpdb->dbh;
		$timer = wpvr_chrono_time();
		$db = array(
			'count'   => array( '@total' => 0, ),
			'results' => array(),
		);
		//For some reason mysqli does not reach the last query.
		// As a hack, we're adding one last one.
		// $queries[] = "select ID from {$wpdb->posts} where ID = '0'";
		$queries[] = "show databases;";
		
		
		//Build our queries_string
		$queries_string = implode( ";\n", $queries ) . "";
		
		
		//collect Types
		$queries_types = array();
		foreach ( (array) $queries as $j => $query ) {
			if ( strpos( strtolower( substr( $query, 0, 20 ) ), 'select' ) !== false ) {
				$queries_types[ $j ] = 'select';
			} elseif ( strpos( strtolower( substr( $query, 0, 20 ) ), 'insert' ) !== false ) {
				$queries_types[ $j ] = 'insert';
			} elseif ( strpos( strtolower( substr( $query, 0, 20 ) ), 'update' ) !== false ) {
				$queries_types[ $j ] = 'update';
			} elseif ( strpos( strtolower( substr( $query, 0, 20 ) ), 'delete' ) !== false ) {
				$queries_types[ $j ] = 'delete';
			} elseif ( strpos( strtolower( substr( $query, 0, 20 ) ), 'show' ) !== false ) {
				$queries_types[ $j ] = 'show';
			}
		}
  
		//Execute Big Query
		$mysqli->multi_query( $queries_string );
		
		$k = 0;
  
		do {
   
			$mysqli->use_result();
		    
		    $query_type = isset( $queries_types[ $k ] ) ? $queries_types[ $k ] : 'unknown';
			
			if ( $query_type === 'show' ) {
				continue;
			}
			
			if ( ! isset( $db['count'][ $query_type ] ) ) {
				$db['count'][ $query_type ] = 0;
			}
			$db['count']['@total']      += $mysqli->affected_rows;
			$db['count'][ $query_type ] += $mysqli->affected_rows;
			
			if ( $query_type == 'select' ) {
				if ( $result = $mysqli->store_result() ) {
					$db['count']['@total']      += $result->num_rows;
					$db['count'][ $query_type ] += $result->num_rows;
					if ( $fetch_results === true ) {
						while ( $row = $result->fetch_assoc() ) {
							if ( ! isset( $db['results'][ $k ] ) ) {
								$db['results'][ $k ] = array();
							}
							$db['results'][ $k ][] = $row;
						}
					}
				}
				
				if ( $result !== null ) {
					$result->free();
				}
			}
			
			$k ++;
		} while ( $mysqli->next_result() && $mysqli->more_results() );
		
		
        // Another hack to allow mysqli to be used in a loop
        //Otherwise we get this error on 50% of the processed items:
        //Commands out of sync; you can't run this command now
        $wpdb->close();
		$wpdb->db_connect();
		
		$db['exec_time'] = wpvr_chrono_time( $timer );
		
		return $db;
	}