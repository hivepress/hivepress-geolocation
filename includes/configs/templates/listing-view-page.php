<?php
/**
 * Listing view page template.
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
				'columns' => [
					'blocks' => [
						'content' => [
							'blocks' => [
								'details_primary' => [
									'blocks' => [
										'location' => [
											'type'      => 'element',
											'file_path' => 'listing/view/location',
											'order'     => 5,
										],
									],
								],
							],
						],

						'sidebar' => [
							'blocks' => [
								'map' => [
									'type'       => 'map',
									'order'      => 25,

									'attributes' => [
										'class' => [ 'hp-listing__map', 'widget' ],
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
