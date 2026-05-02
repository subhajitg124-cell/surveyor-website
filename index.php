<?php
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
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
$activeTheme   = $siteData['active_theme'] ?? 'normal';
$upiId         = $siteData['upi_id'] ?? '9064560741@upi';
$adsensePubId  = trim($siteData['adsense_publisher_id'] ?? '');

// Fetch active, non-expired notices
$noticesStmt = $pdo->query("SELECT * FROM notices WHERE is_active=1 AND (expires_at IS NULL OR expires_at >= date('now')) ORDER BY created_at DESC");
$activeNotices = $noticesStmt ? $noticesStmt->fetchAll() : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="SG Survey — Professional precision land surveying services in Bankura by Swarupananda Ghosh. GPS surveys, AutoCAD sketches, laser range surveys with 30+ years of expertise.">
<meta name="theme-color" content="#06090f">
<title>SG Survey — Professional Land Surveying Services</title>

<link rel="icon" type="image/png" sizes="32x32" href="favicon-32.png">
<link rel="icon" type="image/png" sizes="192x192" href="favicon-192.png">
<link rel="apple-touch-icon" sizes="180x180" href="apple-touch-icon.png">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;0,900;1,400&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="style.css">
<?php if($adsensePubId): ?>
<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=<?= htmlspecialchars($adsensePubId) ?>" crossorigin="anonymous"></script>
<?php endif; ?>
</head>
<body class="theme-<?= htmlspecialchars($activeTheme) ?>">

<!-- WATER RIPPLE CANVAS (interactive water surface) -->
<canvas id="waterCanvas"></canvas>

<!-- BACKGROUND ORBS -->
<div class="bg-orbs">
  <div class="orb orb-1"></div>
  <div class="orb orb-2"></div>
  <div class="orb orb-3"></div>
</div>

<!-- DESKTOP-ONLY: Floating surveyor-themed background icons (react to cursor) -->
<div class="floating-bg" aria-hidden="true">
  <i class="float-icon fb-1  fas fa-compass"></i>
  <i class="float-icon fb-2  fas fa-ruler-combined"></i>
  <i class="float-icon fb-3  fas fa-map-marked-alt"></i>
  <i class="float-icon fb-4  fas fa-drafting-compass"></i>
  <i class="float-icon fb-5  fas fa-vector-square"></i>
  <i class="float-icon fb-6  fas fa-mountain"></i>
  <i class="float-icon fb-7  fas fa-map"></i>
  <i class="float-icon fb-8  fas fa-location-dot"></i>
  <i class="float-icon fb-9  fas fa-satellite-dish"></i>
  <i class="float-icon fb-10 fas fa-route"></i>
  <i class="float-icon fb-11 fas fa-layer-group"></i>
  <i class="float-icon fb-12 fas fa-globe-asia"></i>
</div>

<!-- MOBILE-ONLY ANIMATED BACKGROUND -->
<div class="mobile-bg" aria-hidden="true">
  <div class="m-blob b1"></div>
  <div class="m-blob b2"></div>
  <div class="m-blob b3"></div>
  <span class="m-shape s1"></span>
  <span class="m-shape s2"></span>
  <span class="m-shape s3"></span>
  <span class="m-shape s4"></span>
  <span class="m-shape s5"></span>
</div>

<!-- ANIMATED WATER WAVES BACKGROUND -->
<div class="water-waves-bg">
  <svg class="water-wave water-wave-1" viewBox="0 0 1440 320" preserveAspectRatio="none">
    <path d="M0,160 C320,280 640,40 960,160 C1280,280 1440,100 1440,160 L1440,320 L0,320 Z"></path>
  </svg>
  <svg class="water-wave water-wave-2" viewBox="0 0 1440 320" preserveAspectRatio="none">
    <path d="M0,192 C360,100 720,280 1080,192 C1260,140 1440,220 1440,192 L1440,320 L0,320 Z"></path>
  </svg>
  <svg class="water-wave water-wave-3" viewBox="0 0 1440 320" preserveAspectRatio="none">
    <path d="M0,224 C240,320 480,128 720,224 C960,320 1200,128 1440,224 L1440,320 L0,320 Z"></path>
  </svg>
