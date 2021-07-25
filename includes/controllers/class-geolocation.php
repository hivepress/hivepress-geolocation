<?php
/**
 * Geolocation controller.
 *
 * @package HivePress\Controllers
 */

namespace HivePress\Controllers;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Geolocation controller class.
 *
 * @class Geolocation
 */
final class Geolocation extends Controller {

	/**
	 * Class constructor.
	 *
	 * @param array $args Controller arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'routes' => [
					'location_view_page' => [
						'url' => [ $this, 'get_location_view_url' ],
					],
				],
			],
			$args
		);

		parent::__construct( $args );
	}

	/**
	 * Gets location view URL.
	 *
	 * @param array $params URL parameters.
	 * @return string
	 */
	public function get_location_view_url( $params ) {
		$url = '#';

		if ( ! get_option( 'hp_geolocation_hide_address' ) ) {
			$url = add_query_arg(
				[
					'api'   => 1,
					'query' => hp\get_array_value( $params, 'latitude' ) . ',' . hp\get_array_value( $params, 'longitude' ),
				],
				'https://www.google.com/maps/search/'
			);
		}

		return $url;
	}
}
