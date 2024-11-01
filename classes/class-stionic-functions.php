<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
if ( ! class_exists( 'Stionic_Functions' ) ) {
	class Stionic_Functions {
		public static function esc_json_option( $data ) {
			return is_string( $data ) ? wp_unslash( $data ) : wp_json_encode( $data, JSON_UNESCAPED_UNICODE );
		}
		public static function decode_json_option( $data ) {
			return is_array( $data ) ? $data : json_decode( wp_unslash( $data ), true );
		}
	}
	new Stionic_Functions();
}
