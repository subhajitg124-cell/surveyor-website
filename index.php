<?php
require_once 'db.php';
$pdo = getDBConnection();

$siteDataRows = $pdo->query("SELECT data_key, data_value FROM site_data")->fetchAll();
$siteData = [];
foreach ($siteDataRows as $row) {
    $siteData[$row['data_key']] = $row['data_value'];
}

$phone    = $siteData['phone_primary']    ?? '9475465392';
$phone2   = $siteData['phone_secondary']  ?? '8637829746';
$email    = $siteData['email']            ?? 'swarupanandaghosh2@gmail.com';
$location = $siteData['location']         ?? 'Kochkunda, Shitla, Bankura';

$chargeLand    = $siteData['charge_land_survey']    ?? '5000';
$chargeDigital = $siteData['charge_digital_survey'] ?? '8000';
$chargeAutocad = $siteData['charge_autocad_sketch'] ?? '3000';
$chargeLaser   = $siteData['charge_laser_survey']   ?? '10000';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SG Survey — Professional Land Surveying Services</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700;900&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="style.css">
</head>
<body>

<!-- BACKGROUND ORBS -->
<div class="bg-orbs">
  <div class="orb orb-1"></div>
  <div class="orb orb-2"></div>
  <div class="orb orb-3"></div>
</div>

<!-- LOADER -->
<div id="loader">
  <div class="loader-logo">SG SURVEY</div>
  <div class="loader-bar"><div class="loader-bar-fill"></div></div>
</div>

<!-- NAVBAR -->
<nav class="navbar" id="navbar">
  <a href="#" class="nav-logo">SG <span>Survey</span></a>
  <ul class="nav-links" id="navMenu">
    <li><a href="#" class="nav-link active">Home</a></li>
    <li><a href="#services" class="nav-link">Services</a></li>
    <li><a href="#about" class="nav-link">About</a></li>
    <li><a href="#contact" class="nav-link">Contact</a></li>
  </ul>
  <div class="nav-actions">
    <button id="themeToggle" title="Toggle theme"><i class="fas fa-moon"></i></button>
    <button class="mobile-menu-btn" id="mobileToggle"><i class="fas fa-bars"></i></button>
  </div>
</nav>

<!-- HERO -->
<section class="hero" id="home">
  <div class="hero-container">
    <div class="hero-left fade-up">
      <div class="hero-eyebrow">Licensed Professional</div>
      <h1>Swarupananda<br><em>Ghosh</em></h1>
      <p class="hero-subtitle">Precision land surveying services in Bankura — delivering accurate measurements, legal documentation, and modern digital survey solutions.</p>

      <div class="hero-contact-pills">
        <div class="pill"><i class="fas fa-phone"></i><?= htmlspecialchars($phone) ?></div>
        <div class="pill"><i class="fas fa-envelope"></i><?= htmlspecialchars($email) ?></div>
        <div class="pill"><i class="fas fa-map-marker-alt"></i><?= htmlspecialchars($location) ?></div>
      </div>

      <div class="btn-group">
        <a href="#contact" class="btn btn-primary"><i class="fas fa-calendar-check"></i> Book a Survey</a>
        <a href="tel:<?= htmlspecialchars($phone) ?>" class="btn btn-outline"><i class="fas fa-phone"></i> Call Now</a>
      </div>
    </div>

    <div class="hero-right fade-up">
      <div class="card-flip-wrapper" title="Hover to flip">
        <div class="card-flip-inner">
          <div class="card-face card-front">
            <img src="visiting-card.jpg.jpeg" alt="Swarupananda Ghosh - Land Surveyor Visiting Card">
          </div>
          <div class="card-face card-back">
            <div class="back-logo">SG Survey</div>
            <p>Licensed Land Surveyor, Bankura</p>
            <div class="back-contact">
              <span><i class="fas fa-phone"></i><?= htmlspecialchars($phone) ?></span>
              <span><i class="fas fa-phone"></i><?= htmlspecialchars($phone2) ?></span>
              <span><i class="fas fa-envelope"></i><?= htmlspecialchars($email) ?></span>
            </div>
          </div>
        </div>
      </div>
      <div class="hero-badge"><span>12+</span>Years<br>Expert</div>
    </div>
  </div>

  <div class="scroll-indicator">
    <div class="scroll-line"></div>
    <span>SCROLL</span>
  </div>
