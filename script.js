const menuToggle = document.getElementById("menuToggle");
const siteNav = document.getElementById("siteNav");
const navLinks = [...document.querySelectorAll(".nav-link")];
const sections = [...document.querySelectorAll("main section[id]")];
const revealItems = [...document.querySelectorAll(".reveal")];
const roleRotator = document.getElementById("roleRotator");
const cursorGlow = document.querySelector(".cursor-glow");
document.documentElement.classList.add('js-loaded');

const HERO_ROLES = [
  "Software Engineer",
  "Front-End Developer",
  "Web Developer",
  "SQL & Database Learner",
  "UI/UX Designer",
  "JavaScript Developer",
  "PHP & Laravel Enthusiast",
  "C Programmer",
  "Full-Stack Developer",
  "Systems Explorer"
];

const IT_ROLE_ITEMS = [{ text: "SOFTWARE ENGINEER", icon: "🛠️" }, { text: "FRONT-END DEVELOPER", icon: "🎨" }, { text: "WEB DEVELOPER", icon: "🌐" }, { text: "SQL & DATABASE LEARNER", icon: "🗄️" }, { text: "JAVASCRIPT DEVELOPER", icon: "⚡" }, { text: "C PROGRAMMING", icon: "⌨️" }, { text: "UI/UX DESIGNER", icon: "✨" }, { text: "FULL-STACK DEVELOPER", icon: "💻" }, { text: "SYSTEMS ADMINISTRATOR", icon: "⚙️" }, { text: "API INTEGRATION SPECIALIST", icon: "🔌" }, { text: "ALGORITHM DESIGNER", icon: "📈" }, { text: "DEVOPS ENTHUSIAST", icon: "♾️" }, { text: "AI & ML EXPLORER", icon: "🤖" }, { text: "MOBILE APP LEARNER", icon: "📱" }, { text: "GITHUB CONTRIBUTOR", icon: "🐙" }, { text: "FULL-STACK", icon: "💻" }, { text: "FRONT-END", icon: "🎨" }, { text: "BACK-END", icon: "⚙️" }, { text: "DATABASE", icon: "🗄️" }, { text: "C PROGRAMMING", icon: "⌨️" }, { text: "JAVASCRIPT", icon: "⚡" }, { text: "PHP LARAVEL", icon: "🚀" }, { text: "GIT & GITHUB", icon: "🐙" }, { text: "SQL & QUERY", icon: "📊" }, { text: "UI/UX DESIGN", icon: "✨" }, { text: "API INTEGRATION", icon: "🔌" }, { text: "PROBLEM SOLVER", icon: "🧩" }, { text: "SYSTEM ARCHITECTURE", icon: "🏛️" }, { text: "CLEAN CODE", icon: "🧹" }, { text: "CLOUD COMPUTING", icon: "☁️" }, { text: "SECURITY PROTOCOL", icon: "🔒" }, { text: "ALGORITHM DESIGN", icon: "📈" }, { text: "DATA STRUCTURES", icon: "📦" }, { text: "VERSION CONTROL", icon: "🌿" }, { text: "RESPONSIVE DESIGN", icon: "📱" }, { text: "PERFORMANCE OPTIMIZATION", icon: "⚡" }, { text: "INNOVATION LAB", icon: "💡" }, { text: "FRONT-END ENGINEER", icon: "🎨" }, { text: "BACK-END SYSTEM", icon: "⚙️" }, { text: "DATABASE ADMINISTRATOR", icon: "🗄️" }, { text: "PHP LARAVEL EXPERT", icon: "🚀" }, { text: "CYBER SECURITY ANALYST", icon: "🔒" }, { text: "CLOUD ARCHITECT", icon: "☁️" }, { text: "DEVOPS ENGINEER", icon: "♾️" }, { text: "AI & ML ENTHUSIAST", icon: "🤖" }, { text: "MOBILE APP DEVELOPER", icon: "📱" }, { text: "GAME DEVELOPER", icon: "🎮" }, { text: "SYSTEM ADMINISTRATOR", icon: "🖥️" }, { text: "DEBUGGING MASTER", icon: "🐛" }];

