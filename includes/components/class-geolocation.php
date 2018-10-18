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
		add_filter( 'hivepress/form/field_html/location', [ $this, 'render_location_field' ], 10, 4 );

		add_action( 'hivepress/form/submit_form/listing__update', 'update_listing' );

		if ( ! is_admin() ) {

			// Enqueue scripts.
			add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );

			add_action( 'pre_get_posts', [ $this, 'set_search_query' ] );
		}
	}

	public function update_listing( $values ) {

		// Get listing ID.
		$listing_id = hp_get_post_id(
			[
				'post_type'   => 'hp_listing',
				'post_status' => [ 'draft', 'publish' ],
				'post__in'    => [ absint( $values['post_id'] ) ],
				'author'      => get_current_user_id(),
			]
		);

		if ( 0 !== $listing_id ) {

			// Update location.
		}
	}


	public function render_location_field( $output, $id, $args, $value ) {

		// Render HTML attributes.
		$attributes = hp_replace_placeholders( $args, hp_html_attributes( $args['attributes'] ) );

		$output .= '<div ' . $attributes . '>';

		// Render location field.
		$output .= hivepress()->form->render_field(
			$id,
			[
				// 'placeholder' => $args['placeholder'],
				'type'       => 'text',
				'default'    => $value,
				'attributes' => [
					'class' => 'hp-js-geocomplete',
				],
			]
		);

		// Render location.
		$output .= '<a href="#" title="' . esc_attr__( 'Locate Me', 'hivepress-geolocation' ) . '" class="hp-js-geolocate"><i class="fas fa-location-arrow"></i></a>';

		$output .= '</div>';

		return $output;
	}

	/**
	 * Sets search query.
	 *
	 * @param WP_Query $query
	 */
	public function set_search_query( $query ) {
		if ( $query->is_main_query() && is_post_type_archive( 'hp_listing' ) && is_search() ) {

			// Get latitude and longitude.
			$values = hivepress()->form->validate_form( 'listing__search' );

			$latitude  = floatval( $values['latitude'] );
			$longitude = floatval( $values['longitude'] );

			// Calculate location radiuses.
			$radius           = 15;
			$latitude_radius  = $radius / 110.574;
			$longitude_radius = $radius / ( 111.320 * cos( deg2rad( $latitude ) ) );

			// Get meta query.
			$meta_query = (array) $query->get( 'meta_query' );

			// Add meta filters.
			$meta_query[] = [
				'key'     => 'hp_latitude',
				'value'   => [ $latitude - $latitude_radius, $latitude + $latitude_radius ],
				'compare' => 'BETWEEN',
				'type'    => 'DECIMAL(9, 6)',
			];

			$meta_query[] = [
				'key'     => 'hp_longitude',
				'value'   => [ $longitude - $longitude_radius, $longitude + $longitude_radius ],
				'compare' => 'BETWEEN',
				'type'    => 'DECIMAL(9, 6)',
			];

			// Set meta query.
			$query->set( 'meta_query', $meta_query );
		}
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
						'callback'  => 'initMap',
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
