<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<div class="hp-listing__location"><i class="hp-icon fas fa-map-marker-alt"></i><?php echo get_post_meta( get_the_ID(), 'hp_location', true ); ?></div>
