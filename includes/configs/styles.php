<?php
/**
 * Styles configuration.
 *
 * @package HivePress\Configs
 */

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

return [
	'geolocation_backend'  => [
		'handle'  => 'hp-geolocation-backend',
		'src'     => HP_GEOLOCATION_URL . '/assets/css/backend.min.css',
		'version' => HP_GEOLOCATION_VERSION,
		'scope'   => 'backend',
	],

	'geolocation_frontend' => [
		'handle'  => 'hp-geolocation-frontend',
		'src'     => HP_GEOLOCATION_URL . '/assets/css/frontend.min.css',
		'version' => HP_GEOLOCATION_VERSION,
		'scope'   => [ 'frontend', 'editor' ],
	],
];
