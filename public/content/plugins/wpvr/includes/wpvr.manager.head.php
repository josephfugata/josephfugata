<div class = "wpvr_manage_head">
	
	<div class = "wpvr_manage_head_left pull-left noMargin">
		<button class = "wpvr_button pull-left wpvr_track_dups" url = "<?php echo $wpvr_dups_url; ?>">
			<i class = "wpvr_button_icon fa fa-copy"></i>
			<?php _e( 'Track Duplicates' , WPVR_LANG ); ?>
		</button>
		<button class = "wpvr_button pull-left wpvr_manage_exportAll" url = "<?php echo $wpvr_url; ?>">
			<i class = "wpvr_button_icon fa fa-upload"></i>
			<?php _e( 'Export All Videos' , WPVR_LANG ); ?>
		</button>
		<?php if ( FALSE ) { ?>
			<button
				class = "wpvr_button pull-left wpvr_manage_import"
				url = "<?php echo $wpvr_url; ?>"
				bulk_url = "<?php echo $wpvr_bulk_url; ?>"
				buffer = "<?php echo WPVR_BULK_IMPORT_BUFFER; ?>"
			>
				<i class = "wpvr_button_icon fa fa-download"></i>
				<?php _e( 'Import Videos' , WPVR_LANG ); ?>
			</button>
		<?php } ?>
		<div class = "wpvr_manage_message"></div>
	
	</div>
	<div class = "wpvr_manage_head_right noMargin">
		<input class = "wpvr_manage_search_input" name = "filter_search" type = "text" placeholder = "<?php _e( 'Search Videos' , WPVR_LANG ); ?>"/>
		<button class = "wpvr_button wpvr_small wpvr_manage_search_button">
			<i class = "fa fa-search"></i> Search
		</button>
	</div>
	<div class = "wpvr_clearfix"></div>
</div>