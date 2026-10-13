/**
 * Himachal Dynamic Content Manager - HP Map Script
 * Pure Vanilla JavaScript (Zero External Dependencies)
 *
 * @package HimachalDynamicContentManager
 */

(function () {
  "use strict";

  // In-memory cache for category datasets to ensure instantaneous tab switching
  const categoryCache = {};

  // All known 12 Himachal Pradesh district SVG codes
  const allDistrictCodes = [
    "cha",
    "kan",
    "una",
    "ham",
    "bil",
    "sol",
    "sir",
    "shi",
    "man",
    "kul",
    "las",
    "kin",
  ];

  // Safe execution whether script is loaded in header, footer, async, or defer
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initDashboard);
  } else {
    initDashboard();
  }

  function getParams() {
    if (typeof hpMapParams !== "undefined") {
      return hpMapParams;
    }
    if (typeof hddDashboardParams !== "undefined") {
      return hddDashboardParams;
    }
    return null;
  }

  function initDashboard() {
    const wrappers = document.querySelectorAll(".hp-dashboard-wrapper");
    wrappers.forEach(function (root) {
      initTabs(root);
      initCarouselDots(root);
      initMapHover(root);
    });

    // Initialize View All toggles across map wrappers and standalone sidebar cards
    initViewAllToggles(document);
  }

  /**
   * In-place View All / View Less Toggle for Announcements and Downloads
   */
  function initViewAllToggles(context) {
    const root = context || document;

    const buttons = root.querySelectorAll("button.hp-view-all-btn");

    buttons.forEach(function (btn) {
      if (btn.tagName === "A" || btn.getAttribute("data-initialized") === "true") {
        return;
      }

      btn.setAttribute("data-initialized", "true");

      btn.addEventListener("click", function (e) {
        e.preventDefault();

        const clickedButton = this;

        const card = clickedButton.closest(".hp-dashboard-card");

        if (!card) {
          return;
        }

        const isExpanded = clickedButton.classList.contains("is-expanded");

        const textSpan = clickedButton.querySelector(".hp-view-btn-text");

        const moreText =
          clickedButton.getAttribute("data-more-text") || "View All";

        const lessText =
          clickedButton.getAttribute("data-less-text") || "View Less";

        /*
         * Get the correct PHP limit.
         *
         * Announcements = 2
         * Downloads    = 4
         */
        const visibleLimit = parseInt(
          clickedButton.getAttribute("data-visible-limit") || "3",
          10,
        );

        const targetId = clickedButton.getAttribute("data-target");

        const list = targetId
          ? document.getElementById(targetId)
          : card.querySelector(".hp-announcements-list, .hp-downloads-list");

        /*
         * ==================================================
         * VIEW LESS
         * ==================================================
         */

        if (isExpanded) {
          clickedButton.classList.remove("is-expanded");

          clickedButton.setAttribute("aria-expanded", "false");

          if (textSpan) {
            textSpan.textContent = moreText;
          }

          if (list) {
            const items = list.querySelectorAll(
              ".hp-announcement-item, .hp-download-item",
            );

            items.forEach(function (item, index) {
              if (index >= visibleLimit) {
                item.classList.add("hp-item-hidden");
              } else {
                item.classList.remove("hp-item-hidden");
              }
            });
          }

          return;
        }

        /*
         * ==================================================
         * CLOSE ALL OTHER OPEN CARDS
         * ==================================================
         */

        const allButtons = root.querySelectorAll(".hp-view-all-btn");

        allButtons.forEach(function (otherButton) {
          if (otherButton === clickedButton) {
            return;
          }

          if (!otherButton.classList.contains("is-expanded")) {
            return;
          }

          /* Close other button */

          otherButton.classList.remove("is-expanded");

          otherButton.setAttribute("aria-expanded", "false");

          const otherText = otherButton.querySelector(".hp-view-btn-text");

          const otherMoreText =
            otherButton.getAttribute("data-more-text") || "View All";

          if (otherText) {
            otherText.textContent = otherMoreText;
          }

          /*
           * Get OTHER card's own limit.
           *
           * Announcements = 2
           * Downloads    = 4
           */
          const otherVisibleLimit = parseInt(
            otherButton.getAttribute("data-visible-limit") || "3",
            10,
          );

          /* Find other list */

          const otherTargetId = otherButton.getAttribute("data-target");

          let otherList = null;

          if (otherTargetId) {
            otherList = document.getElementById(otherTargetId);
          }

          if (!otherList) {
            const otherCard = otherButton.closest(".hp-dashboard-card");

            if (otherCard) {
              otherList = otherCard.querySelector(
                ".hp-announcements-list, .hp-downloads-list",
              );
            }
          }

          /*
           * Collapse other list
           */

          if (otherList) {
            const otherItems = otherList.querySelectorAll(
              ".hp-announcement-item, .hp-download-item",
            );

            otherItems.forEach(function (item, index) {
              if (index >= otherVisibleLimit) {
                item.classList.add("hp-item-hidden");
              } else {
                item.classList.remove("hp-item-hidden");
              }
            });
          }
        });

        /*
         * ==================================================
         * OPEN CURRENT CARD
         * ==================================================
         */

        clickedButton.classList.add("is-expanded");

        clickedButton.setAttribute("aria-expanded", "true");

        if (textSpan) {
          textSpan.textContent = lessText;
        }

        /*
         * Show all current items
         */

        if (list) {
          const hiddenItems = list.querySelectorAll(".hp-item-hidden");

          hiddenItems.forEach(function (item) {
            item.classList.remove("hp-item-hidden");
          });
        }
      });
    });
  }

  /**
   * 1. Tab Switching & Dynamic Data Updating
   */
  function initTabs(root) {
    const tabs = root.querySelectorAll(".hp-tab-btn");
    if (!tabs.length) return;

    const params = getParams();

    // 1. Seed preloaded datasets from PHP if provided
    if (params && params.preloaded_data) {
      Object.keys(params.preloaded_data).forEach(function (slug) {
        categoryCache[slug] = params.preloaded_data[slug];
      });
    }

    // 2. Built-in instant Figma presets ensuring zero network lag and offline resilience
    if (!categoryCache["demographic-spread-of-minorities"]) {
      categoryCache["demographic-spread-of-minorities"] = {
        total_population: "68,64,602*",
        districts: {
          CHA: { name: "Chamba", formatted_val: "519080", val: 519080 },
          KAN: { name: "Kangra", formatted_val: "1510075", val: 1510075 },
          UNA: { name: "Una", formatted_val: "521173", val: 521173 },
          HAM: { name: "Hamirpur", formatted_val: "454768", val: 454768 },
          BIL: { name: "Bilaspur", formatted_val: "391835", val: 391835 },
          SOL: { name: "Solan", formatted_val: "580320", val: 580320 },
          SIR: { name: "Sirmaur", formatted_val: "529855", val: 529855 },
          KUL: { name: "Kullu", formatted_val: "437903", val: 437903 },
          LAS: { name: "Lahul & Spiti", formatted_val: "31564", val: 31564 },
          KIN: { name: "Kinnaur", formatted_val: "84121", val: 84121 },
          MAN: { name: "Mandi", formatted_val: "43790", val: 43790 },
          SHI: { name: "Shimla", formatted_val: "814010", val: 814010 },
        },
      };
    }

    if (!categoryCache["disabilities-in-hp"]) {
      categoryCache["disabilities-in-hp"] = {
        total_population: "46,50,404*",
        districts: {
          CHA: { name: "Chamba", formatted_val: "50000", val: 50000 },
          KAN: { name: "Kangra", formatted_val: "1510075", val: 1510075 },
          UNA: { name: "Una", formatted_val: "521173", val: 521173 },
          HAM: { name: "Hamirpur", formatted_val: "454768", val: 454768 },
          BIL: { name: "Bilaspur", formatted_val: "391835", val: 391835 },
          SOL: { name: "Solan", formatted_val: "580320", val: 580320 },
          SIR: { name: "Sirmaur", formatted_val: "529855", val: 529855 },
          KUL: { name: "Kullu", formatted_val: "417903", val: 417903 },
          LAS: { name: "Lahul & Spiti", formatted_val: "31564", val: 31564 },
          KIN: { name: "Kinnaur", formatted_val: "34121", val: 34121 },
          MAN: { name: "Mandi", formatted_val: "41790", val: 41790 },
          SHI: { name: "Shimla", formatted_val: "15000", val: 15000 },
        },
      };
    }

    // Seed initial active tab in cache if provided by PHP
    if (params && params.active_slug && !categoryCache[params.active_slug]) {
      categoryCache[params.active_slug] = {
        total_population: params.initial_total,
        districts: {},
      };
      if (Array.isArray(params.initial_districts)) {
        params.initial_districts.forEach(function (d) {
          if (d.svg_id) {
            categoryCache[params.active_slug].districts[
              d.svg_id.toUpperCase()
            ] = d;
          }
        });
      }
    }

    tabs.forEach(function (tab) {
      tab.addEventListener("click", function (e) {
        e.preventDefault();
        const slug = this.getAttribute("data-slug");
        const index = parseInt(this.getAttribute("data-index") || "0", 10);

        // Update active tab styles
        tabs.forEach(function (t) {
          t.classList.remove("hp-tab-active");
          t.setAttribute("aria-selected", "false");
        });
        this.classList.add("hp-tab-active");
        this.setAttribute("aria-selected", "true");

        // Synchronize SVG carousel dots
        syncCarouselDots(root, index);

        // Fetch or retrieve cached category data
        loadCategoryData(root, slug);

        // Synchronize Scheme / Header Logo (Logo 2 for disabilities-in-hp, Logo 1 for demographic-spread-of-minorities)
        const isCat2 = (slug === "disabilities-in-hp");
        if (typeof window.cscSetActiveScheme === "function") {
          window.cscSetActiveScheme(isCat2 ? 2 : 1, false);
        }
      });
    });

    // Support page refresh and initial load: ?scheme_category=cat-1 or ?scheme_category=cat-2
    try {
      const urlParams = new URLSearchParams(window.location.search);
      const urlCategory = urlParams.get("scheme_category") || urlParams.get("category");
      const savedLogo = localStorage.getItem("csc_active_scheme_logo");

      if (urlCategory === "cat-2" || (!urlCategory && savedLogo === "2")) {
        const disTab = root.querySelector('.hp-tab-btn[data-slug="disabilities-in-hp"]');
        if (disTab && !disTab.classList.contains("hp-tab-active")) {
          disTab.click();
        }
      } else if (urlCategory === "cat-1") {
        const minTab = root.querySelector('.hp-tab-btn[data-slug="demographic-spread-of-minorities"]');
        if (minTab && !minTab.classList.contains("hp-tab-active")) {
          minTab.click();
        }
      }
    } catch (e) {}

    // Listen for Logo / Scheme changes
    document.addEventListener("cscSchemeChanged", function (e) {
      if (!e.detail) return;
      const targetSlug = e.detail.isSecondary ? "disabilities-in-hp" : "demographic-spread-of-minorities";
      const targetTab = root.querySelector('.hp-tab-btn[data-slug="' + targetSlug + '"]');
      if (targetTab && !targetTab.classList.contains("hp-tab-active")) {
        targetTab.click();
      }
    });
  }

  /**
   * Synchronize carousel dots with active tab
   */
  function syncCarouselDots(root, activeIndex) {
    const dots = root.querySelectorAll(".hp-nav-dot-svg");
    dots.forEach(function (dot, i) {
      if (i === activeIndex) {
        dot.classList.add("hp-nav-dot-active");
        dot.setAttribute("fill", "#1b4965");
        dot.setAttribute("r", "4.5");
        dot.setAttribute("aria-selected", "true");
      } else {
        dot.classList.remove("hp-nav-dot-active");
        dot.setAttribute("fill", "#cbd5e1");
        dot.setAttribute("r", "3.5");
        dot.setAttribute("aria-selected", "false");
      }
    });
  }

  /**
   * 2. Carousel Dots Click Handling
   */
  function initCarouselDots(root) {
    const dots = root.querySelectorAll(".hp-nav-dot-svg");
    dots.forEach(function (dot) {
      dot.addEventListener("click", function () {
        const index = parseInt(this.getAttribute("data-index") || "0", 10);
        const targetTab = root.querySelector(
          '.hp-tab-btn[data-index="' + index + '"]',
        );
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
    const params = getParams();
    if (!slug || !params || !params.ajax_url) return;

    // Check local memory cache first for instant response
    if (categoryCache[slug]) {
      applyCategoryData(root, categoryCache[slug]);
      return;
    }

    // Loading state on total display
    const totalDisplay = root.querySelector("#hp-total-display");
    if (totalDisplay) {
      totalDisplay.style.opacity = "0.45";
    }

    const formData = new FormData();
    formData.append("action", params.ajax_action || "hp_map_get_category_data");
    formData.append("slug", slug);
    formData.append("nonce", params.nonce);

    fetch(params.ajax_url, {
      method: "POST",
      body: formData,
      credentials: "same-origin",
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
        console.warn("HP Map: Error loading category data", error);
      })
      .finally(function () {
        if (totalDisplay) {
          totalDisplay.style.opacity = "1";
        }
      });
  }

  /**
   * 4. Apply Category Data to SVG & Total Card
   */
  function applyCategoryData(root, data) {
    // Update Total Population / Metric display
    const totalDisplay = root.querySelector("#hp-total-display");
    if (totalDisplay && typeof data.total_population !== "undefined") {
      totalDisplay.textContent = data.total_population;
    }

    // Update district values & labels
    const districts = data.districts || {};

    allDistrictCodes.forEach(function (code) {
      const upperCode = code.toUpperCase();
      const textVal = root.querySelector("#text-val-" + code);
      const group = root.querySelector("#group-" + code);

      if (districts[upperCode]) {
        const dist = districts[upperCode];
        const formatted = dist.formatted_val || formatNumber(dist.val);

        if (textVal) {
          textVal.textContent = formatted;
        }
        if (group) {
          group.setAttribute("data-value", formatted);
          if (dist.name) {
            group.setAttribute("data-name", dist.name);
          }
        }
      } else {
        // If this category does not contain data for this district, reset cleanly
        if (textVal) {
          textVal.textContent = "—";
        }
        if (group) {
          group.setAttribute("data-value", "—");
        }
      }
    });
  }

  /**
   * 5. Interactive Map District Hover & Tooltip
   */
  function initMapHover(root) {
    const container = root.querySelector("#hp-map-container");
    const tooltip = root.querySelector("#hp-map-tooltip");
    if (!container || !tooltip) return;

    const titleEl = tooltip.querySelector(".hp-tooltip-title");
    const valEl = tooltip.querySelector(".hp-tooltip-val");

    const districtGroups = root.querySelectorAll(".hp-district-group");

    districtGroups.forEach(function (group) {
      group.addEventListener("mouseenter", function () {
        const name = this.getAttribute("data-name") || "";
        const val = this.getAttribute("data-value") || "";

        if (titleEl) titleEl.textContent = name;
        if (valEl) valEl.textContent = val;

        tooltip.style.opacity = "1";
        tooltip.style.visibility = "visible";
      });

      group.addEventListener("mousemove", function (e) {
        const rect = container.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;

        tooltip.style.left = x + 12 + "px";
        tooltip.style.top = y + 12 + "px";
      });

      group.addEventListener("mouseleave", function () {
        tooltip.style.opacity = "0";
        tooltip.style.visibility = "hidden";
      });
    });
  }

  /**
   * Number Formatter Helper (Indian System & Standard)
   */
  function formatNumber(num) {
    const params = getParams();
    if (params && params.format_indian) {
      return formatIndianNumber(num);
    }
    if (
      num === "" ||
      num === null ||
      typeof num === "undefined" ||
      isNaN(Number(num))
    ) {
      return num || "—";
    }
    return Number(num).toLocaleString("en-US");
  }

  function formatIndianNumber(num) {
    const clean = String(num).replace(/[^0-9.]/g, "");
    if (!clean) return num || "—";

    const parts = clean.split(".");
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

    let formatted = chunks.reverse().join(",") + "," + last3;
    if (parts[1]) {
      formatted += "." + parts[1];
    }
    return formatted;
  }
})();
