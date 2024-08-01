<?php
/**
 * Modified Date Control Tests: Bootstrap
 *
 * phpcs:disable Squiz.Commenting.InlineComment.InvalidEndChar
 *
 * @package wp-modified-date-control
 */

/**
 * Visit {@see https://mantle.alley.com/testing/test-framework.html} to learn more.
 */
\Mantle\Testing\manager()
	// Rsync the plugin to plugins/wp-modified-date-control when testing.
	->maybe_rsync_plugin()
	// Load the main file of the plugin.
	->loaded( fn () => require_once __DIR__ . '/../wp-modified-date-control.php' )
	->install();
