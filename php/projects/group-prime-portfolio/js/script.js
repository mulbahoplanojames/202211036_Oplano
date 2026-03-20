// DOM Elements
const hamburger = document.querySelector(".hamburger");
const navMenu = document.querySelector(".nav-menu");
const navLinks = document.querySelectorAll(".nav-link");
const projectsContainer = document.getElementById("projects-container");
const teamContainer = document.getElementById("team-container");

// Mobile Navigation Toggle
hamburger.addEventListener("click", () => {
  hamburger.classList.toggle("active");
  navMenu.classList.toggle("active");
});

// Close mobile menu when clicking on a link
navLinks.forEach((link) => {
  link.addEventListener("click", () => {
    hamburger.classList.remove("active");
    navMenu.classList.remove("active");
  });
});

// Fetch projects from API
async function fetchProjects() {
  try {
    const response = await fetch("/api/data.php/projects");
    const result = await response.json();
    return result.success ? result.data : [];
  } catch (error) {
    console.error("Error fetching projects:", error);
    return [];
  }
}

// Fetch team members from API
async function fetchTeamMembers() {
  try {
    const response = await fetch("/api/data.php/team");
    const result = await response.json();
    return result.success ? result.data : [];
  } catch (error) {
    console.error("Error fetching team members:", error);
    return [];
  }
}

// Render Projects
async function renderProjects() {
  const projects = await fetchProjects();
  projectsContainer.innerHTML = projects
    .map(
      (project) => `
    <div class="project-card" data-aos="fade-up">
      <div class="project-image">
        ${
          project.image.includes("/")
            ? `<img src="${project.image}" alt="${project.title}" style="width: 100%; height: 100%; object-fit: cover;">`
            : project.image.charAt(0).toUpperCase() + project.image.slice(1)
        }
      </div>
      <div class="project-content">
        <h3 class="project-title">${project.title}</h3>
        <p class="project-description">${project.description}</p>
        <div class="project-links">
          <a href="${project.github}" target="_blank" class="project-link">
            <i class="fab fa-github"></i> GitHub
          </a>
          <a href="${project.demo}" target="_blank" class="project-link">
            <i class="fas fa-external-link-alt"></i> Live Demo
          </a>
        </div>
      </div>
    </div>
  `,
    )
    .join("");
}

// Render Team Members
async function renderTeam() {
  const teamMembers = await fetchTeamMembers();
  // Sort team members by ID in ascending order
  const sortedMembers = teamMembers.sort((a, b) => a.id - b.id);

  teamContainer.innerHTML = sortedMembers
    .map(
      (member) => `
    <div class="team-card" data-aos="fade-up">
      <div class="team-card-inner">
        <div class="team-card-bg"></div>
        <div class="team-avatar-wrapper">
          <div class="team-avatar">${member.initials}</div>
          <div class="team-status-dot"></div>
        </div>
        <div class="team-info">
          <h3 class="team-name">${member.name}</h3>
          <p class="team-role">${member.role}</p>
        </div>
        <div class="team-social">
          <a href="${member.github}" target="_blank" class="social-icon" aria-label="GitHub">
            <i class="fab fa-github"></i>
          </a>
          <a href="${member.linkedin}" target="_blank" class="social-icon" aria-label="LinkedIn">
            <i class="fab fa-linkedin"></i>
          </a>
          <a href="${member.twitter}" target="_blank" class="social-icon" aria-label="Twitter">
            <i class="fab fa-twitter"></i>
          </a>
        </div>
      </div>
    </div>
  `,
    )
    .join("");
}

// Smooth scrolling for navigation links
document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
  anchor.addEventListener("click", function (e) {
    e.preventDefault();
    const target = document.querySelector(this.getAttribute("href"));
    if (target) {
      target.scrollIntoView({
        behavior: "smooth",
        block: "start",
      });
    }
  });
});

// Navbar background on scroll
window.addEventListener("scroll", () => {
  const navbar = document.querySelector(".navbar");
  if (window.scrollY > 100) {
    navbar.style.background = "rgba(255, 255, 255, 0.98)";
    navbar.style.boxShadow = "0 4px 6px -1px rgba(0, 0, 0, 0.1)";
  } else {
    navbar.style.background = "rgba(255, 255, 255, 0.95)";
    navbar.style.boxShadow = "0 1px 2px 0 rgba(0, 0, 0, 0.05)";
  }
});

