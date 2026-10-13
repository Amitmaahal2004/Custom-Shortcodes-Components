<?php
/**
 * Meta Boxes: Success Story Details (Loan, Scheme, Video, 4 Sections, Gallery)
 *
 * @package CustomShortcodesComponents
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register Meta Boxes for csc_story.
 */
function csc_register_story_meta_boxes() {
	add_meta_box(
		'csc_story_details_meta_box',
		__( 'Story Key Details (Loan, Scheme, Date & Video)', 'custom-shortcodes-components' ),
		'csc_render_story_details_meta_box',
		'csc_story',
		'normal',
		'high'
	);

	add_meta_box(
		'csc_story_structured_sections_meta_box',
		__( 'Detailed Story Sections (4 Information Cards)', 'custom-shortcodes-components' ),
		'csc_render_story_sections_meta_box',
		'csc_story',
		'normal',
		'default'
	);

	add_meta_box(
		'csc_story_gallery_meta_box',
		__( 'Glimpses of Our Initiatives (Gallery Photos)', 'custom-shortcodes-components' ),
		'csc_render_story_gallery_meta_box',
		'csc_story',
		'normal',
		'default'
	);
}
add_action( 'add_meta_boxes', 'csc_register_story_meta_boxes' );

/**
 * Enqueue admin scripts & styles for Story meta boxes.
 *
 * @param string $hook Current admin page hook.
 */
function csc_story_admin_enqueue_assets( $hook ) {
	global $post_type;

	if ( 'csc_story' !== $post_type ) {
		return;
	}

	wp_enqueue_media();
}
add_action( 'admin_enqueue_scripts', 'csc_story_admin_enqueue_assets' );

/**
 * Render Story Key Details Meta Box.
 *
 * @param WP_Post $post Current post.
 */
