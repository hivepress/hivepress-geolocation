<?php
/**
 * Contains plugin settings.
 *
 * @package HivePress\Geolocation
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

$settings = [

	// Geolocation component.
	'geolocation' => [

		// Options.
		'options' => [
			'integrations' => [
				'sections' => [
					'gmaps' => [
						'name'   => 'Google Maps',
						'order'  => 20,

						'fields' => [
							'gmaps_api_key' => [
								'name'  => esc_html__( 'API Key', 'hivepress-geolocation' ),
								'type'  => 'text',
								'order' => 10,
							],
						],
					],
				],
			],
		],

		// Styles.
		'styles'  => [
			'frontend' => [
				'handle'  => 'hp-geolocation',
				'src'     => HP_GEOLOCATION_URL . '/assets/css/frontend.min.css',
				'version' => HP_GEOLOCATION_VERSION,
			],
		],

		// Scripts.
		'scripts' => [
			'geocomplete' => [
				'handle'  => 'geocomplete',
				'src'     => HP_GEOLOCATION_URL . '/assets/js/jquery.geocomplete.min.js',
				'version' => HP_GEOLOCATION_VERSION,
			],

			'frontend'    => [
				'handle'  => 'hp-geolocation',
				'src'     => HP_GEOLOCATION_URL . '/assets/js/frontend.min.js',
				'deps'    => [ 'hp-core', 'geocomplete' ],
				'version' => HP_GEOLOCATION_VERSION,
			],
		],
	],

	// Listing component.
	'listing'     => [

		// Meta boxes.
		'meta_boxes' => [
			'attributes' => [
				'fields' => [
					'location' => [
						'name'       => esc_html__( 'Location', 'hivepress-geolocation' ),
						'type'       => 'text',
						'max_length' => 256,
						'order'      => 10,
					],
				],
			],
		],

		// Forms.
		'forms'      => [
			'search' => [
				'fields' => [
					'location'  => [
						'placeholder' => esc_html__( 'Location', 'hivepress-geolocation' ),
						'type'        => 'location',
						'max_length'  => 256,
						'order'       => 20,
					],

					'latitude'  => [
						'type' => 'latitude',
					],

					'longitude' => [
						'type' => 'longitude',
					],
				],
			],

			'submit' => [
				'fields' => [
					'location'  => [
						'name'       => esc_html__( 'Location', 'hivepress-geolocation' ),
						'type'       => 'location',
						'max_length' => 256,
						'required'   => true,
						'order'      => 15,
					],

					'latitude'  => [
						'type' => 'latitude',
					],

					'longitude' => [
						'type' => 'longitude',
					],
				],
			],

			'update' => [
				'fields' => [
					'location'  => [
						'name'       => esc_html__( 'Location', 'hivepress-geolocation' ),
						'type'       => 'location',
						'max_length' => 256,
						'required'   => true,
						'order'      => 15,
					],

					'latitude'  => [
						'type' => 'latitude',
					],

					'longitude' => [
						'type' => 'longitude',
					],
				],
			],
		],

		// Templates.
		'templates'  => [
			'archive_listing' => [
				'areas' => [
					'summary' => [
						'location' => [
							'path'  => 'listing/parts/location',
							'order' => 15,
						],
					],
				],
			],

			'single_listing'  => [
				'areas' => [
					'summary' => [
						'location' => [
							'path'  => 'listing/parts/location',
							'order' => 15,
						],
					],

					'sidebar' => [
						'map' => [
							'path'  => 'geolocation/parts/map',
							'order' => 25,
						],
					],
				],
			],

			'listing_archive' => [
				'areas' => [
					'sidebar' => [
						'map' => [
							'path'  => 'geolocation/parts/map',
							'order' => 25,
						],
					],
				],
			],
		],
	],
];
