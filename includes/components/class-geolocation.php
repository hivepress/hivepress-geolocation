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

		// Delete empty regions.
		add_action( 'hivepress/v1/events/hourly', [ $this, 'delete_empty_regions' ] );

		// Add attributes.
		add_filter( 'hivepress/v1/models/listing/attributes', [ $this, 'add_attributes' ] );

		// Add taxonomies.
		add_filter( 'hivepress/v1/taxonomies', [ $this, 'add_taxonomies' ] );

		// Enqueue scripts.
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );

		add_filter( 'hivepress/v1/scripts', [ $this, 'alter_scripts' ] );

		// Update location.
		add_action( 'hivepress/v1/models/listing/update_longitude', [ $this, 'update_location' ] );

		if ( ! is_admin() ) {

			// Set search query.
			add_action( 'hivepress/v1/models/listing/search', [ $this, 'set_search_query' ] );

			// Set search order.
			add_filter( 'posts_orderby', [ $this, 'set_search_order' ], 100, 2 );

			// Set search radius.
			add_filter( 'option_hp_geolocation_radius', [ $this, 'set_search_radius' ] );

			// Alter forms.
			add_filter( 'hivepress/v1/forms/listing_search', [ $this, 'alter_listing_search_form' ], 200 );
			add_filter( 'hivepress/v1/forms/listing_filter', [ $this, 'alter_listing_search_form' ], 200 );
			add_filter( 'hivepress/v1/forms/listing_sort', [ $this, 'alter_listing_search_form' ], 200 );

			add_filter( 'hivepress/v1/forms/listing_sort', [ $this, 'alter_listing_sort_form' ], 200 );

			// Alter templates.
			add_filter( 'hivepress/v1/templates/listing_view_block', [ $this, 'alter_listing_view_block' ] );
			add_filter( 'hivepress/v1/templates/listing_view_page', [ $this, 'alter_listing_view_page' ] );
			add_filter( 'hivepress/v1/templates/listings_view_page', [ $this, 'alter_listings_view_page' ] );
		}

		parent::__construct( $args );
	}

	/**
	 * Adds listing attributes.
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
			]
		);
	}

	/**
	 * Adds taxonomies.
	 *
	 * @param array $taxonomies Taxonomies.
	 * @return array
	 */
	public function add_taxonomies( $taxonomies ) {
		if ( get_option( 'hp_geolocation_generate_regions' ) ) {
			$taxonomies['listing_region'] = [
				'post_type'          => [ 'listing' ],
				'hierarchical'       => true,
				'show_in_quick_edit' => false,
				'meta_box_cb'        => false,
				'rewrite'            => [ 'slug' => 'listing-region' ],

				'labels'             => [
					'name'          => esc_html__( 'Regions', 'hivepress-geolocation' ),
					'singular_name' => esc_html__( 'Region', 'hivepress-geolocation' ),
					'add_new_item'  => esc_html__( 'Add Region', 'hivepress-geolocation' ),
					'edit_item'     => esc_html__( 'Edit Region', 'hivepress-geolocation' ),
					'update_item'   => esc_html__( 'Update Region', 'hivepress-geolocation' ),
					'view_item'     => esc_html__( 'View Region', 'hivepress-geolocation' ),
					'parent_item'   => esc_html__( 'Parent Region', 'hivepress-geolocation' ),
					'search_items'  => esc_html__( 'Search Regions', 'hivepress-geolocation' ),
					'not_found'     => esc_html__( 'No regions found', 'hivepress-geolocation' ),
				],
			];
		}

		return $taxonomies;
	}

	/**
	 * Enqueues scripts.
	 */
	public function enqueue_scripts() {
		if ( get_option( 'hp_geolocation_provider' ) === 'mapbox' ) {
			wp_enqueue_style( 'mapbox', 'https://api.mapbox.com/mapbox-gl-js/v2.7.0/mapbox-gl.css' );
			wp_enqueue_style( 'mapbox-geocoder', 'https://api.mapbox.com/mapbox-gl-js/plugins/mapbox-gl-geocoder/v5.0.0/mapbox-gl-geocoder.css' );

			wp_enqueue_script(
				'mapbox',
				'https://api.mapbox.com/mapbox-gl-js/v2.7.0/mapbox-gl.js',
				[],
				null,
				true
			);

			wp_enqueue_script(
				'mapbox-geocoder',
				'https://api.mapbox.com/mapbox-gl-js/plugins/mapbox-gl-geocoder/v5.0.0/mapbox-gl-geocoder.min.js',
				[ 'mapbox' ],
				null,
				true
			);

			wp_enqueue_script(
				'mapbox-language',
				'https://api.mapbox.com/mapbox-gl-js/plugins/mapbox-gl-language/v1.0.0/mapbox-gl-language.js',
				[ 'mapbox-geocoder' ],
				null,
				true
			);

			wp_localize_script(
				'mapbox',
				'mapboxData',
				[
					'apiKey' => get_option( 'hp_mapbox_api_key' ),
				]
			);
		} else {
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
				[ 'hivepress-geolocation' ],
				null,
				true
			);

			wp_script_add_data( 'google-maps', 'async', true );
			wp_script_add_data( 'google-maps', 'defer', true );
		}
	}

	/**
	 * Alters scripts.
	 *
	 * @param array $scripts Scripts.
	 * @return array
	 */
	public function alter_scripts( $scripts ) {
		if ( get_option( 'hp_geolocation_provider' ) === 'mapbox' ) {
			$scripts['geolocation']['deps'][] = 'mapbox-language';
		}

		return $scripts;
	}

	/**
	 * Updates listing location.
	 *
	 * @param int $listing_id Listing ID.
	 */
	public function update_location( $listing_id ) {

		// Check settings.
		if ( ! get_option( 'hp_geolocation_generate_regions' ) ) {
			return;
		}

		// Get listing.
		$listing = Models\Listing::query()->get_by_id( $listing_id );

		if ( ! $listing ) {
			return;
		}

		// Get coordinates.
		$latitude  = $listing->get_latitude();
		$longitude = $listing->get_longitude();

		if ( ! $latitude || ! $longitude ) {
			return;
		}

		// Get map provider.
		$provider = get_option( 'hp_geolocation_provider' );

		// Get request URL.
		$request_url = null;

		if ( 'mapbox' === $provider ) {
			$request_url = 'https://api.mapbox.com/geocoding/v5/mapbox.places/' . rawurlencode( $longitude . ',' . $latitude ) . '.json?' . http_build_query(
				[
					'access_token' => get_option( 'hp_mapbox_api_key' ),
					'language'     => hivepress()->translator->get_language(),

					'types'        => implode(
						',',
						[
							'place',
							'district',
							'region',
							'country',
						]
					),
				]
			);
		} else {
			$request_url = 'https://maps.googleapis.com/maps/api/geocode/json?' . http_build_query(
				[
					'latlng'      => $latitude . ',' . $longitude,
					'key'         => get_option( 'hp_gmaps_api_key' ),
					'language'    => hivepress()->translator->get_language(),

					'result_type' => implode(
						'|',
						[
							'locality',
							'administrative_area_level_2',
							'administrative_area_level_1',
							'country',
						]
					),
				]
			);
		}

		// Get API response.
		$response = json_decode( wp_remote_retrieve_body( wp_remote_get( $request_url ) ), true );

		if ( ! $response || isset( $response['message'] ) || isset( $response['error_message'] ) ) {
			return;
		}

		// Get regions.
		$regions = [];

		if ( 'mapbox' === $provider ) {
			foreach ( $response['features'] as $result ) {
				$regions[ $result['id'] ] = $result['text'];
			}
		} else {
			foreach ( $response['results'] as $result ) {
				$regions[ $result['place_id'] ] = $result['address_components'][0]['long_name'];
			}
		}

		// Get region ID.
		$region_id = null;

		foreach ( array_reverse( $regions ) as $region_code => $region_name ) {

			// Get region.
			$region_args = term_exists( $region_name, 'hp_listing_region', $region_id );

			if ( ! $region_args ) {

				// Add region.
				$region_args = wp_insert_term(
					$region_name,
					'hp_listing_region',
					[
						'parent' => $region_id,
					]
				);

				if ( is_wp_error( $region_args ) ) {
					break;
				}

				update_term_meta( $region_args['term_id'], 'hp_code', $region_code );
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
	 * Sets search query.
	 *
	 * @param WP_Query $query Query object.
	 */
	public function set_search_query( $query ) {

		// Check settings.
		if ( ! get_option( 'hp_geolocation_generate_regions' ) ) {
			return;
		}

		// Get region code.
		$region_code = sanitize_text_field( hp\get_array_value( $_GET, '_region' ) );

		if ( ! $region_code ) {
			return;
		}

		// Get region ID.
		$region_id = hp\get_first_array_value(
			get_terms(
				[
					'taxonomy'   => 'hp_listing_region',
					'fields'     => 'ids',
					'number'     => 1,
					'hide_empty' => false,
					'meta_key'   => 'hp_code',
					'meta_value' => $region_code,
				]
			)
		);

		if ( ! $region_id ) {
			return;
		}

		// Get meta and taxonomy queries.
		$meta_query = array_filter( (array) $query->get( 'meta_query' ) );
		$tax_query  = array_filter( (array) $query->get( 'tax_query' ) );

		// Remove coordinate filters.
		unset( $meta_query['latitude'], $meta_query['longitude'] );

		// Add region filter.
		$tax_query[] = [
			'taxonomy' => 'hp_listing_region',
			'terms'    => $region_id,
		];

		// Set meta and taxonomy queries.
		$query->set( 'meta_query', $meta_query );
		$query->set( 'tax_query', $tax_query );
	}

	/**
	 * Sets search order.
	 *
	 * @param string   $orderby Order clause.
	 * @param WP_Query $query Query object.
	 * @return string
	 */
	public function set_search_order( $orderby, $query ) {
		global $wpdb;

		// Check query.
		if ( ! $query->is_main_query() || ! $query->is_search() || $query->get( 'post_type' ) !== 'hp_listing' ) {
			return $orderby;
		}

		// Check parameters.
		if ( ! empty( $_GET['_sort'] ) || empty( $_GET['location'] ) || ! empty( $_GET['_region'] ) ) {
			return $orderby;
		}

		// Get coordinates.
		$latitude  = round( floatval( hp\get_array_value( $_GET, 'latitude' ) ), 6 );
		$longitude = round( floatval( hp\get_array_value( $_GET, 'longitude' ) ), 6 );

		if ( $latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180 ) {
			return $orderby;
		}

		// Get table aliases.
		$aliases = [];

		foreach ( $query->meta_query->get_clauses() as $clause ) {
			if ( in_array( $clause['key'], [ 'hp_latitude', 'hp_longitude' ], true ) ) {
				$aliases[ hp\unprefix( $clause['key'] ) ] = sanitize_key( $clause['alias'] );
			}
		}

		if ( count( $aliases ) < 2 ) {
			return $orderby;
		}

		// Set order clause.
		$orderby = $wpdb->prepare(
			"POW( {$aliases['latitude']}.meta_value - %f, 2 ) + POW( {$aliases['longitude']}.meta_value - %f, 2 ) ASC",
			$latitude,
			$longitude
		);

		return $orderby;
	}

	/**
	 * Sets search radius.
	 *
	 * @param string $value Option value.
	 * @return string
	 */
	public function set_search_radius( $value ) {
		if ( get_option( 'hp_geolocation_allow_radius' ) ) {
			$radius = absint( hp\get_array_value( $_GET, '_radius' ) );

			// Recalculate miles in kilometres.
			if ( get_option( 'hp_geolocation_allow_radius' ) && get_option( 'hp_geolocation_metric' ) ) {
				$radius *= 1.60934;
			}

			if ( $radius >= 1 && $radius <= 100 ) {
				$value = $radius;
			}
		}

		return $value;
	}

	/**
	 * Alters listing search form.
	 *
	 * @param array $form Form arguments.
	 * @return array
	 */
	public function alter_listing_search_form( $form ) {

		// Get form flags.
		$is_search = strpos( current_filter(), '_search' );
		$is_filter = strpos( current_filter(), '_filter' );

		if ( get_option( 'hp_geolocation_generate_regions' ) ) {

			// Add region field.
			$form['fields']['_region'] = [
				'type'       => 'hidden',

				'attributes' => [
					'data-region' => true,
				],
			];

			if ( is_tax( 'hp_listing_region' ) ) {

				// Get region.
				$region = get_queried_object();

				// Set defaults.
				$form['fields']['_region']['default'] = get_term_meta( $region->term_id, 'hp_code', true );

				if ( isset( $form['fields']['location'] ) ) {
					$form['fields']['location']['default'] = $region->name;
				}
			}
		}

		if ( get_option( 'hp_geolocation_allow_radius' ) && ! $is_search && hp\get_array_value( $_GET, 'location' ) && ! hp\get_array_value( $_GET, '_region' ) ) {

			// Add radius field.
			$form['fields']['_radius'] = [
				'label'      => esc_html__( 'Radius', 'hivepress-geolocation' ),
				'type'       => 'number',
				'min_value'  => 1,
				'max_value'  => 100,
				'default'    => get_option( 'hp_geolocation_radius' ),
				'_order'     => 15,

				'statuses'   => [
					'optional' => null,
					get_option( 'hp_geolocation_metric' ) ? get_option( 'hp_geolocation_metric' ) : hivepress()->translator->get_string( 'km' ),
				],

				'attributes' => [
					'data-component' => 'radius-slider',
				],
			];

			if ( ! $is_filter ) {
				$form['fields']['_radius']['display_type'] = 'hidden';
			}
		}

		return $form;
	}

	/**
	 * Alters listing sort form.
	 *
	 * @param array $form Form arguments.
	 * @return array
	 */
	public function alter_listing_sort_form( $form ) {
		if ( ! empty( $_GET['location'] ) && empty( $_GET['_region'] ) ) {
			$form['fields']['_sort']['options'][''] = esc_html_x( 'Distance', 'sort order', 'hivepress-geolocation' );
		}

		return $form;
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
								'_label' => esc_html__( 'Location', 'hivepress-geolocation' ),
								'_order' => 5,
							],
						],
					],

					'page_sidebar'            => [
						'blocks' => [
							'listing_map' => [
								'type'       => 'listing_map',
								'_label'     => esc_html__( 'Map', 'hivepress-geolocation' ),
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
								'_label'     => esc_html__( 'Map', 'hivepress-geolocation' ),
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
	 * Deletes empty regions.
	 */
	public function delete_empty_regions() {

		// Check option.
		if ( ! get_option( 'hp_geolocation_generate_regions' ) ) {
			return;
		}

		// Get region terms ids.
		$term_ids = array_filter(
			array_map(
				function ( $term ) {
					if ( 0 === $term->count ) {
						return $term->term_id;
					}
				},
				(array) get_terms(
					[
						'hide_empty' => 0,
						'orderby'    => 'parent',
						'order'      => 'ASC',
						'taxonomy'   => 'hp_listing_region',
						'pad_counts' => 1,
						'number'     => 100,
					]
				)
			)
		);

		foreach ( $term_ids as $term_id ) {
			wp_delete_term( $term_id, 'hp_listing_region' );
		}
	}
}
