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

		// Check API key.
		if ( ! get_option( 'hp_gmaps_api_key' ) ) {
			return;
		}

		// Add attributes.
		add_filter( 'hivepress/v1/models/listing/attributes', [ $this, 'add_attributes' ] );

		// Add search fields.
		add_filter( 'hivepress/v1/forms/listing_search', [ $this, 'add_search_fields' ] );
		add_filter( 'hivepress/v1/forms/listing_filter', [ $this, 'add_search_fields' ] );
		add_filter( 'hivepress/v1/forms/listing_sort', [ $this, 'add_search_fields' ] );

		// Enqueue scripts.
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );

		if ( ! is_admin() ) {

			// Set search query.
			add_action( 'pre_get_posts', [ $this, 'set_search_query' ] );

			// Alter templates.
			add_filter( 'hivepress/v1/templates/listing_view_block', [ $this, 'alter_listing_view_block' ] );
			add_filter( 'hivepress/v1/templates/listing_view_page', [ $this, 'alter_listing_view_page' ] );
			add_filter( 'hivepress/v1/templates/listings_view_page', [ $this, 'alter_listings_view_page' ] );
		}
	}

	/**
	 * Adds attributes.
	 *
	 * @param array $attributes Attributes.
	 * @return array
	 */
	public function add_attributes( $attributes ) {
		return array_merge(
			$attributes,
			[
				'location'  => [
					'editable'   => true,

					'edit_field' => [
						'label'    => esc_html__( 'Location', 'hivepress-geolocation' ),
						'type'     => 'location',
						'required' => true,
						'order'    => 25,
					],
				],

				'latitude'  => [
					'editable'   => true,

					'edit_field' => [
						'type' => 'latitude',
					],
				],

				'longitude' => [
					'editable'   => true,

					'edit_field' => [
						'type' => 'longitude',
					],
				],
			]
		);
	}

	/**
	 * Adds search fields.
	 *
	 * @param array $form Form arguments.
	 * @return array
	 */
	public function add_search_fields( $form ) {
		$fields = [
			'location'  => [
				'placeholder' => esc_html__( 'Location', 'hivepress-geolocation' ),
				'type'        => 'location',
				'order'       => 20,
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

		return $form;
	}

	/**
	 * Sets search query.
	 *
	 * @param WP_Query $query Search query.
	 */
	public function set_search_query( $query ) {
		if ( $query->is_main_query() && is_post_type_archive( 'hp_listing' ) && $query->is_search ) {

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

		// Get locale.
		$locale = explode( '_', get_locale() );

		// Enqueue script.
		wp_enqueue_script(
			'google-maps',
			'https://maps.googleapis.com/maps/api/js?' . http_build_query(
				[
					'libraries' => 'places',
					'callback'  => 'hivepress.initGeolocation',
					'key'       => get_option( 'hp_gmaps_api_key' ),
					'language'  => reset( $locale ),
					'region'    => end( $locale ),
				]
			),
			[],
			null,
			true
		);

		wp_script_add_data( 'google-maps', 'async', true );
		wp_script_add_data( 'google-maps', 'defer', true );
	}

	/**
	 * Alters listing view block.
	 *
	 * @param array $template Template arguments.
	 * @return array
	 */
	public function alter_listing_view_block( $template ) {
		return hp\merge_trees(
			$template,
			[
				'blocks' => [
					'listing_details_primary' => [
						'blocks' => [
							'listing_location' => [
								'type'     => 'element',
								'filepath' => 'listing/view/listing-location',
								'order'    => 5,
							],
						],
					],
				],
			],
			'blocks'
		);
	}

	/**
	 * Alters listing view page.
	 *
	 * @param array $template Template arguments.
	 * @return array
	 */
	public function alter_listing_view_page( $template ) {
		return hp\merge_trees(
			$template,
			[
				'blocks' => [
					'listing_details_primary' => [
						'blocks' => [
							'listing_location' => [
								'type'     => 'element',
								'filepath' => 'listing/view/listing-location',
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

	/**
	 * Alters listings view page.
	 *
	 * @param array $template Template arguments.
	 * @return array
	 */
	public function alter_listings_view_page( $template ) {
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
}
