<?php
/**
 * Modified_Date_Feature class file
 *
 * @package wp-modified-date-control
 */

namespace Alley\WP\Modified_Date_Control;

use Mantle\Support\Attributes\Filter;
use WP_REST_Request;

/**
 * Modified Date Control Feature
 */
class Modified_Date_Feature extends Hookable_Feature {
	/**
	 * REST Request from the dispatcher.
	 *
	 * @var WP_REST_Request|null
	 */
	protected ?WP_REST_Request $rest_request = null;

	/**
	 * Listen for REST requests to update the modified date.
	 *
	 * @param mixed           $pre The pre value.
	 * @param WP_REST_Request $data The request data.
	 * @param string          $route The route.
	 * @return mixed
	 */
	#[Filter( 'rest_dispatch_request', 999 )]
	public function listen_for_rest_update( mixed $pre, WP_REST_Request $data, string $route ): mixed {
		// Ignore non-/wp/v2/posts requests.
		if ( ! str_starts_with( $route, '/wp/v2/posts' ) ) {
			return $pre;
		}

		$this->rest_request = $data;

		return $pre;
	}

	/**
	 * Reset the REST request after callbacks have run.
	 *
	 * @param mixed $pre The pre value.
	 * @return mixed
	 */
	#[Filter( 'rest_request_after_callbacks' )]
	public function clear_rest_request_after_complete( mixed $pre ): mixed {
		$this->rest_request = null;

		return $pre;
	}

	/**
	 * Filter the post data before it is inserted into the database.
	 *
	 * @param array<mixed> $data    The post data to insert.
	 * @param array<mixed> $postarr The raw post data.
	 * @return array<mixed>
	 */
	#[Filter( 'wp_insert_post_data' )]
	public function filter_insert_data( $data, $postarr ): array {
		// Ignore updates without a post ID, if the post is not published, OR if
		// the post type is not queryable.
		if (
			! isset( $postarr['ID'] )
			|| ! $postarr['ID']
			|| ( isset( $data['post_status'] ) && 'publish' !== $data['post_status'] )
			|| ( isset( $data['post_type'] ) && ! get_post_type_object( $data['post_type'] )?->public )
		) {
			return $data;
		}

		if ( is_int( $postarr['ID'] ) && $this->should_prevent_updates( $postarr['ID'] ) ) {
			// Prevent updates to the modified date.
			unset( $data['post_modified'], $data['post_modified_gmt'] );

			// Check if the modified date was passed in the REST API request.
			if (
				$this->rest_request
				&& isset( $this->rest_request['id'] )
				&& $this->rest_request['id'] === $postarr['ID']
				&& isset( $this->rest_request['modified'] )
			) {
				$data['post_modified']     = $this->rest_request['modified'];
				$data['post_modified_gmt'] = get_gmt_from_date( $this->rest_request['modified'] );
			}
		}

		return $data;
	}

	/**
	 * Determine if updates should be prevented.
	 *
	 * @param int $post_id The post ID.
	 * @return bool
	 */
	protected function should_prevent_updates( int $post_id ): bool {
		// Check if the REST request is present and is for this post. If so, use the
		// request meta value if it was passed.
		if (
			$this->rest_request
			&& isset( $this->rest_request['id'] )
			&& $this->rest_request['id'] === $post_id
			&& isset( $this->rest_request['meta'] )
			&& isset( $this->rest_request['meta'][ META_KEY_ALLOW_UPDATES ] )
		) {
			$value = 'false' === $this->rest_request['meta'][ META_KEY_ALLOW_UPDATES ];
		} else {
			$value = 'false' === get_post_meta( $post_id, META_KEY_ALLOW_UPDATES, true );
		}

		/**
		 * Filter the value to allow updates to the modified date.
		 *
		 * @param bool $value          The value to allow updates.
		 * @param int  $post_id        The post ID.
		 * @param WP_REST_Request|null $request The REST request.
		 */
		return apply_filters( 'wp_modified_date_control_prevent_updates', $value, $post_id, $this->rest_request );
	}
}
