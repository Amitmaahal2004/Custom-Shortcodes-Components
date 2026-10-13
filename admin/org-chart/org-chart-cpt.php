<?php
/**
 * Custom Post Type: Organization Chart Members (csc_org_member)
 *
 * @package CustomShortcodesComponents
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register csc_org_member Custom Post Type.
 */
function csc_register_org_member_cpt() {
	$labels = array(
		'name'                  => _x( 'Org Chart / Hierarchy', 'Post Type General Name', 'custom-shortcodes-components' ),
		'singular_name'         => _x( 'Org Member', 'Post Type Singular Name', 'custom-shortcodes-components' ),
		'menu_name'             => __( 'Org Chart / Hierarchy', 'custom-shortcodes-components' ),
		'name_admin_bar'        => __( 'Org Member', 'custom-shortcodes-components' ),
		'archives'              => __( 'Member Archives', 'custom-shortcodes-components' ),
		'attributes'            => __( 'Member Attributes', 'custom-shortcodes-components' ),
		'parent_item_colon'     => __( 'Parent Member / Superior:', 'custom-shortcodes-components' ),
		'all_items'             => __( 'All Org Members', 'custom-shortcodes-components' ),
		'add_new_item'          => __( 'Add New Member', 'custom-shortcodes-components' ),
		'add_new'               => __( 'Add New Member', 'custom-shortcodes-components' ),
		'new_item'              => __( 'New Member', 'custom-shortcodes-components' ),
		'edit_item'             => __( 'Edit Member', 'custom-shortcodes-components' ),
		'update_item'           => __( 'Update Member', 'custom-shortcodes-components' ),
		'view_item'             => __( 'View Member', 'custom-shortcodes-components' ),
		'view_items'            => __( 'View Members', 'custom-shortcodes-components' ),
		'search_items'          => __( 'Search Members', 'custom-shortcodes-components' ),
		'not_found'             => __( 'No members found', 'custom-shortcodes-components' ),
		'not_found_in_trash'    => __( 'No members found in Trash', 'custom-shortcodes-components' ),
		'featured_image'        => __( 'Member Photo / Avatar', 'custom-shortcodes-components' ),
		'set_featured_image'    => __( 'Set member photo / avatar', 'custom-shortcodes-components' ),
		'remove_featured_image' => __( 'Remove photo / avatar', 'custom-shortcodes-components' ),
		'use_featured_image'    => __( 'Use as member photo', 'custom-shortcodes-components' ),
	);

	$args = array(
		'label'               => __( 'Org Member', 'custom-shortcodes-components' ),
		'description'         => __( 'Dynamic organizational hierarchy tree members (Chairman, Directors, Officers).', 'custom-shortcodes-components' ),
		'labels'              => $labels,
		'supports'            => array( 'title', 'editor', 'thumbnail', 'page-attributes' ),
		'taxonomies'          => array( 'csc_org_category', 'csc_org_level' ),
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
		'menu_icon'           => 'dashicons-networking',
	);

	register_post_type( 'csc_org_member', $args );
}
add_action( 'init', 'csc_register_org_member_cpt', 10 );

/**
 * Custom columns in Org Members admin table.
 *
 * @param array $columns
 * @return array
 */
function csc_org_member_edit_columns( $columns ) {
	$custom_columns = array(
		'cb'                 => $columns['cb'],
		'member_photo'       => __( 'Photo / Avatar', 'custom-shortcodes-components' ),
		'title'              => __( 'Member Name', 'custom-shortcodes-components' ),
		'member_level'       => __( 'Tree Level', 'custom-shortcodes-components' ),
		'member_designation' => __( 'Designation', 'custom-shortcodes-components' ),
		'member_cadre'       => __( 'Cadre / Sub-title', 'custom-shortcodes-components' ),
		'member_category'    => __( 'Category', 'custom-shortcodes-components' ),
		'member_order'       => __( 'Order', 'custom-shortcodes-components' ),
		'date'               => $columns['date'],
	);
	return $custom_columns;
}
add_filter( 'manage_csc_org_member_posts_columns', 'csc_org_member_edit_columns' );

/**
 * Render custom column content in Org Members admin table.
 *
 * @param string $column
 * @param int    $post_id
 */
