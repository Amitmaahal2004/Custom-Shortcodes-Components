<?php
/**
 * Custom Post Type: Schemes (csc_scheme)
 *
 * @package CustomShortcodesComponents
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register csc_scheme Custom Post Type.
 */
function csc_register_scheme_cpt() {
	$labels = array(
		'name'                  => _x( 'Schemes', 'Post Type General Name', 'custom-shortcodes-components' ),
		'singular_name'         => _x( 'Scheme', 'Post Type Singular Name', 'custom-shortcodes-components' ),
		'menu_name'             => __( 'Schemes', 'custom-shortcodes-components' ),
		'name_admin_bar'        => __( 'Scheme', 'custom-shortcodes-components' ),
		'archives'              => __( 'Scheme Archives', 'custom-shortcodes-components' ),
		'attributes'            => __( 'Scheme Attributes', 'custom-shortcodes-components' ),
		'parent_item_colon'     => __( 'Parent Scheme:', 'custom-shortcodes-components' ),
		'all_items'             => __( 'All Schemes', 'custom-shortcodes-components' ),
		'add_new_item'          => __( 'Add New Scheme', 'custom-shortcodes-components' ),
		'add_new'               => __( 'Add New Scheme', 'custom-shortcodes-components' ),
		'new_item'              => __( 'New Scheme', 'custom-shortcodes-components' ),
		'edit_item'             => __( 'Edit Scheme', 'custom-shortcodes-components' ),
		'update_item'           => __( 'Update Scheme', 'custom-shortcodes-components' ),
		'view_item'             => __( 'View Scheme', 'custom-shortcodes-components' ),
		'view_items'            => __( 'View Schemes', 'custom-shortcodes-components' ),
		'search_items'          => __( 'Search Schemes', 'custom-shortcodes-components' ),
		'not_found'             => __( 'No schemes found', 'custom-shortcodes-components' ),
		'not_found_in_trash'    => __( 'No schemes found in Trash', 'custom-shortcodes-components' ),
		'featured_image'        => __( 'Card Image / Custom Icon', 'custom-shortcodes-components' ),
		'set_featured_image'    => __( 'Set card image / icon', 'custom-shortcodes-components' ),
		'remove_featured_image' => __( 'Remove image / icon', 'custom-shortcodes-components' ),
		'use_featured_image'    => __( 'Use as card image / icon', 'custom-shortcodes-components' ),
	);

	$args = array(
		'label'               => __( 'Scheme', 'custom-shortcodes-components' ),
		'description'         => __( 'Schemes and service cards displayed in the responsive Swiper slider.', 'custom-shortcodes-components' ),
		'labels'              => $labels,
		'supports'            => array( 'title', 'editor', 'excerpt', 'thumbnail', 'page-attributes', 'revisions' ),
		'taxonomies'          => array( 'csc_scheme_category' ),
		'hierarchical'        => false,
		'public'              => true,
		'show_ui'             => true,
		'show_in_menu'        => 'custom-shortcodes',
		'show_in_nav_menus'   => true,
		'show_in_admin_bar'   => true,
		'show_in_rest'        => true,
		'can_export'          => true,
		'has_archive'         => false,
		'exclude_from_search' => false,
		'publicly_queryable'  => true,
		'capability_type'     => 'post',
		'menu_icon'           => 'dashicons-grid-view',
	);

	register_post_type( 'csc_scheme', $args );
}
add_action( 'init', 'csc_register_scheme_cpt', 10 );

/**
 * Customize columns in Schemes admin table.
 *
 * @param array $columns
 * @return array
 */
function csc_scheme_edit_columns( $columns ) {
	$custom_columns = array(
		'cb'                 => $columns['cb'],
		'scheme_icon'        => __( 'Icon', 'custom-shortcodes-components' ),
		'title'              => __( 'Scheme Title', 'custom-shortcodes-components' ),
		'scheme_color'       => __( 'Accent Color', 'custom-shortcodes-components' ),
		'taxonomy_category'  => __( 'Categories', 'custom-shortcodes-components' ),
		'scheme_link'        => __( 'Button Link', 'custom-shortcodes-components' ),
		'menu_order'         => __( 'Order', 'custom-shortcodes-components' ),
		'date'               => $columns['date'],
	);
	return $custom_columns;
}
add_filter( 'manage_csc_scheme_posts_columns', 'csc_scheme_edit_columns' );

/**
 * Render content for custom admin columns.
 *
 * @param string $column
 * @param int    $post_id
 */
