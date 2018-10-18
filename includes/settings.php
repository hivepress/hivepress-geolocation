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

		// Meta boxes.
		'meta_boxes' => [
			'attributes' => [
				'fields' => [
					'location'  => [
						'name'  => esc_html__( 'Location', 'hivepress-geolocation' ),
						'type'  => 'location',
						'order' => 20,
					],

					'latitude'  => [
						'type'       => 'hidden',
						'attributes' => [
							'data-type' => 'lat',
						],
					],

					'longitude' => [
						'type'       => 'hidden',
						'attributes' => [
							'data-type' => 'lng',
						],
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
						'order'       => 20,
					],

					'latitude'  => [
						'type'       => 'hidden',
						'attributes' => [
							'data-type' => 'lat',
						],
					],

					'longitude' => [
						'type'       => 'hidden',
						'attributes' => [
							'data-type' => 'lng',
						],
					],
				],
			],

			'submit' => [
				'fields' => [
					'location'  => [
						'name'  => esc_html__( 'Location', 'hivepress-geolocation' ),
						'type'  => 'location',
						'order' => 20,
					],

					'latitude'  => [
						'type'       => 'hidden',
						'attributes' => [
							'data-type' => 'lat',
						],
					],

					'longitude' => [
						'type'       => 'hidden',
						'attributes' => [
							'data-type' => 'lng',
						],
					],
				],
			],

			'update' => [
				'fields' => [
					'location'  => [
						'name'  => esc_html__( 'Location', 'hivepress-geolocation' ),
						'type'  => 'location',
						'order' => 20,
					],

					'latitude'  => [
						'type'       => 'hidden',
						'attributes' => [
							'data-type' => 'lat',
						],
					],

					'longitude' => [
						'type'       => 'hidden',
						'attributes' => [
							'data-type' => 'lng',
						],
					],
				],
			],
		],
	],
];