// Intersection Observer for animations
const observerOptions = {
  threshold: 0.1,
  rootMargin: "0px 0px -50px 0px",
};

const observer = new IntersectionObserver((entries) => {
  entries.forEach((entry) => {
    if (entry.isIntersecting) {
      entry.target.style.opacity = "1";
      entry.target.style.transform = "translateY(0)";
    }
  });
}, observerOptions);

// Observe elements for animation
function observeElements() {
  const elements = document.querySelectorAll(".project-card, .team-card");
  elements.forEach((el) => {
    el.style.opacity = "0";
    el.style.transform = "translateY(30px)";
    el.style.transition = "opacity 0.6s ease, transform 0.6s ease";
    observer.observe(el);
  });
}

// Active navigation link based on scroll position
function updateActiveNavLink() {
  const sections = document.querySelectorAll("section[id]");
  const scrollY = window.pageYOffset;

  sections.forEach((section) => {
    const sectionHeight = section.offsetHeight;
    const sectionTop = section.offsetTop - 100;
    const sectionId = section.getAttribute("id");
    const navLink = document.querySelector(`.nav-link[href="#${sectionId}"]`);

    if (scrollY > sectionTop && scrollY <= sectionTop + sectionHeight) {
      navLinks.forEach((link) => link.classList.remove("active"));
      navLink?.classList.add("active");
    }
  });
}

// Parallax effect for hero section
function parallaxEffect() {
  const hero = document.querySelector(".hero");
  const scrolled = window.pageYOffset;
  const parallax = hero
    ? (hero.style.backgroundPositionY = scrolled * 0.5 + "px")
    : null;
}

// Initialize animations and effects
function initializeAnimations() {
  observeElements();
  updateActiveNavLink();
  parallaxEffect();
}

// Event listeners
window.addEventListener("load", async () => {
  await renderProjects();
  await renderTeam();
  initializeAnimations();
});

window.addEventListener("scroll", () => {
  updateActiveNavLink();
  parallaxEffect();
});

// Add hover effect to project cards
document.addEventListener("mouseover", (e) => {
  if (e.target.closest(".project-card")) {
    const card = e.target.closest(".project-card");
    card.style.transform = "translateY(-10px) scale(1.02)";
  }
});

document.addEventListener("mouseout", (e) => {
  if (e.target.closest(".project-card")) {
    const card = e.target.closest(".project-card");
    card.style.transform = "translateY(0) scale(1)";
  }
});

// Add typing effect to hero title
function typeWriter(element, text, speed = 100) {
  let i = 0;
  element.textContent = "";

  function type() {
    if (i < text.length) {
      element.textContent += text.charAt(i);
      i++;
      setTimeout(type, speed);
    }
  }

  type();
}

// Initialize typing effect when page loads
window.addEventListener("DOMContentLoaded", () => {
  const heroTitle = document.querySelector(".hero-title");
  if (heroTitle) {
    const originalText = heroTitle.textContent;
    typeWriter(heroTitle, originalText, 80);
  }
});

// Form validation (for future contact form)
function validateForm(email, name, message) {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  const errors = [];

  if (!name || name.trim().length < 2) {
    errors.push("Name must be at least 2 characters long");
  }

  if (!email || !emailRegex.test(email)) {
    errors.push("Please enter a valid email address");
  }

  if (!message || message.trim().length < 10) {
    errors.push("Message must be at least 10 characters long");
  }

  return errors;
}

// Loading animation
function showLoading() {
  const loader = document.createElement("div");
  loader.className = "loader";
  loader.innerHTML = '<div class="spinner"></div>';
  document.body.appendChild(loader);
}

function hideLoading() {
  const loader = document.querySelector(".loader");
  if (loader) {
    loader.remove();
  }
}

// Utility functions
function debounce(func, wait) {
  let timeout;
  return function executedFunction(...args) {
    const later = () => {
      clearTimeout(timeout);
      func(...args);
    };
    clearTimeout(timeout);
    timeout = setTimeout(later, wait);
  };
}

// Optimize scroll events
const optimizedScroll = debounce(() => {
  updateActiveNavLink();
  parallaxEffect();
}, 10);

window.addEventListener("scroll", optimizedScroll);

// Console welcome message
console.log(
  "%c🚀 Welcome to Group Prime Portfolio!",
  "color: #6366f1; font-size: 20px; font-weight: bold;",
);
console.log(
  "%cBuilt with passion by Group Prime Team",
  "color: #22d3ee; font-size: 14px;",
);
