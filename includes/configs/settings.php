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
	'listings'     => [
		'sections' => [
			'geolocation' => [
				'title'  => esc_html__( 'Geolocation', 'hivepress-geolocation' ),
				'_order' => 100,

				'fields' => [
					'geolocation_provider'         => [
						'label'       => esc_html__( 'Map Provider', 'hivepress-geolocation' ),
						'type'        => 'select',
						'placeholder' => 'Google Maps',
						'_order'      => 10,

						'options'     => [
							'mapbox' => 'Mapbox',
						],
					],

					'geolocation_location_format'  => [
						'label'       => hivepress()->translator->get_string( 'location' ),
						'description' => sprintf(
						/* translators: 1: country token, 2: state token, 3: county token, 4: city token, 5: address token. */
							esc_html__( 'Set the location display format to generate location based on tokens: %1$s, %2$s, %3$s, %4$s, %5$s.', 'hivepress-geolocation' ),
							'%country%',
							'%state%',
							'%county%',
							'%city%',
							'%place_address%'
						),
						'type'        => 'text',
						'max_length'  => 256,
						'_order'      => 20,
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
						'label'     => esc_html__( 'Radius', 'hivepress-geolocation' ),
						'statuses'  => [ get_option( 'hp_geolocation_metric' ) ? get_option( 'hp_geolocation_metric' ) : hivepress()->translator->get_string( 'km' ) ],
						'type'      => 'number',
						'min_value' => 1,
						'default'   => 15,
						'required'  => true,
						'_order'    => 50,
					],

					'geolocation_enable_related'   => [
						'caption' => esc_html__( 'Allow to show nearest related listings within the set radius', 'hivepress-geolocation' ),
						'type'    => 'checkbox',
						'_order'  => 60,
					],

					'geolocation_allow_radius'     => [
						'caption' => esc_html__( 'Allow users to change radius', 'hivepress-geolocation' ),
						'type'    => 'checkbox',
						'_order'  => 70,
					],

					'geolocation_metric'           => [
						'label'       => esc_html__( 'Metric system', 'hivepress-geolocation' ),
						'description' => esc_html__( 'Choose unit of length for listing distance', 'hivepress-geolocation' ),
						'type'        => 'select',
						'options'     => [
							'miles' => hivepress()->translator->get_string( 'miles' ),
						],
						'placeholder' => hivepress()->translator->get_string( 'km' ),
						'_parent'     => 'geolocation_allow_radius',
						'_order'      => 80,
					],

					'geolocation_generate_regions' => [
						'label'       => esc_html__( 'Regions', 'hivepress-geolocation' ),
						'description' => esc_html__( 'Check this option to create a page for each region.', 'hivepress-geolocation' ),
						'caption'     => esc_html__( 'Generate regions from locations', 'hivepress-geolocation' ),
						'type'        => 'checkbox',
						'_order'      => 90,
					],

					'geolocation_areas'            => [
						'label'       => esc_html__( 'Geographical areas', 'hivepress-geolocation' ),
						'description' => esc_html__( 'Choose geographical areas which are used for location search', 'hivepress-geolocation' ),
						'type'        => 'select',
						'options'     => [
							'country' => hivepress()->translator->get_string( 'country' ),
							'state'   => hivepress()->translator->get_string( 'state' ),
							'county'  => hivepress()->translator->get_string( 'county' ),
							'city'    => hivepress()->translator->get_string( 'city' ),
						],
						'multiple'    => true,
						'_parent'     => 'geolocation_generate_regions',
						'_order'      => 100,
					],

					'geolocation_hide_address'     => [
						'label'   => esc_html__( 'Address', 'hivepress-geolocation' ),
						'caption' => esc_html__( 'Hide the exact address', 'hivepress-geolocation' ),
						'type'    => 'checkbox',
						'_order'  => 110,
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
					'gmaps_public_api_key' => [
						'label'      => hivepress()->translator->get_string( 'public_api_key' ),
						'type'       => 'text',
						'max_length' => 256,
						'_order'     => 10,
					],

					'gmaps_secret_api_key' => [
						'label'      => hivepress()->translator->get_string( 'secret_api_key' ),
						'type'       => 'text',
						'max_length' => 256,
						'_order'     => 20,
					],
				],
			],

			'mapbox' => [
				'title'  => 'Mapbox',
				'_order' => 40,

				'fields' => [
					'mapbox_api_key' => [
						'label'      => hivepress()->translator->get_string( 'public_api_key' ),
						'type'       => 'text',
						'max_length' => 256,
						'_order'     => 10,
					],
				],
			],
		],
	],
];
