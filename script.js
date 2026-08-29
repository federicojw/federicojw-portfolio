const menuToggle = document.getElementById("menuToggle");
const siteNav = document.getElementById("siteNav");
const navLinks = [...document.querySelectorAll(".nav-link")];
const sections = [...document.querySelectorAll("main section[id]")];
const revealItems = [...document.querySelectorAll(".reveal")];
const dnaCanvas = document.getElementById("dnaCanvas");
const roleRotator = document.getElementById("roleRotator");
const cursorGlow = document.querySelector(".cursor-glow");
const finePointer = window.matchMedia("(hover: hover) and (pointer: fine)").matches;
const prefersReducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

const HERO_ROLES = [
  "Software Engineer",
  "Front-End Developer",
  "Web Developer",
  "SQL & Database Learner"
];

// --- MOBILE MENU ---
function closeMenu() {
  if (!siteNav || !menuToggle) return;
  siteNav.classList.remove("open");
  menuToggle.setAttribute("aria-expanded", "false");
  menuToggle.setAttribute("aria-label", "Open navigation");
}

menuToggle?.addEventListener("click", () => {
  const isOpen = siteNav.classList.toggle("open");
  menuToggle.setAttribute("aria-expanded", String(isOpen));
  menuToggle.setAttribute("aria-label", isOpen ? "Close navigation" : "Open navigation");
});

// --- ACTIVE NAV SCROLL ---
function updateNavIndicator() {
  if (!siteNav || window.innerWidth <= 850) return;
  const activeLink = siteNav.querySelector(".nav-link.active");
  if (!activeLink) return;
  const navRect = siteNav.getBoundingClientRect();
  const linkRect = activeLink.getBoundingClientRect();
  siteNav.style.setProperty("--nav-indicator-x", `${linkRect.left - navRect.left}px`);
  siteNav.style.setProperty("--nav-indicator-width", `${linkRect.width}px`);
}

function updateActiveNav() {
  const headerHeight = parseFloat(
    getComputedStyle(document.documentElement).getPropertyValue("--header")
  ) || 74;
  const marker = window.scrollY + headerHeight + Math.min(window.innerHeight * 0.28, 220);
  let current = sections[0]?.id || "hero";

  sections.forEach((section) => {
    if (section.offsetTop <= marker) current = section.id;
  });

  if (window.innerHeight + window.scrollY >= document.documentElement.scrollHeight - 8) {
    current = sections.at(-1)?.id || current;
  }

  navLinks.forEach((link) => {
    link.classList.toggle("active", link.getAttribute("href") === `#${current}`);
  });
  updateNavIndicator();
}

navLinks.forEach((link) => {
  link.addEventListener("click", () => {
    closeMenu();
    window.setTimeout(updateActiveNav, 120);
  });
});

window.addEventListener("scroll", updateActiveNav, { passive: true });
window.addEventListener("load", updateActiveNav);
window.addEventListener("resize", () => {
  if (window.innerWidth > 850) closeMenu();
  updateActiveNav();
});

// --- SCROLL REVEAL (PAKSA AKTIF) ---
const revealObserver = new IntersectionObserver((entries) => {
  entries.forEach((entry) => {
    if (entry.isIntersecting) {
      entry.target.classList.add("visible");
    } else {
      entry.target.classList.remove("visible"); 
    }
  });
}, { threshold: 0.1, rootMargin: "0px 0px -50px 0px" });

revealItems.forEach((item) => revealObserver.observe(item));

// --- ROLE ROTATOR ---
function startRoleRotator() {
  if (!roleRotator) return;

  let roleIndex = 0;
  let charIndex = 0;
  let isDeleting = false;

  function typeAutomatically() {
    const activeRole = HERO_ROLES[roleIndex];

    if (isDeleting) {
      roleRotator.textContent = activeRole.substring(0, charIndex - 1);
      charIndex--;
    } else {
      roleRotator.textContent = activeRole.substring(0, charIndex + 1);
      charIndex++;
    }

    let speed = isDeleting ? 40 : 100;

    if (!isDeleting && charIndex === activeRole.length) {
      speed = 2000; 
      isDeleting = true;
    } else if (isDeleting && charIndex === 0) {
      isDeleting = false;
      roleIndex = (roleIndex + 1) % HERO_ROLES.length;
      speed = 400; 
    }

    setTimeout(typeAutomatically, speed);
  }

  setTimeout(typeAutomatically, 800);
}

startRoleRotator();

// --- CURSOR GLOW ---
if (cursorGlow) {
  let targetX = -200;
  let targetY = -200;
  let currentX = targetX;
  let currentY = targetY;

  cursorGlow.style.opacity = "1";

  window.addEventListener("pointermove", (e) => {
    targetX = e.clientX;
    targetY = e.clientY;
  }, { passive: true });

  const renderGlow = () => {
    currentX += (targetX - currentX) * 0.2;
    currentY += (targetY - currentY) * 0.2;
    cursorGlow.style.transform = `translate3d(${currentX - 72}px, ${currentY - 72}px, 0)`;
    requestAnimationFrame(renderGlow);
  };

  requestAnimationFrame(renderGlow);
}


// --- SCROLL PROGRESS BAR (SUPER SMOOTH LERP) ---
const scrollProgress = document.getElementById("scrollProgress");
let targetScroll = 0;
let currentScroll = 0;

function renderProgressBar() {
  if (scrollProgress) {
    const docHeight = document.documentElement.scrollHeight;
    const winHeight = window.innerHeight;
    const maxScroll = docHeight - winHeight;

    targetScroll = window.scrollY;

    currentScroll += (targetScroll - currentScroll) * 0.15;

    let scrollPercent = currentScroll / maxScroll || 0;
    scrollPercent = Math.max(0, Math.min(1, scrollPercent));

    scrollProgress.style.transform = `scaleX(${scrollPercent})`;
  }
  requestAnimationFrame(renderProgressBar);
}

requestAnimationFrame(renderProgressBar);