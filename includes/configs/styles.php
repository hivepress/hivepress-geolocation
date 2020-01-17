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
		'handle'  => 'hivepress-geolocation-backend',
		'src'     => hivepress()->get_url( 'geolocation' ) . '/assets/css/backend.min.css',
		'version' => hivepress()->get_version( 'geolocation' ),
		'scope'   => 'backend',
	],

	'geolocation_frontend' => [
		'handle'  => 'hivepress-geolocation-frontend',
		'src'     => hivepress()->get_url( 'geolocation' ) . '/assets/css/frontend.min.css',
		'version' => hivepress()->get_version( 'geolocation' ),
		'scope'   => [ 'frontend', 'editor' ],
	],
];
