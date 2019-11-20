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
						'label'      => hivepress()->translator->get_string( 'api_key' ),
						'type'       => 'text',
						'max_length' => 256,
						'order'      => 10,
					],
				],
			],
		],
	],
];
