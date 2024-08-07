<?php
/**
 * ManagerTest class file
 *
 * phpcs:disable Squiz.Commenting.VariableComment.Missing
 *
 * @package wp-modified-date-control
 */

namespace Alley\WP\Modified_Date_Control\Tests\Feature;

use Alley\WP\Modified_Date_Control\Tests\TestCase;
use Carbon\Carbon;
use Mantle\Database\Model\Post;

use const Alley\WP\Modified_Date_Control\META_KEY_ALLOW_UPDATES;

/**
 * A test suite for setting the modified date with the plugin.
 */
class ManagerTest extends TestCase {
	public const ORIGINAL_DATE = '2021-01-01 00:00:00';

	public const DATE_FORMAT = 'Y-m-d H:i:s';

	protected int $post_id;

	/**
	 * Set up the test suite.
	 */
	protected function setUp(): void {
		parent::setUp();

		update_option( 'timezone_string', 'America/New_York' );

		// Create a post with a modified date.
		$this->post_id = $this->factory->post->create( [
			'post_modified' => self::ORIGINAL_DATE,
		] );

		$this->assertEquals( self::ORIGINAL_DATE, get_post( $this->post_id )->post_modified );
	}

	/**
	 * Ensure that a post can be saved normally and the modified date is updated.
	 */
	public function test_it_can_save_a_post_normally() {
		$this->acting_as( 'administrator' );

		$this->post( rest_url( 'wp/v2/posts/' . $this->post_id ), [
			'title' => 'Updated Post Title',
		] );

		$this->assertTrue(
			Post::whereModifiedDate( self::ORIGINAL_DATE )->doesntExist(),
		);

		// Potentially flaky test due to time comparison but we'll take it.
		$this->assertTrue(
			Carbon::parse( get_the_modified_date( self::DATE_FORMAT, $this->post_id ) )->isToday(),
		);
	}

	/**
	 * Ensure that a post can be prevent a modified date from being updated when
	 * updates are prevented.
	 */
	public function test_it_can_prevent_modified_date_from_being_updated() {
		$this->acting_as( 'administrator' );

		update_post_meta( $this->post_id, META_KEY_ALLOW_UPDATES, 'false' );

		$this->post( rest_url( 'wp/v2/posts/' . $this->post_id ), [
			'title' => 'Updated Post Title',
		] );

		$this->assertEquals( get_the_modified_date( self::DATE_FORMAT, $this->post_id ), self::ORIGINAL_DATE );
	}

	/**
	 * Ensure that a post can be updated when updates are allowed.
	 */
	public function test_it_will_bump_the_modified_date_when_allowing_updates() {
		$this->acting_as( 'administrator' );

		update_post_meta( $this->post_id, META_KEY_ALLOW_UPDATES, 'false' );

		$this->post( rest_url( 'wp/v2/posts/' . $this->post_id ), [
			'title' => 'Updated Post Title',
			'meta'  => [
				META_KEY_ALLOW_UPDATES => 'true',
			],
		] );

		$this->assertNotEquals( get_the_modified_date( self::DATE_FORMAT, $this->post_id ), self::ORIGINAL_DATE );

		// Potentially flaky test due to time comparison but we'll take it.
		$this->assertTrue(
			Carbon::parse( get_the_modified_date( self::DATE_FORMAT, $this->post_id ) )->isToday(),
		);
	}

	/**
	 * Ensure that a post can be updated when updates are allowed.
	 */
	public function test_it_can_set_the_modified_date_from_the_rest_api() {
		$this->acting_as( 'administrator' );

		$expected = Carbon::now( 'America/New_York' )->setDateTime( 2023, 10, 3, 8, 35, 0, 0 );

		$this->post( rest_url( 'wp/v2/posts/' . $this->post_id ), [
			'title'    => 'Updated Post Title',
			'modified' => $expected->format( self::DATE_FORMAT ),
			'meta'     => [
				META_KEY_ALLOW_UPDATES => 'false',
			],
		] );

		$this->assertEquals(
			$expected->format( self::DATE_FORMAT ),
			get_the_modified_date( self::DATE_FORMAT, $this->post_id ),
		);

		$this->assertEquals(
			$expected->setTimezone( 'UTC' )->format( self::DATE_FORMAT ),
			get_post_modified_time( self::DATE_FORMAT, true, $this->post_id ),
		);
	}

	/**
	 * Ensure that a post will ignore modified date passed when updates are
	 * allowed from already-set meta.
	 */
	public function test_it_ignores_modified_date_passed_when_allowing_updates_set() {
		$this->acting_as( 'administrator' );

		update_post_meta( $this->post_id, META_KEY_ALLOW_UPDATES, 'true' );

		$this->post( rest_url( 'wp/v2/posts/' . $this->post_id ), [
			'title'    => 'Updated Post Title',
			'modified' => '2023-10-03 08:35:00',
		] );

		$this->assertNotEquals( '2023-10-03 08:35:00', get_the_modified_date( self::DATE_FORMAT, $this->post_id ) );
	}

	/**
	 * Ensure that a post will ignore modified date passed when updates are
	 * allowed from passed meta to the REST API.
	 */
	public function test_it_ignores_modified_date_passed_when_allowing_updates_passed() {
		$this->acting_as( 'administrator' );

		$this->post( rest_url( 'wp/v2/posts/' . $this->post_id ), [
			'title'    => 'Updated Post Title',
			'modified' => '2023-10-03 08:35:00',
			'meta'     => [
				META_KEY_ALLOW_UPDATES => 'true',
			],
		] );

		$this->assertNotEquals( '2023-10-03 08:35:00', get_the_modified_date( self::DATE_FORMAT, $this->post_id ) );
	}

	/**
	 * Ensure that a post will not update the modified date when a post is updated outside of the REST API.
	 */
	public function test_it_can_prevent_modified_date_update_outside_of_the_rest_api() {
		update_post_meta( $this->post_id, META_KEY_ALLOW_UPDATES, 'false' );

		wp_update_post( [
			'ID'    => $this->post_id,
			'title' => 'Updated Post Title',
		] );

		$this->assertEquals( self::ORIGINAL_DATE, get_post( $this->post_id )->post_modified );
	}
}