const GREETINGS = ["Halo", "Hello", "你好", "こんにちは", "안녕하세요", "Bonjour", "Hola", "Ciao", "Guten Tag", "Olá", "Привет", "مرحبًا", "Merhaba", "Silav", "Jambo", "Aloha", "Kia ora", "Talofa", "Bula", "Mālō e lelei", "Ia ora na", "Håfa adai", "Yokwe", "Alii", "Saluton", "Salve", "Grüezi", "Servus", "Ahlan", "Marhaba", "Salam", "Aslema", "Сәлем", "Кош келиңиз", "Salom", "Hoş geldiňiz", "Рәхим итегез", "Bienveniu", "Bienveníu", "Adieu", "Benvenguda", "Mandi", "Benvignût", "Benvegnüü", "Benvenù", "Wëllkomm", "Hoi", "Demat", "Allegra", "Bonjou", "Byenveni", "Wah gwaan", "Bon bini", "Maitei", "Kamisaraki", "Pialli", "Yá'át'ééh", "Aluu", "ᐊᐃ", "Cama-i", "Salute", "Benvenite", "Saluto", "Bonveno", "Glidö", "Welkön", "Helo", "Sugeng rawuh", "Rahajeng rauh", "Peue haba?", "Hàlò", "Dydh da", "Mogrey mie", "Bongu", "Merħba", "Molo", "Salaan", "Akkam", "Sannu", "Bawo", "Ndewo", "Mhoro", "Lumela", "Dumela", "Muraho", "Bwakeye", "Ki kati", "Moni", "Salama"];

let lastItIndex = -1;
let lastGreetingIndex = -1;

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
  // 1. Jika TIDAK ADA section dengan ID (seperti di halaman detail project), matikan indikator active
  if (!sections || sections.length === 0) {
    navLinks.forEach((link) => link.classList.remove("active"));
    return;
  }

  const headerHeight = parseFloat(
    getComputedStyle(document.documentElement).getPropertyValue("--header")
  ) || 74;

  let current = "";

  // 2. Hanya hitung section yang aktif jika posisi scroll > 10px
  if (window.scrollY > 10) {
    const marker = window.scrollY + headerHeight + Math.min(window.innerHeight * 0.28, 220);

    sections.forEach((section) => {
      if (section.offsetTop <= marker) current = section.id;
    });

    if (window.innerHeight + window.scrollY >= document.documentElement.scrollHeight - 15) {
      current = sections.at(-1)?.id || current;
    }
  } else {
    // Kalau posisi paling atas (scrollY <= 10), aktifkan section paling pertama jika ada
    current = sections[0]?.id || "";
  }

  // 3. Cocokkan tautan href dengan ID section yang ditemukan
  navLinks.forEach((link) => {
    const href = link.getAttribute("href");
    const isAnchor = href && href.startsWith("#");
    
    // Hanya nyalakan active jika section ID-nya benar-benar cocok
    if (isAnchor && current) {
      link.classList.toggle("active", href === `#${current}`);
    } else {
      link.classList.remove("active");
    }
  });

  updateNavIndicator();
}

// --- SCROLL REVEAL ---
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

// --- ROLE ROTATOR (HERO TITLE) ---
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

// --- HEADER AVAILABLE ROTATOR (1500ms) ---
function startAvailableRotator() {
  const iconEl = document.getElementById("rotatorIcon");
  const textEl = document.getElementById("rotatorRoleText");
  if (!iconEl || !textEl) return;

  setInterval(() => {
    let newIndex;
    do {
      newIndex = Math.floor(Math.random() * IT_ROLE_ITEMS.length);
    } while (newIndex === lastItIndex && IT_ROLE_ITEMS.length > 1);

    lastItIndex = newIndex;
    const selected = IT_ROLE_ITEMS[newIndex];

    iconEl.style.opacity = "0";
    iconEl.style.transform = "translateY(-6px)";
    textEl.style.opacity = "0";
    textEl.style.transform = "translateY(-6px)";

    setTimeout(() => {
      iconEl.textContent = selected.icon;
      textEl.textContent = selected.text;

      iconEl.style.opacity = "1";
      iconEl.style.transform = "translateY(0)";
      textEl.style.opacity = "1";
      textEl.style.transform = "translateY(0)";
    }, 250);
  }, 1500); 
}
startAvailableRotator();

