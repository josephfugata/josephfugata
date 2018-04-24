<?php
	
	//@Deprecated
	// Replaced by wpvr_get_users - This function Insures backward compatibility
	function wpvr_get_authors( $invert = false, $default = false, $restrict = false, $placeholder = true ) {
		return array();
	}
	
	
	function wpvr_render_switch_option( $args = array(), $value = 0 ) {
		$args = wp_parse_args( $args, array(
			'tab'          => '',
			'id'           => '',
			'class'        => '',
			'option_class' => '',
			'label'        => '',
			'desc'         => '',
			'function_in'  => function () {
			},
			'function_out' => function () {
			},
		) );
		//d( $args );
		$option_state = $value ? 'on' : 'off';
		
		?>
        <div
                class="wpvr_option wpvr_option_switch <?php echo $option_state; ?> <?php echo $args['option_class']; ?>"
                tab="<?php echo $args['tab']; ?>"
        >
            <div class="wpvr_option_button pull-right">
				<?php wpvr_make_switch_button_new( $args['id'], $value ); ?>
				<?php $args['function_in'](); ?>
            </div>

            <div class="option_text">
				<span class="wpvr_option_title">
					<?php echo $args['label']; ?>
				</span>
                <br/>
                <p class="wpvr_option_desc">
					<?php echo $args['desc']; ?>
                </p>
            </div>
			<?php $args['function_out'](); ?>
            <div class="wpvr_clearfix"></div>
        </div>
		<?php
	}
	
	function wpvr_render_hybrid_option( $args = array(), $value = 0 ) {
		$args = wp_parse_args( $args, array(
			'tab'          => '',
			'id'           => '',
			'class'        => '',
			'option_class' => '',
			'label'        => '',
			'desc'         => '',
			'render_fct'   => function () {
			},
		) );
		// d( $args );return false;
		//$option_state = $value ? 'on' : 'off';
		
		?>
        <div
                class="wpvr_option wpvr_option_switch on"
                tab="<?php echo $args['tab']; ?>"
        >
            <div class="wpvr_option_button pull-right">
				<?php $args['render_fct'](); ?>
            </div>
            <div class="option_text">
				<span class="wpvr_option_title">
					<?php echo $args['label']; ?>
				</span>
                <br/>
                <p class="wpvr_option_desc">
					<?php echo $args['desc']; ?>
                </p>
            </div>
            <div class="wpvr_clearfix"></div>
        </div>
		<?php
	}
	
	function wpvr_render_input_option( $args = array(), $value = 0 ) {
		$args       = wp_parse_args( $args, array(
			'tab'          => '',
			'id'           => '',
			'class'        => '',
			'option_class' => '',
			'label'        => '',
			'desc'         => '',
			'placeholder'  => '',
			'size'         => 'medium',
			'attributes'   => array(),
		) );
		$attributes = "";
		foreach ( (array) $args['attributes'] as $attr_key => $attr_value ) {
			$attributes .= ' ' . $attr_key . ' = "' . $attr_value . '" ';
		}
		?>
        <div
                class="wpvr_option wpvr_option_input wpvr_input <?php echo $args['option_class']; ?> on"
                tab="<?php echo $args['tab']; ?>"
        >
            <div class="wpvr_option_button pull-right">
                <input
                        type="text"
                        class="<?php echo $args['class']; ?> wpvr_input"
                        name="<?php echo $args['id']; ?>"
                        id="<?php echo $args['id']; ?>"
                        placeholder="<?php echo $args['placeholder']; ?>"
					<?php echo $attributes; ?>
                        value="<?php echo $value; ?>"
                />
            </div>
            <div class="option_text">
				<span class="wpvr_option_title">
					<?php echo $args['label']; ?>
				</span>
                <br/>
                <p class="wpvr_option_desc">
					<?php echo $args['desc']; ?>
                </p>
            </div>
            <div class="wpvr_clearfix"></div>
        </div>
		<?php
	}
	
	function wpvr_render_textarea_option( $args = array(), $value = 0 ) {
		$args       = wp_parse_args( $args, array(
			'tab'          => '',
			'id'           => '',
			'class'        => '',
			'option_class' => '',
			'label'        => '',
			'desc'         => '',
			'placeholder'  => '',
			'size'         => 'medium',
			'attributes'   => array(),
		) );
		$attributes = "";
		foreach ( (array) $args['attributes'] as $attr_key => $attr_value ) {
			$attributes .= ' ' . $attr_key . ' = "' . $attr_value . '" ';
		}
		?>
        <div
                class="wpvr_option wpvr_option_textarea wpvr_textarea <?php echo $args['option_class']; ?> on"
                tab="<?php echo $args['tab']; ?>"
        >
            <div class="wpvr_option_button pull-right">
                <textarea
                        type="text"
                        class="<?php echo $args['class']; ?>"
                        name="<?php echo $args['id']; ?>"
                        id="<?php echo $args['id']; ?>"
                        placeholder="<?php echo $args['placeholder']; ?>"
	                <?php echo $attributes; ?>
                ><?php echo $value; ?></textarea>
            </div>
            <div class="option_text">
				<span class="wpvr_option_title">
					<?php echo $args['label']; ?>
				</span>
                <br/>
                <p class="wpvr_option_desc">
					<?php echo $args['desc']; ?>
                </p>
            </div>
            <div class="wpvr_clearfix"></div>
        </div>
		<?php
	}
	
	function wpvr_render_selectize_option( $args = array(), $value = 0 ) {
		$args = wp_parse_args( $args, array(
			'tab'          => '',
			'id'           => '',
			'class'        => '',
			'option_class' => '',
			'label'        => '',
			'desc'         => '',
			'size'         => 'medium',
			'maxItems'     => '1',
			'options'      => array(),
			'placeholder'  => __( '', WPVR_LANG ),
		) );
		
		
		?>
        <div
                class="wpvr_option wpvr_option_input<?php echo $args['option_class']; ?> on"
                tab="<?php echo $args['tab']; ?>"
        >
            <div class="wpvr_option_button pull-right">
				<?php wpvr_render_selectized_field( array(
					'name'        => $args['id'],
					'class'       => $args['class'],
					'placeholder' => $args['placeholder'],
					'values'      => $args['options'],
					'maxItems'    => 1,
				), $value ); ?>
            </div>
            <div class="option_text">
				<span class="wpvr_option_title">
					<?php echo $args['label']; ?>
				</span>
                <br/>
                <p class="wpvr_option_desc">
					<?php echo $args['desc']; ?>
                </p>
            </div>
            <div class="wpvr_clearfix"></div>
        </div>
		<?php
	}
	
	function wpvr_render_select_option( $args = array(), $value = 0 ) {
		$args = wp_parse_args( $args, array(
			'tab'          => '',
			'id'           => '',
			'class'        => '',
			'option_class' => '',
			'label'        => '',
			'desc'         => '',
			'default'      => '',
			'size'         => 'medium',
			'maxItems'     => '1',
			'options'      => array(),
			'placeholder'  => __( '', WPVR_LANG ),
			'attributes'   => array(),
		) );
		
		$attributes = "";
		foreach ( (array) $args['attributes'] as $attr_key => $attr_value ) {
			$attributes .= ' ' . $attr_key . ' = "' . $attr_value . '" ';
		}
		?>
        <div
                class="wpvr_option wpvr_option_input <?php echo $args['option_class']; ?> on"
                tab="<?php echo $args['tab']; ?>"
        >
            <div class="wpvr_option_button pull-right">

                <select
                        name="<?php echo $args['id']; ?>"
                        id="<?php echo $args['id']; ?>"
                        class="<?php echo $args['class']; ?> wpvr_select "
                        placeholder="<?php echo $args['placeholder']; ?>"
					<?php echo $attributes; ?>
                >
					<?php foreach ( (array) $args['options'] as $option_value => $option_label ) { ?>
						<?php $selected = ( $value == $option_value ) ? ' selected="selected" ' : ''; ?>
                        <option value="<?php echo $option_value; ?>" <?php echo $selected; ?>>
							<?php echo $option_label; ?>
                        </option>
					<?php } ?>
                </select>
            </div>
            <div class="option_text">
				<span class="wpvr_option_title">
					<?php echo $args['label']; ?>
				</span>
                <br/>
                <p class="wpvr_option_desc">
					<?php echo $args['desc']; ?>
                </p>
            </div>
            <div class="wpvr_clearfix"></div>
        </div>
		<?php
	}
	
	function wpvr_render_select_option_only( $args = array(), $value = 0 ) {
		$args = wp_parse_args( $args, array(
			'id'           => '',
			'class'        => '',
			'option_class' => '',
			'default'      => '',
			'size'         => 'medium',
			'maxItems'     => '1',
			'options'      => array(),
			'placeholder'  => __( '', WPVR_LANG ),
			'attributes'   => array(),
		) );
		
		$attributes = "";
		foreach ( (array) $args['attributes'] as $attr_key => $attr_value ) {
			$attributes .= ' ' . $attr_key . ' = "' . $attr_value . '" ';
		}
		?>
        <select
                name="<?php echo $args['id']; ?>"
                id="<?php echo $args['id']; ?>"
                class="<?php echo $args['class']; ?> <?php echo $args['size']; ?> wpvr_select "
                placeholder="<?php echo $args['placeholder']; ?>"
			<?php echo $attributes; ?>
        >
			<?php foreach ( (array) $args['options'] as $option_value => $option_label ) { ?>
				<?php $selected = ( $value == $option_value ) ? ' selected="selected" ' : ''; ?>
                <option value="<?php echo $option_value; ?>" <?php echo $selected; ?>>
					<?php echo $option_label; ?>
                </option>
			<?php } ?>
        </select>
		<?php
	}
	
	function wpvr_render_vs_styles( $vs ) {
		$vs_id    = $vs['id'];
		$vs_color = $vs['color'];
		
		$styles
			= "
			.wpvr_service_icon.$vs_id{ background-color:$vs_color;}\n
			.wpvr_video_author.$vs_id{ background-color:$vs_color;}\n
            .wpvr_source_icon_right.$vs_id{ background-color:$vs_color;}\n
            .wpvrArgs[service=$vs_id] , .wpvr_source_icon[service=$vs_id]{ border-color:$vs_color;}\n
            .wpvr_source_icon[service=$vs_id] .wpvr_source_icon_icon{ background-color:$vs_color;}\n
		";
		
		return $styles;
	}
	
	function wpvr_get_multiselect_options( $type ) {
		
		$options = array();
		if ( $type == 'services' ) {
			global $wpvr_vs;
			
			foreach ( (array) $wpvr_vs as $vs ) {
				$options[ $vs['id'] ] = $vs['label'];
			}
			
			return $options;
		}
		if ( $type == 'authors' ) {
			$all_users = get_users( 'orderby=post_count&order=DESC' );
			foreach ( (array) $all_users as $user ) {
				if ( ! in_array( 'subscriber', $user->roles ) ) {
					$options[ $user->data->ID ] = $user->data->user_nicename;
				}
			}
			
			return $options;
		}
		if ( $type == 'tags' ) {
			$tags = get_tags();
			foreach ( (array) $tags as $tag ) {
				$options[ $tag->term_id ] = $tag->slug;
			}
			
			return $options;
		}
		if ( $type == 'post_types_ext' ) {
			$internal_cpts = array(
				'page',
				'post',
				WPVR_VIDEO_TYPE,
				'attachment',
				'revision',
				WPVR_SOURCE_TYPE,
				'nav_menu_item',
			);
			$post_types    = get_post_types( array(//'public' => true ,
			) );
			foreach ( (array) $post_types as $cpt ) {
				if ( ! in_array( $cpt, $internal_cpts ) ) {
					$options[ $cpt ] = $cpt;
				}
			}
			
			return $options;
		}
		if ( $type == 'taxonomies' ) {
			$taxonomies = get_taxonomies( array(
				'_builtin' => false,
			), 'objects' );
			
			$internal_taxonomies = apply_filters( 'wpvr_extend_internal_taxonomies', array(
				WPVR_SFOLDER_TYPE,
				'wpvr_rule_folder',
			) );
			
			foreach ( (array) $taxonomies as $tax ) {
				if ( in_array( $tax->name, $internal_taxonomies ) ) {
					continue;
				}
				$options[ $tax->name ] = $tax->label;
				// $options[ $tax->name ] = $tax->label . ' (' . $tax->name . ')';
			}
			
			return $options;
		}
		if ( $type == 'post_types' ) {
			$post_types = get_post_types( array(
				'public' => true,
			) );
			foreach ( (array) $post_types as $cpt ) {
				$options[ $cpt ] = $cpt;
			}
		}
		if ( $type == 'all_categories' ) {
			$cats = wpvr_get_categories_count( false, true );
			foreach ( (array) $cats as $cat ) {
				$options[ $cat['value'] ] = $cat['label'] . ' (' . $cat['count'] . ')';
			}
			
			return $options;
		}
		if ( $type == 'categories' ) {
			$cats = wpvr_get_categories_count();
			foreach ( (array) $cats as $cat ) {
				$options[ $cat['value'] ] = $cat['label'] . ' (' . $cat['count'] . ')';
			}
			
			return $options;
		}
		
	}
	
	function wpvr_get_wp_editor( $content, $editor_id, $settings ) {
		ob_start();
		
		wp_editor( $content, $editor_id, $settings );
		
		$output = ob_get_contents();
		ob_get_clean();
		
		return $output;
		
		
	}
	
	function wpvr_print_default_value( $key, $true_label = '', $false_label = '' ) {
		global $wpvr_options, $wpvr_post_statuses;
		
		
		$map               = array(
			'onlyNewVideos' => array(
				true  => __( 'Skip duplicates', WPVR_LANG ),
				false => __( 'Do not skip duplicates', WPVR_LANG ),
			),
			'getTags'       => array(
				true  => __( 'Get Video Tags', WPVR_LANG ),
				false => __( 'Do not get Video Tags', WPVR_LANG ),
			),
			'getStats'      => array(
				true  => __( 'Get Video Statistics', WPVR_LANG ),
				false => __( 'Do not get Video Statistics', WPVR_LANG ),
			),
			'getPostDate'   => array(
				'original' => __( 'Original Date', WPVR_LANG ),
				'new'      => __( 'Updated Date', WPVR_LANG ),
			),
			'videoDuration' => array(
				'any'    => __( 'All Videos', WPVR_LANG ),
				'short'  => __( 'Videos less than 4min.', WPVR_LANG ),
				'medium' => __( 'Videos between 4min. and 20min.', WPVR_LANG ),
				'long'   => __( 'Videos longer than 20min.', WPVR_LANG ),
			),
			'videoQuality'  => array(
				'any'      => __( 'All Videos', WPVR_LANG ),
				'high'     => __( 'Only High Definition Videos', WPVR_LANG ),
				'standard' => __( 'Only Standard Definitions Videos', WPVR_LANG ),
			),
		);
		$map['postStatus'] = $wpvr_post_statuses;
		
		// d( $key );
		// d( $wpvr_options[ $key ] );
		if ( ! isset( $wpvr_options[ $key ] ) || ( ! is_bool( $wpvr_options[ $key ] ) && $wpvr_options[ $key ] == '' ) ) {
			return '';
		}
		// d( $map[ $key ] );
		if ( isset( $map[ $key ] ) ) {
			
			return ' (' . ucwords( $map[ $key ][ $wpvr_options[ $key ] ] ) . ') ';
		}
		
		if ( ! is_bool( $wpvr_options[ $key ] ) ) {
			return ' (' . ___( ucwords( $wpvr_options[ $key ] ), 1 ) . ') ';
		}
		
		if ( $wpvr_options[ $key ] === true ) {
			return ' (' . ucwords( $true_label ) . ') ';
		} else {
			return ' (' . ucwords( $false_label ) . ') ';
		}
		
	}
	
	function wpvr_render_dropdown( $field = array() ) {
		ob_start();
		
		$field = wpvr_extend( $field, array(
			'name'  => '',
			'id'    => '',
			'class' => '',
			
			'maxItems'    => '1',
			'placeholder' => 'Pick one or more item ...',
			'options'     => array(),
			
			'select_name'  => '',
			'select_class' => '',
			'select_id'    => '',
			
			'wrap_class' => '',
			'wrap_id'    => '',
			
			'token' => bin2hex( openssl_random_pseudo_bytes( 10 ) ),
			
			'selectize_args' => array(
				'maxItems' => 1,
			),
			
			'value' => array(),
		
		) );
		
		
		//d( $field );
		
		if ( ! is_array( $field['value'] ) ) {
			$field['value'] = array( $field['value'] );
		}
		$json_string = json_encode( $field['value'] );
		
		?>
        <div
                class="wpvr_dropdown <?php echo $field['wrap_class']; ?>"
                maxItems="<?php echo $field['maxItems']; ?>"
                id="<?php echo $field['wrap_id']; ?>"
			<?php foreach ( (array) $field['selectize_args'] as $key => $value ) { ?>
				<?php echo 'data-' . $key . ' = "' . $value . '" '; ?>
			<?php } ?>
        >
            <textarea
                    id="<?php echo $field['id']; ?>"
                    name="<?php echo $field['name']; ?>"
                    class="wpvr_dropdown_input <?php echo $field['class']; ?>"
                    style="display:none;visibility:hidden;"
            ><?php echo $json_string; ?></textarea>
            <select
                    class="wpvr_dropdown_select <?php echo $field['select_class']; ?>"
                    name="<?php echo $field['select_name']; ?>"
                    id="<?php echo $field['select_id']; ?>"
            >
				<?php if ( $field['placeholder'] !== false ) { ?>
                    <option value=""> <?php echo $field['placeholder']; ?> </option>
				<?php } ?>
				<?php foreach ( (array) $field['options'] as $oValue => $oLabel ) { ?>
					<?php
					
					if ( is_array( $oLabel ) ) {
						$option_label = ! isset( $oLabel['label'] ) ? $oLabel['label'] : '';
					} else {
						$option_label = $oLabel;
					}
					// d( $option_label );
					?>
                    <option value="<?php echo $oValue; ?>">
						<?php echo $option_label; ?>
                    </option>
				<?php } ?>
            </select>
        </div>
		
		<?php
		$output_string = ob_get_contents();
		ob_end_clean();
		
		return $output_string;
	}
	
	function wpvr_get_sources_options() {
		$source_options = array();
		$sources        = wpvr_get_sources( array(
			'post_status' => array( 'publish' ),
			'shorted'     => true,
		) );
		
		foreach ( (array) $sources as $source ) {
			$source_options[ $source->id ] = $source->name;
		}
		
		return $source_options;
		
	}
	
	function wpvr_render_source_list_names( $sources ) {
		ob_start();
		?><?php echo __( 'Sources Overview ...' ); ?><br/>
        <div style="margin-top:5px;">
			<?php foreach ( (array) $sources as $source ) { ?>
                <div style="padding: 5px;margin-bottom: 5px;background: #F9F9F9;border-radius: 3px;">
                    <span style="font-size:9px;margin-top:1px;text-transform: uppercase;background:#222;color:#FFF;padding:3px 5px;border-radius:3px;float: left;margin-right: 8px;">
                        <?php echo $source->service; ?>
                    </span>
                    <span style="font-size:14px;"><?php echo $source->name; ?></span>
                </div>
			<?php } ?>
        </div>
		
		<?php
		$output_string = ob_get_contents();
		ob_end_clean();
		
		return $output_string;
	}
	
	function wpvr_render_automation_data() {
		ob_start();
		global $wpvr_cron_token;
		$cron_url = wpvr_get_cron_url();
		?>
		<?php do_action( 'wpvr_autorun_option_description' ); ?>
        <br/><strong><?php echo __( "CRON URL", WPVR_LANG ); ?></strong>

        <div class="wpvr_code_url">
								<span class="pull-left" id="wpvr_code_url">
									<?php echo $cron_url; ?>
								</span>
			<?php wpvr_render_copy_button( 'wpvr_code_url' ); ?>
        </div>

        <br/><strong><?php echo __( "Crontab line to add", WPVR_LANG ); ?></strong>

        <div class="wpvr_code_url">
								<span class="pull-left" id="wpvr_code_url_cron">
									<?php echo ' */10 * * * * wget -q -O /dev/null ' . $cron_url; ?>
								</span>
			<?php wpvr_render_copy_button( 'wpvr_code_url_cron' ); ?>
        </div>

        <br/>
        <a href="http://support.wpvideorobot.com/how-to-configure-cron-on-wp-video-robot/">
			<?php _e( 'Help on Cron Configuring', WPVR_LANG ); ?>
        </a> |
        <a class="wpvr_button small" href="https://store.wpvideorobot.com/addons/autopilot/" target="_blank">
			<?php _e( 'Discover AutoPilot', WPVR_LANG ); ?>
        </a> |
        <a href="http://support.wpvideorobot.com"><?php _e( 'Get Support', WPVR_LANG ); ?></a>
		
		<?php
		
		
		$output = ob_get_clean();
		
		return $output;
	}
	
	function wpvr_render_wake_up_hours() {
		global $wpvr_options;
		ob_start();
		?><br/>
        <div class="wpvr_wuh_wrap">
            <input type="hidden" class="wpvr_wuh_input a" name="wakeUpHoursA"
                   value="<?php echo $wpvr_options['wakeUpHoursA']; ?>"/>
            <input type="hidden" class="wpvr_wuh_input b" name="wakeUpHoursB"
                   value="<?php echo $wpvr_options['wakeUpHoursB']; ?>"/>
            <div
                    class="wpvr_wuh_slider"
                    data-min="0"
                    data-max="23"
                    data-step="1"
            ></div>
        </div>
        <br/>
		
		
		<?php
		
		
		$output = ob_get_clean();
		
		return $output;
	}
	
	function wpvr_get_users( $args = array(), $bypass_cache = false ) {
		global $wpdb, $wpvr_options;
		
		$cache_hash = md5( json_encode( $args ) );
		if (
			$bypass_cache !== true
			&& isset( $_SESSION['wpvr_cache'] )
			&& isset( $_SESSION['wpvr_cache'][ $cache_hash ] )
		) {
			//Get Data from WPVR Cache
			return $_SESSION['wpvr_cache'][ $cache_hash ];
		}
		
		$users = array();
		
		
		$args = wp_parse_args( $args, array(
			'key'         => 'user_id', //user_id|name
			'default'     => false,
			'restrict'    => false,
			'placeholder' => false,
			'order'       => 'ASC',
			'name'        => 'nickname', //last_name|first_name|nickname|full_name
			'roles'       => array(),
		) );
		
		if( count( $args['roles'] ) == 0 ) {
			$arg['roles'] = array(
				'member',
				'author',
				'administrator',
				'editor',
			);
		}
  
		$args['roles'] = apply_filters( 'wpvr_extend_posting_authors_roles', $args['roles'] );
		
		// d( $args['roles'] );
		
		$default_label     = $default_id = false;
		$placeholder_label = $placeholder_id = false;
		
		
		//Define placeholder
		if ( $args['placeholder'] !== false ) {
			if ( $args['key'] == 'user_id' ) {
				$placeholder_id    = '';
				$placeholder_label = $args['placeholder'];
			} elseif ( $args['key'] == 'name' ) {
				$placeholder_label = '';
				$placeholder_id    = $args['placeholder'];
			}
		}
		
		if ( $args['restrict'] === true ) {
			$current_user = wp_get_current_user();
			
			if (
				in_array( 'administrator', $current_user->roles )
				|| (
					isset( $current_user->allcaps[ WPVR_USER_CAPABILITY ] )
					&& $current_user->allcaps[ WPVR_USER_CAPABILITY ] === true
				)
			) {
				//User is allowed to see other users even with restriction mode
				$args['restrict'] = false;
				
			} else {
				
				//Get user Display Name
				$name = $current_user->display_name;
				
				//Fallback if $name is empty
				if ( empty( trim( $name ) ) ) {
					$name = $current_user->user_login;
				}
				
				//Add user to array
				if ( $args['key'] == 'user_id' ) {
					$users[ $current_user->ID ] = $name;
				} elseif ( $args['key'] == 'name' ) {
					$users[ $name ] = $current_user->ID;
				}
				
				
			}
			
			
		}
		
		//Get users only on non restricted mode
		if ( $args['restrict'] === false ) {
			$having_conditions = array();
			//d( $args['roles'] );
			foreach ( (array) $args['roles'] as $role ) {
				$having_conditions[] = " roles LIKE '%\"{$role}\"%' ";
			}
			
			$having_conditions_string = count( $having_conditions ) == 0 ? '' : "HAVING ( " . implode( " OR ", $having_conditions ) . " )";
			
			
			$sql = "
			SELECT
				GROUP_CONCAT(
				    IF( UM.meta_key = 'nickname' , UM.meta_value, null)
				    SEPARATOR ''
				) as nickname,
				
				GROUP_CONCAT(
				    IF( UM.meta_key = 'first_name' , UM.meta_value, null)
				    SEPARATOR ''
				) as first_name,
				
				GROUP_CONCAT(
				    IF( UM.meta_key = 'last_name' , UM.meta_value, null)
				    SEPARATOR ''
				) as last_name,
				
				GROUP_CONCAT(
				    IF( UM.meta_key = '{$wpdb->prefix}capabilities' , UM.meta_value, null)
				    SEPARATOR ''
				) as roles,
				
				UM.user_id
			FROM
				{$wpdb->base_prefix}usermeta UM
			GROUP BY UM.user_id
			{$having_conditions_string}
		";
			
			$items = $wpdb->get_results( $sql, OBJECT_K );
			// d( $sql );
			// d( $wpdb->last_error );
			// d( $items );
			
			
			foreach ( (array) $items as $item ) {
				
				//Get user Display Name
				$name = $item->nickname;
				if ( $args['name'] == 'first_name' ) {
					$name = $item->first_name;
				} elseif ( $args['name'] == 'last_name' ) {
					$name = $item->last_name;
				} elseif ( $args['name'] == 'nickname' ) {
					$name = $item->nickname;
				} elseif ( $args['name'] == 'full_name' ) {
					$name = $item->first_name . ' ' . $item->last_name;
				}
				
				//Fallback if $name is empty
				if ( empty( trim( $name ) ) ) {
					$name = $item->nickname;
				}
				
				//Add user to array
				if ( $args['key'] == 'user_id' ) {
					$users[ $item->user_id ] = $name;
				} elseif ( $args['key'] == 'name' ) {
					$users[ $name ] = $item->user_id;
				}
				
				//Define the default User
				if ( $args['default'] !== false && $args['default'] === $item->user_id ) {
					$default_id    = 'default';
					$default_label = ' - Default - ' . ' (' . $name . ')';
				}
				
			}
			
			
			//Reorder Users
			if ( $args['order'] === 'ASC' ) {
				asort( $users, SORT_ASC );
			} else {
				arsort( $users, SORT_ASC );
			}
			
		}
		
		
		//Add Default
		if ( $default_id !== false ) {
			if ( $args['key'] == 'user_id' ) {
				$users = array( $default_id => $default_label ) + $users;
			} elseif ( $args['key'] == 'name' ) {
				$users = array( $default_label => $default_id ) + $users;
			}
		}
		
		//Add Placeholder
		if ( $placeholder_id !== false ) {
			if ( $args['key'] == 'user_id' ) {
				$users = array( $placeholder_id => $placeholder_label ) + $users;
			} elseif ( $args['key'] == 'name' ) {
				$users = array( $placeholder_label => $placeholder_id ) + $users;
			}
		}
		
		wpvr_cache_data( $users, $cache_hash );
		
		return $users;
	}
	
	function wpvr_get_post_authors() {
		global $wpvr_options;
		
		return wpvr_get_users( array(
			'key'         => 'user_id',
			'restrict'    => $wpvr_options['restrictVideos'],
			'name'        => 'full_name',
			'order'       => 'ASC',
			'default'     => $wpvr_options['postAuthor'],
			'placeholder' => __( 'Pick an author', WPVR_LANG ) . ' ...',
			'roles'       => array('administrator' , 'author' , 'editor'),
		) );
		
	}