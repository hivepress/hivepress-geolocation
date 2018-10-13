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

		add_filter('hivepress/form/field_html/location', [$this, 'render_field'], 10, 4);

		add_action('wp_footer', [$this, 'load_map']);
	}

	public function render_field($output, $id, $args, $value) {
		$output.=hivepress()->form->render_field($id.'[name]', [
			'placeholder' => $args['placeholder'],
			'type' => 'text',
			'attributes' => [
				'class' => 'hp-js-geocomplete',
			],
		]);

		$output.='<a href="#" class="hp-js-geolocate"><i class="fas fa-location-arrow"></i></a>';

		return $output;
	}

	public function load_map() {
		echo '<script async defer src="https://maps.googleapis.com/maps/api/js?libraries=places&key=AIzaSyCE3F4hgEDS96gT1QbrMxGt0v-CIPlbUbY&callback=initMap"
  type="text/javascript"></script>';
	}
}
