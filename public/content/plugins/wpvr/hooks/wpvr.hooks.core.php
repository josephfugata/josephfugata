<?php
	
	
	/* Get terms */
	add_filter( 'wpvr_extend_get_source' , 'wpvr_randomize_autobuild_terms' , 100 , 3 );
	function wpvr_randomize_autobuild_terms( $source , $source_id , $args ) {
		
		
		//Do nothing if randomize is not enabled
		if ( ! WPVR_ENABLE_RANDOMIZE_SOURCE_SEARCH ) {
			return $source;
		}
		
		//Proceed only on search sources
		if ( ! isset( $source->type ) || strpos( $source->type , 'search' ) === false ) {
			return $source;
		}
		
		//Proceed only when randomizeSearch is defined
		if ( $source->randomizeSearch === 0 || $source->randomizeSearch === null ) {
			return $source;
		}
		
		global $wpvr_vs;
		
		$search_param_name = $wpvr_vs[ $source->service ][ 'types' ][ $source->type ][ 'param' ];
		
		$source->{$search_param_name} = wpvr_randomize_build_terms($source->{$search_param_name} ,$source->randomizeSearch);
		
		
		return $source;
	}
	
	/* Enable Randomize property of source */
	add_filter( 'wpvr_extend_source_additional_meta_fields' , 'wpvr_randomize_inject_meta' , 100 , 1 );
	function wpvr_randomize_inject_meta( $meta ) {
		if ( WPVR_ENABLE_RANDOMIZE_SOURCE_SEARCH ) {
			$meta[] = 'randomizeSearch';
		}
		
		return $meta;
	}
	
	/* Enable Randomize Search Field */
	add_filter( 'wpvr_extend_source_info_fields' , 'wpvr_randomize_inject_field' , 100 , 3 );
	function wpvr_randomize_inject_field( $fields , $prefix , $post_id ) {
		//d( WPVR_ENABLE_RANDOMIZE_SOURCE_SEARCH );
		if ( ! WPVR_ENABLE_RANDOMIZE_SOURCE_SEARCH ) {
			return $fields;
		}
		
		$fields[] = array(
			'id'        => $prefix . 'randomizeSearch' ,
			'type'      => 'select' ,
			'name'      => __( 'Randomize Search' , WPVR_LANG ) ,
			'default'   => '0' ,
			'desc'      => 'Choose how many search terms should be randomly picked to proceed.' ,
			'options'   => array(
				'0'  => 'No randomization' ,
				'1'  => 'Pick 1 term randomly' ,
				'2'  => 'Pick 2 terms randomly' ,
				'5'  => 'Pick 5 terms randomly' ,
				'10' => 'Pick 10 terms randomly' ,
				'15' => 'Pick 15 terms randomly' ,
			) ,
			'wpvrClass' => 'wpvr_args_no_border' ,
		);
		
		return $fields;
	}
	
	
	add_action( 'wpvr_before_die' , 'wpvr_clean_functions_cache' , 100 );
	add_action( 'admin_footer' , 'wpvr_clean_functions_cache' , PHP_INT_MAX );
	function wpvr_clean_functions_cache() {
		if ( isset( $_SESSION[ 'wpvr_cache' ] ) ) {
			unset( $_SESSION[ 'wpvr_cache' ] );
		}
	}
	
	add_filter( 'wpvr_extend_handled_post_types' , 'wpvr_define_supported_cpt' , - 1 , 1 );
	function wpvr_define_supported_cpt( $supported_types ) {
		global $wpvr_options;
		if ( ! isset( $wpvr_options[ 'supportedPostTypes' ] ) ) {
			return $supported_types;
		}
		
		if ( is_string( $wpvr_options[ 'supportedPostTypes' ] ) ) {
			return json_decode( stripslashes( $wpvr_options[ 'supportedPostTypes' ] ) );
		}
		
		if ( is_array( $wpvr_options[ 'supportedPostTypes' ] ) ) {
			return $wpvr_options[ 'supportedPostTypes' ];
		}
		
		return $supported_types;
	}
	
	
	add_filter( 'wpvr_extend_saved_options' , 'wpvr_check_saved_options' , 100 , 3 );
	function wpvr_check_saved_options( $args , $old_options , $new_options ) {
		
		
		$args[ 'refresh' ]  = false;
		$args[ 'param' ]    = '';
		$refreshing_options = array(
			'videoType' ,
		);
		foreach ( $refreshing_options as $option ) {
			if ( $old_options[ $option ] != $new_options[ $option ] ) {
				$args[ 'refresh' ] = true;
				$args[ 'param' ]   = 'do_reset_tables';
				
				
				if ( $option == 'videoType' ) {
					$msg = sprintf( __( 'You have changed the Imported Video Type to %s.' , WPVR_LANG ) . '<br/>' .
					                __( 'Don\'t worry! Your old imported videos are not gone.' , WPVR_LANG ) . '<br/>' .
					                __( 'They have been imported using the %s Post Type.' , WPVR_LANG ) . ' ' .
					                __( 'You need to revert back to that post type to get them back online.' , WPVR_LANG ) ,
						'<strong>' . $new_options[ 'videoType' ] . '</strong>' ,
						'<strong>' . $old_options[ 'videoType' ] . '</strong>'
					);
					
					$error_notice_slug = wpvr_add_notice( array(
						'title'     => 'WP Video Robot : ' ,
						'class'     => 'updated' , //updated or warning or error
						'content'   => $msg ,
						'hidable'   => false ,
						'is_dialog' => false ,
						'show_once' => true ,
						'color'     => '#27A1CA' ,
						'icon'      => 'fa-exclamation-circle' ,
					) );
					//d( $error_notice_slug );
					//wpvr_die();
					//wpvr_render_notice( $error_notice_slug );
					//wpvr_remove_notice( $error_notice_slug );
					
				}
				
				
			}
		}
		
		//Notify that the WPVR Custom Post tYpe has changed and your videos are not gone ...
		
		
		return $args;
	}
	
	/* Plugin Init Action Hook */
	add_action( 'init' , 'wpvr_init' );
	function wpvr_init() {
		/*starting a PHP session if not already started */
		if ( ! session_id() ) {
			@session_start();
		}
		wpvr_mysql_install();
		add_image_size( 'wpvr_hard_thumb' , 200 , 150 , true ); // Hard Crop Mode
		add_image_size( 'wpvr_soft_thumb' , 200 , 150 ); // Soft Crop Mode
		wpvr_capi_init();
	}
	
	
	add_action( 'plugins_loaded' , 'wpvr_load_addons_activation_hooks' , 5 );
	function wpvr_load_addons_activation_hooks() {
		$x           = explode( 'wpvr' , WPVR_MAIN_FILE );
		$plugins_dir = $x[ 0 ];
		$addons_obj  = wpvr_get_addons( array() , false );
		if ( isset( $addons_obj[ 'items' ] ) && count( $addons_obj[ 'items' ] ) != 0 ) {
			foreach ( (array) $addons_obj[ 'items' ] as $addon ) {
				$addon_main_file = $plugins_dir . str_replace( '/' , "\\" , $addon->plugin_dir );
				register_activation_hook(
					$addon_main_file ,
					function () use ( $addon ) {
						wpvr_start_plugin( $addon->id , $addon->version , false );
					}
				);
			}
		}
	}
	
	/* Loading WPVR translation files */
	add_action( 'plugins_loaded' , 'wpvr_load_textdomain' , - 1 );
	function wpvr_load_textdomain() {
		if ( WPVR_FORCE_ENGLISH_LANGUAGE !== true ) {
			load_plugin_textdomain( WPVR_LANG , false , dirname( plugin_basename( __FILE__ ) ) . '/../languages/' );
		}
	}
	
	/* Loading the WPVR Superwrap HEADER*/
	add_action( 'load-edit.php' , 'wpvr_add_slug_edit_screen_header' , - 1 );
	function wpvr_add_slug_edit_screen_header() {
		global $wpvr_options;
		
		if ( isset( $_GET[ '_wpnonce' ] ) || isset( $_POST[ '_wpnonce' ] ) ) {
			//Disable smooth screen on WP redirects
			return;
		}
		if ( $wpvr_options[ 'smoothScreen' ] === true ) {
			$screen                = get_current_screen();
			$screen_edit_post_type = str_replace( 'edit-' , '' , $screen->id );
			
			if (
				$screen_edit_post_type == WPVR_SOURCE_TYPE
				|| wpvr_cpt_is_handled_type( $screen_edit_post_type )
			) {
				?><div class = "wpvr_super_wrap" style = " transition:visibility 1s ease-in-out;visibility:hidden;"><!-- SUPER_WRAP --><?php
			}
		}
	}
	
	/* Loading the WPVR Superwrap FOOTER */
	add_action( 'admin_footer' , 'wpvr_add_slug_edit_screen_footer' , 999999999999 );
	function wpvr_add_slug_edit_screen_footer() {
		global $wpvr_options;
		
		if ( isset( $_GET[ '_wpnonce' ] ) || isset( $_POST[ '_wpnonce' ] ) ) {
			//Disable smooth screen on WP redirects
			return;
		}
		if ( $wpvr_options[ 'smoothScreen' ] === true ) {
			$screen                = get_current_screen();
			$screen_edit_post_type = str_replace( 'edit-' , '' , $screen->id );
			
			if (
				$screen_edit_post_type == WPVR_SOURCE_TYPE
				|| wpvr_cpt_is_handled_type( $screen_edit_post_type )
			) {
				?><!-- SUPER_WRAP --><?php
			}
		}
	}
	
	/*Fix For pagination Category 1/2 */
	add_filter( 'request' , 'wpvr_remove_page_from_query_string' );
	function wpvr_remove_page_from_query_string( $query_string ) {
		if ( isset( $query_string[ 'name' ] ) && $query_string[ 'name' ] == 'page' && isset( $query_string[ 'page' ] ) ) {
			unset( $query_string[ 'name' ] );
			// 'page' in the query_string looks like '/2', so i'm spliting it out
			list( $delim , $page_index ) = split( '/' , $query_string[ 'page' ] );
			$query_string[ 'paged' ] = $page_index;
		}
		
		return $query_string;
	}
	
	/*Fix For pagination Category 2/2 */
	add_filter( 'request' , 'wpvr_fix_category_pagination' );
	function wpvr_fix_category_pagination( $qs ) {
		if ( isset( $qs[ 'category_name' ] ) && isset( $qs[ 'paged' ] ) ) {
			$qs[ 'post_type' ] = get_post_types( $args = array(
				'public'   => true ,
				'_builtin' => false ,
			) );
			array_push( $qs[ 'post_type' ] , 'post' );
		}
		
		return $qs;
	}
	
	/* Actions to be done on the activation of WPVR */
	register_activation_hook( WPVR_MAIN_FILE , 'wpvr_activation' );
	function wpvr_activation() {
		
		wpvr_reset_on_activation();
		
		wpvr_start_plugin( 'wpvr' , WPVR_VERSION , false );
		
		if ( ! get_option( 'wpvr_flush_rewrite_rules_flag' ) ) {
			add_option( 'wpvr_flush_rewrite_rules_flag' , true );
		}
		
		wp_schedule_event( time() , 'hourly' , 'wpvr_hourly_event' );
		wpvr_save_errors( ob_get_contents() );
		//wpvr_set_debug( ob_get_contents() , TRUE );
		flush_rewrite_rules();
		
		global $wp_rewrite;
		$wp_rewrite->set_permalink_structure( '/%postname%/' );
	}
	
	/* Actions to be done on the DEactivation of WPVR */
	register_deactivation_hook( WPVR_MAIN_FILE , 'wpvr_deactivation' );
	function wpvr_deactivation() {
		wp_clear_scheduled_hook( 'wpvr_hourly_event' );
		//flush_rewrite_rules();
		wpvr_save_errors( ob_get_contents() );
		//wpvr_set_debug( ob_get_contents() , TRUE );
	}
	
	register_deactivation_hook( WPVR_MAIN_FILE , 'flush_rewrite_rules' );
	
	/* Set Autoupdate Hook */
	add_action( 'init' , 'wpvr_activate_autoupdate' , 100 );
	function wpvr_activate_autoupdate() {
		global $wpvr_addons;
		
		//Check WPVR updates
		if ( WPVR_CHECK_PLUGIN_UPDATES ) {
			new wpvr_autoupdate_product (
				WPVR_VERSION , // Current Version of the product (ex 1.7.0)
				WPVR_SLUG , // Product Plugin Slug (ex wpvr/wpvr.php')
				false // Update zip url ? (ex TRUE or FALSE ),
			);
		}
		
		//Check for active addons updates
		if ( WPVR_CHECK_ADDONS_UPDATES ) {
			$addons_obj = wpvr_get_addons( array() , false );
			//d( $wpvr_addons );
			if ( ! is_multisite() ) {
				if ( isset( $addons_obj[ 'items' ] ) && count( $addons_obj[ 'items' ] ) != 0 ) {
					foreach ( (array) $addons_obj[ 'items' ] as $addon ) {
						//continue;
						if ( ! isset( $wpvr_addons[ $addon->id ] ) ) {
							continue;
						}
						if ( ! is_plugin_active( $addon->plugin_dir ) ) {
							continue;
						}
						$local_version = $wpvr_addons[ $addon->id ][ 'infos' ][ 'version' ];
						//d( $local_version );
						//d( $addon->id );
						new wpvr_autoupdate_product (
							$local_version , // Current Version of the product (ex 1.7.0)
							$addon->plugin_dir , // Product Plugin Slug (ex wpvr/wpvr.php')
							false // Update zip url ? (ex TRUE or FALSE ),
						);
						
					}
				}
			} else {
				if ( isset( $addons_obj[ 'items' ] ) && count( $addons_obj[ 'items' ] ) != 0 ) {
					foreach ( (array) $addons_obj[ 'items' ] as $addon ) {
						if ( ! isset( $wpvr_addons[ $addon->id ] ) ) {
							continue;
						}
						
						//d( $addon->id );
						//d( is_plugin_active_for_network( $addon->plugin_dir ));
						
						if ( ! is_plugin_active_for_network( $addon->plugin_dir ) ) {
							continue;
						}
						
						
						$local_version = $wpvr_addons[ $addon->id ][ 'infos' ][ 'version' ];
						//d( $local_version );
						//d( $addon->id );
						new wpvr_autoupdate_product (
							$local_version , // Current Version of the product (ex 1.7.0)
							$addon->plugin_dir , // Product Plugin Slug (ex wpvr/wpvr.php')
							false // Update zip url ? (ex TRUE or FALSE ),
						);
						
						//d( $addon );
						//$plugin = explode('/' , $addon->plugin_dir );
						//$plugin_data = get_plugin_data( $plugin[1] , $markup = true, $translate = true );
						//d( $plugin_data );
						
						
					}
				}
			}
		}
		
	}
	
	/* Activation */
	add_action( 'admin_footer' , 'wpvr_check_customer' );
	
	/* Add query video custom post types on pre get posts action */
	add_filter( 'pre_get_posts' , 'wpvr_include_custom_post_type_queries' , 1000 , 1 );
	function wpvr_include_custom_post_type_queries( $query ) {
		global $wpvr_options , $wpvr_private_cpt;
		$getOut = false;
		
		//d( DOING_AJAX );
		if ( $query->is_page ) {
			return $query;
		}
		
		if ( $query->is_attachment ) {
			return $query;
		}
		
		if ( ! defined( 'DOING_AJAX' ) || DOING_AJAX === false ) {
			if ( is_admin() ) {
				return $query;
			}
		}
		
		
		if ( ! is_single() && ! $wpvr_options[ 'addVideoType' ] ) {
			return $query;
		}
		
		//Define Private Query Vars
		$wpvr_private_query_vars = apply_filters( 'wpvr_extend_private_query_vars' , array(
			'product_cat' ,
			'download_artist' ,
			'download_tag' ,
			'download_category' ,
		) );
		
		// Define Private CPT
		$wpvr_private_cpt = apply_filters( 'wpvr_extend_private_cpt' ,
			( $wpvr_options[ 'privateCPT' ] == null ) ? array() : $wpvr_options[ 'privateCPT' ]
		);
		
		// Escaping if using Private Query Vars
		foreach ( (array) $query->query_vars as $key => $val ) {
			if ( in_array( $key , $wpvr_private_query_vars ) ) {
				return $query;
			}
		}
		//d( $query );
		$supported = $query->get( 'post_type' );
		
		if ( is_array( $supported ) ) {
			foreach ( (array) $supported as $s ) {
				if ( in_array( $s , $wpvr_private_cpt ) ) {
					$getOut = true;
				}
			}
		} else {
			$getOut = in_array( $supported , $wpvr_private_cpt );
		}
		
		$getOut = apply_filters( 'wpvr_extend_video_query_injection' , $getOut , $query );
		
		$handled_types = wpvr_cpt_get_handled_types('array' , true);
		
		if ( $getOut === true ) {
			return $query;
		} elseif ( $supported == 'post' || $supported == '' || $supported == null ) {
			$supported = $handled_types;
			
			
			// Was Disabled maybe conflict?
			$supported[] = 'post';
			
		} elseif ( is_array( $supported ) ) {
			$supported = array_unique( array_merge( $supported , $handled_types ) );
		} elseif ( is_string( $supported ) ) {
			$supported   = $handled_types;
			$supported[] = $supported;
		}
		
		$query->set( 'post_type' , $supported );
		//d( $query );
		return $query;
		
	}
	
	add_filter( 'wpvr_extend_source_additional_meta_fields' , 'wpvr_add_service_source_fields' , 100 , 1 );
	function wpvr_add_service_source_fields( $fields ) {
		global $wpvr_vs;
		
		foreach ( (array) $wpvr_vs as $vs ) {
			if ( count( $vs[ 'types' ] ) == 0 ) {
				continue;
			}
			
			foreach ( (array) $vs[ 'types' ] as $vs_type ) {
				if ( ! isset( $vs_type[ 'fields' ] ) || count( $vs_type[ 'fields' ] ) == 0 ) {
					continue;
				}
				foreach ( (array) $vs_type[ 'fields' ] as $vs_type_field ) {
					$fields[] = $vs_type_field[ 'id' ];
				}
			}
		}
		
		return $fields;
	}
	
	
	add_filter( 'wpvr_extend_get_source' , 'wpvr_define_source_default_fields' , 100 , 3 );
	function wpvr_define_source_default_fields( $source , $source_id , $args ) {
		
		global $wpvr_options;
		
		if ( $args[ 'shorted' ] === true ) {
			return $source;
		}
		
		$source->folders = false;
		if ( $args[ 'get_folders' ] === true ) {
			$folders     = array();
			$folders_obj = get_the_terms( $source->id , WPVR_SFOLDER_TYPE );
			foreach ( (array) $folders_obj as $folder ) {
				if ( $folder === false ) {
					continue;
				}
				$folders[] = array(
					'id'   => $folder->term_id ,
					'name' => $folder->name ,
					'slug' => $folder->slug ,
				);
			}
			$source->folders = $folders;
		}
		
		$source->postCats   = wpvr_json_decode( $source->postCats );
		$source->postAuthor = (array) wpvr_json_decode( $source->postAuthor );
		$source->postAuthor = count( $source->postAuthor ) != 0 ? array_pop( $source->postAuthor ) : 'default';
		
		if ( $source->skipUnwanted == '' ) {
			$source->skipUnwanted = 'global';
		}
		
		$source->postCatsSlug = ( wpvr_get_tax_data( 'category' , $source->postCats ) );
		
		if ( $source->era == '' ) {
			$source->era = 0;
		}
		if ( $source->onlyNewVideos == 'default' ) {
			$source->onlyNewVideos = wpvr_get_button_state( $wpvr_options[ 'onlyNewVideos' ] );
		}
		if ( $source->autoPublish == 'default' ) {
			$source->autoPublish = wpvr_get_button_state( $wpvr_options[ 'autoPublish' ] );
		}
		if ( $source->videoBroadcast == 'default' ) {
			$source->videoBroadcast = $wpvr_options[ 'videoBroadcast' ];
		}
		
		if ( $source->postType == 'default' ) {
			$source->postType = $wpvr_options[ 'postType' ];
		} elseif ( $source->postType == '' || $source->postType == null ) {
			$source->postType = $wpvr_options[ 'videoType' ];
		}
		
		if ( $source->startTime == null ) {
			$source->startTime = '';
		}
		
		if ( $source->endTime == null ) {
			$source->endTime = '';
		}
		
		if ( $source->downloadThumb == 'default' || $source->downloadThumb == null ) {
			$source->downloadThumb = wpvr_get_button_state( $wpvr_options[ 'downloadThumb' ] );
		}
		
		if ( $source->hidePlayerAnnotations == 'default' || $source->hidePlayerAnnotations == null ) {
			$source->hidePlayerAnnotations = wpvr_get_button_state( $wpvr_options[ 'hidePlayerAnnotations' ] );
		}
		
		if ( $source->hidePlayerTitle == 'default' || $source->hidePlayerTitle == null ) {
			$source->hidePlayerTitle = wpvr_get_button_state( $wpvr_options[ 'hidePlayerTitle' ] );
		}
		
		if ( $source->hidePlayerRelated == 'default' || $source->hidePlayerRelated == null ) {
			$source->hidePlayerRelated = wpvr_get_button_state( $wpvr_options[ 'hidePlayerRelated' ] );
		}
		
		if ( $source->getVideoStats == 'default' ) {
			$source->getVideoStats = wpvr_get_button_state( $wpvr_options[ 'getStats' ] );
		}
		if ( $source->getVideoTags == 'default' ) {
			$source->getVideoTags = wpvr_get_button_state( $wpvr_options[ 'getTags' ] );
		}
		if ( $source->postAuthor == 'default' ) {
			$source->postAuthor = ( $wpvr_options[ 'postAuthor' ] );
		}
		if ( $source->postStatus == 'default' ) {
			$source->postStatus = ( $wpvr_options[ 'postStatus' ] );
		}
		
		if ( $source->postDate == 'default' ) {
			$source->postDate = ( $wpvr_options[ 'getPostDate' ] );
		}
		
		if ( $source->publishedBefore_bool == 'default' ) {
			$source->publishedBefore = $wpvr_options[ 'publishedBefore' ];
		}
		
		if ( $source->publishedAfter_bool == 'default' ) {
			$source->publishedAfter = $wpvr_options[ 'publishedAfter' ];
		}
		
		//Retro Compatibility for old sources
		if( $source->orderVideos === null ){
		    $source->orderVideos = get_post_meta( $source_id , 'wpvr_source_order' , true );
        }
        
        if ( $source->orderVideos == 'default' ) {
			$source->orderVideos = ( $wpvr_options[ 'orderVideos' ] );
		}
		if ( $source->wantedVideosBool == 'default' ) {
			$source->wantedVideos = ( $wpvr_options[ 'wantedVideos' ] );
		}
		if ( $source->videoQuality == 'default' ) {
			$source->videoQuality = ( $wpvr_options[ 'videoQuality' ] );
		}
		if ( $source->videoDuration == 'default' ) {
			$source->videoDuration = ( $wpvr_options[ 'videoDuration' ] );
		}
		
		if ( $source->count_test == '' ) {
			$source->count_test = 0;
		}
		if ( $source->count_run == '' ) {
			$source->count_run = 0;
		}
		if ( $source->count_success == '' ) {
			$source->count_success = 0;
		}
		if ( $source->count_fail == '' ) {
			$source->count_fail = 0;
		}
		if ( $source->count_imported == '' ) {
			$source->count_imported = 0;
		}
		
		if ( $source->postTagsBool == 'disabled' ) {
			$source->postTags = array();
		} elseif ( $source->postTagsBool == 'default' ) {
			$source->postTags = explode( ',' , $wpvr_options[ 'postTags' ] );
		} else {
			$source->postTags = explode( ',' , $source->postTags );
		}
		
		
		return $source;
	}
	
	add_filter( 'wpvr_extend_test_posting_insights' , 'wpvr_define_source_posting_cpt' , 100 , 2 );
	function wpvr_define_source_posting_cpt( $insights , $sourceResult ) {
		
		$handled_type_data = get_post_type_object( $sourceResult[ 'source' ]->postType );
		$plural_label      = ___( $handled_type_data->labels->name , 1 );
		
		$insights[] = array(
			'order' => - 1 ,
			'title' => __( 'Import Post Type' , WPVR_LANG ) ,
			'icon'  => 'fa-database' ,
			'value' => sprintf( __( 'Importing as %s' , WPVR_LANG ) , $plural_label ) ,
		);
		
		return $insights;
	}
	
	
	add_filter( 'wpvr_extend_source_column_settings' , 'wpvr_print_source_import_post_type' , 100 , 2 );
	function wpvr_print_source_import_post_type( $source_settings , $source ) {
		global $wpvr_options;
		
		$handled_type_data = get_post_type_object( $source->postType );
		
		if( $handled_type_data === null ){
		    return $source_settings;
		}
		$plural_label = ___( $handled_type_data->labels->name , 1 );
		$source_settings[]
		              = '
					<span class=" wpvr_source_span">
						<i class="fa fa-database"></i>
						' . __( 'Importing as' , WPVR_LANG ) . ' <strong>' . $plural_label . '</strong>
					</span>
				';
		
		return $source_settings;
	}
	
    add_filter( 'wpvr_extend_options_before_default', 'wpvr_correct_multiselect_options_fields' );
	function wpvr_correct_multiselect_options_fields( $wpvr_options ){
	    
	    //Correct Multiselects
	    if( isset( $wpvr_options['postAuthor'] ) && is_array( $wpvr_options['postAuthor'] ) ){
	        if( count(  $wpvr_options['postAuthor'] ) == 2 ){
		        $wpvr_options['postAuthor'] =  $wpvr_options['postAuthor'][1];
            }elseif( count( $wpvr_options['postAuthor']) == 1 ){
		        $wpvr_options['postAuthor'] =  $wpvr_options['postAuthor'][0];
            }
        }
	    	  
	    return $wpvr_options;
    }
    
    
    add_action('admin_footer' , 'wpvr_add_processing_html' , 1000 );
	function wpvr_add_processing_html(){
	    $screen = get_current_screen();
	    if( $screen->base != 'edit' || !wpvr_cpt_is_handled_type( $screen->post_type ) ){
	        return false;
        }
	   ?>
        <div style="display:none;">
            <button
                type="button"
                id="redownload_video_thumbnails_ids"
                class="wpvr_button wpvr_bulk_process_btn"
                data-init_action="balance_items"
                data-items=""
                data-refresh_onfinish="true"
                data-progress_message="percentage"
                data-single_action="redownload_video_thumbnails"
                data-single_args='<?php echo htmlspecialchars( json_encode( array() ), ENT_QUOTES, 'UTF-8' ); ?>'
                data-buffer="2"
                data-confirm_title="<?php echo __( 'Update video thumbnails', WPVR_LANG ); ?>"
                data-confirm_message="<?php echo __( 'Do you really want to re-download the selected video thumbnails?' , WPVR_LANG).'<br/>'. __('This may take some time.', WPVR_LANG ); ?>"
                data-finish_message="<?php echo __( '%s thumbnails updated.', WPVR_LANG ); ?>"
                data-finish_title="<?php echo __( 'Work Completed!', WPVR_LANG ); ?>"
                data-counter_callback=""
        > REDOWNLOAD THUMBS </button>
        </div>
        <?php
    }