function csc_render_story_details_meta_box( $post ) {
	wp_nonce_field( 'csc_save_story_meta_action', 'csc_story_meta_nonce' );

	$loan_amount   = get_post_meta( $post->ID, '_csc_story_loan_amount', true );
	$scheme        = get_post_meta( $post->ID, '_csc_story_scheme', true );
	$sanction_date = get_post_meta( $post->ID, '_csc_story_sanction_date', true );
	$district      = get_post_meta( $post->ID, '_csc_story_district', true );
	$video_url     = get_post_meta( $post->ID, '_csc_story_video_url', true );
	$video_poster  = get_post_meta( $post->ID, '_csc_story_video_poster', true );
	$view_more_url = get_post_meta( $post->ID, '_csc_story_view_more_url', true );
	?>
	<style>
		.csc-meta-grid-2 {
			display: grid;
			grid-template-columns: 1fr 1fr;
			gap: 16px;
			margin-bottom: 16px;
		}
		.csc-meta-group {
			margin-bottom: 16px;
		}
		.csc-meta-group label {
			display: block;
			font-weight: 600;
			margin-bottom: 5px;
			color: #1e293b;
		}
		.csc-meta-group input[type="text"],
		.csc-meta-group input[type="url"],
		.csc-meta-group textarea {
			width: 100%;
			padding: 8px 10px;
			border-radius: 4px;
			border: 1px solid #cbd5e1;
		}
		.csc-meta-hint {
			font-size: 12px;
			color: #64748b;
			margin-top: 4px;
		}
		.csc-gallery-uploader-grid {
			display: grid;
			grid-template-columns: repeat(4, 1fr);
			gap: 16px;
		}
		.csc-gallery-card {
			border: 1px dashed #cbd5e1;
			border-radius: 8px;
			padding: 12px;
			background: #f8fafc;
			text-align: center;
		}
		.csc-gallery-card img {
			max-width: 100%;
			height: 110px;
			object-fit: cover;
			border-radius: 4px;
			display: block;
			margin: 0 auto 8px;
		}
	</style>

	<div class="csc-meta-grid-2">
		<div class="csc-meta-group">
			<label for="csc_story_loan_amount"><?php esc_html_e( 'Loan Amount', 'custom-shortcodes-components' ); ?></label>
			<input type="text" id="csc_story_loan_amount" name="csc_story_loan_amount" value="<?php echo esc_attr( $loan_amount ); ?>" placeholder="<?php esc_attr_e( 'e.g. Rs.3,55,000/-', 'custom-shortcodes-components' ); ?>" />
			<div class="csc-meta-hint"><?php esc_html_e( 'Displayed beside the Rupee icon in slider and cards.', 'custom-shortcodes-components' ); ?></div>
		</div>

		<div class="csc-meta-group">
			<label for="csc_story_scheme"><?php esc_html_e( 'Scheme Name', 'custom-shortcodes-components' ); ?></label>
			<input type="text" id="csc_story_scheme" name="csc_story_scheme" value="<?php echo esc_attr( $scheme ); ?>" placeholder="<?php esc_attr_e( 'e.g. Steel Fabrication Roller Shutter.', 'custom-shortcodes-components' ); ?>" />
			<div class="csc-meta-hint"><?php esc_html_e( 'Displayed beside the Briefcase/Scheme icon.', 'custom-shortcodes-components' ); ?></div>
		</div>
	</div>

	<div class="csc-meta-grid-2">
		<div class="csc-meta-group">
			<label for="csc_story_sanction_date"><?php esc_html_e( 'Sanction Date', 'custom-shortcodes-components' ); ?></label>
			<input type="text" id="csc_story_sanction_date" name="csc_story_sanction_date" value="<?php echo esc_attr( $sanction_date ); ?>" placeholder="<?php esc_attr_e( 'e.g. 14 May 2021', 'custom-shortcodes-components' ); ?>" />
			<div class="csc-meta-hint"><?php esc_html_e( 'Displayed beside the Calendar icon in slider and single post.', 'custom-shortcodes-components' ); ?></div>
		</div>

		<div class="csc-meta-group">
			<label for="csc_story_district"><?php esc_html_e( 'District', 'custom-shortcodes-components' ); ?></label>
			<input type="text" id="csc_story_district" name="csc_story_district" value="<?php echo esc_attr( $district ); ?>" placeholder="<?php esc_attr_e( 'e.g. Chamba, Hamirpur, Sirmour, Solan, Una', 'custom-shortcodes-components' ); ?>" />
			<div class="csc-meta-hint"><?php esc_html_e( 'Displayed beside the Location Pin icon in cards grid.', 'custom-shortcodes-components' ); ?></div>
		</div>
	</div>

	<div class="csc-meta-group">
		<label for="csc_story_video_url"><?php esc_html_e( 'Video URL (For Home Slider)', 'custom-shortcodes-components' ); ?></label>
		<input type="text" id="csc_story_video_url" name="csc_story_video_url" value="<?php echo esc_attr( $video_url ); ?>" placeholder="<?php esc_attr_e( 'e.g. https://www.youtube.com/watch?v=... or direct .mp4 file URL', 'custom-shortcodes-components' ); ?>" />
		<div class="csc-meta-hint"><?php esc_html_e( 'If provided, the Home Page slider displays a playable video with custom controls.', 'custom-shortcodes-components' ); ?></div>
	</div>

	<div class="csc-meta-grid-2">
		<div class="csc-meta-group">
			<label for="csc_story_video_poster"><?php esc_html_e( 'Video Poster / Cover Image URL', 'custom-shortcodes-components' ); ?></label>
			<div style="display: flex; gap: 8px;">
				<input type="text" id="csc_story_video_poster" name="csc_story_video_poster" value="<?php echo esc_attr( $video_poster ); ?>" placeholder="<?php esc_attr_e( 'Image URL or select from media', 'custom-shortcodes-components' ); ?>" />
				<button type="button" class="button csc-upload-single-img-btn" data-target="#csc_story_video_poster"><?php esc_html_e( 'Upload', 'custom-shortcodes-components' ); ?></button>
			</div>
			<div class="csc-meta-hint"><?php esc_html_e( 'Optional. If left blank, the Featured Image is used as video poster.', 'custom-shortcodes-components' ); ?></div>
		</div>

		<div class="csc-meta-group">
			<label for="csc_story_view_more_url"><?php esc_html_e( 'Custom "View More Stories >" URL', 'custom-shortcodes-components' ); ?></label>
			<input type="text" id="csc_story_view_more_url" name="csc_story_view_more_url" value="<?php echo esc_attr( $view_more_url ); ?>" placeholder="<?php esc_attr_e( 'e.g. /stories/ or custom grid page URL', 'custom-shortcodes-components' ); ?>" />
			<div class="csc-meta-hint"><?php esc_html_e( 'Defaults to the Stories archive / grid cards page.', 'custom-shortcodes-components' ); ?></div>
		</div>
	</div>
	<?php
}

