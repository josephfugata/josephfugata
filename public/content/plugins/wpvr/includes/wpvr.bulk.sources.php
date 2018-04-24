<?php
	
	if ( ! class_exists( 'wpvr_sources_bulk_actions' ) ) {
		class wpvr_sources_bulk_actions {
			
			public $allowed_actions;
			
			public function __construct() {
				if ( is_admin() ) {
					/* Hooking the bulk functions */
					add_action( 'admin_footer-edit.php', array( &$this, 'bulk_create_menus' ) );
					add_action( 'load-edit.php', array( &$this, 'bulk_handle_actions' ) );
					//add_action( 'admin_notices' , array( &$this , 'bulk_admin_notices' ) );
				}
				
				$this->allowed_actions = array(
					'test',
					'run',
					'duplicate',
					'toggleON',
					'toggleOFF',
					'export',
					'delete',
					'exportAll',
				);
				
			}
			
			function bulk_create_menus() {
				global $post_status;
				
				$screen = get_current_screen();
				if ( $screen->post_type !== WPVR_SOURCE_TYPE ) {
					return false;
				}
				
				?>
                <script type="text/javascript">
                    jQuery(document).ready(function () {
						
						<?php if($post_status != 'trash'){ ?>

                        jQuery('<option>').val('toggleON').text('- <?php echo addslashes( __( 'Toggle sources ON', WPVR_LANG ) ); ?>').appendTo("select[name='action']");
                        jQuery('<option>').val('toggleON').text('- <?php echo addslashes( __( 'Toggle sources ON', WPVR_LANG ) ); ?>').appendTo("select[name='action2']");

                        jQuery('<option>').val('toggleOFF').text('- <?php echo addslashes( __( 'Toggle sources OFF', WPVR_LANG ) ); ?>').appendTo("select[name='action']");
                        jQuery('<option>').val('toggleOFF').text('- <?php echo addslashes( __( 'Toggle sources OFF', WPVR_LANG ) ); ?>').appendTo("select[name='action2']");


                        jQuery('<option>').val('duplicate').text('- <?php echo addslashes( __( 'Duplicate sources', WPVR_LANG ) ); ?>').appendTo("select[name='action']");
                        jQuery('<option>').val('duplicate').text('- <?php echo addslashes( __( 'Duplicate sources', WPVR_LANG ) ); ?>').appendTo("select[name='action2']");

                        jQuery('<option>').val('export').text('- <?php echo addslashes( __( 'Export sources', WPVR_LANG ) ); ?>').appendTo("select[name='action']");
                        jQuery('<option>').val('export').text('- <?php echo addslashes( __( 'Export sources', WPVR_LANG ) ); ?>').appendTo("select[name='action2']");

                        jQuery('<option>').val('test').text('- <?php echo addslashes( __( 'Test sources', WPVR_LANG ) ); ?>').appendTo("select[name='action']");
                        jQuery('<option>').val('test').text('- <?php echo addslashes( __( 'Test sources', WPVR_LANG ) ); ?>').appendTo("select[name='action2']");

                        jQuery('<option>').val('run').text('- <?php echo addslashes( __( 'Run sources', WPVR_LANG ) ); ?>').appendTo("select[name='action']");
                        jQuery('<option>').val('run').text('- <?php echo addslashes( __( 'Run sources', WPVR_LANG ) ); ?>').appendTo("select[name='action2']");
						
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
				
				if ( $action == 'toggleON' ) {
					$this->bulk_toggle( $post_ids, true );
				} elseif ( $action == 'toggleOFF' ) {
					$this->bulk_toggle( $post_ids, false );
				} elseif ( $action == 'delete' ) {
					$this->bulk_delete_permanently( $post_ids );
				} elseif ( $action == 'duplicate' ) {
					foreach ( (array) $post_ids as $id ) {
						wpvr_duplicate_source( $id );
					}
				} elseif ( $action == 'test' ) {
					$sendback = esc_url( add_query_arg( array(
						'bulk_action' => 'test',
						'ids'         => join( ',', $post_ids ),
					), $sendback ) );
				} elseif ( $action == 'run' ) {
					$sendback = esc_url( add_query_arg( array(
						'bulk_action' => 'run',
						'ids'         => join( ',', $post_ids ),
					), $sendback ) );
				} elseif ( $action == 'export' ) {
					$sendback = esc_url( add_query_arg( array(
						'bulk_action' => 'export',
						'ids'         => join( ',', $post_ids ),
					), $sendback ) );
				} elseif ( $action == 'exportAll' ) {
					$sendback = esc_url( add_query_arg( array( 'bulk_action' => 'exportAll', ), $sendback ) );
				} else {
					return false;
				}
				
				$sendback = $this->bulk_clean_sendback( $sendback );
				
				return $sendback;
			}
			
			function bulk_handle_actions() {
				
				$screen = get_current_screen();
				if ( $screen->post_type !== WPVR_SOURCE_TYPE ) {
					return false;
				}
				
				$wp_list_table = _get_list_table( 'WP_Posts_List_Table' );
				
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
				if ( $action == 'test' ) {
					wp_redirect( admin_url(
						'admin.php?page=wpvr&test_sources&ids=' . join( ',', $post_ids )
					) );
					exit;
				} elseif ( $action == 'run' ) {
					wp_redirect( admin_url(
						'admin.php?page=wpvr&run_sources&ids=' . join( ',', $post_ids )
					) );
					exit;
				} elseif ( $action == 'export' ) {
					wp_redirect( admin_url(
						'admin.php?page=wpvr&export_sources&ids=' . join( ',', $post_ids )
					) );
					exit;
				} elseif ( $action == 'exportAll' ) {
					wp_redirect( admin_url(
						'admin.php?page=wpvr&export_all_sources'
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
			
			
			// PERMORM : Bulk Toggle
			function bulk_toggle( $ids, $status = true ) {
				$k = 0;
				if ( count( $ids ) == 0 ) {
					return;
				}
				$newStatus = $status ? 'on' : 'off';
				foreach ( (array) $ids as $id ) {
					update_post_meta( $id, 'wpvr_source_status', $newStatus );
					$k ++;
				}
				
				if ( $status ) {
					wpvr_render_done_notice_redirect( '<strong>' . $k . '</strong> ' . __( 'sources toggled ON successfully.', WPVR_LANG ), true );
				} else {
					wpvr_render_done_notice_redirect( '<strong>' . $k . '</strong> ' . __( 'sources toggled OFF successfully.', WPVR_LANG ), true );
				}
				
				return true;
			}
			
			// PERMORM : Bulk Delete
			function bulk_delete_permanently( $ids ) {
				$k = 0;
				if ( count( $ids ) == 0 ) {
					return;
				}
				foreach ( (array) $ids as $id ) {
					wp_delete_post( $id, true );
					$k ++;
				}
				wpvr_render_done_notice_redirect( '<strong>' . $k . '</strong> ' . __( 'sources deleted successfully.', WPVR_LANG ), true );
				
				return true;
			}
		}
	}
	
	new wpvr_sources_bulk_actions();