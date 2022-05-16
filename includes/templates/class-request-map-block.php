<?php
/**
 * Request map block template.
 *
 * @template request_map_block
 * @description Request block in map context.
 * @package HivePress\Templates
 */

namespace HivePress\Templates;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Request map block template class.
 *
 * @class Request_Map_Block
 */
class Request_Map_Block extends Template {

	/**
	 * Class constructor.
	 *
	 * @param array $args Template arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_trees(
			[
				'blocks' => [
					'request_container' => [
						'type'       => 'container',
						'_order'     => 10,

						'attributes' => [
							'class' => [ 'hp-request', 'hp-request--map-block' ],
						],

						'blocks'     => [
							'request_title' => [
								'type'       => 'container',
								'tag'        => 'h5',
								'_order'     => 10,

								'attributes' => [
									'class' => [ 'hp-request__title' ],
								],

								'blocks'     => [
									'request_title_text' => [
										'type'   => 'part',
										'path'   => 'request/view/block/request-title',
										'_order' => 10,
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
