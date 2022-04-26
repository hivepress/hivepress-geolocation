<?php
/**
 * Strings configuration.
 *
 * @package HivePress\Configs
 */

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

return [

	// Common.
	'km'    => esc_html__( 'km', 'hivepress-geolocation' ),
	'miles' => esc_html__( 'miles', 'hivepress-geolocation' ),
];
