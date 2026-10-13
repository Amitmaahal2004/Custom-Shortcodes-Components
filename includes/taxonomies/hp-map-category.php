<?php
/**
 * Taxonomy: HP Map Categories (hp_map_category)
 *
 * @package HimachalDynamicContentManager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register hp_map_category Custom Taxonomy.
 */
function csc_register_hp_map_category_taxonomy() {
	$labels = array(
		'name'                       => _x( 'HP Map Categories', 'Taxonomy General Name', 'custom-shortcodes-components' ),
		'singular_name'              => _x( 'HP Map Category', 'Taxonomy Singular Name', 'custom-shortcodes-components' ),
		'menu_name'                  => __( 'HP Map Categories', 'custom-shortcodes-components' ),
		'all_items'                  => __( 'All Categories', 'custom-shortcodes-components' ),
		'parent_item'                => __( 'Parent Category', 'custom-shortcodes-components' ),
		'parent_item_colon'          => __( 'Parent Category:', 'custom-shortcodes-components' ),
		'new_item_name'              => __( 'New Category Name', 'custom-shortcodes-components' ),
		'add_new_item'               => __( 'Add New Category', 'custom-shortcodes-components' ),
		'edit_item'                  => __( 'Edit Category', 'custom-shortcodes-components' ),
		'update_item'                => __( 'Update Category', 'custom-shortcodes-components' ),
		'view_item'                  => __( 'View Category', 'custom-shortcodes-components' ),
		'separate_items_with_commas' => __( 'Separate categories with commas', 'custom-shortcodes-components' ),
		'add_or_remove_items'        => __( 'Add or remove categories', 'custom-shortcodes-components' ),
		'choose_from_most_used'      => __( 'Choose from the most used', 'custom-shortcodes-components' ),
		'popular_items'              => __( 'Popular Categories', 'custom-shortcodes-components' ),
		'search_items'               => __( 'Search Categories', 'custom-shortcodes-components' ),
		'not_found'                  => __( 'No categories found', 'custom-shortcodes-components' ),
		'no_terms'                   => __( 'No categories', 'custom-shortcodes-components' ),
		'items_list'                 => __( 'HP Map categories list', 'custom-shortcodes-components' ),
		'items_list_navigation'      => __( 'HP Map categories list navigation', 'custom-shortcodes-components' ),
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
		'rewrite'           => array( 'slug' => 'hp-map-category' ),
	);

	register_taxonomy( 'hp_map_category', array( 'district_map' ), $args );

	// Also support legacy dashboard_category for backward compatibility
	if ( ! taxonomy_exists( 'dashboard_category' ) ) {
		register_taxonomy(
			'dashboard_category',
			array( 'district_map' ),
			array(
				'labels'            => array(
					'name'          => __( 'Legacy Dashboard Categories', 'custom-shortcodes-components' ),
					'singular_name' => __( 'Legacy Dashboard Category', 'custom-shortcodes-components' ),
				),
				'hierarchical'      => true,
				'public'            => false,
				'show_ui'           => false,
				'show_admin_column' => false,
				'show_in_rest'      => true,
				'rewrite'           => false,
			)
		);
	}
}
add_action( 'init', 'csc_register_hp_map_category_taxonomy', 9 );

/**
 * Automatically seed the standard Figma categories if not already present.
 * Ensures the two tabs always exist in the database without requiring manual configuration.
 */
function csc_seed_default_map_categories() {
	if ( ! taxonomy_exists( 'hp_map_category' ) ) {
		return;
	}

	$default_terms = array(
		'demographic-spread-of-minorities' => array(
			'name'        => __( 'Demographic Spread Of Minorities', 'custom-shortcodes-components' ),
			'description' => __( 'Minority demographic distribution across 12 Himachal Pradesh districts.', 'custom-shortcodes-components' ),
		),
		'disabilities-in-hp'               => array(
			'name'        => __( 'Disabilities In H.P', 'custom-shortcodes-components' ),
			'description' => __( 'Disability demographics and beneficiary distribution in H.P.', 'custom-shortcodes-components' ),
		),
	);

	foreach ( $default_terms as $slug => $data ) {
		if ( ! term_exists( $slug, 'hp_map_category' ) ) {
			wp_insert_term(
				$data['name'],
				'hp_map_category',
				array(
					'slug'        => $slug,
					'description' => $data['description'],
				)
			);
		}

		if ( taxonomy_exists( 'dashboard_category' ) && ! term_exists( $slug, 'dashboard_category' ) ) {
			wp_insert_term(
				$data['name'],
				'dashboard_category',
				array(
					'slug'        => $slug,
					'description' => $data['description'],
				)
			);
		}
	}
}
add_action( 'init', 'csc_seed_default_map_categories', 15 );
