<?php
	
	/* Function to redirect download request to permalink structure */
	add_action( 'template_include', 'wpvr_download_export_file' );
	function wpvr_download_export_file( $template ) {
		
		$export_filenames = array(
			'sources' => 'wpvr_export_sources.json',
			'videos'  => 'wpvr_export_videos.json',
			'options' => 'wpvr_export_options.json',
			'system_info' => 'wpvr_export_system_info.txt',
			'data'    => 'wpvr_export_data.json',
		);
		
		$request_uri      = $_SERVER['REQUEST_URI'];
		
		if ( strpos( $request_uri, '/wpvr_export/' ) === false ) {
			return $template;
		}
		
		$array = explode( '/wpvr_export/', $request_uri );
		
		
		if ( ! isset( $array[1] ) || empty( $array[1] ) ) {
			return $template;
		}
		
		$file = $array[1];
		
		if( !file_exists(WPVR_TMP_PATH.$file ) ){
			echo "Could not find the tmp file.";
			return $template;
		}
		
		
		if( strpos( $file , '__sources__' ) !== false ){
			$filename = $export_filenames['sources'] ;
		}elseif( strpos( $file , '__videos__' ) !== false ){
			$filename = $export_filenames['videos'] ;
		}elseif( strpos( $file , '__options__' ) !== false ){
			$filename = $export_filenames['options'] ;
		}elseif( strpos( $file , '__system_info__' ) !== false ){
			$filename = $export_filenames['system_info'] ;
		}elseif( strpos( $file , '__data__' ) !== false ){
			$filename = $export_filenames['data'] ;
		}else{
			return $template;
		}
		
		
		header( "Content-type: application/x-msdownload", true, 200 );
		header( "Content-Disposition: attachment; filename=" . $filename );
		header( "Pragma: no-cache" );
		header( "Expires: 0" );
		readfile( WPVR_TMP_PATH.$file );
		exit();
	}
	
	
	add_action( 'init', 'wpvr_do_rewrite_permalinks', 2000 );
	function wpvr_do_rewrite_permalinks() {
		global $wp_rewrite, $wpvr_options;
		
		if ( $wpvr_options['enableRewriteRule'] === false ) {
			return false;
		}
		
		if ( $wpvr_options['permalinkBase'] === 'none' ) {
			$base = '';
		} elseif ( $wpvr_options['permalinkBase'] === 'category' ) {
			$base = '/%category%';
		} elseif ( $wpvr_options['permalinkBase'] === 'custom' ) {
			$base = $wpvr_options['customPermalinkBase'] == '' ? '' : '/' . $wpvr_options['customPermalinkBase'] . '';
		}
		
		$wp_rewrite->set_permalink_structure( $base . '/%postname%/' );
		
		if ( get_option( 'wpvr_permalink_base' ) != $base ) {
			flush_rewrite_rules();
			update_option( 'wpvr_permalink_base', $base );
		}
	}
	
	
	add_filter( 'post_type_link', 'wpvr_do_rewrite_links', 10, 3 );
	function wpvr_do_rewrite_links( $post_link, $post, $leavename ) {
		global $wpvr_options;
		
		if ( ! wpvr_cpt_has_handled_type( $post->ID ) || 'publish' != $post->post_status ) {
			return $post_link;
		}
		
		
		//Rewrite is OFF
		if ( $wpvr_options['enableRewriteRule'] !== true ) {
			return wpvr_render_video_permalink( $post, null, $post_link );
		}
		
		//Category Permalink Base
		if ( $wpvr_options['permalinkBase'] === 'category' ) {
			return wpvr_render_video_permalink( $post, "/%category%/%postname%/" );
		}
		
		//No Permalink Base
		if ( $wpvr_options['permalinkBase'] === 'none' ) {
			return wpvr_render_video_permalink( $post, "/%postname%/" );
		}
		
		//Custom Permalink Base
		if ( $wpvr_options['permalinkBase'] === 'custom' ) {
			return wpvr_render_video_permalink( $post, "/{$wpvr_options['customPermalinkBase']}/%postname%/" );
		}
		
		return $post_link;
		
	}
	
	
	add_action( 'init', 'wpvr_add_cron_endpoint' );
	function wpvr_add_cron_endpoint() {
		add_rewrite_tag( '%wpvr_cron%', '([^&]+)' );
		add_rewrite_rule( WPVR_CRON_ENDPOINT . '/([^&]+)/?', 'index.php?wpvr_cron=$matches[1]', 'top' );
		//flush_rewrite_rules();
		
		if ( get_option( 'wpvr_automation_cron_endpoint' ) != WPVR_CRON_ENDPOINT ) {
			flush_rewrite_rules();
			update_option( 'wpvr_automation_cron_endpoint', WPVR_CRON_ENDPOINT );
		}
		
	}
	
	
	add_action( 'template_redirect', 'wpvr_process_cron_call', 1000 );
	function wpvr_process_cron_call() {
		global $wp_query, $cron_data_file;
		$token = $wp_query->get( 'wpvr_cron' );
		if ( ! $token ) {
			return;
		}
		//$_GET['debug'] = true ;
		$_GET['token'] = $token;
		//d( $_GET );
		define( 'WPVR_DOING_CRON', true );
		
		if ( ! is_multisite() ) {
			$cron_data_file = WPVR_PATH . "assets/php/cron.txt";
		} else {
			$site_id        = get_current_blog_id();
			$cron_data_file = WPVR_PATH . "assets/php/cron_" . $site_id . ".txt";
		}
		include( WPVR_PATH . 'wpvr.cron.php' );
		exit;
	}
	