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
		'src'     => HP_GEOLOCATION_URL . '/assets/js/jquery.geocomplete.min.js',
		'version' => HP_GEOLOCATION_VERSION,
		'scope'   => [ 'frontend', 'backend' ],
	],

	'geolocation_frontend' => [
		'handle'  => 'hp-geolocation-frontend',
		'src'     => HP_GEOLOCATION_URL . '/assets/js/common.min.js',
		'version' => HP_GEOLOCATION_VERSION,
		'deps'    => [ 'hp-core-frontend', 'geocomplete' ],
	],

	'geolocation_backend'  => [
		'handle'  => 'hp-geolocation-backend',
		'src'     => HP_GEOLOCATION_URL . '/assets/js/common.min.js',
		'version' => HP_GEOLOCATION_VERSION,
		'deps'    => [ 'hp-core-backend', 'geocomplete' ],
		'scope'   => 'backend',
	],
];
