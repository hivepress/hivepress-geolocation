<?php
namespace HivePress\Geolocation;

/**
 * Tests geolocation.
 *
 * @class Geolocation_Test
 */
class Geolocation_Test extends \WP_UnitTestCase {

	/**
	 * Post ID.
	 *
	 * @var int
	 */
	public $post_id;

	/**
	 * Setups test.
	 */
	public function setUp() {
		parent::setUp();

		// Create post.
		$this->post_id = $this->factory->post->create( [ 'post_type' => 'hp_listing' ] );
	}

	/**
	 * Tests updating.
	 */
	public function test_updating() {

		// Test if location is updated.
		hivepress()->geolocation->update_location(
			[
				'post_id'   => $this->post_id,
				'location'  => 'Lorem ipsum dolor sit amet consectetuer',
				'latitude'  => 90,
				'longitude' => 90,
			]
		);

		$this->assertEquals( 'Lorem ipsum dolor sit amet consectetuer', get_post_meta( $this->post_id, 'hp_location', true ) );
		$this->assertEquals( '90', get_post_meta( $this->post_id, 'hp_latitude', true ) );
		$this->assertEquals( '90', get_post_meta( $this->post_id, 'hp_longitude', true ) );
	}
}