/**
 * Render 4 Information Sections Meta Box.
 *
 * @param WP_Post $post Current post.
 */
function csc_render_story_sections_meta_box( $post ) {
	$sec1_title   = get_post_meta( $post->ID, '_csc_story_sec1_title', true );
	$sec1_content = get_post_meta( $post->ID, '_csc_story_sec1_content', true );
	$sec2_title   = get_post_meta( $post->ID, '_csc_story_sec2_title', true );
	$sec2_content = get_post_meta( $post->ID, '_csc_story_sec2_content', true );
	$sec3_title   = get_post_meta( $post->ID, '_csc_story_sec3_title', true );
	$sec3_content = get_post_meta( $post->ID, '_csc_story_sec3_content', true );
	$sec4_title   = get_post_meta( $post->ID, '_csc_story_sec4_title', true );
	$sec4_content = get_post_meta( $post->ID, '_csc_story_sec4_content', true );

	if ( empty( $sec1_title ) && 'auto-draft' === $post->post_status ) {
		$sec1_title = __( 'About The Beneficiary', 'custom-shortcodes-components' );
		$sec2_title = __( 'The Challenge', 'custom-shortcodes-components' );
		$sec3_title = __( 'Our Support', 'custom-shortcodes-components' );
		$sec4_title = __( 'Impact & Outcome', 'custom-shortcodes-components' );
	}
	?>
	<p style="color: #64748b; font-size: 13px; margin-top: 0;">
		<?php esc_html_e( 'These 4 sections will be rendered as the 2x2 information cards on the single story page (matching Screenshot 3).', 'custom-shortcodes-components' ); ?>
	</p>

	<div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px; margin-bottom: 16px;">
		<h4 style="margin: 0 0 10px; color: #0a3b75;">1. <?php esc_html_e( 'About The Beneficiary', 'custom-shortcodes-components' ); ?></h4>
		<div class="csc-meta-group">
			<label><?php esc_html_e( 'Card Title', 'custom-shortcodes-components' ); ?></label>
			<input type="text" name="csc_story_sec1_title" value="<?php echo esc_attr( $sec1_title ); ?>" />
		</div>
		<div class="csc-meta-group">
			<label><?php esc_html_e( 'Description', 'custom-shortcodes-components' ); ?></label>
			<textarea name="csc_story_sec1_content" rows="3"><?php echo esc_textarea( $sec1_content ); ?></textarea>
		</div>
	</div>

	<div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px; margin-bottom: 16px;">
		<h4 style="margin: 0 0 10px; color: #0a3b75;">2. <?php esc_html_e( 'The Challenge', 'custom-shortcodes-components' ); ?></h4>
		<div class="csc-meta-group">
			<label><?php esc_html_e( 'Card Title', 'custom-shortcodes-components' ); ?></label>
			<input type="text" name="csc_story_sec2_title" value="<?php echo esc_attr( $sec2_title ); ?>" />
		</div>
		<div class="csc-meta-group">
			<label><?php esc_html_e( 'Description', 'custom-shortcodes-components' ); ?></label>
			<textarea name="csc_story_sec2_content" rows="3"><?php echo esc_textarea( $sec2_content ); ?></textarea>
		</div>
	</div>

	<div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px; margin-bottom: 16px;">
		<h4 style="margin: 0 0 10px; color: #0a3b75;">3. <?php esc_html_e( 'Our Support', 'custom-shortcodes-components' ); ?></h4>
		<div class="csc-meta-group">
			<label><?php esc_html_e( 'Card Title', 'custom-shortcodes-components' ); ?></label>
			<input type="text" name="csc_story_sec3_title" value="<?php echo esc_attr( $sec3_title ); ?>" />
		</div>
		<div class="csc-meta-group">
			<label><?php esc_html_e( 'Description', 'custom-shortcodes-components' ); ?></label>
			<textarea name="csc_story_sec3_content" rows="3"><?php echo esc_textarea( $sec3_content ); ?></textarea>
		</div>
	</div>

	<div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px;">
		<h4 style="margin: 0 0 10px; color: #0a3b75;">4. <?php esc_html_e( 'Impact & Outcome', 'custom-shortcodes-components' ); ?></h4>
		<div class="csc-meta-group">
			<label><?php esc_html_e( 'Card Title', 'custom-shortcodes-components' ); ?></label>
			<input type="text" name="csc_story_sec4_title" value="<?php echo esc_attr( $sec4_title ); ?>" />
		</div>
		<div class="csc-meta-group">
			<label><?php esc_html_e( 'Description', 'custom-shortcodes-components' ); ?></label>
			<textarea name="csc_story_sec4_content" rows="3"><?php echo esc_textarea( $sec4_content ); ?></textarea>
		</div>
	</div>
	<?php
}

