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

		if ( ! is_admin() ) {

			// Alter templates.
			add_filter( 'hivepress/v1/templates/listing_view_block', [ $this, 'alter_listing_view_block' ] );
			add_filter( 'hivepress/v1/templates/listing_view_page', [ $this, 'alter_listing_view_page' ] );
			add_filter( 'hivepress/v1/templates/listings_view_page', [ $this, 'alter_listings_view_page' ] );
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
}
