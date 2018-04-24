<?php
	
	//@Unused
	function wpvr_curl_check_remote_file_exists( $url ) {
		$ch = curl_init( $url );
		curl_setopt( $ch, CURLOPT_NOBODY, true );
		curl_exec( $ch );
		if ( curl_getinfo( $ch, CURLINFO_HTTP_CODE ) == 200 ) {
			$status = true;
		} else {
			$status = false;
		}
		curl_close( $ch );
		
		return $status;
	}
	
	function wpvr_extract_data_from_url( $html, $searches = array() ) {
		$results = array();
		if ( count( $searches ) == 0 ) {
			return array();
		}
		foreach ( (array) $searches as $s ) {
			
			if ( $s['target_name'] === false ) {
				if ( $s['marker_double_quotes'] === true ) {
					$marker = '<' . $s['tag'] . ' ' . $s['marker_name'] . '="' . $s['marker_value'] . '"';
				} else {
					$marker = "<" . $s['tag'] . " " . $s['marker_name'] . "='" . $s['marker_value'] . "'";
				}
				$x = explode( $marker, $html );
				//d($x );
				if ( $x == $html ) {
					$results[ $s['target'] ] = false;
					continue;
				}
				
				$z = array_pop( $x );
				$y = explode( '</' . $s['tag'] . '>', $z );
				
				$tv                      = $y[0];
				$tv                      = str_replace( array( '<', '>', ',', ' ' ), '', $tv );
				$results[ $s['target'] ] = $tv;
				continue;
			}
			
			
			if ( $s['marker_double_quotes'] === true ) {
				$marker = '' . $s['marker_name'] . '="' . $s['marker_value'] . '"';
			} else {
				$marker = "" . $s['marker_name'] . "='" . $s['marker_value'] . "'";
			}
			
			$x = explode( $marker, $html );
			//d( $marker );d( $x );
			
			if ( $x[0] == $html ) {
				$results[ $s['target'] ] = false;
				continue;
			}
			$y = explode( '<' . $s['tag'], $x[0] );
			if ( $y[0] == $x[0] ) {
				$results[ $s['target'] ] = false;
				continue;
			}
			$z = array_pop( $y );
			if ( $s['target_double_quotes'] === true ) {
				$target = '' . $s['target_name'] . '="';
			} else {
				$target = "" . $s['target_name'] . "='";
			}
			//d( $target);
			$w = explode( $target, $z );
			if ( $w == $z || ! isset( $w[1] ) ) {
				$results[ $s['target'] ] = false;
				continue;
			}
			
			$target_value            = str_replace( '"', "", $w[1] );
			$target_value            = str_replace( "'", "", $target_value );
			$results[ $s['target'] ] = $target_value;
		}
		
		return $results;
	}
	
	function wpvr_touch_remote_file( $url ) {
		$capi = wpvr_capi_remote_get( $url );
		if ( ! isset( $capi['status'] ) || $capi['status'] != 200 ) {
			return false;
		} else {
			return true;
		}
	}
	
	function wpvr_make_curl_request( $api_url = '', $api_args = array(), $curl_object = null, $debug = false, $curl_options = array(), $get_headers = false ) {
		
		$timer = wpvr_chrono_time();
		if ( $curl_object === null || ! is_resource( $curl_object ) ) {
			$curl_object = curl_init();
		}
		if ( is_array( $api_args ) && count( $api_args ) > 0 ) {
			$api_url .= '?' . http_build_query( $api_args );
		}
		//d( is_resource( $curl_object ) );
		curl_setopt( $curl_object, CURLOPT_URL, $api_url );
		curl_setopt( $curl_object, CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt( $curl_object, CURLOPT_RETURNTRANSFER, true );
		
		$headers = false;
		if ( $get_headers ) {
			curl_setopt( $curl_object, CURLOPT_HEADER, true );
			curl_setopt( $curl_object, CURLOPT_VERBOSE, true );
		} else {
			curl_setopt( $curl_object, CURLOPT_HEADER, false );
		}
		
		
		if ( $curl_options != array() ) {
			foreach ( (array) $curl_options as $key => $value ) {
				curl_setopt( $curl_object, $key, $value );
			}
		}
		
		$data = curl_exec( $curl_object );
		//d( $data );
		if ( $get_headers ) {
			$header_size = curl_getinfo( $curl_object, CURLINFO_HEADER_SIZE );
			$headers     = explode( "\n", substr( $data, 0, $header_size ) );
			$data        = substr( $data, $header_size );
		}
		
		if ( $debug === true ) {
			echo $data;
			d( $data );
			d( $api_url );
			d( $api_args );
		}
		$status = curl_getinfo( $curl_object, CURLINFO_HTTP_CODE );
		
		//curl_close( $curl_object );
		
		return array(
			'exec_time' => wpvr_chrono_time( $timer ),
			'status'    => $status,
			'data'      => $data,
			'error'     => curl_error( $curl_object ),
			'json'      => (array) wpvr_json_decode( $data ),
			'headers'   => $headers,
			'caller'    => array(
				'url'  => $api_url,
				'args' => $api_args,
			),
		);
	}
	
	function wpvr_render_video_permalink( $post = null, $permalink_structure = null, $post_link = null ) {
		
		
		if ( $post == null ) {
			global $post;
		}
		
		
		if ( $permalink_structure === false ) {
			return str_replace( '?' . $post->post_type . '=', '', $post_link );
		}
		
		if ( $permalink_structure == null ) {
			global $wp_rewrite;
			$permalink_structure = $wp_rewrite->permalink_structure;
		}
		
		$var_names = array(
			'%year%',
			'%monthnum%',
			'%day%',
			'%hour%',
			'%minute%',
			'%second%',
			'%post_id%',
			'%postname%',
			'%category%',
			'%author%',
		);
		$date      = DateTime::createFromFormat( 'Y-m-d H:i:s', $post->post_date_gmt, new DateTimeZone( 'UTC' ) );
		
		//Getting post category if needed
		$post_category = '';
		if ( strpos( $permalink_structure, '%category%' ) !== false ) {
			$post_categories = wp_get_post_categories( $post->ID, array( 'fields' => 'slugs' ) );
			if ( count( $post_categories ) != 0 && is_array( $post_categories ) ) {
				$post_category = $post_categories[0];
			}
		}
		
		if( $post_category == '' ){
			$post_category = 'uncategorized';
		}
		
		$var_values = array(
			$date->format( 'Y' ),
			$date->format( 'm' ),
			$date->format( 'd' ),
			$date->format( 'G' ),
			$date->format( 'i' ),
			$date->format( 's' ),
			$post->ID,
			$post->post_name,
			$post_category,
			get_the_author_meta( 'user_nicename', $post->post_author ),
		);
		$permalink  = WPVR_SITE_URL . str_replace( $var_names, $var_values, $permalink_structure );
		
		// d( $permalink );
		return $permalink;
		
	}
	
	function wpvr_download_attachment_image( $image_url = '', $image_title = '', $image_desc = '', $unique_id = '' ) {
		
		//if( WPVR_DISABLE_THUMBS_DOWNLOAD === TRUE ) return '';
		
		if ( $image_url == '' ) {
			return false;
		}
		if ( $unique_id == '' ) {
			$unique_id = md5( uniqid( rand(), true ) );
		}
		
		$upload_dir     = wp_upload_dir(); // Set upload folder
		$image_data
		                =  // Get image data
		$file_extension = pathinfo( $image_url, PATHINFO_EXTENSION );
		$fe             = explode( '?', $file_extension );
		$file_extension = $fe[0];
		if ( $file_extension == '' || $file_extension == null ) {
			$file_extension = 'jpg';
		}
		$filename = sanitize_file_name( $image_title );
		if ( preg_match( '/[^\x20-\x7f]/', $filename ) ) {
			$filename = md5( $filename );
		}
		$filename_ext = $filename . '.' . $file_extension;
		//ppg_set_debug( $filename_ext , TRUE);
		
		//if( ! file_exists( $filename ) ) {
		if ( wp_mkdir_p( $upload_dir['path'] ) ) {
			$file = $upload_dir['path'] . '/' . $filename_ext;
		} else {
			$file = $upload_dir['basedir'] . '/' . $filename_ext;
		}
		@file_put_contents(
			$file,
			apply_filters(
				'wpvr_extend_attachment_image_raw_content',
				@file_get_contents( $image_url )
			)
		);
		
		$wp_filetype = @wp_check_filetype( $filename_ext, null );
		$attachment  = array(
			'post_mime_type' => $wp_filetype['type'],
			'post_title'     => $filename . "-attachment",
			'post_name'      => sanitize_title( $image_title . "-attachment" ),
			'post_content'   => $image_desc,
			'post_excerpt'   => $filename,
			'post_status'    => 'inherit',
		);
		
		$attach_id = @wp_insert_attachment( $attachment, $file );
		update_post_meta( $attach_id, '_wp_attachment_image_alt', $filename );
		@require_once( ABSPATH . 'wp-admin/includes/image.php' );
		$attach_data = @wp_generate_attachment_metadata( $attach_id, $file );
		@wp_update_attachment_metadata( $attach_id, $attach_data );
		
		//wpvr_set_debug( $file );
		
		return array(
			'file'         => $file,
			'att'          => $attachment,
			'att_id'       => $attach_id,
			'att_metadata' => $attach_data,
		);
		
	}
	
	function wpvr_download_featured_image( $image_url = '', $fallback_image_url = '', $image_title = '', $image_desc = '', $post_id = '', $unique_id = '', $downloadThumb = true ) {
		// global $wpvr_options;
		
		// if ( $wpvr_options['downloadThumb'] === true ) {
		if ( $downloadThumb === false ) {
			
			update_post_meta( $post_id, 'wpvr_video_using_external_thumbnail_info', getimagesize( $image_url ) );
			update_post_meta( $post_id, 'wpvr_video_using_external_thumbnail', $image_url );
			
			return false;
		}
		
		if ( $image_url == '' ) {
			return false;
		}
		if ( $unique_id == '' ) {
			$unique_id = md5( uniqid( rand(), true ) );
		}
		
		if ( $image_url === false || wpvr_touch_remote_file( $image_url ) === false ) {
			$image_url = $fallback_image_url;
		}
		
		$upload_dir     = wp_upload_dir(); // Set upload folder
		$image_data
		                =  // Get image data
		$file_extension = pathinfo( $image_url, PATHINFO_EXTENSION );
		$fe             = explode( '?', $file_extension );
		$file_extension = $fe[0];
		if ( $file_extension == '' || $file_extension == null ) {
			$file_extension = 'jpg';
		}
		$filename = sanitize_file_name( $image_title );
		if ( preg_match( '/[^\x20-\x7f]/', str_replace( array( '-', '—' ), '', $filename ) ) ) {
			$filename = md5( $filename );
		}
		
		$filename = apply_filters( 'wpvr_extend_video_thumbnail_filename', $filename, $post_id );
		
		
		$filename_ext = $filename . '.' . $file_extension;
		//ppg_set_debug( $filename_ext , TRUE);
		
		//if( ! file_exists( $filename ) ) {
		if ( wp_mkdir_p( $upload_dir['path'] ) ) {
			$file = $upload_dir['path'] . '/' . $filename_ext;
		} else {
			$file = $upload_dir['basedir'] . '/' . $filename_ext;
		}
		
		$existing_attachment_id = wpvr_get_attachment_id_by_image_url( $filename_ext );
		if ( $existing_attachment_id !== false ) {
			$file = get_attached_file( $existing_attachment_id );
			
			// echo "the Video thumbnail already exists on the system. Use it :)";
			@set_post_thumbnail( $post_id, $existing_attachment_id );
			
			delete_post_meta( $post_id, 'wpvr_video_using_external_thumbnail' );
			
			return array(
				'file'          => $file,
				'attachment'    => @wp_generate_attachment_metadata( $existing_attachment_id, $file ),
				'attachment_id' => $existing_attachment_id,
			);
		}
		// d( $file );
		// d( $filename_ext );
		// d( wpvr_get_attachment_id_by_image_url( $filename_ext ) );
		
		@file_put_contents(
			$file,
			apply_filters(
				'wpvr_extend_featured_image_raw_content',
				@file_get_contents( $image_url ),
				$post_id
			)
		);
		
		$wp_filetype = @wp_check_filetype( $filename_ext, null );
		$attachment  = array(
			'post_mime_type' => $wp_filetype['type'],
			'post_title'     => $filename . "-attachment",
			'post_name'      => sanitize_title( $image_title . "-attachment" ),
			'post_content'   => $image_desc,
			'post_excerpt'   => $filename,
			'post_status'    => 'inherit',
		);
		if ( $post_id != '' ) {
			$attach_id = @wp_insert_attachment( $attachment, $file, $post_id );
			update_post_meta( $attach_id, '_wp_attachment_image_alt', $filename );
			@require_once( ABSPATH . 'wp-admin/includes/image.php' );
			$attach_data = @wp_generate_attachment_metadata( $attach_id, $file );
			@wp_update_attachment_metadata( $attach_id, $attach_data );
			@set_post_thumbnail( $post_id, $attach_id );
		} else {
			$attach_id = @wp_insert_attachment( $attachment, $file );
			update_post_meta( $attach_id, '_wp_attachment_image_alt', $filename );
			@require_once( ABSPATH . 'wp-admin/includes/image.php' );
			$attach_data = @wp_generate_attachment_metadata( $attach_id, $file );
			@wp_update_attachment_metadata( $attach_id, $attach_data );
		}
		
		//wpvr_set_debug( $file );
		delete_post_meta( $post_id, 'wpvr_video_using_external_thumbnail' );
		
		return array(
			'file'          => $file,
			'attachment'    => $attach_data,
			'attachment_id' => $attach_id,
		);
		
	}
	
	function wpvr_get_attachment_id_by_image_url( $image_url ) {
		global $wpdb;
		$attachment = $wpdb->get_col( "
          SELECT
            post_id
          FROM
            $wpdb->postmeta
          WHERE
            meta_key='_wp_attached_file'
            and meta_value like '%{$image_url}%'
          ORDER BY post_id desc
         " );
		
		return count( $attachment ) == 0 ? false : intval( $attachment[0] );
	}