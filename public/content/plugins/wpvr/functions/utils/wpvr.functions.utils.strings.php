<?php
	
	function wpvr_strip_html_bad_tags( $string ) {
		return addslashes( preg_replace('#<script(.*?)>(.*?)</script>#is', '', $string) );
	}
	
	function wpvr_substr( $str, $length = 15 ) {
		
		if ( $length <= 5 ) {
			return $str;
		}
		
		if ( strlen( $str ) >= $length ) {
			return substr( $str, 0, $length - 5 ) . ' (...)';
		}
		
		return $str;
	}
	
	function wpvr_clean_up_tags( $tags ) {
		$bad_tags   = array( '...', '..', '.' );
		$clean_tags = array();
		foreach ( (array) $tags as $tag ) {
			if ( ! in_array( trim( $tag ), $bad_tags ) ) {
				$clean_tags[] = trim( $tag );
			}
		}
		
		return $clean_tags;
	}
	
	function wpvr_encrypt_string( $q ) {
		$cryptKey = 'qJB0rGtIn5UB1xG03efyCp';
		$qEncoded = base64_encode( mcrypt_encrypt( MCRYPT_RIJNDAEL_256, md5( $cryptKey ), $q, MCRYPT_MODE_CBC, md5( md5( $cryptKey ) ) ) );
		
		return ( $qEncoded );
	}
	
	function wpvr_decrypt_string( $q ) {
		$cryptKey = 'qJB0rGtIn5UB1xG03efyCp';
		$qDecoded = rtrim( mcrypt_decrypt( MCRYPT_RIJNDAEL_256, md5( $cryptKey ), base64_decode( $q ), MCRYPT_MODE_CBC, md5( md5( $cryptKey ) ) ), "\0" );
		
		return ( $qDecoded );
	}
	
	function wpvr_json_encode( $data, $utf = false ) {
		if ( $utf ) {
			$data = wpvr_utf8_converter( $data );
		}
		
		return json_encode( $data );
	}
	
	function wpvr_json_decode( $data, $utf = false ) {
		if ( $utf ) {
			$data = utf8_decode( $data );
		}
		$decoded = json_decode( $data );
		
		//$decoded = wpvr_utf8_recursive_decode( $decoded );
		return $decoded;
	}
	
	function wpvr_utf8_converter( $array ) {
		array_walk_recursive( $array, function ( &$item, $key ) {
			if ( is_string( $item ) && ! mb_detect_encoding( $item, 'utf-8', true ) ) {
				$item = utf8_encode( $item );
			}
		} );
		
		return $array;
	}
	
	function wpvr_utf8_recursive_encode( &$input ) {
		if ( is_string( $input ) ) {
			$input = utf8_encode( $input );
		} else if ( is_array( $input ) ) {
			foreach ( $input as &$value ) {
				wpvr_utf8_recursive_encode( $value );
			}
			unset( $value );
		} else if ( is_object( $input ) ) {
			$vars = array_keys( get_object_vars( $input ) );
			foreach ( $vars as $var ) {
				wpvr_utf8_recursive_encode( $input->$var );
			}
		}
	}
	
	function wpvr_utf8_recursive_decode( &$input ) {
		if ( is_string( $input ) ) {
			$input = utf8_decode( $input );
		} else if ( is_array( $input ) ) {
			foreach ( $input as &$value ) {
				wpvr_utf8_recursive_decode( $value );
			}
			unset( $value );
		} else if ( is_object( $input ) ) {
			$vars = array_keys( get_object_vars( $input ) );
			foreach ( $vars as $var ) {
				wpvr_utf8_recursive_decode( $input->$var );
			}
		}
	}
	
	function wpvr_get_json_response( $data, $response_status = 1, $response_msg = '', $response_count = 0 ) {
		$response         = array(
			'status' => $response_status,
			'msg'    => $response_msg,
			'count'  => $response_count,
			'data'   => $data,
		);
		$encoded_response = WPVR_JS . wpvr_json_encode( $response ) . WPVR_JS;
		
		return $encoded_response;
	}
	
	function wpvr_render_html_attributes( $attr = array() ) {
		$output = '';
		if ( ! is_array( $attr ) || count( $attr ) == 0 ) {
			return $output;
		}
		foreach ( (array) $attr as $key => $value ) {
			if ( $value == '' || empty( $value ) ) {
				$output .= ' ' . $key . ' ';
			} else {
				$output .= ' ' . $key . ' = "' . $value . '" ';
			}
		}
		
		//_d( $output );
		return $output;
	}
	
	function wpvr_numberK( $n, $double = false ) {
		
		if ( $n <= 999 ) {
			if ( $double && $n < 10 ) {
				return '0' . $n;
			} else {
				return $n;
			}
		} elseif ( $n > 999 && $n < 999999 ) {
			return round( $n / 1000, 2 ) . 'K';
		} elseif ( $n > 99999999 ) {
			return '+99M';
		} elseif ( $n > 999999 ) {
			return round( $n / 1000000, 2 ) . 'M';
		} else {
			return false;
		}
	}
	
	function wpvr_get_plural( $count, $singular, $plural ) {
		return $count > 1 ? $plural : $singular;
	}
	
	/* Get Playlis Data from Channel Id */
	function wpvr_parse_string( $string ) {
		$items     = explode( ',', $string );
		$new_items = array();
		if ( count( $items ) == 0 ) {
			return array();
		} else {
			foreach ( (array) $items as $item ) {
				if ( $item != '' ) {
					$new_items[] = $item;
				}
			}
		}
		
		return $new_items;
	}
	
	function wpvr_randomize_build_terms( $search_terms, $count ) {
		
		$bad_characters = array( 'of', 'at', 'to', 'the', 'in', 'on', 'by', ',', '.', ':', ';', '"', "'", "`" );
		$random_terms   = array();
		$search_terms   = str_replace( $bad_characters, '', $search_terms );
		$terms_array    = explode( ' ', $search_terms );
		$random_keys    = array_rand( $terms_array, $count );
		foreach ( (array) $random_keys as $random_key ) {
			$random_terms [] = strtolower( $terms_array[ $random_key ] );
		}
		
		
		$random_search_terms = implode( ' | ', $random_terms );
		
		return $random_search_terms;
		
	}
	
	function wpvr_retreive_video_id_from_param( $param, $service ) {
		if ( $service == 'youtube' ) {
			////////////// YOUTUBE //////////////
			//https://youtu.be/uIi0xm_tlCU
			if ( strpos( $param, 'youtu.be' ) !== false ) {
				$separator = ( strpos( $param, 'https://' ) !== false ) ? 'https://youtu.be/' : 'http://youtu.be/';
				$x         = explode( $separator, $param );
				if ( ! isset( $x[1] ) ) {
					return false;
				} else {
					return $x[1];
				}
				
			} elseif ( strpos( $param, 'youtube.com' ) === false ) {
				return $param;
			} else {
				parse_str( parse_url( $param, PHP_URL_QUERY ), $args );
				if ( isset( $args['v'] ) ) {
					return $args['v'];
				} else {
					return false;
				}
			}
		} elseif ( $service == 'vimeo' ) {
			////////////// VIMEO //////////////
			if ( strpos( $param, 'vimeo.com' ) === false ) {
				return $param;
			} else {
				if ( strpos( $param, 'www.vimeo' ) === false ) {
					$separator = ( strpos( $param, 'https://' ) !== false ) ? 'https://vimeo.com/' : 'http://vimeo.com/';
				} else {
					$separator = ( strpos( $param, 'https://' ) !== false ) ? 'https://www.vimeo.com/' : 'http://www.vimeo.com/';
				}
				$x = explode( $separator, $param );
				if ( ! isset( $x[1] ) ) {
					return false;
				} else {
					$y = explode( '/', $x[1] );
					
					return $y[0];
				}
			}
		} elseif ( $service == 'facebook' ) {
			////////////// VIMEO //////////////
			if ( strpos( $param, 'facebook.com' ) === false ) {
				return $param;
			} else {
				$separator = '/videos/';
				$x         = explode( $separator, $param );
				if ( ! isset( $x[1] ) ) {
					return false;
				} else {
					$y = explode( '/', $x[1] );
					
					return $y[0];
				}
			}
		} elseif ( $service == 'dailymotion' ) {
			
			////////////// DAILYMOTION //////////////
			//http://dai.ly/x346uwt
			if ( strpos( $param, 'dai.ly' ) !== false ) {
				$separator = ( strpos( $param, 'https://' ) !== false ) ? 'https://dai.ly/' : 'http://dai.ly/';
				$x         = explode( $separator, $param );
				if ( ! isset( $x[1] ) ) {
					return false;
				} else {
					return $x[1];
				}
			} elseif ( strpos( $param, 'dailymotion.com' ) === false ) {
				return $param;
			} else {
				
				if ( strpos( $param, 'www.dailymotion' ) !== false ) {
					$separator = ( strpos( $param, 'https://' ) !== false ) ? 'https://www.dailymotion.com/video/' : 'http://www.dailymotion.com/video/';
				} else {
					$separator = ( strpos( $param, 'https://' ) !== false ) ? 'https://dailymotion.com/video/' : 'http://dailymotion.com/video/';
				}
				$x = explode( $separator, $param );
				if ( ! isset( $x[1] ) ) {
					return false;
				} else {
					$y = explode( '_', $x[1] );
					
					return $y[0];
				}
			}
			
		} elseif ( $service == 'ted' ) {
			
			////////////// TED //////////////
			if ( strpos( $param, 'ted.com' ) === false ) {
				return $param;
			} else {
				if ( strpos( $param, 'www.ted.com' ) !== false ) {
					$separator = ( strpos( $param, 'https://' ) !== false ) ? 'https://www.ted.com/talks/' : 'http://www.ted.com/talks/';
				} else {
					$separator = ( strpos( $param, 'https://' ) !== false ) ? 'https://ted.com/talks/' : 'http://ted.com/talks/';
				}
				$x = explode( $separator, $param );
				if ( ! isset( $x[1] ) ) {
					return false;
				} else {
					$y = explode( '/', $x[1] );
					
					return $y[0];
				}
			}
			
		} elseif ( $service == 'youku' ) {
			
			////////////// YOUKU //////////////
			if ( strpos( $param, 'youku.com' ) === false ) {
				return $param;
			} else {
				$separator = ( strpos( $param, 'https://' ) !== false ) ? 'https://v.youku.com/v_show/id_' : 'http://v.youku.com/v_show/id_';
				$x         = explode( $separator, $param );
				if ( ! isset( $x[1] ) ) {
					return false;
				} else {
					$y = explode( '.', $x[1] );
					
					return $y[0];
				}
			}
			
		} else {
			return $param;
		}
	}
	
	function wpvr_parse_config_file( $path_to_file ) {
		$config = array();
		$lines  = explode( "\n", file_get_contents( $path_to_file ) );
		foreach ( (array) $lines as $line ) {
			if ( strpos( strtolower( $line ), 'define' ) === false ) {
				continue;
			}
			
			if ( strpos( strtolower( $line ), '/*' ) !== false ) {
				continue;
			}
			
			$line = trim( str_replace( array( ' ', 'define', ')', ';', '(' ), '', $line ) );
			$pair = explode( ',', $line );
			
			if ( ! isset( $pair[1] ) ) {
				continue;
			}
			$value = str_replace( "'", '', $pair[1] );
			if ( $pair[1] == 'true' ) {
				$value = true;
			} elseif ( $pair[1] == 'false' ) {
				$value = false;
			}
			$config[ str_replace( "'", '', $pair[0] ) ] = $value;
		}
		
		return $config;
	}
	
	