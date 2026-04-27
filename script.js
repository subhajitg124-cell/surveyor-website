/* =============================================
   SG SURVEY — Main Script (Water & Glass Effects)
   ============================================= */

// =============================================
// LOADER
// =============================================
function hideLoader() {
  const loader = document.getElementById('loader');
  if (loader) loader.classList.add('hidden');
}
document.addEventListener('DOMContentLoaded', () => setTimeout(hideLoader, 500));
setTimeout(hideLoader, 2500);

// =============================================
// WATER RIPPLE CANVAS — Full-screen interactive water
// =============================================
const waterCanvas = document.getElementById('waterCanvas');
let waterCtx, waterWidth, waterHeight;
let buffer1, buffer2;
const DAMPING = 0.97;
const waterDrops = [];

function initWaterCanvas() {
  if (!waterCanvas) return;
  waterCtx = waterCanvas.getContext('2d');
  resizeWaterCanvas();
  window.addEventListener('resize', resizeWaterCanvas);
}

function resizeWaterCanvas() {
  // Use low resolution for performance
  const scale = 0.25;
  waterWidth = Math.floor(window.innerWidth * scale);
  waterHeight = Math.floor(window.innerHeight * scale);
  waterCanvas.width = waterWidth;
  waterCanvas.height = waterHeight;
  waterCanvas.style.width = '100%';
  waterCanvas.style.height = '100%';
  buffer1 = new Float32Array(waterWidth * waterHeight);
  buffer2 = new Float32Array(waterWidth * waterHeight);
}

// Desktop devices have the water ripple effect disabled (per user request).
// Touch/mobile devices keep it for tactile feedback.
const IS_DESKTOP = window.matchMedia('(hover: hover) and (pointer: fine)').matches;

function addWaterDrop(x, y, radius, strength) {
  if (IS_DESKTOP) return; // No ripples on PC
  if (!waterWidth || !waterHeight) return;
  const sx = Math.floor(x * waterWidth / window.innerWidth);
  const sy = Math.floor(y * waterHeight / window.innerHeight);
  
  for (let dy = -radius; dy <= radius; dy++) {
    for (let dx = -radius; dx <= radius; dx++) {
      const px = sx + dx;
      const py = sy + dy;
      if (px >= 0 && px < waterWidth && py >= 0 && py < waterHeight) {
        const dist = Math.sqrt(dx*dx + dy*dy);
        if (dist < radius) {
          const amt = strength * (1 - dist / radius);
          buffer1[py * waterWidth + px] += amt;
        }
      }
    }
  }
}

function updateWater() {
  for (let y = 1; y < waterHeight - 1; y++) {
    for (let x = 1; x < waterWidth - 1; x++) {
      const i = y * waterWidth + x;
      buffer2[i] = (
        buffer1[i - 1] +
        buffer1[i + 1] +
        buffer1[i - waterWidth] +
        buffer1[i + waterWidth]
      ) / 2 - buffer2[i];
      buffer2[i] *= DAMPING;
    }
  }
  // Swap buffers
  const temp = buffer1;
  buffer1 = buffer2;
  buffer2 = temp;
}

function renderWater() {
  if (!waterCtx) return;
  const imageData = waterCtx.createImageData(waterWidth, waterHeight);
  const data = imageData.data;
  const isLight = document.body.classList.contains('light');
  
  for (let y = 1; y < waterHeight - 1; y++) {
    for (let x = 1; x < waterWidth - 1; x++) {
      const i = y * waterWidth + x;
      const val = buffer1[i];
      const idx = i * 4;
      
      // Water-like highlight/shadow
      const brightness = Math.max(0, Math.min(255, 128 + val * 400));
      
      if (isLight) {
        // Light mode — subtle blue tint
        data[idx]     = Math.floor(brightness * 0.65);  // R
        data[idx + 1] = Math.floor(brightness * 0.8);   // G
        data[idx + 2] = Math.floor(brightness * 1.0);   // B
      } else {
        // Dark mode — gold/white tint
        data[idx]     = Math.floor(brightness * 0.9);    // R
        data[idx + 1] = Math.floor(brightness * 0.8);    // G
        data[idx + 2] = Math.floor(brightness * 0.5);    // B
      }
      data[idx + 3] = Math.floor(Math.abs(val) * 80);   // Alpha
    }
  }
  
  waterCtx.putImageData(imageData, 0, 0);
}

