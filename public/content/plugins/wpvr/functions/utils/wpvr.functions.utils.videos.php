<?php
	//@URGENT
	function wpvr_is_imported_video( $post_id = null , $wpvr_imported = null ) {
		global $wpvr_imported;
		
		if ( $post_id === null ) {
			global $post;
			if ( ! isset( $post->ID ) ) {
				return 0;
			}
			
			$post_id = $post->ID;
		}
		if( $wpvr_imported === null ) {
			$wpvr_imported = wpvr_get_imported_videos();
		}
		//d( $wpvr_imported );
		foreach ( (array) $wpvr_imported as $service => $service_videos ) {
			if ( ! is_bool( $service_videos ) && array_search( $post_id, $service_videos ) !== false  ) {
				return true;
			}
		}
		
		return false;
	}
	
	function wpvr_islive_video( $post_id = null ) {
		global $post;
		if ( $post_id === null && isset( $post->ID ) ) {
			$post_id = $post->ID;
		}
		
		$is_live = get_post_meta( $post_id, 'wpvr_video_is_live', true );
		
		return $is_live == 'live' ? true : false;
	}
	
	function wpvr_is_hd( $video ) {
		if ( $video['service'] == 'youtube' ) {
			if ( strpos( $video['hqthumb'], 'maxres' ) !== false ) {
				return true;
			} else {
				return false;
			}
		} else {
			if ( $video['hqthumb'] !== false ) {
				return true;
			} else {
				return false;
			}
		}
	}
	
	function wpvr_get_video_thumbnail( $post_id, $size, $external_thumb_url = '' ) {
		$using_external_thumb = get_post_meta( $post_id, 'wpvr_video_using_external_thumbnail', true );
		
		if ( $using_external_thumb != '' ) {
			return $using_external_thumb;
		}
		
		if ( ! has_post_thumbnail( $post_id ) ) {
			return false;
		}
		$upload_dir = wp_upload_dir();
		$file_src   = wp_get_attachment_image_src( get_post_thumbnail_id( $post_id ), $size );
		if ( ! isset( $file_src[0] ) ) {
			return false;
		}
		$file_url  = $file_src[0];
		$file_path = str_replace( $upload_dir ['url'], $upload_dir['path'], $file_url );
		
		$filesize = @filesize( $file_path );
		if ( $filesize !== false && $filesize == 0 ) {
			return false;
		}
		
		
		return apply_filters( 'wpvr_return_ssl_ready_url', $file_url );
	}
	
	/* Imported Videos Functions */
	function wpvr_flush_all_imported_videos() {
		global $wpdb, $wpvr_vs;
		
		$wpvr_imported = array();
		
		if ( ! is_array( $wpvr_vs ) || count( $wpvr_vs ) == 0 ) {
			// return get_option( 'wpvr_imported' );
			return $wpvr_imported;
		}
		
		$sql
			= "
			select
				P.ID as post_id,
				GROUP_CONCAT( DISTINCT if(M.meta_key = 'wpvr_video_id' , M.meta_value , NULL ) SEPARATOR '') as video_id,
				GROUP_CONCAT( DISTINCT if(M.meta_key = 'wpvr_video_service' , M.meta_value , NULL ) SEPARATOR '') as video_service
			FROM
				$wpdb->posts P
				INNER JOIN $wpdb->postmeta M ON P.ID = M.post_id
			WHERE
				P.post_type IN " . wpvr_cpt_get_handled_types( 'sql' ) . "
			GROUP BY
				P.ID
			HAVING
			    video_id != ''
		";
		
		$videos = $wpdb->get_results( $sql, OBJECT );
		
		foreach ( (array) $wpvr_vs as $vs_id => $vs ) {
			$wpvr_imported[ $vs_id ] = array();
		}
		
		foreach ( (array) $videos as $video ) {
			if ( $video->video_service == '' ) {
				$video->video_service = 'youtube';
			}
			
			if ( $video->video_id != '' ) {
				$wpvr_imported[ $video->video_service ][ $video->video_id ] = $video->post_id;
			}
		}
		
		$wpvr_imported = apply_filters( 'wpvr_extend_flushed_imported_videos', $wpvr_imported );
		
		update_option( 'wpvr_imported', $wpvr_imported );
		
		return $wpvr_imported;
	}
	
	function wpvr_flush_imported_videos( $args = array() ) {
		global $wpdb, $wpvr_vs;
		
		$args = wp_parse_args( $args, array(
			'video_ids' => false,
			'post_ids'  => false,
			'services'  => false,
		) );
		
		$wpvr_imported = wpvr_get_imported_videos();
		
		
		if ( $args['post_ids'] !== false && count( $args['post_ids'] ) != 0 ) {
			$condition = " AND post_id IN ('" . implode( "','", $args['post_ids'] ) . "') ";
		} elseif ( $args['video_ids'] !== false && count( $args['video_ids'] ) != 0 ) {
			$condition = " AND video_id IN ('" . implode( "','", $args['video_ids'] ) . "') ";
		} elseif ( $args['services'] !== false && count( $args['video_ids'] ) != 0 ) {
			$condition = " AND video_service IN ('" . implode( "','", $args['services'] ) . "') ";
		} else {
			$condition = "";
		}
		
		$sql
			    = "
			select
				P.ID as post_id,
				GROUP_CONCAT( DISTINCT if(M.meta_key = 'wpvr_video_id' , M.meta_value , NULL ) SEPARATOR '') as video_id,
				GROUP_CONCAT( DISTINCT if(M.meta_key = 'wpvr_video_service' , M.meta_value , NULL ) SEPARATOR '') as video_service
			FROM
				$wpdb->posts P
				INNER JOIN $wpdb->postmeta M ON P.ID = M.post_id
			WHERE
				1
				AND P.post_type IN " . wpvr_cpt_get_handled_types( 'sql' ) . "
			GROUP BY
				P.ID
			HAVING
			    1
			    AND video_id != ''
			    {$condition}
		";
		$videos = $wpdb->get_results( $sql, OBJECT );
		
		foreach ( (array) $videos as $video ) {
			//d( $video );
			if ( ! isset( $wpvr_imported[ $video->video_service ] ) ) {
				continue;
			}
			if ( ! isset( $wpvr_imported[ $video->video_service ][ $video->video_id ] ) ) {
				continue;
			}
			
			unset( $wpvr_imported[ $video->video_service ][ $video->video_id ] );
			
		}
		
		$wpvr_imported = apply_filters( 'wpvr_extend_flushed_imported_videos', $wpvr_imported );
		
		update_option( 'wpvr_imported', $wpvr_imported );
		
		return $wpvr_imported;
		
	}
	
	function wpvr_get_imported_videos() {
		$wpvr_imported = get_option( 'wpvr_imported' );
		
		if ( $wpvr_imported == '' ) {
			$wpvr_imported = wpvr_flush_all_imported_videos();
		}
		
		return apply_filters( 'wpvr_extend_imported_videos', $wpvr_imported );
	}
	
	function wpvr_add_imported_videos( $videos = array() ) {
		$wpvr_imported = wpvr_get_imported_videos();
		
		foreach ( (array) $videos as $video ) {
			
			
			if ( ! isset( $wpvr_imported[ $video['video_service'] ] ) ) {
				$wpvr_imported[ $video['video_service'] ] = array();
			}
			
			$wpvr_imported[ $video['video_service'] ][ $video['video_id'] ] = $video['post_id'];
			
		}
		
		update_option( 'wpvr_imported', $wpvr_imported );
		
		return true;
	}
	
	/* Deferred Videos Functions */
	function wpvr_get_deferred_videos( $post_type = null ) {
		$wpvr_deferred = get_option( 'wpvr_deferred' );
		
		if ( $wpvr_deferred == '' ) {
			return array();
		}
		
		if ( $post_type == null ) {
			return apply_filters( 'wpvr_extend_deferred_videos', $wpvr_deferred );
		}
		$filtered_deferred_videos = array();
		foreach ( (array) $wpvr_deferred as $video ) {
			if ( isset( $video['postType'] ) && $video['postType'] == $post_type ) {
				$filtered_deferred_videos[] = $video;
			}
		}
		
		return apply_filters( 'wpvr_extend_deferred_videos', $filtered_deferred_videos );
	}
	
	/* Unwanted Videos Functions */
	function wpvr_get_full_unwanted_videos( $post_type = null ) {
		$wpvr_unwanted = get_option( 'wpvr_unwanted' );
		
		if ( $wpvr_unwanted == '' ) {
			return array();
		}
		
		if ( $post_type == null ) {
			return apply_filters( 'wpvr_extend_unwanted_videos', $wpvr_unwanted );
		}
		
		// d( $wpvr_unwanted );
		
		$filtered_unwanted_videos = array();
		foreach ( (array) $wpvr_unwanted as $video ) {
			if ( ! isset( $video['postType'] ) || $video['postType'] === null ) {
				$video['postType'] = '*';
			}
			if ( $video['postType'] == $post_type || $video['postType'] == '*' ) {
				$filtered_unwanted_videos[] = $video;
			}
		}
		
		return apply_filters( 'wpvr_extend_unwanted_videos', $filtered_unwanted_videos );
	}
	
	function wpvr_add_video_unwanted( $video, $source_id = false ) {
		
		//Global Scope
		if ( $source_id == false ) {
			
			global $wpvr_unwanted, $wpvr_unwanted_ids;
			
			if ( ! isset( $wpvr_unwanted_ids[ $video['service'] ][ $video['id'] ] ) ) {
				$wpvr_unwanted[]                                        = $video;
				$wpvr_unwanted_ids[ $video['service'] ][ $video['id'] ] = 'unwanted';
			}
			
			
			update_option( 'wpvr_unwanted', $wpvr_unwanted );
			update_option( 'wpvr_unwanted_ids', $wpvr_unwanted_ids );
			
			return $video;
		}
		
		$source_unwanted     = get_post_meta( $source_id, 'wpvr_source_unwanted', true );
		$source_unwanted_ids = get_post_meta( $source_id, 'wpvr_source_unwanted_ids', true );
		
		if ( $source_unwanted == '' ) {
			$source_unwanted = array();
		}
		
		if ( $source_unwanted_ids == '' ) {
			$source_unwanted_ids = array();
		}
		
		if ( ! isset( $wpvr_unwanted_ids[ $video['service'] ][ $video['id'] ] ) ) {
			$source_unwanted[]                                        = $video;
			$source_unwanted_ids[ $video['service'] ][ $video['id'] ] = 'unwanted';
		}
		
		update_post_meta( $source_id, 'wpvr_source_unwanted', $source_unwanted );
		update_post_meta( $source_id, 'wpvr_source_unwanted_ids', $source_unwanted_ids );
		
		
		return $video;
	}
	
	function wpvr_get_unwanted_videos( $source_ids = false, $ids_only = false, $post_type = null, $search_term = '' ) {
		if ( $post_type !== null && ! is_array( $post_type ) ) {
			$post_type = array( $post_type );
		}
		// d( $post_type );
		
		
		if ( $source_ids === false ) {
			
			if ( $ids_only ) {
				$wpvr_unwanted_ids = get_site_option( 'wpvr_unwanted_ids' );
				
				$results = $wpvr_unwanted_ids == '' ? array() : $wpvr_unwanted_ids;
				
				return apply_filters( 'wpvr_extend_unwanted_videos_ids', $results );
			}
			
			$wpvr_unwanted = get_option( 'wpvr_unwanted' );
			//d( $wpvr_unwanted );
			$wpvr_unwanted = $wpvr_unwanted == '' ? array() : $wpvr_unwanted;
			
			if ( $post_type == null ) {
				$filtered_unwanted_videos = array();
				foreach ( (array) $wpvr_unwanted as $video ) {
					if ( ! isset( $video['postType'] ) || $video['postType'] === null ) {
						$video['postType'] = '*';
					}
					$filtered_unwanted_videos[] = $video;
					
					
				}
				
				//d( $filtered_unwanted_videos );
				return apply_filters( 'wpvr_extend_unwanted_videos', $filtered_unwanted_videos );
			}
			
			$filtered_unwanted_videos = array();
			foreach ( (array) $wpvr_unwanted as $video ) {
				if ( ! isset( $video['postType'] ) || $video['postType'] === null ) {
					$video['postType'] = '*';
				}
				if ( in_array( $video['postType'], $post_type ) || $video['postType'] == '*' ) {
					$filtered_unwanted_videos[] = $video;
				}
				
				
			}
			
			return apply_filters( 'wpvr_extend_unwanted_videos', $filtered_unwanted_videos );
			
		}
		
		if ( $source_ids == 'all' ) {
			$q = new WP_Query( array(
				'post_type'   => WPVR_SOURCE_TYPE,
				'post_status' => 'publish',
				'fields'      => 'ids',
				'nopaging'    => true,
			) );
			if ( $q->found_posts == 0 ) {
				$source_ids = array();
			} else {
				$source_ids = $q->posts;
			}
		}
		
		
		$results = array();
		
		foreach ( (array) $source_ids as $source_id ) {
			if ( $ids_only === true ) {
				$source_unwanted_ids = get_post_meta( $source_id, 'wpvr_source_unwanted_ids', true );
				if ( $source_unwanted_ids == '' ) {
					$source_unwanted_ids = array();
				}
				$results = array_merge( $results, $source_unwanted_ids );
				
				return apply_filters( 'wpvr_extend_unwanted_videos_ids', $results );
				
			} else {
				$source_unwanted = get_post_meta( $source_id, 'wpvr_source_unwanted', true );
				if ( $source_unwanted == '' ) {
					$source_unwanted = array();
				}
				$results = array_merge( $results, $source_unwanted );
			}
		}
		
		
		$filtered_unwanted_videos = array();
		foreach ( (array) $results as $video ) {
			
			if (
				$search_term != ''
				&& strpos( strtolower( $video['title'] ) , strtolower( $search_term) ) === false
				&& strpos( strtolower( $video['description'] ) , strtolower( $search_term) ) === false
			) {
				continue;
			}
			
			if ( $ids_only === false && ! isset( $video['postType'] ) || $video['postType'] === null ) {
				$video['postType'] = '*';
			}
			if ( $post_type == null ) {
				$filtered_unwanted_videos[] = $video;
			} elseif ( in_array( $video['postType'], $post_type ) || $video['postType'] == '*' ) {
				$filtered_unwanted_videos[] = $video;
			}
		}
		
		return apply_filters( 'wpvr_extend_unwanted_videos', $filtered_unwanted_videos );
	}
	
	function wpvr_remove_global_unwanted_video( $videos ) {
		
		$count = 0;
		
		$wpvr_unwanted     = get_option( 'wpvr_unwanted' );
		$wpvr_unwanted_ids = get_option( 'wpvr_unwanted_ids' );
		
		foreach ( (array) $wpvr_unwanted as $k => $vid ) {
			if ( isset( $videos[ $vid['id'] ] ) ) {
				$count ++;
				unset( $wpvr_unwanted[ $k ] );
				unset( $wpvr_unwanted_ids[ $vid['service'] ][ $vid['id'] ] );
			}
		}
		
		// _d( $wpvr_unwanted_ids );
		
		update_option( 'wpvr_unwanted', $wpvr_unwanted );
		update_option( 'wpvr_unwanted_ids', $wpvr_unwanted_ids );
		
		return $count;
		
	}
	
	function wpvr_remove_source_unwanted_video( $videos ) {
		
		$count = 0;
		
		foreach ( (array) $videos as $source_id => $source_videos ) {
			
			$source_unwanted     = get_post_meta( $source_id, 'wpvr_source_unwanted', true );
			$source_unwanted_ids = get_post_meta( $source_id, 'wpvr_source_unwanted_ids', true );
			
			// _d( $source_unwanted );
			
			foreach ( (array) $source_unwanted as $k => $vid ) {
				if ( isset( $videos[ $source_id ][ $vid['id'] ] ) ) {
					$count ++;
					unset( $source_unwanted[ $k ] );
					unset( $source_unwanted_ids[ $vid['service'] ][ $vid['id'] ] );
				}
			}
			
			update_post_meta( $source_id, 'wpvr_source_unwanted', $source_unwanted );
			update_post_meta( $source_id, 'wpvr_source_unwanted_ids', $source_unwanted_ids );
			
		}
		
		
		return $count;
		
	}
	
	function wpvr_count_unwanted_videos( $services ) {
		$count = 0;
		foreach ( (array) $services as $service ) {
			$count += count( $service );
		}
		
		return $count;
	}
	
	function wpvr_create_duplicates( $video_id, $video_service = 'youtube', $number = 10 ) {
		global $wpvr_force_duplicates;
		
		$wpvr_force_duplicates = true;
		
		
		$videoItem = wpvr_get_video_single_data( $video_id, $video_service );
		if ( $videoItem == false ) {
			echo "Could not find that video";
			
			return false;
		}
		$done = array();
		for ( $i = 0; $i < $number; $i ++ ) {
			//$videoItem['title'] .= '( dup #'.$i.') ';
			
			$videoItem['postDate']    = 'updated';
			$videoItem['autoPublish'] = 'on';
			$videoItem['postAppend']  = 'off';
			$videoItem['description'] = "This is a duplicate post of $video_id ." . '( dup #' . $i . ') ';
			$videoItem['sourceId']    = 0;
			$videoItem['sourceName']  = "Duplicate Maker";
			$videoItem['sourceType']  = "search_yt";
			$videoItem['source_tags'] = array();
			$videoItem['postCats']    = array();
			$videoItem['owner']       = get_current_user_id();
			$videoItem['postAuthor']  = get_current_user_id();
			
			$done[] = wpvr_add_video( $videoItem );
		}
		
		return $done;
	}
	
	function wpvr_update_dynamic_video_views( $post_id, $new_views ) {
		$wpvr_fillers = get_option( 'wpvr_fillers' );
		$count        = 0;
		if ( ! is_array( $wpvr_fillers ) || count( $wpvr_fillers ) == 0 ) {
			return 0;
		}
		foreach ( (array) $wpvr_fillers as $filler ) {
			if ( $filler['from'] == 'wpvr_dynamic_views' ) {
				update_post_meta( $post_id, $filler['to'], $new_views );
				$count ++;
			}
		}
		
		return $count;
	}