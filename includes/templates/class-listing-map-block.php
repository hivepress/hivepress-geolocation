<?php
/**
 * Listing map block template.
 *
 * @template listing_map_block
 * @description Listing block in map context.
 * @package HivePress\Templates
 */

namespace HivePress\Templates;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Listing map block template class.
 *
 * @class Listing_Map_Block
 */
class Listing_Map_Block extends Template {

	/**
	 * Class constructor.
	 *
	 * @param array $args Template arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_trees(
			[
				'blocks' => [
					'listing_container' => [
						'type'       => 'container',
						'_order'     => 10,

						'attributes' => [
							'class' => [ 'hp-listing', 'hp-listing--map-block' ],
						],

						'blocks'     => [
							'listing_title' => [
								'type'       => 'container',
								'tag'        => 'h5',
								'_order'     => 10,

								'attributes' => [
									'class' => [ 'hp-listing__title' ],
								],

								'blocks'     => [
									'listing_title_text' => [
										'type'   => 'part',
										'path'   => 'listing/view/block/listing-title',
										'_order' => 10,
									],

									'listing_verified_badge' => [
										'type'   => 'part',
										'path'   => 'listing/view/listing-verified-badge',
										'_order' => 20,
									],
								],
							],
						],
					],
				],
			],
			$args
		);

		parent::__construct( $args );
	}
}
