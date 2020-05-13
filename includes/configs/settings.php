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
		'_order'   => 15,

		'sections' => [
			'search' => [
				'title'  => hivepress()->translator->get_string( 'search_noun' ),
				'_order' => 10,

				'fields' => [
					'geolocation_radius'    => [
						'label'     => esc_html__( 'Radius', 'hivepress-geolocation' ),
						'type'      => 'number',
						'min_value' => 1,
						'default'   => 15,
						'required'  => true,
						'_order'    => 10,
					],

					'geolocation_countries' => [
						'label'    => esc_html__( 'Countries', 'hivepress-geolocation' ),
						'type'     => 'select',
						'options'  => 'countries',
						'multiple' => true,
						'_order'   => 20,
					],
				],
			],
		],
	],

	'integrations' => [
		'sections' => [
			'gmaps' => [
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
		],
	],
];