</div>

<!-- LOADER -->
<div id="loader">
  <img src="logo-icon.png" alt="SG Survey" class="loader-icon">
  <div class="loader-logo">SG SURVEY</div>
  <div class="loader-bar"><div class="loader-bar-fill"></div></div>
  <div class="loader-drops">
    <span class="loader-drop"></span>
    <span class="loader-drop"></span>
    <span class="loader-drop"></span>
  </div>
</div>

<?php if (!empty($activeNotices)): ?>
<?php $n = $activeNotices[0]; $typeIcons = ['info'=>'fa-info-circle','offer'=>'fa-tag','event'=>'fa-star','alert'=>'fa-exclamation-triangle','update'=>'fa-check-circle']; ?>
<div class="notice-banner notice-<?= $n['type'] ?>" id="noticeBanner">
  <div class="notice-banner-inner">
    <?php if(count($activeNotices) > 1): ?>
    <div class="notice-ticker" id="noticeTicker">
      <?php foreach($activeNotices as $ni): ?>
      <div class="notice-tick-item">
        <i class="fas <?= $typeIcons[$ni['type']] ?? 'fa-bell' ?>"></i>
        <strong><?= htmlspecialchars($ni['title']) ?></strong>
        — <?= htmlspecialchars($ni['message']) ?>
      </div>
      <?php endforeach; ?>
    </div>
    <?php else: ?>
    <span class="notice-single">
      <i class="fas <?= $typeIcons[$n['type']] ?? 'fa-bell' ?>"></i>
      <strong><?= htmlspecialchars($n['title']) ?></strong>
      — <?= htmlspecialchars($n['message']) ?>
    </span>
    <?php endif; ?>
  </div>
  <button class="notice-close" onclick="this.closest('.notice-banner').style.display='none'" aria-label="Close">×</button>
</div>
<?php endif; ?>

<!-- NAVBAR -->
<nav class="navbar" id="navbar">
  <a href="#" class="nav-logo"><img src="logo.png" alt="SG Survey Logo" class="logo-img"> <span>Survey</span></a>
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
        <div class="pill water-hover"><?= '<i class="fas fa-phone"></i>' . htmlspecialchars($phone) ?></div>
        <div class="pill water-hover"><?= '<i class="fas fa-envelope"></i>' . htmlspecialchars($email) ?></div>
        <div class="pill water-hover"><?= '<i class="fas fa-map-marker-alt"></i>' . htmlspecialchars($location) ?></div>
      </div>

      <div class="btn-group">
        <a href="#contact" class="btn btn-primary water-hover"><i class="fas fa-calendar-check"></i> Book a Survey</a>
        <a href="tel:<?= htmlspecialchars($phone) ?>" class="btn btn-outline water-hover"><i class="fas fa-phone"></i> Call Now</a>
      </div>
    </div>

    <div class="hero-right fade-up">
      <div class="card-flip-wrapper water-hover" title="Hover to flip">
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
      <div class="hero-badge"><span>30+</span>Years<br>Expert</div>
    </div>
  </div>

  <div class="scroll-indicator">
    <div class="scroll-line"></div>
    <span>SCROLL</span>
  </div>
</section>

<!-- WAVE DIVIDER -->
<div class="wave-divider">
  <svg viewBox="0 0 1440 120" preserveAspectRatio="none">
    <path d="M0,40 C240,120 480,0 720,60 C960,120 1200,20 1440,80 L1440,120 L0,120 Z" class="wave-fill"></path>
    <path d="M0,60 C360,0 720,120 1080,40 C1260,0 1440,60 1440,40 L1440,120 L0,120 Z" class="wave-fill wave-fill-2"></path>
  </svg>
</div>

