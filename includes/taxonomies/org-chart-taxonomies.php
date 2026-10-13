<?php
/**
 * Taxonomies: Organization Chart Categories & Hierarchy Levels
 *
 * @package CustomShortcodesComponents
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register Taxonomies for csc_org_member.
 */
function csc_register_org_chart_taxonomies() {
	// 1. Organization Category / Department / Board taxonomy
	$cat_labels = array(
		'name'                       => _x( 'Org Chart Categories', 'Taxonomy General Name', 'custom-shortcodes-components' ),
		'singular_name'              => _x( 'Org Chart Category', 'Taxonomy Singular Name', 'custom-shortcodes-components' ),
		'menu_name'                  => __( 'Org Categories', 'custom-shortcodes-components' ),
		'all_items'                  => __( 'All Org Categories', 'custom-shortcodes-components' ),
		'parent_item'                => __( 'Parent Category', 'custom-shortcodes-components' ),
		'parent_item_colon'          => __( 'Parent Category:', 'custom-shortcodes-components' ),
		'new_item_name'              => __( 'New Category Name', 'custom-shortcodes-components' ),
		'add_new_item'               => __( 'Add New Org Category', 'custom-shortcodes-components' ),
		'edit_item'                  => __( 'Edit Org Category', 'custom-shortcodes-components' ),
		'update_item'                => __( 'Update Org Category', 'custom-shortcodes-components' ),
		'view_item'                  => __( 'View Category', 'custom-shortcodes-components' ),
		'search_items'               => __( 'Search Categories', 'custom-shortcodes-components' ),
		'not_found'                  => __( 'No categories found', 'custom-shortcodes-components' ),
	);

	register_taxonomy(
		'csc_org_category',
		array( 'csc_org_member' ),
		array(
			'labels'            => $cat_labels,
			'hierarchical'      => true,
			'public'            => true,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_nav_menus' => true,
			'show_tagcloud'     => false,
			'show_in_rest'      => true,
			'rewrite'           => array( 'slug' => 'org-category' ),
		)
	);

	// 2. Hierarchy Level / Tier taxonomy
	$level_labels = array(
		'name'                       => _x( 'Hierarchy Levels', 'Taxonomy General Name', 'custom-shortcodes-components' ),
		'singular_name'              => _x( 'Hierarchy Level', 'Taxonomy Singular Name', 'custom-shortcodes-components' ),
		'menu_name'                  => __( 'Hierarchy Levels', 'custom-shortcodes-components' ),
		'all_items'                  => __( 'All Levels', 'custom-shortcodes-components' ),
		'parent_item'                => __( 'Parent Level', 'custom-shortcodes-components' ),
		'parent_item_colon'          => __( 'Parent Level:', 'custom-shortcodes-components' ),
		'new_item_name'              => __( 'New Level Name', 'custom-shortcodes-components' ),
		'add_new_item'               => __( 'Add New Level', 'custom-shortcodes-components' ),
		'edit_item'                  => __( 'Edit Level', 'custom-shortcodes-components' ),
		'update_item'                => __( 'Update Level', 'custom-shortcodes-components' ),
		'view_item'                  => __( 'View Level', 'custom-shortcodes-components' ),
		'search_items'               => __( 'Search Levels', 'custom-shortcodes-components' ),
		'not_found'                  => __( 'No levels found', 'custom-shortcodes-components' ),
	);

	register_taxonomy(
		'csc_org_level',
		array( 'csc_org_member' ),
		array(
			'labels'            => $level_labels,
			'hierarchical'      => true,
			'public'            => true,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_nav_menus' => true,
			'show_tagcloud'     => false,
			'show_in_rest'      => true,
			'rewrite'           => array( 'slug' => 'org-level' ),
		)
	);
}
add_action( 'init', 'csc_register_org_chart_taxonomies', 9 );
