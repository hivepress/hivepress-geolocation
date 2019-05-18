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
		$attributes = [];

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

		// Set attributes.
		$attributes['data-component'] = 'map';

		if ( ! empty( $markers ) ) {
			$attributes['data-markers'] = wp_json_encode( $markers );
		}

		$this->attributes = hp\merge_arrays( $this->attributes, $attributes );

		parent::bootstrap();
	}

	/**
	 * Renders block HTML.
	 *
	 * @return string
	 */
	public function render() {
		return '<div ' . hp\html_attributes( $this->attributes ) . '></div>';
	}
}