<!-- STATS STRIP -->
<div class="stats-strip">
  <div class="stats-inner">
    <div class="stat-item water-hover fade-up">
      <div class="stat-number" data-target="3000">0</div>
      <div class="stat-label">Surveys Completed</div>
    </div>
    <div class="stat-item water-hover fade-up">
      <div class="stat-number" data-target="30">0</div>
      <div class="stat-label">Years Experience</div>
    </div>
    <div class="stat-item water-hover fade-up">
      <div class="stat-number" data-target="4">0</div>
      <div class="stat-label">Service Types</div>
    </div>
    <div class="stat-item water-hover fade-up">
      <div class="stat-number" data-target="100">0</div>
      <div class="stat-label">Client Satisfaction %</div>
    </div>
  </div>
</div>

<!-- WAVE DIVIDER -->
<div class="wave-divider wave-divider-flip">
  <svg viewBox="0 0 1440 120" preserveAspectRatio="none">
    <path d="M0,40 C240,120 480,0 720,60 C960,120 1200,20 1440,80 L1440,120 L0,120 Z" class="wave-fill"></path>
  </svg>
</div>

<!-- SERVICES -->
<section class="services" id="services">
  <div class="section-label">What We Offer</div>
  <h2 class="section-title">Our Survey Services</h2>
  <p class="section-subtitle">Hover each card to reveal pricing and details</p>

  <div class="services-grid">

    <div class="service-card water-hover fade-up">
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

    <div class="service-card water-hover fade-up">
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

    <div class="service-card water-hover fade-up">
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

    <div class="service-card water-hover fade-up">
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

<!-- WAVE DIVIDER -->
<div class="wave-divider">
  <svg viewBox="0 0 1440 120" preserveAspectRatio="none">
    <path d="M0,80 C360,20 720,100 1080,40 C1260,10 1440,60 1440,40 L1440,120 L0,120 Z" class="wave-fill"></path>
    <path d="M0,60 C200,100 500,20 800,70 C1100,120 1300,30 1440,60 L1440,120 L0,120 Z" class="wave-fill wave-fill-2"></path>
  </svg>
</div>

<!-- ABOUT + SLIDER -->
<section class="about-section" id="about">
  <div class="about-inner">
    <div class="slider-wrapper water-hover fade-up">
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
        <div class="survey-point water-hover">
          <i class="fas fa-check-circle"></i>
          <div>
            <h4>Boundary Accuracy</h4>
            <p>Precise boundary identification prevents future legal disputes.</p>
          </div>
        </div>
        <div class="survey-point water-hover">
          <i class="fas fa-file-alt"></i>
          <div>
            <h4>Legal Documentation</h4>
            <p>Official certified reports accepted by courts and government bodies.</p>
          </div>
        </div>
        <div class="survey-point water-hover">
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

<!-- WAVE DIVIDER -->
<div class="wave-divider wave-divider-flip">
  <svg viewBox="0 0 1440 120" preserveAspectRatio="none">
    <path d="M0,60 C340,120 680,0 1020,80 C1200,120 1440,40 1440,60 L1440,120 L0,120 Z" class="wave-fill"></path>
  </svg>
</div>

<!-- TESTIMONIALS -->
<section class="testimonials">
  <div class="section-label">Client Feedback</div>
  <h2 class="section-title">What Clients Say</h2>
  <p class="section-subtitle">Trusted by hundreds of property owners across Bankura district</p>

  <div class="testimonials-grid">
    <div class="testimonial-card water-hover fade-up">
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
    <div class="testimonial-card water-hover fade-up">
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
    <div class="testimonial-card water-hover fade-up">
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

<!-- WAVE DIVIDER -->
<div class="wave-divider">
  <svg viewBox="0 0 1440 120" preserveAspectRatio="none">
    <path d="M0,40 C240,120 480,0 720,60 C960,120 1200,20 1440,80 L1440,120 L0,120 Z" class="wave-fill"></path>
    <path d="M0,80 C300,20 600,100 900,50 C1200,0 1350,80 1440,50 L1440,120 L0,120 Z" class="wave-fill wave-fill-2"></path>
  </svg>
