/* =============================================
   SG SURVEY — Main Script
   ============================================= */

// =============================================
// LOADER
// =============================================
window.addEventListener('load', () => {
  setTimeout(() => {
    const loader = document.getElementById('loader');
    if (loader) loader.classList.add('hidden');
  }, 900);
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
    navMenu.style.background = 'rgba(8,13,26,0.97)';
    navMenu.style.backdropFilter = 'blur(20px)';
    navMenu.style.padding = '20px 24px';
    navMenu.style.borderBottom = '1px solid rgba(255,255,255,0.1)';
    navMenu.style.gap = '18px';
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

// =============================================
// STATS COUNTER
// =============================================
function animateCounter(el) {
  const target = parseInt(el.getAttribute('data-target'), 10);
  const duration = 1800;
  const step = target / (duration / 16);
  let current = 0;

  const tick = () => {
    current += step;
    if (current < target) {
      el.textContent = Math.floor(current) + '+';
      requestAnimationFrame(tick);
    } else {
      el.textContent = target + '+';
    }
  };
  tick();
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
      setTimeout(() => entry.target.classList.add('visible'), i * 80);
      fadeObserver.unobserve(entry.target);
    }
  });
}, { threshold: 0.1, rootMargin: '0px 0px -60px 0px' });

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
