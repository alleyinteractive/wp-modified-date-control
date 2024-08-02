<?php
/**
 * Entry point "plugin" script registration and enqueue.
 *
 * This file will be copied to the assets output directory
 * with Webpack using wp-scripts build. The build command must
 * be run before this file will be available.
 *
 * This file must be included from the build output directory in a project.
 * and will be loaded from there.
 *
 * @package wp-modified-date-control
 */

/**
 * Register the plugin entry point assets so that they can be enqueued.
 */
function wp_modified_date_control_register_plugin_scripts(): void {
	// Automatically load dependencies and version.
	$asset_file = include __DIR__ . '/index.asset.php';

	// Register the plugin script.
	wp_register_script(
		'wp-modified-date-control-plugin-js',
		plugins_url( 'index.js', __FILE__ ),
		$asset_file['dependencies'],
		$asset_file['version'],
		true
	);
	wp_set_script_translations( 'wp-modified-date-control-plugin-js', 'wp-modified-date-control' );

	// Register the plugin style.
	wp_register_style(
		'wp-modified-date-control-plugin-css',
		plugins_url( 'index.css', __FILE__ ),
		[],
		$asset_file['version'],
	);
}
add_action( 'init', 'wp_modified_date_control_register_plugin_scripts' );

/**
 * Enqueue styles/scripts for the plugin entry point.
 */
function wp_modified_date_control_enqueue_plugin(): void {
	wp_enqueue_script( 'wp-modified-date-control-plugin-js' );
	wp_enqueue_style( 'wp-modified-date-control-plugin-css' );
}
add_action( 'enqueue_block_assets', 'wp_modified_date_control_enqueue_plugin' );
