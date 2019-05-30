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
		'page_container' => [
			'blocks' => [
				'page_content' => [
					'blocks' => [
						'listing_details_primary' => [
							'blocks' => [
								'listing_location' => [
									'type'     => 'element',
									'filepath' => 'listing/view/location',
									'order'    => 5,
								],
							],
						],
					],
				],
			],
		],
	],
];
