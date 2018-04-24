<?php
	
	
	add_filter('wpvr_extend_internal_cpts' , 'wpvr_extend_private_cpt' , 100 , 1 );
	add_filter('wpvr_extend_private_cpt' , 'wpvr_extend_private_cpt' , 100 , 1 );
	function wpvr_extend_private_cpt( $private_cpts ){
		
		//Fix for VideoPro Series Widget Deleting Videos
		$private_cpts[] = 'vseries_post';
		
		//WP Nav Menu
		$private_cpts[] = 'nav_menu_item';
		
		//Optin Monster
		$private_cpts[] = 'omapi';
		
		//Ultimate Member
		$private_cpts[] = 'um_form';
		$private_cpts[] = 'um_role';
		$private_cpts[] = 'um_directory';
		$private_cpts[] = 'um_mailchimp';
		
		//Visual Composer
		$private_cpts[] = 'vc_grid_item';
		$private_cpts[] = 'vc4_templates';
		
		// WP Contact Forms 7
		$private_cpts[] = 'wpcf7_contact_form';
		
		//Option tree
		$private_cpts[] = 'option-tree';
		
		//Advanced Custom Fields
		$private_cpts[] = 'acf';
		
		//WP Core
		$private_cpts[] = 'custom_css';
		$private_cpts[] = 'attachment';
		$private_cpts[] = 'customize_changeset';
		
		
		return $private_cpts;
	}