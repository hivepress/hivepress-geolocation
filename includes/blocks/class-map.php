<?php
/**
 * Map block.
 *
 * @package HivePress\Blocks
 */

namespace HivePress\Blocks;

use HivePress\Helpers as hp;
use Hivepress\Models;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Map block class.
 *
 * @class Map
 */
class Map extends Block {

	/**
	 * Map attributes.
	 *
	 * @var array
	 */
	protected $attributes = [];

	/**
	 * Bootstraps block properties.
	 */
	protected function boot() {

		// Set attributes.
		$this->attributes = hp\merge_arrays(
			$this->attributes,
			[
				'class'          => [ 'hp-map' ],
				'data-component' => 'map',
			]
		);

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
				$output .= '<div data-markers="' . esc_attr( wp_json_encode( $markers ) ) . '" ' . hp\html_attributes( $this->attributes ) . '></div>';
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

			// Set marker.
			$marker = [
				'title'     => esc_html( $listing->get_title() ),
				'latitude'  => $listing->get_latitude(),
				'longitude' => $listing->get_longitude(),

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
