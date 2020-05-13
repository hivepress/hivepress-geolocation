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
	'geocomplete'         => [
		'handle'  => 'geocomplete',
		'src'     => hivepress()->get_url( 'geolocation' ) . '/assets/js/jquery.geocomplete.min.js',
		'version' => hivepress()->get_version( 'geolocation' ),
		'scope'   => [ 'frontend', 'backend' ],
	],

	'markerclustererplus' => [
		'handle'  => 'markerclustererplus',
		'src'     => hivepress()->get_url( 'geolocation' ) . '/assets/js/markerclustererplus.min.js',
		'version' => hivepress()->get_version( 'geolocation' ),
		'scope'   => [ 'frontend', 'backend' ],
	],

	'geolocation'         => [
		'handle'  => 'hivepress-geolocation',
		'src'     => hivepress()->get_url( 'geolocation' ) . '/assets/js/common.min.js',
		'version' => hivepress()->get_version( 'geolocation' ),
		'deps'    => [ 'hivepress-core', 'geocomplete', 'markerclustererplus' ],
		'scope'   => [ 'frontend', 'backend' ],
	],
];
