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

					'geolocation_countries'        => [
						'label'    => esc_html__( 'Countries', 'hivepress-geolocation' ),
						'type'     => 'select',
						'options'  => 'countries',
						'multiple' => true,
						'_order'   => 20,
					],

					'geolocation_max_zoom'         => [
						'label'     => esc_html__( 'Zoom', 'hivepress-geolocation' ),
						'type'      => 'number',
						'min_value' => 2,
						'max_value' => 20,
						'default'   => 18,
						'required'  => true,
						'_order'    => 30,
					],

					'geolocation_radius'           => [
						'label'     => esc_html__( 'Radius', 'hivepress-geolocation' ),
						'statuses'  => [ esc_html__( 'km', 'hivepress-geolocation' ) ],
						'type'      => 'number',
						'min_value' => 1,
						'default'   => 15,
						'required'  => true,
						'_order'    => 40,
					],

					'geolocation_allow_radius'     => [
						'caption' => esc_html__( 'Allow users to change radius', 'hivepress-geolocation' ),
						'type'    => 'checkbox',
						'_order'  => 50,
					],

					'geolocation_generate_regions' => [
						'label'       => esc_html__( 'Regions', 'hivepress-geolocation' ),
						'description' => esc_html__( 'Check this option to create a page for each region.', 'hivepress-geolocation' ),
						'caption'     => esc_html__( 'Generate regions from locations', 'hivepress-geolocation' ),
						'type'        => 'checkbox',
						'_order'      => 60,
					],

					'geolocation_hide_address'     => [
						'label'   => esc_html__( 'Address', 'hivepress-geolocation' ),
						'caption' => esc_html__( 'Hide the exact address', 'hivepress-geolocation' ),
						'type'    => 'checkbox',
						'_order'  => 70,
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
					'gmaps_api_key' => [
						'label'      => hivepress()->translator->get_string( 'api_key' ),
						'type'       => 'text',
						'max_length' => 256,
						'_order'     => 10,
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