function csc_scheme_custom_column_content( $column, $post_id ) {
	switch ( $column ) {
		case 'scheme_icon':
			$color     = get_post_meta( $post_id, '_csc_scheme_color', true );
			$color     = ! empty( $color ) ? $color : '#109b48';
			$icon_type = get_post_meta( $post_id, '_csc_scheme_icon_type', true );
			$preset    = get_post_meta( $post_id, '_csc_scheme_icon_preset', true );
			$img_id    = get_post_meta( $post_id, '_csc_scheme_icon_image_id', true );

			echo '<div style="display:inline-flex;align-items:center;justify-content:center;width:40px;height:40px;border-radius:50%;background:' . esc_attr( $color ) . '18;color:' . esc_attr( $color ) . ';">';
			if ( 'image' === $icon_type && ! empty( $img_id ) ) {
				$img_url = wp_get_attachment_image_url( $img_id, 'thumbnail' );
				if ( $img_url ) {
					echo '<img src="' . esc_url( $img_url ) . '" style="width:24px;height:24px;object-fit:contain;" alt="" />';
				} else {
					echo '<span class="dashicons dashicons-format-image"></span>';
				}
			} elseif ( has_post_thumbnail( $post_id ) && empty( $preset ) ) {
				echo get_the_post_thumbnail( $post_id, array( 24, 24 ), array( 'style' => 'width: 24px; height: 24px; object-fit: contain;' ) );
			} else {
				// SVG icon preview based on preset.
				echo csc_get_scheme_svg_icon( $preset ? $preset : 'education', 22, $color ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
			echo '</div>';
			break;

		case 'scheme_color':
			$color = get_post_meta( $post_id, '_csc_scheme_color', true );
			$color = ! empty( $color ) ? $color : '#109b48';
			echo '<div style="display:flex;align-items:center;gap:8px;">';
			echo '<span style="display:inline-block;width:18px;height:18px;border-radius:4px;background:' . esc_attr( $color ) . ';border:1px solid rgba(0,0,0,0.15);box-shadow:inset 0 0 2px rgba(0,0,0,0.2);"></span>';
			echo '<code>' . esc_html( strtoupper( $color ) ) . '</code>';
			echo '</div>';
			break;

		case 'taxonomy_category':
			$terms = get_the_terms( $post_id, 'csc_scheme_category' );
			if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
				$term_links = array();
				foreach ( $terms as $term ) {
					$term_links[] = sprintf(
						'<a href="%s">%s</a>',
						esc_url( admin_url( 'edit.php?post_type=csc_scheme&csc_scheme_category=' . $term->slug ) ),
						esc_html( $term->name )
					);
				}
				echo implode( ', ', $term_links ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			} else {
				echo '<span style="color: #999;">—</span>';
			}
			break;

		case 'scheme_link':
			$link = get_post_meta( $post_id, '_csc_scheme_button_url', true );
			$text = get_post_meta( $post_id, '_csc_scheme_button_text', true );
			$text = ! empty( $text ) ? $text : __( 'View Details', 'custom-shortcodes-components' );

			if ( ! empty( $link ) ) {
				echo '<a href="' . esc_url( $link ) . '" target="_blank" rel="noopener noreferrer" style="font-weight: 500;">' . esc_html( $text ) . ' <span class="dashicons dashicons-external" style="font-size: 13px; width: 13px; height: 13px; vertical-align: middle;"></span></a>';
			} else {
				echo '<span style="color: #646970;">' . esc_html( $text ) . ' <em>(' . esc_html__( 'Permalink', 'custom-shortcodes-components' ) . ')</em></span>';
			}
			break;

		case 'menu_order':
			$post = get_post( $post_id );
			echo '<strong>' . esc_html( $post->menu_order ) . '</strong>';
			break;
	}
}
add_action( 'manage_csc_scheme_posts_custom_column', 'csc_scheme_custom_column_content', 10, 2 );

/**
 * Make Order column sortable in admin list.
 *
 * @param array $columns
 * @return array
 */
function csc_scheme_sortable_columns( $columns ) {
	$columns['menu_order']   = 'menu_order';
	$columns['scheme_color'] = 'scheme_color';
	return $columns;
}
add_filter( 'manage_edit-csc_scheme_sortable_columns', 'csc_scheme_sortable_columns' );

/**
 * Add Category filter dropdown to Schemes admin post list.
 *
 * @param string $post_type Current post type.
 * @param string $which     Top or bottom filter position.
 */
function csc_scheme_filter_by_taxonomy( $post_type, $which ) {
	if ( 'csc_scheme' !== $post_type ) {
		return;
	}

	$taxonomy = 'csc_scheme_category';
	$selected = isset( $_GET[ $taxonomy ] ) ? sanitize_title( wp_unslash( $_GET[ $taxonomy ] ) ) : '';
	$term     = get_term_by( 'slug', $selected, $taxonomy );
	$selected_id = $term ? $term->term_id : 0;

	wp_dropdown_categories(
		array(
			'show_option_all' => __( 'All Scheme Categories', 'custom-shortcodes-components' ),
			'taxonomy'        => $taxonomy,
			'name'            => $taxonomy,
			'orderby'         => 'name',
			'selected'        => $selected_id,
			'hierarchical'    => true,
			'depth'           => 3,
			'show_count'      => true,
			'hide_empty'      => false,
			'value_field'     => 'slug',
		)
	);
}
add_action( 'restrict_manage_posts', 'csc_scheme_filter_by_taxonomy', 10, 2 );