function waterLoop() {
  updateWater();
  renderWater();
  requestAnimationFrame(waterLoop);
}

// Mouse movement creates water drops
let lastWaterDrop = 0;
document.addEventListener('mousemove', (e) => {
  const now = Date.now();
  if (now - lastWaterDrop > 50) { // Throttle
    addWaterDrop(e.clientX, e.clientY, 3, 80);
    lastWaterDrop = now;
  }
});

// Click creates bigger splash
document.addEventListener('click', (e) => {
  addWaterDrop(e.clientX, e.clientY, 6, 200);
});

// Auto raindrops
function autoRaindrop() {
  if (waterWidth && waterHeight) {
    const x = Math.random() * window.innerWidth;
    const y = Math.random() * window.innerHeight;
    addWaterDrop(x, y, 2, 40);
  }
  setTimeout(autoRaindrop, 2000 + Math.random() * 4000);
}

// Skip the entire water simulation on desktop (saves CPU + GPU)
if (!IS_DESKTOP) {
  initWaterCanvas();
  if (waterCanvas) {
    waterLoop();
    setTimeout(autoRaindrop, 3000);
  }
}

// =============================================
// WATER HOVER — Track cursor position on elements
// =============================================
document.querySelectorAll('.water-hover').forEach(el => {
  el.addEventListener('mousemove', (e) => {
    const rect = el.getBoundingClientRect();
    const x = e.clientX - rect.left;
    const y = e.clientY - rect.top;
    el.style.setProperty('--mouse-x', x + 'px');
    el.style.setProperty('--mouse-y', y + 'px');
  });
  
  el.addEventListener('mouseleave', () => {
    el.style.setProperty('--mouse-x', '50%');
    el.style.setProperty('--mouse-y', '50%');
  });
});

// =============================================
// WATER DROP RINGS — On click create expanding rings
// =============================================
document.addEventListener('click', (e) => {
  // Create 3 concentric ripple rings at click position
  for (let i = 0; i < 3; i++) {
    const ring = document.createElement('div');
    ring.className = 'water-drop-ring';
    ring.style.left = e.clientX + 'px';
    ring.style.top = e.clientY + 'px';
    ring.style.position = 'fixed';
    ring.style.animationDelay = (i * 0.15) + 's';
    document.body.appendChild(ring);
    setTimeout(() => ring.remove(), 1000);
  }
});

// =============================================
// PARTICLES & BUBBLES
// =============================================
function createParticles() {
  const container = document.createElement('div');
  container.className = 'particles-container';
  document.body.appendChild(container);

  // Gold & blue particles
  for (let i = 0; i < 25; i++) {
    const particle = document.createElement('div');
    particle.className = 'particle ' + (Math.random() > 0.5 ? 'gold' : 'blue');
    particle.style.left = Math.random() * 100 + '%';
    particle.style.width = particle.style.height = (Math.random() * 3 + 1) + 'px';
    particle.style.animationDuration = (Math.random() * 15 + 12) + 's';
    particle.style.animationDelay = (Math.random() * 15) + 's';
    container.appendChild(particle);
  }

  // Water bubbles
  for (let i = 0; i < 12; i++) {
    const bubble = document.createElement('div');
    bubble.className = 'bubble';
    bubble.style.left = Math.random() * 100 + '%';
    const size = Math.random() * 20 + 8;
    bubble.style.width = size + 'px';
    bubble.style.height = size + 'px';
    bubble.style.animationDuration = (Math.random() * 20 + 15) + 's';
    bubble.style.animationDelay = (Math.random() * 20) + 's';
    container.appendChild(bubble);
  }
}
createParticles();

