// ==========================================
// PAGE LOADER
// ==========================================

window.addEventListener('load', () => {
    const loader = document.getElementById('pageLoader');
    setTimeout(() => {
        loader.classList.add('hidden');
    }, 1000);
});

// ==========================================
// NAVIGATION - STICKY & SCROLL
// ==========================================

const navbar = document.getElementById('navbar');
const navLinks = document.querySelectorAll('.nav-link');
const mobileToggle = document.getElementById('mobileToggle');
const navMenu = document.getElementById('navMenu');

// Sticky navbar on scroll
window.addEventListener('scroll', () => {
    if (window.scrollY > 50) {
        navbar.classList.add('scrolled');
    } else {
        navbar.classList.remove('scrolled');
    }
    
    // Update active nav link based on scroll position
    updateActiveNavLink();
});

// Smooth scroll and active link
navLinks.forEach(link => {
    link.addEventListener('click', (e) => {
        // Only prevent default for hash links
        if (link.getAttribute('href').startsWith('#')) {
            e.preventDefault();
            
            const targetId = link.getAttribute('href');
            const targetSection = document.querySelector(targetId);
            
            if (targetSection) {
                const offsetTop = targetSection.offsetTop - navbar.offsetHeight;
                window.scrollTo({
                    top: offsetTop,
                    behavior: 'smooth'
                });
            }
            
            // Close mobile menu after click
            navMenu.classList.remove('active');
            mobileToggle.classList.remove('active');
            
            // Update active state
            navLinks.forEach(l => l.classList.remove('active'));
            link.classList.add('active');
        }
    });
});

// Update active nav link based on scroll position
function updateActiveNavLink() {
    const sections = document.querySelectorAll('section[id]');
    const scrollY = window.pageYOffset;
    
    sections.forEach(section => {
        const sectionTop = section.offsetTop - navbar.offsetHeight - 100;
        const sectionHeight = section.offsetHeight;
        const sectionId = section.getAttribute('id');
        
        if (scrollY > sectionTop && scrollY <= sectionTop + sectionHeight) {
            navLinks.forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('href') === `#${sectionId}`) {
                    link.classList.add('active');
                }
            });
        }
    });
}

// Mobile menu toggle
mobileToggle.addEventListener('click', () => {
    mobileToggle.classList.toggle('active');
    navMenu.classList.toggle('active');
});

// Close mobile menu when clicking outside
document.addEventListener('click', (e) => {
    if (!navMenu.contains(e.target) && !mobileToggle.contains(e.target)) {
        navMenu.classList.remove('active');
        mobileToggle.classList.remove('active');
    }
});

// ==========================================
// THEME TOGGLE
// ==========================================

const themeToggle = document.getElementById('themeToggle');
const body = document.body;

// Check for saved theme preference or default to 'light'
const currentTheme = localStorage.getItem('theme') || 'light';
body.setAttribute('data-theme', currentTheme);
updateThemeIcon(currentTheme);

themeToggle.addEventListener('click', () => {
    const theme = body.getAttribute('data-theme');
    const newTheme = theme === 'light' ? 'dark' : 'light';
    
    body.setAttribute('data-theme', newTheme);
    localStorage.setItem('theme', newTheme);
    updateThemeIcon(newTheme);
});

function updateThemeIcon(theme) {
    const icon = themeToggle.querySelector('i');
    if (theme === 'dark') {
        icon.classList.remove('fa-moon');
        icon.classList.add('fa-sun');
    } else {
        icon.classList.remove('fa-sun');
        icon.classList.add('fa-moon');
    }
}

// ==========================================
// IMAGE SLIDER
// ==========================================

const slider = document.getElementById('imageSlider');
const slides = slider.querySelectorAll('.slide');
const prevBtn = document.getElementById('prevBtn');
const nextBtn = document.getElementById('nextBtn');
const dotsContainer = document.getElementById('sliderDots');

let currentSlide = 0;
let slideInterval;