</div>

<!-- BOOKING FORM -->
<section id="contact">
  <div class="contact-inner">

    <div class="contact-info-card water-hover fade-up">
      <div class="section-label">Get In Touch</div>
      <h2 class="section-title">Let's Start<br>Your Survey</h2>
      <p class="lead">Fill the form or reach us directly. We respond within 24 hours.</p>

      <div class="info-items">
        <div class="info-item water-hover">
          <div class="info-icon"><i class="fas fa-phone"></i></div>
          <div>
            <div class="info-label">Phone</div>
            <div class="info-value"><?= htmlspecialchars($phone) ?></div>
            <div class="info-value" style="font-size:0.85rem;color:var(--text-muted)"><?= htmlspecialchars($phone2) ?></div>
          </div>
        </div>
        <div class="info-item water-hover">
          <div class="info-icon"><i class="fas fa-envelope"></i></div>
          <div>
            <div class="info-label">Email</div>
            <div class="info-value"><?= htmlspecialchars($email) ?></div>
          </div>
        </div>
        <div class="info-item water-hover">
          <div class="info-icon"><i class="fas fa-map-marker-alt"></i></div>
          <div>
            <div class="info-label">Location</div>
            <div class="info-value"><?= htmlspecialchars($location) ?></div>
          </div>
        </div>
      </div>
    </div>

    <div class="form-card water-hover fade-up">
      <h3>Book a Survey</h3>
      <form id="bookingForm" action="book.php" method="POST">
        <div class="form-row">
          <div class="form-group">
            <label for="name">Full Name</label>
            <input type="text" id="name" name="name" placeholder="Your full name" required class="water-hover">
          </div>
          <div class="form-group">
            <label for="phone">Phone Number</label>
            <input type="tel" id="phone" name="phone" placeholder="10-digit mobile" maxlength="10" required class="water-hover">
          </div>
        </div>

        <div class="form-group">
          <label for="location">Survey Location</label>
          <input type="text" id="location" name="location" placeholder="Village / Town / District" required class="water-hover">
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="type">Survey Type</label>
            <select id="type" name="type" required class="water-hover">
              <option value="">Select service</option>
              <option value="Land Survey">Land Survey</option>
              <option value="Digital Land Survey">Digital Land Survey</option>
              <option value="AutoCAD Plot Sketch">AutoCAD Plot Sketch</option>
              <option value="Laser Range Finder Survey">Laser Range Survey</option>
            </select>
          </div>
          <div class="form-group">
            <label for="date">Preferred Date</label>
            <input type="date" id="date" name="date" required class="water-hover">
          </div>
        </div>

        <div class="form-group">
          <label for="message">Additional Details</label>
          <textarea id="message" name="message" placeholder="Any specific requirements or notes..." class="water-hover"></textarea>
        </div>

        <div class="form-actions">
          <button type="submit" class="btn btn-primary water-hover"><i class="fas fa-calendar-check"></i> Submit Booking</button>
          <button type="button" class="btn btn-outline water-hover" onclick="payNow()"><i class="fas fa-rupee-sign"></i> Pay Advance</button>
        </div>

        <div class="form-message" id="formMessage"></div>
      </form>
    </div>

  </div>
</section>

<?php if($adsensePubId): ?>
<!-- AD SLOT — above footer -->
<section style="padding:20px 0;text-align:center;">
  <ins class="adsbygoogle"
       style="display:block"
       data-ad-client="<?= htmlspecialchars($adsensePubId) ?>"
       data-ad-slot="auto"
       data-ad-format="auto"
       data-full-width-responsive="true"></ins>
  <script>(adsbygoogle = window.adsbygoogle || []).push({});</script>
</section>
<?php endif; ?>

