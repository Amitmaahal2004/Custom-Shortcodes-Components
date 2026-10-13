<?php
/**
 * Meta Boxes: Scheme Card Settings (Color, Icon, Link, Description)
 *
 * @package CustomShortcodesComponents
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register Scheme meta boxes.
 */
function csc_register_scheme_meta_boxes() {
	add_meta_box(
		'csc_scheme_settings_meta_box',
		__( 'Scheme Card Settings & Styling', 'custom-shortcodes-components' ),
		'csc_render_scheme_settings_meta_box',
		'csc_scheme',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'csc_register_scheme_meta_boxes' );

/**
 * Enqueue admin scripts & styles for Scheme meta boxes.
 *
 * @param string $hook Current admin page hook.
 */
function csc_scheme_admin_enqueue_assets( $hook ) {
	global $post_type;

	if ( 'csc_scheme' !== $post_type ) {
		return;
	}

	wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_script( 'wp-color-picker' );
	wp_enqueue_media();
}
add_action( 'admin_enqueue_scripts', 'csc_scheme_admin_enqueue_assets' );

/**
 * Render Scheme Card Settings meta box HTML.
 *
 * @param WP_Post $post Current post object.
 */
function csc_render_scheme_settings_meta_box( $post ) {
	wp_nonce_field( 'csc_save_scheme_meta_action', 'csc_scheme_meta_nonce' );

	$accent_color = get_post_meta( $post->ID, '_csc_scheme_color', true );
	if ( empty( $accent_color ) ) {
		$accent_color = '#109b48'; // Default Green from Card 1.
	}

	$icon_type = get_post_meta( $post->ID, '_csc_scheme_icon_type', true );
	if ( empty( $icon_type ) ) {
		$icon_type = 'preset';
	}

	$icon_preset = get_post_meta( $post->ID, '_csc_scheme_icon_preset', true );
	if ( empty( $icon_preset ) ) {
		$icon_preset = 'education';
	}

	$icon_image_id  = get_post_meta( $post->ID, '_csc_scheme_icon_image_id', true );
	$icon_image_url = ! empty( $icon_image_id ) ? wp_get_attachment_image_url( $icon_image_id, 'thumbnail' ) : '';

	$button_text = get_post_meta( $post->ID, '_csc_scheme_button_text', true );
	if ( '' === $button_text && 'auto-draft' === $post->post_status ) {
		$button_text = __( 'View Details', 'custom-shortcodes-components' );
	} elseif ( '' === $button_text ) {
		$button_text = __( 'View Details', 'custom-shortcodes-components' );
	}

	$button_url   = get_post_meta( $post->ID, '_csc_scheme_button_url', true );
	$target_blank = get_post_meta( $post->ID, '_csc_scheme_target_blank', true );
	$short_desc   = get_post_meta( $post->ID, '_csc_scheme_short_desc', true );

	$preset_icons = function_exists( 'csc_get_scheme_icon_presets' ) ? csc_get_scheme_icon_presets() : array();
	?>
	<style>
		.csc-meta-section {
			margin-bottom: 22px;
			padding-bottom: 20px;
			border-bottom: 1px solid #e2e8f0;
		}
		.csc-meta-section:last-child {
			border-bottom: none;
			margin-bottom: 0;
			padding-bottom: 0;
		}
		.csc-meta-label {
			font-size: 14px;
			font-weight: 600;
			color: #1e293b;
			display: block;
			margin-bottom: 6px;
		}
		.csc-color-swatches {
			display: flex;
			gap: 8px;
			align-items: center;
			margin-top: 8px;
			flex-wrap: wrap;
		}
		.csc-swatch-btn {
			width: 28px;
			height: 28px;
			border-radius: 50%;
			border: 2px solid #fff;
			box-shadow: 0 0 0 1px #cbd5e1;
			cursor: pointer;
			padding: 0;
			transition: transform 0.15s ease, box-shadow 0.15s ease;
		}
		.csc-swatch-btn:hover {
			transform: scale(1.15);
			box-shadow: 0 0 0 2px #0f172a;
		}
		.csc-preset-grid {
			display: grid;
			grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
			gap: 10px;
			margin-top: 10px;
		}
		.csc-preset-card {
			display: flex;
			flex-direction: column;
			align-items: center;
			justify-content: center;
			padding: 12px 8px;
			border: 2px solid #e2e8f0;
			border-radius: 8px;
			background: #f8fafc;
			cursor: pointer;
			text-align: center;
			transition: all 0.2s ease;
		}
		.csc-preset-card:hover {
			border-color: #94a3b8;
			background: #f1f5f9;
		}
		.csc-preset-card.is-active {
			border-color: #2563eb;
			background: #eff6ff;
			color: #1d4ed8;
		}
		.csc-preset-card input[type="radio"] {
			display: none;
		}
		.csc-preset-icon-wrap {
			margin-bottom: 6px;
			display: inline-flex;
		}
		.csc-preset-card span {
			font-size: 11px;
			font-weight: 500;
			line-height: 1.2;
		}
	</style>

	<div class="csc-meta-wrap">

		<!-- 1. Accent Color -->
		<div class="csc-meta-section">
			<label class="csc-meta-label" for="csc_scheme_color">
				<?php esc_html_e( 'Theme Accent Color', 'custom-shortcodes-components' ); ?>
			</label>
			<p class="description" style="margin-bottom: 8px;">
				<?php esc_html_e( 'Controls the card top border, circular icon badge, title color, and "View Details" link.', 'custom-shortcodes-components' ); ?>
			</p>

			<div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
				<input type="text" id="csc_scheme_color" name="csc_scheme_color" value="<?php echo esc_attr( $accent_color ); ?>" class="csc-color-field" data-default-color="#109b48" />
				
				<div class="csc-color-swatches" title="<?php esc_attr_e( 'Click any preset swatch to apply', 'custom-shortcodes-components' ); ?>">
					<span style="font-size: 12px; color: #64748b; font-weight: 500;"><?php esc_html_e( 'Presets:', 'custom-shortcodes-components' ); ?></span>
					<button type="button" class="csc-swatch-btn" data-color="#109b48" style="background:#109b48;" title="Green (Education Loan)"></button>
					<button type="button" class="csc-swatch-btn" data-color="#0a3b75" style="background:#0a3b75;" title="Dark Navy (Self Employment)"></button>
					<button type="button" class="csc-swatch-btn" data-color="#7b2cbf" style="background:#7b2cbf;" title="Purple (Skill Development)"></button>
					<button type="button" class="csc-swatch-btn" data-color="#e59819" style="background:#e59819;" title="Warm Gold (Women Empowerment)"></button>
					<button type="button" class="csc-swatch-btn" data-color="#e63946" style="background:#e63946;" title="Crimson"></button>
					<button type="button" class="csc-swatch-btn" data-color="#0d9488" style="background:#0d9488;" title="Teal"></button>
				</div>
			</div>
		</div>

		<!-- 2. Card Icon Selection -->
		<div class="csc-meta-section">
			<label class="csc-meta-label">
				<?php esc_html_e( 'Card Icon Badge', 'custom-shortcodes-components' ); ?>
			</label>
			<p class="description">
				<?php esc_html_e( 'Select a crisp built-in icon preset matching the scheme category, or upload your own custom image or SVG.', 'custom-shortcodes-components' ); ?>
			</p>

			<div style="margin: 12px 0;">
				<label style="margin-right: 20px; font-weight: 500; cursor: pointer;">
					<input type="radio" name="csc_scheme_icon_type" value="preset" <?php checked( $icon_type, 'preset' ); ?> id="csc_type_preset" />
					<?php esc_html_e( 'Built-in SVG Icon Preset', 'custom-shortcodes-components' ); ?>
				</label>
				<label style="font-weight: 500; cursor: pointer;">
					<input type="radio" name="csc_scheme_icon_type" value="image" <?php checked( $icon_type, 'image' ); ?> id="csc_type_image" />
					<?php esc_html_e( 'Custom Image / SVG Upload', 'custom-shortcodes-components' ); ?>
				</label>
			</div>

			<!-- Preset Icons Grid -->
			<div id="csc_preset_picker_wrap" style="<?php echo ( 'preset' === $icon_type ) ? '' : 'display:none;'; ?>">
				<div class="csc-preset-grid">
					<?php foreach ( $preset_icons as $preset_key => $preset_data ) : ?>
						<label class="csc-preset-card <?php echo ( $icon_preset === $preset_key ) ? 'is-active' : ''; ?>">
							<input type="radio" name="csc_scheme_icon_preset" value="<?php echo esc_attr( $preset_key ); ?>" <?php checked( $icon_preset, $preset_key ); ?> />
							<div class="csc-preset-icon-wrap">
								<?php echo csc_get_scheme_svg_icon( $preset_key, 28, $accent_color ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</div>
							<span><?php echo esc_html( $preset_data['label'] ); ?></span>
						</label>
					<?php endforeach; ?>
				</div>
			</div>

			<!-- Custom Image / SVG Uploader -->
			<div id="csc_image_picker_wrap" style="<?php echo ( 'image' === $icon_type ) ? '' : 'display:none;'; ?> margin-top: 14px;">
				<div style="display: flex; align-items: center; gap: 14px;">
					<div id="csc_icon_preview" style="width: 56px; height: 56px; border-radius: 50%; background: #f1f5f9; border: 1px dashed #cbd5e1; display: flex; align-items: center; justify-content: center; overflow: hidden;">
						<?php if ( ! empty( $icon_image_url ) ) : ?>
							<img src="<?php echo esc_url( $icon_image_url ); ?>" style="width: 36px; height: 36px; object-fit: contain;" alt="" />
						<?php else : ?>
							<span class="dashicons dashicons-format-image" style="color: #94a3b8; font-size: 24px; width: 24px; height: 24px;"></span>
						<?php endif; ?>
					</div>
					<div>
						<input type="hidden" name="csc_scheme_icon_image_id" id="csc_scheme_icon_image_id" value="<?php echo esc_attr( $icon_image_id ); ?>" />
						<button type="button" class="button button-secondary" id="csc_upload_icon_btn">
							<?php esc_html_e( 'Select / Upload Icon', 'custom-shortcodes-components' ); ?>
						</button>
						<button type="button" class="button button-link-delete" id="csc_remove_icon_btn" style="<?php echo empty( $icon_image_id ) ? 'display:none;' : ''; ?> margin-left: 8px;">
							<?php esc_html_e( 'Remove', 'custom-shortcodes-components' ); ?>
						</button>
					</div>
				</div>
				<p class="description" style="margin-top: 6px;">
					<?php esc_html_e( 'Recommended size: 64x64px or square SVG/PNG with transparent background.', 'custom-shortcodes-components' ); ?>
				</p>
			</div>
		</div>

		<!-- 3. Card Description -->
		<div class="csc-meta-section">
			<label class="csc-meta-label" for="csc_scheme_short_desc">
				<?php esc_html_e( 'Card Short Description', 'custom-shortcodes-components' ); ?>
			</label>
			<textarea id="csc_scheme_short_desc" name="csc_scheme_short_desc" rows="3" class="widefat" placeholder="e.g. Financial assistance for professional & technical education in India & abroad." style="padding: 8px;"><?php echo esc_textarea( $short_desc ); ?></textarea>
			<p class="description">
				<?php esc_html_e( 'Short summary displayed in the center of the slider card. If left blank, the standard post excerpt or content will be used.', 'custom-shortcodes-components' ); ?>
			</p>
		</div>

		<!-- 4. Action Button & Link -->
		<div class="csc-meta-section">
			<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 14px;">
				<div>
					<label class="csc-meta-label" for="csc_scheme_button_text">
						<?php esc_html_e( 'Button Text', 'custom-shortcodes-components' ); ?>
					</label>
					<input type="text" id="csc_scheme_button_text" name="csc_scheme_button_text" value="<?php echo esc_attr( $button_text ); ?>" class="widefat" placeholder="View Details" />
				</div>
				<div>
					<label class="csc-meta-label" for="csc_scheme_button_url">
						<?php esc_html_e( 'Custom Target URL', 'custom-shortcodes-components' ); ?>
					</label>
					<input type="url" id="csc_scheme_button_url" name="csc_scheme_button_url" value="<?php echo esc_attr( $button_url ); ?>" class="widefat" placeholder="https://example.com/scheme-details" />
				</div>
			</div>
			<p class="description" style="margin-top: 6px;">
				<?php esc_html_e( 'Leave Target URL blank to automatically link to this scheme\'s WordPress page.', 'custom-shortcodes-components' ); ?>
			</p>

			<p style="margin-top: 10px; margin-bottom: 0;">
				<label style="cursor: pointer; font-weight: 500;">
					<input type="checkbox" name="csc_scheme_target_blank" value="1" <?php checked( $target_blank, '1' ); ?> />
					<?php esc_html_e( 'Open button link in a new tab (target="_blank")', 'custom-shortcodes-components' ); ?>
				</label>
			</p>
		</div>

	</div>

	<script>
	jQuery(document).ready(function($){
		// Initialize WP Color Picker with live preset icon color updates.
		var $colorInput = $('#csc_scheme_color');
		if ($.fn.wpColorPicker) {
			$colorInput.wpColorPicker({
				change: function(event, ui){
					var newColor = ui.color.toString();
					updatePresetSVGs(newColor);
				},
				clear: function(){
					updatePresetSVGs('#109b48');
				}
			});
		}

		// Preset color swatch click.
		$('.csc-swatch-btn').on('click', function(e){
			e.preventDefault();
			var color = $(this).data('color');
			$colorInput.wpColorPicker('color', color);
			updatePresetSVGs(color);
		});

		function updatePresetSVGs(color) {
			$('.csc-preset-card svg').each(function(){
				$(this).attr('stroke', color);
			});
		}

		// Icon type toggle (preset vs image).
		$('input[name="csc_scheme_icon_type"]').on('change', function(){
			if ($(this).val() === 'preset') {
				$('#csc_preset_picker_wrap').slideDown(150);
				$('#csc_image_picker_wrap').slideUp(150);
			} else {
				$('#csc_preset_picker_wrap').slideUp(150);
				$('#csc_image_picker_wrap').slideDown(150);
			}
		});

		// Preset icon card selection.
		$('.csc-preset-card').on('click', function(){
			$('.csc-preset-card').removeClass('is-active');
			$(this).addClass('is-active');
			$(this).find('input[type="radio"]').prop('checked', true);
		});

		// Media Uploader for Custom Icon/Image.
		var mediaFrame;
		$('#csc_upload_icon_btn').on('click', function(e){
			e.preventDefault();
			if (mediaFrame) {
				mediaFrame.open();
				return;
			}
			mediaFrame = wp.media({
				title: '<?php echo esc_js( __( 'Select Scheme Icon / Image', 'custom-shortcodes-components' ) ); ?>',
				button: { text: '<?php echo esc_js( __( 'Use this icon', 'custom-shortcodes-components' ) ); ?>' },
				multiple: false
			});
			mediaFrame.on('select', function(){
				var attachment = mediaFrame.state().get('selection').first().toJSON();
				$('#csc_scheme_icon_image_id').val(attachment.id);
				var thumbUrl = attachment.sizes && attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url;
				$('#csc_icon_preview').html('<img src="' + thumbUrl + '" style="width:36px;height:36px;object-fit:contain;" alt="" />');
				$('#csc_remove_icon_btn').show();
			});
			mediaFrame.open();
		});

		$('#csc_remove_icon_btn').on('click', function(e){
			e.preventDefault();
			$('#csc_scheme_icon_image_id').val('');
			$('#csc_icon_preview').html('<span class="dashicons dashicons-format-image" style="color:#94a3b8;font-size:24px;width:24px;height:24px;"></span>');
			$(this).hide();
		});
	});
	</script>
	<?php
}

/**
 * Save Scheme Card Settings meta box data.
 *
 * @param int $post_id
 */
function csc_save_scheme_meta_box( $post_id ) {
	// Verify nonce.
	if ( ! isset( $_POST['csc_scheme_meta_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['csc_scheme_meta_nonce'] ), 'csc_save_scheme_meta_action' ) ) {
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

	// Save Accent Color.
	if ( isset( $_POST['csc_scheme_color'] ) ) {
		$color = sanitize_hex_color( wp_unslash( $_POST['csc_scheme_color'] ) );
		update_post_meta( $post_id, '_csc_scheme_color', $color ? $color : '#109b48' );
	}

	// Save Icon Type.
	if ( isset( $_POST['csc_scheme_icon_type'] ) ) {
		$icon_type = sanitize_key( $_POST['csc_scheme_icon_type'] );
		update_post_meta( $post_id, '_csc_scheme_icon_type', in_array( $icon_type, array( 'preset', 'image' ), true ) ? $icon_type : 'preset' );
	}

	// Save Icon Preset.
	if ( isset( $_POST['csc_scheme_icon_preset'] ) ) {
		$preset = sanitize_key( $_POST['csc_scheme_icon_preset'] );
		update_post_meta( $post_id, '_csc_scheme_icon_preset', $preset );
	}

	// Save Custom Icon Image ID.
	if ( isset( $_POST['csc_scheme_icon_image_id'] ) ) {
		$img_id = intval( $_POST['csc_scheme_icon_image_id'] );
		if ( $img_id > 0 ) {
			update_post_meta( $post_id, '_csc_scheme_icon_image_id', $img_id );
		} else {
			delete_post_meta( $post_id, '_csc_scheme_icon_image_id' );
		}
	}

	// Save Short Description.
	if ( isset( $_POST['csc_scheme_short_desc'] ) ) {
		update_post_meta( $post_id, '_csc_scheme_short_desc', sanitize_textarea_field( wp_unslash( $_POST['csc_scheme_short_desc'] ) ) );
	}

	// Save Button Text.
	if ( isset( $_POST['csc_scheme_button_text'] ) ) {
		update_post_meta( $post_id, '_csc_scheme_button_text', sanitize_text_field( wp_unslash( $_POST['csc_scheme_button_text'] ) ) );
	}

	// Save Button URL.
	if ( isset( $_POST['csc_scheme_button_url'] ) ) {
		$btn_url = esc_url_raw( trim( wp_unslash( $_POST['csc_scheme_button_url'] ) ) );
		if ( ! empty( $btn_url ) ) {
			update_post_meta( $post_id, '_csc_scheme_button_url', $btn_url );
		} else {
			delete_post_meta( $post_id, '_csc_scheme_button_url' );
		}
	}

	// Save target blank.
	$target_blank = ! empty( $_POST['csc_scheme_target_blank'] ) ? '1' : '0';
	update_post_meta( $post_id, '_csc_scheme_target_blank', $target_blank );
}
add_action( 'save_post_csc_scheme', 'csc_save_scheme_meta_box' );
