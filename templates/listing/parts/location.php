<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( '' !== get_post_meta( get_the_ID(), 'hp_location', true ) ) :
	?>
	<div class="hp-listing__location"><i class="hp-icon fas fa-map-marker-alt"></i><?php echo esc_html( get_post_meta( get_the_ID(), 'hp_location', true ) ); ?></div>
	<?php
endif;
