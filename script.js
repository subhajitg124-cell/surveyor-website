/* =============================================
   SG SURVEY — Main Script (Premium Interactive)
   ============================================= */

// =============================================
// LOADER — hide as fast as possible
// =============================================
function hideLoader() {
  const loader = document.getElementById('loader');
  if (loader) loader.classList.add('hidden');
}
// Hide immediately when DOM is ready
document.addEventListener('DOMContentLoaded', () => setTimeout(hideLoader, 400));
// Absolute failsafe — force-hide after 2 seconds no matter what
setTimeout(hideLoader, 2000);

// =============================================
// PARTICLES — floating ambient particles
// =============================================
function createParticles() {
  const container = document.createElement('div');
  container.className = 'particles-container';
  document.body.appendChild(container);

  for (let i = 0; i < 30; i++) {
    const particle = document.createElement('div');
    particle.className = 'particle';
    particle.style.left = Math.random() * 100 + '%';
    particle.style.width = particle.style.height = (Math.random() * 3 + 1) + 'px';
    particle.style.animationDuration = (Math.random() * 15 + 10) + 's';
    particle.style.animationDelay = (Math.random() * 15) + 's';
    particle.style.opacity = 0;
    container.appendChild(particle);
  }
}
createParticles();

// =============================================
// CURSOR GLOW — soft ambient light following cursor
// =============================================
const cursorGlow = document.createElement('div');
cursorGlow.className = 'cursor-glow';
document.body.appendChild(cursorGlow);

let mouseX = -500, mouseY = -500;
let glowX = -500, glowY = -500;

document.addEventListener('mousemove', e => {
  mouseX = e.clientX;
  mouseY = e.clientY;
});

function updateGlow() {
  glowX += (mouseX - glowX) * 0.08;
  glowY += (mouseY - glowY) * 0.08;
  cursorGlow.style.left = glowX + 'px';
  cursorGlow.style.top = glowY + 'px';
  requestAnimationFrame(updateGlow);
}
updateGlow();

// =============================================
// RIPPLE EFFECT — on all buttons
// =============================================
function addRipple(e) {
  const btn = e.currentTarget;
  const rect = btn.getBoundingClientRect();
  const ripple = document.createElement('span');
  ripple.className = 'ripple-effect';
  const size = Math.max(rect.width, rect.height);
  ripple.style.width = ripple.style.height = size + 'px';
  ripple.style.left = (e.clientX - rect.left - size / 2) + 'px';
  ripple.style.top = (e.clientY - rect.top - size / 2) + 'px';
  btn.appendChild(ripple);
  setTimeout(() => ripple.remove(), 600);
}

document.querySelectorAll('.btn').forEach(btn => {
  btn.classList.add('ripple');
  btn.addEventListener('click', addRipple);
});

// =============================================
// TILT EFFECT — 3D card hover
// =============================================
function addTiltEffect(elements) {
  elements.forEach(el => {
    el.addEventListener('mousemove', e => {
      const rect = el.getBoundingClientRect();
      const x = (e.clientX - rect.left) / rect.width;
      const y = (e.clientY - rect.top) / rect.height;
      const rotateX = (y - 0.5) * -8;
      const rotateY = (x - 0.5) * 8;
      el.style.transform = `perspective(800px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) scale(1.02)`;
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

// Apply tilt to stats and survey points
document.addEventListener('DOMContentLoaded', () => {
  addTiltEffect(document.querySelectorAll('.stat-item'));
  addTiltEffect(document.querySelectorAll('.survey-point'));
  addTiltEffect(document.querySelectorAll('.pill'));
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

const savedTheme = localStorage.getItem('sg-theme') || 'dark';
applyTheme(savedTheme);

if (themeToggle) {
  themeToggle.addEventListener('click', () => {
    const current = document.body.classList.contains('light') ? 'light' : 'dark';
    const next    = current === 'light' ? 'dark' : 'light';
    localStorage.setItem('sg-theme', next);
    applyTheme(next);
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

// Touch/swipe support for slider
let touchStartX = 0;
let touchEndX = 0;
const sliderWrapper = document.querySelector('.slider-wrapper');
if (sliderWrapper) {
  sliderWrapper.addEventListener('touchstart', e => {
    touchStartX = e.changedTouches[0].screenX;
  }, { passive: true });
  sliderWrapper.addEventListener('touchend', e => {
    touchEndX = e.changedTouches[0].screenX;
    const diff = touchStartX - touchEndX;
    if (Math.abs(diff) > 50) {
      if (diff > 0) { goToSlide(currentSlide + 1); }
      else { goToSlide(currentSlide - 1); }
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

  function easeOutQuart(t) {
    return 1 - Math.pow(1 - t, 4);
  }

  const tick = (now) => {
    const elapsed = now - startTime;
    const progress = Math.min(elapsed / duration, 1);
    const easedProgress = easeOutQuart(progress);
    const current = Math.floor(easedProgress * target);

    el.textContent = current + '+';

    if (progress < 1) {
      requestAnimationFrame(tick);
    } else {
      el.textContent = target + '+';
    }
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
// FADE-UP ON SCROLL — staggered
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
    btn.style.transform = `translateY(-3px) translate(${x * 0.15}px, ${y * 0.15}px)`;
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
        // Remove cursor after typing is done
        setTimeout(() => {
          eyebrow.style.borderRight = 'none';
        }, 800);
      }
    }, 60);
  }
});

// =============================================
// BOOKING FORM — set min date
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

// AJAX form submit
const bookingForm  = document.getElementById('bookingForm');
const formMessage  = document.getElementById('formMessage');

if (bookingForm) {
  bookingForm.addEventListener('submit', async e => {
    e.preventDefault();
    const btn = bookingForm.querySelector('button[type="submit"]');
    const orig = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';

    try {
      const resp = await fetch('book.php', { method: 'POST', body: new FormData(bookingForm) });
      const text = await resp.text();

      if (text.includes('Booking Successful')) {
        formMessage.className = 'form-message success';
        formMessage.textContent = 'Booking submitted successfully! We will contact you soon.';
        bookingForm.reset();
      } else if (text.includes('alert(')) {
        const match = text.match(/alert\('([^']+)'\)/);
        formMessage.className = 'form-message error';
        formMessage.textContent = match ? match[1] : 'Validation error. Please check your input.';
      } else {
        formMessage.className = 'form-message success';
        formMessage.textContent = 'Booking submitted! We will contact you soon.';
        bookingForm.reset();
      }
    } catch (err) {
      formMessage.className = 'form-message error';
      formMessage.textContent = 'Network error. Please try again.';
    } finally {
      btn.disabled = false;
      btn.innerHTML = orig;
      setTimeout(() => { if (formMessage) formMessage.style.display = 'none'; }, 6000);
    }
  });

  // Floating label effect
  bookingForm.querySelectorAll('input, textarea, select').forEach(field => {
    field.addEventListener('focus', () => {
      field.parentElement.classList.add('focused');
    });
    field.addEventListener('blur', () => {
      if (!field.value) {
        field.parentElement.classList.remove('focused');
      }
    });
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
// SMOOTH REVEAL — section dividers
// =============================================
document.addEventListener('DOMContentLoaded', () => {
  // Parallax scroll for hero badge
  const badge = document.querySelector('.hero-badge');
  if (badge) {
    window.addEventListener('scroll', () => {
      const scrolled = window.scrollY;
      badge.style.transform = `rotate(${scrolled * 0.02}deg)`;
    }, { passive: true });
  }
});