<!-- UPI SUPPORT SECTION -->
<?php if($upiId): ?>
<section class="upi-support-section">
  <div class="upi-support-inner">
    <div class="upi-support-text">
      <div class="upi-support-icon"><i class="fas fa-heart"></i></div>
      <h3>Support This Website</h3>
      <p>SG Survey is a free service. If you find it useful, a small contribution helps us keep it running and improve it.</p>
      <a href="upi://pay?pa=<?= urlencode($upiId) ?>&pn=SG+Survey&cu=INR" class="upi-pay-btn">
        <i class="fas fa-rupee-sign"></i> Pay via UPI
      </a>
      <div class="upi-id-label">UPI ID: <span><?= htmlspecialchars($upiId) ?></span></div>
    </div>
    <div class="upi-qr-wrap">
      <img src="https://api.qrserver.com/v1/create-qr-code/?size=160x160&data=<?= urlencode('upi://pay?pa='.$upiId.'&pn=SG+Survey&cu=INR') ?>" alt="Scan to Pay" loading="lazy">
      <div class="upi-qr-label">Scan to Pay</div>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- FOOTER -->
<footer>
  <div class="footer-wave">
    <svg viewBox="0 0 1440 80" preserveAspectRatio="none">
      <path d="M0,20 C240,60 480,0 720,30 C960,60 1200,10 1440,40 L1440,80 L0,80 Z" class="wave-fill"></path>
    </svg>
  </div>
  <div class="footer-inner">
    <div class="footer-logo">SG Survey</div>
    <p class="footer-copy">© <?= date('Y') ?> Swarupananda Ghosh. All rights reserved.</p>
    <div class="footer-links">
      <a href="#home">Home</a>
      <a href="#services">Services</a>
      <a href="#contact">Contact</a>
      <a href="admin-login.html" class="admin-link"><i class="fas fa-lock"></i> Admin</a>
    </div>
  </div>
</footer>

<!-- WHATSAPP FLOAT -->
<a href="https://wa.me/91<?= htmlspecialchars($phone) ?>" class="whatsapp-float" target="_blank" title="Chat on WhatsApp">
  <i class="fab fa-whatsapp"></i>
</a>

<!-- BACK TO TOP -->
<button class="back-to-top" id="backToTop" title="Back to top" aria-label="Back to top">
  <i class="fas fa-arrow-up"></i>
</button>

<!-- SCROLL PROGRESS BAR -->
<div class="scroll-progress" id="scrollProgress"></div>

<script src="script.js"></script>

