/**
 * Forms Module
 *
 * Handles form functionality including:
 * - Form validation
 * - AJAX form submissions
 * - Real-time feedback
 * - File uploads
 */

import { __ } from "@wordpress/i18n";
import apiFetch from "@wordpress/api-fetch";

class Forms {
  constructor() {
    this.init();
  }

  init() {
    this.bindEvents();
    this.initializeValidation();
  }

  bindEvents() {
    // Form submission
    document.addEventListener("submit", (e) => {
      if (e.target.matches(".ajax-form")) {
        e.preventDefault();
        this.handleFormSubmit(e.target);
      }
    });

    // Real-time validation
    document.addEventListener("input", (e) => {
      if (e.target.matches(".validate-field")) {
        this.validateField(e.target);
      }
    });

    // File upload handling
    document.addEventListener("change", (e) => {
      if (e.target.matches(".file-upload")) {
        this.handleFileUpload(e.target);
      }
    });

    // Password visibility toggle
    document.addEventListener("click", (e) => {
      if (e.target.matches(".password-toggle")) {
        this.togglePasswordVisibility(e.target);
      }
    });
  }

  initializeValidation() {
    const forms = document.querySelectorAll(".ajax-form");

    forms.forEach((form) => {
      const fields = form.querySelectorAll(".validate-field");

      fields.forEach((field) => {
        this.setupFieldValidation(field);
      });
    });
  }

  setupFieldValidation(field) {
    const fieldContainer = field.closest(".form-field");
    if (!fieldContainer) return;

    // Add error message container if it doesn't exist
    if (!fieldContainer.querySelector(".field-error")) {
      const errorDiv = document.createElement("div");
      errorDiv.className = "field-error";
      fieldContainer.appendChild(errorDiv);
    }
  }

  async handleFormSubmit(form) {
    const submitButton = form.querySelector('[type="submit"]');
    const originalButtonText = submitButton.textContent;

    // Disable submit button and show loading state
    submitButton.disabled = true;
    submitButton.textContent = __("Processing...", "liftingtracker-pro");

    try {
      // Validate form before submission
      if (!this.validateForm(form)) {
        throw new Error(
          __("Please fix the errors above", "liftingtracker-pro")
        );
      }

      const formData = new FormData(form);
      const action = form.getAttribute("data-action") || "submit_form";

      // Add nonce for security
      formData.append("action", action);
      formData.append("nonce", liftingtracker_ajax.nonce);

      const response = await fetch(liftingtracker_ajax.ajax_url, {
        method: "POST",
        body: formData,
      });

      const result = await response.json();

      if (result.success) {
        this.showFormSuccess(form, result.data.message);
        this.resetForm(form);
      } else {
        throw new Error(
          result.data.message ||
            __("Something went wrong", "liftingtracker-pro")
        );
      }
    } catch (error) {
      this.showFormError(form, error.message);
    } finally {
      // Re-enable submit button
      submitButton.disabled = false;
      submitButton.textContent = originalButtonText;
    }
  }

  validateForm(form) {
    const fields = form.querySelectorAll(".validate-field");
    let isValid = true;

    fields.forEach((field) => {
      if (!this.validateField(field)) {
        isValid = false;
      }
    });

    return isValid;
  }

  validateField(field) {
    const value = field.value.trim();
    const type = field.getAttribute("data-validate");
    const required = field.hasAttribute("required");
    const fieldContainer = field.closest(".form-field");
    const errorDiv = fieldContainer.querySelector(".field-error");

    let isValid = true;
    let errorMessage = "";

    // Required field validation
    if (required && !value) {
      isValid = false;
      errorMessage = __("This field is required", "liftingtracker-pro");
    }

    // Type-specific validation
    if (value && type) {
      switch (type) {
        case "email":
          const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
          if (!emailRegex.test(value)) {
            isValid = false;
            errorMessage = __(
              "Please enter a valid email address",
              "liftingtracker-pro"
            );
          }
          break;

        case "password":
          if (value.length < 8) {
            isValid = false;
            errorMessage = __(
              "Password must be at least 8 characters",
              "liftingtracker-pro"
            );
          }
          break;

        case "phone":
          const phoneRegex = /^\+?[\d\s\-\(\)]+$/;
          if (!phoneRegex.test(value)) {
            isValid = false;
            errorMessage = __(
              "Please enter a valid phone number",
              "liftingtracker-pro"
            );
          }
          break;

        case "number":
          if (isNaN(value)) {
            isValid = false;
            errorMessage = __(
              "Please enter a valid number",
              "liftingtracker-pro"
            );
          }
          break;

        case "url":
          try {
            new URL(value);
          } catch {
            isValid = false;
            errorMessage = __("Please enter a valid URL", "liftingtracker-pro");
          }
          break;
      }
    }

    // Custom validation for password confirmation
    if (field.getAttribute("data-confirm")) {
      const confirmField = document.querySelector(
        `[name="${field.getAttribute("data-confirm")}"]`
      );
      if (confirmField && value !== confirmField.value) {
        isValid = false;
        errorMessage = __("Passwords do not match", "liftingtracker-pro");
      }
    }

    // Update field UI
    this.updateFieldUI(field, fieldContainer, errorDiv, isValid, errorMessage);

    return isValid;
  }

