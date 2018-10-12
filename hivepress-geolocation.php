<?php
/**
 * Plugin Name: HivePress Geolocation
 * Description: Geolocation add-on for HivePress plugin.
 * Version: 1.0.0
 * Author: HivePress
 * Author URI: https://hivepress.co/
 * Text Domain: hivepress-geolocation
 * Domain Path: /languages/
 *
 * @package HivePress\Geolocation
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Register plugin path.
add_filter(
	'hivepress/core/plugin_paths',
	function( $paths ) {
		return array_merge( $paths, [ dirname( __FILE__ ) ] );
	}
);
