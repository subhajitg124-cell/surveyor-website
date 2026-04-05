<?php
session_start();

if (!isset($_SESSION['booking_data'])) {
    header('Location: index.php');
    exit;
}

$b = $_SESSION['booking_data'];
unset($_SESSION['booking_data']);

$typeIcons = [
    'Land Survey'     => 'fa-map',
    'Digital Survey'  => 'fa-laptop-code',
    'AutoCAD Sketch'  => 'fa-drafting-compass',
    'Laser Survey'    => 'fa-crosshairs',
];
$icon = $typeIcons[$b['survey_type']] ?? 'fa-clipboard-check';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Booking Confirmed — SG Survey</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
*{margin:0;padding:0;box-sizing:border-box;}
:root{
  --bg:#0a0c12;
  --card:#12151e;
  --card2:#1a1e2a;
  --border:#ffffff10;
  --accent:#c9a84c;
  --accent2:#e8c96e;
  --green:#22c55e;
  --text:#e8eaf0;
  --muted:#7a8099;
}
body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:24px;overflow-x:hidden;}

/* Background orbs */
.orb{position:fixed;border-radius:50%;filter:blur(100px);pointer-events:none;z-index:0;}
.orb1{width:500px;height:500px;background:rgba(201,168,76,.07);top:-100px;right:-100px;}
.orb2{width:400px;height:400px;background:rgba(34,197,94,.06);bottom:-80px;left:-80px;}

/* Wrapper */
.wrapper{position:relative;z-index:1;width:100%;max-width:560px;}

/* Success animation */
.success-ring{width:90px;height:90px;margin:0 auto 24px;position:relative;}
.ring-svg{width:90px;height:90px;transform:rotate(-90deg);}
.ring-bg{fill:none;stroke:var(--border);stroke-width:4;}
.ring-fill{fill:none;stroke:var(--green);stroke-width:4;stroke-linecap:round;stroke-dasharray:251.2;stroke-dashoffset:251.2;animation:drawRing 1s ease forwards .3s;}
@keyframes drawRing{to{stroke-dashoffset:0;}}
.check-icon{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font-size:32px;color:var(--green);opacity:0;animation:popIn .3s ease forwards 1.1s;}
@keyframes popIn{from{transform:scale(.5);opacity:0;}to{transform:scale(1);opacity:1;}}

/* Card */
.card{background:var(--card);border:1px solid var(--border);border-radius:24px;padding:36px 32px;text-align:center;animation:slideUp .5s ease;}
@keyframes slideUp{from{opacity:0;transform:translateY(30px);}to{opacity:1;transform:translateY(0);}}

.confirmed-label{font-size:11px;letter-spacing:3px;text-transform:uppercase;color:var(--green);margin-bottom:10px;display:flex;align-items:center;justify-content:center;gap:6px;}
.card h1{font-family:'Playfair Display',serif;font-size:28px;margin-bottom:8px;}
.card h1 span{color:var(--accent);}
.card .sub{font-size:14px;color:var(--muted);margin-bottom:28px;line-height:1.6;}

/* Booking ID pill */
.booking-id{display:inline-flex;align-items:center;gap:8px;background:rgba(201,168,76,.1);border:1px solid rgba(201,168,76,.2);color:var(--accent);padding:7px 16px;border-radius:30px;font-size:13px;font-weight:600;margin-bottom:28px;}

