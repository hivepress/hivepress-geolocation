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
	 * Field type.
	 *
	 * @var string
	 */
	protected static $type;

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
	 * Bootstraps field properties.
	 */
	protected function bootstrap() {
		$this->attributes = hp\merge_arrays(
			[
				'data-coordinate' => 'lat',
			],
			$this->attributes
		);

		parent::bootstrap();
	}

	/**
	 * Renders field HTML.
	 *
	 * @return string
	 */
	public function render() {
		// todo.
		$output = '<div ' . hp\html_attributes( $this->attributes ) . '>';

		// Render field.
		$output .= ( new Hidden( array_merge( $this->args, [ 'default' => $this->value ] ) ) )->render();

		$output .= '</div>';

		return $output;
	}
}
