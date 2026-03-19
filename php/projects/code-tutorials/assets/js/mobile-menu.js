/**
 * Mobile Menu Toggle Functionality
 * Handles responsive navigation for all pages
 */

document.addEventListener("DOMContentLoaded", function () {
  // Mobile menu toggle functionality
  initMobileMenu();

  // Close mobile menu when clicking outside
  initClickOutsideMenu();

  // Handle window resize
  handleResize();
});

function initMobileMenu() {
  // Get all mobile menu toggle buttons
  const toggleButtons = document.querySelectorAll(".mobile-menu-toggle");

  toggleButtons.forEach((button) => {
    button.addEventListener("click", function (e) {
      e.preventDefault();
      e.stopPropagation();

      // Find the corresponding navigation menu
      const nav = this.closest("header, .admin-nav, .student-nav");
      if (!nav) return;

      // Handle different navigation structures
      let navLinks;
      if (nav.classList.contains("admin-nav")) {
        navLinks = nav.querySelector(".admin-nav-links");
      } else if (nav.classList.contains("student-nav")) {
        navLinks = nav.querySelector(".student-nav-links");
      } else {
        navLinks = nav.querySelector(".nav-links");
      }

      if (navLinks) {
        // Toggle the active class
        navLinks.classList.toggle("active");

        // Update button text/icon
        updateToggleButton(this, navLinks.classList.contains("active"));

        // Prevent body scroll when menu is open
        document.body.style.overflow = navLinks.classList.contains("active")
          ? "hidden"
          : "";
      }
    });
  });
}

function updateToggleButton(button, isActive) {
  // Change button text between ☰ and ✕
  if (isActive) {
    button.innerHTML = "✕";
    button.setAttribute("aria-expanded", "true");
  } else {
    button.innerHTML = "☰";
    button.setAttribute("aria-expanded", "false");
  }
}

function initClickOutsideMenu() {
  // Close mobile menu when clicking outside
  document.addEventListener("click", function (e) {
    const openMenus = document.querySelectorAll(
      ".nav-links.active, .admin-nav-links.active, .student-nav-links.active",
    );

    openMenus.forEach((menu) => {
      const nav = menu.closest("header, .admin-nav, .student-nav");
      if (!nav) return;

      const toggleButton = nav.querySelector(".mobile-menu-toggle");

      // Check if click is outside the navigation
      if (!nav.contains(e.target)) {
        menu.classList.remove("active");
        document.body.style.overflow = "";

        if (toggleButton) {
          updateToggleButton(toggleButton, false);
        }
      }
    });
  });
}

function handleResize() {
  // Close mobile menu on window resize if screen becomes larger
  let resizeTimer;
  window.addEventListener("resize", function () {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(function () {
      if (window.innerWidth > 575) {
        // Close all mobile menus
        const openMenus = document.querySelectorAll(
          ".nav-links.active, .admin-nav-links.active, .student-nav-links.active",
        );
        const toggleButtons = document.querySelectorAll(".mobile-menu-toggle");

        openMenus.forEach((menu) => {
          menu.classList.remove("active");
        });

        toggleButtons.forEach((button) => {
          updateToggleButton(button, false);
        });

        document.body.style.overflow = "";
      }
    }, 250);
  });
}

// Keyboard navigation support
document.addEventListener("keydown", function (e) {
  if (e.key === "Escape") {
    // Close all mobile menus on Escape key
    const openMenus = document.querySelectorAll(
      ".nav-links.active, .admin-nav-links.active, .student-nav-links.active",
    );
    const toggleButtons = document.querySelectorAll(".mobile-menu-toggle");

    openMenus.forEach((menu) => {
      menu.classList.remove("active");
    });

    toggleButtons.forEach((button) => {
      updateToggleButton(button, false);
    });

    document.body.style.overflow = "";
  }
});

// Add smooth scroll behavior for mobile menu links
document.addEventListener("DOMContentLoaded", function () {
  const menuLinks = document.querySelectorAll(
    ".nav-links a, .admin-nav-links a, .student-nav-links a",
  );

  menuLinks.forEach((link) => {
    link.addEventListener("click", function (e) {
      // Close mobile menu after clicking a link
      const nav = this.closest("header, .admin-nav, .student-nav");
      if (!nav) return;

      let navLinks;
      if (nav.classList.contains("admin-nav")) {
        navLinks = nav.querySelector(".admin-nav-links");
      } else if (nav.classList.contains("student-nav")) {
        navLinks = nav.querySelector(".student-nav-links");
      } else {
        navLinks = nav.querySelector(".nav-links");
      }

      const toggleButton = nav.querySelector(".mobile-menu-toggle");

      if (navLinks && navLinks.classList.contains("active")) {
        navLinks.classList.remove("active");
        document.body.style.overflow = "";

        if (toggleButton) {
          updateToggleButton(toggleButton, false);
        }
      }
    });
  });
});
