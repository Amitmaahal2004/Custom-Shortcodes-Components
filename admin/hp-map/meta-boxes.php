<?php
/**
 * Meta Boxes and Custom Fields for HP Map Items (district_map)
 *
 * @package HimachalDynamicContentManager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register Meta Boxes for HP Map Items.
 */
function csc_district_map_add_meta_boxes() {
	add_meta_box(
		'csc_district_meta_box',
		__( 'District Configuration & Map Positioning', 'custom-shortcodes-components' ),
		'csc_district_map_render_meta_box',
		'district_map',
		'normal',
		'high'
	);

}
add_action( 'add_meta_boxes', 'csc_district_map_add_meta_boxes' );

/**
 * Enqueue scripts and styles for HP Map admin screens.
 */
function csc_district_map_admin_assets( $hook ) {
	global $post_type;

	if ( in_array( $post_type, array( 'district_map', 'announcement', 'important_download' ), true ) ) {
		wp_enqueue_media();

		wp_add_inline_style(
			'wp-admin',
			'
			.csc-meta-section { margin-bottom: 22px; padding: 16px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; }
			.csc-meta-section h4 { margin: 0 0 14px; padding-bottom: 8px; border-bottom: 2px solid #0b3b60; color: #0b3b60; font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px; }
			.csc-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
			.csc-grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; }
			.csc-field-group { margin-bottom: 12px; }
			.csc-field-group label { display: block; font-weight: 600; margin-bottom: 4px; font-size: 12px; color: #334155; }
			.csc-field-group input[type="text"], .csc-field-group input[type="number"], .csc-field-group select { width: 100%; border-radius: 4px; border: 1px solid #cbd5e1; padding: 6px 8px; }
			.csc-field-desc { font-size: 11px; color: #64748b; margin-top: 4px; }
			.csc-img-preview-box { max-width: 140px; margin-top: 8px; border: 1px solid #cbd5e1; border-radius: 4px; padding: 4px; background: #fff; display: inline-block; }
			.csc-img-preview-box img { max-width: 100%; height: auto; display: block; border-radius: 3px; }
			.csc-coord-badge { display: inline-block; background: #0b3b60; color: #fff; padding: 2px 6px; border-radius: 3px; font-size: 10px; font-weight: 700; margin-right: 4px; }
		'
		);

		wp_add_inline_script(
			'common',
			'
			jQuery(document).ready(function($) {
				// District Image upload
				$(document).on("click", ".csc-upload-btn", function(e) {
					e.preventDefault();
					var btn = $(this);
					var targetInput = $("#" + btn.data("target"));
					var previewContainer = $("#" + btn.data("preview"));

					var frame = wp.media({
						title: "Select or Upload District Photo Texture",
						button: { text: "Use This Image" },
						multiple: false
					});

					frame.on("select", function() {
						var attachment = frame.state().get("selection").first().toJSON();
						targetInput.val(attachment.url);
						previewContainer.html(\'<div class="csc-img-preview-box"><img src="\' + attachment.url + \'" alt="" /><button type="button" class="button button-link-delete csc-remove-img-btn" style="margin-top:4px;">Remove</button></div>\');
					});

					frame.open();
				});

				// Remove Image
				$(document).on("click", ".csc-remove-img-btn", function(e) {
					e.preventDefault();
					var container = $(this).closest(".csc-img-field-wrap");
					container.find(".csc-img-url-input").val("");
					container.find(".csc-preview-area").html("");
				});
			});
		'
		);
	}
}
add_action( 'admin_enqueue_scripts', 'csc_district_map_admin_assets' );

/**
 * Render District Map Meta Box.
 */
function csc_district_map_render_meta_box( $post ) {
	wp_nonce_field( 'csc_save_district_meta_nonce', 'csc_district_nonce' );

	$val          = get_post_meta( $post->ID, '_hdd_district_val', true );
	$short_val    = get_post_meta( $post->ID, '_hdd_district_short_val', true );
	$svg_id       = get_post_meta( $post->ID, '_hdd_district_svg_id', true );
	$dot_x        = get_post_meta( $post->ID, '_hdd_district_dot_x', true );
	$dot_y        = get_post_meta( $post->ID, '_hdd_district_dot_y', true );
	$line_start_x = get_post_meta( $post->ID, '_hdd_district_line_start_x', true );
	$line_start_y = get_post_meta( $post->ID, '_hdd_district_line_start_y', true );
	$line_end_x   = get_post_meta( $post->ID, '_hdd_district_line_end_x', true );
	$line_end_y   = get_post_meta( $post->ID, '_hdd_district_line_end_y', true );
	$label_x      = get_post_meta( $post->ID, '_hdd_district_label_x', true );
	$label_y      = get_post_meta( $post->ID, '_hdd_district_label_y', true );
	$label_align  = get_post_meta( $post->ID, '_hdd_district_label_align', true );
	$image_url    = get_post_meta( $post->ID, '_hdd_district_image_url', true );
	$is_enabled   = get_post_meta( $post->ID, '_hdd_district_enabled', true );
	if ( '' === $is_enabled ) {
		$is_enabled = '1';
	}
	$sort_order   = get_post_meta( $post->ID, '_hdd_district_sort', true );

	$districts_list = array(
		'CHA' => 'Chamba',
		'KAN' => 'Kangra',
		'UNA' => 'Una',
		'HAM' => 'Hamirpur',
		'BIL' => 'Bilaspur',
		'SOL' => 'Solan',
		'SIR' => 'Sirmaur',
		'SHI' => 'Shimla',
		'MAN' => 'Mandi',
		'KUL' => 'Kullu',
		'LAS' => 'Lahul & Spiti',
		'KIN' => 'Kinnaur',
	);
	?>
	<div class="csc-meta-container">
		<!-- 1. GENERAL CONFIGURATION -->
		<div class="csc-meta-section">
			<h4><span class="dashicons dashicons-admin-settings"></span> <?php esc_html_e( 'General Configuration', 'custom-shortcodes-components' ); ?></h4>
			<div class="csc-grid-3">
				<div class="csc-field-group">
					<label for="hdd_district_val"><?php esc_html_e( 'Population / Value', 'custom-shortcodes-components' ); ?> <span style="color:red;">*</span></label>
					<input type="number" step="any" id="hdd_district_val" name="hdd_district_val" value="<?php echo esc_attr( $val ); ?>" placeholder="e.g. 519080" required />
					<div class="csc-field-desc"><?php esc_html_e( 'Numeric value displayed next to district name and totaled in bottom card.', 'custom-shortcodes-components' ); ?></div>
				</div>

				<div class="csc-field-group">
					<label for="hdd_district_short_val"><?php esc_html_e( 'Short Display Value (Optional)', 'custom-shortcodes-components' ); ?></label>
					<input type="text" id="hdd_district_short_val" name="hdd_district_short_val" value="<?php echo esc_attr( $short_val ); ?>" placeholder="e.g. 5.19 Lakh" />
					<div class="csc-field-desc"><?php esc_html_e( 'Optional short label representation.', 'custom-shortcodes-components' ); ?></div>
				</div>

				<div class="csc-field-group">
					<label for="hdd_district_svg_id"><?php esc_html_e( 'District Map Region', 'custom-shortcodes-components' ); ?> <span style="color:red;">*</span></label>
					<select id="hdd_district_svg_id" name="hdd_district_svg_id" required>
						<option value=""><?php esc_html_e( '-- Select District --', 'custom-shortcodes-components' ); ?></option>
						<?php foreach ( $districts_list as $code => $dname ) : ?>
							<option value="<?php echo esc_attr( $code ); ?>" <?php selected( strtoupper( $svg_id ), $code ); ?>><?php echo esc_html( $dname . ' (' . $code . ')' ); ?></option>
						<?php endforeach; ?>
					</select>
					<div class="csc-field-desc"><?php esc_html_e( 'Connects this post to the SVG polygon shape on the map.', 'custom-shortcodes-components' ); ?></div>
				</div>
			</div>

			<div class="csc-grid-2" style="margin-top: 10px;">
				<div class="csc-field-group">
					<label for="hdd_district_enabled">
						<input type="checkbox" id="hdd_district_enabled" name="hdd_district_enabled" value="1" <?php checked( $is_enabled, '1' ); ?> />
						<strong><?php esc_html_e( 'Enable on Map', 'custom-shortcodes-components' ); ?></strong>
					</label>
					<div class="csc-field-desc"><?php esc_html_e( 'Uncheck to temporarily hide this district entry from the map.', 'custom-shortcodes-components' ); ?></div>
				</div>

				<div class="csc-field-group">
					<label for="hdd_district_sort"><?php esc_html_e( 'Display Order', 'custom-shortcodes-components' ); ?></label>
					<input type="number" id="hdd_district_sort" name="hdd_district_sort" value="<?php echo esc_attr( $sort_order ); ?>" placeholder="1" style="max-width:120px;" />
				</div>
			</div>
		</div>

		<!-- 2. MAP POSITIONING & COORDINATES (OPTIONAL OVERRIDES) -->
		<div class="csc-meta-section">
			<h4><span class="dashicons dashicons-location"></span> <?php esc_html_e( 'Positioning & Coordinates (Leave blank to use default coordinates)', 'custom-shortcodes-components' ); ?></h4>
			<p class="csc-field-desc" style="margin-bottom: 12px;">
				<?php esc_html_e( 'Coordinates automatically default to built-in presets. You only need to enter values here if customizing dot or label positions.', 'custom-shortcodes-components' ); ?>
			</p>

			<div class="csc-grid-3">
				<!-- Blue Marker Dot -->
				<div style="background:#fff; padding:10px; border-radius:4px; border:1px solid #e2e8f0;">
					<span class="csc-coord-badge">Dot</span> <strong><?php esc_html_e( 'Marker Dot', 'custom-shortcodes-components' ); ?></strong>
					<div class="csc-grid-2" style="margin-top:8px;">
						<div class="csc-field-group">
							<label>Dot X</label>
							<input type="number" step="any" name="hdd_district_dot_x" value="<?php echo esc_attr( $dot_x ); ?>" placeholder="Default" />
						</div>
						<div class="csc-field-group">
							<label>Dot Y</label>
							<input type="number" step="any" name="hdd_district_dot_y" value="<?php echo esc_attr( $dot_y ); ?>" placeholder="Default" />
						</div>
					</div>
				</div>

				<!-- Connector Line End -->
				<div style="background:#fff; padding:10px; border-radius:4px; border:1px solid #e2e8f0;">
					<span class="csc-coord-badge">Line</span> <strong><?php esc_html_e( 'Line End', 'custom-shortcodes-components' ); ?></strong>
					<div class="csc-grid-2" style="margin-top:8px;">
						<div class="csc-field-group">
							<label>End X</label>
							<input type="number" step="any" name="hdd_district_line_end_x" value="<?php echo esc_attr( $line_end_x ); ?>" placeholder="Default" />
						</div>
						<div class="csc-field-group">
							<label>End Y</label>
							<input type="number" step="any" name="hdd_district_line_end_y" value="<?php echo esc_attr( $line_end_y ); ?>" placeholder="Default" />
						</div>
					</div>
				</div>

				<!-- Text Label -->
				<div style="background:#fff; padding:10px; border-radius:4px; border:1px solid #e2e8f0;">
					<span class="csc-coord-badge">Text</span> <strong><?php esc_html_e( 'Label Position', 'custom-shortcodes-components' ); ?></strong>
					<div class="csc-grid-2" style="margin-top:8px;">
						<div class="csc-field-group">
							<label>Label X</label>
							<input type="number" step="any" name="hdd_district_label_x" value="<?php echo esc_attr( $label_x ); ?>" placeholder="Default" />
						</div>
						<div class="csc-field-group">
							<label>Label Y</label>
							<input type="number" step="any" name="hdd_district_label_y" value="<?php echo esc_attr( $label_y ); ?>" placeholder="Default" />
						</div>
					</div>
					<div class="csc-field-group" style="margin-top:4px;">
						<label>Alignment</label>
						<select name="hdd_district_label_align">
							<option value=""><?php esc_html_e( 'Default', 'custom-shortcodes-components' ); ?></option>
							<option value="left" <?php selected( $label_align, 'left' ); ?>>Left</option>
							<option value="center" <?php selected( $label_align, 'center' ); ?>>Center</option>
							<option value="right" <?php selected( $label_align, 'right' ); ?>>Right</option>
						</select>
					</div>
				</div>
			</div>
		</div>

		<!-- 3. PHOTO TEXTURE / IMAGE -->
		<div class="csc-meta-section">
			<h4><span class="dashicons dashicons-format-image"></span> <?php esc_html_e( 'District Photo Texture (Optional Custom Image)', 'custom-shortcodes-components' ); ?></h4>
			<p class="csc-field-desc" style="margin-bottom: 8px;">
				<?php esc_html_e( 'Leave blank to use the default high-resolution bundled landscape photo for this district. You can also set a Featured Image for this post.', 'custom-shortcodes-components' ); ?>
			</p>
			<div class="csc-img-field-wrap">
				<input type="text" id="hdd_district_image_url" name="hdd_district_image_url" class="csc-img-url-input" value="<?php echo esc_attr( $image_url ); ?>" placeholder="https://..." style="width:75%; max-width:550px;" />
				<button type="button" class="button button-secondary csc-upload-btn" data-target="hdd_district_image_url" data-preview="hdd-img-preview"><?php esc_html_e( 'Choose Image', 'custom-shortcodes-components' ); ?></button>
				<div id="hdd-img-preview" class="csc-preview-area">
					<?php if ( ! empty( $image_url ) ) : ?>
						<div class="csc-img-preview-box">
							<img src="<?php echo esc_url( $image_url ); ?>" alt="" />
							<button type="button" class="button button-link-delete csc-remove-img-btn" style="margin-top:4px;"><?php esc_html_e( 'Remove', 'custom-shortcodes-components' ); ?></button>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
	<?php
}

/**
 * Save District Map Post Meta.
 */
function csc_district_map_save_meta( $post_id, $post ) {
	if ( ! isset( $_POST['csc_district_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['csc_district_nonce'] ) ), 'csc_save_district_meta_nonce' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$fields = array(
		'hdd_district_val'          => '_hdd_district_val',
		'hdd_district_short_val'    => '_hdd_district_short_val',
		'hdd_district_svg_id'       => '_hdd_district_svg_id',
		'hdd_district_dot_x'        => '_hdd_district_dot_x',
		'hdd_district_dot_y'        => '_hdd_district_dot_y',
		'hdd_district_line_start_x' => '_hdd_district_line_start_x',
		'hdd_district_line_start_y' => '_hdd_district_line_start_y',
		'hdd_district_line_end_x'   => '_hdd_district_line_end_x',
		'hdd_district_line_end_y'   => '_hdd_district_line_end_y',
		'hdd_district_label_x'      => '_hdd_district_label_x',
		'hdd_district_label_y'      => '_hdd_district_label_y',
		'hdd_district_label_align'  => '_hdd_district_label_align',
		'hdd_district_image_url'    => '_hdd_district_image_url',
		'hdd_district_sort'         => '_hdd_district_sort',
	);

	foreach ( $fields as $input_key => $meta_key ) {
		if ( isset( $_POST[ $input_key ] ) ) {
			$val = sanitize_text_field( wp_unslash( $_POST[ $input_key ] ) );
			update_post_meta( $post_id, $meta_key, $val );
		}
	}

	$is_enabled = isset( $_POST['hdd_district_enabled'] ) ? '1' : '0';
	update_post_meta( $post_id, '_hdd_district_enabled', $is_enabled );
}
add_action( 'save_post_district_map', 'csc_district_map_save_meta', 10, 2 );