// =============================================
// CURSOR SPOTLIGHT — Professional smooth glow
// (Native cursor is preserved — no custom replacement)
// =============================================
const isTouchDevice = window.matchMedia('(hover: none), (pointer: coarse)').matches;

if (!isTouchDevice) {
  const cursorGlow = document.createElement('div');
  cursorGlow.className = 'cursor-glow';
  document.body.appendChild(cursorGlow);

  let mouseX = window.innerWidth / 2;
  let mouseY = window.innerHeight / 2;
  let glowX = mouseX;
  let glowY = mouseY;
  let isMouseOver = false;
  let targetOpacity = 0;
  let currentOpacity = 0;

  document.addEventListener('mousemove', (e) => {
    mouseX = e.clientX;
    mouseY = e.clientY;
    if (!isMouseOver) {
      isMouseOver = true;
      targetOpacity = 1;
    }
  }, { passive: true });

  document.addEventListener('mouseleave', () => {
    isMouseOver = false;
    targetOpacity = 0;
  });

  document.addEventListener('mouseenter', () => {
    isMouseOver = true;
    targetOpacity = 1;
  });

  function updateCursor() {
    // Smooth position tracking with easing
    glowX += (mouseX - glowX) * 0.12;
    glowY += (mouseY - glowY) * 0.12;

    // Smooth opacity transition
    currentOpacity += (targetOpacity - currentOpacity) * 0.15;

    cursorGlow.style.transform = `translate3d(${glowX}px, ${glowY}px, 0) translate(-50%, -50%)`;
    cursorGlow.style.opacity = currentOpacity.toFixed(3);

    requestAnimationFrame(updateCursor);
  }
  updateCursor();
}

// =============================================
// RIPPLE EFFECT — Professional Material Design
// =============================================
function addRipple(e) {
  const btn = e.currentTarget;
  const rect = btn.getBoundingClientRect();
  
  // Create main ripple
  const ripple = document.createElement('span');
  ripple.className = 'ripple-effect';
  const size = Math.max(rect.width, rect.height) * 1.5;
  ripple.style.width = ripple.style.height = size + 'px';
  ripple.style.left = (e.clientX - rect.left - size / 2) + 'px';
  ripple.style.top = (e.clientY - rect.top - size / 2) + 'px';
  
  btn.appendChild(ripple);
  
  // Remove ripple after animation completes
  setTimeout(() => ripple.remove(), 750);
}

document.querySelectorAll('.btn').forEach(btn => {
  btn.classList.add('ripple');
  btn.addEventListener('click', addRipple);
});

// =============================================
// TILT EFFECT — 3D water-like card hover
// =============================================
function addTiltEffect(elements) {
  elements.forEach(el => {
    el.addEventListener('mousemove', e => {
      const rect = el.getBoundingClientRect();
      const x = (e.clientX - rect.left) / rect.width;
      const y = (e.clientY - rect.top) / rect.height;
      const rotateX = (y - 0.5) * -6;
      const rotateY = (x - 0.5) * 6;
      el.style.transform = `perspective(800px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) scale(1.01)`;
    });

    el.addEventListener('mouseleave', () => {
      el.style.transform = '';
      el.style.transition = 'transform 0.5s cubic-bezier(.4,0,.2,1)';
      setTimeout(() => { el.style.transition = ''; }, 500);
    });

    el.addEventListener('mouseenter', () => {
      el.style.transition = 'none';
    });
  });
}

document.addEventListener('DOMContentLoaded', () => {
  addTiltEffect(document.querySelectorAll('.stat-item'));
  addTiltEffect(document.querySelectorAll('.survey-point'));
  addTiltEffect(document.querySelectorAll('.testimonial-card'));
  addTiltEffect(document.querySelectorAll('.info-item'));
});

