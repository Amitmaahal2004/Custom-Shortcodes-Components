<?php
/**
 * Taxonomy: Scheme Categories (csc_scheme_category)
 *
 * @package CustomShortcodesComponents
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register csc_scheme_category Custom Taxonomy.
 */
function csc_register_scheme_category_taxonomy() {
	$labels = array(
		'name'                       => _x( 'Scheme Categories', 'Taxonomy General Name', 'custom-shortcodes-components' ),
		'singular_name'              => _x( 'Scheme Category', 'Taxonomy Singular Name', 'custom-shortcodes-components' ),
		'menu_name'                  => __( 'Scheme Categories', 'custom-shortcodes-components' ),
		'all_items'                  => __( 'All Categories', 'custom-shortcodes-components' ),
		'parent_item'                => __( 'Parent Category', 'custom-shortcodes-components' ),
		'parent_item_colon'          => __( 'Parent Category:', 'custom-shortcodes-components' ),
		'new_item_name'              => __( 'New Scheme Category Name', 'custom-shortcodes-components' ),
		'add_new_item'               => __( 'Add New Scheme Category', 'custom-shortcodes-components' ),
		'edit_item'                  => __( 'Edit Scheme Category', 'custom-shortcodes-components' ),
		'update_item'                => __( 'Update Scheme Category', 'custom-shortcodes-components' ),
		'view_item'                  => __( 'View Category', 'custom-shortcodes-components' ),
		'separate_items_with_commas' => __( 'Separate categories with commas', 'custom-shortcodes-components' ),
		'add_or_remove_items'        => __( 'Add or remove categories', 'custom-shortcodes-components' ),
		'choose_from_most_used'      => __( 'Choose from the most used', 'custom-shortcodes-components' ),
		'popular_items'              => __( 'Popular Categories', 'custom-shortcodes-components' ),
		'search_items'               => __( 'Search Categories', 'custom-shortcodes-components' ),
		'not_found'                  => __( 'No categories found', 'custom-shortcodes-components' ),
		'no_terms'                   => __( 'No categories', 'custom-shortcodes-components' ),
		'items_list'                 => __( 'Scheme categories list', 'custom-shortcodes-components' ),
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
		'rewrite'           => array( 'slug' => 'scheme-category' ),
	);

	register_taxonomy( 'csc_scheme_category', array( 'csc_scheme' ), $args );
}
add_action( 'init', 'csc_register_scheme_category_taxonomy', 9 );
