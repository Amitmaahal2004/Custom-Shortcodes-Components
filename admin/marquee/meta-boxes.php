<?php
/**
 * Meta Boxes: Marquee Link Settings
 *
 * @package CustomShortcodesComponents
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register Marquee Link meta box on csc_marquee post type.
 */
function csc_register_marquee_link_meta_box() {
	add_meta_box(
		'csc_marquee_link_meta_box',
		__( 'Marquee Link Settings', 'custom-shortcodes-components' ),
		'csc_render_marquee_link_meta_box',
		'csc_marquee',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'csc_register_marquee_link_meta_box' );

/**
 * Render Marquee Link meta box HTML.
 *
 * @param WP_Post $post
 */
function csc_render_marquee_link_meta_box( $post ) {
	wp_nonce_field( 'csc_save_marquee_link_meta_action', 'csc_marquee_link_nonce' );

	$current_link = get_post_meta( $post->ID, '_csc_marquee_link', true );
	$open_blank   = get_post_meta( $post->ID, '_csc_marquee_target_blank', true );
	?>
	<div style="padding: 6px 0;">
		<p style="margin-top: 0; margin-bottom: 8px;">
			<label for="csc_marquee_link" style="font-weight: 600; display: block; margin-bottom: 4px;">
				<?php esc_html_e( 'Custom Target URL', 'custom-shortcodes-components' ); ?>
			</label>
			<input type="url" id="csc_marquee_link" name="csc_marquee_link" value="<?php echo esc_attr( $current_link ); ?>" class="widefat" placeholder="https://example.com/brand-page" style="padding: 6px 8px;" />
		</p>
		<p class="description" style="color: #646970; font-size: 13px; margin: 4px 0 12px;">
			<?php esc_html_e( 'Enter a custom URL where visitors will be directed when clicking this marquee item. If left empty, the standard WordPress item permalink will be used automatically.', 'custom-shortcodes-components' ); ?>
		</p>

		<p style="margin-bottom: 0;">
			<label style="cursor: pointer;">
				<input type="checkbox" name="csc_marquee_target_blank" value="1" <?php checked( $open_blank, '1' ); ?> />
				<?php esc_html_e( 'Open link in a new tab (target="_blank")', 'custom-shortcodes-components' ); ?>
			</label>
		</p>
	</div>
	<?php
}

/**
 * Save Marquee Link meta box data.
 *
 * @param int $post_id
 */
function csc_save_marquee_link_meta_box( $post_id ) {
	// Verify nonce.
	if ( ! isset( $_POST['csc_marquee_link_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['csc_marquee_link_nonce'] ), 'csc_save_marquee_link_meta_action' ) ) {
		return;
	}

	// Ignore autosaves.
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	// Capability check.
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	// Save custom link.
	if ( isset( $_POST['csc_marquee_link'] ) ) {
		$clean_link = esc_url_raw( trim( wp_unslash( $_POST['csc_marquee_link'] ) ) );
		if ( ! empty( $clean_link ) ) {
			update_post_meta( $post_id, '_csc_marquee_link', $clean_link );
		} else {
			delete_post_meta( $post_id, '_csc_marquee_link' );
		}
	}

	// Save target blank.
	$target_blank = ! empty( $_POST['csc_marquee_target_blank'] ) ? '1' : '0';
	update_post_meta( $post_id, '_csc_marquee_target_blank', $target_blank );
}
add_action( 'save_post_csc_marquee', 'csc_save_marquee_link_meta_box' );
