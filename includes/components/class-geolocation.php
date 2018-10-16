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

		add_filter( 'hivepress/form/field_html/location', [ $this, 'render_field' ], 10, 4 );

		add_action( 'wp_footer', [ $this, 'load_map' ] );
	}

	public function render_field( $output, $id, $args, $value ) {

		// Render HTML attributes.
		$attributes = hp_replace_placeholders( $args, hp_html_attributes( $args['attributes'] ) );

		$output .= '<div ' . $attributes . '>';

		// Render location field.
		$output .= hivepress()->form->render_field($id, [
				//'placeholder' => $args['placeholder'],
				'type'        => 'text',
				'default' => $value,
				'attributes'  => [
					'class' => 'hp-js-geocomplete',
				],
			]
		);

		// Render location.
		$output .= '<a href="#" title="'.esc_attr__('Locate Me', 'hivepress-geolocation').'" class="hp-js-geolocate"><i class="fas fa-location-arrow"></i></a>';

		$output .= '</div>';

		return $output;
	}

	public function load_map() {
		echo '<script async defer src="https://maps.googleapis.com/maps/api/js?libraries=places&key=AIzaSyCE3F4hgEDS96gT1QbrMxGt0v-CIPlbUbY&callback=initMap"
  type="text/javascript"></script>';
	}
}
