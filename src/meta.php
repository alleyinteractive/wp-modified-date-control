<?php
/**
 * Contains functions for working with meta.
 *
 * @package wp-modified-date-control
 */

namespace Alley\WP\Modified_Date_Control;

/**
 * Meta key to allow updates to the modified date.
 */
const META_KEY_ALLOW_UPDATES = 'wp_modified_date_control_allow_updates';

// Register the meta for the plugin.
register_meta( 'post', META_KEY_ALLOW_UPDATES, [
	'type'         => 'boolean',
	'description'  => __( 'Allow updates to the modified date.', 'wp-modified-date-control' ),
	'single'       => true,
	'show_in_rest' => true,

	/**
	 * Filter the default value for allowing updates to the modified date.
	 *
	 * @param bool $default The default value.
	 */
	'default'      => (bool) apply_filters( 'wp_modified_date_control_default_allow_updates', true ),
] );