/* Details */
.details{background:var(--card2);border:1px solid var(--border);border-radius:16px;text-align:left;overflow:hidden;margin-bottom:24px;}
.detail-head{padding:14px 18px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:8px;font-size:13px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:1px;}
.detail-head i{color:var(--accent);}
.detail-row{display:flex;align-items:flex-start;gap:14px;padding:14px 18px;border-bottom:1px solid var(--border);transition:.2s;}
.detail-row:last-child{border-bottom:none;}
.detail-row:hover{background:rgba(255,255,255,.02);}
.detail-icon{width:34px;height:34px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:14px;flex-shrink:0;margin-top:1px;}
.di-gold{background:rgba(201,168,76,.12);color:var(--accent);}
.di-green{background:rgba(34,197,94,.12);color:var(--green);}
.di-blue{background:rgba(59,130,246,.12);color:#3b82f6;}
.di-purple{background:rgba(168,85,247,.12);color:#a855f7;}
.di-orange{background:rgba(245,158,11,.12);color:#f59e0b;}
.detail-content{}
.detail-label{font-size:10px;color:var(--muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:3px;}
.detail-value{font-size:14px;font-weight:500;}

/* Message box */
.msg-box{background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:10px;padding:12px 14px;font-size:13px;color:var(--muted);font-style:italic;margin-top:4px;line-height:1.5;}

/* Status pill */
.status-pill{display:inline-flex;align-items:center;gap:6px;background:rgba(245,158,11,.12);border:1px solid rgba(245,158,11,.2);color:#f59e0b;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600;}
.status-dot{width:6px;height:6px;border-radius:50%;background:#f59e0b;animation:pulse 1.5s ease infinite;}
@keyframes pulse{0%,100%{opacity:1;}50%{opacity:.3;}}

/* Actions */
.actions{display:flex;flex-direction:column;gap:10px;}
.btn{display:flex;align-items:center;justify-content:center;gap:8px;padding:13px 20px;border-radius:12px;border:none;font-size:14px;font-weight:600;cursor:pointer;transition:.2s;font-family:'Inter',sans-serif;text-decoration:none;}
.btn-wa{background:linear-gradient(135deg,#25D366,#128C7E);color:white;}
.btn-wa:hover{opacity:.9;transform:translateY(-1px);}
.btn-home{background:var(--card2);color:var(--text);border:1px solid var(--border);}
.btn-home:hover{background:var(--border);}

/* Countdown */
.countdown{font-size:12px;color:var(--muted);margin-top:14px;text-align:center;}
.countdown span{color:var(--accent);font-weight:600;}

@media(max-width:480px){
  .card{padding:24px 18px;}
  .card h1{font-size:22px;}
}
</style>
</head>
<body>
<div class="orb orb1"></div>
<div class="orb orb2"></div>

<div class="wrapper">
  <div class="card">
    <!-- Success ring -->
    <div class="success-ring">
      <svg class="ring-svg" viewBox="0 0 90 90">
        <circle class="ring-bg" cx="45" cy="45" r="40"/>
        <circle class="ring-fill" cx="45" cy="45" r="40"/>
      </svg>
      <div class="check-icon"><i class="fas fa-check"></i></div>
    </div>

    <div class="confirmed-label"><i class="fas fa-circle" style="font-size:7px;"></i> Booking Confirmed</div>
    <h1>Thank You, <span><?= htmlspecialchars(explode(' ', $b['name'])[0]) ?>!</span></h1>
    <p class="sub">Your survey request has been received. We'll get back to you shortly to confirm the details.</p>

    <div class="booking-id"><i class="fas fa-hashtag"></i> Booking ID: <?= $b['id'] ?></div>

    <!-- Details card -->
    <div class="details">
      <div class="detail-head"><i class="fas fa-clipboard-list"></i> Your Booking Summary</div>

      <div class="detail-row">
        <div class="detail-icon di-gold"><i class="fas fa-user"></i></div>
        <div class="detail-content">
          <div class="detail-label">Full Name</div>
          <div class="detail-value"><?= htmlspecialchars($b['name']) ?></div>
        </div>
      </div>

      <div class="detail-row">
        <div class="detail-icon di-blue"><i class="fas fa-phone"></i></div>
        <div class="detail-content">
          <div class="detail-label">Phone Number</div>
          <div class="detail-value"><?= htmlspecialchars($b['phone']) ?></div>
        </div>
      </div>

      <div class="detail-row">
        <div class="detail-icon di-purple"><i class="fas fa-map-marker-alt"></i></div>
        <div class="detail-content">
          <div class="detail-label">Survey Location</div>
          <div class="detail-value"><?= htmlspecialchars($b['location']) ?></div>
        </div>
      </div>

      <div class="detail-row">
        <div class="detail-icon di-green"><i class="fas <?= $icon ?>"></i></div>
        <div class="detail-content">
          <div class="detail-label">Survey Type</div>
          <div class="detail-value"><?= htmlspecialchars($b['survey_type']) ?></div>
        </div>
      </div>

      <div class="detail-row">
        <div class="detail-icon di-orange"><i class="fas fa-calendar-alt"></i></div>
        <div class="detail-content">
          <div class="detail-label">Preferred Date</div>
          <div class="detail-value"><?= htmlspecialchars($b['preferred_date']) ?></div>
        </div>
      </div>

      <?php if (!empty($b['message'])): ?>
      <div class="detail-row">
        <div class="detail-icon di-gold"><i class="fas fa-comment-dots"></i></div>
        <div class="detail-content">
          <div class="detail-label">Your Message</div>
          <div class="msg-box">"<?= htmlspecialchars($b['message']) ?>"</div>
        </div>
      </div>
      <?php endif; ?>

      <div class="detail-row">
        <div class="detail-icon" style="background:rgba(245,158,11,.12);"><i class="fas fa-hourglass-half" style="color:#f59e0b;"></i></div>
        <div class="detail-content">
          <div class="detail-label">Current Status</div>
          <div style="margin-top:4px;"><span class="status-pill"><span class="status-dot"></span>Pending Review</span></div>
        </div>
      </div>
    </div>

    <!-- Actions -->
    <div class="actions">
      <a class="btn btn-wa" href="https://wa.me/91<?= htmlspecialchars($b['phone']) ?>?text=<?= urlencode('Hello! I just booked a ' . $b['survey_type'] . ' with SG Survey for ' . $b['preferred_date'] . '. My booking ID is #' . $b['id'] . '. Please confirm my appointment.') ?>" target="_blank">
        <i class="fab fa-whatsapp" style="font-size:18px;"></i> Send via WhatsApp
      </a>
      <a class="btn btn-home" href="index.php">
        <i class="fas fa-arrow-left"></i> Back to Home
      </a>
    </div>

    <div class="countdown">Redirecting to home in <span id="timer">10</span>s</div>
  </div>
</div>

<script>
let t = 10;
const el = document.getElementById('timer');
const interval = setInterval(() => {
  t--;
  el.textContent = t;
  if (t <= 0) { clearInterval(interval); window.location.href = 'index.php'; }
}, 1000);
</script>
</body>
</html>