/**
 * Render Glimpses Gallery Meta Box.
 *
 * @param WP_Post $post Current post.
 */
function csc_render_story_gallery_meta_box( $post ) {
	$img1 = get_post_meta( $post->ID, '_csc_story_gallery_img_1', true );
	$img2 = get_post_meta( $post->ID, '_csc_story_gallery_img_2', true );
	$img3 = get_post_meta( $post->ID, '_csc_story_gallery_img_3', true );
	$img4 = get_post_meta( $post->ID, '_csc_story_gallery_img_4', true );
	?>
	<p style="color: #64748b; font-size: 13px; margin-top: 0;">
		<?php esc_html_e( 'Select up to 4 photos to show in the "GLIMPSES OF OUR INITIATIVES" section on the single story page.', 'custom-shortcodes-components' ); ?>
	</p>

	<div class="csc-gallery-uploader-grid">
		<?php for ( $i = 1; $i <= 4; $i++ ) : 
			$val = ${'img' . $i};
		?>
			<div class="csc-gallery-card">
				<div class="csc-img-preview-box" id="csc_gallery_preview_<?php echo $i; ?>">
					<?php if ( ! empty( $val ) ) : ?>
						<img src="<?php echo esc_url( $val ); ?>" alt="" />
					<?php else : ?>
						<div style="height: 110px; display: flex; align-items: center; justify-content: center; background: #e2e8f0; border-radius: 4px; color: #94a3b8; font-size: 12px; margin-bottom: 8px;">
							<?php esc_html_e( 'No Image', 'custom-shortcodes-components' ); ?>
						</div>
					<?php endif; ?>
				</div>
				<input type="text" id="csc_story_gallery_img_<?php echo $i; ?>" name="csc_story_gallery_img_<?php echo $i; ?>" value="<?php echo esc_attr( $val ); ?>" placeholder="<?php esc_attr_e( 'Image URL', 'custom-shortcodes-components' ); ?>" style="width: 100%; font-size: 11px; margin-bottom: 6px;" />
				<button type="button" class="button button-small csc-upload-gallery-img-btn" data-target="#csc_story_gallery_img_<?php echo $i; ?>" data-preview="#csc_gallery_preview_<?php echo $i; ?>">
					<?php esc_html_e( 'Select Image', 'custom-shortcodes-components' ); ?>
				</button>
			</div>
		<?php endfor; ?>
	</div>

	<script>
	jQuery(document).ready(function($) {
		// Single image upload button.
		$('.csc-upload-single-img-btn').on('click', function(e) {
			e.preventDefault();
			var target = $(this).data('target');
			var frame = wp.media({
				title: 'Select Image',
				button: { text: 'Use this image' },
				multiple: false
			});
			frame.on('select', function() {
				var attachment = frame.state().get('selection').first().toJSON();
				$(target).val(attachment.url);
			});
			frame.open();
		});

		// Gallery image upload button.
		$('.csc-upload-gallery-img-btn').on('click', function(e) {
			e.preventDefault();
			var target = $(this).data('target');
			var preview = $(this).data('preview');
			var frame = wp.media({
				title: 'Select Gallery Image',
				button: { text: 'Select Image' },
				multiple: false
			});
			frame.on('select', function() {
				var attachment = frame.state().get('selection').first().toJSON();
				$(target).val(attachment.url);
				$(preview).html('<img src="' + attachment.url + '" style="max-width:100%; height:110px; object-fit:cover; border-radius:4px; display:block; margin:0 auto 8px;" />');
			});
			frame.open();
		});
	});
	</script>
	<?php
}

