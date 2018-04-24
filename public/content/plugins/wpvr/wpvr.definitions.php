<?php
	
	/* Debug Assets */
	require_once( 'assets/php/dBug.php' );
	require_once( 'assets/php/kint/Kint.class.php' );
	wpvrKint::$theme = 'aante-light';
	function _d( $var ) {
		new dBug( $var );
	}
	
	
	function wpvr_strtoupper( $string ) {
			if ( function_exists( 'mb_strtoupper' ) ) {
			return mb_strtoupper( $string );
		} else {
			return strtoupper( $string );
		}
	}
	
	function ___( $string, $ucwords = false ) {
		if ( $ucwords === true ) {
			
			if ( get_locale() == 'zh_CN' || get_locale() == 'fr_FR' ) {
				return mb_convert_case( __( $string, WPVR_LANG ), MB_CASE_UPPER, "UTF-8" );
			} else {
				return ucwords( __( $string, WPVR_LANG ) );
			}
			
			
		} elseif ( $ucwords == 2 ) {
			return wpvr_strtoupper( strtolower( __( $string, WPVR_LANG ) ) );
		} elseif ( $ucwords == 3 ) {
			return strtolower( __( $string, WPVR_LANG ) );
		} elseif ( $ucwords == 1 ) {
			return ucfirst( strtolower( __( $string, WPVR_LANG ) ) );
		} else {
			return __( $string, WPVR_LANG );
		}
	}
	
	/* Predef Functions */
	require_once( 'definitions/wpvr.predef.php' );
	
	/* Defining Constants */
	require_once( 'definitions/wpvr.constants.php' );
	
	/* Defining plugin links */
	require_once( 'definitions/wpvr.urls.php' );
	
	/* Including Services definitons */
	add_action( 'plugins_loaded', 'wpvr_load_services_init', 5 );
	function wpvr_load_services_init() {
		
		
		/* Definings the plugin global variables */
		require_once( 'definitions/wpvr.globals.php' );
		
		/* Wrapping up definitions */
		require_once( 'definitions/wpvr.set.before.php' );
		
		
		/* Including Services definitons */
		require_once( 'definitions/wpvr.services.php' );
		
		/* Definings the plugin default options values */
		require_once( 'definitions/wpvr.defaults.php' );
		
		
		/* Wrapping up definitions */
		require_once( 'definitions/wpvr.set.after.php' );
		
	}
	
	
	require_once( 'definitions/wpvr.templater.php' );
	
	if ( WPVR_ENABLE_TEMPLATER === true ) {
		add_action( 'plugins_loaded', array(
			'wpvr_PageTemplater',
			'get_instance',
		), 10000 );
	}


	
	
	