// =============================================
// FORM FIELD INTERACTIVE SPOTLIGHT
// Mouse-tracking gradient + ripple on focus
// =============================================
document.querySelectorAll('.form-group input, .form-group select, .form-group textarea').forEach(field => {
  // Track mouse position inside field for the spotlight gradient
  field.addEventListener('mousemove', e => {
    const rect = field.getBoundingClientRect();
    const x = ((e.clientX - rect.left) / rect.width) * 100;
    const y = ((e.clientY - rect.top) / rect.height) * 100;
    field.style.setProperty('--mx', x + '%');
    field.style.setProperty('--my', y + '%');
  });

  field.addEventListener('mouseleave', () => {
    field.style.setProperty('--mx', '-100px');
    field.style.setProperty('--my', '-100px');
  });

  // Water ripple at field on focus
  field.addEventListener('focus', () => {
    const rect = field.getBoundingClientRect();
    const cx = rect.left + rect.width / 2;
    const cy = rect.top + rect.height / 2;
    if (typeof addWaterDrop === 'function') {
      addWaterDrop(cx, cy, 5, 120);
    }
    field.style.setProperty('--mx', '50%');
    field.style.setProperty('--my', '50%');
  });

  // Subtle "shimmer sweep" on hover-in
  field.addEventListener('mouseenter', () => {
    field.style.transition = 'border-color 0.35s ease, background 0.4s ease, box-shadow 0.35s ease, transform 0.35s cubic-bezier(.34,1.56,.64,1), padding-left 0.35s ease';
  });
});

// =============================================
// NAVBAR — sticky + scroll active
// =============================================
const navbar  = document.getElementById('navbar');
const navLinks = document.querySelectorAll('.nav-link');

window.addEventListener('scroll', () => {
  navbar.classList.toggle('scrolled', window.scrollY > 60);
  updateActiveLink();
});

function updateActiveLink() {
  const scrollY = window.pageYOffset + 120;
  document.querySelectorAll('section[id], div.stats-strip').forEach(section => {
    const top    = section.offsetTop;
    const height = section.offsetHeight;
    const id     = section.id || '';
    if (scrollY >= top && scrollY < top + height) {
      navLinks.forEach(a => {
        a.classList.toggle('active', a.getAttribute('href') === '#' + id || (id === 'home' && a.getAttribute('href') === '#'));
      });
    }
  });
}

// Mobile menu
const mobileToggle = document.getElementById('mobileToggle');
const navMenu      = document.getElementById('navMenu');

if (mobileToggle && navMenu) {
  mobileToggle.addEventListener('click', () => {
    navMenu.style.display = navMenu.style.display === 'flex' ? 'none' : 'flex';
    navMenu.style.flexDirection = 'column';
    navMenu.style.position = 'absolute';
    navMenu.style.top = '70px';
    navMenu.style.left = '0';
    navMenu.style.right = '0';
    navMenu.style.background = 'rgba(6,9,15,0.95)';
    navMenu.style.backdropFilter = 'blur(24px)';
    navMenu.style.padding = '20px 24px';
    navMenu.style.borderBottom = '1px solid rgba(255,255,255,0.08)';
    navMenu.style.gap = '18px';
    navMenu.style.boxShadow = '0 20px 40px rgba(0,0,0,0.3)';
  });

  document.addEventListener('click', e => {
    if (!navMenu.contains(e.target) && !mobileToggle.contains(e.target)) {
      navMenu.style.display = 'none';
    }
  });
}

// =============================================
// THEME TOGGLE
// =============================================
const themeToggle = document.getElementById('themeToggle');

function applyTheme(theme) {
  document.body.classList.toggle('light', theme === 'light');
  const icon = themeToggle ? themeToggle.querySelector('i') : null;
  if (icon) {
    icon.className = theme === 'light' ? 'fas fa-moon' : 'fas fa-sun';
  }
}

const savedTheme = localStorage.getItem('sg-theme') || 'light';
applyTheme(savedTheme);

if (themeToggle) {
  themeToggle.addEventListener('click', () => {
    const current = document.body.classList.contains('light') ? 'light' : 'dark';
    const next    = current === 'light' ? 'dark' : 'light';
    localStorage.setItem('sg-theme', next);
    applyTheme(next);
    // Create a big splash on theme toggle
    addWaterDrop(window.innerWidth / 2, window.innerHeight / 2, 10, 300);
  });
}