// Create dots
slides.forEach((_, index) => {
    const dot = document.createElement('div');
    dot.classList.add('slider-dot');
    if (index === 0) dot.classList.add('active');
    dot.addEventListener('click', () => goToSlide(index));
    dotsContainer.appendChild(dot);
});

const dots = dotsContainer.querySelectorAll('.slider-dot');

function showSlide(index) {
    slides.forEach(slide => slide.classList.remove('active'));
    dots.forEach(dot => dot.classList.remove('active'));
    
    slides[index].classList.add('active');
    dots[index].classList.add('active');
    
    currentSlide = index;
}

function nextSlide() {
    currentSlide = (currentSlide + 1) % slides.length;
    showSlide(currentSlide);
}

function prevSlide() {
    currentSlide = (currentSlide - 1 + slides.length) % slides.length;
    showSlide(currentSlide);
}

function goToSlide(index) {
    showSlide(index);
    resetSlideInterval();
}

function resetSlideInterval() {
    clearInterval(slideInterval);
    slideInterval = setInterval(nextSlide, 5000);
}

// Event listeners
nextBtn.addEventListener('click', () => {
    nextSlide();
    resetSlideInterval();
});

prevBtn.addEventListener('click', () => {
    prevSlide();
    resetSlideInterval();
});

// Auto-play slider
slideInterval = setInterval(nextSlide, 5000);

// ==========================================
// STATISTICS COUNTER ANIMATION
// ==========================================

const stats = document.querySelectorAll('.stat-number');
let statsAnimated = false;

function animateStats() {
    if (statsAnimated) return;
    
    stats.forEach(stat => {
        const target = parseInt(stat.getAttribute('data-target'));
        const increment = target / 100;
        let current = 0;
        
        const updateCounter = () => {
            current += increment;
            if (current < target) {
                stat.textContent = Math.ceil(current);
                requestAnimationFrame(updateCounter);
            } else {
                stat.textContent = target + '+';
            }
        };
        
        updateCounter();
    });
    
    statsAnimated = true;
}

// Trigger stats animation when section is in view
const statsSection = document.querySelector('.stats');
const statsObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            animateStats();
        }
    });
}, { threshold: 0.5 });

if (statsSection) {
    statsObserver.observe(statsSection);
}

// ==========================================
// TESTIMONIAL SLIDER
// ==========================================

const testimonialSlider = document.getElementById('testimonialSlider');
const testimonialTrack = testimonialSlider.querySelector('.testimonial-track');
const testimonialCards = testimonialTrack.querySelectorAll('.testimonial-card');
const testimonialPrev = document.getElementById('testimonialPrev');
const testimonialNext = document.getElementById('testimonialNext');

let currentTestimonial = 0;

function showTestimonial(index) {
    const offset = -index * 100;
    testimonialTrack.style.transform = `translateX(${offset}%)`;
    currentTestimonial = index;
}

function nextTestimonial() {
    currentTestimonial = (currentTestimonial + 1) % testimonialCards.length;
    showTestimonial(currentTestimonial);
}

function prevTestimonial() {
    currentTestimonial = (currentTestimonial - 1 + testimonialCards.length) % testimonialCards.length;
    showTestimonial(currentTestimonial);
}

testimonialNext.addEventListener('click', nextTestimonial);
testimonialPrev.addEventListener('click', prevTestimonial);

// Auto-rotate testimonials
setInterval(nextTestimonial, 7000);

// ==========================================
// BOOKING FORM SUBMISSION
// ==========================================

const bookingForm = document.getElementById('bookingForm');
const formMessage = document.getElementById('formMessage');

bookingForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    // Get form data
    const formData = new FormData(bookingForm);
    
    // Disable submit button
    const submitBtn = bookingForm.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
    
    try {
        // Send data to backend
        const response = await fetch('book.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Show success message
            formMessage.className = 'form-message success';
            formMessage.textContent = result.message || 'Booking submitted successfully! We will contact you soon.';
            
            // Reset form
            bookingForm.reset();
            
            // Hide message after 5 seconds
            setTimeout(() => {
                formMessage.style.display = 'none';
            }, 5000);
        } else {
            // Show error message
            formMessage.className = 'form-message error';
            formMessage.textContent = result.message || 'Failed to submit booking. Please try again.';
        }
    } catch (error) {
        console.error('Error:', error);
        formMessage.className = 'form-message error';
        formMessage.textContent = 'An error occurred. Please try again later.';
    } finally {
        // Re-enable submit button
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
});

// ==========================================
// FORM VALIDATION
// ==========================================

// Set minimum date for booking (today)
const dateInput = document.getElementById('date');
if (dateInput) {
    const today = new Date().toISOString().split('T')[0];
    dateInput.setAttribute('min', today);
}

// Phone number validation
const phoneInput = document.getElementById('phone');
if (phoneInput) {
    phoneInput.addEventListener('input', (e) => {
        // Remove non-numeric characters
        e.target.value = e.target.value.replace(/[^0-9]/g, '');
        
        // Limit to 10 digits
        if (e.target.value.length > 10) {
            e.target.value = e.target.value.slice(0, 10);
        }
    });
}

// ==========================================
// SCROLL ANIMATIONS
// ==========================================

// Fade in elements on scroll
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -100px 0px'
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
        }
    });
}, observerOptions);

// Observe elements
const animateElements = document.querySelectorAll('.service-card, .survey-point, .testimonial-card, .info-item');
animateElements.forEach(el => {
    el.style.opacity = '0';
    el.style.transform = 'translateY(20px)';
    el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
    observer.observe(el);
});

// ==========================================
// SMOOTH SCROLL FOR ALL HASH LINKS
// ==========================================

document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        const href = this.getAttribute('href');
        
        // Skip if it's just '#' or admin link
        if (href === '#' || this.classList.contains('admin-link')) return;
        
        e.preventDefault();
        
        const targetElement = document.querySelector(href);
        if (targetElement) {
            const offsetTop = targetElement.offsetTop - navbar.offsetHeight;
            window.scrollTo({
                top: offsetTop,
                behavior: 'smooth'
            });
        }
    });
});

// ==========================================
// UTILITY FUNCTIONS
// ==========================================

// Debounce function for performance
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

// Apply debounce to scroll events if needed
window.addEventListener('scroll', debounce(() => {
    // Additional scroll-based animations can be added here
}, 100));

// ==========================================
// CONSOLE MESSAGE
// ==========================================

console.log('%c🗺️ SG Survey - Professional Land Surveying Services', 'color: #0a4d68; font-size: 16px; font-weight: bold;');
console.log('%cWebsite developed with precision and care', 'color: #05c3de; font-size: 12px;');
window.addEventListener("scroll", () => {
  document.querySelectorAll(".card").forEach(card => {
    const top = card.getBoundingClientRect().top;
    if(top < window.innerHeight){
      card.style.opacity = 1;
      card.style.transform = "translateY(0)";
    }
  });
});
const toggle = document.getElementById("themeToggle");

toggle.onclick = () => {
  document.body.classList.toggle("light");

  if(document.body.classList.contains("light")){
    localStorage.setItem("theme","light");
  } else {
    localStorage.setItem("theme","dark");
  }
};

window.onload = () => {
  if(localStorage.getItem("theme") === "light"){
    document.body.classList.add("light");
  }
};
window.addEventListener("load", () => {
  document.getElementById("loader").style.display = "none";
});
function payNow() {

  let amount = prompt("Enter amount to pay (₹):");

  if(!amount) return;

  let upiID = "yourupi@upi"; // 🔴 change this
  let name = "Swarupananda Ghosh";

  let url = `upi://pay?pa=${upiID}&pn=${name}&am=${amount}&cu=INR&tn=Survey Payment`;

  window.location.href = url;
}