<?php
	
	
	function wpvr_cpt_get_handled_types( $output = 'array' , $bypass_cache = false ) {
		
		$cache_hash = md5( 'wpvr_cpt_handled_types_' . $output );
		if (
			$bypass_cache !== true
			&& isset( $_SESSION['wpvr_cache'] )
			&& isset( $_SESSION['wpvr_cache'][ $cache_hash ] )
		) {
			//Get Sources from WPVR Cache
			return $_SESSION['wpvr_cache'][ $cache_hash ];
		}
		
		$wpvr_post_types_default = array( WPVR_VIDEO_TYPE );
		
		$wpvr_post_types = apply_filters( 'wpvr_extend_handled_post_types', $wpvr_post_types_default );
		
		if ( $wpvr_post_types === null ) {
			$wpvr_post_types = $wpvr_post_types_default;
		}
		
		if( $output == 'all' ){
			wpvr_cache_data( $wpvr_post_types, $cache_hash );
			
			return $wpvr_post_types;
		}
		
		//Check if the post types are up and running
		$valid_cpts = array();
		foreach ( (array) $wpvr_post_types as $k => $wpvr_post_type ) {
			$o = get_post_type_object( $wpvr_post_type );
			if( $o !== null && $o !== false && !is_wp_error($o ) ){
				$valid_cpts[] = $wpvr_post_type ;
			}
		}
		$wpvr_post_types = $valid_cpts ;
		
		
		if ( $output == 'sql' ) {
			return " ('" . implode( "', '", $wpvr_post_types ) . "') ";
		}
		
		if ( $output == 'options' ) {
			$options = array();
			foreach( (array) $wpvr_post_types as $handled_type ){
				$handled_type_data = get_post_type_object( $handled_type );
				$options[ $handled_type ] = ___( $handled_type_data->labels->singular_name, 1 );
			}
			return $options;
		}
		
		if ( $output == 'options_extended' ) {
			$options = array();
			foreach( (array) $wpvr_post_types as $handled_type ){
				
				//d( $handled_type );
				//d( get_post_type_object($handled_type) );
				
				$handled_type_data = get_post_type_object( $handled_type );
				$singular_label          = ___( $handled_type_data->labels->singular_name, 1 );
				$options[ $handled_type ] = $singular_label.' ['.$handled_type.'] ';
			}
			return $options;
		}
		
		wpvr_cache_data( $wpvr_post_types, $cache_hash );
		
		return $wpvr_post_types;
		
	}
	
	function wpvr_cpt_has_handled_type( $post_id = null ) {
		
		$wpvr_handled_post_types = wpvr_cpt_get_handled_types();
		
		$post_type = $post_id === null ? get_post_type() : get_post_type( $post_id );
		
		return in_array( $post_type, $wpvr_handled_post_types );
	}
	
	function wpvr_cpt_is_handled_type( $post_types = null ) {
		
		$wpvr_handled_post_types = wpvr_cpt_get_handled_types();
		
		if ( $post_types === null ) {
			$post_types = get_post_type();
		}
		
		if ( ! is_array( $post_types ) ) {
			return in_array( $post_types, $wpvr_handled_post_types );
		}
		
		foreach ( $post_types as $post_type ) {
			if ( ! in_array( $post_type, $wpvr_handled_post_types ) ) {
				return false;
			}
		}
		
		return true;
	}
	
	function wpvr_cpt_is_singular() {
		$is_singular = 1;
		
		foreach ( (array ) wpvr_cpt_get_handled_types() as $handled_type ) {
			$is_singular = $is_singular * ( is_singular( $handled_type ) ? 1 : 0 );
		}
		
		return $is_singular === 1 ? true : false;
		
	}