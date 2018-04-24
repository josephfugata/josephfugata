<?php
	
	global $wpvr_colors, $wpvr_status, $wpvr_services, $wpvr_vs;
	global $wpvr_using_cpt, $wpvr_pages;
	global $is_DT;
	
	$wpvr_pages = true;
	
	
	$active = array(
		'content'     => '',
		'automation'  => '',
		'duplicates'  => '',
		'datafillers' => '',
		'setters'     => '',
	);
	
	if ( ! isset( $_GET['section'] ) || ! isset( $active[ $_GET['section'] ] ) ) {
		$active['content'] = 'active';
	} else {
		
		$active[ $_GET['section'] ] = 'active';
	}

?>
<div class="wrap wpvr_wrap" style="visibility:hidden;">
	<?php wpvr_show_logo(); ?>
    <h2 class="wpvr_title">
        <i class="wpvr_title_icon fa fa-dashboard"></i>
		<?php echo __( 'Dashboard', WPVR_LANG ); ?>
    </h2>

    <div class="wpvr_nav_tabs pull-left">


        <div title="<?php _e( 'Sources & Videos', WPVR_LANG ); ?>"
             class="wpvr_nav_tab pull-left noMargin <?php echo $active['content']; ?>" id="a">
            <i class="wpvr_tab_icon fa fa-bar-chart"></i><br/>
            <span><?php _e( 'Sources & Videos', WPVR_LANG ); ?> </span>
        </div>

        <div title="<?php _e( 'Automation Stats', WPVR_LANG ); ?>"
             class="wpvr_nav_tab pull-left noMargin <?php echo $active['automation']; ?>" id="b">
            <i class="wpvr_tab_icon fa fa-calendar"></i><br/>
            <span><?php _e( 'Automation Stats', WPVR_LANG ); ?></span>
        </div>


        <div title="<?php _e( 'Duplicates Cleaner', WPVR_LANG ); ?>"
             class="wpvr_nav_tab pull-left noMargin <?php echo $active['duplicates']; ?>" id="c">
            <i class="wpvr_tab_icon fa fa-copy"></i><br/>
            <span><?php _e( 'Duplicates Cleaner', WPVR_LANG ); ?></span>
        </div>
		
		<?php if ( WPVR_ENABLE_DATA_FILLERS === true ) { ?>
            <div title="<?php _e( 'Data Fillers', WPVR_LANG ); ?>"
                 class="wpvr_nav_tab pull-left noMargin <?php echo $active['datafillers']; ?>" id="d">
                <i class="wpvr_tab_icon fa fa-tags"></i><br/>
                <span><?php _e( 'Data Fillers', WPVR_LANG ); ?></span>
            </div>
		<?php } ?>
		
		<?php if ( WPVR_ENABLE_SETTERS === true ) { ?>
            <div title="<?php _e( 'Admin Actions', WPVR_LANG ); ?>"
                 class="wpvr_nav_tab pull-left noMargin <?php echo $active['setters']; ?>" id="e">
                <i class="wpvr_tab_icon fa fa-hand-o-up"></i><br/>
                <span><?php _e( 'Admin Actions', WPVR_LANG ); ?></span>
            </div>
		<?php } ?>


        <span class="wpvr_version_helper pull-right">
			<?php echo "v" . WPVR_VERSION; ?>
		</span>

        <div class="wpvr_clearfix"></div>
    </div>
    <div class="wpvr_clearfix"></div>
    <div class="wpvr_dashboard">

        <!-- DATAFILLERS -->
		<?php if ( WPVR_ENABLE_DATA_FILLERS === true ) { ?>
            <div id="" class="wpvr_nav_tab_content tab_d">
				<?php include( WPVR_PATH . '/includes/wpvr.datafillers.php' ); ?>
            </div>
		<?php } ?>

        <!-- SETTERS -->
		<?php if ( WPVR_ENABLE_SETTERS === true ) { ?>
            <div id="" class="wpvr_nav_tab_content tab_e">
				<?php include( WPVR_PATH . '/includes/wpvr.setters.php' ); ?>
            </div>
		<?php } ?>

        <!-- DUPLICATES TRACKER -->
        <div id="" class="wpvr_nav_tab_content tab_c">
			<?php $is_DT = true; ?>
			<?php include( WPVR_PATH . '/includes/wpvr.manage.php' ); ?>
        </div>

        <!-- SOURCE & VIDEOS DASHBOARD -->
        <div id="" class="wpvr_nav_tab_content tab_a">
            <div id="dashboard-widgets" class="metabox-holder">
	            
	            <div class="wpvr_overview_wrap wpvr_ajax_deferred_load" data-action="wpvr_render_overview">
		            <?php echo wpvr_render_loading_message(); ?>
	            </div>
             
            </div>
        </div>

        <!-- AUTOMATION DASHBOARD -->
        <div id="" class="wpvr_nav_tab_content tab_b">
            <div id="dashboard-widgets" class="metabox-holder">
				<?php include( WPVR_PATH . '/includes/wpvr.dashboard.automation.php' ); ?>
            </div>
        </div>
        <!-- AUTOMATION DASHBOARD -->


    </div>