// --- GREETING ROTATOR (2000ms) ---
function startGreetingRotator() {
  const greetingEl = document.getElementById("greetingRotator");
  if (!greetingEl) return;

  setInterval(() => {
    let newIndex;
    do {
      newIndex = Math.floor(Math.random() * GREETINGS.length);
    } while (newIndex === lastGreetingIndex && GREETINGS.length > 1);

    lastGreetingIndex = newIndex;
    const selectedGreeting = GREETINGS[newIndex];

    greetingEl.style.opacity = "0";
    greetingEl.style.transform = "translateY(-6px)";

    setTimeout(() => {
      greetingEl.textContent = selectedGreeting;
      greetingEl.style.opacity = "1";
      greetingEl.style.transform = "translateY(0)";
    }, 250);
  }, 2000); 
}
startGreetingRotator();

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

// --- SCROLL PROGRESS BAR ---
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

// --- TERMINAL PROGRESS BAR SCRIPT (INSTANT START & SMOOTH SHUTTER SPLIT) ---
(function runIntroLoader() {
  const introOverlay = document.getElementById("introOverlay");
  const percentEl = document.getElementById("loaderPercent");
  const terminalBarEl = document.getElementById("terminalBar");

  if (!introOverlay) return;

  let currentPercent = 0;
  const duration = 800; // Durasi total loading (0.8 detik) - makin cepat!
  const intervalTime = 16; 
  const totalBlocks = 20; 
  const step = 100 / (duration / intervalTime);

  const counterInterval = setInterval(() => {
    currentPercent += step;
    
    if (currentPercent >= 100) {
      currentPercent = 100;
      clearInterval(counterInterval);

      // 1. Ubah warna teks ke hijau neon (Ready State)
      introOverlay.classList.add("ready");

      // 2. Langsung jalankan animasi kebelah pintu shutter & tampilkan konten
      setTimeout(() => {
        introOverlay.classList.add("fade-out");
        document.body.classList.add("intro-done");
      }, 150);
    }

    const percentage = Math.floor(currentPercent);
    if (percentEl) percentEl.textContent = `${percentage}%`;

    const filledBlocks = Math.round((percentage / 100) * totalBlocks);
    const emptyBlocks = totalBlocks - filledBlocks;
    if (terminalBarEl) {
      terminalBarEl.textContent = `[${'█'.repeat(filledBlocks)}${'-'.repeat(emptyBlocks)}]`;
    }
  }, intervalTime);
})();

// --- INTERACTIVE STATUS GLOW REACTION ---
const statusEyebrow = document.querySelector('.eyebrow');
const statusDot = document.querySelector('.status-dot');

if (statusEyebrow && statusDot) {
  statusEyebrow.addEventListener('pointerenter', () => {
    statusDot.style.animationDuration = '1.2s'; // Berdetak lebih cepat saat disentuh kursor
  });
  
  statusEyebrow.addEventListener('pointerleave', () => {
    statusDot.style.animationDuration = '2.4s'; // Kembali normal
  });
}

// --- THEME TOGGLE (DARK / LIGHT MODE) ---
const themeToggleBtn = document.getElementById("themeToggleBtn");
const savedTheme = localStorage.getItem("portfolio_theme");

if (savedTheme === "light") {
  document.body.classList.add("light-mode");
}

themeToggleBtn?.addEventListener("click", () => {
  document.body.classList.toggle("light-mode");
  const isLight = document.body.classList.contains("light-mode");
  localStorage.setItem("portfolio_theme", isLight ? "light" : "dark");
});

