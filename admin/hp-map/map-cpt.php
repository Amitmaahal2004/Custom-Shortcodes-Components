<?php
/**
 * Custom Post Type: HP Map Items (district_map)
 *
 * @package HimachalDynamicContentManager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register district_map Custom Post Type under Himachal Dynamic Content Manager.
 */
function csc_register_district_map_cpt() {
	$labels = array(
		'name'                  => _x( 'HP Map Items', 'Post Type General Name', 'custom-shortcodes-components' ),
		'singular_name'         => _x( 'HP Map Item', 'Post Type Singular Name', 'custom-shortcodes-components' ),
		'menu_name'             => __( 'HP Map', 'custom-shortcodes-components' ),
		'name_admin_bar'        => __( 'HP Map Item', 'custom-shortcodes-components' ),
		'archives'              => __( 'HP Map Archives', 'custom-shortcodes-components' ),
		'all_items'             => __( 'All HP Map Items', 'custom-shortcodes-components' ),
		'add_new_item'          => __( 'Add New HP Map Item', 'custom-shortcodes-components' ),
		'add_new'               => __( 'Add New', 'custom-shortcodes-components' ),
		'new_item'              => __( 'New HP Map Item', 'custom-shortcodes-components' ),
		'edit_item'             => __( 'Edit HP Map Item', 'custom-shortcodes-components' ),
		'update_item'           => __( 'Update HP Map Item', 'custom-shortcodes-components' ),
		'view_item'             => __( 'View HP Map Item', 'custom-shortcodes-components' ),
		'view_items'            => __( 'View HP Map Items', 'custom-shortcodes-components' ),
		'search_items'          => __( 'Search HP Map Items', 'custom-shortcodes-components' ),
		'not_found'             => __( 'No HP Map items found', 'custom-shortcodes-components' ),
		'not_found_in_trash'    => __( 'No HP Map items found in Trash', 'custom-shortcodes-components' ),
		'featured_image'        => __( 'District Photo Texture / Image', 'custom-shortcodes-components' ),
		'set_featured_image'    => __( 'Set district photo', 'custom-shortcodes-components' ),
		'remove_featured_image' => __( 'Remove district photo', 'custom-shortcodes-components' ),
		'use_featured_image'    => __( 'Use as district photo', 'custom-shortcodes-components' ),
	);

	$args = array(
		'label'               => __( 'HP Map Item', 'custom-shortcodes-components' ),
		'description'         => __( 'Districts and data points displayed on the interactive Himachal Pradesh map.', 'custom-shortcodes-components' ),
		'labels'              => $labels,
		'supports'            => array( 'title', 'thumbnail', 'page-attributes', 'revisions' ),
		'taxonomies'          => array( 'hp_map_category', 'dashboard_category' ),
		'hierarchical'        => false,
		'public'              => true,
		'show_ui'             => true,
		'show_in_menu'        => 'custom-shortcodes',
		'show_in_nav_menus'   => true,
		'show_in_admin_bar'   => true,
		'show_in_rest'        => true,
		'can_export'          => true,
		'has_archive'         => false,
		'exclude_from_search' => true,
		'publicly_queryable'  => true,
		'capability_type'     => 'post',
		'menu_icon'           => 'dashicons-location',
	);

	register_post_type( 'district_map', $args );
}
add_action( 'init', 'csc_register_district_map_cpt', 9 );

/**
 * Custom columns for district_map admin list table.
 */
function csc_district_map_columns( $columns ) {
	$new_cols = array();
	$new_cols['cb']                    = $columns['cb'];
	$new_cols['title']                 = __( 'District Name', 'custom-shortcodes-components' );
	$new_cols['svg_id']                = __( 'SVG Code', 'custom-shortcodes-components' );
	$new_cols['taxonomy-hp_map_category'] = __( 'Categories', 'custom-shortcodes-components' );
	$new_cols['district_val']          = __( 'Value / Population', 'custom-shortcodes-components' );
	$new_cols['district_status']       = __( 'Status', 'custom-shortcodes-components' );
	$new_cols['date']                  = $columns['date'];
	return $new_cols;
}
add_filter( 'manage_district_map_posts_columns', 'csc_district_map_columns' );

/**
 * Custom column content for district_map admin list table.
 */
function csc_district_map_column_content( $column, $post_id ) {
	switch ( $column ) {
		case 'svg_id':
			$svg_id = get_post_meta( $post_id, '_hdd_district_svg_id', true );
			if ( ! empty( $svg_id ) ) {
				echo '<code style="font-weight:700; background:#e0f2fe; color:#0b3b60; padding:2px 6px; border-radius:3px;">' . esc_html( strtoupper( $svg_id ) ) . '</code>';
			} else {
				echo '<span style="color:#94a3b8;">&mdash;</span>';
			}
			break;

		case 'district_val':
			$val = get_post_meta( $post_id, '_hdd_district_val', true );
			if ( '' !== $val && null !== $val ) {
				echo '<strong style="color:#107c41;">' . esc_html( is_numeric( $val ) ? number_format( (float) $val ) : $val ) . '</strong>';
			} else {
				echo '<span style="color:#94a3b8;">&mdash;</span>';
			}
			break;

		case 'district_status':
			$en = get_post_meta( $post_id, '_hdd_district_enabled', true );
			if ( '0' === $en ) {
				echo '<span style="color:#ef4444; font-weight:600;">' . esc_html__( 'Disabled', 'custom-shortcodes-components' ) . '</span>';
			} else {
				echo '<span style="color:#109b48; font-weight:600;">' . esc_html__( 'Active', 'custom-shortcodes-components' ) . '</span>';
			}
			break;
	}
}
add_action( 'manage_district_map_posts_custom_column', 'csc_district_map_column_content', 10, 2 );
