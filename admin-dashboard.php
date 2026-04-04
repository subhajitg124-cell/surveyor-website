<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin-login.html');
    exit;
}

require_once 'db.php';
$pdo = getDBConnection();

if (!$pdo) {
    die('Database connection failed');
}

// Stats
$statsQueries = [
    'total_bookings' => "SELECT COUNT(*) as count FROM bookings",
    'pending_bookings' => "SELECT COUNT(*) as count FROM bookings WHERE status = 'pending'",
    'confirmed_bookings' => "SELECT COUNT(*) as count FROM bookings WHERE status = 'confirmed'",
    'completed_bookings' => "SELECT COUNT(*) as count FROM bookings WHERE status = 'completed'"
];

$stats = [];
foreach ($statsQueries as $key => $query) {
    $result = $pdo->query($query)->fetch();
    $stats[$key] = $result['count'];
}

$bookings = $pdo->query("SELECT * FROM bookings ORDER BY created_at DESC LIMIT 50")->fetchAll();

$siteData = $pdo->query("SELECT * FROM site_data")->fetchAll();
$siteDataArray = [];
foreach ($siteData as $data) {
    $siteDataArray[$data['data_key']] = $data['data_value'];
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Admin Dashboard</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;600&display=swap" rel="stylesheet">

<style>

/* ===== LOADER ===== */
#loader {
  position: fixed;
  width: 100%;
  height: 100%;
  background: linear-gradient(135deg,#0a4d68,#05c3de);
  display: flex;
  justify-content: center;
  align-items: center;
  color: white;
  font-size: 24px;
  z-index: 9999;
}

/* ===== BASE ===== */
body {
  margin:0;
  font-family:Poppins;
  background:#f5f6fa;
  transition:0.3s;
}

/* DARK MODE */
body.dark {
  background:#111;
  color:white;
}

body.dark .top-bar,
body.dark .content-section,
body.dark .stat-card {
  background:#1e1e1e;
}

/* HEADER */
.top-bar {
  background:white;
  padding:20px;
  display:flex;
  justify-content:space-between;
  align-items:center;
  border-radius:10px;
  margin:20px;
}

/* GRID */
.stats-grid {
  display:grid;
  grid-template-columns:repeat(auto-fit,minmax(200px,1fr));
  gap:20px;
  margin:20px;
}

/* CARD */
.stat-card {
  background:white;
  padding:20px;
  border-radius:10px;
  transition:0.3s;
}

.stat-card:hover {
  transform:translateY(-5px);
}

/* SECTION */
.content-section {
  background:white;
  margin:20px;
  padding:20px;
  border-radius:10px;
  backdrop-filter:blur(10px);
}

/* TABLE */
table {
  width:100%;
  border-collapse:collapse;
}

th,td {
  padding:10px;
}

tr:hover {
  background:#eee;
}

/* STATUS */
.status-badge {
  padding:5px 10px;
  border-radius:20px;
}

.status-pending {background:orange;}
.status-confirmed {background:blue;}
.status-completed {background:green;}
.status-cancelled {background:red;}

/* BUTTON */
button {
  cursor:pointer;
  padding:6px 10px;
  border:none;
  border-radius:5px;
}

/* ANIMATION */
.main-content {
  animation:fadeIn 0.8s ease;
}

@keyframes fadeIn {
  from {opacity:0; transform:translateY(20px);}
  to {opacity:1;}
}

</style>
</head>

<body>

<div id="loader">Loading Dashboard...</div>

<div class="top-bar">
  <h2>Admin Dashboard</h2>
  <div>
    <button onclick="logout()">Logout</button>
    <button id="themeToggle">🌙</button>
  </div>
</div>

<div class="stats-grid">
  <div class="stat-card"><h3><?= $stats['total_bookings'] ?></h3>Total</div>
  <div class="stat-card"><h3><?= $stats['pending_bookings'] ?></h3>Pending</div>
  <div class="stat-card"><h3><?= $stats['confirmed_bookings'] ?></h3>Confirmed</div>
  <div class="stat-card"><h3><?= $stats['completed_bookings'] ?></h3>Completed</div>
</div>

<div class="content-section">
<h3>Bookings</h3>

<table>
<tr>
<th>ID</th>
<th>Name</th>
<th>Phone</th>
<th>Status</th>
</tr>

<?php foreach($bookings as $b): ?>
<tr>
<td><?= $b['id'] ?></td>
<td><?= $b['name'] ?></td>
<td><?= $b['phone'] ?></td>
<td><span class="status-badge status-<?= $b['status'] ?>">
<?= $b['status'] ?></span></td>
</tr>
<?php endforeach; ?>

</table>

</div>

<script>

// LOADER
window.addEventListener("load", () => {
  document.getElementById("loader").style.display = "none";
});

// DARK MODE
const toggle = document.getElementById("themeToggle");

toggle.onclick = () => {
  document.body.classList.toggle("dark");

  if(document.body.classList.contains("dark")){
    localStorage.setItem("theme","dark");
  } else {
    localStorage.setItem("theme","light");
  }
};

window.onload = () => {
  if(localStorage.getItem("theme") === "dark"){
    document.body.classList.add("dark");
  }
};

// LOGOUT
function logout(){
  window.location.href = "logout.php";
}

// COUNTER
document.querySelectorAll('.stat-card h3').forEach(counter => {
  let target = +counter.innerText;
  let count = 0;
  let step = target/30;

  function update(){
    if(count < target){
      count += step;
      counter.innerText = Math.floor(count);
      requestAnimationFrame(update);
    } else {
      counter.innerText = target;
    }
  }
  update();
});

</script>

</body>
</html>