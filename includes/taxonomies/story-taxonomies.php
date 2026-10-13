<?php
/**
 * Taxonomies: Story Categories & Story Districts
 *
 * @package CustomShortcodesComponents
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register Taxonomies for csc_story.
 */
function csc_register_story_taxonomies() {
	// 1. Story Categories Taxonomy
	$cat_labels = array(
		'name'                       => _x( 'Story Categories', 'Taxonomy General Name', 'custom-shortcodes-components' ),
		'singular_name'              => _x( 'Story Category', 'Taxonomy Singular Name', 'custom-shortcodes-components' ),
		'menu_name'                  => __( 'Story Categories', 'custom-shortcodes-components' ),
		'all_items'                  => __( 'All Story Categories', 'custom-shortcodes-components' ),
		'parent_item'                => __( 'Parent Category', 'custom-shortcodes-components' ),
		'parent_item_colon'          => __( 'Parent Category:', 'custom-shortcodes-components' ),
		'new_item_name'              => __( 'New Category Name', 'custom-shortcodes-components' ),
		'add_new_item'               => __( 'Add New Story Category', 'custom-shortcodes-components' ),
		'edit_item'                  => __( 'Edit Story Category', 'custom-shortcodes-components' ),
		'update_item'                => __( 'Update Story Category', 'custom-shortcodes-components' ),
		'view_item'                  => __( 'View Category', 'custom-shortcodes-components' ),
		'search_items'               => __( 'Search Categories', 'custom-shortcodes-components' ),
		'not_found'                  => __( 'No categories found', 'custom-shortcodes-components' ),
	);

	register_taxonomy(
		'csc_story_category',
		array( 'csc_story' ),
		array(
			'labels'            => $cat_labels,
			'hierarchical'      => true,
			'public'            => true,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_nav_menus' => true,
			'show_tagcloud'     => false,
			'show_in_rest'      => true,
			'rewrite'           => array( 'slug' => 'story-category' ),
		)
	);

	// 2. Story Districts Taxonomy (Chamba, Hamirpur, Sirmour, Solan, Una, etc.)
	$district_labels = array(
		'name'                       => _x( 'Districts', 'Taxonomy General Name', 'custom-shortcodes-components' ),
		'singular_name'              => _x( 'District', 'Taxonomy Singular Name', 'custom-shortcodes-components' ),
		'menu_name'                  => __( 'Districts', 'custom-shortcodes-components' ),
		'all_items'                  => __( 'All Districts', 'custom-shortcodes-components' ),
		'parent_item'                => __( 'Parent District', 'custom-shortcodes-components' ),
		'parent_item_colon'          => __( 'Parent District:', 'custom-shortcodes-components' ),
		'new_item_name'              => __( 'New District Name', 'custom-shortcodes-components' ),
		'add_new_item'               => __( 'Add New District', 'custom-shortcodes-components' ),
		'edit_item'                  => __( 'Edit District', 'custom-shortcodes-components' ),
		'update_item'                => __( 'Update District', 'custom-shortcodes-components' ),
		'view_item'                  => __( 'View District', 'custom-shortcodes-components' ),
		'search_items'               => __( 'Search Districts', 'custom-shortcodes-components' ),
		'not_found'                  => __( 'No districts found', 'custom-shortcodes-components' ),
	);

	register_taxonomy(
		'csc_story_district',
		array( 'csc_story' ),
		array(
			'labels'            => $district_labels,
			'hierarchical'      => true,
			'public'            => true,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_nav_menus' => true,
			'show_tagcloud'     => false,
			'show_in_rest'      => true,
			'rewrite'           => array( 'slug' => 'district' ),
		)
	);
}
add_action( 'init', 'csc_register_story_taxonomies', 9 );
