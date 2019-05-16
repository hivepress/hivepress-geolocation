<?php
/**
 * Scripts configuration.
 *
 * @package HivePress\Configs
 */

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

return [
	'geocomplete'          => [
		'handle'  => 'geocomplete',
		'src'     => HP_GEOLOCATION_URL . '/assets/js/geocomplete.min.js',
		'version' => HP_GEOLOCATION_VERSION,
	],

	'geolocation_frontend' => [
		'handle'  => 'hp-geolocation-frontend',
		'src'     => HP_GEOLOCATION_URL . '/assets/js/frontend.min.js',
		'version' => HP_GEOLOCATION_VERSION,
		'deps'    => [ 'geocomplete' ],
	],
];
