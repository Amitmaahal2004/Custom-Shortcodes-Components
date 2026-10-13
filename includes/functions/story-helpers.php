<?php
/**
 * Success Stories Helpers, SVG Icons & Template Hook
 *
 * @package CustomShortcodesComponents
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return inline SVG icons for story components.
 *
 * @param string $icon_name Icon identifier.
 * @param int    $size      Icon width/height.
 * @param string $color     Stroke or fill color.
 * @return string Inline SVG HTML.
 */
function csc_get_story_svg_icon( $icon_name, $size = 20, $color = '#2563eb' ) {
	$size_attr  = intval( $size );
	$color_attr = esc_attr( $color );

	switch ( $icon_name ) {
		case 'rupee':
			// Circular Indian Rupee icon matching screenshot.
			return '<svg width="' . $size_attr . '" height="' . $size_attr . '" viewBox="0 0 24 24" fill="none" stroke="' . $color_attr . '" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'
				. '<circle cx="12" cy="12" r="10"/>'
				. '<path d="M6 7h12M6 11h8M6 15l7 5M9 7v10"/>'
				. '<path d="M14 7a3 3 0 0 1 0 6H9"/>'
				. '</svg>';

		case 'scheme':
		case 'briefcase':
			// Briefcase / Suitcase scheme icon matching screenshot.
			return '<svg width="' . $size_attr . '" height="' . $size_attr . '" viewBox="0 0 24 24" fill="none" stroke="' . $color_attr . '" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'
				. '<rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>'
				. '<path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/>'
				. '<path d="M12 12v2"/>'
				. '<path d="M2 13h20"/>'
				. '</svg>';

		case 'calendar':
			// Calendar date icon matching screenshot.
			return '<svg width="' . $size_attr . '" height="' . $size_attr . '" viewBox="0 0 24 24" fill="none" stroke="' . $color_attr . '" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'
				. '<rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>'
				. '<line x1="16" y1="2" x2="16" y2="6"/>'
				. '<line x1="8" y1="2" x2="8" y2="6"/>'
				. '<line x1="3" y1="10" x2="21" y2="10"/>'
				. '<circle cx="8" cy="14" r="1" fill="' . $color_attr . '"/>'
				. '<circle cx="12" cy="14" r="1" fill="' . $color_attr . '"/>'
				. '<circle cx="16" cy="14" r="1" fill="' . $color_attr . '"/>'
				. '</svg>';

		case 'location':
		case 'pin':
			// Map pin location icon matching screenshot.
			return '<svg width="' . $size_attr . '" height="' . $size_attr . '" viewBox="0 0 24 24" fill="none" stroke="' . $color_attr . '" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'
				. '<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>'
				. '<circle cx="12" cy="10" r="3"/>'
				. '</svg>';

		case 'play':
			// Play button triangle.
			return '<svg width="' . $size_attr . '" height="' . $size_attr . '" viewBox="0 0 24 24" fill="' . $color_attr . '" aria-hidden="true">'
				. '<polygon points="6 4 20 12 6 20 6 4"/>'
				. '</svg>';

		case 'pause':
			// Pause button.
			return '<svg width="' . $size_attr . '" height="' . $size_attr . '" viewBox="0 0 24 24" fill="' . $color_attr . '" aria-hidden="true">'
				. '<rect x="6" y="4" width="4" height="16"/>'
				. '<rect x="14" y="4" width="4" height="16"/>'
				. '</svg>';

		case 'fullscreen':
			// Fullscreen icon.
			return '<svg width="' . $size_attr . '" height="' . $size_attr . '" viewBox="0 0 24 24" fill="none" stroke="' . $color_attr . '" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'
				. '<path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"/>'
				. '</svg>';

		case 'chevron_right':
			// Right chevron arrow.
			return '<svg width="' . $size_attr . '" height="' . $size_attr . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'
				. '<polyline points="9 18 15 12 9 6"/>'
				. '</svg>';

		default:
			return '';
	}
}

/**
 * Filter template_include to load plugin single template for csc_story.
 *
 * @param string $template Current template file path.
 * @return string Modified template file path.
 */
function csc_story_template_include( $template ) {
	if ( is_singular( 'csc_story' ) ) {
		// First check if active theme has single-csc_story.php.
		$theme_template = locate_template( array( 'single-csc_story.php' ) );
		if ( ! empty( $theme_template ) ) {
			return $theme_template;
		}

		// Fallback to plugin template.
		$plugin_template = CSC_PATH . 'templates/single-csc_story.php';
		if ( file_exists( $plugin_template ) ) {
			return $plugin_template;
		}
	}

	return $template;
}
add_filter( 'template_include', 'csc_story_template_include', 99 );

/**
 * Convert video URL to embed or HTML5 player data.
 *
 * @param string $url Video URL.
 * @return array
 */
function csc_parse_video_source( $url ) {
	$url = trim( $url );
	if ( empty( $url ) ) {
		return array( 'type' => 'none', 'url' => '' );
	}

	// Check YouTube.
	if ( preg_match( '/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/ ]{11})/i', $url, $matches ) ) {
		return array(
			'type'     => 'youtube',
			'video_id' => $matches[1],
			'embed'    => 'https://www.youtube.com/embed/' . $matches[1] . '?autoplay=1&rel=0',
			'url'      => $url,
		);
	}

	// Check Vimeo.
	if ( preg_match( '/vimeo\.com\/(?:channels\/(?:\w+\/)?|groups\/([^\/]*)\/videos\/|album\/(\d+)\/video\/|)(\d+)/i', $url, $matches ) ) {
		$vimeo_id = end( $matches );
		return array(
			'type'     => 'vimeo',
			'video_id' => $vimeo_id,
			'embed'    => 'https://player.vimeo.com/video/' . $vimeo_id . '?autoplay=1',
			'url'      => $url,
		);
	}

	// Direct MP4 / WebM / HTML5 video.
	return array(
		'type' => 'html5',
		'url'  => $url,
	);
}