// =============================================
// IMAGE SLIDER
// =============================================
const sliderTrack = document.getElementById('sliderTrack');
const dotsEl      = document.querySelectorAll('#sliderDots .dot');
const prevBtn     = document.getElementById('prevBtn');
const nextBtn     = document.getElementById('nextBtn');

let currentSlide  = 0;
const totalSlides = dotsEl.length;
let sliderTimer;

function goToSlide(index) {
  currentSlide = (index + totalSlides) % totalSlides;
  if (sliderTrack) sliderTrack.style.transform = `translateX(-${currentSlide * 100}%)`;
  dotsEl.forEach((d, i) => d.classList.toggle('active', i === currentSlide));
}

function startTimer() {
  clearInterval(sliderTimer);
  sliderTimer = setInterval(() => goToSlide(currentSlide + 1), 5000);
}

if (prevBtn) prevBtn.addEventListener('click', () => { goToSlide(currentSlide - 1); startTimer(); });
if (nextBtn) nextBtn.addEventListener('click', () => { goToSlide(currentSlide + 1); startTimer(); });
dotsEl.forEach((d, i) => d.addEventListener('click', () => { goToSlide(i); startTimer(); }));
startTimer();

// Touch/swipe support
let touchStartX = 0;
const sliderWrapper = document.querySelector('.slider-wrapper');
if (sliderWrapper) {
  sliderWrapper.addEventListener('touchstart', e => {
    touchStartX = e.changedTouches[0].screenX;
  }, { passive: true });
  sliderWrapper.addEventListener('touchend', e => {
    const diff = touchStartX - e.changedTouches[0].screenX;
    if (Math.abs(diff) > 50) {
      diff > 0 ? goToSlide(currentSlide + 1) : goToSlide(currentSlide - 1);
      startTimer();
    }
  }, { passive: true });
}

// =============================================
// STATS COUNTER — with easing
// =============================================
function animateCounter(el) {
  const target = parseInt(el.getAttribute('data-target'), 10);
  const duration = 2000;
  const startTime = performance.now();

  function easeOutQuart(t) { return 1 - Math.pow(1 - t, 4); }

  const tick = (now) => {
    const elapsed = now - startTime;
    const progress = Math.min(elapsed / duration, 1);
    const current = Math.floor(easeOutQuart(progress) * target);
    el.textContent = current + '+';
    if (progress < 1) requestAnimationFrame(tick);
    else el.textContent = target + '+';
  };
  requestAnimationFrame(tick);
}

const statNumbers = document.querySelectorAll('.stat-number');
const statsObserver = new IntersectionObserver(entries => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      animateCounter(entry.target);
      statsObserver.unobserve(entry.target);
    }
  });
}, { threshold: 0.5 });

statNumbers.forEach(el => statsObserver.observe(el));

// =============================================
// FADE-UP ON SCROLL
// =============================================
const fadeObserver = new IntersectionObserver(entries => {
  entries.forEach((entry, i) => {
    if (entry.isIntersecting) {
      setTimeout(() => entry.target.classList.add('visible'), i * 100);
      fadeObserver.unobserve(entry.target);
    }
  });
}, { threshold: 0.08, rootMargin: '0px 0px -50px 0px' });

document.querySelectorAll('.fade-up').forEach(el => fadeObserver.observe(el));

// =============================================
// SMOOTH SCROLL
// =============================================
document.querySelectorAll('a[href^="#"]').forEach(a => {
  a.addEventListener('click', e => {
    const href = a.getAttribute('href');
    if (href === '#') return;
    const target = document.querySelector(href);
    if (target) {
      e.preventDefault();
      const offset = target.offsetTop - (navbar ? navbar.offsetHeight : 0) - 10;
      window.scrollTo({ top: offset, behavior: 'smooth' });
      if (navMenu) navMenu.style.display = 'none';
    }
  });
});

