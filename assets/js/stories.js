/**
 * Success Stories JavaScript: Swiper Slider, Video Player, Taxonomy Filters & Lightbox
 *
 * @package CustomShortcodesComponents
 */

(function () {
	'use strict';

	var initAttempts = 0;
	var maxAttempts = 50;

	/* ==========================================================================
	   1. Swiper Slider Initialization
	   ========================================================================== */
	function initStorySliders() {
		if (typeof Swiper === 'undefined') {
			if (initAttempts < maxAttempts) {
				initAttempts++;
				setTimeout(initStorySliders, 60);
			}
			return;
		}

		var swiperEls = document.querySelectorAll('.csc-story-swiper:not(.swiper-initialized)');
		if (!swiperEls.length) {
			return;
		}

		swiperEls.forEach(function (el) {
			var isAutoplay = el.getAttribute('data-autoplay') === 'yes';
			var autoplayDelay = parseInt(el.getAttribute('data-autoplay-delay'), 10) || 5000;
			var speed = parseInt(el.getAttribute('data-speed'), 10) || 600;
			var paginationEl = el.querySelector('.csc-story-pagination');

			var swiperOptions = {
				slidesPerView: 1,
				spaceBetween: 24,
				speed: speed,
				loop: true,
				grabCursor: true,
				autoHeight: false,
				pagination: {
					el: paginationEl,
					clickable: true,
				},
				keyboard: {
					enabled: true,
					onlyInViewport: true,
				},
			};

			if (isAutoplay) {
				swiperOptions.autoplay = {
					delay: autoplayDelay,
					disableOnInteraction: false,
					pauseOnMouseEnter: true,
				};
			}

			new Swiper(el, swiperOptions);
		});
	}

	/* ==========================================================================
	   2. Inline Video Player Interactions
	   ========================================================================== */
	function initVideoPlayers() {
		document.addEventListener('click', function (e) {
			var playBtn = e.target.closest('.csc-story-play-trigger, .csc-bar-play-icon');
			if (!playBtn) {
				return;
			}

			var videoBox = playBtn.closest('.csc-story-video-box');
			if (!videoBox) {
				return;
			}

			var type = videoBox.getAttribute('data-video-type');
			var url = videoBox.getAttribute('data-video-url');
			var embed = videoBox.getAttribute('data-video-embed');
			var container = videoBox.querySelector('.csc-video-embed-container');
			var poster = videoBox.querySelector('.csc-story-poster-img, .csc-story-placeholder-poster');
			var bar = videoBox.querySelector('.csc-story-video-bar');
			var centerTrigger = videoBox.querySelector('.csc-story-play-trigger');

			if (!url && !embed) {
				return;
			}

			if (container) {
				container.style.display = 'block';

				if (type === 'youtube' || type === 'vimeo') {
					container.innerHTML = '<iframe src="' + (embed || url) + '" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe>';
				} else if (type === 'html5') {
					container.innerHTML = '<video src="' + url + '" controls autoplay playsinline style="width:100%;height:100%;object-fit:cover;"></video>';
				}

				if (poster) {
					poster.style.display = 'none';
				}
				if (centerTrigger) {
					centerTrigger.style.display = 'none';
				}
				if (bar) {
					bar.style.display = 'none';
				}
			}
		});

		// Fullscreen button trigger
		document.addEventListener('click', function (e) {
			var fsBtn = e.target.closest('.csc-bar-fs-icon');
			if (!fsBtn) {
				return;
			}
			var videoBox = fsBtn.closest('.csc-story-video-box');
			if (videoBox) {
				if (!document.fullscreenElement) {
					if (videoBox.requestFullscreen) {
						videoBox.requestFullscreen();
					} else if (videoBox.webkitRequestFullscreen) {
						videoBox.webkitRequestFullscreen();
					}
				} else {
					if (document.exitFullscreen) {
						document.exitFullscreen();
					}
				}
			}
		});
	}

	/* ==========================================================================
	   3. Taxonomy Filter Bar for Grid Cards
	   ========================================================================== */
	function initGridFilters() {
		document.addEventListener('click', function (e) {
			var filterBtn = e.target.closest('.csc-filter-btn');
			if (!filterBtn) {
				return;
			}

			var wrapper = filterBtn.closest('.csc-story-grid-wrapper');
			if (!wrapper) {
				return;
			}

			var filterVal = filterBtn.getAttribute('data-filter');
			var buttons = wrapper.querySelectorAll('.csc-filter-btn');
			var cards = wrapper.querySelectorAll('.csc-story-grid-card');

			buttons.forEach(function (btn) {
				btn.classList.remove('is-active');
			});
			filterBtn.classList.add('is-active');

			cards.forEach(function (card) {
				if (filterVal === '*' || filterVal === '') {
					card.classList.remove('is-hidden');
				} else {
					var selectorClass = filterVal.replace('.', '');
					if (card.classList.contains(selectorClass)) {
						card.classList.remove('is-hidden');
					} else {
						card.classList.add('is-hidden');
					}
				}
			});
		});
	}

	/* ==========================================================================
	   4. Glimpses Lightbox Modal
	   ========================================================================== */
	function initGlimpsesLightbox() {
		// Create modal element if not present.
		var lightbox = document.querySelector('.csc-lightbox-overlay');
		if (!lightbox) {
			lightbox = document.createElement('div');
			lightbox.className = 'csc-lightbox-overlay';
			lightbox.innerHTML = '<div class="csc-lightbox-content">' +
				'<button type="button" class="csc-lightbox-close" aria-label="Close">&times;</button>' +
				'<img src="" alt="" />' +
				'</div>';
			document.body.appendChild(lightbox);
		}

		var lbImg = lightbox.querySelector('img');
		var lbClose = lightbox.querySelector('.csc-lightbox-close');

		function closeLightbox() {
			lightbox.classList.remove('is-active');
			if (lbImg) {
				lbImg.src = '';
			}
		}

		document.addEventListener('click', function (e) {
			var link = e.target.closest('.csc-glimpse-link');
			if (!link) {
				return;
			}
			e.preventDefault();
			var imgSrc = link.getAttribute('href');
			if (imgSrc && lbImg) {
				lbImg.src = imgSrc;
				lightbox.classList.add('is-active');
			}
		});

		if (lbClose) {
			lbClose.addEventListener('click', closeLightbox);
		}

		lightbox.addEventListener('click', function (e) {
			if (e.target === lightbox) {
				closeLightbox();
			}
		});

		document.addEventListener('keydown', function (e) {
			if (e.key === 'Escape' && lightbox.classList.contains('is-active')) {
				closeLightbox();
			}
		});
	}

	// Bootstrap on DOM Ready.
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', function () {
			initStorySliders();
			initVideoPlayers();
			initGridFilters();
			initGlimpsesLightbox();
		});
	} else {
		initStorySliders();
		initVideoPlayers();
		initGridFilters();
		initGlimpsesLightbox();
	}
})();
