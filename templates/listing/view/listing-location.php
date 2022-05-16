<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( $listing->get_location() ) :
	?>
	<div class="hp-listing__location">
		<i class="hp-icon fas fa-map-marker-alt"></i>
		<?php
		if ( get_option( 'hp_geolocation_hide_address' ) ) :

			$location = [];

			$post_location = hivepress()->helper->get_first_array_value( wp_get_post_terms( $listing->get_id(), 'hp_listing_region' ) );

			$post_location_parents = get_ancestors( $post_location->term_id, 'hp_listing_region' );

			if ( $post_location_parents ) {
				$location = array_merge(
					[ $post_location->name ],
					get_terms(
						[
							'taxonomy' => 'hp_listing_region',
							'fields'   => 'names',
							'orderby'  => 'include',
							'include'  => get_ancestors( $post_location->term_id, 'hp_listing_region' ),
						]
					)
				);
			}
			?>
			<span><?php echo esc_html( implode( ', ', $location ) ); ?></span>
		<?php else : ?>
			<a href="
			<?php
			echo esc_url(
				hivepress()->router->get_url(
					'model_view_page',
					[
						'latitude'  => $listing->get_latitude(),
						'longitude' => $listing->get_longitude(),
					]
				)
			);
			?>
						" target="_blank"><?php echo esc_html( $listing->get_location() ); ?></a>
		<?php endif; ?>
	</div>
	<?php
endif;