// =============================================
// MAGNETIC BUTTONS — subtle pull effect
// =============================================
document.querySelectorAll('.btn-primary').forEach(btn => {
  btn.addEventListener('mousemove', e => {
    const rect = btn.getBoundingClientRect();
    const x = e.clientX - rect.left - rect.width / 2;
    const y = e.clientY - rect.top - rect.height / 2;
    btn.style.transform = `translateY(-3px) translate(${x * 0.12}px, ${y * 0.12}px)`;
  });
  btn.addEventListener('mouseleave', () => {
    btn.style.transform = '';
  });
});

// =============================================
// TYPED TEXT EFFECT — on hero eyebrow
// =============================================
document.addEventListener('DOMContentLoaded', () => {
  const eyebrow = document.querySelector('.hero-eyebrow');
  if (eyebrow) {
    const text = eyebrow.textContent;
    eyebrow.textContent = '';
    eyebrow.style.borderRight = '2px solid var(--accent)';
    let i = 0;
    const typeInterval = setInterval(() => {
      eyebrow.textContent += text[i];
      i++;
      if (i >= text.length) {
        clearInterval(typeInterval);
        setTimeout(() => { eyebrow.style.borderRight = 'none'; }, 800);
      }
    }, 60);
  }
});

// =============================================
// SCROLL-TRIGGERED WATER DROPS
// =============================================
let lastScrollY = 0;
window.addEventListener('scroll', () => {
  const scrollDelta = Math.abs(window.scrollY - lastScrollY);
  if (scrollDelta > 30 && waterWidth) {
    // Create random drops when scrolling fast
    const x = Math.random() * window.innerWidth;
    const y = Math.random() * window.innerHeight;
    addWaterDrop(x, y, 2, scrollDelta * 0.5);
  }
  lastScrollY = window.scrollY;
}, { passive: true });

// =============================================
// BOOKING FORM
// =============================================
const dateInput = document.getElementById('date');
if (dateInput) {
  dateInput.min = new Date().toISOString().split('T')[0];
}

const phoneInput = document.getElementById('phone');
if (phoneInput) {
  phoneInput.addEventListener('input', e => {
    e.target.value = e.target.value.replace(/[^0-9]/g, '').slice(0, 10);
  });
}

const bookingForm = document.getElementById('bookingForm');
const formMessage = document.getElementById('formMessage');

if (bookingForm) {
  bookingForm.addEventListener('submit', async e => {
    e.preventDefault();
    const btn = bookingForm.querySelector('button[type="submit"]');
    const orig = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';

    try {
      const fd = new FormData(bookingForm);
      const resp = await fetch('book.php', {
        method: 'POST',
        body: fd,
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
      });
      const data = await resp.json();

      if (data.success) {
        formMessage.className = 'form-message success';
        formMessage.textContent = 'Booking confirmed! Redirecting...';
        // Celebration splash
        for (let i = 0; i < 5; i++) {
          setTimeout(() => {
            addWaterDrop(Math.random() * window.innerWidth, Math.random() * window.innerHeight, 5, 150);
          }, i * 200);
        }
        // Redirect to confirmation page (which auto-sends WhatsApp to owner)
        setTimeout(() => { window.location.href = 'booking-confirmation.php'; }, 800);
      } else {
        formMessage.className = 'form-message error';
        formMessage.textContent = data.message || 'Validation error. Please check your input.';
        btn.disabled = false;
        btn.innerHTML = orig;
        setTimeout(() => { if (formMessage) formMessage.style.display = 'none'; }, 6000);
      }
    } catch (err) {
      formMessage.className = 'form-message error';
      formMessage.textContent = 'Network error. Please try again.';
      btn.disabled = false;
      btn.innerHTML = orig;
      setTimeout(() => { if (formMessage) formMessage.style.display = 'none'; }, 6000);
    }
  });
}

