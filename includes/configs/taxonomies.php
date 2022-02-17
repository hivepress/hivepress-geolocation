<?php
/**
 * Taxonomies configuration.
 *
 * @package HivePress\Configs
 */

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

return [
	'listing_region' => [
		'post_type'    => [ 'listing' ],
		'hierarchical' => true,
		'rewrite'      => [ 'slug' => 'listing-region' ],

		'labels'       => [
			'name'          => esc_html__( 'Regions', 'hivepress-geolocation' ),
			'singular_name' => esc_html__( 'Region', 'hivepress-geolocation' ),
			'add_new_item'  => esc_html__( 'Add Region', 'hivepress-geolocation' ),
			'edit_item'     => esc_html__( 'Edit Region', 'hivepress-geolocation' ),
			'update_item'   => esc_html__( 'Update Region', 'hivepress-geolocation' ),
			'view_item'     => esc_html__( 'View Region', 'hivepress-geolocation' ),
			'parent_item'   => esc_html__( 'Parent Region', 'hivepress-geolocation' ),
			'search_items'  => esc_html__( 'Search Regions', 'hivepress-geolocation' ),
			'not_found'     => esc_html__( 'No regions found', 'hivepress-geolocation' ),
		],
	],
];
