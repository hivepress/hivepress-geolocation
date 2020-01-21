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

			// Get markers.
			$markers = [];

			rewind_posts();

			while ( have_posts() ) {
				the_post();

				// Get listing.
				$listing = Models\Listing::query()->get_by_id( get_post() );

				if ( $listing && ! is_null( $listing->get_latitude() ) && ! is_null( $listing->get_longitude() ) ) {

					// Add marker.
					$markers[] = [
						'title'     => esc_html( $listing->get_title() ),
						'content'   => '<h5><a href="' . esc_url( hivepress()->router->get_url( 'listing_view_page', [ 'listing_id' => $listing->get_id() ] ) ) . '">' . esc_html( $listing->get_title() ) . '</a></h5>',
						'latitude'  => $listing->get_latitude(),
						'longitude' => $listing->get_longitude(),
					];
				}
			}

			rewind_posts();

			if ( $markers ) {
				$output .= '<div data-markers="' . esc_attr( wp_json_encode( $markers ) ) . '" ' . hp\html_attributes( $this->attributes ) . '></div>';
			}
		}

		return $output;
	}
}
