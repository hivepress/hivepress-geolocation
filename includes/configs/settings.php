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
	'integrations' => [
		'sections' => [
			'gmaps' => [
				'title'  => 'Google Maps',
				'order'  => 20,

				'fields' => [
					'gmaps_api_key' => [
						'label'      => esc_html__( 'API Key', 'hivepress-geolocation' ),
						'type'       => 'text',
						'max_length' => 256,
						'order'      => 10,
					],
				],
			],
		],
	],
];
