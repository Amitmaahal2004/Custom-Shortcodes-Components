/**
 * Script: Dynamic Organization Chart / Hierarchy Tree
 * Handles dynamic branch line alignment, responsive adjustments, and category tab filtering.
 *
 * @package CustomShortcodesComponents
 */

(function($) {
	'use strict';

	/**
	 * Align tree branch connector lines pixel-perfectly between first and last child nodes.
	 */
	function alignTreeConnectors() {
		$('.csc-org-tree-wrapper').each(function() {
			var $wrapper = $(this);
			var $canvas = $wrapper.find('.csc-org-tree-canvas');
			var $connectorContainer = $wrapper.find('.csc-org-connector-container');
			var $branch = $wrapper.find('.csc-org-horizontal-branch');
			var $subordinates = $wrapper.find('.csc-org-subordinate-item:visible');

			if (!$subordinates.length || !$branch.length) {
				return;
			}

			var $first = $subordinates.first();
			var $last = $subordinates.last();

			if ($subordinates.length === 1) {
				// Single subordinate node: horizontal line collapsed to 0
				$branch.hide();
				return;
			}

			$branch.show();

			var canvasOffset = $canvas.offset().left;
			var firstCenter = $first.offset().left + ($first.outerWidth() / 2) - canvasOffset;
			var lastCenter = $last.offset().left + ($last.outerWidth() / 2) - canvasOffset;

			var branchLeft = Math.min(firstCenter, lastCenter);
			var branchWidth = Math.abs(lastCenter - firstCenter);

			$branch.css({
				'left': branchLeft + 'px',
				'width': branchWidth + 'px',
				'right': 'auto'
			});
		});
	}

	/**
	 * Initialize interactive Category Tabs.
	 */
	function initCategoryTabs() {
		$(document).on('click', '.csc-org-tab', function(e) {
			e.preventDefault();
			var $tab = $(this);
			var $wrapper = $tab.closest('.csc-org-tree-wrapper');
			var filter = $tab.data('filter');

			$wrapper.find('.csc-org-tab').removeClass('is-active').attr('aria-selected', 'false');
			$tab.addClass('is-active').attr('aria-selected', 'true');

			if (filter === 'all' || !filter) {
				$wrapper.find('.csc-org-subordinate-item').fadeIn(200, function() {
					alignTreeConnectors();
				});
			} else {
				$wrapper.find('.csc-org-subordinate-item').each(function() {
					var $item = $(this);
					var itemCategory = $item.data('category');
					if (itemCategory && itemCategory.indexOf(filter) !== -1) {
						$item.fadeIn(200);
					} else {
						$item.fadeOut(200);
					}
				});
				setTimeout(alignTreeConnectors, 250);
			}
		});
	}

	/**
	 * Initialize on document ready & window events.
	 */
	$(document).ready(function() {
		initCategoryTabs();
		alignTreeConnectors();

		// Handle images loading
		$(window).on('load', function() {
			alignTreeConnectors();
		});

		// Throttled window resize
		var resizeTimeout;
		$(window).on('resize', function() {
			clearTimeout(resizeTimeout);
			resizeTimeout = setTimeout(alignTreeConnectors, 50);
		});

		// Horizontal scroll sync on mobile/overflow
		$('.csc-org-subordinates-scroll-wrap').on('scroll', function() {
			alignTreeConnectors();
		});

		// Observer for container resize if available
		if (window.ResizeObserver) {
			var observer = new ResizeObserver(function() {
				alignTreeConnectors();
			});
			$('.csc-org-tree-wrapper').each(function() {
				observer.observe(this);
			});
		}
	});

})(jQuery);
