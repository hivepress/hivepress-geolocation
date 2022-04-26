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
	'km'      => esc_html__( 'km', 'hivepress-geolocation' ),
	'miles'   => esc_html__( 'miles', 'hivepress-geolocation' ),

	// Geographical areas.
	'country' => esc_html__( 'Country', 'hivepress-geolocation' ),
	'state'   => esc_html__( 'State', 'hivepress-geolocation' ),
	'county'  => esc_html__( 'County', 'hivepress-geolocation' ),
	'city'    => esc_html__( 'City', 'hivepress-geolocation' ),
];