// --- SHUTTER CLOSING TRANSITION UNTUK NAVIGASI INTERNAL ---
document.addEventListener("click", (e) => {
  const link = e.target.closest("a");
  if (!link) return;

  const href = link.getAttribute("href");
  const isDownload = link.hasAttribute("download");
  const isExternal = link.getAttribute("target") === "_blank" || (href && href.startsWith("http"));

  // Abaikan scroll internal (#), mailto, tel, dan tombol download CV
  if (!href || href.startsWith("#") || href.startsWith("javascript:") || href.startsWith("mailto:") || href.startsWith("tel:") || isDownload) {
    return;
  }

  // JIKA LINK INTERNAL (View Detail, Back to Projects, Navigasi antarhalaman)
  if (!isExternal) {
    e.preventDefault(); 
    const introOverlay = document.getElementById("introOverlay");

    if (introOverlay) {
      // 1. Reset angka ke 0% & hapus status ready hijau
      const percentEl = document.getElementById("loaderPercent");
      const terminalBarEl = document.getElementById("terminalBar");
      if (percentEl) percentEl.textContent = "0%";
      if (terminalBarEl) terminalBarEl.textContent = "[--------------------]";

      // 2. Reset animasi halaman
      document.body.classList.remove("intro-done");

      // 3. Pasang efek tutup tirai & tampilkan elemen loader
      introOverlay.classList.remove("fade-out", "ready");
      introOverlay.classList.add("closing");

      // 4. Pindah halaman tepat setelah tirai rapat (400ms)
      setTimeout(() => {
        window.location.href = href;
      }, 400);
    } else {
      window.location.href = href;
    }
  }
});

// --- CUSTOM SMOOTH SCROLL (KECEPATAN DISAMAKAN DENGAN VIEW MY WORK) ---
function fastScrollTo(targetY, baseDuration = 550) {
  const startPosition = window.scrollY;
  const distance = targetY - startPosition;
  const startTime = performance.now();

  const isMobile = window.innerWidth <= 850;
  const duration = isMobile ? 650 : baseDuration; 

  function easeOutCubic(t) {
    return 1 - Math.pow(1 - t, 3);
  }

  function animation(currentTime) {
    const timeElapsed = currentTime - startTime;
    const progress = Math.min(timeElapsed / duration, 1);
    const easeProgress = easeOutCubic(progress);

    window.scrollTo(0, startPosition + distance * easeProgress);

    if (progress < 1) {
      requestAnimationFrame(animation);
    }
  }
  requestAnimationFrame(animation);
}

// 1. Event listener khusus Navigasi Header (Home, About, Skills, dll)
navLinks.forEach((link) => {
  link.addEventListener("click", (e) => {
    const href = link.getAttribute("href");

    // Cek apakah link menuju ID lokal (#about, #projects, dll) di halaman ini
    if (href && href.startsWith("#") && href.length > 1) {
      const targetSection = document.querySelector(href);

      if (targetSection) {
        e.preventDefault(); // Tahan scroll kaku bawaan browser
        closeMenu();       // Tutup menu mobile jika sedang terbuka

        // Hitung posisi elemen dikurangi tinggi header
        const headerHeight = parseFloat(
          getComputedStyle(document.documentElement).getPropertyValue("--header")
        ) || 74;

        const targetY = Math.max(0, targetSection.offsetTop - headerHeight + 5);

        // Jalankan scroll cepat (350ms)
        fastScrollTo(targetY, 350);
      }
    }
  });
});

// 2. Event listener khusus tombol Back to Top
document.addEventListener("click", (e) => {
  const backBtn = e.target.closest(".back-to-top-link");
  if (!backBtn) return;

  e.preventDefault();
  fastScrollTo(0, 300); // Scroll cepat ke posisi paling atas (0px)
});

// --- JALANKAN ACTIVE NAV SAAT LOAD, SCROLL & RESIZE ---
window.addEventListener("DOMContentLoaded", updateActiveNav);
window.addEventListener("scroll", updateActiveNav, { passive: true });
window.addEventListener("resize", updateNavIndicator);

// Auto-scroll pas ke hash tujuan saat halaman dimuat (terutama setelah pindah halaman)
window.addEventListener("DOMContentLoaded", () => {
  if (window.location.hash) {
    const targetElement = document.querySelector(window.location.hash);
    if (targetElement) {
      setTimeout(() => {
        targetElement.scrollIntoView({ behavior: "smooth" });
      }, 300); // Beri jeda sedikit agar splash screen selesai termuat
    }
  }
});