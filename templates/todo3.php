<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// todo.
$data = [];

while ( have_posts() ) :
	the_post();

	$latitude  = get_post_meta( get_the_ID(), 'hp_latitude', true );
	$longitude = get_post_meta( get_the_ID(), 'hp_longitude', true );

	if ( $latitude && $longitude ) :
		$data[] = [
			'position' => [
				'lat' => floatval($latitude),
				'lng' => floatval($longitude),
			],
			'title'    => get_the_title(),
			'content'  => 'todo',
		];
	endif;
endwhile;

rewind_posts();

if ( have_posts() ) :
	?>
	<div class="hp-widget widget">
		<div class="hp-js-map" data-todo="<?php echo esc_attr( wp_json_encode( $data ) ); ?>" style="height:500px;"></div>
	</div>
	<?php
endif;
