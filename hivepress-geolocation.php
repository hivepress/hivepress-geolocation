<?php
/**
 * Plugin Name: HivePress Geolocation
 * Description: Allow users to search listings by location.
 * Requires at least: 5.0
 * Requires PHP: 7.4
 * Version: 1.3.10
 * Author: HivePress
 * Author URI: https://hivepress.io/
 * Text Domain: hivepress-geolocation
 * Domain Path: /languages/
 *
 * @package HivePress
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Register extension directory.
add_filter(
	'hivepress/v1/extensions',
	function( $extensions ) {
		$extensions[] = __DIR__;

		return $extensions;
	}
);
