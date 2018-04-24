<?php
	
	//$videos_stats = wpvr_get_videos_statistics(array(
	//		'skip_services' => true,
	//));
	//$videos_data  = wpvr_generate_videos_chart_data( $videos_stats );
	//
	//$sources_stats = wpvr_get_sources_statistics( array(
	//	'skip_services' => true,
	//));
	//$sources_data  = wpvr_generate_sources_chart_data( $sources_stats );
	
	$videos_stats = wpvr_get_videos_statistics(array(
		//'skip_authors'    => true ,
		// 'skip_services'   => WPVR_ENABLE_DASHBOARD_CHARTS_OPTIMIZATION ? false : true ,
		//'skip_categories' => true ,
		//'skip_folders'    => true ,
	));
	// d( WPVR_ENABLE_DASHBOARD_CHARTS_OPTIMIZATION );
	//d( $videos_stats['services'] );
	$videos_data  = wpvr_generate_videos_chart_data( $videos_stats );
	// d( $videos_data );
	$sources_stats = wpvr_get_sources_statistics( array(
		//'skip_authors'    => true ,
		//'skip_services'   => true ,
		//'skip_categories' => true ,
		//'skip_folders'    => true ,
	));
	$sources_data  = wpvr_generate_sources_chart_data( $sources_stats );


?>

<!-- LEFT DASHBOARD WIDGETS -->
<div class="postbox-container wpvr_dashboard_left_panel">
	
	<?php if ( $videos_data['total'] != 0 ) { ?>
        <!-- Videos by Status -->
        <div id="" class="postbox ">
            <h3 class="hndle"><span> <?php echo wpvr_strtoupper( __( 'Your videos', WPVR_LANG ) .' - ' . __('By Status' , WPVR_LANG ) ); ?> </span>
            </h3>
            <div class="inside">
				<?php echo wpvr_render_donut( array(
					'total'             => $videos_data['statuses']['total'],
					'subtitle_singular' => __( 'video', WPVR_LANG ),
					'subtitle_plural'   => __( 'videos', WPVR_LANG ),
					'empty_label'       => __( 'No video found.', WPVR_LANG ),
					'legend'            => 'bottom',
					'chart_width'       => '370px',
					'class'             => 'dashboard_big_chart',
					'data'              => $videos_data['statuses'],
				) ); ?>

                <div class="wpvr_clearfix"></div>
            </div>
        </div>

        <!-- Videos by post type -->
        <div id="" class="postbox ">
            <h3 class="hndle">
                <span> <?php echo wpvr_strtoupper( __( 'Your videos', WPVR_LANG ) . ' - ' . __( 'By post type', WPVR_LANG ) ); ?> </span>
            </h3>
            <div class="inside">
				<?php echo wpvr_render_donut( array(
					'total'             => $videos_data['post_types']['total'],
					'subtitle_singular' => __( 'video', WPVR_LANG ),
					'subtitle_plural'   => __( 'videos', WPVR_LANG ),
					'empty_label'       => __( 'No video found.', WPVR_LANG ),
					'legend'            => 'bottom',
					'chart_width'       => '370px',
					'class'             => 'dashboard_big_chart',
					'data'              => $videos_data['post_types'],
				) ); ?>

                <div class="wpvr_clearfix"></div>
            </div>
        </div>

        <!-- Videos by Service -->
        <div id="" class="postbox ">
            <h3 class="hndle">
                <span> <?php echo wpvr_strtoupper( __( 'Your videos', WPVR_LANG ) . ' - ' . __( 'By Service', WPVR_LANG ) ); ?> </span>
            </h3>
            <div class="inside">
	            <?php if( WPVR_ENABLE_DASHBOARD_CHARTS_OPTIMIZATION === false ){ ?>
				<?php echo wpvr_render_donut( array(
					'total'             => $videos_data['services']['total'],
					'subtitle_singular' => __( 'video', WPVR_LANG ),
					'subtitle_plural'   => __( 'videos', WPVR_LANG ),
					'empty_label'       => __( 'No video found.', WPVR_LANG ),
					'legend'            => 'bottom',
					'chart_width'       => '370px',
					'class'             => 'dashboard_big_chart',
					'data'              => $videos_data['services'],
				) ); ?>
	            <?php }else{ ?>
		            <div style="margin:2em 0; color:#CCC;font-style:italic;text-align:center;">
			            <?php echo __( 'This is disabled for better performances.' , WPVR_LANG ); ?>
		            </div>
	            <?php } ?>

                <div class="wpvr_clearfix"></div>
            </div>
        </div>

        <!-- Videos by Categories -->
        <div id="" class="postbox ">
            <h3 class="hndle">
                <span> <?php echo wpvr_strtoupper( __( 'Your videos', WPVR_LANG ) . ' - ' . __( 'By Category', WPVR_LANG ) ); ?> </span>
            </h3>
            <div class="inside">
				<?php echo wpvr_render_donut( array(
					'total'             => $videos_data['total'],
					'subtitle_singular' => __( 'video', WPVR_LANG ),
					'subtitle_plural'   => __( 'videos', WPVR_LANG ),
					'empty_label'       => __( 'No video found.', WPVR_LANG ),
					'legend'            => 'bottom',
					//'legend'            => false,
					'chart_width'       => '370px',
					'class'             => 'dashboard_big_chart',
					'data'              => $videos_data['taxonomies']['category'],
				) ); ?>

                <div class="wpvr_clearfix"></div>
            </div>
        </div>

        <!-- Videos by Authors -->
        <div id="" class="postbox ">
            <h3 class="hndle">
                <span> <?php echo wpvr_strtoupper( __( 'Your videos', WPVR_LANG ) . ' - ' . __( 'By Author', WPVR_LANG ) ); ?> </span>
            </h3>
            <div class="inside">
				<?php echo wpvr_render_donut( array(
					'total'             => $videos_data['authors']['total'],
					'subtitle_singular' => __( 'video', WPVR_LANG ),
					'subtitle_plural'   => __( 'videos', WPVR_LANG ),
					'empty_label'       => __( 'No video found.', WPVR_LANG ),
					'legend'            => 'bottom',
					'chart_width'       => '370px',
					'class'             => 'dashboard_big_chart',
					'data'              => $videos_data['authors'],
				) ); ?>

                <div class="wpvr_clearfix"></div>
            </div>
        </div>
	
	<?php } ?>

