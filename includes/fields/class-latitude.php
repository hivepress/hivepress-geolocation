<?php
/**
 * Latitude field.
 *
 * @package HivePress\Fields
 */

namespace HivePress\Fields;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Latitude field class.
 *
 * @class Latitude
 */
class Latitude extends Number {

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
				'min_value' => -90,
				'max_value' => 90,
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
					'attributes' => [ 'data-coordinate' => 'lat' ],
				]
			)
		) )->render();
	}
}
