<?php
/**
 * Listing view block template.
 *
 * @package HivePress\Configs\Templates
 */

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

return [
	'blocks' => [
		'container' => [
			'blocks' => [
				'content' => [
					'blocks' => [
						'details_primary' => [
							'blocks' => [
								'location' => [
									'type'      => 'element',
									'filepath' => 'listing/view/location',
									'order'     => 5,
								],
							],
						],
					],
				],
			],
		],
	],
];
