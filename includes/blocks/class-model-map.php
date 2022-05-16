<?php
/**
 * Model map block.
 *
 * @package HivePress\Blocks
 */

namespace HivePress\Blocks;

use HivePress\Helpers as hp;
use Hivepress\Models;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Model map block class.
 *
 * @class Model_Map
 */
class Model_Map extends Block {

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

		// Get markers.
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

		// Get post type.
		$post_type = hivepress()->geolocation->get_post_type_name( get_post_type( $post ) );

		// Get template name.
		$template_name = null;

		// Get model.
		$model = null;

		if ( 'listing' === $post_type ) {
			$model         = Models\Listing::query()->get_by_id( $post );
			$template_name = 'listing_map_block';
		} elseif ( 'request' === $post_type ) {
			$model         = Models\Request::query()->get_by_id( $post );
			$template_name = 'request_map_block';
		}

		if ( $model && ! is_null( $model->get_latitude() ) && ! is_null( $model->get_longitude() ) ) {

			// Get model context.
			$context = [];

			if ( 'listing' === $post_type ) {
				$context = [
					'listing' => $model,
				];
			} elseif ( 'request' === $post_type ) {
				$context = [
					'request' => $model,
				];
			}

			// Get position.
			$latitude  = $model->get_latitude();
			$longitude = $model->get_longitude();

			if ( $this->scatter ) {
				$longitude += round( wp_rand( -100, 100 ) / 111320, 6 );
				$latitude  += round( wp_rand( -100, 100 ) / 110574, 6 );
			}

			// Set marker.
			$marker = [
				'title'     => $model->get_title(),
				'latitude'  => $latitude,
				'longitude' => $longitude,

				'content'   => ( new Template(
					[
						'template' => $template_name,

						'context'  => $context,
					]
				) )->render(),
			];
		}

		return $marker;
	}
}
