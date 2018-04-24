<?php
	
	function wpvr_get_recent_activity() {
		global $wpdb;
		
		$now = new DateTime( 'now', new DateTimeZone( 'UTC' ) );
		
		
		$dateB             = date( 'Y-m-d', strtotime( "-0 days" ) );
		$dateA             = date( 'Y-m-d', strtotime( "-1 months" ) );
		$this_month_period = " STR_TO_DATE(L.time, '%Y-%m-%d') BETWEEN '" . $dateA . "' AND '" . $dateB . "' ";
		
		$dateOldB          = date( 'Y-m-d', strtotime( "-1 months" ) );
		$dateOldA          = date( 'Y-m-d', strtotime( "-2 months" ) );
		$last_month_period = " STR_TO_DATE(L.time, '%Y-%m-%d') BETWEEN '" . $dateOldA . "' AND '" . $dateOldB . "' ";
		
		$dateToday    = date( 'Y-m-d' );
		$today_period = " STR_TO_DATE(L.time, '%Y-%m-%d') = '" . $dateToday . "' ";
		
		$sql
			= "
			select
				COUNT(if(L.type= 'source' AND {$this_month_period}, L.id , null ) ) as this_month_sources,
				COUNT(if(L.type= 'source' AND {$last_month_period}, L.id , null) ) as last_month_sources,
				COUNT(if(L.type= 'source' AND {$today_period}, L.id , null) ) as today_sources,
				COUNT(if(L.type= 'source' , L.id , null) ) as all_sources,
				
				COUNT(if(L.type= 'video' AND L.action = 'add' AND {$this_month_period}, L.id , null) ) as this_month_videos,
				COUNT(if(L.type= 'video' AND L.action = 'add' AND {$last_month_period}, L.id , null) ) as last_month_videos,
				COUNT(if(L.type= 'video' AND L.action = 'add' AND {$today_period}, L.id , null) ) as today_videos,
				COUNT(if(L.type= 'video' AND L.action = 'add', L.id , null) ) as all_videos,
				1 as end
			from
				{$wpdb->prefix}wpvr_logs L
			where
				1
				AND L.type IN ('video' , 'source' )
		";
		
		$sqlVideos
			= "
			select
				L.data
			from
				{$wpdb->prefix}wpvr_logs L
			where
				1
				AND L.type= 'video'
				AND L.action = 'add'
			ORDER BY L.id DESC
			LIMIT 0, 10
		";
		
		$resultsVideos = $wpdb->get_results( $sqlVideos, ARRAY_A );
		
		$videos = array();
		if ( count( $resultsVideos ) != 0 ) {
			foreach ( (array) $resultsVideos as $row ) {
				
				$videos[] = json_decode( $row['data'], true );
			}
		}
		
		
		$results = $wpdb->get_results( $sql, ARRAY_A );
		
		if ( ! isset( $results[0] ) ) {
			return array(
				'this_month_sources' => 0,
				'this_month_videos'  => 0,
				'last_month_sources' => 0,
				'last_month_videos'  => 0,
				'videos'             => $videos,
			);
		}
		
		$results[0]['videos'] = $videos;
		
		return $results[0];
		
	}
	
	function wpvr_render_donut( $args = array() ) {
		ob_start();
		
		$args = wp_parse_args( $args, array(
			'id'                => false,
			'data'              => array(),
			'total'             => 0,
			'chart_width'       => false,
			'fact'              => true,
			'legend'            => false, // false, top , bottom, left , right
			'class'             => '',
			'subtitle_plural'   => 'items',
			'hide_empty'        => true,
			'subtitle_singular' => 'item',
			'empty_label'       => __( 'No item found.', WPVR_LANG ),
		) );
		if ( $args['id'] === false ) {
			$token      = bin2hex( openssl_random_pseudo_bytes( 16 ) );
			$args['id'] = 'wpvr_xchart_' . $token;
		}
		
		$i             = 0;
		$random_colors = wpvr_generate_flat_colors( count( $args['data'] ), true, true );
		// $random_colors = wpvr_generate_colors( count( $args['data'] ), 'random' , 'dark');
		
		$data   = $labels = $colors = array();
		$legend = array();
		
		$fact_class = $args['chart_width'] == '250px' ? 'small' : '';
		
		foreach ( (array) $args['data'] as $item ) {
			
			$item = wp_parse_args( $item, array(
				'label' => 'Item',
				'color' => WPVR_UNASSIGNED_COLOR_HEX,
				'value' => 0,
			) );
			
			if ( $item['color'] === false ) {
				$i ++;
				$item['color'] = isset( $random_colors[ $i ] ) ? $random_colors[ $i ] : $random_colors[ rand( 0, count( $random_colors ) - 1 ) ];
			}
			
			
			if ( ! $args['hide_empty'] || $item['value'] != 0 ) {
				$legend[]
					= '<li class="wpvr_xchart_legend_item">
					    <i class="fa fa-circle" style="font-size:20px;margin-right:3px;color:' . $item['color'] . '"></i>
					    <span>' . wpvr_strtoupper( $item['label'] ) . ' (' . wpvr_numberK( $item['value'] ) . ')</span>
					</li>';
			}
			
			if ( $item['label'] != 'total' && $item['label'] != '@total' ) {
				$data[]   = "parseInt('" . $item['value'] . "')";
				$labels[] = "' " . wpvr_strtoupper( $item['label'] ) . "'";
				$colors[] = "'" . $item['color'] . "'";
			}
		}
		
		
		?>


        <div class="wpvr_xchart_wrap <?php echo $args['legend']; ?> <?php echo $args['legend'] == 'left' || $args['legend'] == 'right' ? 'aside' : ''; ?>">
			
			<?php if ( $args['legend'] == 'top' && $args['total'] != 0 ) { ?>
                <div class="wpvr_xchart_legend wpvr_show_when_loaded float top" id="<?php echo $args['id']; ?>_legend"
                     style="display:none;">
					<?php echo implode( "\n", $legend ); ?>
                    <div class="wpvr_clearfix"></div>
                </div>
			<?php } ?>
			
			
			<?php if ( $args['legend'] == 'left' && $args['total'] != 0 ) { ?>
                <div class="wpvr_xchart_legend wpvr_show_when_loaded left" id="<?php echo $args['id']; ?>_legend"
                     style="display:none;">
					<?php echo implode( "\n", $legend ); ?>
                    <div class="wpvr_clearfix"></div>
                </div>
			<?php } ?>

            <div
                    class="wpvr_xchart_drawing <?php echo $args['legend']; ?> <?php echo $args['class']; ?>"
                    style="min-height:250px;<?php echo $args['chart_width'] !== false ? 'width:' . $args['chart_width'] . ';margin:0 auto;' : ''; ?>"
            >
				<?php if ( $args['fact'] === true ) { ?>
                    <div class="wpvr_xchart_fact  <?php echo $fact_class; ?>" style="display:none;">
						<?php if ( $args['total'] != 0 ) { ?>
                            <span><?php echo wpvr_numberK( $args['total'] ); ?></span>
                            <i class="wpvr_graph_fact_subtitle">
								<?php echo $args['total'] > 1 ? $args['subtitle_plural'] : $args['subtitle_singular']; ?>
                            </i>
						<?php } else { ?>
                            <span style="color:#CCC;"><i class="fa fa-frown-o"></i></span>
                            <i class="wpvr_graph_fact_subtitle">
								<?php echo $args['empty_label']; ?>
                            </i>
						<?php } ?>
                    </div>
				<?php } ?>

                <canvas id="<?php echo $args['id']; ?>"></canvas>

            </div>
			
			<?php if ( $args['legend'] == 'bottom' && $args['total'] != 0 ) { ?>
                <div class="wpvr_xchart_legend wpvr_show_when_loaded float bottom"
                     id="<?php echo $args['id']; ?>_legend" style="display:none;">
					<?php echo implode( "\n", $legend ); ?>
                    <div class="wpvr_clearfix"></div>
                </div>
			<?php } ?>
			
			<?php if ( $args['legend'] == 'right' && $args['total'] != 0 ) { ?>
                <div class="wpvr_xchart_legend wpvr_show_when_loaded right" id="<?php echo $args['id']; ?>_legend"
                     style="display:none;">
					<?php echo implode( "\n", $legend ); ?>
                    <div class="wpvr_clearfix"></div>
                </div>
			<?php } ?>

            <div class="wpvr_clearfix"></div>

        </div>


        <script>

            jQuery(document).ready(function ($) {

                var data_videos_by_status = {
                    labels: [<?php echo implode( ',', $labels ); ?>],
                    datasets: [{
                        label: 'Dataset1',
                        data: [<?php echo implode( ',', $data ); ?>],
                        backgroundColor: [<?php echo implode( ',', $colors ); ?>],
                        hoverBackgroundColor: [<?php echo implode( ',', $colors ); ?>],
                    }],
                };

                wpvr_draw_doughnut($('#<?php echo $args['id']; ?>'), data_videos_by_status);


            });
        </script>
		
		<?php
		$output_string = ob_get_contents();
		ob_end_clean();
		
		return $output_string;
		
		
	}
	
	function wpvr_render_stress_chart( $data ) {
		$graph_id = 'wpvr_chart_stress_graph-' . rand( 100, 10000 );
		
		
		list( $r, $g, $b ) = sscanf( '#27a1ca', "#%02x%02x%02x" );
		$colorBis      = "rgba({$r} , {$g} , {$b} ,1)";
		$colorBisLight = "rgba({$r} , {$g} , {$b} ,0.1)";
		
		list( $r, $g, $b ) = sscanf( '#145267', "#%02x%02x%02x" );
		$colorLine      = "rgba({$r} , {$g} , {$b} ,1)";
		$colorLineLight = "rgba({$r} , {$g} , {$b} ,0.1)";
		
		$sources_dataset = array(
			'name'   => __( 'Source Executions', WPVR_LANG ),
			'labels' => array(),
			'values' => array(),
		);
		
		$videos_dataset = array(
			'name'   => __( 'Videos Fetched', WPVR_LANG ),
			'labels' => array(),
			'values' => array(),
		);
		
		$security_values = array();
		
		global $wpvr_hours, $wpvr_hours_us, $wpvr_options;
		$wpvr_hours_formatted = $wpvr_options['timeFormat'] == 'standard' ? $wpvr_hours : $wpvr_hours_us;
		
		$max_sources = 0;
		$danger      = false;
		$max_videos  = WPVR_SECURITY_WANTED_VIDEOS_HOUR;
		foreach ( (array) $data as $time => $row ) {
			$sources_dataset['labels'][] = '"' . $wpvr_hours_formatted [ $time ] . '"';
			$sources_dataset['values'][] = count( $row['sources'] );
			
			if ( count( $row['sources'] ) > $max_sources ) {
				$max_sources = count( $row['sources'] );
			}
			if ( $row['wanted'] > $max_videos ) {
				$max_videos = $row['wanted'];
				$danger     = true;
			}
			
			$security_values[] = WPVR_SECURITY_WANTED_VIDEOS_HOUR;
			
			$videos_dataset['labels'][] = '"' . $wpvr_hours_formatted [ $time ] . '"';
			$videos_dataset['values'][] = $row['wanted'];
		}
		
		if ( $danger === true ) {
			list( $r, $g, $b ) = sscanf( '#E4503C', "#%02x%02x%02x" );
			$color      = "rgba({$r} , {$g} , {$b} ,1)";
			$colorLight = "rgba({$r} , {$g} , {$b} ,0.1)";
		} else {
			list( $r, $g, $b ) = sscanf( '#1BA39C', "#%02x%02x%02x" );
			$color      = "rgba({$r} , {$g} , {$b} ,1)";
			$colorLight = "rgba({$r} , {$g} , {$b} ,0.1)";
		}
		
		ob_start();
		?>

        <div class="wpvr_graph_wrapper" style="padding:1em;">
            <canvas id="<?php echo $graph_id; ?>" height="300"></canvas>
        </div>
        <script>

            jQuery(document).ready(function ($) {


                var chart_options = {
                    animateScale: false,
                    animationSteps: 50,
                    animationEasing: "easeOutQuart",
                    maintainAspectRatio: false,
                    responsive: true,
                    legend: {
                        display: true,
                    },
                    scaleStartValue: 0,
                    scaleStepWidth: 50,
                    elements: {
                        line: {
                            tension: 0, // disables bezier curves
                        },
                    },

                    scales: {
                        xAxes: [{
                            // time: {
                            //     unit: 'day',
                            // },
                            gridLines: {
                                display: false,
                                drawTicks: true,
                            },
                            display: true,
                            ticks: {
                                fontSize: 10,
                                fontColor: '#AAA',
                            },
                        }],
                        yAxes: [{
                            id: 'sourcesAxe',
                            type: 'linear',
                            position: 'left',
                            beginAtZero: true,
                            min: 0,
                            max: 500,
                            suggestedMin: 0,
                            scaleLabel: {
                                labelString: '<?php echo __( 'Source Executions', WPVR_LANG ); ?>',
                                display: true,
                                fontSize: 10,
                                fontColor: '#AAA',
                            },
                            gridLines: {
                                display: false,
                                //drawTicks: false,
                            },
                            ticks: {
                                beginAtZero: true,
                                max: parseInt('<?php echo $max_sources; ?>') + 5,
                                callback: function (tick, index, ticks) {
                                    if (typeof tick === 'number' && tick % 1 == 0) {
                                        return tick;
                                    } else {
                                        return '';
                                    }
                                }
                            },
                        },
                            {
                                id: 'videosAxe',
                                type: 'linear',
                                position: 'right',
                                beginAtZero: true,
                                min: 0,
                                suggestedMin: 0,
                                scaleLabel: {
                                    labelString: "<?php echo __( 'Videos Fetched', WPVR_LANG ); ?>",
                                    display: true,
                                    fontSize: 10,
                                    fontColor: '#AAA',
                                },
                                gridLines: {
                                    display: true,

                                    // drawTicks: false,
                                },
                                ticks: {
                                    beginAtZero: true,
                                    max: parseInt('<?php echo $max_videos; ?>') + 5,
                                    callback: function (tick, index, ticks) {
                                        if (typeof tick === 'number' && tick % 1 == 0) {
                                            return tick;
                                        } else {
                                            return '';
                                        }
                                    }
                                },
                            }],
                    },

                    tooltips: {
                        callbacks: {
                            label: function (tooltipItem, data) {
                                return data.datasets[tooltipItem.datasetIndex].label +
                                    ': ' + tooltipItem.yLabel;
                            }
                        }
                    }


                };
                var canevasObject = $('#<?php echo $graph_id; ?>');
                canevasObject.attr("width", canevasObject.parent().width());
                var ctx = canevasObject.get(0).getContext("2d");
                setTimeout(function () {
                    var wpvr_chart_object = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: <?php echo '[' . implode( ',', $sources_dataset['labels'] ) . ']'; ?>,
                            datasets: [
                                {
                                    yAxisID: 'videosAxe',
                                    label: "<?php echo __( 'Max Recommended', WPVR_LANG ); ?>",
                                    borderWidth: 3,
                                    pointBorderWidth: 2,
                                    borderColor: "<?php echo $color; ?>",
                                    pointBackgroundColor: "<?php echo $color; ?>",
                                    backgroundColor: "<?php echo $colorLight; ?>",
                                    pointBorderColor: "rgba(255,255,255,1)",
                                    data: <?php echo '[' . implode( ',', $security_values ) . ']'; ?> ,
                                    type: 'line',

                                },
                                {
                                    yAxisID: 'videosAxe',
                                    label: "<?php echo $videos_dataset['name']; ?>",
                                    backgroundColor: "<?php echo $colorLine; ?>",
                                    data: <?php echo '[' . implode( ',', $videos_dataset['values'] ) . ']'; ?> ,
                                    type: 'bar',

                                },

                                {
                                    yAxisID: 'sourcesAxe',
                                    label: "<?php echo $sources_dataset['name']; ?>",
                                    backgroundColor: "<?php echo $colorBis; ?>",

                                    data: <?php echo '[' . implode( ',', $sources_dataset['values'] ) . ']'; ?> ,
                                    type: 'bar'
                                }
                            ]
                        },
                        options: chart_options
                    });
                }, 500);

            });
        </script>
		
		<?php
		$output_string = ob_get_contents();
		ob_end_clean();
		
		return $output_string;
		
	}
	
	function wpvr_render_activity_chart( $data ) {
		
		global $wpvr_options;
		// d( $wpvr_options );
		$time_format = $wpvr_options['timeFormat'] == 'us' ? 'hA' : 'H\H00';
		
		$graph_id = 'wpvr_chart_stress_graph-' . rand( 100, 10000 );
		
		list( $r, $g, $b ) = sscanf( '#222222', "#%02x%02x%02x" );
		$color      = "rgba({$r} , {$g} , {$b} ,1)";
		$colorLight = "rgba({$r} , {$g} , {$b} ,0.1)";
		
		list( $r, $g, $b ) = sscanf( '#27a1ca', "#%02x%02x%02x" );
		$colorBis      = "rgba({$r} , {$g} , {$b} ,1)";
		$colorBisLight = "rgba({$r} , {$g} , {$b} ,0.1)";
		
		list( $r, $g, $b ) = sscanf( '#145267', "#%02x%02x%02x" );
		$colorLine      = "rgba({$r} , {$g} , {$b} ,1)";
		$colorLineLight = "rgba({$r} , {$g} , {$b} ,0.1)";
		
		$sources_dataset = array(
			'name'   => __( "Source Executions", WPVR_LANG ),
			'labels' => array(),
			'values' => array(),
		);
		
		$videos_dataset = array(
			'name'   => __( "Videos Added", WPVR_LANG ),
			'labels' => array(),
			'values' => array(),
		);
		
		$max_sources = $max_videos = 0;
		
		foreach ( (array) $data as $time => $row ) {
			
			$oTime                       = new Datetime( $time );
			$formatted_time              = $oTime->format( "M d, {$time_format} " );
			$sources_dataset['labels'][] = '"' . $formatted_time . '"';
			$sources_dataset['values'][] = $row['source'];
			
			$videos_dataset['labels'][] = '"' . $formatted_time . '"';
			$videos_dataset['values'][] = $row['video'];
			
			if ( $row['video'] > $max_videos ) {
				$max_videos = $row['video'];
			}
			if ( $row['source'] > $max_sources ) {
				$max_sources = $row['source'];
			}
			
		}
		ob_start();
		?>

        <div class="wpvr_graph_wrapper" style="width:100% !important;height:300px;">
            <canvas id="<?php echo $graph_id; ?>" height="300"></canvas>
        </div>
        <script>

            jQuery(document).ready(function ($) {


                var chart_options = {
                    animateScale: false,
                    animationSteps: 50,
                    animationEasing: "easeOutQuart",
                    maintainAspectRatio: false,
                    responsive: true,
                    legend: {
                        display: true,
                        position: 'bottom',
                    },
                    scaleStartValue: 0,
                    scaleStepWidth: 50,
                    elements: {
                        line: {
                            // tension: 0, // disables bezier curves
                        },
                    },

                    scales: {
                        xAxes: [{
                            time: {
                                unit: 'day',
                            },
                            gridLines: {
                                display: false,
                                drawTicks: true,
                            },
                            display: false,
                            ticks: {
                                fontSize: 10,
                                fontColor: '#AAA',
                            },
                        }],
                        yAxes: [{
                            id: 'sourcesAxe',
                            type: 'linear',
                            position: 'left',
                            beginAtZero: true,
                            min: 0,
                            max: 500,
                            suggestedMin: 0,
                            scaleLabel: {
                                labelString: "<?php echo __( 'Source Executions', WPVR_LANG ); ?>",
                                display: true,
                                fontSize: 10,
                                fontColor: '#AAA',
                            },
                            gridLines: {
                                display: false,
                                //drawTicks: false,
                            },
                            ticks: {
                                beginAtZero: true,
                                max: parseInt('<?php echo $max_sources; ?>') + 2,
                                callback: function (tick, index, ticks) {
                                    if (typeof tick === 'number' && tick % 1 == 0) {
                                        return tick;
                                    } else {
                                        return '';
                                    }
                                }
                            },
                        },
                            {
                                id: 'videosAxe',
                                type: 'linear',
                                position: 'right',
                                beginAtZero: true,
                                min: 0,
                                suggestedMin: 0,
                                scaleLabel: {
                                    labelString: 'Videos Added',
                                    display: true,
                                    fontSize: 10,
                                    fontColor: '#AAA',
                                },
                                gridLines: {
                                    display: true,

                                    // drawTicks: false,
                                },
                                ticks: {
                                    beginAtZero: true,
                                    max: parseInt('<?php echo $max_videos; ?>') + 2,
                                    callback: function (tick, index, ticks) {
                                        if (typeof tick === 'number' && tick % 1 == 0) {
                                            return tick;
                                        } else {
                                            return '';
                                        }
                                    }
                                },
                            }],
                    },

                    tooltips: {
                        callbacks: {
                            label: function (tooltipItem, data) {
                                return ' ' + data.datasets[tooltipItem.datasetIndex].label +
                                    ': ' + tooltipItem.yLabel;
                            }
                        }
                    }


                };
                var canevasObject = $('#<?php echo $graph_id; ?>');
                canevasObject.attr("width", canevasObject.parent().width());
                var ctx = canevasObject.get(0).getContext("2d");
                setTimeout(function () {
                    var wpvr_chart_object = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: <?php echo '[' . implode( ',', $sources_dataset['labels'] ) . ']'; ?>,
                            datasets: [
                                {
                                    yAxisID: 'videosAxe',
                                    label: "<?php echo $videos_dataset['name']; ?>",
                                    borderWidth: 0,
                                    borderColor: "<?php echo $colorLine; ?>",
                                    pointRadius: 0,
                                    backgroundColor: "<?php echo $colorLine; ?>",

                                    data: <?php echo '[' . implode( ',', $videos_dataset['values'] ) . ']'; ?> ,
                                    type: 'bar',

                                },
                                {
                                    yAxisID: 'sourcesAxe',
                                    label: "<?php echo $sources_dataset['name']; ?>",
                                    borderWidth: 2,
                                    borderColor: "<?php echo $colorBis; ?>",
                                    backgroundColor: "<?php echo $colorBis; ?>",

                                    data: <?php echo '[' . implode( ',', $sources_dataset['values'] ) . ']'; ?> ,
                                    type: 'bar'
                                }
                            ]
                        },
                        options: chart_options
                    });
                }, 500);

            });
        </script>
		
		<?php
		$output_string = ob_get_contents();
		ob_end_clean();
		
		return $output_string;
		
	}
	
	function wpvr_get_recent_activity_data() {
		$chart_data = array();
		
		$oLogs = wpvr_get_log_entries( array(
			'nopaging' => true,
			'type'     => 'all',
			'period'   => 'lastWeekInclusive',
			'timezone' => wpvr_get_timezone(),
		) );
		
		if ( $oLogs['total'] == 0 ) {
			return $chart_data;
		}
		
		
		foreach ( $oLogs['items'] as $item ) {
			
			//Get only autorun executions
			if ( $item['owner'] != '0' ) {
				continue;
			}
			
			if ( ! isset( $chart_data[ $item['slot'] ] ) ) {
				$chart_data[ $item['slot'] ] = array(
					'video'  => 0,
					'source' => 0,
				);
			}
			
			$chart_data[ $item['slot'] ][ $item['type'] ] ++;
			
		}
		
		return wpvr_fill_missing_activity_chart_plots( $chart_data );
		// return ( $chart_data );
		
	}
	
	function wpvr_get_videos_statistics( $args = array() ) {
		
		global $wpdb, $wpvr_vs, $wpvr_status;
		
		$args = wp_parse_args( $args, array(
			'hide_empty'      => false,
			'separator'       => '|_|',
			'array_separator' => '|*|',
			'post_types'      => false,
			'skip_authors'    => false,
			'skip_services'   => false,
			'skip_categories' => false,
		) );
		
		
		$handled_post_types        = wpvr_cpt_get_handled_types( 'array' );
		$handled_post_types_sql    = " ('" . implode( "', '", $handled_post_types ) . "') ";
		$handled_post_statuses     = array( 'trash', 'pending', 'publish', 'draft', 'invalid' );
		$handled_post_statuses_sql = " ('" . implode( "', '", $handled_post_statuses ) . "') ";
		
		if ( $args['post_types'] !== false && count( $args['post_types'] ) != 0 ) {
			$handled_post_types_sql = " ('" . implode( "', '", $args['post_types'] ) . "') ";
		}
		
		$stats = array(
			'@total'     => array( 'label' => ___( 'Total', 1 ), 'count' => 0 ),
			'taxonomies' => array(
				'category' => array(
					'@total' => array( 'label' => ___( 'Total', 1 ), 'count' => 0 ),
					'@none'  => array( 'label' => ___( 'Unassigned', 1 ), 'count' => 0 ),
				),
			),
			'services'   => array( '@total' => array( 'label' => ___( 'Total', 1 ), 'count' => 0 ), ),
			'authors'    => array( '@total' => array( 'label' => ___( 'Total', 1 ), 'count' => 0 ), ),
			'post_types' => array( '@total' => array( 'label' => ___( 'Total', 1 ), 'count' => 0 ), ),
			'statuses'   => array( '@total' => array( 'label' => ___( 'Total', 1 ), 'count' => 0 ), ),
		);
		
		
		//Filling Empty Values
		if ( $args['hide_empty'] === false ) {
			
			if ( $args['skip_services'] === false ) {
				//Filling Empty Services
				foreach ( (array) $wpvr_vs as $vs ) {
					$stats['services'][ $vs['id'] ] = array(
						'label' => $vs['label'],
						'color' => $vs['color'],
						'count' => 0,
					);
				}
			}
			
			//Filling Empty Post Types
			foreach ( (array) $handled_post_types as $post_type ) {
				$post_type_object = get_post_type_object( $post_type );
				if ( $post_type_object === null ) {
					continue;
				}
				$stats['post_types'][ $post_type ] = array(
					'label' => ___( $post_type_object->labels->singular_name, 1 ),
					'color' => false,
					'count' => 0,
				);
			}
			
			//Filling Empty Statuses
			foreach ( (array) $handled_post_statuses as $post_status ) {
				$stats['statuses'][ $post_status ] = array(
					'label' => $wpvr_status[ $post_status ]['label'],
					'color' => $wpvr_status[ $post_status ]['color'],
					'count' => 0,
				);
			}
			
			if ( $args['skip_authors'] === false ) {
				//Filling Empty Authors
				$wpvr_authors = wpvr_get_users( array(
					'key'   => 'user_id',
					'name'  => 'nickname',
					'order' => 'ASC',
				) );
				
				foreach ( (array) $wpvr_authors as $author_id => $author_name ) {
					$stats['authors'][ $author_id . $args['separator'] . $author_name ] = array(
						'id'    => $author_id,
						'label' => $author_name,
						'color' => false,
						'count' => 0,
					);
				}
				
				//d( $stats['authors'] );
				//return false;
			}
			
			if ( $args['skip_categories'] === false ) {
				//Filling Empty Categories
				$categories = get_terms( array(
					'taxonomy'   => 'category',
					'hide_empty' => false,
				) );
				foreach ( (array) $categories as $category ) {
					$stats['taxonomies']['category'][ $category->term_id . $args['separator'] . $category->name ] = array(
						'id'    => $category->term_id,
						'label' => $category->name,
						'color' => false,
						'count' => 0,
					);
				}
			}
			
		}
		
		
		$sql_all
			   = "
            select
                    P.ID as post_id,
                    P.post_status as post_status,
                    P.post_type as post_type,
         			CONCAT( U.ID , '{$args['separator']}' , U.user_login ) as post_author,
         			GROUP_CONCAT( DISTINCT
                      if(
                          WTT.taxonomy = 'category' ,
                          CONCAT( WT.term_id , '{$args['separator']}' , WT.name ) ,
                          ''
                      ) SEPARATOR '{$args['array_separator']}' ) as categories
                FROM
                    $wpdb->posts P
                    LEFT JOIN $wpdb->users U on U.ID = P.post_author
                    LEFT JOIN $wpdb->term_relationships WTR on P.ID = WTR.object_id
                    LEFT JOIN $wpdb->term_taxonomy WTT on WTR.term_taxonomy_id = WTT.term_taxonomy_id
                    LEFT JOIN $wpdb->terms WT on WT.term_id = WTT.term_id
                WHERE
                    P.post_type IN {$handled_post_types_sql}
                    AND P.post_status in {$handled_post_statuses_sql}
                GROUP BY P.ID
                ORDER BY P.ID DESC
              
         
		";
		$timer = wpvr_chrono_time();
		// d( $sql_all );
		$rows = $wpdb->get_results( $sql_all, OBJECT_K );
		
		//d( wpvr_chrono_time( $timer ) );
		//d(  count( $rows ) );
		//d(  $rows );
		//print_r( $rows );
		
		//return false;
		
		$rows_meta = array();
		if ( $args['skip_services'] === false ) {
			$sql_meta_better
				= "
			 select
				M.post_id as post_id,
				M.meta_value as service
            FROM
                {$wpdb->postmeta} M
                WHERE
                M.meta_key = 'wpvr_video_service'
                AND M.meta_value != ''
            GROUP BY M.post_id
            ORDER BY M.post_id DESC
            
		";
			
			$rows_meta = $wpdb->get_results( $sql_meta_better, OBJECT_K );
			//d( wpvr_chrono_time( $timer ) );
			//d( $rows_meta );
			
		}
		
		foreach ( (array) $rows as $post_id => $row ) {
			
			//Increment Total
			$stats['@total']['count'] ++;
			
			// d( $row['categories'] );
			if ( $args['skip_categories'] === false ) {
				//Collect Categories
				$stats['taxonomies']['category']['@total']['count'] ++;
				if ( $row->categories == '' ) {
					$stats['taxonomies']['category']['@none']['count'] ++;
				} else {
					foreach ( (array) explode( $args['array_separator'], $row->categories ) as $category ) {
						if ( $category == '' ) {
							continue;
						}
						$stats['taxonomies']['category'][ $category ]['count'] ++;
					}
				}
			}
			
			
			//Collect post_status
			$stats['statuses']['@total']['count'] ++;
			$stats['statuses'][ $row->post_status ]['count'] ++;
			
			if ( $args['skip_authors'] === false ) {
				//Collect post_author
				$stats['authors']['@total']['count'] ++;
				if ( isset( $stats['authors'][ $row->post_author ] ) ) {
					$stats['authors'][ $row->post_author ]['count'] ++;
				}
			}
			
			
			//Collect post_type
			$stats['post_types']['@total']['count'] ++;
			$stats['post_types'][ $row->post_type ]['count'] ++;
			
			if ( $args['skip_services'] === false ) {
				//Collect services
				// d( $post_id ) ;
				// d( $rows_meta[ $post_id ] ) ;
				$stats['services']['@total']['count'] ++;
				if ( isset( $stats['services'][ $rows_meta[ $post_id ]->service ] ) ) {
					$stats['services'][ $rows_meta[ $post_id ]->service ]['count'] ++;
				}
			}
		}
		
		return $stats;
	}
	
	function wpvr_generate_videos_chart_data( $videos_stats ) {
		$videos_data = array();
		foreach ( (array) $videos_stats as $scope_id => $scope_data ) {
			
			if ( $scope_id == '@total' ) {
				$videos_data['total'] = $scope_data['count'];
				continue;
			}
			
			if ( ! isset( $videos_data[ $scope_id ] ) ) {
				$videos_data[ $scope_id ] = array();
			}
			
			if ( $scope_id == 'taxonomies' ) {
				// d( $data  );
				foreach ( (array) $scope_data as $taxonomy => $taxonomy_data ) {
					if ( ! isset( $videos_data['taxonomies'][ $taxonomy ] ) ) {
						$videos_data['taxonomies'][ $taxonomy ] = array();
					}
					foreach ( (array) $taxonomy_data as $taxonomy_key => $taxonomy_value ) {
						
						if ( $taxonomy_key == '@total' ) {
							continue;
						}
						
						if ( $taxonomy_key == '@none' ) {
							$videos_data['taxonomies'][ $taxonomy ][] = array(
								'label' => ___( 'Unassigned', 1 ),
								'color' => WPVR_UNASSIGNED_COLOR_HEX,
								'value' => $taxonomy_value['count'],
							);
						} else {
							$videos_data['taxonomies'][ $taxonomy ][] = array(
								'label' => $taxonomy_value['label'],
								'color' => $taxonomy_value['color'],
								'value' => $taxonomy_value['count'],
							);
						}
						
						
					}
					
				}
				
				continue;
			}
			
			
			foreach ( (array) $scope_data as $key => $data ) {
				if ( $key == '@total' ) {
					$videos_data[ $scope_id ]['total'] = $data['count'];
					continue;
				}
				
				if ( $key == '' ) {
					continue;
				}
				
				if ( ! isset( $videos_data[ $scope_id ][ $key ] ) ) {
					$videos_data[ $scope_id ][ $key ] = array();
				}
				
				$videos_data[ $scope_id ][ $key ] = array(
					'label' => addslashes( $data['label'] ),
					'color' => $data['color'],
					'value' => $data['count'],
				);
			}
		}
		
		return $videos_data;
	}
	
	function wpvr_generate_sources_chart_data( $sources_stats ) {
		
		// d( $sources_stats );
		
		$sources_data = array();
		foreach ( (array) $sources_stats as $scope_id => $scope_data ) {
			
			if ( $scope_id == '@total' ) {
				$sources_data['total'] = $scope_data['count'];
				continue;
			}
			
			if ( ! isset( $sources_data[ $scope_id ] ) ) {
				$sources_data[ $scope_id ] = array();
			}
			
			if ( $scope_id == 'post_taxonomies' ) {
				// d( $data  );
				foreach ( (array) $scope_data as $taxonomy => $taxonomy_data ) {
					if ( ! isset( $sources_data['post_taxonomies'][ $taxonomy ] ) ) {
						$sources_data['post_taxonomies'][ $taxonomy ] = array();
					}
					if ( $taxonomy == '@none' ) {
						continue;
					}
					foreach ( (array) $taxonomy_data as $taxonomy_key => $taxonomy_value ) {
						
						if ( $taxonomy_key == '@total' ) {
							$sources_data['post_taxonomies'][ $taxonomy ]['total'] = $taxonomy_value['count'];
							continue;
						}
						
						if ( $taxonomy_key == '@none' ) {
							$sources_data['post_taxonomies'][ $taxonomy ][] = array(
								'label' => ___( 'Unassigned', 1 ),
								'color' => WPVR_UNASSIGNED_COLOR_HEX,
								'value' => $taxonomy_value['count'],
							);
						} else {
							$sources_data['post_taxonomies'][ $taxonomy ][] = array(
								'label' => $taxonomy_value['label'],
								'color' => $taxonomy_value['color'],
								'value' => $taxonomy_value['count'],
							);
						}
						
						
					}
					
				}
				
				continue;
			}
			
			
			foreach ( (array) $scope_data as $key => $data ) {
				// if( $scope_id == 'authors' ) {
				// 	d( $data['label'] );
				// 	d( $data['count'] );
				// }
				
				if ( $key == '@total' ) {
					$sources_data[ $scope_id ]['total'] = $data['count'];
					continue;
				}
				
				if ( ! isset( $sources_data[ $scope_id ][ $key ] ) ) {
					$sources_data[ $scope_id ][ $key ] = array();
				}
				if ( ! isset( $data['color'] ) ) {
					$data['color'] = false;
				}
				
				$sources_data[ $scope_id ][ $key ] = array(
					'label' => addslashes( $data['label'] ),
					'color' => $data['color'],
					'value' => $data['count'],
				);
			}
		}
		
		return $sources_data;
	}
	
	function wpvr_get_sources_statistics( $args = array() ) {
		
		global $wpdb, $wpvr_vs, $wpvr_options, $wpvr_status;
		
		$args = wp_parse_args( $args, array(
			'hide_empty'      => false,
			'separator'       => '|_|',
			'array_separator' => '|*|',
			'skip_authors'    => false,
			'skip_services'   => false,
			'skip_categories' => false,
			'skip_folders'    => false,
		) );
		
		
		$handled_post_types        = wpvr_cpt_get_handled_types( 'array' );
		$handled_post_types_sql    = " ('" . implode( "', '", $handled_post_types ) . "') ";
		$handled_post_statuses     = array( 'trash', 'pending', 'publish', 'draft', 'invalid' );
		$handled_post_statuses_sql = " ('" . implode( "', '", $handled_post_statuses ) . "') ";
		
		
		$stats = array(
			'@total'          => array( 'label' => ___( 'Total', 1 ), 'count' => 0 ),
			'services'        => array( '@total' => array( 'label' => ___( 'Total', 1 ), 'count' => 0 ), ),
			'authors'         => array( '@total' => array( 'label' => ___( 'Total', 1 ), 'count' => 0 ), ),
			'types'           => array( '@total' => array( 'label' => ___( 'Total', 1 ), 'count' => 0 ), ),
			'states'          => array(
				'@total' => array( 'label' => ___( 'Total', 1 ), 'count' => 0 ),
				'on'     => array( 'label' => ___( 'Enabled', 1 ), 'count' => 0 ),
				'off'    => array( 'label' => ___( 'Disabled', 1 ), 'count' => 0 ),
			),
			'statuses'        => array(
				'@total' => array( 'label' => ___( 'Total', 1 ), 'count' => 0 ),
			),
			'folders'         => array(
				'@total' => array( 'label' => ___( 'Total', 1 ), 'count' => 0 ),
				'@none'  => array(
					'label' => ___( 'Unassigned', 1 ),
					'count' => 0,
					'color' => WPVR_UNASSIGNED_COLOR_HEX,
				),
			),
			'post_types'      => array( '@total' => array( 'label' => ___( 'Total', 1 ), 'count' => 0 ), ),
			'post_statuses'   => array( '@total' => array( 'label' => ___( 'Total', 1 ), 'count' => 0 ), ),
			'post_authors'    => array( '@total' => array( 'label' => ___( 'Total', 1 ), 'count' => 0 ), ),
			'post_taxonomies' => array(
				'category' => array(
					'@total' => array( 'label' => ___( 'Total', 1 ), 'count' => 0 ),
					'@none'  => array( 'label' => ___( 'Unassigned', 1 ), 'count' => 0 ),
				),
				'@none'    => array( 'label' => ___( 'No taxonomies', 1 ), 'count' => 0 ),
			),
		);
		
		//Filling Empty Values
		if ( $args['hide_empty'] === false ) {
			
			
			if ( $args['skip_services'] === false ) {
				//Filling Empty Services
				foreach ( (array) $wpvr_vs as $vs ) {
					$stats['services'][ $vs['id'] ] = array(
						'label' => $vs['label'],
						'color' => $vs['color'],
						'count' => 0,
					);
					foreach ( (array) $vs['types'] as $vs_type ) {
						$stats['types'][ $vs_type['global_id'] ] = array(
							'label' => $vs_type['label'],
							'color' => false,
							'count' => 0,
						);
					}
				}
			}
			
			
			//Filling Empty Post Types
			foreach ( (array) $handled_post_types as $post_type ) {
				$post_type_object = get_post_type_object( $post_type );
				if ( $post_type_object === null ) {
					continue;
				}
				$stats['post_types'][ $post_type ] = array(
					'label' => ___( $post_type_object->labels->singular_name, 1 ),
					'color' => false,
					'count' => 0,
				);
			}
			
			//Filling Empty Statuses
			foreach ( (array) $handled_post_statuses as $post_status ) {
				$stats['statuses'][ $post_status ] = array(
					'label' => $wpvr_status[ $post_status ]['label'],
					'color' => $wpvr_status[ $post_status ]['color'],
					'count' => 0,
				);
			}
			
			//Filling Empty Post Statuses
			foreach ( (array) $handled_post_statuses as $post_status ) {
				$stats['post_statuses'][ $post_status ] = array(
					'label' => $wpvr_status[ $post_status ]['label'],
					'color' => $wpvr_status[ $post_status ]['color'],
					'count' => 0,
				);
			}
			
			//Filling Empty Authors
			if ( $args['skip_authors'] === false ) {
				$wpvr_authors = wpvr_get_users( array(
					'key'   => 'user_id',
					'name'  => 'full_name',
					'order' => 'ASC',
				) );
				// d( $wpvr_authors );
				foreach ( (array) $wpvr_authors as $author_id => $author ) {
					//d( $author );
					if ( isset( $author->ID ) ) {
						$author_id   = $author->ID;
						$author_name = $author->display_name;
					} else {
						$author_name = $author;
					}
					
					// d( $author_name );
					
					//Filling Empty Source Authors
					$stats['authors'][ $author_id ] = array(
						'id'    => $author_id,
						'label' => $author_name,
						'color' => false,
						'count' => 0,
					);
					
					//Filling Empty Post Authors
					$stats['post_authors'][ $author_id . $args['separator'] . $author_name ] = array(
						'id'    => $author_id,
						'label' => $author_name,
						'color' => false,
						'count' => 0,
					);
				}
			}
			
			if ( $args['skip_categories'] === false ) {
				//Filling Empty Post Categories
				$categories = get_terms( array(
					'taxonomy'   => 'category',
					'hide_empty' => false,
				) );
				foreach ( (array) $categories as $category ) {
					$stats['post_taxonomies']['category'][ $category->term_id . $args['separator'] . $category->name ] = array(
						'id'    => $category->term_id,
						'label' => $category->name,
						'color' => false,
						'count' => 0,
					);
				}
			}
			
			if ( $args['skip_folders'] === false ) {
				//Filling Empty Folders
				$folders = get_terms( array(
					'taxonomy'   => WPVR_SFOLDER_TYPE,
					'hide_empty' => false,
				) );
				foreach ( (array) $folders as $folder ) {
					$stats['folders'][ $folder->term_id . $args['separator'] . $folder->name ] = array(
						'id'    => $folder->term_id,
						'label' => $folder->name,
						'color' => false,
						'count' => 0,
					);
				}
			}
		}
		
		
		$sql_all
			= "
            select
                    P.ID as source_id,
                    P.post_status as source_status,
                    
                    GROUP_CONCAT( DISTINCT
                      if(
                          WTT.taxonomy = '" . WPVR_SFOLDER_TYPE . "' ,
                          CONCAT( WT.term_id , '{$args['separator']}' , WT.name ) ,
                          ''
                      ) SEPARATOR '{$args['array_separator']}'
                    ) as source_folders,
                    
                    CONCAT( U.ID , '{$args['separator']}' , U.display_name ) as source_author,
                    CONCAT( U.ID ) as source_author_id,
                    
                    GROUP_CONCAT( distinct if( M.meta_key = 'wpvr_source_name' , M.meta_value , '' ) SEPARATOR '' ) as source_name,
                    GROUP_CONCAT( distinct if( M.meta_key = 'wpvr_source_service' , M.meta_value , '' ) SEPARATOR '' ) as source_service,
                    GROUP_CONCAT( distinct if( M.meta_key = 'wpvr_source_type' , M.meta_value , '' ) SEPARATOR '' ) as source_type,
                    GROUP_CONCAT( distinct if( M.meta_key = 'wpvr_source_postType' , M.meta_value , '' ) SEPARATOR '' ) as post_type,
                    GROUP_CONCAT( distinct if( M.meta_key = 'wpvr_source_postStatus' , M.meta_value , '' ) SEPARATOR '' ) as post_status,
                    GROUP_CONCAT( distinct if( M.meta_key = 'wpvr_source_postCats' , M.meta_value , '' ) SEPARATOR '' ) as post_categories,
                    GROUP_CONCAT( distinct if( M.meta_key = 'wpvr_source_postAuthor' , M.meta_value , '' ) SEPARATOR '' ) as post_author,
                    GROUP_CONCAT( distinct if( M.meta_key = 'wpvr_source_status' , M.meta_value , '' ) SEPARATOR '' ) as source_state,
                   	1 as end
                FROM
                    $wpdb->posts P
                    INNER JOIN $wpdb->postmeta M ON P.ID = M.post_id
                    LEFT JOIN $wpdb->users U on U.ID = P.post_author
                    LEFT JOIN $wpdb->term_relationships WTR on P.ID = WTR.object_id
                    LEFT JOIN $wpdb->term_taxonomy WTT on WTR.term_taxonomy_id = WTT.term_taxonomy_id
                    LEFT JOIN $wpdb->terms WT on WT.term_id = WTT.term_id
                WHERE
                    P.post_type IN ('" . WPVR_SOURCE_TYPE . "')
                    AND P.post_status in {$handled_post_statuses_sql}
                GROUP BY P.ID
		";
		// d( $sql_all );
		$rows = $wpdb->get_results( $sql_all, ARRAY_A );
		//d( $rows );
		foreach ( (array) $rows as $row ) {
			
			//Replace default values
			if ( $row['post_status'] == 'default' ) {
				$row['post_status'] = $wpvr_options['postStatus'];
			}
			
			if ( $row['post_type'] == 'default' || $row['post_type'] == '' ) {
				$row['post_type'] = $wpvr_options['postType'];
			}
			
			if ( $row['post_status'] == '' || $row['post_type'] == '' ) {
				continue;
			}
			
			//Increment Total
			$stats['@total']['count'] ++;
			
			//Collect post_status
			$stats['post_statuses']['@total']['count'] ++;
			$stats['post_statuses'][ $row['post_status'] ]['count'] ++;
			
			//Collect source_status
			$stats['statuses']['@total']['count'] ++;
			$stats['statuses'][ $row['source_status'] ]['count'] ++;
			
			if ( $args['skip_authors'] === false ) {
				//Collect post_author
				$stats['post_authors']['@total']['count'] ++;
				$json = json_decode( $row['post_author'], true );
				if ( $json !== null ) {
					$post_author = array_pop( $json );
					$post_author = $post_author == 'default' ? $wpvr_options['postAuthor'] : $post_author;
					
					$user = wpvr_get_user_by_id( $post_author );
					if ( $user !== false ) {
						$stats['post_authors'][ $user->ID . $args['separator'] . $user->data->display_name ]['count'] ++;
					}
				}
				
				//Collect source_author
				$stats['authors']['@total']['count'] ++;
				$stats['authors'][ $row['source_author_id'] ]['count'] ++;
			}
			
			if ( in_array( $row['post_type'], $handled_post_types ) ) {
				//Collect post_type
				$stats['post_types']['@total']['count'] ++;
				$stats['post_types'][ $row['post_type'] ]['count'] ++;
			}
			
			if ( $args['skip_services'] === false ) {
				//Collect services
				$stats['services']['@total']['count'] ++;
				$stats['services'][ $row['source_service'] ]['count'] ++;
				
				
				//Collect source_type
				$source_type = $wpvr_vs[ $row['source_service'] ]['types'][ $row['source_type'] ]['global_id'];
				$stats['types']['@total']['count'] ++;
				$stats['types'][ $source_type ]['count'] ++;
				
			}
			
			//Collect source_state
			$stats['states']['@total']['count'] ++;
			$stats['states'][ $row['source_state'] ]['count'] ++;
			
			if ( $args['skip_folders'] === false ) {
				//Collect source_folders
				if ( $row['source_folders'] == '' ) {
					$stats['folders']['@none']['count'] ++;
					$stats['folders']['@total']['count'] ++;
				} else {
					$stats['folders']['@total']['count'] ++;
					foreach ( (array) explode( $args['array_separator'], $row['source_folders'] ) as $source_folder ) {
						$stats['folders'][ $source_folder ]['count'] ++;
					}
				}
			}
			
			if ( $args['skip_categories'] === false ) {
				//Collect Categories
				$stats['post_taxonomies']['category']['@total']['count'] ++;
				if ( $row['post_categories'] == '' ) {
					$stats['post_taxonomies']['category']['@none']['count'] ++;
				} else {
					foreach ( (array) json_decode( $row['post_categories'], true ) as $term_id ) {
						$category = get_term( $term_id, 'category' );
						if ( is_wp_error( $category ) ) {
							continue;
						}
						$stats['post_taxonomies']['category'][ $term_id . $args['separator'] . $category->name ]['count'] ++;
					}
				}
			}
			
			
		}
		
		return $stats;
	}
	