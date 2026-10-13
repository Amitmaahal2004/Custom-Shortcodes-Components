<?php
/**
 * Frontend Template for HP Map [hp_map] and [hp_minority_dashboard]
 *
 * @package HimachalDynamicContentManager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="hp-dashboard-wrapper" id="hp-dashboard-root">
	<div class="hp-dashboard-grid">

		<!-- ============================================================== -->
		<!-- LEFT COLUMN: MAP CARD WITH DYNAMIC TABS & SVG MAP              -->
		<!-- ============================================================== -->
		<div class="hp-dashboard-card hp-map-card">

			<!-- Dynamic Dashboard Tabs matching Figma Design -->
			<?php if ( ! empty( $categories ) ) : ?>
				<div class="hp-tabs-container" role="tablist" aria-label="<?php esc_attr_e( 'HP Map Category Tabs', 'custom-shortcodes-components' ); ?>">
					<?php
					foreach ( $categories as $index => $cat ) :
						$cat_slug  = isset( $cat->slug ) ? $cat->slug : '';
						$cat_name  = isset( $cat->name ) ? $cat->name : '';
						$is_active = ( $active_cat && ( ( isset( $active_cat->slug ) && $active_cat->slug === $cat_slug ) || ( isset( $active_cat->term_id ) && $active_cat->term_id === $cat->term_id ) ) );
					?>
						<button type="button"
							class="hp-tab-btn <?php echo $is_active ? 'hp-tab-active' : ''; ?>"
							role="tab"
							aria-selected="<?php echo $is_active ? 'true' : 'false'; ?>"
							aria-controls="hp-tab-panel-<?php echo esc_attr( $cat_slug ); ?>"
							id="hp-tab-<?php echo esc_attr( $cat_slug ); ?>"
							data-slug="<?php echo esc_attr( $cat_slug ); ?>"
							data-index="<?php echo esc_attr( $index ); ?>">
							<?php echo esc_html( $cat_name ); ?>
						</button>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<!-- Interactive Map Viewport -->
			<div class="hp-map-viewport" id="hp-map-container">

				<!-- Rendered SVG Map -->
				<?php
				if ( class_exists( 'HDD_Map' ) ) {
					echo HDD_Map::render_svg( $districts_data, $categories, $active_index );
				}
				?>

				<!-- Bottom-Left Total Population Card -->
				<div class="hp-total-pop-card">
					<div class="hp-total-label"><?php esc_html_e( 'Total Population', 'custom-shortcodes-components' ); ?></div>
					<div class="hp-total-value" id="hp-total-display"><?php echo esc_html( $formatted_total ); ?></div>
				</div>

				<!-- Hover Tooltip -->
				<div class="hp-map-tooltip" id="hp-map-tooltip" style="opacity:0; visibility:hidden;">
					<div class="hp-tooltip-title"></div>
					<div class="hp-tooltip-val"></div>
				</div>
			</div>

		</div>

		<!-- ============================================================== -->
		<!-- RIGHT COLUMN: ANNOUNCEMENTS & IMPORTANT DOWNLOADS CARDS        -->
		<!-- ============================================================== -->
		<?php
		$sidebar_cards_template = CSC_PATH . 'templates/sidebar-cards.php';
		if ( file_exists( $sidebar_cards_template ) ) {
			include $sidebar_cards_template;
		}
		?>

	</div>
</div>
