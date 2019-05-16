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
	'geolocation_frontend' => [
		'handle'  => 'hp-geolocation-frontend',
		'src'     => HP_GEOLOCATION_URL . '/assets/css/frontend.min.css',
		'version' => HP_GEOLOCATION_VERSION,
		'editor'  => true,
	],
];
