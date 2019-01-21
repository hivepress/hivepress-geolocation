<?php
namespace HivePress\Geolocation;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Manages geolocation.
 *
 * @class Geolocation
 */
class Geolocation extends \HivePress\Component {

	/**
	 * Class constructor.
	 *
	 * @param array $settings
	 */
	public function __construct( $settings ) {
		parent::__construct( $settings );

		// Manage location field.
		add_filter( 'hivepress/form/field_value/location', [ $this, 'sanitize_location_field' ], 10, 2 );
		add_filter( 'hivepress/form/field_html/location', [ $this, 'render_location_field' ], 10, 4 );

		// Manage coordinate fields.
		add_filter( 'hivepress/form/field_value/latitude', [ $this, 'sanitize_coordinate_field' ], 10, 2 );
		add_filter( 'hivepress/form/field_value/longitude', [ $this, 'sanitize_coordinate_field' ], 10, 2 );
		add_filter( 'hivepress/form/field_html/latitude', [ $this, 'render_coordinate_field' ], 10, 4 );
		add_filter( 'hivepress/form/field_html/longitude', [ $this, 'render_coordinate_field' ], 10, 4 );

		// Set location.
		add_filter( 'hivepress/admin/meta_box_fields/listing__attributes', [ $this, 'set_location_fields' ] );
		add_filter( 'hivepress/form/form_fields/listing__search', [ $this, 'set_location_fields' ] );
		add_filter( 'hivepress/form/form_fields/listing__submit', [ $this, 'set_location_fields' ] );
		add_filter( 'hivepress/form/form_values/listing__submit', [ $this, 'set_location_values' ] );
		add_filter( 'hivepress/form/form_fields/listing__update', [ $this, 'set_location_fields' ] );
		add_filter( 'hivepress/form/form_values/listing__update', [ $this, 'set_location_values' ] );

		// Update location.
		add_action( 'hivepress/form/submit_form/listing__submit', [ $this, 'update_location' ] );
		add_action( 'hivepress/form/submit_form/listing__update', [ $this, 'update_location' ] );

		if ( ! is_admin() ) {

			// Set search query.
			add_action( 'pre_get_posts', [ $this, 'set_search_query' ] );

			// Enqueue scripts.
			add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		}
	}

	/**
	 * Sanitizes location field.
	 *
	 * @param mixed $value
	 * @param array $args
	 * @return mixed
	 */
	public function sanitize_location_field( $value ) {
		return sanitize_text_field( $value );
	}

	/**
	 * Renders location field.
	 *
	 * @param string $output
	 * @param string $id
	 * @param array  $args
	 * @param mixed  $value
	 * @return string
	 */
	public function render_location_field( $output, $id, $args, $value ) {

		// Get wrapper class.
		$class = hp_replace_placeholders( $args, $args['attributes']['class'] );

		$output .= '<div class="' . esc_attr( $class ) . '">';

		// Set field arguments.
		$args = hp_merge_arrays(
			[
				'placeholder' => '',
			],
			$args,
			[
				'type'       => 'text',
				'before'     => '',
				'after'      => '',
				'attributes' => [
					'class' => 'hp-form__field hp-form__field--location hp-js-geocomplete',
				],
			]
		);

		// Render field.
		$output .= hivepress()->form->render_field( $id, $args, $value );

		// Render button.
		$output .= '<a href="#" title="' . esc_attr__( 'Locate Me', 'hivepress-geolocation' ) . '" class="hp-js-geolocate"><i class="fas fa-location-arrow"></i></a>';

		$output .= '</div>';

		return $output;
	}

	/**
	 * Sanitizes coordinate field.
	 *
	 * @param mixed $value
	 * @param array $args
	 * @return mixed
	 */
	public function sanitize_coordinate_field( $value, $args ) {
		if ( '' !== $value ) {
			$value = round( floatval( $value ), 6 );

			if ( ( 'latitude' === $args['type'] && ( $value < -90 || $value > 90 ) ) || ( 'longitude' === $args['type'] && ( $value < -180 || $value > 180 ) ) ) {
				$value = '';
			}
		}

		return $value;
	}

	/**
	 * Renders coordinate field.
	 *
	 * @param string $output
	 * @param string $id
	 * @param array  $args
	 * @param mixed  $value
	 * @return string
	 */
	public function render_coordinate_field( $output, $id, $args, $value ) {

		// Set field arguments.
		if ( 'latitude' === $args['type'] ) {
			$args['attributes']['data-type'] = 'lat';
		} else {
			$args['attributes']['data-type'] = 'lng';
		}

		$args['type'] = 'hidden';

		// Render field.
		$output .= hivepress()->form->render_field( $id, $args, $value );

		return $output;
	}

	/**
	 * Sets location fields.
	 *
	 * @param array $fields
	 * @return array
	 */
	public function set_location_fields( $fields ) {

		// Unset location fields.
		if ( get_option( 'hp_gmaps_api_key' ) === '' ) {
			unset( $fields['location'] );
			unset( $fields['latitude'] );
			unset( $fields['longitude'] );
		}

		return $fields;
	}

