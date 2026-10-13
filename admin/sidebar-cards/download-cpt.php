<?php
/**
 * Custom Post Type: Important Downloads (important_download)
 *
 * @package CustomShortcodesComponents
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register important_download Custom Post Type under parent menu 'custom-shortcodes'.
 */
function csc_register_important_download_cpt() {
	$labels = array(
		'name'                  => _x( 'Important Downloads', 'Post Type General Name', 'custom-shortcodes-components' ),
		'singular_name'         => _x( 'Important Download', 'Post Type Singular Name', 'custom-shortcodes-components' ),
		'menu_name'             => __( 'Important Downloads', 'custom-shortcodes-components' ),
		'name_admin_bar'        => __( 'Important Download', 'custom-shortcodes-components' ),
		'archives'              => __( 'Download Archives', 'custom-shortcodes-components' ),
		'attributes'            => __( 'Download Attributes', 'custom-shortcodes-components' ),
		'all_items'             => __( 'All Downloads', 'custom-shortcodes-components' ),
		'add_new_item'          => __( 'Add New Download', 'custom-shortcodes-components' ),
		'add_new'               => __( 'Add New', 'custom-shortcodes-components' ),
		'new_item'              => __( 'New Download', 'custom-shortcodes-components' ),
		'edit_item'             => __( 'Edit Download', 'custom-shortcodes-components' ),
		'update_item'           => __( 'Update Download', 'custom-shortcodes-components' ),
		'view_item'             => __( 'View Download', 'custom-shortcodes-components' ),
		'view_items'            => __( 'View Downloads', 'custom-shortcodes-components' ),
		'search_items'          => __( 'Search Downloads', 'custom-shortcodes-components' ),
		'not_found'             => __( 'No downloads found', 'custom-shortcodes-components' ),
		'not_found_in_trash'    => __( 'No downloads found in Trash', 'custom-shortcodes-components' ),
	);

	$args = array(
		'label'               => __( 'Important Download', 'custom-shortcodes-components' ),
		'description'         => __( 'Official forms, guidelines, and PDF documents downloadable from the sidebar widgets.', 'custom-shortcodes-components' ),
		'labels'              => $labels,
		'supports'            => array( 'title', 'page-attributes' ),
		'hierarchical'        => false,
		'public'              => false,
		'publicly_queryable'  => false,
		'show_ui'             => true,
		'show_in_menu'        => 'custom-shortcodes',
		'show_in_nav_menus'   => false,
		'show_in_admin_bar'   => true,
		'show_in_rest'        => true,
		'can_export'          => true,
		'has_archive'         => false,
		'exclude_from_search' => true,
		'capability_type'     => 'post',
		'menu_icon'           => 'dashicons-download',
	);

	register_post_type( 'important_download', $args );
}
add_action( 'init', 'csc_register_important_download_cpt', 9 );

/**
 * Custom columns for important_download admin list table.
 *
 * @param array $columns Existing columns.
 * @return array Modified columns.
 */
function csc_important_download_columns( $columns ) {
	$new_cols = array();
	$new_cols['cb']            = $columns['cb'];
	$new_cols['title']         = __( 'Document Title', 'custom-shortcodes-components' );
	$new_cols['download_file'] = __( 'Attached File / Media', 'custom-shortcodes-components' );
	$new_cols['menu_order']    = __( 'Display Order', 'custom-shortcodes-components' );
	$new_cols['date']          = $columns['date'];
	return $new_cols;
}
add_filter( 'manage_important_download_posts_columns', 'csc_important_download_columns' );

/**
 * Custom column content for important_download admin list table.
 *
 * @param string $column  Column name.
 * @param int    $post_id Post ID.
 */
function csc_important_download_column_content( $column, $post_id ) {
	switch ( $column ) {
		case 'download_file':
			$file_url = get_post_meta( $post_id, '_download_file_url', true );
			if ( empty( $file_url ) ) {
				$file_url = get_post_meta( $post_id, '_hdd_download_file_url', true );
			}

			if ( ! empty( $file_url ) ) {
				$ext = strtoupper( pathinfo( parse_url( $file_url, PHP_URL_PATH ), PATHINFO_EXTENSION ) );
				if ( empty( $ext ) ) {
					$ext = 'FILE';
				}
				echo '<div style="display:flex; align-items:center; gap:8px;">';
				echo '<span style="background:#fee2e2; color:#ef4444; font-weight:700; font-size:11px; padding:2px 6px; border-radius:3px; border:1px solid #fca5a5;">' . esc_html( $ext ) . '</span>';
				echo '<a href="' . esc_url( $file_url ) . '" target="_blank" rel="noopener noreferrer" style="font-weight:600; text-decoration:none; display:inline-flex; align-items:center; gap:4px;">';
				echo esc_html( basename( parse_url( $file_url, PHP_URL_PATH ) ) );
				echo ' &UpperRightArrow;</a>';
				echo '</div>';
			} else {
				echo '<span style="color:#ef4444; font-weight:600;">&times; ' . esc_html__( 'No file attached', 'custom-shortcodes-components' ) . '</span>';
			}
			break;

		case 'menu_order':
			$post = get_post( $post_id );
			echo '<strong>' . esc_html( $post->menu_order ) . '</strong>';
			break;
	}
}
add_action( 'manage_important_download_posts_custom_column', 'csc_important_download_column_content', 10, 2 );

/**
 * Make important_download columns sortable.
 *
 * @param array $columns Sortable columns.
 * @return array
 */
function csc_important_download_sortable_columns( $columns ) {
	$columns['menu_order'] = 'menu_order';
	return $columns;
}
add_filter( 'manage_edit-important_download_sortable_columns', 'csc_important_download_sortable_columns' );
