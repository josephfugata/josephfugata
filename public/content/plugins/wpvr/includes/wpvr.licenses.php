<?php
	global $wpvr_pages;
	$wpvr_pages = true;
	
	if ( WPVR_IS_DEMO ) {
		wpvr_refuse_access();
		
		return false;
	}

?>

<div class="wrap wpvr_wrap " style="display:none;">
	<?php wpvr_show_logo(); ?>
    <h2 class="wpvr_title">
        <i class="wpvr_title_icon fa fa-certificate"></i>
		<?php echo __( 'Manage Licenses', WPVR_LANG ); ?>
    </h2>

    <div id="dashboard-widgets-wrap">
        <div id="dashboard-widgets" class="metabox-holder">
            <!-- LEFT -->
            <div id="postbox-container-1" class="postbox-container">
                <div id="normal-sortables" class="meta-box-sortables ui-sortable">
                    <div id="dashboard_right_now" class="postbox wpvr_license_wrap">
                        <h3 class="hndle">
                            <span><?php _e( 'WP VIDEO ROBOT LICENSE', WPVR_LANG ); ?></span>

                            <span class="wpvr_license_version pull-right">version <?php echo WPVR_VERSION; ?> </span>
                        </h3>

                        <div class="inside">
                            <div class="main">
								
								<?php
									$reset_act_link = '#';
									$act            = wpvr_get_activation( 'wpvr' );
									if ( ! isset( $act['act_status'] ) || $act['act_status'] == 0 ) {
										$status       = '<div class="wpvr_license_status inactive"> <i class="fa fa-exclamation-circle"></i> ' . __( 'NOT ACTIVATED', WPVR_LANG ) . ' </div>';
										$act_email    = $act_code = $act_domain = $act_date = '';
										$license      = '';
										$is_activated = false;
									} else {
										$status     = '<div class="wpvr_license_status active"> <i class="fa fa-check"></i> ' . __( 'ACTIVATED', WPVR_LANG ) . ' </div>';
										$act_email  = $act['act_email'];
										$act_code   = $act['act_code'];
										$act_domain = $act['act_domain'];
										$act_date   = $act['act_date'];
										if ( $act['buy_license'] != '' ) {
											$license = '<div class="wpvr_license_title">' . $act['buy_license'] . '</div>';
										} else {
											$license = '';
										}
										$is_activated = true;
									}
									
									if ( WPVR_IS_DEMO ) {
										$act_code = '******************************';
									}
								?>

                                <div class="wpvr_license">
									
									
									<?php echo $license; ?>
									<?php echo $status; ?>
                                    <div class="wpvr_clearfix"></div>
									<?php if ( $is_activated === true ) { ?>
                                        <br/>
                                        <hr/>
                                        <div class="wpvr_license_data">
                                            <label>
												<?php echo __( "Email", WPVR_LANG ); ?>
                                            </label><br/>
                                            <input
                                                    type="text" id="" readonly="readonly"
                                                    class="wpvr_license_input"
                                                    value="<?php echo $act_email; ?>"
                                            />
                                        </div>
                                        <div class="wpvr_license_data">
                                            <label><?php echo __( "Purchase Code", WPVR_LANG ); ?></label><br/>
                                            <input
                                                    type="text" id="" readonly="readonly"
                                                    class="wpvr_license_input"
                                                    value="<?php echo $act_code; ?>"
                                            />
                                        </div>

                                        <div class="wpvr_license_data">
                                            <label><?php echo __( "Domain", WPVR_LANG ); ?></label><br/>
                                            <input
                                                    type="text" id="" readonly="readonly"
                                                    class="wpvr_license_input"
                                                    value="<?php echo $act_domain; ?>"
                                            />
                                        </div>

                                        <div class="wpvr_license_data">
                                            <label><?php echo __( "Activation Date", WPVR_LANG ); ?></label><br/>
                                            <input
                                                    type="text" id="" readonly="readonly"
                                                    class="wpvr_license_input"
                                                    value="<?php echo $act_date; ?>"
                                            />
                                        </div>
                                        <button
                                                url="<?php echo WPVR_ACTIONS_URL; ?>"
                                                id="wpvr_reset_activation"
                                                class="wpvr_black_button pull-left wpvr_button wpvr_large"
                                                is_demo="<?php echo WPVR_IS_DEMO ? 1 : 0; ?>"
                                        >
                                            <i class="wpvr_button_icon fa fa-undo"></i><?php _e( 'Reset Activation', WPVR_LANG ); ?>
                                        </button>

                                        <button
                                                url="<?php echo WPVR_ACTIONS_URL; ?>"
                                                id="wpvr_cancel_activation"
                                                class="wpvr_red_button pull-right wpvr_button wpvr_large"
                                                is_demo="<?php echo WPVR_IS_DEMO ? 1 : 0; ?>"
                                        >
                                            <i class="wpvr_button_icon fa fa-times-circle"></i><?php _e( 'Cancel Activation', WPVR_LANG ); ?>
                                        </button>

                                        <div class="wpvr_clearfix"></div>
									<?php } ?>
                                </div>
								
								
								<?php ?>


                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <!-- RIGHT -->
            <div id="postbox-container-2" class="postbox-container">
                <div id="normal-sortables" class="meta-box-sortables ui-sortable">

                    <div id="dashboard_right_now" class="postbox ">
                        <h3 class="hndle"><span><?php _e( 'ADDONS LICENSES', WPVR_LANG ); ?></span></h3>

                        <div class="inside">
                            <div class="main">
								
								<?php
									if ( $is_activated ) {
										wpvr_addons_licenses_form_render();
									} else {
										echo __( 'Start by activating your license of WP Video Robot', WPVR_LANG );
									}
								?>

                            </div>
                        </div>
                    </div>


                </div>
            </div>
        </div>
    </div>

</div>