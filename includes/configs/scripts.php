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
	'geolocation'          => [
		'handle'  => 'hivepress-geolocation',
		'src'     => hivepress()->get_url( 'geolocation' ) . '/assets/js/common.min.js',
		'version' => hivepress()->get_version( 'geolocation' ),
		'deps'    => [ 'hivepress-core' ],
		'scope'   => [ 'frontend', 'backend' ],

		'data'    => [
			'assetURL' => hivepress()->get_url( 'geolocation' ) . '/assets',
		],
	],

	'geolocation_frontend' => [
		'handle'  => 'hivepress-geolocation-frontend',
		'src'     => hivepress()->get_url( 'geolocation' ) . '/assets/js/frontend.min.js',
		'version' => hivepress()->get_version( 'geolocation' ),
		'deps'    => [ 'hivepress-core' ],
	],
];
