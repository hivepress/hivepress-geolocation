<?php
/**
 * Geolocation component.
 *
 * @package HivePress\Components
 */

namespace HivePress\Components;

use HivePress\Helpers as hp;
use HivePress\Models;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Geolocation component class.
 *
 * @class Geolocation
 */
final class Geolocation extends Component {

	/**
	 * Class constructor.
	 *
	 * @param array $args Component arguments.
	 */
	public function __construct( $args = [] ) {

		// Add attributes.
		add_filter( 'hivepress/v1/models/listing/attributes', [ $this, 'add_attributes' ] );

		// Enqueue scripts.
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );

		// Alter forms.
		add_filter( 'hivepress/v1/forms/listing_search', [ $this, 'alter_listing_search_form' ], 1000, 2 );
		add_filter( 'hivepress/v1/forms/listing_filter', [ $this, 'alter_listing_search_form' ], 1000, 2 );
		add_filter( 'hivepress/v1/forms/listing_sort', [ $this, 'alter_listing_search_form' ], 1000, 2 );

		// Update models fields.
		add_action( 'hivepress/v1/models/listing/update_location', [ $this, 'update_location' ], 1000 );

		if ( ! is_admin() ) {

			// Alter options.
			add_filter( 'option_hp_geolocation_radius', [ $this, 'alter_geolocation_radius_option' ], 100 );

			// Alter forms.
			add_filter( 'hivepress/v1/forms/listing_filter', [ $this, 'alter_listing_filter_form' ], 100 );

			// Alter templates.
			add_filter( 'hivepress/v1/templates/listing_view_block', [ $this, 'alter_listing_view_block' ] );
			add_filter( 'hivepress/v1/templates/listing_view_page', [ $this, 'alter_listing_view_page' ] );
			add_filter( 'hivepress/v1/templates/listings_view_page', [ $this, 'alter_listings_view_page' ] );

			// Set search query.
			add_action( 'pre_get_posts', [ $this, 'set_search_query' ], 1000 );
		}