</section>

<!-- STATS STRIP -->
<div class="stats-strip">
  <div class="stats-inner">
    <div class="stat-item fade-up">
      <div class="stat-number" data-target="500">0</div>
      <div class="stat-label">Surveys Completed</div>
    </div>
    <div class="stat-item fade-up">
      <div class="stat-number" data-target="12">0</div>
      <div class="stat-label">Years Experience</div>
    </div>
    <div class="stat-item fade-up">
      <div class="stat-number" data-target="4">0</div>
      <div class="stat-label">Service Types</div>
    </div>
    <div class="stat-item fade-up">
      <div class="stat-number" data-target="100">0</div>
      <div class="stat-label">Client Satisfaction %</div>
    </div>
  </div>
</div>

<!-- SERVICES -->
<section class="services" id="services">
  <div class="section-label">What We Offer</div>
  <h2 class="section-title">Our Survey Services</h2>
  <p class="section-subtitle">Hover each card to reveal pricing and details</p>

  <div class="services-grid">

    <div class="service-card fade-up">
      <div class="service-card-inner">
        <div class="service-front">
          <div class="service-icon"><i class="fas fa-map"></i></div>
          <h3>Land Survey</h3>
          <p>Traditional boundary surveys for legal documentation and property demarcation.</p>
        </div>
        <div class="service-back">
          <h3>Land Survey</h3>
          <div class="service-price">₹<?= number_format((int)$chargeLand) ?><small> /survey</small></div>
          <ul>
            <li>Boundary demarcation</li>
            <li>Legal documentation</li>
            <li>Plot verification</li>
            <li>Official stamp &amp; seal</li>
          </ul>
        </div>
      </div>
    </div>

    <div class="service-card fade-up">
      <div class="service-card-inner">
        <div class="service-front">
          <div class="service-icon"><i class="fas fa-satellite"></i></div>
          <h3>Digital Land Survey</h3>
          <p>High-precision GPS-aided digital measurements with georeferenced output.</p>
        </div>
        <div class="service-back">
          <h3>Digital Survey</h3>
          <div class="service-price">₹<?= number_format((int)$chargeDigital) ?><small> /survey</small></div>
          <ul>
            <li>GPS precision</li>
            <li>Digital maps</li>
            <li>Georeferenced data</li>
            <li>Fast turnaround</li>
          </ul>
        </div>
      </div>
    </div>

    <div class="service-card fade-up">
      <div class="service-card-inner">
        <div class="service-front">
          <div class="service-icon"><i class="fas fa-drafting-compass"></i></div>
          <h3>AutoCAD Plot Sketch</h3>
          <p>Technical CAD drawings and plot sketches for construction and legal use.</p>
        </div>
        <div class="service-back">
          <h3>AutoCAD Sketch</h3>
          <div class="service-price">₹<?= number_format((int)$chargeAutocad) ?><small> /sketch</small></div>
          <ul>
            <li>CAD drawings</li>
            <li>Scale-accurate plans</li>
            <li>DWG/PDF delivery</li>
            <li>Revision included</li>
          </ul>
        </div>
      </div>
    </div>

    <div class="service-card fade-up">
      <div class="service-card-inner">
        <div class="service-front">
          <div class="service-icon"><i class="fas fa-crosshairs"></i></div>
          <h3>Laser Range Survey</h3>
          <p>Long-distance precision measurements using laser rangefinder technology.</p>
        </div>
        <div class="service-back">
          <h3>Laser Survey</h3>
          <div class="service-price">₹<?= number_format((int)$chargeLaser) ?><small> /survey</small></div>
          <ul>
            <li>Long-range accuracy</li>
            <li>Large property support</li>
            <li>3D point cloud data</li>
            <li>Detailed report</li>
          </ul>
        </div>
      </div>
    </div>

  </div>
