<?php
	
	if ( ! class_exists( 'wpvr_videos_bulk_actions' ) ) {
		class wpvr_videos_bulk_actions {
			
			public $allowed_actions;
			
			public function __construct() {
				
				if ( is_admin() ) {
					/* Hooking the bulk functions */
					add_action( 'admin_footer-edit.php', array( &$this, 'bulk_create_menus' ) );
					add_action( 'load-edit.php', array( &$this, 'bulk_handle_actions' ) );
				}
				
				$this->allowed_actions = array(
					'export',
					'delete',
					'unwant',
					'undo_unwant',
					'publish',
					'exportAll',
					'autoembed',
					'undo_autoembed',
					'update_thumbs',
				);
			}
			
			function bulk_create_menus() {
				global $post_status;
				
				$screen = get_current_screen();
				
				if ( ! wpvr_cpt_is_handled_type( $screen->post_type ) ) {
					return false;
				}
				
				?>
                <script type="text/javascript">
                    jQuery(document).ready(function () {
						
						<?php if($post_status != 'trash'){ ?>

                        jQuery('<option>').val('publish').text('- <?php echo addslashes( __( 'Publish videos', WPVR_LANG ) ); ?>').appendTo("select[name='action']");
                        jQuery('<option>').val('publish').text('- <?php echo addslashes( __( 'Publish videos', WPVR_LANG ) ); ?>').appendTo("select[name='action2']");

                        jQuery('<option>').val('update_thumbs').text('- <?php echo addslashes( __( '(re)Download Thumbnails', WPVR_LANG ) ); ?>').appendTo("select[name='action']");
                        jQuery('<option>').val('update_thumbs').text('- <?php echo addslashes( __( '(re)Download Thumbnails', WPVR_LANG ) ); ?>').appendTo("select[name='action2']");

                        jQuery('<option>').val('unwant').text('- <?php echo addslashes( __( 'Add to Unwanted', WPVR_LANG ) ); ?>').appendTo("select[name='action']");
                        jQuery('<option>').val('unwant').text('- <?php echo addslashes( __( 'Add to Unwanted', WPVR_LANG ) ); ?>').appendTo("select[name='action2']");

                        jQuery('<option>').val('undo_unwant').text('- <?php echo addslashes( __( 'Remove from Unwanted', WPVR_LANG ) ); ?>').appendTo("select[name='action']");
                        jQuery('<option>').val('undo_unwant').text('- <?php echo addslashes( __( 'Remove from Unwanted', WPVR_LANG ) ); ?>').appendTo("select[name='action2']");

                        jQuery('<option>').val('autoembed').text('- <?php echo addslashes( __( 'Enable AutoEmbed', WPVR_LANG ) ); ?>').appendTo("select[name='action']");
                        jQuery('<option>').val('autoembed').text('- <?php echo addslashes( __( 'Enable AutoEmbed', WPVR_LANG ) ); ?>').appendTo("select[name='action2']");

                        jQuery('<option>').val('undo_autoembed').text('- <?php echo addslashes( __( 'Disable AutoEmbed', WPVR_LANG ) ); ?>').appendTo("select[name='action']");
                        jQuery('<option>').val('undo_autoembed').text('- <?php echo addslashes( __( 'Disable AutoEmbed', WPVR_LANG ) ); ?>').appendTo("select[name='action2']");

                        jQuery('<option>').val('export').text('- <?php echo addslashes( __( 'Export videos', WPVR_LANG ) ); ?>').appendTo("select[name='action']");
                        jQuery('<option>').val('export').text('- <?php echo addslashes( __( 'Export videos', WPVR_LANG ) ); ?>').appendTo("select[name='action2']");

                        jQuery('<option>').val('delete').text('- <?php echo addslashes( __( 'Delete permanently', WPVR_LANG ) ); ?>').appendTo("select[name='action']");
                        jQuery('<option>').val('delete').text('- <?php echo addslashes( __( 'Delete permanently', WPVR_LANG ) ); ?>').appendTo("select[name='action2']");
						
						<?php } ?>
                    });
                </script>
				<?php
			}
			
			function bulk_clean_sendback( $sendback ) {
				$sendback = remove_query_arg( array(
					'action',
					'action2',
					'tags_input',
					'post_author',
					'comment_status',
					'ping_status',
					'_status',
					'post',
					'bulk_edit',
					'post_view',
				), $sendback );
				$sendback = str_replace( '#038;', '&', $sendback );
				
				return $sendback;
			}
			
			function bulk_perform_actions( $action, $post_ids, $sendback ) {
				
				if ( $action == 'publish' ) {
					$this->bulk_publish( $post_ids, true );
				} elseif ( $action == 'unwant' ) {
					$this->bulk_unwant( $post_ids );
				} elseif ( $action == 'undo_unwant' ) {
					$this->bulk_undo_unwant( $post_ids );
				} elseif ( $action == 'autoembed' ) {
					$this->bulk_update_meta( $post_ids, 'wpvr_video_disableAutoEmbed', 'off' );
				} elseif ( $action == 'undo_autoembed' ) {
					$this->bulk_update_meta( $post_ids, 'wpvr_video_disableAutoEmbed', 'on' );
				} elseif ( $action == 'update_thumbs' ) {
					$this->bulk_update_thumbs( $post_ids );
				} elseif ( $action == 'delete' ) {
					$this->bulk_delete_permanently( $post_ids );
				} elseif ( $action == 'export' ) {
					$sendback = esc_url( add_query_arg( array(
						'bulk_action' => 'export',
						'ids'         => join( ',', $post_ids ),
					), $sendback ) );
				}
				
				$sendback = $this->bulk_clean_sendback( $sendback );
				
				return $sendback;
			}
			
			/****/
			function bulk_handle_actions() {
				global $typenow;
				$post_type = $typenow;
				
				$wp_list_table = _get_list_table( 'WP_Posts_List_Table' );
    
				if ( !wpvr_cpt_is_handled_type( $post_type )  ) {
					return false;
				}
				
				// Get $action
				$action = $wp_list_table->current_action();
				if ( ! in_array( $action, $this->allowed_actions ) ) {
					return;
				}
				
				// Get $post_ids
				check_admin_referer( 'bulk-posts' );
				if ( isset( $_REQUEST['post'] ) ) {
					$post_ids = array_map( 'intval', $_REQUEST['post'] );
				}
				if ( empty( $post_ids ) ) {
					return;
				}
				
				// Get $sendback
				$sendback = remove_query_arg( array( 'exported', 'untrashed', 'deleted', 'ids' ), wp_get_referer() );
				if ( ! $sendback ) {
					$sendback = admin_url( "edit.php?post_type=$post_type" );
				}
				$pagenum  = $wp_list_table->get_pagenum();
				$sendback = esc_url( add_query_arg( 'paged', $pagenum, $sendback ) );
				
				
				// Perform Bulk Action
				$this->bulk_perform_actions( $action, $post_ids, $sendback );
				
				//Redirect after performing
				if ( $action == 'export' ) {
					wp_redirect( admin_url(
						'admin.php?page=wpvr&export_videos&ids=' . join( ',', $post_ids )
					) );
					exit;
				} elseif ( $action == 'delete' ) {
					//$sendback = admin_url( "edit.php?post_type=$post_type" );
					$sendback = $this->bulk_clean_sendback( $sendback );
					wp_redirect( $sendback );
					exit;
				} else {
					wp_redirect( $sendback );
				}
				
			}
			
			
			// PERMORM : Bulk Delete
			function bulk_delete_permanently( $post_ids ) {
				global $wpvr_options;
				
				$count_deleted = 0;
				
				if ( count( $post_ids ) == 0 ) {
					return false;
				}
				
				// Flush Imported videos that are going to be deleted, before deleting theml :)
				wpvr_flush_imported_videos( array(
					'post_ids' => $post_ids,
				) );
				
				//Delete WP Posts
				foreach ( (array) $post_ids as $post_id ) {
					wp_delete_post( $post_id, true );
					$count_deleted ++;
				}
				
				$message = '';
				if ( $wpvr_options['unwantOnDelete'] === true ) {
					$message .= '<br/><br/><div class="wpvr_inner_notice">';
					$message .= '<strong>' . ___( 'Notice' ) . ': </strong><br/>';
					$message .= ___( 'The deleted videos have been automatically added to unwanted. That means the plugin will skip them on next executions.' ) . '<br/>';
					$message .= ___( 'You can disable the "Auto Unwant After Deleting Videos" on the WPVR General Options.' );
					$message .= '</div> <br/>';
				}
				
				
				wpvr_render_done_notice_redirect( '<strong>' . $count_deleted . '</strong> ' . __( 'videos deleted successfully.', WPVR_LANG ) . $message, true );
				
				return true;
			}
			
			// PERFORM : bulk want
			function bulk_unwant( $post_ids ) {
				wpvr_unwant_videos( $post_ids );
				wpvr_render_done_notice_redirect( '<strong>' . count( $post_ids ) . '</strong> ' . __( 'videos added to unwanted.', WPVR_LANG ), true );
				
				return true;
			}
			
			
			function bulk_update_thumbs( $post_ids ) {
				foreach ( (array) $post_ids as $post_id ) {
					$metas = get_post_meta( $post_id );
					// d( $metas ) ;
					if ( $metas['wpvr_video_id'][0] == 'youtube' ) {
						
						$thumbs = wpvr_youtube_get_best_thumbnails( $metas['wpvr_video_id'][0] );
						
						$done = wpvr_download_featured_image(
							$thumbs['hqthumb'],
							$thumbs['thumb'],
							get_the_title( $post_id ),
							$metas['wpvr_video_service_desc'][0],
							$post_id, '', true
						);
						
					} else {
						$done = wpvr_download_featured_image(
							$metas['wpvr_video_service_hqthumb'][0],
							$metas['wpvr_video_service_thumb'][0],
							get_the_title( $post_id ),
							$metas['wpvr_video_service_desc'][0],
							$post_id, '', true
						);
					}
					
					
					//d( $done );
				}
				
				// wpvr_die();
				wpvr_render_done_notice_redirect( '<strong>' . count( $post_ids ) . '</strong> ' . __( 'videos thumbnails redownloaded.', WPVR_LANG ), true );
				
				return true;
			}
			
			// PERFORM : bulk unwant
			function bulk_undo_unwant( $post_ids ) {
				wpvr_undo_unwant_videos( $post_ids );
				wpvr_render_done_notice_redirect( '<strong>' . count( $post_ids ) . '</strong> ' . __( 'videos removed from unwanted.', WPVR_LANG ), true );
				
				return true;
			}
			
			// PERFORM : bulk publish
			function bulk_publish( $ids ) {
				$k = 0;
				
				if ( count( $ids ) == 0 ) {
					return;
				}
				foreach ( (array) $ids as $id ) {
					wp_update_post( array( 'ID' => $id, 'post_status' => 'publish' ) );
					$k ++;
				}
				wpvr_render_done_notice_redirect( '<strong>' . $k . '</strong> ' . __( 'videos published.', WPVR_LANG ), true );
				
				return true;
			}
			
			// PERMORM : Bulk Delete
			function bulk_update_meta( $post_ids, $meta_key, $meta_value ) {
				$i = 0;
				foreach ( (array) $post_ids as $post_id ) {
					$done = update_post_meta( $post_id, $meta_key, $meta_value );
					if ( $done != false ) {
						$i ++;
					}
				}
				wpvr_render_done_notice_redirect( '<strong>' . $i . '</strong> ' . __( 'videos processed.', WPVR_LANG ), true );
				
				return true;
			}
		}
	}
	
	new wpvr_videos_bulk_actions();