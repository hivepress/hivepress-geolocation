<?php
/**
 * Geolocation component.
 *
 * @package HivePress\Components
 */

namespace HivePress\Components;

use HivePress\Helpers as hp;
use HivePress\Models;
use HivePress\Emails;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Geolocation component class.
 *
 * @class Geolocation
 */
final class Geolocation {

	/**
	 * Class constructor.
	 */
	public function __construct() {
		// todo remove.
		add_filter( 'hivepress/v1/attributes', [ $this, 'todoremove' ] );

		if ( ! is_admin() ) {

			// Enqueue scripts.
			add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		}
	}

	public function todoremove( $attributes ) {
		$attributes['location'] = [
			'model'        => 'listing',
			'editable'     => true,
			'searchable'   => true,
			'edit_field'   => [
				'label' => esc_html__( 'Location', 'hivepress-geolocation' ),
				'type'  => 'location',
				'order' => 25,
			],
			'search_field' => [
				'label' => 'Location',
				'type'  => 'location',
				'order' => 20,
			],
		];

		// $attributes['latitude'] = [
		// 'model'        => 'listing',
		// 'editable'     => true,
		// 'searchable'   => true,
		// 'edit_field'   => [
		// 'label' => 'Location',
		// 'type'  => 'location',
		// 'order' => 20,
		// ],
		// 'search_field' => [
		// 'label' => 'Location',
		// 'type'  => 'location',
		// 'order' => 20,
		// ],
		// ];
		return $attributes;
	}

	/**
	 * Enqueues scripts.
	 */
	public function enqueue_scripts() {
		if ( get_option( 'hp_gmaps_api_key' ) ) {
			wp_enqueue_script(
				'google-maps',
				'https://maps.googleapis.com/maps/api/js?' . http_build_query(
					[
						'libraries' => 'places',
						'callback'  => 'todo',
						'key'       => get_option( 'hp_gmaps_api_key' ),
					]
				),
				[],
				null,
				true
			);

			wp_script_add_data( 'google-maps', 'async', true );
			wp_script_add_data( 'google-maps', 'defer', true );
		}
	}
}
