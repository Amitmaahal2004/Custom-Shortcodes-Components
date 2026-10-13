<?php
/**
 * Custom Shortcodes & Components Uninstall Handler
 *
 * @package CustomShortcodesComponents
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Safely exit. The plugin is a code-based developer framework and does not store
// configuration in the database or modify user content, posts, or taxonomies.
