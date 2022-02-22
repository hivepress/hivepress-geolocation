<?php
/**
 * Geolocation component.
 *
 * @package HivePress\Components
 */

namespace HivePress\Components;

use HivePress\Helpers as hp;

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

		// Check API key.
		if ( ! get_option( 'hp_gmaps_api_key' ) ) {
			return;
		}

		// Add attributes.
		add_filter( 'hivepress/v1/models/listing/attributes', [ $this, 'add_attributes' ] );

		// Enqueue scripts.
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );

		// Alter forms.
		add_filter( 'hivepress/v1/forms/listing_search', [ $this, 'alter_listing_filter_search_sort_forms' ], 1000, 2 );
		add_filter( 'hivepress/v1/forms/listing_filter', [ $this, 'alter_listing_filter_search_sort_forms' ], 1000, 2 );
		add_filter( 'hivepress/v1/forms/listing_sort', [ $this, 'alter_listing_filter_search_sort_forms' ], 1000, 2 );
		add_filter( 'hivepress/v1/forms/listing_sort', [ $this, 'alter_listing_sort_form' ], 1000, 2 );

		// Update models fields.
		add_action( 'hivepress/v1/models/listing/update', [ $this, 'update_listing_model' ], 1000, 2 );

		add_filter( 'posts_orderby', [ $this, 'set_listing_order' ], 100, 2 );

		if ( ! is_admin() ) {

			// Alter forms.
			add_filter( 'hivepress/v1/forms/listing_filter', [ $this, 'alter_listing_filter_form' ], 100 );

			// Alter templates.
			add_filter( 'hivepress/v1/templates/listing_view_block', [ $this, 'alter_listing_view_block' ] );
			add_filter( 'hivepress/v1/templates/listing_view_page', [ $this, 'alter_listing_view_page' ] );
			add_filter( 'hivepress/v1/templates/listings_view_page', [ $this, 'alter_listings_view_page' ] );

			// Set search query.
			add_action( 'pre_get_posts', [ $this, 'set_search_query' ], 5 );
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
		$radius = absint( hp\get_array_value( $_GET, '_radius', get_option( 'hp_geolocation_radius', 15 ) ) );

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
	 * Alters listing filter form.
	 *
	 * @param array $form Form arguments.
	 * @return array
	 */
	public function alter_listing_filter_form( $form ) {
		if ( get_option( 'hp_geolocation_allow_radius' ) ) {
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

		return $form;
	}

	/**
	 * Alters listing search form.
	 *
	 * @param array  $form_args Form arguments.
	 * @param object $form Form object.
	 * @return array
	 */
	public function alter_listing_filter_search_sort_forms( $form_args, $form ) {
		$form_args['fields']['_regions'] = [
			'type' => 'hidden',
		];

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
		if ( ! $query->is_search() || $query->get( 'post_type' ) !== 'hp_listing' ) {
			return;
		}

		$region_field = hp\get_array_value( $_GET, '_regions' );

		// Check filter.
		if ( ! $region_field ) {
			return;
		}

		// Get meta query.
		$meta_query = (array) $query->get( 'meta_query' );

		foreach ( $meta_query as $key => $value ) {
			if ( $value && in_array( $value['key'], [ 'hp_latitude', 'hp_longitude' ], true ) ) {
				unset( $meta_query[ $key ] );
			}
		}

		// Change region sort to country - state - city.
		$regions = array_reverse( explode( '|', $region_field ) );

		// Term id.
		$term_id = null;

		// Taxonomy.
		$taxonomy = 'hp_listing_region';

		foreach ( $regions as $region ) {
			$term = term_exists( $region, $taxonomy, $term_id );

			// Check term is existed.
			if ( ! $term ) {
				break;
			}

			$term_id = $term['term_id'];
		}

		if ( $term_id ) {

			// Get meta query.
			$tax_query = array_filter( (array) $query->get( 'tax_query' ) );

			// Add meta clause.
			$tax_query[] = [
				'taxonomy' => $taxonomy,
				'field'    => 'id',
				'terms'    => $term_id,
			];

			// Set meta query.
			$query->set( 'tax_query', $tax_query );
		}
	}

	/**
	 * Updates listing longitude/latitude.
	 *
	 * @param int    $listing_id Listing ID.
	 * @param object $listing Listing object.
	 */
	public function update_listing_model( $listing_id, $listing ) {

		// Get google api key.
		$api_key = get_option( 'hp_gmaps_api_key' );

		// Check api key and listing.
		if ( ! $api_key || ! $listing ) {
			return;
		}

		$lng = $listing->get_longitude();
		$lat = $listing->get_latitude();

		// Check fields.
		if ( ! $lng && ! $lat ) {
			return;
		}

		// Get location data.
		$response = wp_remote_get( 'https://maps.googleapis.com/maps/api/geocode/json?latlng=' . $lat . ',' . $lng . '&key=' . $api_key );

		if ( 200 !== $response['response']['code'] ) {
			return;
		}

		$results = json_decode( $response['body'] )->results;

		if ( ! $results ) {
			return;
		}

		$types = [
			'locality',
			'administrative_area_level_1',
			'administrative_area_level_2',
			'country',
		];

		$place = [];

		foreach ( $results as $result ) {
			foreach ( array_reverse( $result->address_components ) as $component ) {
				if ( array_intersect( $types, $component->types ) ) {
					$place[] = $component->long_name;
				}
			}
			if ( count( $place ) > 2 ) {
				break;
			}
		}

		if ( count( $place ) < 3 ) {
			return;
		}
		// Term id.
		$parent_id = null;

		// Taxonomy.
		$taxonomy = 'hp_listing_region';

		// Delete old term.
		wp_delete_object_term_relationships( $listing_id, $taxonomy );

		foreach ( $place as $region ) {
			$term = term_exists( $region, $taxonomy, $parent_id );

			// Check term is existed.
			if ( $term ) {
				$parent_id = $term['term_id'];
			} else {
				wp_insert_term(
					$region,
					$taxonomy,
					array(
						'parent' => $parent_id,
					)
				);
				$new_term = term_exists( $region, $taxonomy, $parent_id );

				if ( ! $new_term ) {
					break;
				}

				$parent_id = $new_term['term_id'];
			}

			// Set listing to term.
			wp_set_object_terms( $listing_id, intval( $parent_id ), $taxonomy, true );
		}

	}

	/**
	 * Alters listing sort form.
	 *
	 * @param array  $form_args Form arguments.
	 * @param object $form Form object.
	 * @return array
	 */
	public function alter_listing_sort_form( $form_args, $form ) {
		if ( isset( $_GET['latitude'], $_GET['longitude'] ) ) {
			$form_args['fields']['_sort']['options'] = array_merge(
				$form_args['fields']['_sort']['options'],
				[
					'distance' => esc_html__( 'Distance', 'hivepress-geolocation' ),
				]
			);
		}
		return $form_args;
	}

	/**
	 * Sets listing order.
	 *
	 * @param string   $orderby ORDER BY clause.
	 * @param WP_Query $query Query object.
	 * @return string
	 */
	public function set_listing_order( $orderby, $query ) {

		// Check query.
		if ( ! $query->is_search() || $query->get( 'post_type' ) !== 'hp_listing' || ( ! isset( $_GET['_sort'], $_GET['location'] ) || 'distance' !== $_GET['_sort'] ) ) {
			return $orderby;
		}

		// Get latitutde.
		$lat = filter_var( hp\get_array_value( $_GET, 'latitude' ), FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION );

		// Get longitude.
		$lng = filter_var( hp\get_array_value( $_GET, 'longitude' ), FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION );

		// Check latitutde and longitude.
		if ( ! $lat || ! $lng ) {
			return $orderby;
		}

		if ( ! strpos( $orderby, 'RAND' ) ) {
			$orderby = '( ( wp_postmeta.meta_value - ' . $lat . ' ) * ( wp_postmeta.meta_value - ' . $lat . ' ) ) + ( ( mt1.meta_value - ' . $lng . ' ) * ( mt1.meta_value - ' . $lng . ' ) ) ASC';
		}

		return $orderby;
	}
}
