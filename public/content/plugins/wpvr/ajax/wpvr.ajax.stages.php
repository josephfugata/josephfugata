<?php
	
	add_action( 'wp_ajax_nopriv_balance_items', 'wpvr_balance_items_function' );
	add_action( 'wp_ajax_balance_items', 'wpvr_balance_items_function' );
	function wpvr_balance_items_function() {
		global $wpdb;
		
		$timer  = wpvr_chrono_time();
		$buffer = ! isset( $_POST['buffer'] ) ? 1 : intval( $_POST['buffer'] );
		
		$items       = explode( ',', str_replace( ' ', '', $_POST['items'] ) );
		$total_items = count( $items );
		
		$items = wpvr_async_balance_items( $items, $buffer, true );
//d( $items );
		
		echo wpvr_get_json_response( array(
			'total'     => $total_items,
			'items'     => $items,
			'exec_time' => wpvr_chrono_time( $timer ),
		), 1, 'Items returned' );
		
		wpvr_die();
	}
	
	add_action( 'wp_ajax_nopriv_redownload_video_thumbnails', 'wpvr_redownload_video_thumbnails_function' );
	add_action( 'wp_ajax_redownload_video_thumbnails', 'wpvr_redownload_video_thumbnails_function' );
	function wpvr_redownload_video_thumbnails_function() {
		global $wpdb;
		
		$timer       = wpvr_chrono_time();
		$args        = $_POST['args'];
		$single_args = $args['single_args'];
		$items       = $_POST['items'];
		
		$count = wpvr_bulk_update_thumbs( $items );
//print_r( $count );
		
		
		$args['total_exec_time'] += wpvr_chrono_time( $timer );
		$args['exec_time']       = wpvr_chrono_time( $timer );
		
		echo wpvr_get_json_response( array(
			'args' => $args,
		), 1, 'Items processed.' );
		
		wpvr_die();
	}
	
	add_action( 'wp_ajax_nopriv_prepare_existing_videos', 'wpvr_prepare_existing_videos_function' );
	add_action( 'wp_ajax_prepare_existing_videos', 'wpvr_prepare_existing_videos_function' );
	function wpvr_prepare_existing_videos_function() {
		
		
		global $wpdb;
		$limit = false;
		
		
		$buffer = ! isset( $_POST['buffer'] ) ? 1 : intval( $_POST['buffer'] );
		
		$timer     = wpvr_chrono_time();
		$limit_sql = $limit !== false ? " LIMIT 0 , $limit " : "";
		
		
		$sql_select = "
select P.ID from {$wpdb->posts} P
where
P.post_type IN " . wpvr_cpt_get_handled_types( 'sql' ) . "


$limit_sql ";
		
		
		$rows = $wpdb->get_results( $sql_select );
		
		$items         = array();
		$wpvr_imported = wpvr_get_imported_videos();
//d( $wpvr_imported );
		foreach ( (array) $rows as $row ) {
			
			if ( ! wpvr_is_imported_video( $row->ID, $wpvr_imported ) ) {
				continue;
			}
			$items[] = $row->ID;
		}
		
		$items = wpvr_async_balance_items( $items, $buffer, true );
//d( $items );
		
		echo wpvr_get_json_response( array(
			'total'     => count( $rows ),
			'items'     => $items,
			'exec_time' => wpvr_chrono_time( $timer ),
		), 1, 'Items returned' );
		
		wpvr_die();
	}
	
	add_action( 'wp_ajax_nopriv_prepare_existing_videos_fast', 'wpvr_prepare_existing_videos_function_fast' );
	add_action( 'wp_ajax_prepare_existing_videos_fast', 'wpvr_prepare_existing_videos_function_fast' );
	function wpvr_prepare_existing_videos_function_fast() {
		
		
		global $wpdb;
		$buffer = ! isset( $_POST['buffer'] ) ? 1 : intval( $_POST['buffer'] );
		
		$timer = wpvr_chrono_time();
		
		$wpvr_imported = wpvr_get_imported_videos();
		$items         = array();
		foreach ( (array) $wpvr_imported as $service => $service_posts ) {
			$items = array_merge( $items, $service_posts );
		}
		
		$total_items = count( $items );
		
		$items = wpvr_async_balance_items( $items, $buffer, true );
//d( $items );
		
		echo wpvr_get_json_response( array(
			'total'     => $total_items,
			'items'     => $items,
			'exec_time' => wpvr_chrono_time( $timer ),
		), 1, 'Items returned' );
		
		wpvr_die();
	}
	
	
	add_action( 'wp_ajax_nopriv_partially_execute_fillers', 'wpvr_partially_execute_fillers_function' );
	add_action( 'wp_ajax_partially_execute_fillers', 'wpvr_partially_execute_fillers_function' );
	function wpvr_partially_execute_fillers_function() {
		
		$args = $_POST['args'];
		
		$single_args = $args['single_args'];
		
		$timer = wpvr_chrono_time();
		
		if ( ! isset( $args['count'] ) ) {
			$args['count'] = array();
		}
		
		$args['count']['total'] = 0;
		if ( ! isset( $args['count']['insert'] ) ) {
			$args['count']['insert'] = 0;
		}
		if ( ! isset( $args['count']['delete'] ) ) {
			$args['count']['delete'] = 0;
		}
		
		$sql = array();
		
		foreach ( (array) $_POST['items'] as $post_id ) {
			
			$sql = array_merge( $sql, wpvr_execute_dataFillers( $post_id, null, true, true ) );
			
			$args['count']['total'] ++;
			
		}
		
		$db = wpvr_run_multiple_db_queries( $sql );
		
		if ( isset( $db['count']['insert'] ) ) {
			$args['count']['insert'] += $db['count']['insert'];
		}
		
		if ( isset( $db['count']['delete'] ) ) {
			$args['count']['delete'] += $db['count']['delete'];
		}
		
		
		$args['total_exec_time'] += wpvr_chrono_time( $timer );
		$args['exec_time']       = wpvr_chrono_time( $timer );
		
		echo wpvr_get_json_response( array(
			'args' => $args,
		), 1, 'Items Partially Processed.' );
		
		wpvr_die();
	}