  updateFieldUI(field, fieldContainer, errorDiv, isValid, errorMessage) {
    if (isValid) {
      field.classList.remove("error");
      fieldContainer.classList.remove("has-error");
      errorDiv.textContent = "";
      errorDiv.style.display = "none";
    } else {
      field.classList.add("error");
      fieldContainer.classList.add("has-error");
      errorDiv.textContent = errorMessage;
      errorDiv.style.display = "block";
    }
  }

  handleFileUpload(input) {
    const file = input.files[0];
    if (!file) return;

    const maxSize = 5 * 1024 * 1024; // 5MB
    const allowedTypes = ["image/jpeg", "image/png", "image/gif", "image/webp"];

    if (file.size > maxSize) {
      this.showFieldError(
        input,
        __("File size must be less than 5MB", "liftingtracker-pro")
      );
      input.value = "";
      return;
    }

    if (!allowedTypes.includes(file.type)) {
      this.showFieldError(
        input,
        __("Only image files are allowed", "liftingtracker-pro")
      );
      input.value = "";
      return;
    }

    // Show preview for images
    if (file.type.startsWith("image/")) {
      this.showImagePreview(input, file);
    }
  }

  showImagePreview(input, file) {
    const preview = input.parentElement.querySelector(".image-preview");
    if (!preview) return;

    const reader = new FileReader();
    reader.onload = (e) => {
      preview.innerHTML = `<img src="${e.target.result}" alt="Preview" style="max-width: 200px; max-height: 200px;">`;
    };
    reader.readAsDataURL(file);
  }

  togglePasswordVisibility(button) {
    const input = button.parentElement.querySelector(
      'input[type="password"], input[type="text"]'
    );
    if (!input) return;

    if (input.type === "password") {
      input.type = "text";
      button.textContent = __("Hide", "liftingtracker-pro");
    } else {
      input.type = "password";
      button.textContent = __("Show", "liftingtracker-pro");
    }
  }

  showFormSuccess(form, message) {
    this.removeFormMessages(form);

    const successDiv = document.createElement("div");
    successDiv.className = "form-success";
    successDiv.textContent = message;

    form.insertBefore(successDiv, form.firstChild);

    // Auto-remove after 5 seconds
    setTimeout(() => {
      successDiv.remove();
    }, 5000);
  }

  showFormError(form, message) {
    this.removeFormMessages(form);

    const errorDiv = document.createElement("div");
    errorDiv.className = "form-error";
    errorDiv.textContent = message;

    form.insertBefore(errorDiv, form.firstChild);
  }

  showFieldError(field, message) {
    const fieldContainer = field.closest(".form-field");
    const errorDiv = fieldContainer.querySelector(".field-error");

    field.classList.add("error");
    fieldContainer.classList.add("has-error");
    errorDiv.textContent = message;
    errorDiv.style.display = "block";
  }

  removeFormMessages(form) {
    const messages = form.querySelectorAll(".form-success, .form-error");
    messages.forEach((msg) => msg.remove());
  }

  resetForm(form) {
    form.reset();

    // Remove all field errors
    const fields = form.querySelectorAll(".validate-field");
    fields.forEach((field) => {
      field.classList.remove("error");
      const fieldContainer = field.closest(".form-field");
      fieldContainer.classList.remove("has-error");
      const errorDiv = fieldContainer.querySelector(".field-error");
      errorDiv.textContent = "";
      errorDiv.style.display = "none";
    });

    // Clear image previews
    const previews = form.querySelectorAll(".image-preview");
    previews.forEach((preview) => {
      preview.innerHTML = "";
    });
  }
}

// Initialize forms
const forms = new Forms();

export default Forms;

