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
				'version' => '1.7.0',
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

		// Forms.
		'forms'     => [
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
						'order'      => 20,
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
						'order'      => 20,
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
		'templates' => [
			'archive_listing' => [
				'areas' => [
					'summary' => [
						'todo' => [
							'path'  => 'todo',
							'order' => 15,
						],
					],
				],
			],

			'single_listing'  => [
				'areas' => [
					'summary' => [
						'todo' => [
							'path'  => 'todo2',
							'order' => 15,
						],
					],
				],
			],

			'listing_archive' => [
				'areas' => [
					'sidebar' => [
						'todo' => [
							'path'  => 'todo3',
							'order' => 15,
						],
					],
				],
			],
		],
	],
];
