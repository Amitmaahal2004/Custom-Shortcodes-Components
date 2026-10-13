/**
 * Schemes & Services Swiper Slider + Header Logo Category & Color Switching
 *
 * @package CustomShortcodesComponents
 */

(function () {
	'use strict';

	var initAttempts = 0;
	var maxAttempts = 50;

	// Global parameters from wp_localize_script
	var params = window.cscSchemesParams || {
		ajax_url: '/wp-admin/admin-ajax.php',
		nonce: '',
		logo1_selector: '.csc-header-logo-1, .header-logo-1, .site-header .custom-logo-link:first-of-type, .elementor-widget-theme-site-logo:first-of-type a, [data-scheme-logo="1"], [data-scheme-logo="primary"], .logo-1, #logo-1',
		logo1_category: 'demographic-spread-of-minorities',
		logo1_name: 'Demographic Spread Of Minorities',
		logo2_selector: '.csc-header-logo-2, .header-logo-2, .site-header .custom-logo-link:last-of-type, .elementor-widget-theme-site-logo:last-of-type a, [data-scheme-logo="2"], [data-scheme-logo="secondary"], .logo-2, #logo-2',
		logo2_category: 'disabilities-in-hp',
		logo2_name: 'Disabilities In H.P',
		active_class: 'is-active',
		enable_color_switch: 'yes',
		enable_storage: 'yes',
		enable_url_param: 'yes',
		prevent_navigation: 'yes',
		empty_message: 'No schemes available in this category.',
	};

	/**
	 * Initialize Swiper Sliders.
	 */
	function initSchemesSliders() {
		// Ensure Swiper library is available.
		if (typeof Swiper === 'undefined') {
			if (initAttempts < maxAttempts) {
				initAttempts++;
				setTimeout(initSchemesSliders, 60);
			}
			return;
		}

		var sliders = document.querySelectorAll('.csc-schemes-slider:not(.swiper-initialized)');

		if (!sliders.length) {
			return;
		}

		sliders.forEach(function (sliderEl) {
			var wrapper = sliderEl.closest('.csc-schemes-wrapper') || sliderEl.parentElement;

			// Read configuration options from data attributes.
			var slidesDesktop = parseInt(sliderEl.getAttribute('data-slides-per-view'), 10) || 4;
			var spaceBetween = parseInt(sliderEl.getAttribute('data-space-between'), 10) || 24;
			var isAutoplay = sliderEl.getAttribute('data-autoplay') === 'yes';
			var autoplayDelay = parseInt(sliderEl.getAttribute('data-autoplay-delay'), 10) || 3500;
			var isLoop = sliderEl.getAttribute('data-loop') === 'yes';
			var speed = parseInt(sliderEl.getAttribute('data-speed'), 10) || 600;
			var hasArrows = sliderEl.getAttribute('data-arrows') !== 'no';
			var hasDots = sliderEl.getAttribute('data-dots') === 'yes';

			// Navigation buttons.
			var prevBtn = wrapper.querySelector('.csc-swiper-button-prev');
			var nextBtn = wrapper.querySelector('.csc-swiper-button-next');
			var paginationEl = wrapper.querySelector('.csc-schemes-pagination');

			var swiperOptions = {
				slidesPerView: 1.15,
				spaceBetween: 16,
				speed: speed,
				loop: isLoop,
				grabCursor: true,
				watchOverflow: true,
				centeredSlides: false,
				breakpoints: {
					540: {
						slidesPerView: 1.6,
						spaceBetween: 18,
					},
					768: {
						slidesPerView: 2.5,
						spaceBetween: 20,
					},
					992: {
						slidesPerView: Math.min(3, slidesDesktop),
						spaceBetween: spaceBetween,
					},
					1200: {
						slidesPerView: slidesDesktop,
						spaceBetween: spaceBetween,
					},
				},
				keyboard: {
					enabled: true,
					onlyInViewport: true,
				},
				a11y: {
					prevSlideMessage: 'Previous Scheme',
					nextSlideMessage: 'Next Scheme',
				},
			};

			// Add navigation if enabled.
			if (hasArrows && prevBtn && nextBtn) {
				swiperOptions.navigation = {
					prevEl: prevBtn,
					nextEl: nextBtn,
				};
			}

			// Add pagination if enabled.
			if (hasDots && paginationEl) {
				swiperOptions.pagination = {
					el: paginationEl,
					clickable: true,
				};
			}

			// Add autoplay if enabled.
			if (isAutoplay) {
				swiperOptions.autoplay = {
					delay: autoplayDelay,
					disableOnInteraction: false,
					pauseOnMouseEnter: true,
				};
			}

			// Initialize Swiper instance.
			try {
				var swiperInstance = new Swiper(sliderEl, swiperOptions);
				sliderEl._cscSwiper = swiperInstance;
			} catch (err) {
				console.error('[CSC Schemes Slider] Initialization error:', err);
			}
		});
	}

	/**
	 * Helper: Query logo elements safely by selector.
	 *
	 * @param {string} selector
	 * @returns {NodeList}
	 */
	function queryLogos(selector) {
		if (!selector) {
			return [];
		}
		try {
			return document.querySelectorAll(selector);
		} catch (e) {
			console.warn('[CSC Schemes] Invalid logo selector:', selector, e);
			return [];
		}
	}

	/**
	 * Set the active Scheme (Logo 1 vs Logo 2) and perform color switching & category filtering.
	 *
	 * @param {number|string} logoNumber 1 or 2
	 * @param {boolean} triggerAjax Whether to trigger AJAX reload of sliders
	 */
	function setActiveScheme(logoNumber, triggerAjax) {
		logoNumber = String(logoNumber) === '2' ? '2' : '1';
		var isLogo2 = logoNumber === '2';
		var activeClass = params.active_class || 'is-active';
		var targetCategory = isLogo2 ? (params.logo2_category || '') : (params.logo1_category || '');

		// 1. Update HTML and Body Color State Classes
		if (isLogo2) {
			document.documentElement.classList.add('scheme-secondary-active');
			document.documentElement.classList.remove('scheme-primary-active');
			if (document.body) {
				document.body.classList.add('scheme-secondary-active');
				document.body.classList.remove('scheme-primary-active');
			}
		} else {
			document.documentElement.classList.add('scheme-primary-active');
			document.documentElement.classList.remove('scheme-secondary-active');
			if (document.body) {
				document.body.classList.add('scheme-primary-active');
				document.body.classList.remove('scheme-secondary-active');
			}
		}

		// 2. Update Header Logo Active States & Accessibility
		var logo1Els = queryLogos(params.logo1_selector);
		var logo2Els = queryLogos(params.logo2_selector);

		logo1Els.forEach(function (el) {
			if (!isLogo2) {
				el.classList.add(activeClass);
				el.setAttribute('aria-pressed', 'true');
			} else {
				el.classList.remove(activeClass);
				el.setAttribute('aria-pressed', 'false');
			}
		});

		logo2Els.forEach(function (el) {
			if (isLogo2) {
				el.classList.add(activeClass);
				el.setAttribute('aria-pressed', 'true');
			} else {
				el.classList.remove(activeClass);
				el.setAttribute('aria-pressed', 'false');
			}
		});

		// 3. Persist State in LocalStorage
		if (params.enable_storage !== 'no') {
			try {
				localStorage.setItem('csc_active_scheme_logo', logoNumber);
			} catch (e) {}
		}

		// 4. Activate existing map category button so existing AJAX/rendering/accessibility works normally
		try {
			var mapTabSlug = isLogo2 ? 'disabilities-in-hp' : 'demographic-spread-of-minorities';
			var mapTabs = document.querySelectorAll('.hp-tab-btn[data-slug="' + mapTabSlug + '"]');
			mapTabs.forEach(function (tab) {
				if (!tab.classList.contains('hp-tab-active')) {
					tab.click();
				}
			});
		} catch (e) {}

		// 5. Trigger In-Place AJAX Category Filtering on Schemes Sliders (if present)
		if (triggerAjax !== false) {
			filterSchemesSlider(targetCategory);
		}

		// 6. Dispatch custom event for extensible integrations
		try {
			var event = new CustomEvent('cscSchemeChanged', {
				detail: {
					logo: logoNumber,
					category: targetCategory,
					isSecondary: isLogo2,
				},
			});
			document.dispatchEvent(event);
		} catch (e) {}
	}

	/**
	 * Perform AJAX category filtering on all Schemes sliders on the current page.
	 *
	 * @param {string} categorySlug
	 */
	function filterSchemesSlider(categorySlug) {
		var sliders = document.querySelectorAll('.csc-schemes-slider');
		if (!sliders.length) {
			return;
		}

		sliders.forEach(function (sliderEl) {
			var wrapper = sliderEl.closest('.csc-schemes-wrapper') || sliderEl.parentElement;
			var wrapperEl = sliderEl.querySelector('.swiper-wrapper');
			if (!wrapperEl) {
				return;
			}

			// Add loading state
			sliderEl.classList.add('is-loading');

			// Read slider params from data attributes
			var limit = sliderEl.getAttribute('data-limit') || -1;
			var orderby = sliderEl.getAttribute('data-orderby') || 'menu_order title';
			var order = sliderEl.getAttribute('data-order') || 'ASC';

			var formData = new FormData();
			formData.append('action', 'csc_filter_schemes');
			formData.append('nonce', params.nonce);
			formData.append('category', categorySlug || 'all');
			formData.append('limit', limit);
			formData.append('orderby', orderby);
			formData.append('order', order);

			fetch(params.ajax_url, {
				method: 'POST',
				body: formData,
			})
				.then(function (res) {
					return res.json();
				})
				.then(function (data) {
					sliderEl.classList.remove('is-loading');

					if (data && data.success && data.data) {
						// Destroy existing Swiper instance
						if (sliderEl._cscSwiper) {
							sliderEl._cscSwiper.destroy(true, true);
							delete sliderEl._cscSwiper;
						}
						sliderEl.classList.remove('swiper-initialized', 'swiper-horizontal');

						// Replace slide cards
						wrapperEl.innerHTML = data.data.html;

						// Update category data attribute
						sliderEl.setAttribute('data-category', categorySlug || '');

						// Re-initialize Swiper
						initSchemesSliders();
					}
				})
				.catch(function (err) {
					sliderEl.classList.remove('is-loading');
					console.error('[CSC Schemes] AJAX filter error:', err);
				});
		});
	}

	/**
	 * Attach event listeners directly to the two existing website header logos.
	 */
	function attachHeaderLogoListeners() {
		var logo1Els = queryLogos(params.logo1_selector);
		var logo2Els = queryLogos(params.logo2_selector);

		function handleLogoClick(e, logoNum) {
			var hasSliders = document.querySelectorAll('.csc-schemes-slider').length > 0;
			var shouldPrevent = params.prevent_navigation !== 'no' && hasSliders;

			if (shouldPrevent) {
				e.preventDefault();
			}

			setActiveScheme(logoNum, true);
		}

		logo1Els.forEach(function (el) {
			if (el._cscLogoBound) {
				return;
			}
			el._cscLogoBound = true;
			el.setAttribute('role', 'button');
			el.setAttribute('tabindex', '0');
			el.setAttribute('aria-label', params.logo1_name || 'Category 1 Schemes');

			el.addEventListener('click', function (e) {
				handleLogoClick(e, 1);
			});

			el.addEventListener('keydown', function (e) {
				if (e.key === 'Enter' || e.key === ' ' || e.keyCode === 13 || e.keyCode === 32) {
					e.preventDefault();
					handleLogoClick(e, 1);
				}
			});
		});

		logo2Els.forEach(function (el) {
			if (el._cscLogoBound) {
				return;
			}
			el._cscLogoBound = true;
			el.setAttribute('role', 'button');
			el.setAttribute('tabindex', '0');
			el.setAttribute('aria-label', params.logo2_name || 'Category 2 Schemes');

			el.addEventListener('click', function (e) {
				handleLogoClick(e, 2);
			});

			el.addEventListener('keydown', function (e) {
				if (e.key === 'Enter' || e.key === ' ' || e.keyCode === 13 || e.keyCode === 32) {
					e.preventDefault();
					handleLogoClick(e, 2);
				}
			});
		});
	}

	/**
	 * Boot and restore active scheme state on page load.
	 */
	function bootSchemeState() {
		var activeLogo = '1';

		// 1. Check URL query param first
		try {
			var urlParams = new URLSearchParams(window.location.search);
			var urlCat = urlParams.get('scheme_category') || urlParams.get('category');
			if (urlCat === 'cat-2' || urlCat === 'disabilities-in-hp' || (params.logo2_category && urlCat === params.logo2_category)) {
				activeLogo = '2';
			} else if (urlCat === 'cat-1' || urlCat === 'demographic-spread-of-minorities' || (params.logo1_category && urlCat === params.logo1_category)) {
				activeLogo = '1';
			} else if (params.enable_storage !== 'no') {
				// 2. Check localStorage
				var savedLogo = localStorage.getItem('csc_active_scheme_logo');
				if (savedLogo === '2') {
					activeLogo = '2';
				}
			}
		} catch (e) {}

		// Apply active scheme state without triggering duplicate AJAX on initial load
		setActiveScheme(activeLogo, false);

		// Attach listeners to existing header logos
		attachHeaderLogoListeners();
	}

	// Run initialization on DOMContentLoaded and load
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', function () {
			initSchemesSliders();
			bootSchemeState();
		});
	} else {
		initSchemesSliders();
		bootSchemeState();
	}

	window.addEventListener('load', function () {
		initSchemesSliders();
		attachHeaderLogoListeners();
	});

	// Support dynamic re-initialization (e.g. Elementor preview, AJAX page builders)
	window.cscInitSchemesSliders = initSchemesSliders;
	window.cscSetActiveScheme = setActiveScheme;
	window.cscFilterSchemesSlider = filterSchemesSlider;
	window.cscAttachHeaderLogoListeners = attachHeaderLogoListeners;
})();
