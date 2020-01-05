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
	'geocomplete'        => [
		'handle'  => 'geocomplete',
		'src'     => hivepress()->get_url( 'geolocation' ) . '/assets/js/jquery.geocomplete.min.js',
		'version' => hivepress()->get_version( 'geolocation' ),
		'scope'   => [ 'frontend', 'backend' ],
	],

	'geolocation_common' => [
		'handle'  => 'hp-geolocation-common',
		'src'     => hivepress()->get_url( 'geolocation' ) . '/assets/js/common.min.js',
		'version' => hivepress()->get_version( 'geolocation' ),
		'deps'    => [ 'hp-core-common', 'geocomplete' ],
		'scope'   => [ 'frontend', 'backend' ],
	],
];
