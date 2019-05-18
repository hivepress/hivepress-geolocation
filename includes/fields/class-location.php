<?php
/**
 * Location field.
 *
 * @package HivePress\Fields
 */

namespace HivePress\Fields;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Location field class.
 *
 * @class Location
 */
class Location extends Text {

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
				'placeholder' => '',
				'max_length'  => 256,
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
			$this->attributes,
			[
				'data-component' => 'location',
			]
		);

		Field::bootstrap();
	}

	/**
	 * Renders field HTML.
	 *
	 * @return string
	 */
	public function render() {
		$output = '<div ' . hp\html_attributes( $this->attributes ) . '>';

		// Render field.
		$output .= ( new Text( array_merge( $this->args, [ 'default' => $this->value ] ) ) )->render();

		// Render button.
		$output .= '<a href="#" title="' . esc_attr__( 'Locate Me', 'hivepress-geolocation' ) . '"><i class="hp-icon fas fa-location-arrow"></i></a>';

		$output .= '</div>';

		return $output;
	}
}
