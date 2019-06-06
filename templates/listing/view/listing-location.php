<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( $listing->get_location() ) :
	?>
	<div class="hp-listing__location"><i class="hp-icon fas fa-map-marker-alt"></i><span><?php echo esc_html( $listing->get_location() ); ?></span></div>
	<?php
endif;
