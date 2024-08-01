<?php
/**
 * The main plugin function
 *
 * @package wp-modified-date-control
 */

namespace Alley\WP\Modified_Date_Control;

use Alley\WP\Features\Group;

/**
 * Instantiate the plugin.
 */
function main(): void {
	// Add features here.
	$plugin = new Group();

	$plugin->boot();
}
