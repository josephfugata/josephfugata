<?php
	
	function wpvr_human_time_diff( $post_id ) {
		$post          = get_post( $post_id );
		$now_date_obj  = DateTime::createFromFormat( 'Y-m-d H:i:s', current_time( 'Y-m-d H:i:s' ) );
		$now_date      = $now_date_obj->format( 'U' );
		$post_date_obj = DateTime::createFromFormat( 'Y-m-d H:i:s', $post->post_date );
		$post_date     = $post_date_obj->format( 'U' );
		
		
		return strtolower( sprintf( __( '%s ago' ), human_time_diff( $post_date, $now_date ) ) );
	}
	
	function wpvr_datetime_human_diff( $time ) {
		$now  = new Datetime( 'now', new DateTimeZone( 'UTC' ) );
		$then = new Datetime( $time, new DateTimeZone( 'UTC' ) );
		
		$human_diff =  human_time_diff( $then->format( 'U' ), $now->format( 'U' ) ) ;
		return strtolower( sprintf( __( '%s ago' ), $human_diff ) );
	}
	
	function wpvr_chrono_time( $start = false, $round = 6 ) {
		$time = explode( ' ', microtime() );
		if ( $start === false ) {
			return $time[0] + $time[1];
		} else {
			return round( wpvr_chrono_time() - $start, $round );
		}
		
		return true;
	}
	
	function wpvr_human_duration( $seconds ) {
		if ( $seconds > 86400 ) {
			$seconds -= 86400;
			
			return ( gmdate( "j\d H:i:s", $seconds ) );
		} else {
			return ( gmdate( "H:i:s", $seconds ) );
		}
	}
	
	function wpvr_get_zoned_formatted_time( $time ) {
		global $wpvr_options;
		$zoned  = wpvr_get_time( $time, false, true, true, true );
		$format = $wpvr_options['timeFormat'] == 'standard' ? 'Y-m-d H:i:s' : 'Y-m-d h:i:sA';
		
		return $zoned->format( $format );
	}
	
	function wpvr_get_working_hours_formatted() {
		global $wpvr_hours, $wpvr_hours_us, $wpvr_options;
		
		$wpvr_hours_formatted = $wpvr_options['timeFormat'] == 'standard' ? $wpvr_hours : $wpvr_hours_us;
		
		if ( $wpvr_options['wakeUpHours'] === false ) {
			return __( 'All the time', WPVR_LANG );
		}
		
		$whA = $wpvr_options['wakeUpHoursA'] <= 9 ? '0' . $wpvr_options['wakeUpHoursA'] . 'H00' : $wpvr_options['wakeUpHoursA'] . 'H00';
		$whB = $wpvr_options['wakeUpHoursB'] <= 9 ? '0' . $wpvr_options['wakeUpHoursB'] . 'H00' : $wpvr_options['wakeUpHoursB'] . 'H00';
		
		return __( 'from', WPVR_LANG ) . " {$wpvr_hours_formatted[ $whA ]} " .
		       __( 'to', WPVR_LANG ) . " {$wpvr_hours_formatted[ $whB ]} ";
		
	}
	
	function wpvr_make_interval( $start, $end, $bool = true ) {
		
		if ( $start == '' || $end == '' ) {
			return array();
		}
		
		$workingHours = array();
		for ( $i = 0; $i < 24; $i ++ ) {
			if ( strlen( $i ) == 1 ) {
				$i = '0' . $i;
			}
			$workingHours[ $i ] = ! $bool;
		}
		if ( $start > $end ) {
			return wpvr_make_interval( $end, $start, ! $bool );
		} elseif ( $start == $end ) {
			return array();
		} else {
			if ( $start <= 12 && $end <= 12 ) {
				for ( $i = $start; $i <= $end; $i ++ ) {
					if ( strlen( $i ) == 1 ) {
						$i = '0' . $i;
					}
					$workingHours[ $i ] = $bool;
				}
			} elseif ( $start > 12 && $end > 12 ) {
				for ( $i = $start; $i <= $end; $i ++ ) {
					if ( strlen( $i ) == 1 ) {
						$i = '0' . $i;
					}
					$workingHours[ $i ] = $bool;
				}
				
			} elseif ( $start < 12 && $end > 12 ) {
				for ( $i = $start; $i < 12; $i ++ ) {
					if ( strlen( $i ) == 1 ) {
						$i = '0' . $i;
					}
					$workingHours[ $i ] = $bool;
				}
				
				for ( $i = 12; $i <= $end; $i ++ ) {
					if ( strlen( $i ) == 1 ) {
						$i = '0' . $i;
					}
					$workingHours[ $i ] = $bool;
				}
				
				$workingHours[ $start ] = $workingHours[ $end ] = true;
				
			}
		}
		
		return $workingHours;
	}
	
	function wpvr_make_postdate( $datetime = '' ) {
		global $wpvr_options;
		
		$gmt_offset = get_option( 'gmt_offset' );
		$post_date  = $datetime == '' ? 'now' : $datetime;
		$post_date  = new DateTime( $post_date );
		
		if ( $datetime == '' ) {
			$post_date == new DateTime( 'now' );
		} else {
			$post_date = new DateTime( $datetime );
		}
		
		
		//_d( $post_date->format('H:i:s') );
		
		// Convert Updated DAte to use WP Offset
		if ( $datetime == '' ) {
			// echo 'adapting time ...';
			if ( $gmt_offset > 0 ) {
				$post_date->add( new DateInterval( 'PT' . abs( $gmt_offset ) . 'H' ) );
			} elseif ( $gmt_offset < 0 ) {
				$post_date->sub( new DateInterval( 'PT' . abs( $gmt_offset ) . 'H' ) );
			}
		}
		
		
		// _d( $gmt_offset );
		// _d( $post_date->format('H:i:s') );
		
		return $post_date;
	}
	
	function wpvr_get_timezone_name( $zone_id ) {
		global $wpvr_timezones;
		$timezones = array();
		foreach ( (array) $wpvr_timezones as $groupid => $group ) {
			$timezones[ $groupid ] = array();
			
			if ( ! isset( $group[ $zone_id ] ) ) {
				continue;
			}
			
			return str_replace( '_', ' ', $group[ $zone_id ] );
		}
		
		return '';
		
	}
	
	function wpvr_get_timezone() {
		global $wpvr_options;
		$timeZone = 'UTC';
		$wpvr_options['timeZone'];
		if ( ! isset( $wpvr_options['timeZone'] ) ) {
			return 'UTC';
		}
		
		if ( isset( $wpvr_options['timeZone'][0] ) && $wpvr_options['timeZone'][0] != '0' ) {
			$timezone = $wpvr_options['timeZone'][0];
		} else if ( isset( $wpvr_options['timeZone'][1] ) && $wpvr_options['timeZone'][1] != '0' ) {
			$timezone = $wpvr_options['timeZone'][1];
		} else {
			$timezone = 'UTC';
		}
		
		if ( ! in_array( $timezone, timezone_identifiers_list() ) ) {
			$timezone = 'UTC';
		}
		
		// wpvr_ooo( $timezone );
		return $timezone;
		
	}
	
	function wpvr_get_time( $time, $seconds = true, $timezone = false, $time_only = false, $full_date = false ) {
		global $wpvr_options;
		if ( strpos( $time, 'H' ) !== false ) {
			$php_time  = str_replace( 'H', ':', $time );
			$separator = '\H';
		} else {
			$separator = ':';
			$php_time  = $time;
		}
		$time_object = new DateTime( $php_time . ' +00' );
		// d( $time_object );
		
		if ( $timezone !== false ) {
			$time_object->setTimezone( new DateTimeZone( wpvr_get_timezone() ) );
		}
		// d( $time_object->format('H:i:s') );
		//wpvr_die();
		$seconds_format = $seconds === true ? ':s' : '';
		
		$days_format = $full_date ? ' Y-m-d ' : '';
		
		if ( $wpvr_options['timeFormat'] == 'standard' ) {
			$output_time = $time_object->format( $days_format . 'H' . $separator . 'i' . $seconds_format );
		} else {
			$output_time = $time_object->format( $days_format . 'h' . $separator . 'i' . $seconds_format . ' A' );
		}
		if ( $time_only === true ) {
			return $time_object;
		}
		if ( $time_only === false ) {
			return '<span class="wpvr_time" title="' . wpvr_get_timezone_name( wpvr_get_timezone() ) . '">' . $output_time . '</span>';
		}
		if ( $time_only == 'output' ) {
			return $output_time;
		}
		
		
	}
	
	