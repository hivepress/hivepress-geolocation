<?php
/**
 * Map block.
 *
 * @package HivePress\Blocks
 */

namespace HivePress\Blocks;

use HivePress\Helpers as hp;

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
	protected function bootstrap() {

		// Set attributes.
		$this->attributes = hp\merge_arrays(
			$this->attributes,
			[
				'class' => [ 'hp-map' ],
			]
		);

		parent::bootstrap();
	}

	/**
	 * Renders block HTML.
	 *
	 * @return string
	 */
	public function render() {
		$output = '';

		// Get markers.
		$markers = [];

		rewind_posts();

		while ( have_posts() ) {
			the_post();

			// Get coordinates.
			$latitude  = get_post_meta( get_the_ID(), 'hp_latitude', true );
			$longitude = get_post_meta( get_the_ID(), 'hp_longitude', true );

			// Add marker.
			if ( '' !== $latitude && '' !== $longitude ) {
				$markers[] = [
					'title'     => esc_html( get_the_title() ),
					'content'   => '<h4><a href="' . esc_url( get_permalink( get_the_ID() ) ) . '">' . esc_html( get_the_title() ) . '</a></h4>',
					'latitude'  => round( floatval( $latitude ), 6 ),
					'longitude' => round( floatval( $longitude ), 6 ),
				];
			}
		}

		rewind_posts();

		if ( ! empty( $markers ) ) {
			$output .= '<div data-component="map" data-markers="' . esc_attr( wp_json_encode( $markers ) ) . '" ' . hp\html_attributes( $this->attributes ) . '></div>';
		}

		return $output;
	}
}
