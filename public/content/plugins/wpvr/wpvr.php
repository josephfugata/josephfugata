<?php
	/*
	Plugin Name: WP Video Robot
	Plugin URI: https://www.wpvideorobot.com
	Description: The Ultimate WordPress Automated Video Importer
	Version: 1.9.17
	Author: pressaholic
	Author URI: http://www.pressaholic.com
	License: GPL2
	*/
	
	define( 'WPVR_MAIN_FILE', __FILE__ );
	define( 'WPVR_VERSION', '1.9.17' );
	
	
	require_once( 'wpvr.config.php' );
	
	/* Plugin Default Definitions */
	require_once( 'wpvr.definitions.php' );
	
	/* Including functions definitions */
	require_once( 'wpvr.functions.php' );
	
	/* Including functions definitions */
	require_once( 'wpvr.hooks.php' );
	
	/* Include AJAX definitions */
	require_once( 'wpvr.ajax.php' );
	
	/* Including Sources CPT definitions */
	require_once( 'includes/wpvr.sources.php' );
	
	require_once( 'assets/php/RollingCurlX.php' );
	
	
	/* Including Videos CPT definitions */
	require_once( 'includes/wpvr.videos.php' );
	
	
	/* Including Sources & Videos Bulk Action */
	require_once( 'includes/wpvr.bulk.sources.php' );
	require_once( 'includes/wpvr.bulk.videos.php' );
	