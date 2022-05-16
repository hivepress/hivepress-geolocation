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
	'km'                => esc_html__( 'km', 'hivepress-geolocation' ),
	'miles'             => esc_html__( 'miles', 'hivepress-geolocation' ),
	'location'          => esc_html__( 'Location', 'hivepress-geolocation' ),
	'map'               => esc_html__( 'Map', 'hivepress-geolocation' ),

	// Geographical areas.
	'country'           => esc_html__( 'Country', 'hivepress-geolocation' ),
	'state'             => esc_html__( 'State', 'hivepress-geolocation' ),
	'county'            => esc_html__( 'County', 'hivepress-geolocation' ),
	'city'              => esc_html__( 'City', 'hivepress-geolocation' ),

	// API.
	'public_api_key'    => esc_html__( 'Public API Key', 'hivepress-geolocation' ),
	'secret_api_key'    => esc_html__( 'Secret API Key', 'hivepress-geolocation' ),

	// Taxonomies.
	'regions'           => esc_html__( 'Regions', 'hivepress-geolocation' ),
	'region'            => esc_html__( 'Region', 'hivepress-geolocation' ),
	'add_new_region'    => esc_html__( 'Add Region', 'hivepress-geolocation' ),
	'edit_region'       => esc_html__( 'Edit Region', 'hivepress-geolocation' ),
	'update_region'     => esc_html__( 'Update Region', 'hivepress-geolocation' ),
	'view_region'       => esc_html__( 'View Region', 'hivepress-geolocation' ),
	'parent_region'     => esc_html__( 'Parent Region', 'hivepress-geolocation' ),
	'search_regions'    => esc_html__( 'Search Regions', 'hivepress-geolocation' ),
	'not_found_regions' => esc_html__( 'No regions found', 'hivepress-geolocation' ),
];
