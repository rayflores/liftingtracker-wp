/**
 * LiftingTracker Pro Theme - Main JavaScript Entry Point
 *
 * This file serves as the main entry point for all JavaScript functionality
 * in the LiftingTracker Pro theme. It imports and initializes all components.
 */

// Import WordPress dependencies
import { domReady } from "@wordpress/dom-ready";
import { __ } from "@wordpress/i18n";

// Import theme modules
import "./modules/workout-tracker";
import "./modules/dashboard";
import "./modules/navigation";
import "./modules/forms";

// Import styles
import "../scss/main.scss";

/**
 * Initialize theme when DOM is ready
 */
domReady(() => {
  console.log(
    __("🏋️ LiftingTracker Pro theme initialized", "liftingtracker-pro")
  );

  // Initialize theme components
  initializeTheme();
});

/**
 * Initialize all theme functionality
 */
function initializeTheme() {
  // Mobile menu toggle
  initializeMobileMenu();

  // Form handling
  initializeForms();

  // Dashboard interactions
  initializeDashboard();

  // Workout tracker functionality
  initializeWorkoutTracker();
}

/**
 * Initialize mobile menu functionality
 */
function initializeMobileMenu() {
  const mobileMenuToggle = document.querySelector(".mobile-menu-toggle");
  const mobileMenu = document.querySelector(".mobile-menu");

  if (mobileMenuToggle && mobileMenu) {
    mobileMenuToggle.addEventListener("click", () => {
      mobileMenu.classList.toggle("active");
      mobileMenuToggle.classList.toggle("active");
    });
  }
}

/**
 * Initialize form handling
 */
function initializeForms() {
  const forms = document.querySelectorAll(".ajax-form");

  forms.forEach((form) => {
    form.addEventListener("submit", handleFormSubmit);
  });
}

/**
 * Initialize dashboard functionality
 */
function initializeDashboard() {
  // Dashboard-specific initialization
  if (document.body.classList.contains("dashboard")) {
    console.log("Dashboard initialized");
  }
}

/**
 * Initialize workout tracker functionality
 */
function initializeWorkoutTracker() {
  // Workout tracker-specific initialization
  if (document.body.classList.contains("workout-tracker")) {
    console.log("Workout tracker initialized");
  }
}

/**
 * Handle AJAX form submissions
 */
function handleFormSubmit(event) {
  event.preventDefault();

  const form = event.target;
  const formData = new FormData(form);

  // Add nonce for security
  formData.append("nonce", liftingtracker_ajax.nonce);

  fetch(liftingtracker_ajax.ajax_url, {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        showNotification(data.message, "success");
      } else {
        showNotification(data.message, "error");
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      showNotification(
        __("Something went wrong", "liftingtracker-pro"),
        "error"
      );
    });
}

/**
 * Show notification to user
 */
function showNotification(message, type = "info") {
  const notification = document.createElement("div");
  notification.className = `notification notification-${type}`;
  notification.textContent = message;

  document.body.appendChild(notification);

  setTimeout(() => {
    notification.remove();
  }, 5000);
}