function csc_org_member_custom_column_content( $column, $post_id ) {
	switch ( $column ) {
		case 'member_photo':
			$photo_url = get_post_meta( $post_id, '_csc_org_photo_url', true );
			$avatar_color = get_post_meta( $post_id, '_csc_org_avatar_color', true );
			if ( empty( $avatar_color ) ) {
				$avatar_color = 'blue';
			}

			if ( has_post_thumbnail( $post_id ) ) {
				echo get_the_post_thumbnail( $post_id, array( 45, 45 ), array( 'style' => 'width: 42px; height: 42px; object-fit: cover; border-radius: 50%; border: 2px solid #e2e8f0;' ) );
			} elseif ( ! empty( $photo_url ) ) {
				echo '<img src="' . esc_url( $photo_url ) . '" style="width: 42px; height: 42px; object-fit: cover; border-radius: 50%; border: 2px solid #e2e8f0;" alt="" />';
			} else {
				$bg_colors = array(
					'blue'   => '#e0f2fe; color: #0284c7;',
					'green'  => '#dcfce7; color: #16a34a;',
					'peach'  => '#ffedd5; color: #ea580c;',
					'purple' => '#f3e8ff; color: #9333ea;',
					'teal'   => '#ccfbf1; color: #0d9488;',
				);
				$color_style = isset( $bg_colors[ $avatar_color ] ) ? $bg_colors[ $avatar_color ] : $bg_colors['blue'];
				echo '<div style="width: 38px; height: 38px; border-radius: 50%; background: ' . esc_attr( $color_style ) . ' display: inline-flex; align-items: center; justify-content: center; font-size: 18px;"><span class="dashicons dashicons-admin-users" style="font-size: 20px; width: 20px; height: 20px;"></span></div>';
			}
			break;

		case 'member_level':
			$level = get_post_meta( $post_id, '_csc_org_level', true );
			if ( 'apex' === $level || 'chairman' === $level || 'level_1' === $level ) {
				echo '<span style="display: inline-block; background: #009639; color: #fff; padding: 3px 10px; border-radius: 12px; font-weight: 600; font-size: 11px; text-transform: uppercase;">' . esc_html__( 'Apex / Top Node', 'custom-shortcodes-components' ) . '</span>';
			} elseif ( 'level_3' === $level ) {
				echo '<span style="display: inline-block; background: #64748b; color: #fff; padding: 3px 10px; border-radius: 12px; font-weight: 600; font-size: 11px;">' . esc_html__( 'Level 3 / Officer', 'custom-shortcodes-components' ) . '</span>';
			} else {
				echo '<span style="display: inline-block; background: #0284c7; color: #fff; padding: 3px 10px; border-radius: 12px; font-weight: 600; font-size: 11px;">' . esc_html__( 'Subordinate / Level 2', 'custom-shortcodes-components' ) . '</span>';
			}
			break;

		case 'member_designation':
			$designation = get_post_meta( $post_id, '_csc_org_designation', true );
			echo ! empty( $designation ) ? '<strong>' . esc_html( $designation ) . '</strong>' : '<span style="color: #999;">—</span>';
			break;

		case 'member_cadre':
			$cadre = get_post_meta( $post_id, '_csc_org_cadre', true );
			echo ! empty( $cadre ) ? '<span style="color: #475569; font-weight: 500;">' . esc_html( $cadre ) . '</span>' : '<span style="color: #999;">—</span>';
			break;

		case 'member_category':
			$categories = get_the_terms( $post_id, 'csc_org_category' );
			if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) {
				$names = wp_list_pluck( $categories, 'name' );
				echo esc_html( implode( ', ', $names ) );
			} else {
				echo '<span style="color: #999;">—</span>';
			}
			break;

		case 'member_order':
			$post = get_post( $post_id );
			echo esc_html( $post->menu_order );
			break;
	}
}
add_action( 'manage_csc_org_member_posts_custom_column', 'csc_org_member_custom_column_content', 10, 2 );

/**
 * Make custom columns sortable.
 *
 * @param array $columns
 * @return array
 */
function csc_org_member_sortable_columns( $columns ) {
	$columns['member_order'] = 'menu_order';
	$columns['member_level'] = 'member_level';
	return $columns;
}
add_filter( 'manage_edit-csc_org_member_sortable_columns', 'csc_org_member_sortable_columns' );
