<?php
	
	
	/* Show Home dashboard Stats */
	function wpvr_render_global_activity_dashboard_widget() {
		
		$newSourceLink = admin_url( 'post-new.php?post_type=' . WPVR_SOURCE_TYPE );
		$dashboardLink = admin_url( 'admin.php?page=wpvr' );
		$optionsLink   = admin_url( 'admin.php?page=wpvr-options' );
		$manageLink    = admin_url( 'admin.php?page=wpvr-manage' );
		$addonsLink    = admin_url( 'admin.php?page=wpvr-addons' );
		
		$sources_data = wpvr_generate_sources_chart_data( wpvr_get_sources_statistics( array(
			'skip_authors'    => TRUE ,
			'skip_services'   => TRUE ,
			'skip_folders'    => TRUE ,
			'skip_categories' => TRUE ,
		) ) );
		
		?>
		<div>
			<?php echo wpvr_render_donut( array(
				'total'             => $sources_data[ 'post_types' ][ 'total' ] ,
				'subtitle_singular' => __( 'source' , WPVR_LANG ) ,
				'subtitle_plural'   => __( 'sources' , WPVR_LANG ) ,
				'empty_label'       => __( 'No source found.' , WPVR_LANG ) ,
				'legend'            => 'bottom' ,
				'chart_width'       => '250px' ,
				'class'             => 'dashboard_big_chart' ,
				'data'              => $sources_data[ 'post_types' ] ,
			) ); ?>
		</div>
		<div class = "wpvr_clearfix"></div>
		
		<div class = "wpvr_dashboard_center wpvr_show_when_loaded" style = "display:none;">
			<a href = "<?php echo $newSourceLink; ?>">
				<button class = "wpvr_button wpvr_full_width wpvr_wp_dashboard_button">
					<i class = "wpvr_link_icon fa fa-plus"></i>
					<?php _e( 'Add New Source' , WPVR_LANG ); ?>
				</button>
			</a>
			
			<a href = "<?php echo $dashboardLink; ?>">
				<button class = "wpvr_button  wpvr_full_width wpvr_wp_dashboard_button">
					<i class = " wpvr_link_icon fa fa-dashboard"></i>
					<?php _e( 'View Dashboard' , WPVR_LANG ); ?>
				</button>
			</a>
			<a href = "<?php echo $manageLink; ?>">
				<button class = "wpvr_button  wpvr_full_width wpvr_wp_dashboard_button">
					<i class = "wpvr_link_icon  fa fa-film"></i>
					<?php _e( 'Manage Videos' , WPVR_LANG ); ?>
				</button>
			</a>
			<a href = "<?php echo $optionsLink; ?>">
				<button class = "wpvr_button  wpvr_full_width wpvr_wp_dashboard_button">
					<i class = "wpvr_link_icon fa fa-wrench"></i>
					<?php _e( 'Manage Options' , WPVR_LANG ); ?>
				</button>
			</a>
			
			<a href = "<?php echo $addonsLink; ?>">
				<button class = "wpvr_button  wpvr_full_width wpvr_wp_dashboard_button">
					<i class = "wpvr_link_icon fa fa-cubes"></i>
					<?php _e( 'Browse Addons' , WPVR_LANG ); ?>
				</button>
			</a>
		</div>
		<br/><br/>
		<div class = "wpvr_dashboard_version pull-left">
			<?php echo __( 'You are using' , WPVR_LANG ) . '<br/> WP Video Robot  <b>' . WPVR_VERSION . '</b>'; ?>
		</div>
		<div class = "wpvr_dashboard_links pull-right">
			<a
					href = "#"
					class = "wpvr_button small"
					id = "wpvr_system_infos">
				<i class = "wpvr_link_icon fa fa-info"></i> System Info
			</a> |
			<a href = "<?php echo WPVR_SUPPORT_URL; ?>"><?php _e( 'Get Support' , WPVR_LANG ); ?></a>
			<div id = "wpvr_export" style = "display:none;"></div>
		</div>
		<div class = "wpvr_clearfix"></div>
		
		
		<?php
		return FALSE;
		
	}
	
	function wpvr_render_content_dashboard_widget( $post , $callback_args ) {
		
		
		if ( ! isset( $callback_args[ 'args' ][ 0 ] ) ) {
			return FALSE;
		}
		
		$stats              = $callback_args[ 'args' ][ 0 ];
		$handled_post_types = $callback_args[ 'args' ][ 1 ];
		
		$videos_data = wpvr_generate_videos_chart_data( $stats );
		
		?>
		<div>
			<?php echo wpvr_render_donut( array(
				'total'             => $videos_data[ 'post_types' ][ 'total' ] ,
				'subtitle_singular' => 'Item' ,
				'subtitle_plural'   => 'Items' ,
				'empty_label'       => sprintf( __( 'No %s found.' , WPVR_LANG ) , 'Item' ) ,
				'legend'            => 'bottom' ,
				'chart_width'       => '250px' ,
				'class'             => 'dashboard_big_chart' ,
				'data'              => $videos_data[ 'post_types' ] ,
			) ); ?>
		</div>
		
		<div class = "wpvr_clearfix"></div>
		
		<?php foreach ( (array) $handled_post_types as $post_type ) { ?>
			
			<?php
			
			$handled_type_data = get_post_type_object( $post_type );
			$singular_label    = ___( $handled_type_data->labels->singular_name , 1 );
			$plural_label      = ___( $handled_type_data->labels->name , 1 );
			
			//d( $handled_type_data->labels->name );
			
			$newVideoLink = admin_url( 'post-new.php?post_type=' . $post_type );
			$deferredLink = admin_url( 'admin.php?page=wpvr-deferred&post-type=' . $post_type );
			$unwantedLink = admin_url( 'admin.php?page=wpvr-unwanted&post-type=' . $post_type );
			$reviewLink   = admin_url( 'edit.php?post_status=pending&post_type=' . $post_type );
			
			
			?>
			
			
			<div class = "wpvr_dashboard_post_type wpvr_show_when_loaded" style = "display:none;">
				
				<div class = "wpvr_dashboard_post_type_tile">
					<i class="fa fa-database"></i>
                    <?php echo sprintf( __( 'Your %s' , WPVR_LANG ) , $plural_label ); ?>
				</div>
				
				<a href = "<?php echo $newVideoLink; ?>" target = "_blank">
					<button class = "wpvr_button  small wpvr_wp_dashboard_button">
						<i class = " wpvr_link_icon fa fa-plus"></i>
						<?php echo sprintf( __( 'New %s' , WPVR_LANG ) , '' ); ?>
					</button>
				</a>
				
				<a href = "<?php echo $reviewLink; ?>" target = "_blank">
					<button class = "wpvr_button small wpvr_wp_dashboard_button">
						<i class = "wpvr_link_icon fa fa-pencil"></i>
						<?php echo sprintf( __( 'Review %s' , WPVR_LANG ) , '' ); ?>
					</button>
				</a>
				
				<a href = "<?php echo $deferredLink; ?>" target = "_blank">
					<button class = "wpvr_button small wpvr_wp_dashboard_button">
						<i class = "wpvr_link_icon  fa fa-inbox"></i>
						<?php echo sprintf( __( 'Deferred %s' , WPVR_LANG ) , '' ); ?>
					</button>
				</a>
				<a href = "<?php echo $unwantedLink; ?>" target = "_blank">
					<button class = "wpvr_button small wpvr_wp_dashboard_button">
						<i class = "wpvr_link_icon fa fa-ban"></i>
						<?php echo sprintf( __( 'Unwanted %s' , WPVR_LANG ) , '' ); ?>
					</button>
				</a>
			</div>
		<?php } ?>
		
		<div class = "wpvr_clearfix"></div>
		
		
		<?php
		return FALSE;
		
	}
	
	/* Get Playlis Data from Channel Id */
	function wpvr_get_country_name( $country_code ) {
		global $wpvr_countries;
		
		return $wpvr_countries[ $country_code ];
	}
	
	/* Render manage_filters */
	if ( ! function_exists( 'wpvr_manage_render_filters' ) ) {
		function wpvr_manage_render_filters( $filter_name , $button = TRUE ) {
			
			global $wpvr_status , $wpvr_services;
			$filter_class = 'wpvr_manage';
			
			if ( $filter_name == 'authors' ) {
				$filter = wpvr_get_authors_count();
				$prefix = 'filter_authors';
			} elseif ( $filter_name == 'dates' ) {
				
				$filter = wpvr_get_dates_count();
				$prefix = 'filter_dates';
				
			} elseif ( $filter_name == 'categories' ) {
				$filter = wpvr_get_categories_count();
				$prefix = 'filter_categories';
			} elseif ( $filter_name == 'services' ) {
				$filter = wpvr_get_services_count();
				$prefix = 'filter_services';
			} elseif ( $filter_name == 'statuses' ) {
				$filter = wpvr_get_status_count();
				$prefix = 'filter_statuses';
				
			}
			//new dBug( $filter);		return false;
			$render = '';
			//$render .= 	//'<input type="hidden" name="'.$prefix.'[]" value="0">'.
			$render .= '<div class="wpvr_manage_box_content_inner">';
			$render .= '<ul id="' . $filter_class . '_' . $prefix . '" class="' . $filter_class . ' wpvr_manage_check_ul">';
			
			if ( count( $filter ) == 0 ) {
				return FALSE;
			}
			foreach ( (array) $filter as $value => $data ) {
				
				
				if ( $filter_name == 'services' ) {
					$label = '<span class="wpvr_service_icon ' . $data[ 'value' ] . '"> ' . $data[ 'label' ] . ' </span>';
				} elseif ( $filter_name == 'statuses' ) {
					$icon  = '<i class="wpvr_video_status_icon fa ' . $wpvr_status[ $data[ 'value' ] ][ 'icon' ] . ' "></i>';
					$label = '<span class="wpvr_video_status ' . $data[ 'value' ] . '"> ' . $icon . $data[ 'label' ] . ' </span>';
				} else {
					$label = wpvr_substr( $data[ 'label' ] , 25 );
				}
				
				
				$render .= '<li id="category-289">' .
				           '<label class="selectit">' .
				           '<input type="checkbox" name="' . $prefix . '[]" value="' . $data[ 'value' ] . '" />' .
				           '<e>' . $label . '</e>' .
				           '<span class="wpvr_filter_count" >' .
				           wpvr_numberK( $data[ 'count' ] ) .
				           '</span>' .
				           '</label>' .
				           '</li>';
				
			}
			
			$render .= '</ul>';
			$render .= '</div>';
			
			if ( $button === TRUE ) {
				$render
					.= '
				<div class="wpvr_button wpvr_manage_refresh">
					<i class="wpvr_button_icon fa fa-refresh"></i>
					' . __( 'REFRESH' , WPVR_LANG ) . '
				</div>
			';
			}
			
			return $render;
		}
	}
	
	
	function wpvr_render_recent_activity_dashboard_widget() {
		
		$sourceExecutions = __( 'Source Executions' , WPVR_LANG );
		$videosAdded      = __( 'Videos Added' , WPVR_LANG );
		
		$recent_activity = wpvr_get_recent_activity();
		
		?>
		<div class = "wpvr_hide_when_loaded" style = "color:#CCC;">
			<?php echo wpvr_render_loading_message(); ?>
        </div>
		<div class = "wpvr_ra_wrap wpvr_show_when_loaded" style = "display:none;">
			<div class = "wpvr_ra_row">
				<div class = "wpvr_ra_column left">
					<div class = "wpvr_ra_title">
						<?php echo __( 'Current Month' , WPVR_LANG ); ?>
					</div>
					<div class = "wpvr_ra_line">
						<span class = "wpvr_ra_line_label"><?php echo $sourceExecutions; ?></span>
						<span class = "wpvr_ra_line_value"><?php echo wpvr_numberK( $recent_activity[ 'this_month_sources' ] ); ?></span>
						<div class = "wpvr_clearfix"></div>
					</div>
					<div class = "wpvr_ra_line">
						<span class = "wpvr_ra_line_label"><?php echo $videosAdded; ?></span>
						<span class = "wpvr_ra_line_value"><?php echo wpvr_numberK( $recent_activity[ 'this_month_videos' ] ); ?></span>
						<div class = "wpvr_clearfix"></div>
					</div>
				</div>
				<div class = "wpvr_ra_column ">
					<div class = "wpvr_ra_title">
						<?php echo __( 'Today' , WPVR_LANG ); ?>
					</div>
					<div class = "wpvr_ra_line">
						<span class = "wpvr_ra_line_label"><?php echo $sourceExecutions; ?></span>
						<span class = "wpvr_ra_line_value"><?php echo wpvr_numberK( $recent_activity[ 'today_sources' ] ); ?></span>
						<div class = "wpvr_clearfix"></div>
					</div>
					<div class = "wpvr_ra_line">
						<span class = "wpvr_ra_line_label"><?php echo $videosAdded; ?></span>
						<span class = "wpvr_ra_line_value"><?php echo wpvr_numberK( $recent_activity[ 'today_videos' ] ); ?></span>
						<div class = "wpvr_clearfix"></div>
					</div>
				</div>
				<div class = "wpvr_clearfix"></div>
			
			</div>
			
			<div class = "wpvr_ra_row">
				<div class = "wpvr_ra_column left">
					<div class = "wpvr_ra_title">
						<?php echo __( 'Last Month' , WPVR_LANG ); ?>
					</div>
					<div class = "wpvr_ra_line">
						<span class = "wpvr_ra_line_label"><?php echo $sourceExecutions; ?></span>
						<span class = "wpvr_ra_line_value"><?php echo wpvr_numberK( $recent_activity[ 'last_month_sources' ] ); ?></span>
						<div class = "wpvr_clearfix"></div>
					</div>
					<div class = "wpvr_ra_line">
						<span class = "wpvr_ra_line_label"><?php echo $videosAdded; ?></span>
						<span class = "wpvr_ra_line_value"><?php echo wpvr_numberK( $recent_activity[ 'last_month_videos' ] ); ?></span>
						<div class = "wpvr_clearfix"></div>
					</div>
				</div>
				<div class = "wpvr_ra_column ">
					<div class = "wpvr_ra_title">
						<?php echo __( 'Totals' , WPVR_LANG ); ?>
					</div>
					<div class = "wpvr_ra_line">
						<span class = "wpvr_ra_line_label"><?php echo $sourceExecutions; ?></span>
						<span class = "wpvr_ra_line_value"><?php echo wpvr_numberK( $recent_activity[ 'all_sources' ] ); ?></span>
						<div class = "wpvr_clearfix"></div>
					</div>
					<div class = "wpvr_ra_line">
						<span class = "wpvr_ra_line_label"><?php echo $videosAdded; ?></span>
						<span class = "wpvr_ra_line_value"><?php echo wpvr_numberK( $recent_activity[ 'all_videos' ] ); ?></span>
						<div class = "wpvr_clearfix"></div>
					</div>
				</div>
				<div class = "wpvr_clearfix"></div>
			
			</div>
			
			<?php if ( count( $recent_activity[ 'videos' ] ) != 0 ) { ?>
				<div class = "wpvr_ra_row ">
					<div class = "wpvr_ra_column full">
						<div class = "wpvr_ra_title">
							<?php echo __( 'Recently Added Videos' , WPVR_LANG ); ?>
							-
							<a target = "_blank" href = "<?php echo admin_url( 'admin.php?page=wpvr-logs&type=video' ); ?>"
							   class = "wpvr_ra_view_all">
								<?php echo strtoupper( __( 'View all' , WPVR_LANG ) ); ?>
							</a>
						</div>
						
						<div class = "wpvr_clearfix"></div>
						
						<?php foreach ( (array) $recent_activity[ 'videos' ] as $video ) { ?>
							<?php
							$maxlength = 45;
							if ( strlen( $video[ 'video_title' ] ) > $maxlength ) {
								$video[ 'video_title' ] = substr( $video[ 'video_title' ] , 0 , $maxlength - 3 ) . ' ... ';
							}
							
							?>
							<div class = "wpvr_ra_line video">
                <span class = "wpvr_ra_line_label">
                    <strong class = "wpvr_ra_service">[<?php echo strtoupper( $video[ 'video_service' ] ); ?>] </strong>
	                <?php echo $video[ 'video_title' ]; ?>
                </span>
								<span class = "wpvr_ra_line_buttons">
                    <?php $status = get_post_status( $video[ 'post_id' ] ); ?>
									<?php if ( $status === FALSE ) { ?>
										<?php echo __( 'Deleted' , WPVR_LANG ); ?>
									<?php } elseif ( $status === 'trash' ) { ?>
										<?php echo __( 'Trashed' , WPVR_LANG ); ?>
									<?php } else { ?>
										<a target = "_blank"
										   href = "<?php echo admin_url( 'post.php?post=' . $video[ 'post_id' ] . '&action=edit' ); ?>"
										   title = "Edit Video">
                        <?php echo __( 'Edit' , WPVR_LANG ); ?>
                    </a> |
                    <a target = "_blank" href = "<?php echo get_post_permalink( $video[ 'post_id' ] ); ?>" title = "View Video">
                        <?php echo __( 'View' , WPVR_LANG ); ?>
                    </a>
									<?php } ?>
                
                </span>
								<div class = "wpvr_clearfix"></div>
							</div>
						<?php } ?>
					</div>
					<div class = "wpvr_clearfix"></div>
				</div>
			<?php } ?>
		
		</div>
		<?php
		return FALSE;
		
	}
 