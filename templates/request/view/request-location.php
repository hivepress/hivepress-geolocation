<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( $request->get_location() ) :
	?>
	<div class="hp-request__location">
		<i class="hp-icon fas fa-map-marker-alt"></i>
		<?php if ( get_option( 'hp_geolocation_hide_address' ) ) : ?>
			<span><?php esc_html_e( preg_replace( '/\d+/u', '', $request->get_location() ) ); ?></span>
		<?php else : ?>
			<a href="
			<?php
			echo esc_url(
				hivepress()->router->get_url(
					'model_view_page',
					[
						'latitude'  => $request->get_latitude(),
						'longitude' => $request->get_longitude(),
					]
				)
			);
			?>
						" target="_blank"><?php echo esc_html( $request->get_location() ); ?></a>
		<?php endif; ?>
	</div>
	<?php
endif;
