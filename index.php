<?php
require_once 'db.php';
$pdo = getDBConnection();

$siteDataRows = $pdo->query("SELECT data_key, data_value FROM site_data")->fetchAll();
$siteData = [];
foreach ($siteDataRows as $row) {
    $siteData[$row['data_key']] = $row['data_value'];
}

$phone = $siteData['phone_primary'] ?? '9475465392';
$email = $siteData['email'] ?? 'swarupanandaghosh2@gmail.com';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Swarupananda Ghosh - Professional Land Surveyor</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

</head>

<body>

<!-- NAVBAR -->
<nav class="navbar">
  <div class="logo">SG Survey</div>
</nav>

<!-- HERO -->
<section class="hero">
  <div class="hero-container">

    <div class="hero-left">
      <h1>Swarupananda Ghosh</h1>
      <h3>Licensed Land Surveyor</h3>

      <p><i class="fas fa-map-marker-alt"></i> Kochkunda, Shitla, Bankura</p>

      <div class="hero-contact">
        <p><i class="fas fa-phone"></i> <?= $phone ?></p>
        <p><i class="fas fa-envelope"></i> <?= $email ?></p>
      </div>

      <div class="btn-group">
        <a href="#contact" class="btn">Book Survey</a>
        <a href="tel:<?= $phone ?>" class="btn btn-outline">Call Now</a>
      </div>
    </div>

    <div class="hero-right">
      <img src="visiting-card.jpg.jpeg" alt="Visiting Card" class="floating-card">
    </div>

  </div>
</section>

<!-- IMAGE SLIDER -->
<section class="slider">
  <div class="slides">
    <img src="https://images.unsplash.com/photo-1589939705384-5185137a7f0f" class="active">
    <img src="https://images.unsplash.com/photo-1503387762-592deb58ef4e">
    <img src="https://images.unsplash.com/photo-1454165804606-c3d57bc86b40">
  </div>
</section>
<button id="themeToggle">🌙</button>
<!-- SERVICES -->
<section class="services">
  <h2>Our Services</h2>

  <div class="cards">
    <div class="card">Land Survey</div>
    <div class="card">Digital Land Survey</div>
    <div class="card">AutoCAD Plot Sketch</div>
    <div class="card">Laser Range Finder Survey</div>
  </div>
</section>

<!-- ABOUT -->
<section class="about">
  <h2>What is Land Survey?</h2>
  <p>
    Land surveying is the science of measuring land boundaries, ensuring accuracy,
    and creating legal documentation for construction and ownership.
  </p>
</section>

<!-- STATS -->
<section class="stats">
  <div class="stat">
    <h3>12+</h3>
    <p>Years Experience</p>
  </div>
  <div class="stat">
    <h3>500+</h3>
    <p>Surveys Completed</p>
  </div>
</section>

<!-- TESTIMONIAL -->
<section class="testimonials">
  <h2>Client Feedback</h2>
  <p>"Highly professional and accurate survey work."</p>
</section>

<!-- CONTACT -->
<section id="contact">
  <h2>Book a Survey</h2>

  <form action="book.php" method="POST">
    <input name="name" placeholder="Your Name" required>
    <input name="phone" placeholder="Phone" required>
    <input name="location" placeholder="Location" required>

    <select name="type">
      <option>Land Survey</option>
      <option>Digital Survey</option>
      <option>AutoCAD</option>
    </select>

    <input type="date" name="date" required>
    <textarea name="message" placeholder="Additional details"></textarea>

    <button type="submit">Submit Booking</button>
    <button type="button" onclick="payNow()">Pay Advance</button>
  </form>

  <div class="contact-info">
    <p>📞 <?= $phone ?></p>
    <p>📧 <?= $email ?></p>
  </div>
</section>

<!-- FOOTER -->
<footer>
  <p>© Swarupananda Ghosh</p>
  <p><?= $phone ?> | <?= $email ?></p>
</footer>

<!-- WHATSAPP FLOAT -->
<a href="https://wa.me/91<?= $phone ?>" class="whatsapp-float">💬</a>

<script src="script.js"></script>

</body>
</html>