// =============================================
// PAY NOW (UPI)
// =============================================
function payNow() {
  const amount = prompt('Enter advance amount to pay (₹):');
  if (!amount || isNaN(amount)) return;
  const upiID = 'swarupanandaghosh@upi';
  const url = `upi://pay?pa=${upiID}&pn=Swarupananda+Ghosh&am=${amount}&cu=INR&tn=Survey+Advance+Payment`;
  window.location.href = url;
}

// =============================================
// PARALLAX SCROLL — badge rotation
// =============================================
document.addEventListener('DOMContentLoaded', () => {
  const badge = document.querySelector('.hero-badge');
  if (badge) {
    window.addEventListener('scroll', () => {
      badge.style.transform = `rotate(${window.scrollY * 0.02}deg)`;
    }, { passive: true });
  }
});

// =============================================
// SCROLL PROGRESS BAR
// =============================================
const scrollProgress = document.getElementById('scrollProgress');
if (scrollProgress) {
  window.addEventListener('scroll', () => {
    const h = document.documentElement;
    const scrolled = (h.scrollTop / (h.scrollHeight - h.clientHeight)) * 100;
    scrollProgress.style.width = scrolled + '%';
  }, { passive: true });
}

// =============================================
// BACK TO TOP BUTTON
// =============================================
const backToTop = document.getElementById('backToTop');
if (backToTop) {
  window.addEventListener('scroll', () => {
    if (window.scrollY > 500) backToTop.classList.add('visible');
    else backToTop.classList.remove('visible');
  }, { passive: true });

  backToTop.addEventListener('click', () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  });
}

// =============================================
// WHATSAPP FLOAT — auto-lift when footer in view
// (so it never covers the Admin link)
// =============================================
const waFloat = document.querySelector('.whatsapp-float');
const footerEl = document.querySelector('footer');
if (waFloat && footerEl && 'IntersectionObserver' in window) {
  const obs = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        waFloat.classList.add('lifted');
        if (backToTop) backToTop.classList.add('lifted');
      } else {
        waFloat.classList.remove('lifted');
        if (backToTop) backToTop.classList.remove('lifted');
      }
    });
  }, { rootMargin: '0px 0px -40px 0px', threshold: 0.01 });
  obs.observe(footerEl);
}

// =============================================
// MOBILE TAP RIPPLE — visual touch feedback
// =============================================
if (window.matchMedia('(hover: none), (pointer: coarse)').matches) {
  const rippleSelector = '.btn, .service-card, .testimonial-card, .info-item, .stat-item, .nav-link, .footer-links a';
  document.addEventListener('touchstart', (e) => {
    const target = e.target.closest(rippleSelector);
    if (!target) return;
    const rect = target.getBoundingClientRect();
    const touch = e.touches[0];
    const ripple = document.createElement('span');
    ripple.className = 'tap-ripple';
    const size = Math.max(rect.width, rect.height) * 0.6;
    ripple.style.width = ripple.style.height = size + 'px';
    ripple.style.left = (touch.clientX - rect.left - size / 2) + 'px';
    ripple.style.top = (touch.clientY - rect.top - size / 2) + 'px';
    const prevPos = getComputedStyle(target).position;
    if (prevPos === 'static') target.style.position = 'relative';
    const prevOverflow = getComputedStyle(target).overflow;
    if (prevOverflow !== 'hidden') target.style.overflow = 'hidden';
    target.appendChild(ripple);
    setTimeout(() => ripple.remove(), 650);
  }, { passive: true });
}

// =============================================
// MOBILE PARALLAX — gentle background blob drift on scroll
// =============================================
if (window.matchMedia('(max-width: 900px)').matches) {
  const blobs = document.querySelectorAll('.mobile-bg .m-blob');
  let ticking = false;
  window.addEventListener('scroll', () => {
    if (ticking) return;
    ticking = true;
    requestAnimationFrame(() => {
      const y = window.scrollY;
      blobs.forEach((b, i) => {
        const speed = (i + 1) * 0.08;
        b.style.translate = `0 ${y * speed}px`;
      });
      ticking = false;
    });
  }, { passive: true });
}
