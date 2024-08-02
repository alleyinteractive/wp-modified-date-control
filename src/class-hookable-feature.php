<?php
/**
 * Hookable_Feature class file
 *
 * @package wp-modified-date-control
 */

namespace Alley\WP\Modified_Date_Control;

use Alley\WP\Types\Feature;
use Mantle\Support\Traits\Hookable;

/**
 * Feature class that when booted will setup the hooks for the feature via the
 * "Hookable" trait.
 *
 * @link https://mantle.alley.com/docs/features/support/hookable
 */
abstract class Hookable_Feature implements Feature {
	use Hookable;

	/**
	 * Boot the feature.
	 */
	public function boot(): void {
		$this->register_hooks();
	}
}
