<?php
	
	/* ADd Plugins Page WPVR menu */
	add_filter( 'plugin_action_links_' . plugin_basename( WPVR_MAIN_FILE ), 'wpvr_add_wpvr_links_to_plugins_page' );
	function wpvr_add_wpvr_links_to_plugins_page( $links ) {
		$links[] = '<br/>';
		$links[] = '<a href="' . esc_url( admin_url( 'admin.php?page=wpvr-welcome' ) ) . '" class="wpvr_first_actions_link" >' . ___( 'Welcome', true ) . '</a>';
		$links[] = '<a href="' . esc_url( admin_url( 'admin.php?page=wpvr' ) ) . '">' . ___( 'Dashboard', true ) . '</a>';
		$links[] = '<a href="' . esc_url( admin_url( 'admin.php?page=wpvr-options' ) ) . '">' . ___( 'Options', true ) . '</a>';
		$links[] = '<a href="' . esc_url( admin_url( 'admin.php?page=wpvr-licenses' ) ) . '">' . ___( 'Licenses', true ) . '</a>';
		
		return $links;
	}
	
	
	/* Define WPVR menu items */
	add_action( 'admin_menu', 'wpvr_admin_actions' );
	function wpvr_admin_actions() {
		$can_show_menu_links = wpvr_can_show_menu_links();
		if ( $can_show_menu_links === true ) {
			
			add_menu_page(
				WPVR_LANG,
				'WP Video Robot',
				'read',
				WPVR_LANG,
				'wpvr_action_render',
				WPVR_URL . "assets/images/wpadmin.icon.png"
			);
			
			add_submenu_page(
				WPVR_LANG,
				___( 'Welcome', 2 ) . ' | WP Video Robot',
				___( 'Welcome', 1 ),
				'read',
				'wpvr-welcome',
				'wpvr_welcome_render'
			);
			
			add_submenu_page(
				WPVR_LANG,
				___( 'Videos', 2 ) . ' | WP Video Robot',
				___( 'Manage Videos', 1 ),
				'read',
				'wpvr-manage',
				'wpvr_manage_render'
			);
			
			add_submenu_page(
				WPVR_LANG,
				___( 'Options', 2 ) . ' | WP Video Robot',
				___( 'Manage Options', 1 ),
				'read',
				'wpvr-options',
				'wpvr_options_render'
			);
			
			add_submenu_page(
				WPVR_LANG,
				___( 'Activity Logs', 2 ) . ' | WP Video Robot',
				___( 'Activity Logs', 1 ),
				'read',
				'wpvr-logs',
				'wpvr_logs_render'
			);
			
			add_submenu_page(
				WPVR_LANG,
				___( 'Deferred Videos', 2 ) . ' | WP Video Robot',
				___( 'Deferred Videos', 1 ),
				'read',
				'wpvr-deferred',
				'wpvr_deferred_render'
			);
			
			add_submenu_page(
				WPVR_LANG,
				___( 'Unwanted Videos', 2 ) . ' | WP Video Robot',
				___( 'Unwanted Videos', 1 ),
				'read',
				'wpvr-unwanted',
				'wpvr_unwanted_render'
			);
			
			add_submenu_page(
				WPVR_LANG,
				___( 'Import Panel', 2 ) . ' | WP Video Robot',
				___( 'Import Panel', 1 ),
				'read',
				'wpvr-import',
				'wpvr_import_render'
			);
			
			add_submenu_page(
				WPVR_LANG,
				___( 'Manage Licenses', 2 ) . ' | WP Video Robot',
				___( 'Manage Licenses', 1 ),
				'read',
				'wpvr-licenses',
				'wpvr_licenses_render'
			);
			
			if ( WPVR_DEV_MODE === true || WPVR_ENABLE_SANDBOX === true ) {
				add_submenu_page(
					WPVR_LANG,
					___( 'Sandbox', 2 ) . ' | WP Video Robot',
					___( 'Sandbox', 1 ),
					'read',
					'wpvr-sandbox',
					'wpvr_sandbox_render'
				);
			}
			
			/* Removing Main WPVR Menu Item */
			global $submenu;
			$submenu[ WPVR_LANG ][0][0] = ___( 'Dashboard', 1 );
			
		}
	}
	
	/* Add Menu of Addons */
	add_action( 'admin_menu', 'wpvr_addons_admin_actions' );
	function wpvr_addons_admin_actions() {
		if ( WPVR_ENABLE_ADDONS === true ) {
			
			$can_show_menu_links = wpvr_can_show_menu_links();
			if ( $can_show_menu_links === true ) {
				add_menu_page(
					'WPVRM',
					'WPVR Addons',
					'read',
					'wpvr-addons',
					'wpvr_addons_render',
					WPVR_URL . "assets/images/wpadmin.icon.png"
				);
				add_submenu_page(
					'wpvr-addons',
					___( 'ADDONS | WP video Robot', true ),
					___( 'Browse Addons', true ),
					'read',
					'wpvr-addons',
					'wpvr_addons_render'
				);
				
				/* Removing Main WPVR Menu Item */
				global $menu, $submenu, $wpvr_addons;
				//$submenu['wpvr-addons'][0][0] = ___('Browse Addons', true );
			}
		}
	}
	
	
	add_filter( 'custom_menu_order', 'wpvr_reorder_addons_submenu' );
	function wpvr_reorder_addons_submenu( $menu_ord ) {
		global $submenu;
		$a = $b = $c = array();
		if ( ! isset( $submenu['wpvr-addons'] ) ) {
			return $menu_ord;
		}
		
		foreach ( (array) $submenu['wpvr-addons'] as $link ) {
			if ( $link[2] == 'wpvr-addons' ) {
				$a[] = $link;
			} elseif ( strpos( $link[0], '+' ) != false ) {
				$a[] = $link;
			} else {
				$b[] = $link;
			}
		}
		$submenu['wpvr-addons'] = array_merge( $a, $b );
		
		return $menu_ord;
	}
	
	/* Define WPVR menu items */
	add_action( 'admin_bar_menu', 'wpvr_adminbar_actions' );
	function wpvr_adminbar_actions() {
		$can_show_menu_links = wpvr_can_show_menu_links();
		
		if ( $can_show_menu_links === true ) {
			add_menu_page(
				WPVR_LANG,
				'WP Video Robot',
				'read',
				WPVR_LANG,
				'wpvr_action_render',
				WPVR_URL . "assets/images/wpadmin.icon.png"
			//'dashicons-lightbulb'
			);
			
			add_submenu_page(
				WPVR_LANG,
				___( 'Welcome', 2 ) .' | WP Video Robot',
				___( 'Welcome', 1 ),
				'read',
				'wpvr-welcome',
				'wpvr_welcome_render'
			);
			
			add_submenu_page(
				WPVR_LANG,
				___( 'Manage Videos', 2 ) .' | WP Video Robot',
				___( 'Manage Videos', 1 ),
				'read',
				'wpvr-manage',
				'wpvr_manage_render'
			);
			
			add_submenu_page(
				WPVR_LANG,
				___( 'Options', 2 ) .' | WP Video Robot',
				___( 'Options', 1 ),
				'read',
				'wpvr-options',
				'wpvr_options_render'
			);
			add_submenu_page(
				WPVR_LANG,
				___( 'Activity Logs', 2 ) .' | WP Video Robot',
				___( 'Activity Logs', 1 ),
				'read',
				'wpvr-log',
				'wpvr_log_render'
			);
			
			add_submenu_page(
				WPVR_LANG,
				___( 'Deferred Videos', 2 ) .' | WP Video Robot',
				___( 'Deferred Videos', 1 ),
				'read',
				'wpvr-deferred',
				'wpvr_deferred_render'
			);
			
			add_submenu_page(
				WPVR_LANG,
				___( 'Unwanted Videos', 2 ) .' | WP Video Robot',
				___( 'Unwanted Videos', 1 ),
				'read',
				'wpvr-unwanted',
				'wpvr_unwanted_render'
			);
			
			add_submenu_page(
				WPVR_LANG,
				___( 'Import Panel', 2 ) .' | WP Video Robot',
				___( 'Import Panel', 1 ),
				'read',
				'wpvr-import',
				'wpvr_import_render'
			);
			add_submenu_page(
				WPVR_LANG,
				___( 'Manage Licenses', 2 ) .' | WP Video Robot',
				___( 'Manage Licenses', 1 ),
				'read',
				'wpvr-licenses',
				'wpvr_licenses_render'
			);
			if ( WPVR_DEV_MODE === true || WPVR_ENABLE_SANDBOX === true ) {
				add_submenu_page(
					WPVR_LANG,
					___( 'Sandbox', 2 ) .' | WP Video Robot',
					___( 'Sandbox', 1 ),
					'read',
					'wpvr-sandbox',
					'wpvr_sandbox_render'
				);
			}
			
			
			/* Removing Main WPVR Menu Item */
			global $menu;
			global $submenu;
			$submenu[ WPVR_LANG ][0][0] = ___( 'Plugin Dashboard', true );
			//remove_submenu_page( WPVR_LANG , true );
		}
	}
	
	/* Add Menu of Addons */
	add_action( 'admin_bar_menu', 'wpvr_addons_adminbar_actions', 100 );
	function wpvr_addons_adminbar_actions() {
		if ( ! WPVR_ENABLE_ADMINBAR_MENU ) {
			return false;
		}
		if ( wpvr_can_show_menu_links() ) {
			global $wp_admin_bar;
			
			// WPVR MAIN TOP BUTTON
			$wp_admin_bar->add_menu( array(
				'id'    => 'wpvr_ab',
				'title' => 'WP VIDEO ROBOT',
				'href'  => admin_url( 'admin.php?page=wpvr' ),
			) );
			
			// DASHBOARD TOP MENU
			$wp_admin_bar->add_menu( array(
				'parent' => 'wpvr_ab',
				'id'     => 'wpvr_ab_dashboard',
				'title'  => ___( 'Dashboard', 1 ),
				'href'   => admin_url( 'admin.php?page=wpvr' ),
			) );
			$wp_admin_bar->add_menu( array(
				'parent' => 'wpvr_ab_dashboard',
				'id'     => 'wpvr_ab_dashboard_content',
				'title'  => ___( 'Sources & Videos', 1 ),
				'href'   => admin_url( 'admin.php?page=wpvr&section=content' ),
			) );
			$wp_admin_bar->add_menu( array(
				'parent' => 'wpvr_ab_dashboard',
				'id'     => 'wpvr_ab_dashboard_automation',
				'title'  => ___( 'Automation', 1 ),
				'href'   => admin_url( 'admin.php?page=wpvr&section=automation' ),
			) );
			$wp_admin_bar->add_menu( array(
				'parent' => 'wpvr_ab_dashboard',
				'id'     => 'wpvr_ab_dashboard_duplicates',
				'title'  => ___( 'Duplicates Cleaner', 1 ),
				'href'   => admin_url( 'admin.php?page=wpvr&section=duplicates' ),
			) );
			$wp_admin_bar->add_menu( array(
				'parent' => 'wpvr_ab_dashboard',
				'id'     => 'wpvr_ab_dashboard_datafillers',
				'title'  => 'DataFillers',
				'href'   => admin_url( 'admin.php?page=wpvr&section=datafillers' ),
			) );
			$wp_admin_bar->add_menu( array(
				'parent' => 'wpvr_ab_dashboard',
				'id'     => 'wpvr_ab_dashboard_setters',
				'title'  => ___( 'Admin Actions', 1 ),
				'href'   => admin_url( 'admin.php?page=wpvr&section=setters' ),
			) );
			
			// OPTIONS TOP MENU
			$wp_admin_bar->add_menu( array(
				'parent' => 'wpvr_ab',
				'id'     => 'wpvr_ab_options',
				'title'  => ___( 'Options', 1 ),
				'href'   => admin_url( 'admin.php?page=wpvr-options' ),
			) );
			$wp_admin_bar->add_menu( array(
				'parent' => 'wpvr_ab_options',
				'id'     => 'wpvr_ab_options_general',
				'title'  => ___( 'General', 1 ),
				'href'   => admin_url( 'admin.php?page=wpvr-options&section=general' ),
			) );
			$wp_admin_bar->add_menu( array(
				'parent' => 'wpvr_ab_options',
				'id'     => 'wpvr_ab_options_fetching',
				'title'  => ___( 'Fetching', 1 ),
				'href'   => admin_url( 'admin.php?page=wpvr-options&section=fetching' ),
			) );
			$wp_admin_bar->add_menu( array(
				'parent' => 'wpvr_ab_options',
				'id'     => 'wpvr_ab_options_posting',
				'title'  => ___( 'Posting', 1 ),
				'href'   => admin_url( 'admin.php?page=wpvr-options&section=posting' ),
			) );
			$wp_admin_bar->add_menu( array(
				'parent' => 'wpvr_ab_options',
				'id'     => 'wpvr_ab_options_integration',
				'title'  => ___( 'Integration', 1 ),
				'href'   => admin_url( 'admin.php?page=wpvr-options&section=integration' ),
			) );
			$wp_admin_bar->add_menu( array(
				'parent' => 'wpvr_ab_options',
				'id'     => 'wpvr_ab_options_automation',
				'title'  => ___( 'Automation', 1 ),
				'href'   => admin_url( 'admin.php?page=wpvr-options&section=automation' ),
			) );
			$wp_admin_bar->add_menu( array(
				'parent' => 'wpvr_ab_options',
				'id'     => 'wpvr_ab_options_api_keys',
				'title'  => ___( 'API Access', false ),
				'href'   => admin_url( 'admin.php?page=wpvr-options&section=api_keys' ),
			) );
			
			$icon_search = is_admin() ? '<i class="wpvr_topmenu_icon fa fa-search"></i> ' : '' ;
			
			
			// SOURCES TOP MENU
			$wp_admin_bar->add_menu( array(
				'parent' => 'wpvr_ab',
				'id'     => 'wpvr_ab_sources',
				'title'  =>  $icon_search . ___( 'Sources', 1 ),
				'href'   => admin_url( 'edit.php?post_type=' . WPVR_SOURCE_TYPE ),
			) );
			$wp_admin_bar->add_menu( array(
				'parent' => 'wpvr_ab_sources',
				'id'     => 'wpvr_ab_sources_all',
				'title'  => ___( 'All Sources', 1 ),
				'href'   => admin_url( 'edit.php?post_type=' . WPVR_SOURCE_TYPE ),
			) );
			$wp_admin_bar->add_menu( array(
				'parent' => 'wpvr_ab_sources',
				'id'     => 'wpvr_ab_sources_new',
				'title'  => ___( 'New Source', 1 ),
				'href'   => admin_url( 'post-new.php?post_type=' . WPVR_SOURCE_TYPE ),
			) );
			
			foreach ( wpvr_cpt_get_handled_types() as $handled_type ) {
				
				$handled_type_data = get_post_type_object( $handled_type );
				$plural_label          = ___( $handled_type_data->labels->name , 1 );
				$singular_label          = ___( $handled_type_data->labels->singular_name, 1 );
				$link = $handled_type == 'post' ? 'edit.php' : 'edit.php?post_type=' . $handled_type;
				
				$icon = is_admin() ? '<i class="wpvr_topmenu_icon fa fa-database"></i> ' : '' ;
				
				$wp_admin_bar->add_menu( array(
					'parent' => 'wpvr_ab',
					'id'     => 'wpvr_ab_'.$handled_type,
					'title'  => $icon . $plural_label,
					'href'   => admin_url( $link ),
				) );
				$wp_admin_bar->add_menu( array(
					'parent' => 'wpvr_ab_'.$handled_type,
					'id'     => 'wpvr_ab_'.$handled_type.'_all',
					'title'  => sprintf( ___( 'All %s', 1 ) , $plural_label ),
					'href'   => admin_url( $link ),
				) );
				$wp_admin_bar->add_menu( array(
					'parent' =>'wpvr_ab_'.$handled_type,
					'id'     => 'wpvr_ab_'.$handled_type.'_new',
					'title'  => sprintf( ___( 'New %s', 1 ) , $singular_label ),
					'href'   => admin_url( 'post-new.php?post_type=' . $handled_type ),
				) );
				$wp_admin_bar->add_menu( array(
					'parent' =>'wpvr_ab_'.$handled_type,
					'id'     => 'wpvr_ab_'.$handled_type.'_manage',
					'title'  => sprintf( ___( 'Manage %s', 1 ) , $plural_label ),
					'href'   => admin_url( 'admin.php?page=wpvr-manage&post_type='.$handled_type ),
				) );
				$wp_admin_bar->add_menu( array(
					'parent' =>'wpvr_ab_'.$handled_type,
					'id'     => 'wpvr_ab_'.$handled_type.'_deferred',
					'title'  => sprintf( ___( 'Deferred %s', 1 ) , $plural_label ),
					'href'   => admin_url( 'admin.php?page=wpvr-deferred&post-type='.$handled_type ),
				) );
				$wp_admin_bar->add_menu( array(
					'parent' =>'wpvr_ab_'.$handled_type,
					'id'     => 'wpvr_ab_'.$handled_type.'_unwanted',
					'title'  => sprintf( ___( 'Unwanted %s', 1 ) , $plural_label ),
					'href'   => admin_url( 'admin.php?page=wpvr-unwanted&post-type='.$handled_type.''),
				) );
				
			}
			
			
			//ADDONS TOP MENU
			if ( WPVR_ENABLE_ADDONS === true ) {
				$wp_admin_bar->add_menu( array(
					'parent' => 'wpvr_ab',
					'id'     => 'wpvr_ab_addons',
					'title'  => ___( 'Addons', 1 ),
					'href'   => admin_url( 'admin.php?page=wpvr-addons' ),
				) );
				$wp_admin_bar->add_menu( array(
					'parent' => 'wpvr_ab_addons',
					'id'     => 'wpvr_ab_addons_browse',
					'title'  => ___( 'Browse Addons', 1 ),
					'href'   => admin_url( 'admin.php?page=wpvr-addons' ),
				) );
				global $wpvr_addons;
				foreach ( (array) $wpvr_addons as $addon ) {
					
					
					$addon['infos'] = wp_parse_args( $addon['infos'], array(
						'menu'        => $addon['infos']['title'],
						'menu_prefix' => '-',
					) );
					
					if ( $addon['infos']['menu'] === false ) {
						continue;
					}
					
					$menu_label = (string) $addon['infos']['menu'];
					
					if ( $addon['infos']['menu_prefix'] === false ) {
						$menu_prefix     = '';
						$menu_max_length = 19;
					} else {
						$menu_prefix     = ' ' . trim( $addon['infos']['menu_prefix'] ) . ' ';
						$menu_max_length = 18;
					}
					
					$menu_full_label = $menu_prefix . $menu_label;
					if ( strlen( $menu_label ) > $menu_max_length ) {
						$menu_cut_label = $menu_prefix . substr( $menu_label, 0, $menu_max_length ) . '...';
					} else {
						$menu_cut_label = $menu_full_label;
					}
					
					
					$wp_admin_bar->add_node( array(
						'parent' => 'wpvr_ab_addons',
						'id'     => 'adminbar-' . $addon['infos']['id'],
						'title'  => $menu_cut_label,
						'href'   => admin_url( 'admin.php?page=' . $addon['infos']['id'] ),
					) );
				}
				
			}
			
			// LICENSES TOP MENU
			$wp_admin_bar->add_menu( array(
				'parent' => 'wpvr_ab',
				'id'     => 'wpvr_ab_licenses',
				'title'  => ___( 'Licenses', 1 ),
				'href'   => admin_url( 'admin.php?page=wpvr-licenses' ),
			) );
			
			// ACTIVITY LOGS TOP MENU
			$wp_admin_bar->add_menu( array(
				'parent' => 'wpvr_ab',
				'id'     => 'wpvr_ab_logs',
				'title'  => ___( 'Activity Logs', 1 ),
				'href'   => admin_url( 'admin.php?page=wpvr-logs' ),
			) );
			
			// SANDBOX
			if ( WPVR_DEV_MODE === true || WPVR_ENABLE_SANDBOX === true ) {
				$wp_admin_bar->add_menu( array(
					'parent' => 'wpvr_ab',
					'id'     => 'wpvr_ab_sandbox',
					'title'  => ___( 'Sandbox', true ),
					'href'   => admin_url( 'admin.php?page=wpvr-sandbox' ),
				) );
			}
			
			if ( WPVR_DEV_MODE ) {
				$wp_admin_bar->add_menu( array(
					'id'    => 'wpvr_dev_mode',
					'title' => '<span class="wpvr_topbar_badge warning orange wpvr_show_when_loaded" style="display:none;">  WPVR DEV MODE </span>',
					'href'  => '#',
				) );
			}
			
			if ( WPVR_IS_DEMO ) {
				$wp_admin_bar->add_menu( array(
					'id'    => 'wpvr_demo_mode',
					'title' => '<span class="wpvr_topbar_badge play green wpvr_show_when_loaded" style="display:none;"> WPVR DEMO </span>',
					'href'  => '#',
				) );
			}
			
		}
	}
	
	/* restricting Actions for demo user */
	if ( WPVR_IS_DEMO_SITE === true ) {
		add_action( 'admin_init', 'wpvr_remove_menu_pages' );
		if ( ! function_exists( 'wpvr_remove_menu_pages' ) ) {
			function wpvr_remove_menu_pages() {
				
				global $user_ID;
				
				if ( $user_ID == WPVR_IS_DEMO_USER ) {
					define( 'DISALLOW_FILE_EDIT', true );
					remove_menu_page( 'plugins.php' );
					remove_menu_page( 'users.php' );
					remove_menu_page( 'tools.php' );
				}
			}
		}
	}
	
	
	/* Rendering Options */
	function wpvr_manage_render() {
		if ( ! WPVR_NONADMIN_CAP_MANAGE && ! current_user_can( WPVR_USER_CAPABILITY ) ) {
			wpvr_refuse_access();
			
			return false;
		}
		include( WPVR_PATH . 'includes/wpvr.manage.php' );
	}
	
	
	/* Rendering Addons */
	function wpvr_welcome_render() {
		if ( ! WPVR_NONADMIN_CAP_MANAGE && ! current_user_can( WPVR_USER_CAPABILITY ) ) {
			wpvr_refuse_access();
			
			return false;
		}
		include( WPVR_PATH . 'includes/wpvr.welcome.php' );
	}
	
	/* Rendering Addons */
	function wpvr_addons_render() {
		if ( ! WPVR_NONADMIN_CAP_MANAGE && ! current_user_can( WPVR_USER_CAPABILITY ) ) {
			wpvr_refuse_access();
			
			return false;
		}
		//global $addon_id;
		//$addon_id = 'wpvrm';
		include( WPVR_PATH . 'addons/wpvr.addons.php' );
	}
	
	/* Rendering Licenses */
	function wpvr_licenses_render() {
		if ( ! WPVR_NONADMIN_CAP_MANAGE && ! current_user_can( WPVR_USER_CAPABILITY ) ) {
			wpvr_refuse_access();
			
			return false;
		}
		include( WPVR_PATH . 'includes/wpvr.licenses.php' );
	}
	
	
	function wpvr_options_render() {
		if ( ! WPVR_NONADMIN_CAP_OPTIONS && ! current_user_can( WPVR_USER_CAPABILITY ) ) {
			wpvr_refuse_access();
			
			return false;
		}
		include( WPVR_PATH . 'options/wpvr.options.php' );
	}
	
	/* Rendering Logs */
	function wpvr_log_render() {
		if ( ! WPVR_NONADMIN_CAP_LOGS && ! current_user_can( WPVR_USER_CAPABILITY ) ) {
			wpvr_refuse_access();
			
			return false;
		}
		include( WPVR_PATH . 'includes/_wpvr.log.php' );
	}
	
	function wpvr_logs_render() {
		if ( ! WPVR_NONADMIN_CAP_LOGS && ! current_user_can( WPVR_USER_CAPABILITY ) ) {
			wpvr_refuse_access();
			
			return false;
		}
		include( WPVR_PATH . 'includes/wpvr.logs.php' );
	}
	
	/* Rendering Deferred */
	function wpvr_deferred_render() {
		if ( ! WPVR_NONADMIN_CAP_DEFERRED && ! current_user_can( WPVR_USER_CAPABILITY ) ) {
			wpvr_refuse_access();
			
			return false;
		}
		include( WPVR_PATH . 'includes/wpvr.deferred.php' );
	}
	
	/* Rendering Deferred */
	function wpvr_unwanted_render() {
		if ( ! WPVR_NONADMIN_CAP_DEFERRED && ! current_user_can( WPVR_USER_CAPABILITY ) ) {
			wpvr_refuse_access();
			
			return false;
		}
		include( WPVR_PATH . 'includes/wpvr.unwanted.php' );
	}
	
	/* Rendering Actions */
	function wpvr_action_render() {
		if ( ! WPVR_NONADMIN_CAP_ACTIONS && ! current_user_can( WPVR_USER_CAPABILITY ) ) {
			wpvr_refuse_access();
			
			return false;
		}
		global $wpvr_pages;
		$wpvr_pages = true;
		include( WPVR_PATH . 'includes/wpvr.actions.php' );
	}
	
	/* Rendering Import */
	function wpvr_import_render() {
		if ( ! WPVR_NONADMIN_CAP_IMPORT && ! current_user_can( WPVR_USER_CAPABILITY ) ) {
			wpvr_refuse_access();
			
			return false;
		}
		include( WPVR_PATH . 'includes/wpvr.import.php' );
	}
	
	function wpvr_manage_videos_render() {
		if ( ! WPVR_NONADMIN_CAP_IMPORT && ! current_user_can( WPVR_USER_CAPABILITY ) ) {
			wpvr_refuse_access();
			
			return false;
		}
		include( WPVR_PATH . 'includes/wpvr.manage.php' );
	}
	
	function wpvr_sandbox_render() {
		echo "<h2>WP VIDEO ROBOT SANDBOX</h2><br/><br/>";
		include( WPVR_PATH . 'wpvr.sandbox.php' );
	}