/**
 * Save meta box values on post save.
 *
 * @param int $post_id Post ID.
 */
function csc_save_story_meta( $post_id ) {
	if ( ! isset( $_POST['csc_story_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['csc_story_meta_nonce'] ) ), 'csc_save_story_meta_action' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$fields = array(
		'csc_story_loan_amount'    => 'sanitize_text_field',
		'csc_story_scheme'         => 'sanitize_text_field',
		'csc_story_sanction_date'  => 'sanitize_text_field',
		'csc_story_district'       => 'sanitize_text_field',
		'csc_story_video_url'      => 'esc_url_raw',
		'csc_story_video_poster'   => 'esc_url_raw',
		'csc_story_view_more_url'  => 'esc_url_raw',
		'csc_story_sec1_title'     => 'sanitize_text_field',
		'csc_story_sec1_content'   => 'wp_kses_post',
		'csc_story_sec2_title'     => 'sanitize_text_field',
		'csc_story_sec2_content'   => 'wp_kses_post',
		'csc_story_sec3_title'     => 'sanitize_text_field',
		'csc_story_sec3_content'   => 'wp_kses_post',
		'csc_story_sec4_title'     => 'sanitize_text_field',
		'csc_story_sec4_content'   => 'wp_kses_post',
		'csc_story_gallery_img_1'  => 'esc_url_raw',
		'csc_story_gallery_img_2'  => 'esc_url_raw',
		'csc_story_gallery_img_3'  => 'esc_url_raw',
		'csc_story_gallery_img_4'  => 'esc_url_raw',
	);

	foreach ( $fields as $field => $sanitizer ) {
		if ( isset( $_POST[ $field ] ) ) {
			$val = call_user_func( $sanitizer, wp_unslash( $_POST[ $field ] ) );
			update_post_meta( $post_id, '_' . $field, $val );
		}
	}

	// Also sync district to taxonomy term if entered.
	if ( ! empty( $_POST['csc_story_district'] ) ) {
		$district_name = sanitize_text_field( wp_unslash( $_POST['csc_story_district'] ) );
		wp_set_object_terms( $post_id, $district_name, 'csc_story_district' );
	}
}
add_action( 'save_post_csc_story', 'csc_save_story_meta' );
