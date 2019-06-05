<?php
/**
 * Geolocation component.
 *
 * @package HivePress\Components
 */

namespace HivePress\Components;

use HivePress\Helpers as hp;
use HivePress\Fields;

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

		// Add attributes.
		add_filter( 'hivepress/v1/attributes', [ $this, 'add_attributes' ] );

		// Add search fields.
		add_filter( 'hivepress/v1/forms/listing_search', [ $this, 'add_search_fields' ] );
		add_filter( 'hivepress/v1/forms/listing_filter', [ $this, 'add_search_fields' ] );
		add_filter( 'hivepress/v1/forms/listing_sort', [ $this, 'add_search_fields' ] );

		// todo
		add_filter( 'hivepress/v1/templates/listing_view_block', [ $this, 'todo1' ] );
		add_filter( 'hivepress/v1/templates/listing_view_page', [ $this, 'todo2' ] );
		add_filter( 'hivepress/v1/templates/listings_view_page', [ $this, 'todo3' ] );

		if ( ! is_admin() ) {

			// Set search query.
			add_action( 'pre_get_posts', [ $this, 'set_search_query' ] );

			// Enqueue scripts.
			add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		}
	}

	// todo
	public function todo1( $template ) {
		return hp\merge_trees(
			$template,
			[
				'blocks' => [
					'listing_details_primary' => [
						'blocks' => [
							'listing_location' => [
								'type'     => 'element',
								'filepath' => 'listing/view/location',
								'order'    => 5,
							],
						],
					],
				],
			],
			'blocks'
		);
	}

	// todo
	public function todo2( $template ) {
		return hp\merge_trees(
			$template,
			[
				'blocks' => [
					'listing_details_primary' => [
						'blocks' => [
							'listing_location' => [
								'type'     => 'element',
								'filepath' => 'listing/view/location',
								'order'    => 5,
							],
						],
					],

					'page_sidebar'            => [
						'blocks' => [
							'listing_map' => [
								'type'       => 'map',
								'order'      => 25,

								'attributes' => [
									'class' => [ 'hp-listing__map', 'widget' ],
								],
							],
						],
					],
				],
			],
			'blocks'
		);
	}

	// todo
	public function todo3( $template ) {
		return hp\merge_trees(
			$template,
			[
				'blocks' => [
					'page_sidebar' => [
						'blocks' => [
							'listing_map' => [
								'type'       => 'map',
								'order'      => 15,

								'attributes' => [
									'class' => [ 'widget' ],
								],
							],
						],
					],
				],
			],
			'blocks'
		);
	}

	/**
	 * Adds attributes.
	 *
	 * @param array $attributes Attributes.
	 * @return array
	 */
	public function add_attributes( $attributes ) {
		if ( get_option( 'hp_gmaps_api_key' ) ) {
			$attributes = array_merge(
				$attributes,
				[
					'location'  => [
						'model'      => 'listing',
						'editable'   => true,

						'edit_field' => [
							'label'    => esc_html__( 'Location', 'hivepress-geolocation' ),
							'type'     => 'location',
							'required' => true,
							'order'    => 25,
						],
					],

					'latitude'  => [
						'model'      => 'listing',
						'editable'   => true,

						'edit_field' => [
							'type' => 'latitude',
						],
					],

					'longitude' => [
						'model'      => 'listing',
						'editable'   => true,

						'edit_field' => [
							'type' => 'longitude',
						],
					],
				]
			);
		}

		return $attributes;
	}

	/**
	 * Adds search fields.
	 *
	 * @param array $form Form arguments.
	 * @return array
	 */
	public function add_search_fields( $form ) {
		if ( get_option( 'hp_gmaps_api_key' ) ) {
			$fields = [
				'location'  => [
					'label' => esc_html__( 'Location', 'hivepress-geolocation' ),
					'type'  => 'location',
					'order' => 20,
				],

				'latitude'  => [
					'type' => 'latitude',
				],

				'longitude' => [
					'type' => 'longitude',
				],
			];

			if ( 'listing_search' !== $form['name'] ) {
				foreach ( $fields as $field_name => $field_args ) {
					$fields[ $field_name ]['type'] = 'hidden';
				}
			}

			$form['fields'] = array_merge( $form['fields'], $fields );
		}

		return $form;
	}

	/**
	 * Sets search query.
	 *
	 * @param WP_Query $query Search query.
	 */
	public function set_search_query( $query ) {
		if ( get_option( 'hp_gmaps_api_key' ) && $query->is_main_query() && is_post_type_archive( 'hp_listing' ) && $query->is_search ) {

			// Get coordinates.
			$latitude_field  = new Fields\Latitude();
			$longitude_field = new Fields\Longitude();

			$latitude_field->set_value( hp\get_array_value( $_GET, 'latitude' ) );
			$longitude_field->set_value( hp\get_array_value( $_GET, 'longitude' ) );

			$latitude  = $latitude_field->get_value();
			$longitude = $longitude_field->get_value();

			if ( ! is_null( $latitude ) && ! is_null( $longitude ) ) {

				// Calculate radiuses.
				$radius           = 15;
				$latitude_radius  = $radius / 110.574;
				$longitude_radius = $radius / ( 111.320 * cos( deg2rad( $latitude ) ) );

				// Set meta query.
				$query->set(
					'meta_query',
					array_merge(
						(array) $query->get( 'meta_query' ),
						[
							[
								'key'     => 'hp_latitude',
								'value'   => [ $latitude - $latitude_radius, $latitude + $latitude_radius ],
								'compare' => 'BETWEEN',
								'type'    => 'DECIMAL(8, 6)',
							],
							[
								'key'     => 'hp_longitude',
								'value'   => [ $longitude - $longitude_radius, $longitude + $longitude_radius ],
								'compare' => 'BETWEEN',
								'type'    => 'DECIMAL(9, 6)',
							],
						]
					)
				);
			}
		}
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
						'callback'  => 'hivepress.initGeolocation',
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
