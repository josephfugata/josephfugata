<?php
	
	if( defined('WPVR_FUNCTIONS_LOADED') ){
		return false;
	}
	
	/* Require Source Functions */
	require_once( WPVR_PATH. 'functions/wpvr.functions.core.php');	
	
	/* Require addons Functions */
	require_once( WPVR_PATH. 'functions/wpvr.functions.addons.php');		

	/* Require services Functions */
	require_once( WPVR_PATH. 'functions/wpvr.functions.services.php');

	/* Require Source Functions */
	require_once( WPVR_PATH. 'functions/wpvr.functions.sources.php');	
	
	/* Require API Functions */
	require_once( WPVR_PATH. 'functions/wpvr.functions.api.php');	
	
	/* Require Videos Functions */
	require_once( WPVR_PATH. 'functions/wpvr.functions.videos.php');	
	
	/* Require Source Functions */
	require_once( WPVR_PATH. 'functions/wpvr.functions.globals.php');	
	
	/* Require Source Functions */
	require_once( WPVR_PATH. 'functions/wpvr.functions.utils.php');	
	
	/* Require Adapt Functions */
	require_once( WPVR_PATH. 'functions/wpvr.functions.adapt.php');
	
	/* Require Merging Class and Functions */
	require_once( WPVR_PATH. 'functions/wpvr.functions.merge.php');
	
	/* Require Autoupdate Class and Functions */
	require_once( WPVR_PATH. 'functions/wpvr.functions.autoupdate.php');
	
	/* Require CPT functions */
	require_once( WPVR_PATH. 'functions/wpvr.functions.cpt.php');
	
	/* Require Autoupdate Class and Functions */
	require_once( WPVR_PATH. 'functions/wpvr.functions.mail.php');

	/* Require Anti Duplicates and Merging Tools */
	require_once( WPVR_PATH. 'functions/wpvr.functions.duplicates.php');
	
	require_once( WPVR_PATH. 'functions/wpvr.functions.fake.php');
	
	require_once( WPVR_PATH. 'functions/wpvr.functions.stats.php');
	
	define('WPVR_FUNCTIONS_LOADED' , true );
	