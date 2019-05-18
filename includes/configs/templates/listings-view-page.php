<?php
/**
 * Listings view page template.
 *
 * @package HivePress\Configs\Templates
 */

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

return [
	'blocks' => [
		'columns' => [
			'blocks' => [
				'sidebar' => [
					'blocks' => [
						'map' => [
							'type'       => 'map',
							'order'      => 15,

							'attributes' => [
								'class' => [ 'widget' ],
							],
						],
					],
				],
			],
		],
	],
];
