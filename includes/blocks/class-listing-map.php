<?php
/**
 * Listing map block.
 *
 * @package HivePress\Blocks
 */

namespace HivePress\Blocks;

use HivePress\Helpers as hp;
use Hivepress\Models;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Listing map block class.
 *
 * @class Listing_Map
 */
class Listing_Map extends Block {

	/**
	 * Map attributes.
	 *
	 * @var array
	 */
	protected $attributes = [];

	/**
	 * Scattering flag.
	 *
	 * @var bool
	 */
	protected $scatter = false;

	/**
	 * Bootstraps block properties.
	 */
	protected function boot() {
		$attributes = [];

		// Set zoom.
		$attributes['data-max-zoom'] = absint( get_option( 'hp_geolocation_max_zoom', 18 ) );

		// Set scattering.
		$this->scatter = (bool) get_option( 'hp_geolocation_hide_address' );

		if ( $this->scatter ) {
			$attributes['data-scatter'] = 'true';
		}

		// Set component.
		$attributes['data-component'] = 'map';

		if ( get_option( 'hp_geolocation_map_provider' ) ) {

			// Set map provider.
			$attributes['data-provider'] = esc_html( get_option( 'hp_geolocation_map_provider' ) );

			// Set api key.
			$attributes['data-map-key'] = esc_html( get_option( 'hp_mapbox_api_key' ) );
		}

		// Set class.
		$attributes['class'] = [ 'hp-map' ];

		$this->attributes = hp\merge_arrays( $this->attributes, $attributes );

		parent::boot();
	}

	/**
	 * Renders block HTML.
	 *
	 * @return string
	 */
	public function render() {
		$output = '';

		if ( get_option( 'hp_gmaps_api_key' ) ) {
			$markers = [];

			// Get featured IDs.
			$featured_ids = hivepress()->request->get_context( 'featured_ids' );

			if ( $featured_ids ) {

				// Query featured listings.
				$featured_query = new \WP_Query(
					Models\Listing::query()->filter(
						[
							'status' => 'publish',
							'id__in' => $featured_ids,
						]
					)->limit( count( $featured_ids ) )
					->get_args()
				);

				while ( $featured_query->have_posts() ) {
					$featured_query->the_post();

					// Add marker.
					$markers[] = $this->get_marker( get_post() );
				}

				// Reset query.
				wp_reset_postdata();
			}

			// Query regular listings.
			rewind_posts();

			while ( have_posts() ) {
				the_post();

				// Add marker.
				$markers[] = $this->get_marker( get_post() );
			}

			rewind_posts();

			// Render markers.
			$markers = array_filter( $markers );

			if ( $markers ) {
				$output .= '<div data-markers="' . hp\esc_json( wp_json_encode( $markers ) ) . '" ' . hp\html_attributes( $this->attributes ) . '></div>';
			}
		}

		return $output;
	}

	/**
	 * Gets map marker.
	 *
	 * @param WP_Post $post Post object.
	 * @return array
	 */
	protected function get_marker( $post ) {
		$marker = null;

		// Get listing.
		$listing = Models\Listing::query()->get_by_id( $post );

		if ( $listing && ! is_null( $listing->get_latitude() ) && ! is_null( $listing->get_longitude() ) ) {

			// Get position.
			$latitude  = $listing->get_latitude();
			$longitude = $listing->get_longitude();

			if ( $this->scatter ) {
				$longitude += round( wp_rand( -100, 100 ) / 111320, 6 );
				$latitude  += round( wp_rand( -100, 100 ) / 110574, 6 );
			}

			// Set marker.
			$marker = [
				'title'     => $listing->get_title(),
				'latitude'  => $latitude,
				'longitude' => $longitude,

				'content'   => ( new Template(
					[
						'template' => 'listing_map_block',

						'context'  => [
							'listing' => $listing,
						],
					]
				) )->render(),
			];
		}

		return $marker;
	}
}
