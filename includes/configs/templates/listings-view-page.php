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
		'page_container' => [
			'blocks' => [
				'page_columns' => [
					'blocks' => [
						'page_sidebar' => [
							'blocks' => [
								'listing_map' => [
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
		],
	],
];
