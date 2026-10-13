/**
 * Himachal Dynamic Dashboard Frontend Script
 * Pure Vanilla JavaScript (Zero Dependencies)
 *
 * @package HimachalDynamicDashboard
 */

(function () {
    'use strict';

    // Global in-memory cache for category datasets
    const categoryCache = {};

    document.addEventListener('DOMContentLoaded', function () {
        initDashboard();
    });

    function initDashboard() {
        const root = document.getElementById('hp-dashboard-root');
        if (!root) return;

        // Initialize Tab Handlers
        initTabs(root);

        // Initialize Carousel Navigation Dots
        initCarouselDots(root);

        // Initialize Map Hover & Tooltip
        initMapHover(root);
    }

    /**
     * 1. Tab Switching & Dynamic Data Updating
     */
    function initTabs(root) {
        const tabs = root.querySelectorAll('.hp-tab-btn');
        if (!tabs.length) return;

        // Seed initial active tab in cache if provided
        if (typeof hddDashboardParams !== 'undefined' && hddDashboardParams.active_slug) {
            categoryCache[hddDashboardParams.active_slug] = {
                total_population: hddDashboardParams.initial_total,
                districts: {}
            };
            if (Array.isArray(hddDashboardParams.initial_districts)) {
                hddDashboardParams.initial_districts.forEach(function (d) {
                    if (d.svg_id) {
                        categoryCache[hddDashboardParams.active_slug].districts[d.svg_id.toUpperCase()] = d;
                    }
                });
            }
        }

        tabs.forEach(function (tab) {
            tab.addEventListener('click', function (e) {
                e.preventDefault();
                const slug = this.getAttribute('data-slug');
                const index = parseInt(this.getAttribute('data-index') || '0', 10);

                // Update active tab styles
                tabs.forEach(function (t) {
                    t.classList.remove('hp-tab-active');
                    t.setAttribute('aria-selected', 'false');
                });
                this.classList.add('hp-tab-active');
                this.setAttribute('aria-selected', 'true');

                // Synchronize SVG carousel dots
                syncCarouselDots(root, index);

                // Fetch or retrieve cached data
                loadCategoryData(root, slug);
            });
        });
    }

    /**
     * Synchronize carousel dots with active tab
     */
    function syncCarouselDots(root, activeIndex) {
        const dots = root.querySelectorAll('.hp-nav-dot-svg');
        dots.forEach(function (dot, i) {
            if (i === activeIndex) {
                dot.classList.add('hp-nav-dot-active');
                dot.setAttribute('fill', '#1b4965');
                dot.setAttribute('r', '4.5');
                dot.setAttribute('aria-selected', 'true');
            } else {
                dot.classList.remove('hp-nav-dot-active');
                dot.setAttribute('fill', '#cbd5e1');
                dot.setAttribute('r', '3.5');
                dot.setAttribute('aria-selected', 'false');
            }
        });
    }

    /**
     * 2. Carousel Dots Click Handling
     */
    function initCarouselDots(root) {
        const dots = root.querySelectorAll('.hp-nav-dot-svg');
        dots.forEach(function (dot) {
            dot.addEventListener('click', function () {
                const index = parseInt(this.getAttribute('data-index') || '0', 10);
                const targetTab = root.querySelector('.hp-tab-btn[data-index="' + index + '"]');
                if (targetTab) {
                    targetTab.click();
                }
            });
        });
    }

    /**
     * 3. Fetch Category Data via AJAX
     */
    function loadCategoryData(root, slug) {
        if (!slug || typeof hddDashboardParams === 'undefined') return;

        // Check local cache first for instant UX
        if (categoryCache[slug]) {
            applyCategoryData(root, categoryCache[slug]);
            return;
        }

        // Show subtle loading state on total population
        const totalDisplay = root.querySelector('#hp-total-display');
        if (totalDisplay) {
            totalDisplay.style.opacity = '0.5';
        }

        const formData = new FormData();
        formData.append('action', 'hdd_get_category_data');
        formData.append('slug', slug);
        formData.append('nonce', hddDashboardParams.nonce);

        fetch(hddDashboardParams.ajax_url, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
        .then(function (response) {
            return response.json();
        })
        .then(function (result) {
            if (result.success && result.data) {
                categoryCache[slug] = result.data;
                applyCategoryData(root, result.data);
            }
        })
        .catch(function (error) {
            console.error('Error loading dashboard category data:', error);
        })
        .finally(function () {
            if (totalDisplay) {
                totalDisplay.style.opacity = '1';
            }
        });
    }

    /**
     * 4. Apply Category Data to SVG & Total Card
     */
    function applyCategoryData(root, data) {
        // Update Total Population
        const totalDisplay = root.querySelector('#hp-total-display');
        if (totalDisplay && data.total_population) {
            totalDisplay.textContent = data.total_population;
        }

        // Update district values & labels
        if (data.districts) {
            for (const [code, dist] of Object.entries(data.districts)) {
                const lowerCode = code.toLowerCase();

                // Update text label value
                const textVal = root.querySelector('#text-val-' + lowerCode);
                if (textVal) {
                    const formatted = dist.formatted_val || formatNumber(dist.val);
                    textVal.textContent = formatted;
                }

                // Update group dataset for tooltips
                const group = root.querySelector('#group-' + lowerCode);
                if (group) {
                    group.setAttribute('data-value', dist.formatted_val || formatNumber(dist.val));
                    if (dist.name) {
                        group.setAttribute('data-name', dist.name);
                    }
                }
            }
        }
    }

    /**
     * 5. Interactive Map District Hover & Tooltip
     */
    function initMapHover(root) {
        const container = root.querySelector('#hp-map-container');
        const tooltip = root.querySelector('#hp-map-tooltip');
        if (!container || !tooltip) return;

        const titleEl = tooltip.querySelector('.hp-tooltip-title');
        const valEl = tooltip.querySelector('.hp-tooltip-val');

        const districtGroups = root.querySelectorAll('.hp-district-group');

        districtGroups.forEach(function (group) {
            group.addEventListener('mouseenter', function (e) {
                const name = this.getAttribute('data-name') || '';
                const val = this.getAttribute('data-value') || '';

                if (titleEl) titleEl.textContent = name;
                if (valEl) valEl.textContent = val;

                tooltip.style.opacity = '1';
                tooltip.style.visibility = 'visible';
            });

            group.addEventListener('mousemove', function (e) {
                const rect = container.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;

                tooltip.style.left = x + 'px';
                tooltip.style.top = y + 'px';
            });

            group.addEventListener('mouseleave', function () {
                tooltip.style.opacity = '0';
                tooltip.style.visibility = 'hidden';
            });
        });
    }

    /**
     * Number Formatter Helper (Indian System)
     */
    function formatNumber(num) {
        if (typeof hddDashboardParams !== 'undefined' && hddDashboardParams.format_indian) {
            return formatIndianNumber(num);
        }
        return Number(num).toLocaleString('en-US');
    }

    function formatIndianNumber(num) {
        const clean = String(num).replace(/[^0-9.]/g, '');
        if (!clean) return num;

        const parts = clean.split('.');
        let intPart = parts[0];

        if (intPart.length <= 3) {
            return intPart;
        }

        const last3 = intPart.slice(-3);
        let rest = intPart.slice(0, -3);
        const chunks = [];

        while (rest.length > 2) {
            chunks.push(rest.slice(-2));
            rest = rest.slice(0, -2);
        }
        if (rest.length > 0) {
            chunks.push(rest);
        }

        let formatted = chunks.reverse().join(',') + ',' + last3;
        if (parts[1]) {
            formatted += '.' + parts[1];
        }
        return formatted;
    }

})();