	/**
	 * Sets location values.
	 *
	 * @param array $values
	 * @return array
	 */
	public function set_location_values( $values ) {

		// Get listing ID.
		$listing_id = absint( hp_get_array_value( $values, 'post_id' ) );

		if ( 0 !== $listing_id ) {

			// Set location.
			$values['location']  = get_post_meta( $listing_id, 'hp_location', true );
			$values['latitude']  = get_post_meta( $listing_id, 'hp_latitude', true );
			$values['longitude'] = get_post_meta( $listing_id, 'hp_longitude', true );
		}

		return $values;
	}

	/**
	 * Updates location.
	 *
	 * @param array $values
	 */
	public function update_location( $values ) {

		if ( get_option( 'hp_gmaps_api_key' ) !== '' ) {

			// Get listing ID.
			$listing_id = hp_get_post_id(
				[
					'post_type'   => 'hp_listing',
					'post_status' => [ 'auto-draft', 'draft', 'publish' ],
					'post_parent' => 0,
					'post__in'    => [ absint( $values['post_id'] ) ],
					'author'      => get_current_user_id(),
				]
			);

			if ( 0 !== $listing_id ) {

				if ( '' === $values['latitude'] || '' === $values['longitude'] ) {
					hivepress()->form->add_error( esc_html__( '"Location" is required.', 'hivepress-geolocation' ) );
				} else {

					// Update location.
					update_post_meta( $listing_id, 'hp_latitude', $values['latitude'] );
					update_post_meta( $listing_id, 'hp_longitude', $values['longitude'] );
					update_post_meta( $listing_id, 'hp_location', $values['location'] );
				}
			}
		}
	}

	/**
	 * Sets search query.
	 *
	 * @param WP_Query $query
	 */
	public function set_search_query( $query ) {
		if ( get_option( 'hp_gmaps_api_key' ) !== '' && $query->is_main_query() && is_post_type_archive( 'hp_listing' ) && is_search() ) {

			// Validate search form.
			$values = hivepress()->form->validate_form( 'listing__search' );

			if ( false !== $values && '' !== $values['latitude'] && '' !== $values['longitude'] ) {

				// Calculate coordinate radiuses.
				$radius           = 15;
				$latitude_radius  = $radius / 110.574;
				$longitude_radius = $radius / ( 111.320 * cos( deg2rad( $values['latitude'] ) ) );

				// Get meta query.
				$meta_query = (array) $query->get( 'meta_query' );

				// Add meta filters.
				$meta_query[] = [
					'key'     => 'hp_latitude',
					'value'   => [ $values['latitude'] - $latitude_radius, $values['latitude'] + $latitude_radius ],
					'compare' => 'BETWEEN',
					'type'    => 'DECIMAL(9, 6)',
				];

				$meta_query[] = [
					'key'     => 'hp_longitude',
					'value'   => [ $values['longitude'] - $longitude_radius, $values['longitude'] + $longitude_radius ],
					'compare' => 'BETWEEN',
					'type'    => 'DECIMAL(9, 6)',
				];

				// Set meta query.
				$query->set( 'meta_query', $meta_query );
			}
		}
	}

	/**
	 * Renders map.
	 *
	 * @return string
	 */
	public function render_map() {
		$output = '';

		// Set map data.
		$data = [];

		rewind_posts();

		while ( have_posts() ) {
			the_post();

			// Get coordinates.
			$latitude  = get_post_meta( get_the_ID(), 'hp_latitude', true );
			$longitude = get_post_meta( get_the_ID(), 'hp_longitude', true );

			// Set location.
			if ( '' !== $latitude && '' !== $longitude ) {
				$data[] = [
					'latitude'  => round( floatval( $latitude ), 6 ),
					'longitude' => round( floatval( $longitude ), 6 ),
					'title'     => esc_html( get_the_title() ),
					'content'   => '<h4><a href="' . esc_url( get_permalink( get_the_ID() ) ) . '">' . esc_html( get_the_title() ) . '</a></h4>',
				];
			}
		}

		rewind_posts();

		// Render map.
		if ( ! empty( $data ) ) {
			$output .= '<div class="hp-js-map" data-json="' . esc_attr( wp_json_encode( $data ) ) . '"></div>';
		}

		return $output;
	}

	/**
	 * Enqueues scripts.
	 */
	public function enqueue_scripts() {

		// Get API key.
		$api_key = get_option( 'hp_gmaps_api_key' );

		if ( '' !== $api_key ) {

			// Enqueue script.
			wp_enqueue_script(
				'google-maps',
				'https://maps.googleapis.com/maps/api/js?' . http_build_query(
					[
						'libraries' => 'places',
						'callback'  => 'hivepress.initMap',
						'key'       => $api_key,
					]
				),
				[ 'hp-geolocation' ],
				HP_GEOLOCATION_VERSION,
				true
			);
		}
	}
}
