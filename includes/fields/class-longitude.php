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
	 * Longitude radius.
	 *
	 * @var int
	 */
	protected $radius = 15;

	/**
	 * Class initializer.
	 *
	 * @param array $meta Field meta.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'label'    => null,
				'sortable' => false,
			],
			$meta
		);

		parent::init( $meta );
	}

	/**
	 * Class constructor.
	 *
	 * @param array $args Field arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			$args,
			[
				'display_type' => 'hidden',
				'decimals'     => 6,
				'min_value'    => -180,
				'max_value'    => 180,
			]
		);

		parent::__construct( $args );
	}

	/**
	 * Bootstraps field properties.
	 */
	protected function boot() {

		// Set attributes.
		$this->attributes = hp\merge_arrays(
			$this->attributes,
			[
				'data-coordinate' => 'lng',
			]
		);

		Field::boot();
	}

	/**
	 * Adds field filter.
	 */
	protected function add_filter() {
		parent::add_filter();

		// Get divisor.
		$divisor = 0;

		if ( ! is_null( $this->parent_value ) ) {
			$divisor = 111.320 * cos( deg2rad( $this->parent_value ) );
		}

		// Get radius.
		$radius = 0;

		if ( $divisor ) {
			$radius = round( $this->radius / $divisor, 6 );
		}

		// Set filter.
		$this->filter = array_merge(
			$this->filter,
			[
				'operator' => 'BETWEEN',
				'value'    => [ $this->value - $radius, $this->value + $radius ],
			]
		);
	}
}
