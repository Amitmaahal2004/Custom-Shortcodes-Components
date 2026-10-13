<?php
/**
 * Custom Post Type: Marquee Items (csc_marquee)
 *
 * @package CustomShortcodesComponents
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register csc_marquee Custom Post Type.
 */
function csc_register_marquee_cpt() {
	$labels = array(
		'name'                  => _x( 'Marquee Items', 'Post Type General Name', 'custom-shortcodes-components' ),
		'singular_name'         => _x( 'Marquee Item', 'Post Type Singular Name', 'custom-shortcodes-components' ),
		'menu_name'             => __( 'Marquee Items', 'custom-shortcodes-components' ),
		'name_admin_bar'        => __( 'Marquee Item', 'custom-shortcodes-components' ),
		'archives'              => __( 'Marquee Archives', 'custom-shortcodes-components' ),
		'attributes'            => __( 'Item Attributes', 'custom-shortcodes-components' ),
		'parent_item_colon'     => __( 'Parent Item:', 'custom-shortcodes-components' ),
		'all_items'             => __( 'Marquee Items', 'custom-shortcodes-components' ),
		'add_new_item'          => __( 'Add New Marquee Item', 'custom-shortcodes-components' ),
		'add_new'               => __( 'Add New Item', 'custom-shortcodes-components' ),
		'new_item'              => __( 'New Marquee Item', 'custom-shortcodes-components' ),
		'edit_item'             => __( 'Edit Marquee Item', 'custom-shortcodes-components' ),
		'update_item'           => __( 'Update Marquee Item', 'custom-shortcodes-components' ),
		'view_item'             => __( 'View Marquee Item', 'custom-shortcodes-components' ),
		'view_items'            => __( 'View Marquee Items', 'custom-shortcodes-components' ),
		'search_items'          => __( 'Search Marquee Items', 'custom-shortcodes-components' ),
		'not_found'             => __( 'No marquee items found', 'custom-shortcodes-components' ),
		'not_found_in_trash'    => __( 'No marquee items found in Trash', 'custom-shortcodes-components' ),
		'featured_image'        => __( 'Item Image / Logo', 'custom-shortcodes-components' ),
		'set_featured_image'    => __( 'Set image or logo', 'custom-shortcodes-components' ),
		'remove_featured_image' => __( 'Remove image / logo', 'custom-shortcodes-components' ),
		'use_featured_image'    => __( 'Use as marquee image / logo', 'custom-shortcodes-components' ),
	);

	$args = array(
		'label'               => __( 'Marquee Item', 'custom-shortcodes-components' ),
		'description'         => __( 'Items and logos displayed in continuous marquee sliders.', 'custom-shortcodes-components' ),
		'labels'              => $labels,
		'supports'            => array( 'title', 'thumbnail', 'excerpt', 'revisions' ),
		'taxonomies'          => array( 'csc_marquee_category' ),
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
		'menu_icon'           => 'dashicons-slides',
	);

	register_post_type( 'csc_marquee', $args );
}
add_action( 'init', 'csc_register_marquee_cpt', 10 );

/**
 * Customize columns in Marquee Items admin table.
 *
 * @param array $columns
 * @return array
 */
function csc_marquee_edit_columns( $columns ) {
	$custom_columns = array(
		'cb'                 => $columns['cb'],
		'thumbnail'          => __( 'Image / Logo', 'custom-shortcodes-components' ),
		'title'              => __( 'Title', 'custom-shortcodes-components' ),
		'custom_link'        => __( 'Custom Link', 'custom-shortcodes-components' ),
		'taxonomy_category'  => __( 'Marquee Categories', 'custom-shortcodes-components' ),
		'date'               => $columns['date'],
	);
	return $custom_columns;
}
add_filter( 'manage_csc_marquee_posts_columns', 'csc_marquee_edit_columns' );

/**
 * Render content for custom admin columns.
 *
 * @param string $column
 * @param int    $post_id
 */
function csc_marquee_custom_column_content( $column, $post_id ) {
	switch ( $column ) {
		case 'thumbnail':
			if ( has_post_thumbnail( $post_id ) ) {
				echo get_the_post_thumbnail( $post_id, array( 50, 50 ), array( 'style' => 'max-height: 40px; width: auto; object-fit: contain; border-radius: 4px;' ) );
			} else {
				echo '<span style="color: #999;">' . esc_html__( 'No image', 'custom-shortcodes-components' ) . '</span>';
			}
			break;

		case 'custom_link':
			$link = get_post_meta( $post_id, '_csc_marquee_link', true );
			if ( ! empty( $link ) ) {
				echo '<a href="' . esc_url( $link ) . '" target="_blank" rel="noopener noreferrer">' . esc_html( $link ) . ' <span class="dashicons dashicons-external" style="font-size: 14px; width: 14px; height: 14px; vertical-align: middle;"></span></a>';
			} else {
				echo '<span style="color: #999;"><em>' . esc_html__( 'Permalink (default)', 'custom-shortcodes-components' ) . '</em></span>';
			}
			break;

		case 'taxonomy_category':
			$terms = get_the_terms( $post_id, 'csc_marquee_category' );
			if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
				$term_links = array();
				foreach ( $terms as $term ) {
					$term_links[] = sprintf(
						'<a href="%s">%s</a>',
						esc_url( admin_url( 'edit.php?post_type=csc_marquee&csc_marquee_category=' . $term->slug ) ),
						esc_html( $term->name )
					);
				}
				echo implode( ', ', $term_links ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			} else {
				echo '<span style="color: #999;">—</span>';
			}
			break;
	}
}
add_action( 'manage_csc_marquee_posts_custom_column', 'csc_marquee_custom_column_content', 10, 2 );
