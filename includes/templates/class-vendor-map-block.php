<?php
/**
 * Vendor map block template.
 *
 * @package HivePress\Templates
 */

namespace HivePress\Templates;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Vendor map block template class.
 *
 * @class Vendor_Map_Block
 */
class Vendor_Map_Block extends Template {

	/**
	 * Class constructor.
	 *
	 * @param array $args Template arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_trees(
			[
				'blocks' => [
					'vendor_container' => [
						'type'       => 'container',
						'_order'     => 10,

						'attributes' => [
							'class' => [ 'hp-vendor', 'hp-vendor--map-block' ],
						],

						'blocks'     => [
							'vendor_name' => [
								'type'       => 'container',
								'tag'        => 'h5',
								'_order'     => 10,

								'attributes' => [
									'class' => [ 'hp-vendor__name' ],
								],

								'blocks'     => [
									'vendor_name_text' => [
										'type'   => 'part',
										'path'   => 'vendor/view/block/vendor-name',
										'_order' => 10,
									],

									'vendor_verified_badge' => [
										'type'   => 'part',
										'path'   => 'vendor/view/vendor-verified-badge',
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