		parent::__construct( $args );
	}

	/**
	 * Adds attributes.
	 *
	 * @param array $attributes Attributes.
	 * @return array
	 */
	public function add_attributes( $attributes ) {

		// Get countries.
		$countries = array_filter( (array) get_option( 'hp_geolocation_countries' ) );

		// Get radius.
		$radius = absint( get_option( 'hp_geolocation_radius', 15 ) );

		return array_merge(
			$attributes,
			[
				'latitude'  => [
					'editable'     => true,
					'searchable'   => true,

					'edit_field'   => [
						'type' => 'latitude',
					],

					'search_field' => [
						'type'   => 'latitude',
						'radius' => $radius,
					],
				],

				'longitude' => [
					'editable'     => true,
					'searchable'   => true,

					'edit_field'   => [
						'type' => 'longitude',
					],

					'search_field' => [
						'type'    => 'longitude',
						'radius'  => $radius,
						'_parent' => 'latitude',
					],
				],

				'location'  => [
					'editable'     => true,
					'searchable'   => true,

					'edit_field'   => [
						'label'     => esc_html__( 'Location', 'hivepress-geolocation' ),
						'type'      => 'location',
						'countries' => $countries,
						'required'  => true,
						'_order'    => 25,
					],

					'search_field' => [
						'placeholder' => esc_html__( 'Location', 'hivepress-geolocation' ),
						'type'        => 'location',
						'countries'   => $countries,
						'_order'      => 20,
					],
				],
			]
		);
	}

	/**
	 * Enqueues scripts.
	 */
	public function enqueue_scripts() {
		wp_enqueue_script(
			'google-maps',
			'https://maps.googleapis.com/maps/api/js?' . http_build_query(
				[
					'libraries' => 'places',
					'callback'  => 'hivepress.initGeolocation',
					'key'       => get_option( 'hp_gmaps_api_key' ),
					'language'  => hivepress()->translator->get_language(),
					'region'    => hivepress()->translator->get_region(),
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
								'type'   => 'part',
								'path'   => 'listing/view/listing-location',
								'_order' => 5,
							],
						],
					],
				],
			]
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
								'type'   => 'part',
								'path'   => 'listing/view/listing-location',
								'_order' => 5,
							],
						],
					],

					'page_sidebar'            => [
						'blocks' => [
							'listing_map' => [
								'type'       => 'listing_map',
								'_order'     => 25,

								'attributes' => [
									'class' => [ 'hp-listing__map', 'widget' ],
								],
							],
						],
					],
				],
			]
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
								'type'       => 'listing_map',
								'_order'     => 15,

								'attributes' => [
									'class' => [ 'widget' ],
								],
							],
						],
					],
				],
			]
		);
	}

	/**
	 * Alters listing search form.
	 *
	 * @param array  $form_args Form arguments.
	 * @param object $form Form object.
	 * @return array
	 */
	public function alter_listing_search_form( $form_args, $form ) {
		$form_args['fields']['_region'] = [
			'type'       => 'hidden',

			'attributes' => [
				'data-regions' => true,
			],
		];

		// todo hide in some forms.
		if ( get_option( 'hp_geolocation_allow_radius' ) && hp\get_array_value( $_GET, 'location' ) && ! hp\get_array_value( $_GET, '_region' ) ) {
			$form['fields']['_radius'] = [
				'label'      => esc_html__( 'Radius', 'hivepress-geolocation' ),
				'type'       => 'number',
				'min_value'  => 1,
				'max_value'  => 100,
				'default'    => get_option( 'hp_geolocation_radius' ),
				'_order'     => 15,

				'statuses'   => [
					'optional' => null,
					'km'       => esc_html__( 'km', 'hivepress-geolocation' ),
				],

				'attributes' => [
					'data-component' => 'radius-slider',
				],
			];
		}

		return $form_args;
	}

	/**
	 * Sets search query.
	 *
	 * @param WP_Query $query Query object.
	 * @return string
	 */
	public function set_search_query( $query ) {

		// Check query.
		if ( ! $query->is_main_query() || ! $query->is_search() || $query->get( 'post_type' ) !== 'hp_listing' ) {
			return;
		}

		$region_field = sanitize_key( hp\get_array_value( $_GET, '_region' ) );

		// Check filter.
		if ( ! $region_field ) {
			return;
		}

		// Get meta and taxonomy queries.
		$meta_query = array_filter( (array) $query->get( 'meta_query' ) );
		$tax_query  = array_filter( (array) $query->get( 'tax_query' ) );

		unset( $meta_query['latitude'] );
		unset( $meta_query['longitude'] );

		// Get region ID.
		$region_id = hp\get_first_array_value(
			get_terms(
				[
					'taxonomy'   => 'hp_listing_region',
					'fields'     => 'ids',
					'number'     => 1,
					'hide_empty' => false,
					'meta_key'   => 'hp_place_id',
					'meta_value' => $region_field,
				]
			)
		);

		if ( ! $region_id ) {
			return;
		}

		// Add meta clause.
		$tax_query[] = [
			'taxonomy' => 'hp_listing_region',
			'terms'    => $region_id,
		];

		// Set meta and taxonomy queries.
		$query->set( 'meta_query', $meta_query );
		$query->set( 'tax_query', $tax_query );
	}

	/**
	 * Updates listing location.
	 *
	 * @param int $listing_id Listing ID.
	 */
	public function update_location( $listing_id ) {

		// Get listing.
		$listing = Models\Listing::query()->get_by_id( $listing_id );

		if ( ! $listing ) {
			return;
		}

		// Get coordinates.
		$longitude = $listing->get_longitude();
		$latitude  = $listing->get_latitude();

		if ( ! $longitude || ! $latitude ) {
			return;
		}

		// Get map provider.
		$provider = get_option( 'hp_geolocation_provider' );

		// Get location types.
		$types = [
			'locality',
			'administrative_area_level_2',
			'administrative_area_level_1',
			'country',
		];

		// Get request URL.
		$request_url = null;

		if ( 'mapbox' === $provider ) {
			$request_url = 'https://api.mapbox.com/geocoding/v5/mapbox.places/' . rawurlencode( $longitude . ',' . $latitude ) . '.json?' . http_build_query(
				[
					'access_token' => get_option( 'hp_mapbox_api_key' ),
				]
			);
		} else {
			$request_url = 'https://maps.googleapis.com/maps/api/geocode/json?' . http_build_query(
				[
					'latlng'      => $latitude . ',' . $longitude,
					'key'         => get_option( 'hp_gmaps_api_key' ),
					'language'    => hivepress()->translator->get_language(),
					'result_type' => implode( '|', $types ),
				]
			);
		}

		// Get API response.
		$response = json_decode( wp_remote_retrieve_body( wp_remote_get( $request_url ) ), true );

		if ( ! $response || isset( $response['error_message'] ) ) {
			return;
		}

		// Get region names.
		$regions = [];

		if ( 'mapbox' === $provider ) {
			// todo.
		} else {
			foreach ( $response['results'] as $result ) {
				$regions[ $result['place_id'] ] = $result['address_components'][0]['long_name'];
			}
		}

		// Get region ID.
		$region_id = null;

		foreach ( array_reverse( $regions ) as $region_key => $region ) {

			// Get region.
			$region_args = term_exists( $region, 'hp_listing_region', $region_id );

			if ( ! $region_args ) {

				// Add region.
				$region_args = wp_insert_term(
					$region,
					'hp_listing_region',
					[
						'parent' => $region_id,
					]
				);

				if ( is_wp_error( $region_args ) ) {
					break;
				}

				update_term_meta( $region_args['term_id'], 'hp_place_id', $region_key );
			}

			$region_id = (int) $region_args['term_id'];
		}

		if ( ! $region_id ) {
			return;
		}

		// Set region ID.
		wp_set_object_terms( $listing->get_id(), $region_id, 'hp_listing_region' );
	}

	/**
	 * Alters geolocation radius option.
	 *
	 * @param array $value Form arguments.
	 * @return array
	 */
	public function alter_geolocation_radius_option( $value ) {
		$radius = absint( hp\get_array_value( $_GET, '_radius' ) );

		if ( $radius >= 1 && $radius <= 100 ) {
			$value = $radius;
		}

		return $value;
	}
}
