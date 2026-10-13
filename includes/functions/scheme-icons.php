<?php
/**
 * Scheme Icon Library & SVG Helpers
 *
 * @package CustomShortcodesComponents
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return list of available icon presets.
 *
 * @return array
 */
function csc_get_scheme_icon_presets() {
	return array(
		'education' => array(
			'label' => __( 'Graduation Cap (Education)', 'custom-shortcodes-components' ),
			'group' => 'Schemes',
		),
		'briefcase' => array(
			'label' => __( 'Briefcase (Self Employment)', 'custom-shortcodes-components' ),
			'group' => 'Schemes',
		),
		'users'     => array(
			'label' => __( 'Community / People (Skill Development)', 'custom-shortcodes-components' ),
			'group' => 'Schemes',
		),
		'woman'     => array(
			'label' => __( 'Woman Avatar (Empowerment)', 'custom-shortcodes-components' ),
			'group' => 'Schemes',
		),
		'money'     => array(
			'label' => __( 'Finance & Loan (Coins/Money)', 'custom-shortcodes-components' ),
			'group' => 'Finance',
		),
		'award'     => array(
			'label' => __( 'Award / Certificate', 'custom-shortcodes-components' ),
			'group' => 'General',
		),
		'shield'    => array(
			'label' => __( 'Security / Protection', 'custom-shortcodes-components' ),
			'group' => 'General',
		),
		'heart'     => array(
			'label' => __( 'Health & Care', 'custom-shortcodes-components' ),
			'group' => 'General',
		),
		'leaf'      => array(
			'label' => __( 'Growth & Agriculture', 'custom-shortcodes-components' ),
			'group' => 'General',
		),
		'building'  => array(
			'label' => __( 'Enterprise & Infrastructure', 'custom-shortcodes-components' ),
			'group' => 'General',
		),
	);
}

/**
 * Return inline SVG code for a given icon preset.
 *
 * @param string $preset Icon key.
 * @param int    $size   Width/Height in px.
 * @param string $color  Stroke/Fill color.
 * @return string Inline SVG.
 */
function csc_get_scheme_svg_icon( $preset, $size = 36, $color = 'currentColor' ) {
	$size_attr  = intval( $size );
	$color_attr = esc_attr( $color );

	switch ( $preset ) {
		case 'education':
			// Graduation Cap (matches card 1).
			return '<svg width="' . $size_attr . '" height="' . $size_attr . '" viewBox="0 0 24 24" fill="none" stroke="' . $color_attr . '" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'
				. '<path d="M22 10v6M2 10l10-5 10 5-10 5z"/>'
				. '<path d="M6 12v5c3 3 9 3 12 0v-5"/>'
				. '</svg>';

		case 'briefcase':
			// Briefcase (matches card 2).
			return '<svg width="' . $size_attr . '" height="' . $size_attr . '" viewBox="0 0 24 24" fill="none" stroke="' . $color_attr . '" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'
				. '<rect x="2" y="7" width="20" height="14" rx="3" ry="3"/>'
				. '<path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/>'
				. '<path d="M12 12v2"/>'
				. '<path d="M2 13h20"/>'
				. '</svg>';

		case 'users':
			// Users / Community / Team (matches card 3).
			return '<svg width="' . $size_attr . '" height="' . $size_attr . '" viewBox="0 0 24 24" fill="none" stroke="' . $color_attr . '" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'
				. '<circle cx="12" cy="7" r="3"/>'
				. '<path d="M6 21v-2a4 4 0 0 1 4-4h4a4 4 0 0 1 4 4v2"/>'
				. '<circle cx="5" cy="8" r="2"/>'
				. '<path d="M1 19v-1a3 3 0 0 1 3-3"/>'
				. '<circle cx="19" cy="8" r="2"/>'
				. '<path d="M23 19v-1a3 3 0 0 0-3-3"/>'
				. '</svg>';

		case 'woman':
			// Woman / Person Silhouette (matches card 4).
			return '<svg width="' . $size_attr . '" height="' . $size_attr . '" viewBox="0 0 24 24" fill="none" stroke="' . $color_attr . '" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'
				. '<circle cx="12" cy="7" r="3.5"/>'
				. '<path d="M7 6c0-2 2-3.5 5-3.5s5 1.5 5 3.5c0 2-1 3.5-1 4"/>'
				. '<path d="M6 21c0-4 2.5-6 6-6s6 2 6 6"/>'
				. '</svg>';

		case 'money':
			return '<svg width="' . $size_attr . '" height="' . $size_attr . '" viewBox="0 0 24 24" fill="none" stroke="' . $color_attr . '" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'
				. '<rect x="2" y="6" width="20" height="12" rx="2"/>'
				. '<circle cx="12" cy="12" r="2.5"/>'
				. '<path d="M6 12h.01M18 12h.01"/>'
				. '</svg>';

		case 'award':
			return '<svg width="' . $size_attr . '" height="' . $size_attr . '" viewBox="0 0 24 24" fill="none" stroke="' . $color_attr . '" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'
				. '<circle cx="12" cy="8" r="6"/>'
				. '<path d="m15.477 12.89 1.515 8.526a.5.5 0 0 1-.724.524l-4.268-2.247-4.268 2.247a.5.5 0 0 1-.724-.524l1.515-8.526"/>'
				. '</svg>';

		case 'shield':
			return '<svg width="' . $size_attr . '" height="' . $size_attr . '" viewBox="0 0 24 24" fill="none" stroke="' . $color_attr . '" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'
				. '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>'
				. '<path d="m9 12 2 2 4-4"/>'
				. '</svg>';

		case 'heart':
			return '<svg width="' . $size_attr . '" height="' . $size_attr . '" viewBox="0 0 24 24" fill="none" stroke="' . $color_attr . '" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'
				. '<path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/>'
				. '</svg>';

		case 'leaf':
			return '<svg width="' . $size_attr . '" height="' . $size_attr . '" viewBox="0 0 24 24" fill="none" stroke="' . $color_attr . '" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'
				. '<path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10Z"/>'
				. '<path d="M2 21c0-3 1.85-5.36 5.08-6C9.5 14.52 12 13 13 12"/>'
				. '</svg>';

		case 'building':
			return '<svg width="' . $size_attr . '" height="' . $size_attr . '" viewBox="0 0 24 24" fill="none" stroke="' . $color_attr . '" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'
				. '<rect x="4" y="2" width="16" height="20" rx="2" ry="2"/>'
				. '<path d="M9 22v-4h6v4M8 6h.01M16 6h.01M12 6h.01M12 10h.01M12 14h.01M16 10h.01M16 14h.01M8 10h.01M8 14h.01"/>'
				. '</svg>';

		default:
			// Fallback generic star or circle.
			return '<svg width="' . $size_attr . '" height="' . $size_attr . '" viewBox="0 0 24 24" fill="none" stroke="' . $color_attr . '" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'
				. '<circle cx="12" cy="12" r="9"/>'
				. '<path d="m12 8 2 4 4 .5-3 3 .8 4.5-3.8-2-3.8 2 .8-4.5-3-3 4-.5z"/>'
				. '</svg>';
	}
}
