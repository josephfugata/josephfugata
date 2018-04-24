<?php
	
	global $wpvr_filler_data;

?>
<div id="dashboard-widgets" class="metabox-holder">
    <div id="postbox-container-1" class="postbox-container wpvr_datafillers_left_panel">
        <div id="normal-sortables" class="meta-box-sortables ui-sortable">

            <!-- Add Manually -->
            <div id="dashboard_right_now" class="postbox ">
                <h3 class="hndle"><span> <?php echo __( 'Add a new Filler', WPVR_LANG ); ?></span></h3>
                <div class="inside">
                    <div class="main">

                        <form class="wpvr_filler_form">
                            <label for="filler_from"><?php echo __( 'Video Data to add', WPVR_LANG ); ?></label><br/>
                            <select class="wpvr_filler_input" name="filler_from">
                                <option value=""> - <?php echo __( 'Choose a data', WPVR_LANG ); ?> -</option>
								
								<?php foreach ( (array) $wpvr_filler_data as $value => $label ) { ?>
                                    <option value="<?php echo $value; ?>">
										<?php echo $label; ?>
                                    </option>
								<?php } ?>
                                <option value="custom_data">
									<?php echo ucfirst( strtolower( __( 'Custom Data', WPVR_LANG ) ) ); ?>
                                </option>
                            </select><br/>
                            <input
                                    class="wpvr_filler_input"
                                    id="filler_from_custom"
                                    name="filler_from_custom"
                                    type="text"
                                    placeholder="<?php echo __( 'Custom String', WPVR_LANG ); ?>..."
                                    style="display:none;"
                            />
                            <br/><br/>

                            <label for="filler_to"><?php echo __( 'Custom Field name to populate', WPVR_LANG ); ?></label><br/>
                            <input class="wpvr_filler_input" name="filler_to" type="text"
                                   placeholder="<?php echo ucfirst( strtolower( __( 'Custom Field Name', WPVR_LANG ) ) ) . '...'; ?>">
                            <br/><br/>
                            <div class="wpvr_clearfix"></div>
                            <button
                                    id="wpvr_filler_add"
                                    class="pull-right wpvr_button"
                                    form="wpvr_filler_form"
                            >
                                <i class="wpvr_button_icon fa fa-plus"></i>
								<?php echo __( 'ADD SINGLE FILLER', WPVR_LANG ); ?>
                            </button>
                            <div class="wpvr_clearfix"></div>

                            <input type="hidden" name="action" value="add_filler"/>

                        </form>

                    </div>
                </div>
            </div>

        </div>
    </div>
    <div id="postbox-container-2" class="postbox-container wpvr_datafillers_right_panel">
        <div id="normal-sortables" class="meta-box-sortables ui-sortable">


            <div id="dashboard_right_now" class="postbox ">
                <h3 class="hndle"><span> Fillers </span></h3>
                <div class="inside">
                    <div class="main">
                        <div id="wpvr_filler_list">
                            <?php echo wpvr_render_loading_message(); ?>
                        </div>
                    </div>
                </div>
            </div>


        </div>
    </div>
</div>