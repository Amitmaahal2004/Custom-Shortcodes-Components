<?php
/**
 * Taxonomy: Marquee Categories (csc_marquee_category)
 *
 * @package CustomShortcodesComponents
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register csc_marquee_category Custom Taxonomy.
 */
function csc_register_marquee_category_taxonomy() {
	$labels = array(
		'name'                       => _x( 'Marquee Categories', 'Taxonomy General Name', 'custom-shortcodes-components' ),
		'singular_name'              => _x( 'Marquee Category', 'Taxonomy Singular Name', 'custom-shortcodes-components' ),
		'menu_name'                  => __( 'Marquee Categories', 'custom-shortcodes-components' ),
		'all_items'                  => __( 'All Categories', 'custom-shortcodes-components' ),
		'parent_item'                => __( 'Parent Category', 'custom-shortcodes-components' ),
		'parent_item_colon'          => __( 'Parent Category:', 'custom-shortcodes-components' ),
		'new_item_name'              => __( 'New Marquee Category Name', 'custom-shortcodes-components' ),
		'add_new_item'               => __( 'Add New Marquee Category', 'custom-shortcodes-components' ),
		'edit_item'                  => __( 'Edit Marquee Category', 'custom-shortcodes-components' ),
		'update_item'                => __( 'Update Marquee Category', 'custom-shortcodes-components' ),
		'view_item'                  => __( 'View Category', 'custom-shortcodes-components' ),
		'separate_items_with_commas' => __( 'Separate categories with commas', 'custom-shortcodes-components' ),
		'add_or_remove_items'        => __( 'Add or remove categories', 'custom-shortcodes-components' ),
		'choose_from_most_used'      => __( 'Choose from the most used', 'custom-shortcodes-components' ),
		'popular_items'              => __( 'Popular Categories', 'custom-shortcodes-components' ),
		'search_items'               => __( 'Search Categories', 'custom-shortcodes-components' ),
		'not_found'                  => __( 'No categories found', 'custom-shortcodes-components' ),
		'no_terms'                   => __( 'No categories', 'custom-shortcodes-components' ),
		'items_list'                 => __( 'Marquee categories list', 'custom-shortcodes-components' ),
		'items_list_navigation'      => __( 'Categories list navigation', 'custom-shortcodes-components' ),
	);

	$args = array(
		'labels'            => $labels,
		'hierarchical'      => true,
		'public'            => true,
		'show_ui'           => true,
		'show_admin_column' => true,
		'show_in_nav_menus' => true,
		'show_tagcloud'     => false,
		'show_in_rest'      => true,
		'rewrite'           => array( 'slug' => 'marquee-category' ),
	);

	register_taxonomy( 'csc_marquee_category', array( 'csc_marquee' ), $args );
}
add_action( 'init', 'csc_register_marquee_category_taxonomy', 9 );
