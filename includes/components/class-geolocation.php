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
	 * Model names.
	 *
	 * @var array
	 */
	protected $models = [ 'listing', 'vendor', 'request' ];

	/**
	 * Class constructor.
	 *
	 * @param array $args Component arguments.
	 */
	public function __construct( $args = [] ) {

		// Set models.
		$this->models = array_intersect( $this->models, (array) get_option( 'hp_geolocation_models', [ 'listing' ] ) );

		// Add taxonomies.
		add_filter( 'hivepress/v1/taxonomies', [ $this, 'add_taxonomies' ] );

		// Enqueue scripts.
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ], 1 );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ], 1 );

		add_filter( 'hivepress/v1/scripts', [ $this, 'alter_scripts' ] );

		foreach ( $this->models as $model ) {

			// Add attributes.
			add_filter( 'hivepress/v1/models/' . $model . '/attributes', [ $this, 'add_attributes' ] );

			// Update location.
			add_action( 'hivepress/v1/models/' . $model . '/update_longitude', [ $this, 'update_location' ] );

			// Alter forms.
			add_filter( 'hivepress/v1/forms/' . $model . '_search', [ $this, 'alter_search_form' ], 200, 2 );
			add_filter( 'hivepress/v1/forms/' . $model . '_filter', [ $this, 'alter_search_form' ], 200, 2 );
			add_filter( 'hivepress/v1/forms/' . $model . '_sort', [ $this, 'alter_search_form' ], 200, 2 );

			add_filter( 'hivepress/v1/forms/' . $model . '_sort', [ $this, 'alter_sort_form' ], 200 );

			// Set search query.
			add_action( 'hivepress/v1/models/' . $model . '/search', [ $this, 'set_search_query' ] );

			// Alter templates.
			add_filter( 'hivepress/v1/templates/' . $model . '_view_block', [ $this, 'alter_model_view_block' ], 10, 2 );
			add_filter( 'hivepress/v1/templates/' . $model . '_view_page', [ $this, 'alter_model_view_page' ], 10, 2 );
			add_filter( 'hivepress/v1/templates/' . $model . 's_view_page', [ $this, 'alter_models_view_page' ], 10, 2 );
		}

		if ( is_admin() ) {

			// Alter settings.
			add_filter( 'hivepress/v1/settings', [ $this, 'alter_settings' ] );
		} else {

			// Set related query.
			add_action( 'hivepress/v1/models/listing/relate', [ $this, 'set_related_query' ], 10, 2 );

			// Set search order.
			add_filter( 'posts_orderby', [ $this, 'set_search_order' ], 100, 2 );

			// Set search radius.
			add_filter( 'option_hp_geolocation_radius', [ $this, 'set_search_radius' ] );
		}

		parent::__construct( $args );
	}

	/**
	 * Adds model attributes.
	 *
	 * @param array $attributes Attributes.
	 * @return array
	 */
	public function add_attributes( $attributes ) {

		// Get countries.
		$countries = array_filter( (array) get_option( 'hp_geolocation_countries' ) );

		// Get radius.
		$radius = absint( get_option( 'hp_geolocation_radius', 15 ) );

		if ( get_option( 'hp_geolocation_use_miles' ) ) {
			$radius *= 1.60934;
		}

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
						'_order'    => 35,
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
						'label' => esc_html__( 'Latitude', 'hivepress-geolocation' ),
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
						'label' => esc_html__( 'Longitude', 'hivepress-geolocation' ),
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
			foreach ( $this->models as $model ) {
				$taxonomies[ $model . '_region' ] = [
					'post_type'          => [ $model ],
					'hierarchical'       => true,
					'show_in_quick_edit' => false,
					'meta_box_cb'        => false,
					'rewrite'            => [ 'slug' => $model . '-region' ],

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
						'key'       => get_option( 'hp_gmaps_api_key' ),
						'language'  => hivepress()->translator->get_language(),
						'region'    => hivepress()->translator->get_region(),
					]
				),
				[],
				null,
				true
			);
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
		} else {
			$scripts['geolocation']['deps'] = array_merge(
				$scripts['geolocation']['deps'],
				[ 'geocomplete', 'markerclustererplus', 'markerspiderfier' ]
			);
		}

		return $scripts;
	}

	/**
	 * Updates model location.
	 *
	 * @param int $model_id Model ID.
	 */
	public function update_location( $model_id ) {

		// Check settings.
		if ( ! get_option( 'hp_geolocation_generate_regions' ) ) {
			return;
		}

		// Get model name.
		$model_name = hp\get_array_value( explode( '/', current_action() ), 3 );

		if ( ! in_array( $model_name, $this->models ) ) {
			return;
		}

		// Get model object.
		$model = hivepress()->model->get_model_object( $model_name, $model_id );

		if ( ! $model ) {
			return;
		}

		// Get coordinates.
		$latitude  = $model->get_latitude();
		$longitude = $model->get_longitude();

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
					'key'         => get_option( 'hp_gmaps_secret_key' ) ? get_option( 'hp_gmaps_secret_key' ) : get_option( 'hp_gmaps_api_key' ),
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

		// Get region taxonomy.
		$region_taxonomy = hp\prefix( $model_name . '_region' );

		// Get region ID.
		$region_id = null;

		foreach ( array_reverse( $regions ) as $region_code => $region_name ) {

			// Get region.
			$region_args = term_exists( $region_name, $region_taxonomy, $region_id );

			if ( ! $region_args ) {

				// Add region.
				$region_args = wp_insert_term(
					$region_name,
					$region_taxonomy,
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
		wp_set_object_terms( $model->get_id(), $region_id, $region_taxonomy );
	}

	/**
	 * Alters settings.
	 *
	 * @param array $settings Settings configuration.
	 * @return array
	 */
	public function alter_settings( $settings ) {
		if ( hivepress()->get_version( 'requests' ) ) {
			$settings['geolocation']['sections']['restrictions']['fields']['geolocation_models']['options']['request'] = hivepress()->translator->get_string( 'requests' );
		}

		return $settings;
	}

	/**
	 * Sets related query.
	 *
	 * @todo Remove when related field flags are implemented.
	 * @param WP_Query $query Query object.
	 * @param object   $listing Listing object.
	 */
	public function set_related_query( $query, $listing ) {

		// Check coordinates.
		if ( ! $listing->get_latitude() || ! $listing->get_longitude() ) {
			return;
		}

		// Get fields.
		$latitude_field  = hp\get_array_value( $listing->_get_fields(), 'latitude' );
		$longitude_field = hp\get_array_value( $listing->_get_fields(), 'longitude' );

		// Update filter.
		$longitude_field->set_parent_value( $listing->get_latitude() );
		$longitude_field->update_filter();

		foreach ( [ $latitude_field, $longitude_field ] as $field ) {

			// Get filter.
			$filter = $field->get_filter();

			if ( $filter ) {

				// Set filter.
				$query->set_args(
					[
						'meta_query' => [
							$field->get_name() => [
								'key'     => hp\prefix( $filter['name'] ),
								'type'    => $filter['type'],
								'value'   => $filter['value'],
								'compare' => $filter['operator'],
							],
						],
					]
				);
			}
		}
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

		// Get region taxonomy.
		$region_taxonomy = $query->get( 'post_type' ) . '_region';

		// Get region ID.
		$region_id = hp\get_first_array_value(
			get_terms(
				[
					'taxonomy'   => $region_taxonomy,
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
			'taxonomy' => $region_taxonomy,
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
		if ( ! $query->is_main_query() || ! $query->is_search() || ! in_array( $query->get( 'post_type' ), hp\prefix( $this->models ) ) ) {
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
				$aliases[ hp\unprefix( $clause['key'] ) ] = $clause['alias'];
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

			if ( $radius >= 1 && $radius <= 100 ) {
				$value = $radius;
			}
		}

		return $value;
	}

	/**
	 * Alters search form.
	 *
	 * @param array  $form_args Form arguments.
	 * @param object $form Form object.
	 * @return array
	 */
	public function alter_search_form( $form_args, $form ) {

		// Get form flags.
		$is_search = strpos( current_filter(), '_search' );
		$is_filter = strpos( current_filter(), '_filter' );

		if ( get_option( 'hp_geolocation_generate_regions' ) ) {

			// Add region field.
			$form_args['fields']['_region'] = [
				'type'       => 'hidden',

				'attributes' => [
					'data-region' => true,
				],
			];

			if ( is_tax( hp\prefix( $form::get_meta( 'model' ) . '_region' ) ) ) {

				// Get region.
				$region = get_queried_object();

				// Set defaults.
				$form_args['fields']['_region']['default'] = get_term_meta( $region->term_id, 'hp_code', true );

				if ( isset( $form_args['fields']['location'] ) ) {
					$form_args['fields']['location']['default'] = $region->name;
				}
			}
		}

		if ( get_option( 'hp_geolocation_allow_radius' ) && ! $is_search && hp\get_array_value( $_GET, 'location' ) && ! hp\get_array_value( $_GET, '_region' ) ) {

			// Add radius field.
			$form_args['fields']['_radius'] = [
				'label'      => esc_html__( 'Radius', 'hivepress-geolocation' ),
				'type'       => 'number',
				'min_value'  => 1,
				'max_value'  => 100,
				'default'    => get_option( 'hp_geolocation_radius' ),
				'_order'     => 15,

				'statuses'   => [
					'optional' => null,
					'unit'     => get_option( 'hp_geolocation_use_miles' ) ? esc_html_x( 'mi', 'miles', 'hivepress-geolocation' ) : esc_html_x( 'km', 'kilometers', 'hivepress-geolocation' ),
				],

				'attributes' => [
					'data-component' => 'radius-slider',
				],
			];

			if ( ! $is_filter ) {
				$form_args['fields']['_radius']['display_type'] = 'hidden';
			}
		}

		return $form_args;
	}

	/**
	 * Alters sort form.
	 *
	 * @param array $form Form arguments.
	 * @return array
	 */
	public function alter_sort_form( $form ) {
		if ( ! empty( $_GET['location'] ) && empty( $_GET['_region'] ) ) {
			$form['fields']['_sort']['options'][''] = esc_html_x( 'Distance', 'sort order', 'hivepress-geolocation' );
		}

		return $form;
	}

	/**
	 * Alters model view block.
	 *
	 * @param array  $template_args Template arguments.
	 * @param object $template Template object.
	 * @return array
	 */
	public function alter_model_view_block( $template_args, $template ) {

		// Get model name.
		$model = $template::get_meta( 'model' );

		// @todo remove once models are set, also below.
		if ( ! $model ) {
			$model = 'listing';
		}

		return hp\merge_trees(
			$template_args,
			[
				'blocks' => [
					$model . '_details_primary' => [
						'blocks' => [
							$model . '_location' => [
								'type'   => 'part',
								'path'   => $model . '/view/' . $model . '-location',
								'_order' => 5,
							],
						],
					],
				],
			]
		);
	}

	/**
	 * Alters model view page.
	 *
	 * @param array  $template_args Template arguments.
	 * @param object $template Template object.
	 * @return array
	 */
	public function alter_model_view_page( $template_args, $template ) {

		// Get model name.
		$model = $template::get_meta( 'model' );

		if ( ! $model ) {
			$model = 'listing';
		}

		// Get new blocks.
		$blocks = [
			$model . '_details_primary' => [
				'blocks' => [
					$model . '_location' => [
						'type'   => 'part',
						'path'   => $model . '/view/' . $model . '-location',
						'_label' => esc_html__( 'Location', 'hivepress-geolocation' ),
						'_order' => 5,
					],
				],
			],
		];

		if ( 'vendor' !== $model ) {
			$blocks['page_sidebar'] = [
				'blocks' => [
					$model . '_map' => [
						'type'       => 'listing_map',
						'model'      => $model,
						'_label'     => esc_html__( 'Map', 'hivepress-geolocation' ),
						'_order'     => 25,

						'attributes' => [
							'class' => [ 'hp-' . $model . '__map', 'hp-listing__map', 'widget' ],
						],
					],
				],
			];
		}

		return hp\merge_trees(
			$template_args,
			[
				'blocks' => $blocks,
			]
		);
	}

	/**
	 * Alters models view page.
	 *
	 * @param array  $template_args Template arguments.
	 * @param object $template Template object.
	 * @return array
	 */
	public function alter_models_view_page( $template_args, $template ) {

		// Get model name.
		$model = $template::get_meta( 'model' );

		if ( ! $model ) {
			$model = 'listing';
		}

		return hp\merge_trees(
			$template_args,
			[
				'blocks' => [
					'page_sidebar' => [
						'blocks' => [
							$model . '_map' => [
								'type'       => 'listing_map',
								'model'      => $model,
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
}
