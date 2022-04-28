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
	'km'             => esc_html__( 'km', 'hivepress-geolocation' ),
	'miles'          => esc_html__( 'miles', 'hivepress-geolocation' ),

	// Geographical areas.
	'country'        => esc_html__( 'Country', 'hivepress-geolocation' ),
	'state'          => esc_html__( 'State', 'hivepress-geolocation' ),
	'county'         => esc_html__( 'County', 'hivepress-geolocation' ),
	'city'           => esc_html__( 'City', 'hivepress-geolocation' ),

	// API.
	'public_api_key' => esc_html__( 'Public API Key', 'hivepress-geolocation' ),
	'secret_api_key' => esc_html__( 'Secret API Key', 'hivepress-geolocation' ),
];
