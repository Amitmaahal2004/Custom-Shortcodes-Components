<?php
/**
 * Custom Post Type: Success Stories (csc_story)
 *
 * @package CustomShortcodesComponents
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register csc_story Custom Post Type.
 */
function csc_register_story_cpt() {
	$labels = array(
		'name'                  => _x( 'Success Stories', 'Post Type General Name', 'custom-shortcodes-components' ),
		'singular_name'         => _x( 'Success Story', 'Post Type Singular Name', 'custom-shortcodes-components' ),
		'menu_name'             => __( 'Success Stories', 'custom-shortcodes-components' ),
		'name_admin_bar'        => __( 'Success Story', 'custom-shortcodes-components' ),
		'archives'              => __( 'Story Archives', 'custom-shortcodes-components' ),
		'attributes'            => __( 'Story Attributes', 'custom-shortcodes-components' ),
		'parent_item_colon'     => __( 'Parent Story:', 'custom-shortcodes-components' ),
		'all_items'             => __( 'All Success Stories', 'custom-shortcodes-components' ),
		'add_new_item'          => __( 'Add New Story', 'custom-shortcodes-components' ),
		'add_new'               => __( 'Add New Story', 'custom-shortcodes-components' ),
		'new_item'              => __( 'New Success Story', 'custom-shortcodes-components' ),
		'edit_item'             => __( 'Edit Story', 'custom-shortcodes-components' ),
		'update_item'           => __( 'Update Story', 'custom-shortcodes-components' ),
		'view_item'             => __( 'View Story', 'custom-shortcodes-components' ),
		'view_items'            => __( 'View Stories', 'custom-shortcodes-components' ),
		'search_items'          => __( 'Search Stories', 'custom-shortcodes-components' ),
		'not_found'             => __( 'No stories found', 'custom-shortcodes-components' ),
		'not_found_in_trash'    => __( 'No stories found in Trash', 'custom-shortcodes-components' ),
		'featured_image'        => __( 'Story / Video Thumbnail', 'custom-shortcodes-components' ),
		'set_featured_image'    => __( 'Set story image / thumbnail', 'custom-shortcodes-components' ),
		'remove_featured_image' => __( 'Remove image / thumbnail', 'custom-shortcodes-components' ),
		'use_featured_image'    => __( 'Use as story image', 'custom-shortcodes-components' ),
	);

	$args = array(
		'label'               => __( 'Success Story', 'custom-shortcodes-components' ),
		'description'         => __( 'Success stories of beneficiaries with video, loan details, and outcome impact.', 'custom-shortcodes-components' ),
		'labels'              => $labels,
		'supports'            => array( 'title', 'editor', 'excerpt', 'thumbnail', 'page-attributes', 'revisions' ),
		'taxonomies'          => array( 'csc_story_category', 'csc_story_district' ),
		'hierarchical'        => false,
		'public'              => true,
		'show_ui'             => true,
		'show_in_menu'        => 'custom-shortcodes',
		'show_in_nav_menus'   => true,
		'show_in_admin_bar'   => true,
		'show_in_rest'        => true,
		'can_export'          => true,
		'has_archive'         => 'stories',
		'exclude_from_search' => false,
		'publicly_queryable'  => true,
		'rewrite'             => array( 'slug' => 'story', 'with_front' => false ),
		'capability_type'     => 'post',
		'menu_icon'           => 'dashicons-awards',
	);

	register_post_type( 'csc_story', $args );
}
add_action( 'init', 'csc_register_story_cpt', 10 );

/**
 * Custom columns in Stories admin table.
 *
 * @param array $columns
 * @return array
 */
function csc_story_edit_columns( $columns ) {
	$custom_columns = array(
		'cb'                 => $columns['cb'],
		'story_thumb'        => __( 'Thumbnail', 'custom-shortcodes-components' ),
		'title'              => __( 'Beneficiary / Story Title', 'custom-shortcodes-components' ),
		'story_district'     => __( 'District', 'custom-shortcodes-components' ),
		'story_loan'         => __( 'Loan Amount', 'custom-shortcodes-components' ),
		'story_scheme'       => __( 'Scheme', 'custom-shortcodes-components' ),
		'story_date'         => __( 'Sanction Date', 'custom-shortcodes-components' ),
		'date'               => $columns['date'],
	);
	return $custom_columns;
}
add_filter( 'manage_csc_story_posts_columns', 'csc_story_edit_columns' );

/**
 * Render custom column content in Stories admin table.
 *
 * @param string $column
 * @param int    $post_id
 */
function csc_story_custom_column_content( $column, $post_id ) {
	switch ( $column ) {
		case 'story_thumb':
			if ( has_post_thumbnail( $post_id ) ) {
				echo get_the_post_thumbnail( $post_id, array( 50, 50 ), array( 'style' => 'width: 50px; height: 35px; object-fit: cover; border-radius: 4px;' ) );
			} else {
				$video_url = get_post_meta( $post_id, '_csc_story_video_url', true );
				if ( ! empty( $video_url ) ) {
					echo '<span class="dashicons dashicons-video-alt3" style="font-size: 28px; width: 28px; height: 28px; color: #2271b1;"></span>';
				} else {
					echo '<span style="color: #999;">—</span>';
				}
			}
			break;

		case 'story_district':
			$districts = get_the_terms( $post_id, 'csc_story_district' );
			if ( ! empty( $districts ) && ! is_wp_error( $districts ) ) {
				$names = wp_list_pluck( $districts, 'name' );
				echo esc_html( implode( ', ', $names ) );
			} else {
				$meta_district = get_post_meta( $post_id, '_csc_story_district', true );
				echo ! empty( $meta_district ) ? esc_html( $meta_district ) : '<span style="color: #999;">—</span>';
			}
			break;

		case 'story_loan':
			$loan = get_post_meta( $post_id, '_csc_story_loan_amount', true );
			echo ! empty( $loan ) ? '<strong>' . esc_html( $loan ) . '</strong>' : '<span style="color: #999;">—</span>';
			break;

		case 'story_scheme':
			$scheme = get_post_meta( $post_id, '_csc_story_scheme', true );
			echo ! empty( $scheme ) ? esc_html( $scheme ) : '<span style="color: #999;">—</span>';
			break;

		case 'story_date':
			$sanction = get_post_meta( $post_id, '_csc_story_sanction_date', true );
			echo ! empty( $sanction ) ? esc_html( $sanction ) : '<span style="color: #999;">—</span>';
			break;
	}
}
add_action( 'manage_csc_story_posts_custom_column', 'csc_story_custom_column_content', 10, 2 );
