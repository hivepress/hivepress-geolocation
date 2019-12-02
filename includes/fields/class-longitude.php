<?php
/**
 * Longitude field.
 *
 * @package HivePress\Fields
 */

namespace HivePress\Fields;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Longitude field class.
 *
 * @class Longitude
 */
class Longitude extends Number {

	/**
	 * Field title.
	 *
	 * @var string
	 */
	protected static $title;

	/**
	 * Class initializer.
	 *
	 * @param array $args Field arguments.
	 */
	public static function init( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'title' => null,
			],
			$args
		);

		parent::init( $args );
	}

	/**
	 * Class constructor.
	 *
	 * @param array $args Field arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'decimals'  => 6,
				'min_value' => -180,
				'max_value' => 180,
			],
			$args
		);

		parent::__construct( $args );
	}

	/**
	 * Renders field HTML.
	 *
	 * @return string
	 */
	public function render() {
		return ( new Hidden(
			array_merge(
				$this->args,
				[
					'default'    => $this->value,
					'attributes' => [ 'data-coordinate' => 'lng' ],
				]
			)
		) )->render();
	}
}
