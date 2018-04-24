<?php
	
	
	//You should be suing WPVR Hooker Free plugin ...
	return false;
	
	// Register Program CPT
	add_action( 'init', 'wpvr_add_program_custom_post_type', 1 );
	function wpvr_add_program_custom_post_type() {
		
		$labels = array(
			'name'                  => _x( 'Programs', 'Program General Name', 'text_domain' ),
			'singular_name'         => _x( 'Program', 'Program Singular Name', 'text_domain' ),
			'menu_name'             => __( 'Programs', 'text_domain' ),
			'name_admin_bar'        => __( 'Program', 'text_domain' ),
			'archives'              => __( 'Program Archives', 'text_domain' ),
			'attributes'            => __( 'Program Attributes', 'text_domain' ),
			'parent_item_colon'     => __( 'Parent Program:', 'text_domain' ),
			'all_items'             => __( 'All Programs', 'text_domain' ),
			'add_new_item'          => __( 'Add New Program', 'text_domain' ),
			'add_new'               => __( 'Add New', 'text_domain' ),
			'new_item'              => __( 'New Program', 'text_domain' ),
			'edit_item'             => __( 'Edit Program', 'text_domain' ),
			'update_item'           => __( 'Update Program', 'text_domain' ),
			'view_item'             => __( 'View Program', 'text_domain' ),
			'view_items'            => __( 'View Programs', 'text_domain' ),
			'search_items'          => __( 'Search Program', 'text_domain' ),
			'not_found'             => __( 'Not found', 'text_domain' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'text_domain' ),
			'featured_image'        => __( 'Featured Image', 'text_domain' ),
			'set_featured_image'    => __( 'Set featured image', 'text_domain' ),
			'remove_featured_image' => __( 'Remove featured image', 'text_domain' ),
			'use_featured_image'    => __( 'Use as featured image', 'text_domain' ),
			'insert_into_item'      => __( 'Insert into program', 'text_domain' ),
			'uploaded_to_this_item' => __( 'Uploaded to this program', 'text_domain' ),
			'items_list'            => __( 'Programs list', 'text_domain' ),
			'items_list_navigation' => __( 'Programs list navigation', 'text_domain' ),
			'filter_items_list'     => __( 'Filter programs list', 'text_domain' ),
		);
		$args   = array(
			'label'               => __( 'Program', 'text_domain' ),
			'description'         => __( 'Program Description', 'text_domain' ),
			'labels'              => $labels,
			'supports'            => array('post-formats'),
			'taxonomies'          => array( 'category', 'post_tag' ),
			'hierarchical'        => false,
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'menu_position'       => 5,
			'show_in_admin_bar'   => true,
			'show_in_nav_menus'   => true,
			'can_export'          => true,
			'has_archive'         => true,
			'exclude_from_search' => false,
			'publicly_queryable'  => true,
			'capability_type'     => 'page',
		);
		register_post_type( 'program', $args );
		
	}
	
	// Register Radio CPT
	add_action( 'init', 'wpvr_add_radio_custom_post_type', 1 );
	function wpvr_add_radio_custom_post_type() {
		
		$labels = array(
			'name'                  => _x( 'Radios', 'Radio General Name', 'text_domain' ),
			'singular_name'         => _x( 'Radio', 'Radio Singular Name', 'text_domain' ),
			'menu_name'             => __( 'Radios', 'text_domain' ),
			'name_admin_bar'        => __( 'Radio', 'text_domain' ),
			'archives'              => __( 'Radio Archives', 'text_domain' ),
			'attributes'            => __( 'Radio Attributes', 'text_domain' ),
			'parent_item_colon'     => __( 'Parent Radio:', 'text_domain' ),
			'all_items'             => __( 'All Radios', 'text_domain' ),
			'add_new_item'          => __( 'Add New Radio', 'text_domain' ),
			'add_new'               => __( 'Add New', 'text_domain' ),
			'new_item'              => __( 'New Radio', 'text_domain' ),
			'edit_item'             => __( 'Edit Radio', 'text_domain' ),
			'update_item'           => __( 'Update Radio', 'text_domain' ),
			'view_item'             => __( 'View Radio', 'text_domain' ),
			'view_items'            => __( 'View Radios', 'text_domain' ),
			'search_items'          => __( 'Search Radio', 'text_domain' ),
			'not_found'             => __( 'Not found', 'text_domain' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'text_domain' ),
			'featured_image'        => __( 'Featured Image', 'text_domain' ),
			'set_featured_image'    => __( 'Set featured image', 'text_domain' ),
			'remove_featured_image' => __( 'Remove featured image', 'text_domain' ),
			'use_featured_image'    => __( 'Use as featured image', 'text_domain' ),
			'insert_into_item'      => __( 'Insert into radio', 'text_domain' ),
			'uploaded_to_this_item' => __( 'Uploaded to this radio', 'text_domain' ),
			'items_list'            => __( 'Radios list', 'text_domain' ),
			'items_list_navigation' => __( 'Radios list navigation', 'text_domain' ),
			'filter_items_list'     => __( 'Filter radios list', 'text_domain' ),
		);
		$args   = array(
			'label'               => __( 'Radio', 'text_domain' ),
			'description'         => __( 'Radio Description', 'text_domain' ),
			'labels'              => $labels,
			'supports'            => array(),
			'taxonomies'          => array( 'category', 'post_tag' ),
			'hierarchical'        => false,
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'menu_position'       => 5,
			'show_in_admin_bar'   => true,
			'show_in_nav_menus'   => true,
			'can_export'          => true,
			'has_archive'         => true,
			'exclude_from_search' => false,
			'publicly_queryable'  => true,
			'capability_type'     => 'page',
		);
		register_post_type( 'radio', $args );
		
	}