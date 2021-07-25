<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( $listing->get_location() ) :
	?>
	<div class="hp-listing__location">
		<i class="hp-icon fas fa-map-marker-alt"></i>
		<?php if ( get_option( 'hp_geolocation_hide_address' ) ) : ?>
			<span><?php echo esc_html( $listing->get_location() ); ?></span>
		<?php else : ?>
			<a href="<?php echo esc_url( hivepress()->router->get_url( 'location_view_page', [ 'latitude' => $listing->get_latitude(), 'longitude' => $listing->get_longitude() ] ) ); ?>" target="_blank"><?php echo esc_html( $listing->get_location() ); ?></a>
		<?php endif; ?>
	</div>
	<?php
endif;
