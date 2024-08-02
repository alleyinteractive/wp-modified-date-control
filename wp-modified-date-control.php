<?php
/**
 * Plugin Name: Modified Date Control
 * Plugin URI: https://github.com/alleyinteractive/wp-modified-date-control
 * Description: Control the modified date for a post with Gutenberg.
 * Version: 0.0.0
 * Author: Sean Fisher
 * Author URI: https://github.com/alleyinteractive/wp-modified-date-control
 * Requires at least: 6.0
 * Tested up to: 6.6
 *
 * Text Domain: wp-modified-date-control
 *
 * @package wp-modified-date-control
 */

namespace Alley\WP\Modified_Date_Control;

use Alley\WP\Features\Group;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Root directory to this plugin.
 */
define( 'WP_MODIFIED_DATE_CONTROL_DIR', __DIR__ );

// Check if Composer is installed (remove if Composer is not required for your plugin).
if ( ! file_exists( __DIR__ . '/vendor/wordpress-autoload.php' ) ) {
	// Will also check for the presence of an already loaded Composer autoloader
	// to see if the Composer dependencies have been installed in a parent
	// folder. This is useful for when the plugin is loaded as a Composer
	// dependency in a larger project.
	if ( ! class_exists( \Composer\InstalledVersions::class ) ) {
		\add_action(
			'admin_notices',
			function () {
				?>
				<div class="notice notice-error">
					<p><?php esc_html_e( 'Composer is not installed and wp-modified-date-control cannot load. Try using a `*-built` branch if the plugin is being loaded as a submodule.', 'wp-modified-date-control' ); ?></p>
				</div>
				<?php
			}
		);

		return;
	}
} else {
	// Load Composer dependencies.
	require_once __DIR__ . '/vendor/wordpress-autoload.php';
}

// Load the plugin's main files.
require_once __DIR__ . '/src/assets.php';
require_once __DIR__ . '/src/meta.php';

// Load the plugin's assets.
load_scripts();

// Load the plugin's features.
( new Group( new Modified_Date_Feature() ) )->boot();