</section>

<!-- ABOUT + SLIDER -->
<section class="about-section" id="about">
  <div class="about-inner">
    <div class="slider-wrapper fade-up">
      <div class="slider-track" id="sliderTrack">
        <div class="slide"><img src="https://images.unsplash.com/photo-1589939705384-5185137a7f0f?w=700&q=80" alt="Land surveying field work"></div>
        <div class="slide"><img src="https://images.unsplash.com/photo-1503387762-592deb58ef4e?w=700&q=80" alt="Construction surveying"></div>
        <div class="slide"><img src="https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?w=700&q=80" alt="Professional survey equipment"></div>
      </div>
      <div class="slider-dots" id="sliderDots">
        <div class="dot active"></div>
        <div class="dot"></div>
        <div class="dot"></div>
      </div>
      <div class="slider-controls">
        <button class="slider-btn" id="prevBtn"><i class="fas fa-chevron-left"></i></button>
        <button class="slider-btn" id="nextBtn"><i class="fas fa-chevron-right"></i></button>
      </div>
    </div>

    <div class="about-right fade-up">
      <div class="section-label">About Us</div>
      <h2 class="section-title">What is<br>Land Survey?</h2>
      <p class="lead">Precision measurements that define your land.</p>
      <p>Land surveying is the science of measuring and mapping boundaries, elevations, and spatial relationships of land. It creates the legal foundation for property ownership, construction, and infrastructure development.</p>

      <div class="survey-points">
        <div class="survey-point">
          <i class="fas fa-check-circle"></i>
          <div>
            <h4>Boundary Accuracy</h4>
            <p>Precise boundary identification prevents future legal disputes.</p>
          </div>
        </div>
        <div class="survey-point">
          <i class="fas fa-file-alt"></i>
          <div>
            <h4>Legal Documentation</h4>
            <p>Official certified reports accepted by courts and government bodies.</p>
          </div>
        </div>
        <div class="survey-point">
          <i class="fas fa-tools"></i>
          <div>
            <h4>Modern Equipment</h4>
            <p>GPS, laser rangefinders and AutoCAD for maximum precision.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- TESTIMONIALS -->
<section class="testimonials">
  <div class="section-label">Client Feedback</div>
  <h2 class="section-title">What Clients Say</h2>
  <p class="section-subtitle">Trusted by hundreds of property owners across Bankura district</p>

  <div class="testimonials-grid">
    <div class="testimonial-card fade-up">
      <div class="stars">★★★★★</div>
      <p>"Swarupananda sir completed our property boundary survey with exceptional accuracy. The report was ready in 2 days and fully accepted by the local authority."</p>
      <div class="testimonial-author">
        <div class="author-avatar">R</div>
        <div>
          <div class="author-name">Rajesh Kumar</div>
          <div class="author-location"><i class="fas fa-map-marker-alt" style="color:var(--accent);margin-right:4px;"></i>Bankura Town</div>
        </div>
      </div>
    </div>
    <div class="testimonial-card fade-up">
      <div class="stars">★★★★★</div>
      <p>"The digital land survey was extremely precise. Got a georeferenced map with full documentation. Highly professional service at a fair price."</p>
      <div class="testimonial-author">
        <div class="author-avatar">P</div>
        <div>
          <div class="author-name">Priya Sharma</div>
          <div class="author-location"><i class="fas fa-map-marker-alt" style="color:var(--accent);margin-right:4px;"></i>Bishnupur</div>
        </div>
      </div>
    </div>
    <div class="testimonial-card fade-up">
      <div class="stars">★★★★★</div>
      <p>"The AutoCAD plot sketch was exactly what we needed for the construction permit. Clean drawings, properly scaled. Will use again for the next plot."</p>
      <div class="testimonial-author">
        <div class="author-avatar">A</div>
        <div>
          <div class="author-name">Amit Patel</div>
          <div class="author-location"><i class="fas fa-map-marker-alt" style="color:var(--accent);margin-right:4px;"></i>Saltora</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- BOOKING FORM -->