</div>

<!-- RIGHT DASHBOARD WIDGETS -->
<div id="postbox-container-2" class="wpvr_dashboard_right_panel postbox-container">

    <!-- Sources by Folders -->
    <div id="" class="postbox ">
        <h3 class="hndle"><span> <?php echo wpvr_strtoupper( __( 'Your sources', WPVR_LANG ) . ' - '. __( 'By folder', WPVR_LANG )); ?> </span></h3>
        <div class="inside">
			<?php echo wpvr_render_donut( array(
				'total'             => $sources_data['folders']['total'],
				'subtitle_singular' => __( 'source', WPVR_LANG ),
				'subtitle_plural'   => __( 'sources', WPVR_LANG ),
				'empty_label'       => __( 'No source found.', WPVR_LANG ),
				'legend'            => 'bottom',
				'chart_width'       => '370px',
				'class'             => 'dashboard_big_chart',
				'data'              => $sources_data['folders'],
			) ); ?>
            <div class="wpvr_clearfix"></div>
        </div>
    </div>
    
	<?php if ( $sources_data['total'] != 0 ) { ?>
    
        <!-- Sources by Post types -->
        <div id="" class="postbox ">
            <h3 class="hndle"><span> <?php echo wpvr_strtoupper( __( 'Your sources', WPVR_LANG ) . ' - '. __( 'By post type', WPVR_LANG )); ?> </span></h3>
            <div class="inside">
				<?php echo wpvr_render_donut( array(
					'total'             => $sources_data['post_types']['total'],
					'subtitle_singular' => __( 'source', WPVR_LANG ),
					'subtitle_plural'   => __( 'sources', WPVR_LANG ),
					'empty_label'       => __( 'No source found.', WPVR_LANG ),
					'legend'            => 'bottom',
					'chart_width'       => '370px',
					'class'             => 'dashboard_big_chart',
					'data'              => $sources_data['post_types'],
				) ); ?>
                <div class="wpvr_clearfix"></div>
            </div>
        </div>

        <!-- Sources by Services -->
        <div id="" class="postbox ">
            <h3 class="hndle"><span> <?php echo wpvr_strtoupper( __( 'Your sources', WPVR_LANG ) . ' - '. __( 'By Service', WPVR_LANG )); ?> </span></h3>
            <div class="inside">
				<?php echo wpvr_render_donut( array(
					'total'             => $sources_data['services']['total'],
					'subtitle_singular' => __( 'source', WPVR_LANG ),
					'subtitle_plural'   => __( 'sources', WPVR_LANG ),
					'empty_label'       => __( 'No source found.', WPVR_LANG ),
					'legend'            => 'bottom',
					'chart_width'       => '370px',
					'class'             => 'dashboard_big_chart',
					'data'              => $sources_data['services'],
				) ); ?>
                <div class="wpvr_clearfix"></div>
            </div>
        </div>

        <!-- Sources by Categories-->
        <div id="" class="postbox ">
            <h3 class="hndle"><span> <?php echo wpvr_strtoupper( __( 'Your sources', WPVR_LANG ) . ' - '. __( 'By posting category', WPVR_LANG )); ?> </span></h3>
            <div class="inside">
				<?php echo wpvr_render_donut( array(
					'total'             => $sources_data['post_taxonomies']['category']['total'],
					'subtitle_singular' => __( 'source', WPVR_LANG ),
					'subtitle_plural'   => __( 'sources', WPVR_LANG ),
					'empty_label'       => __( 'No source found.', WPVR_LANG ),
					'legend'            => 'bottom',
					'chart_width'       => '370px',
					'class'             => 'dashboard_big_chart',
					'data'              => $sources_data['post_taxonomies']['category'],
				) ); ?>
                <div class="wpvr_clearfix"></div>
            </div>
        </div>
        
        <!-- Sources by Authors -->
        <div id="" class="postbox ">
            <h3 class="hndle"><span> <?php echo wpvr_strtoupper( __( 'Your sources', WPVR_LANG ) . ' - '. __( 'By posting author', WPVR_LANG )); ?> </span></h3>
            <div class="inside">
				<?php echo wpvr_render_donut( array(
					'total'             => $sources_data['authors']['total'],
					'subtitle_singular' => __( 'source', WPVR_LANG ),
					'subtitle_plural'   => __( 'sources', WPVR_LANG ),
					'empty_label'       => __( 'No source found.', WPVR_LANG ),
					'legend'            => 'bottom',
					'chart_width'       => '370px',
					'class'             => 'dashboard_big_chart',
					'data'              => $sources_data['authors'],
				) ); ?>
                <div class="wpvr_clearfix"></div>
            </div>
        </div>

        
        
        
	<?php } ?>
</div>