<?php
/**
 * Settings configuration.
 *
 * @package HivePress\Configs
 */

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

return [
	'geolocation'  => [
		'title'    => esc_html__( 'Geolocation', 'hivepress-geolocation' ),
		'_order'   => 990,

		'sections' => [
			'restrictions' => [
				'title'  => esc_html__( 'Geolocation', 'hivepress-geolocation' ),
				'_order' => 100,

				'fields' => [
					'geolocation_models'           => [
						'label'       => esc_html__( 'todo', 'hivepress-geolocation' ),
						'description' => esc_html__( 'todo', 'hivepress-geolocation' ),
						'type'        => 'select',
						'default'     => [ 'listing' ],
						'multiple'    => true,
						'required'    => true,
						'_order'      => 10,

						'options'     => [
							'listing' => hivepress()->translator->get_string( 'listings' ),
							'vendor'  => hivepress()->translator->get_string( 'vendors' ),
						],
					],

					'geolocation_provider'         => [
						'label'       => esc_html__( 'Map Provider', 'hivepress-geolocation' ),
						'type'        => 'select',
						'placeholder' => 'Google Maps',
						'_order'      => 20,

						'options'     => [
							'mapbox' => 'Mapbox',
						],
					],

					'geolocation_countries'        => [
						'label'    => esc_html__( 'Countries', 'hivepress-geolocation' ),
						'type'     => 'select',
						'options'  => 'countries',
						'multiple' => true,
						'_order'   => 30,
					],

					'geolocation_max_zoom'         => [
						'label'     => esc_html__( 'Zoom', 'hivepress-geolocation' ),
						'type'      => 'number',
						'min_value' => 2,
						'max_value' => 20,
						'default'   => 18,
						'required'  => true,
						'_order'    => 40,
					],

					'geolocation_radius'           => [
						'label'       => esc_html__( 'Radius', 'hivepress-geolocation' ),
						'description' => esc_html__( 'todo', 'hivepress-geolocation' ),
						'type'        => 'number',
						'min_value'   => 1,
						'default'     => 15,
						'required'    => true,
						'_order'      => 50,
					],

					'geolocation_use_miles'        => [
						'caption' => esc_html__( 'Use miles instead of kilometers', 'hivepress-geolocation' ),
						'type'    => 'checkbox',
						'_order'  => 60,
					],

					'geolocation_allow_radius'     => [
						'caption' => esc_html__( 'Allow users to change radius', 'hivepress-geolocation' ),
						'type'    => 'checkbox',
						'_order'  => 70,
					],

					'geolocation_generate_regions' => [
						'label'       => esc_html__( 'Regions', 'hivepress-geolocation' ),
						'description' => esc_html__( 'Check this option to create a page for each region.', 'hivepress-geolocation' ),
						'caption'     => esc_html__( 'Generate regions from locations', 'hivepress-geolocation' ),
						'type'        => 'checkbox',
						'_order'      => 80,
					],

					'geolocation_region_levels'    => [
						'label'       => esc_html__( 'Region Levels', 'hivepress-geolocation' ),
						'description' => esc_html__( 'todo', 'hivepress-geolocation' ),
						'type'        => 'select',
						'multiple'    => true,
						'_parent'     => 'geolocation_generate_regions',
						'_order'      => 90,

						'options'     => [
							'country' => esc_html__( 'Country', 'hivepress-geolocation' ),
							'state'   => esc_html__( 'State', 'hivepress-geolocation' ),
							'county'  => esc_html__( 'County', 'hivepress-geolocation' ),
							'city'    => esc_html__( 'City', 'hivepress-geolocation' ),
						],
					],

					'geolocation_address_format'   => [
						'label'       => esc_html__( 'Address', 'hivepress-geolocation' ),
						'description' => esc_html__( 'todo', 'hivepress-geolocation' ) . ' ' . sprintf( hivepress()->translator->get_string( 'these_tokens_are_available' ), '%listing%, %vendor%' ),
						'type'        => 'text',
						'max_length'  => 256,
						'_order'      => 100,
					],

					'geolocation_hide_address'     => [
						'description' => esc_html__( 'todo.', 'hivepress-geolocation' ),
						'caption'     => esc_html__( 'Hide the exact address', 'hivepress-geolocation' ),
						'type'        => 'checkbox',
						'_order'      => 110,
					],
				],
			],
		],
	],

	'integrations' => [
		'sections' => [
			'gmaps'  => [
				'title'  => 'Google Maps',
				'_order' => 30,

				'fields' => [
					'gmaps_api_key'    => [
						'label'      => hivepress()->translator->get_string( 'api_key' ),
						'type'       => 'text',
						'max_length' => 256,
						'_order'     => 10,
					],

					'gmaps_secret_key' => [
						'label'       => hivepress()->translator->get_string( 'secret_key' ),
						'description' => esc_html__( 'Set the API key without HTTP restrictions used for generating regions from locations.', 'hivepress-geolocation' ),
						'type'        => 'text',
						'max_length'  => 256,
						'_order'      => 20,
					],
				],
			],

			'mapbox' => [
				'title'  => 'Mapbox',
				'_order' => 40,

				'fields' => [
					'mapbox_api_key' => [
						'label'      => hivepress()->translator->get_string( 'api_key' ),
						'type'       => 'text',
						'max_length' => 256,
						'_order'     => 10,
					],
				],
			],
		],
	],
];