<section id="contact">
  <div class="contact-inner">

    <div class="contact-info-card fade-up">
      <div class="section-label">Get In Touch</div>
      <h2 class="section-title">Let's Start<br>Your Survey</h2>
      <p class="lead">Fill the form or reach us directly. We respond within 24 hours.</p>

      <div class="info-items">
        <div class="info-item">
          <div class="info-icon"><i class="fas fa-phone"></i></div>
          <div>
            <div class="info-label">Phone</div>
            <div class="info-value"><?= htmlspecialchars($phone) ?></div>
            <div class="info-value" style="font-size:0.85rem;color:var(--text-muted)"><?= htmlspecialchars($phone2) ?></div>
          </div>
        </div>
        <div class="info-item">
          <div class="info-icon"><i class="fas fa-envelope"></i></div>
          <div>
            <div class="info-label">Email</div>
            <div class="info-value"><?= htmlspecialchars($email) ?></div>
          </div>
        </div>
        <div class="info-item">
          <div class="info-icon"><i class="fas fa-map-marker-alt"></i></div>
          <div>
            <div class="info-label">Location</div>
            <div class="info-value"><?= htmlspecialchars($location) ?></div>
          </div>
        </div>
      </div>
    </div>

    <div class="form-card fade-up">
      <h3>Book a Survey</h3>
      <form id="bookingForm" action="book.php" method="POST">
        <div class="form-row">
          <div class="form-group">
            <label for="name">Full Name</label>
            <input type="text" id="name" name="name" placeholder="Your full name" required>
          </div>
          <div class="form-group">
            <label for="phone">Phone Number</label>
            <input type="tel" id="phone" name="phone" placeholder="10-digit mobile" maxlength="10" required>
          </div>
        </div>

        <div class="form-group">
          <label for="location">Survey Location</label>
          <input type="text" id="location" name="location" placeholder="Village / Town / District" required>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="type">Survey Type</label>
            <select id="type" name="type" required>
              <option value="">Select service</option>
              <option value="Land Survey">Land Survey</option>
              <option value="Digital Land Survey">Digital Land Survey</option>
              <option value="AutoCAD Plot Sketch">AutoCAD Plot Sketch</option>
              <option value="Laser Range Finder Survey">Laser Range Survey</option>
            </select>
          </div>
          <div class="form-group">
            <label for="date">Preferred Date</label>
            <input type="date" id="date" name="date" required>
          </div>
        </div>

        <div class="form-group">
          <label for="message">Additional Details</label>
          <textarea id="message" name="message" placeholder="Any specific requirements or notes..."></textarea>
        </div>

        <div class="form-actions">
          <button type="submit" class="btn btn-primary"><i class="fas fa-calendar-check"></i> Submit Booking</button>
          <button type="button" class="btn btn-outline" onclick="payNow()"><i class="fas fa-rupee-sign"></i> Pay Advance</button>
        </div>

        <div class="form-message" id="formMessage"></div>
      </form>
    </div>

  </div>
</section>

<!-- FOOTER -->
<footer>
  <div class="footer-inner">
    <div class="footer-logo">SG Survey</div>
    <p class="footer-copy">© <?= date('Y') ?> Swarupananda Ghosh. All rights reserved.</p>
    <div class="footer-links">
      <a href="#home">Home</a>
      <a href="#services">Services</a>
      <a href="#contact">Contact</a>
      <a href="admin-login.html">Admin</a>
    </div>
  </div>
</footer>

<!-- WHATSAPP FLOAT -->
<a href="https://wa.me/91<?= htmlspecialchars($phone) ?>" class="whatsapp-float" target="_blank" title="Chat on WhatsApp">
  <i class="fab fa-whatsapp"></i>
</a>

<script src="script.js"></script>
</body>
</html>