<!-- EVENT THEME PARTICLES -->
<canvas id="eventCanvas" style="position:fixed;inset:0;pointer-events:none;z-index:1;"></canvas>
<script>
(function(){
  const body  = document.body;
  const theme = [...body.classList].find(c => c.startsWith('theme-'))?.replace('theme-','') || 'normal';
  if (theme === 'normal') return;

  const canvas = document.getElementById('eventCanvas');
  const ctx    = canvas.getContext('2d');
  let W, H, particles = [], animId;

  function resize() {
    W = canvas.width  = window.innerWidth;
    H = canvas.height = window.innerHeight;
  }
  window.addEventListener('resize', resize);
  resize();

  /* ── Particle configs per theme ── */
  const configs = {
    independence_day: {
      count: 80,
      colors: ['#FF9933','#FFFFFF','#138808','#FF9933','#ffffff'],
      shapes: ['rect'],
      size: () => Math.random()*7+3,
      speed: () => Math.random()*1.5+0.8,
      drift: () => (Math.random()-0.5)*1.2,
      spin: true,
      emoji: null
    },
    new_year: {
      count: 60,
      colors: ['#c9a84c','#ffffff','#e8c96e','#a0a0ff','#ffeb3b'],
      shapes: ['star'],
      size: () => Math.random()*10+5,
      speed: () => Math.random()*1+0.5,
      drift: () => (Math.random()-0.5)*0.8,
      spin: true,
      fireworks: true,
      emoji: null
    },
    diwali: {
      count: 55,
      colors: ['#FF8C00','#FFD700','#FF4500','#FFA500','#FFEC8B'],
      shapes: ['circle','star'],
      size: () => Math.random()*8+3,
      speed: () => Math.random()*1.2+0.6,
      drift: () => (Math.random()-0.5)*1,
      glow: true,
      emoji: '🪔'
    },
    eid: {
      count: 45,
      colors: ['#00b478','#c9a84c','#ffffff','#88ffcc'],
      shapes: ['star','circle'],
      size: () => Math.random()*6+2,
      speed: () => Math.random()*0.8+0.3,
      drift: () => (Math.random()-0.5)*0.6,
      twinkle: true,
      emoji: '🌙'
    },
    offer: {
      count: 50,
      colors: ['#ef4444','#c9a84c','#ffffff','#ff8800'],
      shapes: ['rect','star'],
      size: () => Math.random()*9+4,
      speed: () => Math.random()*2+1,
      drift: () => (Math.random()-0.5)*1.5,
      emoji: '🏷️'
    },
    christmas: {
      count: 90, colors: ['#ffffff','#e8f4fd','#cce8ff'], shapes: ['circle'],
      size: ()=>Math.random()*6+2, speed: ()=>Math.random()*0.9+0.3, drift: ()=>(Math.random()-0.5)*0.5, snow:true
    },
    republic_day: {
      count: 70, colors: ['#FF9933','#FFFFFF','#138808','#1E40AF','#c9a84c'], shapes: ['rect'],
      size: ()=>Math.random()*7+3, speed: ()=>Math.random()*1.4+0.7, drift: ()=>(Math.random()-0.5)*1, spin:true
    },
    valentine: {
      count: 55, colors: ['#ec4899','#f43f5e','#fb7185','#fda4af','#ffffff'], shapes: ['heart'],
      size: ()=>Math.random()*14+6, speed: ()=>Math.random()*1.2+0.5, drift: ()=>(Math.random()-0.5)*0.8, spin:false
    },
    holi: {
      count: 100, colors: ['#ff0080','#ff6600','#ffdd00','#00cc44','#0088ff','#cc00ff','#ff3399'], shapes: ['circle','rect'],
      size: ()=>Math.random()*12+4, speed: ()=>Math.random()*2+0.8, drift: ()=>(Math.random()-0.5)*2, spin:true, glow:true
    },
    easter: {
      count: 50, colors: ['#a7f3d0','#c4b5fd','#fde68a','#fbcfe8','#bfdbfe'], shapes: ['circle'],
      size: ()=>Math.random()*10+5, speed: ()=>Math.random()*0.7+0.3, drift: ()=>(Math.random()-0.5)*0.5, twinkle:true
    },
    lohri: {
      count: 65, colors: ['#ff4500','#ff8c00','#ffdd00','#ff6600','#fff700'], shapes: ['circle','star'],
      size: ()=>Math.random()*8+3, speed: ()=>-(Math.random()*2+1), drift: ()=>(Math.random()-0.5)*1.5, glow:true, fire:true
    },
    makar_sankranti: {
      count: 40, colors: ['#f59e0b','#facc15','#38bdf8','#fb923c','#86efac'], shapes: ['kite'],
      size: ()=>Math.random()*16+8, speed: ()=>Math.random()*1.2+0.5, drift: ()=>(Math.random()-0.5)*1.2, spin:false
    },
    baisakhi: {
      count: 80, colors: ['#eab308','#facc15','#f59e0b','#ffffff','#4ade80'], shapes: ['rect','star'],
      size: ()=>Math.random()*8+3, speed: ()=>Math.random()*1.8+0.8, drift: ()=>(Math.random()-0.5)*1.4, spin:true
    },
    navratri: {
      count: 75, colors: ['#d946ef','#f43f5e','#f97316','#facc15','#4ade80','#38bdf8','#818cf8'], shapes: ['circle','rect'],
      size: ()=>Math.random()*9+3, speed: ()=>Math.random()*1.5+0.8, drift: ()=>(Math.random()-0.5)*1.5, spin:true, glow:true
    },
    ganesh_chaturthi: {
      count: 60, colors: ['#f97316','#fb923c','#fbbf24','#c9a84c','#fde68a'], shapes: ['star','circle'],
      size: ()=>Math.random()*10+4, speed: ()=>-(Math.random()*1.8+0.5), drift: ()=>(Math.random()-0.5)*1.2, glow:true, fire:true
    },
    onam: {
      count: 60, colors: ['#22c55e','#4ade80','#eab308','#facc15','#f9a8d4','#fb923c'], shapes: ['petal'],
      size: ()=>Math.random()*12+5, speed: ()=>Math.random()*1+0.5, drift: ()=>(Math.random()-0.5)*0.8, spin:true
    },
    raksha_bandhan: {
      count: 50, colors: ['#f59e0b','#fbbf24','#ec4899','#f9a8d4','#ffffff'], shapes: ['heart','star'],
      size: ()=>Math.random()*10+4, speed: ()=>Math.random()*1+0.4, drift: ()=>(Math.random()-0.5)*0.8, spin:false, twinkle:true
    },
    halloween: {
      count: 45, colors: ['#f97316','#fb923c','#c026d3','#a855f7','#ffffff'], shapes: ['bat','circle'],
      size: ()=>Math.random()*12+6, speed: ()=>Math.random()*1.2+0.5, drift: ()=>(Math.random()-0.5)*1.5, spin:false
    },
    thanksgiving: {
      count: 55, colors: ['#b45309','#d97706','#f59e0b','#92400e','#dc2626','#854d0e'], shapes: ['leaf'],
      size: ()=>Math.random()*14+6, speed: ()=>Math.random()*1.5+0.6, drift: ()=>(Math.random()-0.5)*1.8, spin:true
    }
  };

  const cfg = configs[theme];
  if (!cfg) return;

  function drawStar(x, y, r, color, alpha) {
    ctx.save();
    ctx.globalAlpha = alpha;
    ctx.fillStyle = color;
    ctx.beginPath();
    for (let i = 0; i < 5; i++) {
      const a = (i * 4 * Math.PI / 5) - Math.PI / 2;
      const ia = a + 2 * Math.PI / 5;
      i === 0 ? ctx.moveTo(x + r*Math.cos(a), y + r*Math.sin(a))
              : ctx.lineTo(x + r*Math.cos(a), y + r*Math.sin(a));
      ctx.lineTo(x + (r*0.4)*Math.cos(ia), y + (r*0.4)*Math.sin(ia));
    }
    ctx.closePath();
    ctx.fill();
    ctx.restore();
  }

  function createParticle() {
    return {
      x: Math.random() * W,
      y: Math.random() * -H,
      size: cfg.size(),
      color: cfg.colors[Math.floor(Math.random()*cfg.colors.length)],
      shape: cfg.shapes[Math.floor(Math.random()*cfg.shapes.length)],
      speed: cfg.speed(),
      drift: cfg.drift(),
      angle: Math.random() * Math.PI * 2,
      spin: (Math.random()-0.5) * 0.05,
      alpha: Math.random()*0.6 + 0.4,
      twinkleDir: Math.random() > 0.5 ? 1 : -1
    };
  }

  for (let i = 0; i < cfg.count; i++) {
    const p = createParticle();
    p.y = Math.random() * H;
    particles.push(p);
  }

  // Fireworks bursts for New Year
  let fireworks = [];
  if (cfg.fireworks) {
    setInterval(() => {
      if (fireworks.length > 6) return;
      const fx = Math.random() * W * 0.8 + W * 0.1;
      const fy = Math.random() * H * 0.5 + 50;
      const fc = cfg.colors[Math.floor(Math.random()*cfg.colors.length)];
      for (let i = 0; i < 20; i++) {
        const a = (i / 20) * Math.PI * 2;
        fireworks.push({ x:fx, y:fy, vx: Math.cos(a)*3*(Math.random()+0.5), vy: Math.sin(a)*3*(Math.random()+0.5), color:fc, alpha:1, life:60 });
      }
    }, 1200);
  }

  function draw() {
    ctx.clearRect(0, 0, W, H);

    // Falling particles
    particles.forEach((p, i) => {
      if (cfg.twinkle) p.alpha += 0.02 * p.twinkleDir;
      if (p.alpha > 1 || p.alpha < 0.1) p.twinkleDir *= -1;

      if (cfg.glow) {
        ctx.shadowBlur  = 12;
        ctx.shadowColor = p.color;
      }

      ctx.save();
      ctx.globalAlpha = Math.max(0, p.alpha);
      ctx.translate(p.x, p.y);
      if (cfg.spin) ctx.rotate(p.angle);

      ctx.fillStyle = p.color;
      if (p.shape === 'rect') {
        ctx.fillRect(-p.size/2, -p.size/4, p.size, p.size/2);
      } else if (p.shape === 'circle') {
        ctx.beginPath(); ctx.arc(0,0,p.size/2,0,Math.PI*2); ctx.fill();
      } else if (p.shape === 'heart') {
        const s = p.size/2;
        ctx.beginPath();
        ctx.moveTo(0, s*0.3);
        ctx.bezierCurveTo(-s*1.2,-s*0.6,-s*2,s*0.5,0,s*1.4);
        ctx.bezierCurveTo(s*2,s*0.5,s*1.2,-s*0.6,0,s*0.3);
        ctx.fill();
      } else if (p.shape === 'star') {
        ctx.restore();
        drawStar(p.x, p.y, p.size/2, p.color, p.alpha);
        ctx.save(); ctx.translate(p.x, p.y);
      } else if (p.shape === 'bat') {
        const s = p.size/2;
        ctx.beginPath();
        ctx.ellipse(-s, 0, s, s*0.4, 0, 0, Math.PI*2);
        ctx.ellipse(s, 0, s, s*0.4, 0, 0, Math.PI*2);
        ctx.ellipse(0, s*0.2, s*0.3, s*0.4, 0, 0, Math.PI*2);
        ctx.fill();
      } else if (p.shape === 'leaf') {
        const s = p.size/2;
        ctx.beginPath();
        ctx.moveTo(0,-s); ctx.quadraticCurveTo(s,-s*0.2,0,s);
        ctx.quadraticCurveTo(-s,-s*0.2,0,-s);
        ctx.fill();
      } else if (p.shape === 'kite') {
        const s = p.size/2;
        ctx.beginPath();
        ctx.moveTo(0,-s); ctx.lineTo(s*0.6,0);
        ctx.lineTo(0,s); ctx.lineTo(-s*0.6,0);
        ctx.closePath(); ctx.fill();
      } else if (p.shape === 'petal') {
        const s = p.size/2;
        ctx.beginPath();
        ctx.ellipse(0,-s*0.4,s*0.4,s,0,0,Math.PI*2);
        ctx.fill();
      }
      ctx.restore();
      ctx.shadowBlur = 0;

      p.y += p.speed;
      p.x += p.drift;
      p.angle += p.spin || 0;

      // Fire: sparks go up, reset at bottom
      if (cfg.fire && p.speed < 0) {
        if (p.y < -20) { p.y = H + 10; p.x = Math.random()*W; p.alpha = Math.random()*0.7+0.3; }
      } else if (p.y > H + 20) {
        particles[i] = createParticle();
      }
    });

    // Fireworks
    fireworks = fireworks.filter(f => f.life > 0);
    fireworks.forEach(f => {
      ctx.beginPath();
      ctx.arc(f.x, f.y, 3, 0, Math.PI*2);
      ctx.fillStyle = f.color;
      ctx.globalAlpha = f.alpha;
      ctx.fill();
      ctx.globalAlpha = 1;
      f.x += f.vx; f.y += f.vy;
      f.vy += 0.08;
      f.alpha -= 1/f.life;
      f.life--;
    });

    animId = requestAnimationFrame(draw);
  }

  draw();

  // Pause when tab hidden
  document.addEventListener('visibilitychange', () => {
    if (document.hidden) cancelAnimationFrame(animId);
    else draw();
  });
})();
</script>
</body>
</html>
