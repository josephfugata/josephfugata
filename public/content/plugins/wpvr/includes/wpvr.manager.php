<?php
	
	global $wpvr_vs, $wpvr_pages, $wpvr_is_duplicate_cleaner;
	
	//global $wpvr_options , $wpvr_default_options , $wpvr_token;
	$wpvr_url        = WPVR_MANAGE_URL;
	$wpvr_bulk_url   = WPVR_IMPORT_URL;
	$wpvr_url_export = WPVR_ACTIONS_URL;
	$wpvr_dups_url   = admin_url( 'admin.php?page=wpvr&section=duplicates' );
	
	$wpvr_pages = true;
	
	if ( ! isset( $wpvr_is_duplicate_cleaner ) ) {
		$wpvr_is_duplicate_cleaner = false;
	}
	
	//Define Layout Value
	$active = array(
		'sgrid' => '',
		'bgrid' => '',
		'grid'  => '',
		'list'  => '',
	);
	
	$active[ WPVR_MANAGE_LAYOUT ] = 'active';


?>
<div class="wrap wpvr_wrap" style="display:none;">

    <!-- DON'T USE HEADER IF DUPLICATE CLEANER -->
	<?php if ( ! $wpvr_is_duplicate_cleaner ) { ?>
		<?php wpvr_show_logo(); ?>
        <h2 class="wpvr_title">
            <i class="wpvr_title_icon fa fa-film"></i>
			<?php echo __( 'Manage Videos', WPVR_LANG ); ?>
        </h2>
	<?php } ?>

    <div class="wpvr_dashboard">
        <div id="dashboard-widgets-wrap" class="wpvr_nav_tab_content tab_c">
            <form
                    class="wpvr_manage_main_form"
                    action=""
                    _url="<?php echo $wpvr_url; ?>"
                    url_export="<?php echo $wpvr_url_export; ?>"
                    enctype="multipart/form-data"
            >
                <div class="wpvr_manage_wrapper">


                    <!-- DON'T USE HEADER IF DUPLICATE CLEANER -->
					<?php if ( ! $wpvr_is_duplicate_cleaner ) { ?>
						<?php include_once( WPVR_PATH . 'includes/wpvr.manager.head.php' ); ?>
					<?php } ?>


                    <div class="wpvr_manage_bulk">
                        <div class="wpvr_manage_bulk_left pull-left noMargin">

                            <!-- DON'T USE HEADER IF DUPLICATE CLEANER -->
							<?php if ( ! $wpvr_is_duplicate_cleaner ) { ?>
                                <button is_merging_all="1" url="<?php echo $wpvr_url; ?>"
                                        class="wpvr_button wpvr_black_button pull-left wpvr_merge_selected_duplicates">
                                    <i class="wpvr_button_icon fa fa-magic"></i>
									<?php _e( 'Merge all duplicates', WPVR_LANG ); ?>
                                </button>
							<?php } ?>

                            <!-- manage_buttons -->
                            <button class="wpvr_button pull-left wpvr_manage_checkAll" state="off">
                                <i class="wpvr_button_icon fa fa-check-square-o"></i>
								<?php _e( 'CHECK/UNCHECK ALL', WPVR_LANG ); ?>
                            </button>

                            <!-- manage_buttons -->
                        </div>
                        <div class="wpvr_manage_bulk_right  noMargin">
							
							<?php
							
							
							?>


                            <div class="wpvr_manage_bulk_actions">

								<span class="wpvr_manage_layout pull-right">
									<button class="wpvr_icon_only wpvr_button wpvr_layout_btn <?php echo $active['sgrid']; ?>"
                                            layout="sgrid"><i class="fa fa-table"></i></button>
									<button class="wpvr_icon_only wpvr_button wpvr_layout_btn <?php echo $active['bgrid']; ?>"
                                            layout="bgrid"><i class="fa fa-th"></i></button>
									<button class="wpvr_icon_only wpvr_button wpvr_layout_btn <?php echo $active['grid']; ?>"
                                            layout="grid"><i class="fa fa-th-large"></i></button>
									<button class="wpvr_icon_only wpvr_button wpvr_layout_btn <?php echo $active['list']; ?>"
                                            layout="list"><i class="fa fa-th-list"></i></button>
								</span>
                                <input type="hidden" class="wpvr_manage_layout_hidden" name="manage_layout"
                                       value="<?php echo WPVR_MANAGE_LAYOUT; ?>"/>

                                <!-- DON'T USE HEADER IF DUPLICATE CLEANER -->
	                            <?php if ( ! $wpvr_is_duplicate_cleaner ) { ?>
                                    <span class="wpvr_manage_bulk_actions_select" style="display:none;">
										<button url="<?php echo $wpvr_url; ?>"
                                                class="wpvr_button pull-left wpvr_merge_selected_duplicates">
											<i class="wpvr_button_icon fa fa-magic"></i>
											<?php echo __( 'Merge', WPVR_LANG ); ?>
                                            <span class="wpvr_count_checked"></span>
											<?php echo __( 'Selected Items', WPVR_LANG ); ?>
										</button>
									</span>
								<?php } else { ?>
                                    <select class="wpvr_manage_bulk_actions_select pull-left" style="display:none;">
                                        <option class="" value="">
                                            - <?php _e( 'Bulk Actions', WPVR_LANG ); ?> -
                                        </option>
                                        <option class="" value="export">
											<?php _e( 'Export', WPVR_LANG ); ?>
                                        </option>
                                        <option class="" value="publish">
											<?php _e( 'Publish', WPVR_LANG ); ?>
                                        </option>
                                        <option class="" value="pending">
											<?php _e( 'UnPublish', WPVR_LANG ); ?>
                                        </option>
                                        <option class="" value="draft">
											<?php _e( 'Draft', WPVR_LANG ); ?>
                                        </option>
                                        <option class="" value="trash">
											<?php _e( 'Trash', WPVR_LANG ); ?>
                                        </option>
                                        <option class="" value="untrash">
											<?php _e( 'Restore', WPVR_LANG ); ?>
                                        </option>
                                        <option class="" value="delete">
											<?php _e( 'Delete', WPVR_LANG ); ?>
                                        </option>
                                    </select>
                                    <div class="wpvr_button pull-left wpvr_manage_bulkApply" state="off"
                                         style="display:none;">
                                        <i class="wpvr_button_icon fa fa-magic"></i>
										<?php _e( 'Apply to', WPVR_LANG ); ?>
                                        <span class="wpvr_count_checked"></span>
										<?php _e( 'items', WPVR_LANG ); ?>
                                    </div>
								<?php } ?>


                            </div>

                        </div>
                        <div class="wpvr_clearfix"></div>
                    </div>
                    <div class="wpvr_manage_sidebar pull-left noMargin">
						<?php if ( false ) { ?>
                            <div class="wpvr_manage_sidebar_tab pull-left noMargin active" id="wpvr_manage_filter">
                                <i class="wpvr_manage_tab_icons  fa fa-filter"></i>
                                FILTER
                            </div>
                            <div class="wpvr_manage_sidebar_tab pull-right noMargin" id="wpvr_manage_filter">
                                <i class="wpvr_manage_tab_icons fa fa-sort"></i>
                                ORDER
                            </div>
                            <div class="wpvr_clearfix"></div>
						<?php } ?>
                        <div class="wpvr_manage_sidebar_content">
							<?php if ( ! $is_DT ) { ?>
                                <div class="wpvr_sidebar_toggle on">
                                    <span class="is_on"><?php _e( 'Close All', WPVR_LANG ); ?></span>
                                    <span class="is_off"><?php _e( 'Show All', WPVR_LANG ); ?></span>
                                </div>

                                <!-- FILTER BY SERVICE -->
								<?php $fcb_services = wpvr_manage_render_filters( 'services' ); ?>
								<?php if ( $fcb_services ) { ?>
                                    <div class="wpvr_manage_box open">
                                        <div class="wpvr_manage_box_head">
                                            <i class="fa fa-globe"></i>
											<?php _e( 'Filter by', WPVR_LANG ); ?> <?php _e( 'Video Service', WPVR_LANG ); ?>
                                            <i class="pull-right caretDown fa fa-caret-down"></i>
                                            <i class="pull-right caretUp fa fa-caret-up"></i>
                                        </div>
                                        <div class="wpvr_manage_box_content">
											<?php echo $fcb_services; ?>
                                        </div>
                                    </div>
								<?php } ?>
                                <!-- FILTER BY SERVICE -->
                                <!-- FILTER BY DATES -->
								<?php $fcb_dates = wpvr_manage_render_filters( 'dates' ); ?>
								<?php if ( $fcb_dates ) { ?>
                                    <div class="wpvr_manage_box open">
                                        <div class="wpvr_manage_box_head">
                                            <i class="fa fa-clock-o"></i>
											<?php _e( 'Filter by', WPVR_LANG ); ?> <?php _e( 'Post Dates', WPVR_LANG ); ?>

                                            <i class="pull-right caretDown fa fa-caret-down"></i>
                                            <i class="pull-right caretUp fa fa-caret-up"></i>
                                        </div>
                                        <div class="wpvr_manage_box_content">
											<?php echo $fcb_dates; ?>
                                        </div>
                                    </div>
								<?php } ?>
                                <!-- FILTER BY DATES -->


                                <!-- FILTER BY AUTHOR -->
								<?php $fcb_authors = wpvr_manage_render_filters( 'authors' ); ?>
								<?php if ( $fcb_authors ) { ?>
                                    <div class="wpvr_manage_box open">
                                        <div class="wpvr_manage_box_head">
                                            <i class=" fa fa-user"></i>
											<?php _e( 'Filter by', WPVR_LANG ); ?> <?php _e( 'Authors', WPVR_LANG ); ?>

                                            <i class="pull-right caretDown fa fa-caret-down"></i>
                                            <i class="pull-right caretUp fa fa-caret-up"></i>
                                        </div>
                                        <div class="wpvr_manage_box_content">
											<?php echo $fcb_authors; ?>
                                        </div>
                                    </div>
								<?php } ?>
                                <!-- FILTER BY AUTHOR -->

                                <!-- FILTER BY CAT -->
								<?php $fcb_categories = wpvr_manage_render_filters( 'categories' ); ?>
								<?php if ( $fcb_categories ) { ?>
                                    <div class="wpvr_manage_box open">
                                        <div class="wpvr_manage_box_head">
                                            <i class=" fa fa-folder-open"></i>
											<?php _e( 'Filter by', WPVR_LANG ); ?> <?php _e( 'Categories', WPVR_LANG ); ?>
                                            <i class="pull-right caretDown fa fa-caret-down"></i>
                                            <i class="pull-right caretUp fa fa-caret-up"></i>
                                        </div>
                                        <div class="wpvr_manage_box_content">
											<?php echo $fcb_categories; ?>
                                        </div>
                                    </div>
								<?php } ?>
                                <!-- FILTER BY CAT -->


                                <!-- FILTER BY Statuses -->
								<?php $fcb_statuses = wpvr_manage_render_filters( 'statuses' ); ?>
								<?php if ( $fcb_statuses ) { ?>
                                    <div class="wpvr_manage_box open">
                                        <div class="wpvr_manage_box_head">
                                            <i class="fa fa-tags"></i>
											<?php _e( 'Filter by', WPVR_LANG ); ?> <?php _e( 'Video Status', WPVR_LANG ); ?>

                                            <i class="pull-right caretDown fa fa-caret-down"></i>
                                            <i class="pull-right caretUp fa fa-caret-up"></i>
                                        </div>
                                        <div class="wpvr_manage_box_content">
											<?php echo $fcb_statuses; ?>
                                        </div>
                                    </div>
								<?php } ?>
                                <!-- FILTER BY Statuses -->
							
							
							<?php } ?>
							
							<?php if ( $is_DT ) { ?>
                                <!-- DUPTOOL  -->
                                <div class="wpvr_manage_box wpvrOrder open">
                                    <div class="wpvr_manage_box_head ">
                                        <i class="wpvr_manage_tab_icons fa fa-copy"></i>
										<?php _e( 'Duplicates Toolbox', WPVR_LANG ); ?>
                                        <i class="pull-right caretDown fa fa-caret-down"></i>
                                        <i class="pull-right caretUp fa fa-caret-up"></i>

                                    </div>
                                    <div class="wpvr_manage_box_content">

                                        <input type="hidden" value="0" class="wpvr_manage_is_filtering"
                                               name="is_filtering"/>
                                        <input type="hidden" value="video_id" class="" name="dupsBy"/>

                                        <div class="wpvr_button wpvr_big_button wpvr_manage_refresh">
                                            <i class="wpvr_button_icon fa fa-search"></i>
											<?php _e( 'Find Duplicates', WPVR_LANG ); ?>
                                        </div>
                                    </div>
                                </div>
                                <!-- DUPTOOL  -->
							<?php } ?>
							
							<?php if ( ! $is_DT ) { ?>
                                <!-- ORDER  -->
                                <div class="wpvr_manage_box wpvrOrder open">
                                    <div class="wpvr_manage_box_head ">
                                        <i class="wpvr_manage_tab_icons fa fa-sort"></i>
										<?php _e( 'Order Results', WPVR_LANG ); ?>
                                        <i class="pull-right caretDown fa fa-caret-down"></i>
                                        <i class="pull-right caretUp fa fa-caret-up"></i>

                                    </div>
                                    <div class="wpvr_manage_box_content">

                                        <input type="hidden" value="0" class="wpvr_manage_is_filtering"
                                               name="is_filtering"/>

                                        <label>
											<?php _e( 'Order By', WPVR_LANG ); ?>
                                        </label><br/>
                                        <select name="filter_orderby">
                                            <option value="date"
                                                    selected="selected"><?php _e( 'Date', WPVR_LANG ); ?></option>
                                            <option value="title"><?php _e( 'Title', WPVR_LANG ); ?></option>
											<?php if ( $is_DT ) { ?>
                                                <option value="views"><?php _e( 'Total Views', WPVR_LANG ); ?></option>
                                                <option value="dupCount"><?php _e( 'Duplicates', WPVR_LANG ); ?></option>
											<?php } ?>

                                        </select>
                                        <br/>
                                        <br/>
                                        <label>
											<?php _e( 'Order', WPVR_LANG ); ?>
                                        </label><br/>
                                        <select name="filter_order">
                                            <option value="asc"><?php _e( 'Ascendant', WPVR_LANG ); ?></option>
                                            <option value="desc"
                                                    selected="selected"><?php _e( 'Descendant', WPVR_LANG ); ?></option>
                                        </select>
                                        <br/><br/>

                                        <div class="wpvr_button wpvr_manage_refresh">
                                            <i class="wpvr_button_icon fa fa-sort"></i>
											<?php _e( 'Sort Results', WPVR_LANG ); ?>
                                        </div>


                                    </div>
                                </div>
                                <!-- ORDER  -->
							<?php } ?>


                            <div class="wpvr_clearfix"></div>

                        </div>


                        <!-- manage_filter -->
                    </div>
                    <div class="wpvr_manage_main">

                        <div class="wpvr_manage_page">
								<span class="wpvr_manage_page_left ">
									<span class="wpvr_manage_page_message"></span>
								</span>
                            <span class="wpvr_manage_page_right ">
									<span class="wpvr_manage_page_select"></span>
								</span>


                        </div>
						
						<?php
							if ( ! $is_DT ) {
								$refresh_once = "1";
							} else {
								$refresh_once = "";
							}
						?>

                        <div class="wpvr_manage_content" is_fresh="1" refresh_once="<?php echo $refresh_once; ?>"></div>


                        <div class="wpvr_clearfix"></div>
                    </div>
                    <div class="wpvr_clearfix"></div>

                </div>
            </form>
            <div class="wpvr_clearfix"></div>


        </div>


    </div>
</div>