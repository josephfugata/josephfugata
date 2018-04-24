<?php
	
	
	function wpvr_mail( $args ) {
		$token = bin2hex( openssl_random_pseudo_bytes( 50 ) );
		$args  = wp_parse_args( $args, array(
			'recipients'  => array(),
			'subject'     => 'Default WPVR Mail Subject',
			'head'        => 'Default WPVR Mail Footer',
			'body'        => 'Default WPVR Mail Body',
			'footer'      => 'Default WPVR Mail Footer',
			'headers'     => array(),
			'attachments' => array(),
		) );
		
		$args['headers'][] = 'Content-Type: text/html; charset=UTF-8';
		$args['headers'][] = 'From: WP Video Robot <wpvideorobot@' . $_SERVER['SERVER_NAME'] . '>';
		
		
		$subject = apply_filters( 'wpvr_extend_mail_subject', $args['subject'] );
		
		ob_start();
		?>

        <div style="background:#F1F1F1;padding: 40px;">
			<?php echo apply_filters( 'wpvr_extend_mail_head', $args['head'] ); ?>
			<?php echo apply_filters( 'wpvr_extend_mail_body', $args['body'] ); ?>
			<?php echo apply_filters( 'wpvr_extend_mail_footer', $args['footer'] ); ?>


        </div>

        <div style="display:none;visibility:hidden;color:transparent;">
			<?php echo $token; ?>
        </div>
		<?php
		$message = ob_get_contents();
		ob_end_clean();
		
		return wp_mail(
			$args['recipients'],
			$subject,
			$message,
			$args['headers'],
			$args['attachments']
		);
		
	}
	
	
	add_action( 'wpvr_extend_mail_subject', 'wpvr_define_mail_subject', 100, 1 );
	function wpvr_define_mail_subject( $subject ) {
		return 'WP Video Robot - ' . $subject . ' [' . $_SERVER['SERVER_NAME'] . ']';
	}
	
	add_action( 'wpvr_extend_mail_head', 'wpvr_define_mail_head', 100, 1 );
	function wpvr_define_mail_head( $head ) {
		
		ob_start();
		?>


        <div style="background:#303e4d;padding:25px 60px 25px 60px;margin:0 auto; max-width:612px;text-align:left;">
            <img
                    alt="WP Video Robot"
                    title="WP Video Robot"
                    style="color:#FFF;border:none !important;"
                    src="<?php echo WPVR_LOGO_WHITE; ?>"
            />

            <span style="line-height: 30px;float:right;font-weight:bold;color:#FFF;">
				<a href="<?php echo $_SERVER['SERVER_NAME']; ?>" style="color:#FFF;">
					<?php echo $_SERVER['SERVER_NAME']; ?>
				</a>
			</span>

        </div>
		
		
		<?php
		$output_string = ob_get_contents();
		ob_end_clean();
		
		return $output_string;
		
	}
	
	add_action( 'wpvr_extend_mail_body', 'wpvr_define_mail_body', 100, 1 );
	function wpvr_define_mail_body( $body ) {
		
		ob_start();
		?>


        <div style="background:#FFF;padding:40px 60px; margin:0 auto; max-width:612px;font-size:15px;color:#AAA;line-height:16px;">
			
			<?php echo $body; ?>

        </div>
		
		
		<?php
		$output_string = ob_get_contents();
		ob_end_clean();
		
		return $output_string;
		
	}
	
	add_action( 'wpvr_extend_mail_footer', 'wpvr_define_mail_footer', 100, 1 );
	function wpvr_define_mail_footer( $footer ) {
		
		ob_start();
		?>


        <div style="background:#FFF;padding:16px 60px 40px 60px; margin:0 auto; max-width:612px;font-size:15px;color:#AAA;line-height:16px;">
            Have a good day! <br/>
            <strong>WP Video Robot</strong>
        </div>
		
		
		<?php
		$output_string = ob_get_contents();
		ob_end_clean();
		
		return $output_string;
		
	}
	
	
	
	