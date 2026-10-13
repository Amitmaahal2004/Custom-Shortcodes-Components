<?php
/**
 * Meta Boxes: Organization Member Details & Seed Data
 *
 * @package CustomShortcodesComponents
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register Meta Boxes for csc_org_member.
 */
function csc_register_org_member_meta_boxes() {
	add_meta_box(
		'csc_org_member_details_meta_box',
		__( 'Org Hierarchy Member Details', 'custom-shortcodes-components' ),
		'csc_render_org_member_details_meta_box',
		'csc_org_member',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'csc_register_org_member_meta_boxes' );

/**
 * Enqueue admin scripts & media library for Org Member meta box.
 *
 * @param string $hook Current admin page hook.
 */
function csc_org_member_admin_enqueue_assets( $hook ) {
	global $post_type;

	if ( 'csc_org_member' !== $post_type ) {
		return;
	}

	wp_enqueue_media();
}
add_action( 'admin_enqueue_scripts', 'csc_org_member_admin_enqueue_assets' );

/**
 * Render Org Member Details Meta Box.
 *
 * @param WP_Post $post Current post.
 */
function csc_render_org_member_details_meta_box( $post ) {
	wp_nonce_field( 'csc_save_org_member_meta_action', 'csc_org_member_meta_nonce' );

	$level         = get_post_meta( $post->ID, '_csc_org_level', true );
	$designation   = get_post_meta( $post->ID, '_csc_org_designation', true );
	$cadre         = get_post_meta( $post->ID, '_csc_org_cadre', true );
	$avatar_type   = get_post_meta( $post->ID, '_csc_org_avatar_type', true );
	$avatar_color  = get_post_meta( $post->ID, '_csc_org_avatar_color', true );
	$photo_url     = get_post_meta( $post->ID, '_csc_org_photo_url', true );
	$email         = get_post_meta( $post->ID, '_csc_org_email', true );
	$phone         = get_post_meta( $post->ID, '_csc_org_phone', true );

	// Default fallbacks
	if ( empty( $level ) ) {
		$level = 'subordinate';
	}
	if ( empty( $avatar_type ) ) {
		$avatar_type = 'icon';
	}
	if ( empty( $avatar_color ) ) {
		$avatar_color = 'blue';
	}
	?>
	<style>
		.csc-org-meta-grid {
			display: grid;
			grid-template-columns: 1fr 1fr;
			gap: 16px;
			margin-bottom: 16px;
		}
		.csc-org-meta-group {
			margin-bottom: 16px;
		}
		.csc-org-meta-group label {
			display: block;
			font-weight: 600;
			margin-bottom: 6px;
			color: #1e293b;
		}
		.csc-org-meta-group input[type="text"],
		.csc-org-meta-group input[type="email"],
		.csc-org-meta-group select {
			width: 100%;
			padding: 8px 10px;
			border-radius: 4px;
			border: 1px solid #cbd5e1;
		}
		.csc-org-hint {
			font-size: 12px;
			color: #64748b;
			margin-top: 4px;
		}
		.csc-badge-preview {
			display: inline-flex;
			align-items: center;
			gap: 6px;
			margin-top: 6px;
			padding: 4px 10px;
			border-radius: 6px;
			font-size: 12px;
			font-weight: 600;
		}
	</style>

	<div class="csc-org-meta-grid">
		<div class="csc-org-meta-group">
			<label for="csc_org_level"><?php esc_html_e( 'Hierarchy Tree Position / Tier', 'custom-shortcodes-components' ); ?></label>
			<select id="csc_org_level" name="csc_org_level">
				<option value="apex" <?php selected( $level, 'apex' ); ?>><?php esc_html_e( 'Apex / Top Node (Chairman / President with Photo cutout)', 'custom-shortcodes-components' ); ?></option>
				<option value="subordinate" <?php selected( $level, 'subordinate' ); ?>><?php esc_html_e( 'Subordinate / Level 2 (Director / Board Member)', 'custom-shortcodes-components' ); ?></option>
				<option value="level_3" <?php selected( $level, 'level_3' ); ?>><?php esc_html_e( 'Level 3 / Department Officer', 'custom-shortcodes-components' ); ?></option>
			</select>
			<div class="csc-org-hint"><?php esc_html_e( 'Select "Apex / Top Node" for the leader card placed above the tree connector line.', 'custom-shortcodes-components' ); ?></div>
		</div>

		<div class="csc-org-meta-group">
			<label for="csc_org_designation"><?php esc_html_e( 'Designation / Title', 'custom-shortcodes-components' ); ?></label>
			<input type="text" id="csc_org_designation" name="csc_org_designation" value="<?php echo esc_attr( $designation ); ?>" placeholder="<?php esc_attr_e( 'e.g. Chairman, Director, Non Official Director', 'custom-shortcodes-components' ); ?>" />
			<div class="csc-org-hint"><?php esc_html_e( 'Displayed in the bottom box of the card (e.g. Chairman, Director).', 'custom-shortcodes-components' ); ?></div>
		</div>
	</div>

	<div class="csc-org-meta-grid">
		<div class="csc-org-meta-group">
			<label for="csc_org_cadre"><?php esc_html_e( 'Cadre / Sub-designation (Optional)', 'custom-shortcodes-components' ); ?></label>
			<input type="text" id="csc_org_cadre" name="csc_org_cadre" value="<?php echo esc_attr( $cadre ); ?>" placeholder="<?php esc_attr_e( 'e.g. (IAS), (HPAS), IAS', 'custom-shortcodes-components' ); ?>" />
			<div class="csc-org-hint"><?php esc_html_e( 'Appended to the member name (e.g. "Sh. Chander Prakash Verma, (IAS)").', 'custom-shortcodes-components' ); ?></div>
		</div>

		<div class="csc-org-meta-group">
			<label for="csc_org_avatar_color"><?php esc_html_e( 'Avatar Badge Color (For Subordinate Cards)', 'custom-shortcodes-components' ); ?></label>
			<select id="csc_org_avatar_color" name="csc_org_avatar_color">
				<option value="blue" <?php selected( $avatar_color, 'blue' ); ?>><?php esc_html_e( 'Light Blue (Icon: #0284c7, Bg: #e0f2fe)', 'custom-shortcodes-components' ); ?></option>
				<option value="green" <?php selected( $avatar_color, 'green' ); ?>><?php esc_html_e( 'Light Green (Icon: #16a34a, Bg: #dcfce7)', 'custom-shortcodes-components' ); ?></option>
				<option value="peach" <?php selected( $avatar_color, 'peach' ); ?>><?php esc_html_e( 'Peach / Orange (Icon: #ea580c, Bg: #ffedd5)', 'custom-shortcodes-components' ); ?></option>
				<option value="purple" <?php selected( $avatar_color, 'purple' ); ?>><?php esc_html_e( 'Purple / Lavender (Icon: #9333ea, Bg: #f3e8ff)', 'custom-shortcodes-components' ); ?></option>
				<option value="teal" <?php selected( $avatar_color, 'teal' ); ?>><?php esc_html_e( 'Teal / Emerald (Icon: #0d9488, Bg: #ccfbf1)', 'custom-shortcodes-components' ); ?></option>
			</select>
			<div class="csc-org-hint"><?php esc_html_e( 'Matches the colored circular avatars shown in the design.', 'custom-shortcodes-components' ); ?></div>
		</div>
	</div>

	<div class="csc-org-meta-group">
		<label for="csc_org_photo_url"><?php esc_html_e( 'Custom Photo / Cutout Image URL', 'custom-shortcodes-components' ); ?></label>
		<div style="display: flex; gap: 8px; align-items: center;">
			<input type="text" id="csc_org_photo_url" name="csc_org_photo_url" value="<?php echo esc_attr( $photo_url ); ?>" placeholder="<?php esc_attr_e( 'Upload or paste image URL', 'custom-shortcodes-components' ); ?>" />
			<button type="button" class="button csc-upload-org-photo-btn"><?php esc_html_e( 'Choose Image', 'custom-shortcodes-components' ); ?></button>
		</div>
		<div class="csc-org-hint"><?php esc_html_e( 'For Apex / Top node (e.g. Chairman portrait) or subordinate profile photos. You can also use WordPress Featured Image.', 'custom-shortcodes-components' ); ?></div>
		<div id="csc_org_photo_preview" style="margin-top: 10px;">
			<?php if ( ! empty( $photo_url ) ) : ?>
				<img src="<?php echo esc_url( $photo_url ); ?>" style="max-height: 100px; border-radius: 6px; border: 1px solid #e2e8f0;" alt="" />
			<?php endif; ?>
		</div>
	</div>

	<div class="csc-org-meta-grid">
		<div class="csc-org-meta-group">
			<label for="csc_org_email"><?php esc_html_e( 'Email (Optional)', 'custom-shortcodes-components' ); ?></label>
			<input type="email" id="csc_org_email" name="csc_org_email" value="<?php echo esc_attr( $email ); ?>" placeholder="<?php esc_attr_e( 'e.g. chairman@example.hp.gov.in', 'custom-shortcodes-components' ); ?>" />
		</div>

		<div class="csc-org-meta-group">
			<label for="csc_org_phone"><?php esc_html_e( 'Phone / Ext (Optional)', 'custom-shortcodes-components' ); ?></label>
			<input type="text" id="csc_org_phone" name="csc_org_phone" value="<?php echo esc_attr( $phone ); ?>" placeholder="<?php esc_attr_e( 'e.g. 0177-2621234', 'custom-shortcodes-components' ); ?>" />
		</div>
	</div>

	<script>
	jQuery(document).ready(function($) {
		$('.csc-upload-org-photo-btn').on('click', function(e) {
			e.preventDefault();
			var frame = wp.media({
				title: 'Select Member Photo / Cutout',
				button: { text: 'Use this Photo' },
				multiple: false
			});
			frame.on('select', function() {
				var attachment = frame.state().get('selection').first().toJSON();
				$('#csc_org_photo_url').val(attachment.url);
				$('#csc_org_photo_preview').html('<img src="' + attachment.url + '" style="max-height: 100px; border-radius: 6px; border: 1px solid #e2e8f0;" />');
			});
			frame.open();
		});
	});
	</script>
	<?php
}

/**
 * Save Org Member meta fields.
 *
 * @param int $post_id Post ID.
 */
function csc_save_org_member_meta( $post_id ) {
	if ( ! isset( $_POST['csc_org_member_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['csc_org_member_meta_nonce'] ) ), 'csc_save_org_member_meta_action' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$fields = array(
		'csc_org_level'        => 'sanitize_text_field',
		'csc_org_designation'  => 'sanitize_text_field',
		'csc_org_cadre'        => 'sanitize_text_field',
		'csc_org_avatar_type'  => 'sanitize_text_field',
		'csc_org_avatar_color' => 'sanitize_text_field',
		'csc_org_photo_url'    => 'esc_url_raw',
		'csc_org_email'        => 'sanitize_email',
		'csc_org_phone'        => 'sanitize_text_field',
	);

	foreach ( $fields as $field => $sanitizer ) {
		if ( isset( $_POST[ $field ] ) ) {
			$val = call_user_func( $sanitizer, wp_unslash( $_POST[ $field ] ) );
			update_post_meta( $post_id, '_' . $field, $val );
		}
	}
}
add_action( 'save_post_csc_org_member', 'csc_save_org_member_meta' );

/**
 * AJAX handler for One-Click Demo Data Generation.
 */
function csc_ajax_seed_org_chart_data() {
	check_ajax_referer( 'csc_org_seed_nonce', 'nonce' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => __( 'Unauthorized user.', 'custom-shortcodes-components' ) ) );
	}

	// 1. Ensure category exists
	$cat_term = term_exists( 'Board of Directors', 'csc_org_category' );
	if ( ! $cat_term ) {
		$cat_term = wp_insert_term( 'Board of Directors', 'csc_org_category', array( 'slug' => 'board-of-directors' ) );
	}
	$cat_id = is_array( $cat_term ) ? $cat_term['term_id'] : (int) $cat_term;

	// Default Chairman image relative path
	$default_photo_url = CSC_URL . 'assets/images/org-chart/dr-dhani-ram-shandil.png';

	$demo_members = array(
		array(
			'name'         => 'Dr. (Col.) Dhani Ram Shandil',
			'level'        => 'apex',
			'designation'  => 'Chairman',
			'cadre'        => '',
			'avatar_color' => 'green',
			'photo_url'    => $default_photo_url,
			'order'        => 0,
		),
		array(
			'name'         => 'Sh. Chander Prakash Verma',
			'level'        => 'subordinate',
			'designation'  => 'Director',
			'cadre'        => '(IAS)',
			'avatar_color' => 'blue',
			'photo_url'    => '',
			'order'        => 1,
		),
		array(
			'name'         => 'Sh. Vijay Vardhan',
			'level'        => 'subordinate',
			'designation'  => 'Director',
			'cadre'        => '(IAS)',
			'avatar_color' => 'green',
			'photo_url'    => '',
			'order'        => 2,
		),
		array(
			'name'         => 'Sh. Sumit Khimta',
			'level'        => 'subordinate',
			'designation'  => 'Director',
			'cadre'        => '(IAS)',
			'avatar_color' => 'peach',
			'photo_url'    => '',
			'order'        => 3,
		),
		array(
			'name'         => 'Sh. Manoj Kumar',
			'level'        => 'subordinate',
			'designation'  => 'Director',
			'cadre'        => 'IAS',
			'avatar_color' => 'blue',
			'photo_url'    => '',
			'order'        => 4,
		),
		array(
			'name'         => 'Sh. Mohammad Latif',
			'level'        => 'subordinate',
			'designation'  => 'Non Official Director',
			'cadre'        => '',
			'avatar_color' => 'green',
			'photo_url'    => '',
			'order'        => 5,
		),
		array(
			'name'         => 'Sh. Ashok Messy',
			'level'        => 'subordinate',
			'designation'  => 'Non Official Director',
			'cadre'        => '',
			'avatar_color' => 'blue',
			'photo_url'    => '',
			'order'        => 6,
		),
	);

	$created_count = 0;
	foreach ( $demo_members as $m ) {
		// Check if already exists
		$existing = get_page_by_title( $m['name'], OBJECT, 'csc_org_member' );
		if ( ! $existing ) {
			$post_id = wp_insert_post( array(
				'post_title'  => $m['name'],
				'post_type'   => 'csc_org_member',
				'post_status' => 'publish',
				'menu_order'  => $m['order'],
			) );

			if ( ! is_wp_error( $post_id ) ) {
				update_post_meta( $post_id, '_csc_org_level', $m['level'] );
				update_post_meta( $post_id, '_csc_org_designation', $m['designation'] );
				update_post_meta( $post_id, '_csc_org_cadre', $m['cadre'] );
				update_post_meta( $post_id, '_csc_org_avatar_color', $m['avatar_color'] );
				if ( ! empty( $m['photo_url'] ) ) {
					update_post_meta( $post_id, '_csc_org_photo_url', $m['photo_url'] );
				}
				if ( $cat_id ) {
					wp_set_object_terms( $post_id, array( (int) $cat_id ), 'csc_org_category' );
				}
				$created_count++;
			}
		}
	}

	wp_send_json_success( array(
		'message' => sprintf( __( 'Successfully seeded %d sample Org Chart members.', 'custom-shortcodes-components' ), $created_count ),
		'count'   => $created_count,
	) );
}
add_action( 'wp_ajax_csc_seed_org_chart_data', 'csc_ajax_seed_org_chart_data' );
