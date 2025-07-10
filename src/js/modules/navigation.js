/**
 * Navigation Module
 *
 * Handles navigation functionality including:
 * - Mobile menu toggle (matching navbar.jsx behavior)
 * - Dropdown menus
 * - Active states
 * - Search functionality
 * - Responsive behavior
 */

import { __ } from "@wordpress/i18n";

class Navigation {
  constructor() {
    this.mobileMenuOpen = false;
    this.init();
  }

  init() {
    this.bindEvents();
    this.initializeDropdowns();
    this.setActiveMenuItem();
    this.handleResize();
  }

  bindEvents() {
    // Mobile menu toggle
    const mobileMenuButton = document.getElementById("mobile-menu-button");
    if (mobileMenuButton) {
      mobileMenuButton.addEventListener("click", () => {
        this.toggleMobileMenu();
      });
    }

    // Close mobile menu on window resize (like React navbar) - matching navbar.jsx behavior
    window.addEventListener("resize", () => {
      if (window.innerWidth >= 1024 && this.mobileMenuOpen) {
        this.closeMobileMenu();
      }
    });

    // Dropdown toggles
    document.addEventListener("click", (e) => {
      if (e.target.matches(".dropdown-toggle")) {
        this.toggleDropdown(e.target);
      }
    });

    // Close dropdowns when clicking outside
    document.addEventListener("click", (e) => {
      if (!e.target.closest(".dropdown") && !e.target.closest(".user-menu")) {
        this.closeAllDropdowns();
      }
    });

    // Search functionality
    document.addEventListener("input", (e) => {
      if (e.target.matches(".nav-search-input")) {
        this.handleSearch(e.target.value);
      }
    });

    // Handle escape key
    document.addEventListener("keydown", (e) => {
      if (e.key === "Escape") {
        this.closeAllDropdowns();
        this.closeMobileMenu();
      }
    });
  }

  toggleMobileMenu() {
    const menu = document.getElementById("mobile-menu");
    const button = document.getElementById("mobile-menu-button");
    const icon = button.querySelector(".mobile-menu-icon");

    if (menu && button && icon) {
      this.mobileMenuOpen = !this.mobileMenuOpen;

      if (this.mobileMenuOpen) {
        // Show menu
        menu.classList.remove("hidden");
        icon.textContent = "close";

        // Add animation class
        requestAnimationFrame(() => {
          menu.classList.add("mobile-menu-open");
        });
      } else {
        // Hide menu
        menu.classList.remove("mobile-menu-open");
        icon.textContent = "menu";

        // Wait for animation to complete before hiding
        setTimeout(() => {
          menu.classList.add("hidden");
        }, 200);
      }
    }
  }

  closeMobileMenu() {
    const menu = document.getElementById("mobile-menu");
    const button = document.getElementById("mobile-menu-button");
    const icon = button?.querySelector(".mobile-menu-icon");

    if (menu && this.mobileMenuOpen) {
      this.mobileMenuOpen = false;
      menu.classList.remove("mobile-menu-open");
      if (icon) {
        icon.textContent = "menu";
      }
      setTimeout(() => {
        menu.classList.add("hidden");
      }, 200);
    }
  }

  handleResize() {
    // Close mobile menu when window is resized to desktop size
    // This matches the navbar.jsx behavior exactly
    window.addEventListener("resize", () => {
      if (window.innerWidth >= 1024 && this.mobileMenuOpen) {
        this.closeMobileMenu();
      }
    });
  }

  initializeDropdowns() {
    const dropdowns = document.querySelectorAll(".dropdown");

    dropdowns.forEach((dropdown) => {
      const toggle = dropdown.querySelector(".dropdown-toggle");
      const menu = dropdown.querySelector(".dropdown-menu");

      if (toggle && menu) {
        toggle.setAttribute("aria-expanded", "false");
        toggle.setAttribute("aria-haspopup", "true");
        menu.setAttribute("aria-hidden", "true");
      }
    });
  }

  toggleDropdown(toggle) {
    const dropdown = toggle.closest(".dropdown");
    const menu = dropdown.querySelector(".dropdown-menu");
    const isOpen = dropdown.classList.contains("active");

    // Close all other dropdowns first
    this.closeAllDropdowns();

    if (!isOpen) {
      dropdown.classList.add("active");
      toggle.setAttribute("aria-expanded", "true");
      menu.setAttribute("aria-hidden", "false");

      // Focus first menu item for keyboard navigation
      const firstMenuItem = menu.querySelector("a, button");
      if (firstMenuItem) {
        firstMenuItem.focus();
      }
    }
  }

  closeAllDropdowns() {
    const dropdowns = document.querySelectorAll(".dropdown.active");

    dropdowns.forEach((dropdown) => {
      const toggle = dropdown.querySelector(".dropdown-toggle");
      const menu = dropdown.querySelector(".dropdown-menu");

      dropdown.classList.remove("active");
      toggle.setAttribute("aria-expanded", "false");
      menu.setAttribute("aria-hidden", "true");
    });
  }

  setActiveMenuItem() {
    const currentPath = window.location.pathname;
    const menuItems = document.querySelectorAll(".nav-menu a");

    menuItems.forEach((item) => {
      const href = item.getAttribute("href");

      if (
        href === currentPath ||
        (href !== "/" && currentPath.startsWith(href))
      ) {
        item.classList.add("active");
        item.closest("li")?.classList.add("active");
      } else {
        item.classList.remove("active");
        item.closest("li")?.classList.remove("active");
      }
    });
  }

  handleSearch(query) {
    if (query.length < 2) {
      this.clearSearchResults();
      return;
    }

    // Debounce search
    clearTimeout(this.searchTimeout);
    this.searchTimeout = setTimeout(() => {
      this.performSearch(query);
    }, 300);
  }

  async performSearch(query) {
    try {
      const response = await fetch(
        `${
          window.location.origin
        }/wp-json/wp/v2/search?search=${encodeURIComponent(query)}&per_page=5`
      );
      const results = await response.json();

      this.displaySearchResults(results);
    } catch (error) {
      console.error("Search error:", error);
    }
  }

  displaySearchResults(results) {
    const container = document.querySelector(".search-results");
    if (!container) return;

    if (results.length === 0) {
      container.innerHTML = `
                <div class="no-results">
                    <p>${__("No results found", "liftingtracker-pro")}</p>
                </div>
            `;
      return;
    }

    const html = results
      .map(
        (result) => `
            <div class="search-result">
                <a href="${result.url}" class="search-result-link">
                    <h4>${result.title}</h4>
                    <p>${result.excerpt || ""}</p>
                </a>
            </div>
        `
      )
      .join("");

    container.innerHTML = html;
    container.classList.add("active");
  }

  clearSearchResults() {
    const container = document.querySelector(".search-results");
    if (container) {
      container.innerHTML = "";
      container.classList.remove("active");
    }
  }
}

// Initialize navigation
const navigation = new Navigation();

export default Navigation;

