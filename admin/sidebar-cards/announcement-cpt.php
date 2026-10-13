<?php
/**
 * Custom Post Type: Announcements (announcement)
 *
 * @package CustomShortcodesComponents
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register announcement Custom Post Type under parent menu 'custom-shortcodes'.
 */
function csc_register_announcement_cpt() {
	$labels = array(
		'name'                  => _x( 'Announcements', 'Post Type General Name', 'custom-shortcodes-components' ),
		'singular_name'         => _x( 'Announcement', 'Post Type Singular Name', 'custom-shortcodes-components' ),
		'menu_name'             => __( 'Announcements', 'custom-shortcodes-components' ),
		'name_admin_bar'        => __( 'Announcement', 'custom-shortcodes-components' ),
		'archives'              => __( 'Announcement Archives', 'custom-shortcodes-components' ),
		'attributes'            => __( 'Announcement Attributes', 'custom-shortcodes-components' ),
		'all_items'             => __( 'All Announcements', 'custom-shortcodes-components' ),
		'add_new_item'          => __( 'Add New Announcement', 'custom-shortcodes-components' ),
		'add_new'               => __( 'Add New', 'custom-shortcodes-components' ),
		'new_item'              => __( 'New Announcement', 'custom-shortcodes-components' ),
		'edit_item'             => __( 'Edit Announcement', 'custom-shortcodes-components' ),
		'update_item'           => __( 'Update Announcement', 'custom-shortcodes-components' ),
		'view_item'             => __( 'View Announcement', 'custom-shortcodes-components' ),
		'view_items'            => __( 'View Announcements', 'custom-shortcodes-components' ),
		'search_items'          => __( 'Search Announcements', 'custom-shortcodes-components' ),
		'not_found'             => __( 'No announcements found', 'custom-shortcodes-components' ),
		'not_found_in_trash'    => __( 'No announcements found in Trash', 'custom-shortcodes-components' ),
	);

	$args = array(
		'label'               => __( 'Announcement', 'custom-shortcodes-components' ),
		'description'         => __( 'Important and timely announcements displayed in dashboard and sidebar widgets.', 'custom-shortcodes-components' ),
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
		'menu_icon'           => 'dashicons-megaphone',
	);

	register_post_type( 'announcement', $args );
}
add_action( 'init', 'csc_register_announcement_cpt', 9 );

/**
 * Custom columns for announcement admin list table.
 *
 * @param array $columns Existing columns.
 * @return array Modified columns.
 */
function csc_announcement_columns( $columns ) {
	$new_cols = array();
	$new_cols['cb']                 = $columns['cb'];
	$new_cols['title']              = __( 'Announcement Title', 'custom-shortcodes-components' );
	$new_cols['announcement_date']  = __( 'Announcement Date', 'custom-shortcodes-components' );
	$new_cols['announcement_link']  = __( 'Target Link / URL', 'custom-shortcodes-components' );
	$new_cols['announcement_badge'] = __( '"NEW" Badge', 'custom-shortcodes-components' );
	$new_cols['menu_order']         = __( 'Display Order', 'custom-shortcodes-components' );
	$new_cols['date']               = $columns['date'];
	return $new_cols;
}
add_filter( 'manage_announcement_posts_columns', 'csc_announcement_columns' );

/**
 * Custom column content for announcement admin list table.
 *
 * @param string $column  Column name.
 * @param int    $post_id Post ID.
 */
function csc_announcement_column_content( $column, $post_id ) {
	switch ( $column ) {
		case 'announcement_date':
			$date_val = get_post_meta( $post_id, '_announcement_date', true );
			if ( empty( $date_val ) ) {
				$date_val = get_post_meta( $post_id, '_hdd_announcement_date', true );
			}
			if ( ! empty( $date_val ) ) {
				$timestamp = strtotime( $date_val );
				echo '<strong style="color:#107c41;">' . esc_html( date_i18n( 'd M Y', $timestamp ) ) . '</strong>';
			} else {
				echo '<span style="color:#94a3b8;">' . esc_html( get_the_date( 'd M Y', $post_id ) ) . ' (Publish Date)</span>';
			}
			break;

		case 'announcement_link':
			$link = get_post_meta( $post_id, '_announcement_url', true );
			if ( empty( $link ) ) {
				$link = get_post_meta( $post_id, '_hdd_announcement_link', true );
			}
			if ( ! empty( $link ) ) {
				echo '<a href="' . esc_url( $link ) . '" target="_blank" rel="noopener noreferrer" style="display:inline-flex; align-items:center; gap:4px; max-width:220px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">';
				echo esc_html( $link );
				echo ' &UpperRightArrow;</a>';
			} else {
				echo '<span style="color:#94a3b8;">&mdash;</span>';
			}
			break;

		case 'announcement_badge':
			$is_new = get_post_meta( $post_id, '_announcement_is_new', true );
			if ( '' === $is_new ) {
				$is_new = get_post_meta( $post_id, '_hdd_announcement_is_new', true );
			}
			if ( '1' === $is_new ) {
				echo '<span style="background:#107c41; color:#fff; font-size:10px; font-weight:700; padding:2px 8px; border-radius:10px;">' . esc_html__( 'NEW', 'custom-shortcodes-components' ) . '</span>';
			} else {
				echo '<span style="color:#94a3b8;">&mdash;</span>';
			}
			break;

		case 'menu_order':
			$post = get_post( $post_id );
			echo '<strong>' . esc_html( $post->menu_order ) . '</strong>';
			break;
	}
}
add_action( 'manage_announcement_posts_custom_column', 'csc_announcement_column_content', 10, 2 );

/**
 * Make announcement columns sortable.
 *
 * @param array $columns Sortable columns.
 * @return array
 */
function csc_announcement_sortable_columns( $columns ) {
	$columns['menu_order']        = 'menu_order';
	$columns['announcement_date'] = 'announcement_date';
	return $columns;
}
add_filter( 'manage_edit-announcement_sortable_columns', 'csc_announcement_sortable_columns' );
