<?php
/**
 * Meta Boxes and Media Library Integration for Announcements & Important Downloads
 *
 * @package CustomShortcodesComponents
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register Meta Boxes for Announcements and Downloads.
 */
function csc_sidebar_cards_register_meta_boxes() {
	// Announcement Meta Box
	add_meta_box(
		'csc_announcement_details_meta_box',
		__( 'Announcement Details & Configuration', 'custom-shortcodes-components' ),
		'csc_render_announcement_meta_box',
		'announcement',
		'normal',
		'high'
	);

	// Important Download Meta Box
	add_meta_box(
		'csc_download_details_meta_box',
		__( 'File Attachment & Download Settings', 'custom-shortcodes-components' ),
		'csc_render_download_meta_box',
		'important_download',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'csc_sidebar_cards_register_meta_boxes' );

/**
 * Enqueue scripts and styles for announcement & download admin screens.
 *
 * @param string $hook Admin page hook.
 */
function csc_sidebar_cards_admin_assets( $hook ) {
	global $post_type;

	if ( ! in_array( $post_type, array( 'announcement', 'important_download' ), true ) ) {
		return;
	}

	// Enqueue WordPress native Media Uploader
	wp_enqueue_media();

	// Meta Box Admin Styles
	wp_add_inline_style(
		'wp-admin',
		'
		.csc-sc-panel {
			background: #ffffff;
			border: 1px solid #e2e8f0;
			border-radius: 8px;
			padding: 20px;
			margin-top: 10px;
		}
		.csc-sc-row {
			margin-bottom: 18px;
		}
		.csc-sc-row:last-child {
			margin-bottom: 0;
		}
		.csc-sc-row label {
			display: block;
			font-weight: 600;
			font-size: 13px;
			color: #1e293b;
			margin-bottom: 6px;
		}
		.csc-sc-row .csc-desc {
			font-size: 12px;
			color: #64748b;
			margin-top: 4px;
		}
		.csc-sc-input {
			width: 100%;
			max-width: 520px;
			padding: 8px 12px;
			border: 1px solid #cbd5e1;
			border-radius: 6px;
			font-size: 14px;
			background: #f8fafc;
			transition: border-color 0.2s;
		}
		.csc-sc-input:focus {
			border-color: #0b3b60;
			outline: none;
			background: #ffffff;
		}
		.csc-file-preview-card {
			display: flex;
			align-items: center;
			justify-content: space-between;
			background: #f1f5f9;
			border: 1px solid #cbd5e1;
			border-radius: 6px;
			padding: 12px 16px;
			max-width: 520px;
			margin-top: 10px;
		}
		.csc-file-meta {
			display: flex;
			align-items: center;
			gap: 10px;
			overflow: hidden;
		}
		.csc-file-badge {
			background: #ef4444;
			color: #ffffff;
			font-size: 11px;
			font-weight: 800;
			padding: 3px 8px;
			border-radius: 4px;
			text-transform: uppercase;
		}
		.csc-file-name {
			font-weight: 600;
			font-size: 13px;
			color: #0f172a;
			text-decoration: none;
			overflow: hidden;
			text-overflow: ellipsis;
			white-space: nowrap;
			max-width: 320px;
		}
		.csc-file-name:hover {
			color: #0b3b60;
			text-decoration: underline;
		}
		'
	);

	// Meta Box Admin Scripts for Media Selector
	wp_add_inline_script(
		'common',
		'
		jQuery(document).ready(function($) {
			// WordPress Media Library File Picker
			$(document).on("click", "#csc-select-file-btn", function(e) {
				e.preventDefault();

				var frame = wp.media({
					title: "Select or Upload Download File",
					button: { text: "Attach File to Download" },
					multiple: false
				});

				frame.on("select", function() {
					var attachment = frame.state().get("selection").first().toJSON();
					$("#csc_download_file_url").val(attachment.url);
					$("#csc_download_file_id").val(attachment.id);

					var ext = attachment.url.split(".").pop().toUpperCase();
					if (!ext || ext.length > 5) {
						ext = "FILE";
					}

					var filename = attachment.filename || attachment.title || attachment.url.split("/").pop();

					var html = \'<div class="csc-file-preview-card">\' +
						\'<div class="csc-file-meta">\' +
							\'<span class="csc-file-badge">\' + ext + \'</span>\' +
							\'<a href="\' + attachment.url + \'" target="_blank" rel="noopener noreferrer" class="csc-file-name">\' + filename + \'</a>\' +
						\'</div>\' +
						\'<button type="button" class="button button-link-delete" id="csc-remove-file-btn">Remove</button>\' +
					\'</div>\';

					$("#csc-file-preview-wrapper").html(html);
				});

				frame.open();
			});

			// Remove Attached File
			$(document).on("click", "#csc-remove-file-btn", function(e) {
				e.preventDefault();
				$("#csc_download_file_url").val("");
				$("#csc_download_file_id").val("");
				$("#csc-file-preview-wrapper").empty();
			});
		});
		'
	);
}
add_action( 'admin_enqueue_scripts', 'csc_sidebar_cards_admin_assets' );

/**
 * Render Announcement Meta Box.
 *
 * @param WP_Post $post Current post object.
 */
function csc_render_announcement_meta_box( $post ) {
	wp_nonce_field( 'csc_save_announcement_meta_nonce', 'csc_announcement_meta_nonce' );

	$date_val = get_post_meta( $post->ID, '_announcement_date', true );
	if ( empty( $date_val ) ) {
		$date_val = get_post_meta( $post->ID, '_hdd_announcement_date', true );
	}
	if ( empty( $date_val ) ) {
		$date_val = current_time( 'Y-m-d' );
	}

	$link_url = get_post_meta( $post->ID, '_announcement_url', true );
	if ( empty( $link_url ) ) {
		$link_url = get_post_meta( $post->ID, '_hdd_announcement_link', true );
	}

	$is_new = get_post_meta( $post->ID, '_announcement_is_new', true );
	if ( '' === $is_new ) {
		$is_new = get_post_meta( $post->ID, '_hdd_announcement_is_new', true );
	}

	$display_order = $post->menu_order;
	?>
	<div class="csc-sc-panel">
		<!-- Announcement Date -->
		<div class="csc-sc-row">
			<label for="csc_announcement_date">
				<?php esc_html_e( 'Announcement Date', 'custom-shortcodes-components' ); ?>
			</label>
			<input type="date" id="csc_announcement_date" name="csc_announcement_date" value="<?php echo esc_attr( $date_val ); ?>" class="csc-sc-input" style="max-width:240px;" />
			<div class="csc-desc">
				<?php esc_html_e( 'The date rendered in the highlighted green date badge (e.g. 24 Jan 2026).', 'custom-shortcodes-components' ); ?>
			</div>
		</div>

		<!-- Optional Link / URL -->
		<div class="csc-sc-row">
			<label for="csc_announcement_url">
				<?php esc_html_e( 'Target Link / URL (Optional)', 'custom-shortcodes-components' ); ?>
			</label>
			<input type="url" id="csc_announcement_url" name="csc_announcement_url" value="<?php echo esc_attr( $link_url ); ?>" placeholder="https://example.com/announcement-details" class="csc-sc-input" />
			<div class="csc-desc">
				<?php esc_html_e( 'If provided, the announcement title will link directly to this URL.', 'custom-shortcodes-components' ); ?>
			</div>
		</div>

		<!-- "NEW" Badge Toggle -->
		<div class="csc-sc-row">
			<label for="csc_announcement_is_new" style="display:inline-flex; align-items:center; gap:8px; cursor:pointer;">
				<input type="checkbox" id="csc_announcement_is_new" name="csc_announcement_is_new" value="1" <?php checked( $is_new, '1' ); ?> />
				<span><strong><?php esc_html_e( 'Display "NEW" Badge', 'custom-shortcodes-components' ); ?></strong></span>
			</label>
			<div class="csc-desc" style="margin-left:24px;">
				<?php esc_html_e( 'Adds an animated / highlighted green "NEW" badge next to the announcement title.', 'custom-shortcodes-components' ); ?>
			</div>
		</div>

		<!-- Display Order -->
		<div class="csc-sc-row">
			<label for="csc_announcement_order">
				<?php esc_html_e( 'Display Order', 'custom-shortcodes-components' ); ?>
			</label>
			<input type="number" id="csc_announcement_order" name="csc_announcement_order" value="<?php echo esc_attr( $display_order ); ?>" step="1" class="csc-sc-input" style="max-width:140px;" />
			<div class="csc-desc">
				<?php esc_html_e( 'Display position in ascending order (lower numbers like 1, 2, 3 appear first). Default is 0.', 'custom-shortcodes-components' ); ?>
			</div>
		</div>
	</div>
	<?php
}

/**
 * Save Announcement Meta Data.
 *
 * @param int $post_id Post ID.
 */
function csc_save_announcement_meta( $post_id ) {
	if ( ! isset( $_POST['csc_announcement_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['csc_announcement_meta_nonce'] ) ), 'csc_save_announcement_meta_nonce' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	// 1. Announcement Date
	if ( isset( $_POST['csc_announcement_date'] ) ) {
		$clean_date = sanitize_text_field( wp_unslash( $_POST['csc_announcement_date'] ) );
		update_post_meta( $post_id, '_announcement_date', $clean_date );
		update_post_meta( $post_id, '_hdd_announcement_date', $clean_date );
	}

	// 2. Target Link / URL
	if ( isset( $_POST['csc_announcement_url'] ) ) {
		$clean_url = esc_url_raw( wp_unslash( $_POST['csc_announcement_url'] ) );
		update_post_meta( $post_id, '_announcement_url', $clean_url );
		update_post_meta( $post_id, '_hdd_announcement_link', $clean_url );
	}

	// 3. "NEW" Badge
	$is_new = isset( $_POST['csc_announcement_is_new'] ) ? '1' : '0';
	update_post_meta( $post_id, '_announcement_is_new', $is_new );
	update_post_meta( $post_id, '_hdd_announcement_is_new', $is_new );

	// 4. Display Order (sync with menu_order)
	if ( isset( $_POST['csc_announcement_order'] ) ) {
		$order = intval( $_POST['csc_announcement_order'] );
		update_post_meta( $post_id, '_announcement_order', $order );

		// Prevent infinite save_post loop
		remove_action( 'save_post_announcement', 'csc_save_announcement_meta' );
		wp_update_post(
			array(
				'ID'         => $post_id,
				'menu_order' => $order,
			)
		);
		add_action( 'save_post_announcement', 'csc_save_announcement_meta' );
	}
}
add_action( 'save_post_announcement', 'csc_save_announcement_meta' );

/**
 * Render Important Download Meta Box.
 *
 * @param WP_Post $post Current post object.
 */
function csc_render_download_meta_box( $post ) {
	wp_nonce_field( 'csc_save_download_meta_nonce', 'csc_download_meta_nonce' );

	$file_url = get_post_meta( $post->ID, '_download_file_url', true );
	if ( empty( $file_url ) ) {
		$file_url = get_post_meta( $post->ID, '_hdd_download_file_url', true );
	}

	$file_id = get_post_meta( $post->ID, '_download_file_id', true );
	if ( empty( $file_id ) ) {
		$file_id = get_post_meta( $post->ID, '_hdd_download_file_id', true );
	}

	$display_order = $post->menu_order;

	$filename = '';
	$file_ext = 'FILE';
	if ( ! empty( $file_url ) ) {
		$path_part = parse_url( $file_url, PHP_URL_PATH );
		$filename  = basename( $path_part );
		$ext       = strtoupper( pathinfo( $path_part, PATHINFO_EXTENSION ) );
		if ( ! empty( $ext ) ) {
			$file_ext = $ext;
		}
	}
	?>
	<div class="csc-sc-panel">
		<!-- File Selector -->
		<div class="csc-sc-row">
			<label><?php esc_html_e( 'Attached Download File (PDF, Word, Excel, ZIP, etc.)', 'custom-shortcodes-components' ); ?></label>

			<input type="hidden" id="csc_download_file_url" name="csc_download_file_url" value="<?php echo esc_attr( $file_url ); ?>" />
			<input type="hidden" id="csc_download_file_id" name="csc_download_file_id" value="<?php echo esc_attr( $file_id ); ?>" />

			<button type="button" class="button button-primary" id="csc-select-file-btn" style="background:#0b3b60; border-color:#0b3b60;">
				<span class="dashicons dashicons-upload" style="margin-top:3px; margin-right:4px;"></span>
				<?php esc_html_e( 'Select / Upload File from Media Library', 'custom-shortcodes-components' ); ?>
			</button>

			<div class="csc-desc">
				<?php esc_html_e( 'Click to choose any file from the WordPress Media Library or upload a new one.', 'custom-shortcodes-components' ); ?>
			</div>

			<div id="csc-file-preview-wrapper">
				<?php if ( ! empty( $file_url ) ) : ?>
					<div class="csc-file-preview-card">
						<div class="csc-file-meta">
							<span class="csc-file-badge"><?php echo esc_html( $file_ext ); ?></span>
							<a href="<?php echo esc_url( $file_url ); ?>" target="_blank" rel="noopener noreferrer" class="csc-file-name">
								<?php echo esc_html( $filename ); ?>
							</a>
						</div>
						<button type="button" class="button button-link-delete" id="csc-remove-file-btn">
							<?php esc_html_e( 'Remove', 'custom-shortcodes-components' ); ?>
						</button>
					</div>
				<?php endif; ?>
			</div>
		</div>

		<!-- Direct URL Override -->
		<div class="csc-sc-row" style="margin-top:16px;">
			<label for="csc_manual_file_url"><?php esc_html_e( 'Or Specify Direct File URL (Optional override)', 'custom-shortcodes-components' ); ?></label>
			<input type="text" id="csc_manual_file_url" name="csc_manual_file_url" value="<?php echo esc_attr( $file_url ); ?>" placeholder="https://example.com/uploads/document.pdf" class="csc-sc-input" oninput="document.getElementById('csc_download_file_url').value = this.value;" />
		</div>

		<!-- Display Order -->
		<div class="csc-sc-row" style="margin-top:16px;">
			<label for="csc_download_order">
				<?php esc_html_e( 'Display Order', 'custom-shortcodes-components' ); ?>
			</label>
			<input type="number" id="csc_download_order" name="csc_download_order" value="<?php echo esc_attr( $display_order ); ?>" step="1" class="csc-sc-input" style="max-width:140px;" />
			<div class="csc-desc">
				<?php esc_html_e( 'Display position in ascending order (lower numbers like 1, 2, 3 appear first). Default is 0.', 'custom-shortcodes-components' ); ?>
			</div>
		</div>
	</div>
	<?php
}

/**
 * Save Important Download Meta Data.
 *
 * @param int $post_id Post ID.
 */
function csc_save_download_meta( $post_id ) {
	if ( ! isset( $_POST['csc_download_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['csc_download_meta_nonce'] ) ), 'csc_save_download_meta_nonce' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	// 1. File URL
	$file_url = '';
	if ( ! empty( $_POST['csc_download_file_url'] ) ) {
		$file_url = esc_url_raw( wp_unslash( $_POST['csc_download_file_url'] ) );
	} elseif ( ! empty( $_POST['csc_manual_file_url'] ) ) {
		$file_url = esc_url_raw( wp_unslash( $_POST['csc_manual_file_url'] ) );
	}

	update_post_meta( $post_id, '_download_file_url', $file_url );
	update_post_meta( $post_id, '_hdd_download_file_url', $file_url );

	// 2. File Attachment ID
	if ( isset( $_POST['csc_download_file_id'] ) ) {
		$file_id = absint( $_POST['csc_download_file_id'] );
		update_post_meta( $post_id, '_download_file_id', $file_id );
		update_post_meta( $post_id, '_hdd_download_file_id', $file_id );
	}

	// 3. Display Order (sync with menu_order)
	if ( isset( $_POST['csc_download_order'] ) ) {
		$order = intval( $_POST['csc_download_order'] );
		update_post_meta( $post_id, '_download_order', $order );

		// Prevent infinite save_post loop
		remove_action( 'save_post_important_download', 'csc_save_download_meta' );
		wp_update_post(
			array(
				'ID'         => $post_id,
				'menu_order' => $order,
			)
		);
		add_action( 'save_post_important_download', 'csc_save_download_meta' );
	}
}
add_action( 'save_post_important_download', 'csc_save_download_meta' );
