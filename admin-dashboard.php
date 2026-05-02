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

// Handle status update via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    header('Content-Type: application/json');
    $id = intval($_POST['id']);
    $status = in_array($_POST['status'], ['pending','confirmed','completed','cancelled']) ? $_POST['status'] : 'pending';
    $stmt = $pdo->prepare("UPDATE bookings SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
    $stmt->execute([$status, $id]);
    echo json_encode(['success' => true]);
    exit;
}

// Handle DELETE booking via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_booking') {
    header('Content-Type: application/json');
    $id = intval($_POST['id']);
    if ($id <= 0) { echo json_encode(['success' => false, 'error' => 'invalid_id']); exit; }
    try {
        $stmt = $pdo->prepare("DELETE FROM bookings WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true, 'deleted' => $stmt->rowCount()]);
    } catch (\Throwable $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// Handle notice actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_notice') {
    header('Content-Type: application/json');
    $title   = trim($_POST['title'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $type    = in_array($_POST['type'] ?? '', ['info','offer','event','alert','update']) ? $_POST['type'] : 'info';
    $expires = !empty($_POST['expires_at']) ? $_POST['expires_at'] : null;
    if (!$title || !$message) { echo json_encode(['success'=>false,'error'=>'Title and message required']); exit; }
    $stmt = $pdo->prepare("INSERT INTO notices (title,message,type,is_active,expires_at) VALUES (?,?,?,1,?)");
    $stmt->execute([$title,$message,$type,$expires]);
    echo json_encode(['success'=>true,'id'=>$pdo->lastInsertId()]);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle_notice') {
    header('Content-Type: application/json');
    $id = intval($_POST['id']);
    $pdo->prepare("UPDATE notices SET is_active = CASE WHEN is_active=1 THEN 0 ELSE 1 END WHERE id=?")->execute([$id]);
    $row = $pdo->prepare("SELECT is_active FROM notices WHERE id=?");
    $row->execute([$id]);
    echo json_encode(['success'=>true,'is_active'=>(int)$row->fetch()['is_active']]);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_notice') {
    header('Content-Type: application/json');
    $id = intval($_POST['id']);
    $pdo->prepare("DELETE FROM notices WHERE id=?")->execute([$id]);
    echo json_encode(['success'=>true]);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_theme') {
    header('Content-Type: application/json');
    $theme = in_array($_POST['theme']??'',['normal','independence_day','new_year','diwali','eid','offer','christmas']) ? $_POST['theme'] : 'normal';
    $pdo->prepare("UPDATE site_data SET data_value=?, updated_at=CURRENT_TIMESTAMP WHERE data_key='active_theme'")->execute([$theme]);
    echo json_encode(['success'=>true]);
    exit;
}

// Handle save site data
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_site_data') {
    header('Content-Type: application/json');
    $keys = ['phone_primary','phone_secondary','email','location','charge_land_survey','charge_digital_survey','charge_autocad_sketch','charge_laser_survey'];
    foreach ($keys as $key) {
        if (isset($_POST[$key])) {
            $stmt = $pdo->prepare("UPDATE site_data SET data_value = ?, updated_at = CURRENT_TIMESTAMP WHERE data_key = ?");
            $stmt->execute([htmlspecialchars(trim($_POST[$key]), ENT_QUOTES, 'UTF-8'), $key]);
        }
    }
    echo json_encode(['success' => true]);
    exit;
}

// Stats
$stats = [];
foreach ([
    'total'    => "SELECT COUNT(*) as c FROM bookings",
    'pending'  => "SELECT COUNT(*) as c FROM bookings WHERE status='pending'",
    'confirmed'=> "SELECT COUNT(*) as c FROM bookings WHERE status='confirmed'",
    'completed'=> "SELECT COUNT(*) as c FROM bookings WHERE status='completed'",
    'cancelled'=> "SELECT COUNT(*) as c FROM bookings WHERE status='cancelled'",
] as $k => $q) {
    $stats[$k] = $pdo->query($q)->fetch()['c'];
}

$bookings = $pdo->query("SELECT * FROM bookings ORDER BY created_at DESC LIMIT 100")->fetchAll();

$siteRows = $pdo->query("SELECT data_key, data_value FROM site_data")->fetchAll();
$site = [];
foreach ($siteRows as $r) $site[$r['data_key']] = $r['data_value'];

$notices = $pdo->query("SELECT * FROM notices ORDER BY created_at DESC")->fetchAll();
$activeTheme = $site['active_theme'] ?? 'normal';
$adminName = $_SESSION['admin_name'] ?? 'Administrator';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SG Survey — Admin Panel</title>
<link rel="icon" type="image/png" sizes="32x32" href="favicon-32.png">
<link rel="apple-touch-icon" sizes="180x180" href="apple-touch-icon.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
*{margin:0;padding:0;box-sizing:border-box;}
:root{
  --bg:#0d0f14;
  --sidebar:#111318;
  --card:#161a22;
  --card2:#1c2130;
  --border:#ffffff12;
  --accent:#c9a84c;
  --accent2:#e8c96e;
  --text:#e8eaf0;
  --muted:#7a8099;
  --green:#22c55e;
  --blue:#3b82f6;
  --orange:#f59e0b;
  --red:#ef4444;
  --radius:14px;
}
body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;display:flex;overflow:hidden;}

/* ── LOADER ── */
#loader{position:fixed;inset:0;background:var(--bg);display:flex;flex-direction:column;align-items:center;justify-content:center;z-index:9999;transition:opacity .5s;}
#loader.hide{opacity:0;pointer-events:none;}
.loader-ring{width:64px;height:64px;border:3px solid var(--border);border-top-color:var(--accent);border-radius:50%;animation:spin 1s linear infinite;margin-bottom:20px;}
.loader-text{color:var(--accent);font-size:13px;letter-spacing:3px;text-transform:uppercase;}
@keyframes spin{to{transform:rotate(360deg);}}

/* ── SIDEBAR ── */
.sidebar{width:260px;min-height:100vh;background:var(--sidebar);border-right:1px solid var(--border);display:flex;flex-direction:column;position:fixed;left:0;top:0;bottom:0;z-index:100;transition:transform .3s;}
.sidebar-logo{padding:28px 24px 20px;border-bottom:1px solid var(--border);}
.sidebar-logo .logo-mark{font-size:22px;font-weight:700;color:var(--accent);letter-spacing:1px;}
.sidebar-logo .logo-sub{font-size:11px;color:var(--muted);letter-spacing:2px;text-transform:uppercase;margin-top:3px;}
.sidebar-admin{display:flex;align-items:center;gap:12px;padding:20px 24px;border-bottom:1px solid var(--border);}
.admin-avatar{width:40px;height:40px;background:linear-gradient(135deg,var(--accent),var(--accent2));border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:16px;color:#111;flex-shrink:0;}
.admin-name{font-size:13px;font-weight:600;color:var(--text);}
.admin-role{font-size:11px;color:var(--muted);}
.sidebar-nav{flex:1;padding:16px 12px;}
.nav-label{font-size:10px;letter-spacing:2px;color:var(--muted);text-transform:uppercase;padding:10px 12px 6px;}
.nav-item{display:flex;align-items:center;gap:12px;padding:11px 14px;border-radius:10px;cursor:pointer;transition:.2s;margin-bottom:2px;font-size:14px;color:var(--muted);}
.nav-item:hover{background:var(--card2);color:var(--text);}
.nav-item.active{background:linear-gradient(135deg,rgba(201,168,76,.18),rgba(201,168,76,.08));color:var(--accent);border:1px solid rgba(201,168,76,.2);}
.nav-item i{width:18px;text-align:center;font-size:15px;}
.sidebar-footer{padding:16px 12px;border-top:1px solid var(--border);}
.logout-btn{display:flex;align-items:center;gap:10px;padding:11px 14px;border-radius:10px;cursor:pointer;color:var(--red);font-size:14px;transition:.2s;}
.logout-btn:hover{background:rgba(239,68,68,.1);}

/* ── MAIN ── */
.main{margin-left:260px;flex:1;overflow-y:auto;min-height:100vh;}
.topbar{display:flex;align-items:center;justify-content:space-between;padding:20px 32px;border-bottom:1px solid var(--border);background:rgba(13,15,20,.8);backdrop-filter:blur(12px);position:sticky;top:0;z-index:50;}
.page-title{font-size:18px;font-weight:600;}
.page-title span{color:var(--accent);}
.topbar-right{display:flex;align-items:center;gap:12px;}
.topbar-date{font-size:12px;color:var(--muted);}
.hamburger{display:none;background:none;border:none;color:var(--text);font-size:20px;cursor:pointer;}

/* ── VIEWS ── */
.view{display:none;animation:fadeUp .4s ease;}
.view.active{display:block;}
@keyframes fadeUp{from{opacity:0;transform:translateY(16px);}to{opacity:1;transform:translateY(0);}}
.view-wrap{padding:28px 32px;}

/* ── STATS ── */
.stats-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:16px;margin-bottom:28px;}
.stat-card{background:var(--card);border:1px solid var(--border);border-radius:var(--radius);padding:22px;position:relative;overflow:hidden;transition:.3s;}
.stat-card::before{content:'';position:absolute;inset:0;background:linear-gradient(135deg,transparent,rgba(255,255,255,.02));pointer-events:none;}
.stat-card:hover{transform:translateY(-4px);border-color:rgba(201,168,76,.25);}
.stat-icon{width:44px;height:44px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:18px;margin-bottom:14px;}
.stat-value{font-size:32px;font-weight:700;line-height:1;}
.stat-label{font-size:12px;color:var(--muted);margin-top:6px;text-transform:uppercase;letter-spacing:1px;}
.stat-trend{font-size:11px;margin-top:8px;}
.ic-total{background:rgba(201,168,76,.15);color:var(--accent);}
.ic-pending{background:rgba(245,158,11,.15);color:var(--orange);}
.ic-confirmed{background:rgba(59,130,246,.15);color:var(--blue);}
.ic-completed{background:rgba(34,197,94,.15);color:var(--green);}
.ic-cancelled{background:rgba(239,68,68,.15);color:var(--red);}

/* ── SECTION CARD ── */
.section-card{background:var(--card);border:1px solid var(--border);border-radius:var(--radius);overflow:hidden;margin-bottom:24px;}
.section-head{display:flex;align-items:center;justify-content:space-between;padding:18px 22px;border-bottom:1px solid var(--border);}
.section-head h3{font-size:15px;font-weight:600;display:flex;align-items:center;gap:8px;}
.section-head h3 i{color:var(--accent);}
.section-body{padding:0;}

/* ── TABLE ── */
.tbl-wrap{overflow-x:auto;}
table{width:100%;border-collapse:collapse;}
th{padding:12px 16px;text-align:left;font-size:11px;text-transform:uppercase;letter-spacing:1px;color:var(--muted);border-bottom:1px solid var(--border);background:rgba(255,255,255,.02);font-weight:500;}
td{padding:13px 16px;font-size:13px;border-bottom:1px solid var(--border);vertical-align:top;}
tr:last-child td{border-bottom:none;}
tr.booking-row{transition:.2s;cursor:pointer;}
tr.booking-row:hover{background:var(--card2);}
.booking-name{font-weight:600;font-size:14px;margin-bottom:2px;}
.booking-meta{font-size:11px;color:var(--muted);}
.badge{display:inline-flex;align-items:center;gap:5px;padding:4px 10px;border-radius:20px;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;}
.badge-pending{background:rgba(245,158,11,.15);color:var(--orange);border:1px solid rgba(245,158,11,.25);}
.badge-confirmed{background:rgba(59,130,246,.15);color:var(--blue);border:1px solid rgba(59,130,246,.25);}
.badge-completed{background:rgba(34,197,94,.15);color:var(--green);border:1px solid rgba(34,197,94,.25);}
.badge-cancelled{background:rgba(239,68,68,.15);color:var(--red);border:1px solid rgba(239,68,68,.25);}
.status-select{background:transparent;border:none;color:inherit;font-size:11px;font-weight:600;cursor:pointer;outline:none;}

/* ── MODAL ── */
.modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.65);backdrop-filter:blur(6px);z-index:200;display:flex;align-items:center;justify-content:center;opacity:0;pointer-events:none;transition:.3s;}
.modal-overlay.open{opacity:1;pointer-events:all;}
.modal{background:var(--card);border:1px solid var(--border);border-radius:20px;width:min(560px,94vw);max-height:85vh;overflow-y:auto;transform:scale(.92) translateY(20px);transition:.3s;}
.modal-overlay.open .modal{transform:scale(1) translateY(0);}
.modal-head{display:flex;align-items:center;justify-content:space-between;padding:22px 24px;border-bottom:1px solid var(--border);}
.modal-head h3{font-size:16px;font-weight:600;}
.modal-close{background:var(--card2);border:none;color:var(--muted);width:32px;height:32px;border-radius:8px;cursor:pointer;font-size:16px;transition:.2s;display:flex;align-items:center;justify-content:center;}
.modal-close:hover{color:var(--text);background:var(--border);}
.modal-body{padding:22px 24px;}
.detail-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px;}
.detail-item{background:var(--card2);border:1px solid var(--border);border-radius:10px;padding:14px;}
.detail-item.full{grid-column:1/-1;}
.detail-label{font-size:10px;color:var(--muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:6px;}
.detail-value{font-size:14px;font-weight:500;}
.modal-actions{display:flex;gap:10px;margin-top:20px;flex-wrap:wrap;}
.btn{display:inline-flex;align-items:center;gap:7px;padding:10px 18px;border-radius:10px;border:none;font-size:13px;font-weight:600;cursor:pointer;transition:.2s;font-family:'Inter',sans-serif;}
.btn-accent{background:linear-gradient(135deg,var(--accent),var(--accent2));color:#111;}
.btn-accent:hover{opacity:.9;transform:translateY(-1px);}
.btn-ghost{background:var(--card2);color:var(--text);border:1px solid var(--border);}
.btn-ghost:hover{background:var(--border);}
.btn-danger{background:rgba(239,68,68,.15);color:var(--red);border:1px solid rgba(239,68,68,.2);}
.btn-success{background:rgba(34,197,94,.15);color:var(--green);border:1px solid rgba(34,197,94,.2);}
.btn-blue{background:rgba(59,130,246,.15);color:var(--blue);border:1px solid rgba(59,130,246,.2);}
.btn-danger:hover{background:rgba(239,68,68,.25);transform:translateY(-1px);}
.btn-icon-danger:hover{background:rgba(239,68,68,.18) !important;color:var(--red) !important;border-color:rgba(239,68,68,.3) !important;}

/* ── SETTINGS ── */
.settings-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;padding:22px;}
.form-group{display:flex;flex-direction:column;gap:6px;}
.form-group label{font-size:12px;color:var(--muted);text-transform:uppercase;letter-spacing:1px;font-weight:500;}
.form-input{background:var(--card2);border:1px solid var(--border);color:var(--text);padding:11px 14px;border-radius:10px;font-size:14px;font-family:'Inter',sans-serif;outline:none;transition:.2s;}
.form-input:focus{border-color:var(--accent);}
.settings-footer{padding:0 22px 22px;display:flex;gap:10px;}

/* ── NOTICES & THEMES ── */
.notice-form{padding:22px;border-bottom:1px solid var(--border);}
.notice-form-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;}
.notice-form-grid .full{grid-column:1/-1;}
.notice-list{padding:16px 22px;display:flex;flex-direction:column;gap:10px;}
.notice-item{background:var(--card2);border:1px solid var(--border);border-radius:12px;padding:14px 16px;display:flex;align-items:flex-start;gap:14px;transition:.2s;}
.notice-item.inactive{opacity:.45;}
.notice-dot{width:10px;height:10px;border-radius:50%;flex-shrink:0;margin-top:5px;}
.nt-info{background:#3b82f6;}.nt-offer{background:#c9a84c;}.nt-event{background:#a855f7;}.nt-alert{background:#ef4444;}.nt-update{background:#22c55e;}
.notice-content{flex:1;min-width:0;}
.notice-title{font-size:14px;font-weight:600;margin-bottom:3px;}
.notice-msg{font-size:12px;color:var(--muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.notice-meta{font-size:11px;color:var(--muted);margin-top:4px;}
.notice-actions{display:flex;gap:6px;flex-shrink:0;}
.toggle-btn{background:rgba(34,197,94,.12);color:var(--green);border:1px solid rgba(34,197,94,.2);width:30px;height:30px;border-radius:8px;cursor:pointer;font-size:12px;display:flex;align-items:center;justify-content:center;transition:.2s;}
.toggle-btn.off{background:rgba(255,255,255,.05);color:var(--muted);border-color:var(--border);}
.toggle-btn:hover{filter:brightness(1.2);}
.del-btn{background:rgba(239,68,68,.12);color:var(--red);border:1px solid rgba(239,68,68,.2);width:30px;height:30px;border-radius:8px;cursor:pointer;font-size:12px;display:flex;align-items:center;justify-content:center;transition:.2s;}
.del-btn:hover{background:rgba(239,68,68,.25);}
/* Theme Cards */
.theme-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:14px;padding:22px;}
.theme-card{border-radius:14px;padding:20px 14px;text-align:center;cursor:pointer;border:2px solid transparent;transition:.25s;position:relative;}
.theme-card:hover{transform:translateY(-3px);}
.theme-card.selected{border-color:var(--accent);box-shadow:0 0 0 3px rgba(201,168,76,.2);}
.theme-card .theme-icon{font-size:32px;margin-bottom:8px;}
.theme-card .theme-name{font-size:13px;font-weight:600;}
.theme-card .theme-desc{font-size:11px;opacity:.7;margin-top:3px;}
.theme-card .selected-check{position:absolute;top:8px;right:8px;background:var(--accent);color:#111;width:20px;height:20px;border-radius:50%;display:none;align-items:center;justify-content:center;font-size:10px;}
.theme-card.selected .selected-check{display:flex;}
.th-normal{background:rgba(201,168,76,.08);border-color:rgba(201,168,76,.15);}
.th-independence{background:linear-gradient(135deg,rgba(255,153,51,.12),rgba(19,136,8,.12));}
.th-newyear{background:linear-gradient(135deg,rgba(201,168,76,.15),rgba(100,80,200,.12));}
.th-diwali{background:linear-gradient(135deg,rgba(255,140,0,.15),rgba(201,168,76,.1));}
.th-eid{background:linear-gradient(135deg,rgba(0,180,120,.12),rgba(201,168,76,.1));}
.th-offer{background:linear-gradient(135deg,rgba(239,68,68,.12),rgba(201,168,76,.1));}
.th-christmas{background:linear-gradient(135deg,rgba(220,38,38,.12),rgba(34,197,94,.12));}

/* ── TOAST ── */
.toast{position:fixed;bottom:28px;right:28px;background:var(--card);border:1px solid var(--border);border-left:4px solid var(--green);padding:14px 20px;border-radius:12px;font-size:14px;z-index:999;transform:translateY(20px);opacity:0;transition:.3s;display:flex;align-items:center;gap:10px;box-shadow:0 8px 40px rgba(0,0,0,.4);}
.toast.show{transform:translateY(0);opacity:1;}
.toast i{color:var(--green);}

/* ── EMPTY ── */
.empty-state{text-align:center;padding:60px 20px;color:var(--muted);}
.empty-state i{font-size:48px;margin-bottom:16px;display:block;opacity:.3;}

/* ── SCROLLBAR ── */
::-webkit-scrollbar{width:5px;height:5px;}
::-webkit-scrollbar-track{background:transparent;}
::-webkit-scrollbar-thumb{background:var(--border);border-radius:10px;}

@media(max-width:768px){
  .sidebar{transform:translateX(-100%);}
  .sidebar.open{transform:translateX(0);}
  .main{margin-left:0;}
  .hamburger{display:block;}
  .view-wrap{padding:20px 16px;}
  .detail-grid{grid-template-columns:1fr;}
  .settings-grid{grid-template-columns:1fr;}
}
</style>
</head>
<body>

<div id="loader"><div class="loader-ring"></div><div class="loader-text">Loading Dashboard</div></div>

<!-- SIDEBAR -->
<aside class="sidebar" id="sidebar">
  <div class="sidebar-logo">
    <div class="logo-mark">SG Survey</div>
    <div class="logo-sub">Admin Panel</div>
  </div>
  <div class="sidebar-admin">
    <div class="admin-avatar"><?= strtoupper(substr($adminName, 0, 1)) ?></div>
    <div>
      <div class="admin-name"><?= htmlspecialchars($adminName) ?></div>
      <div class="admin-role">Administrator</div>
    </div>
  </div>
  <nav class="sidebar-nav">
    <div class="nav-label">Menu</div>
    <div class="nav-item active" onclick="switchView('dashboard', this)"><i class="fas fa-chart-pie"></i> Dashboard</div>
    <div class="nav-item" onclick="switchView('bookings', this)"><i class="fas fa-calendar-check"></i> Bookings <span id="pending-badge" style="margin-left:auto;background:rgba(245,158,11,.2);color:var(--orange);padding:2px 8px;border-radius:20px;font-size:11px;"><?= $stats['pending'] ?></span></div>
    <div class="nav-item" onclick="switchView('settings', this)"><i class="fas fa-sliders-h"></i> Settings</div>
    <div class="nav-item" onclick="switchView('notices', this)"><i class="fas fa-bullhorn"></i> Notices & Themes</div>
  </nav>
  <div class="sidebar-footer">
    <div class="logout-btn" onclick="confirmLogout()"><i class="fas fa-sign-out-alt"></i> Logout</div>
  </div>
</aside>

<!-- MAIN CONTENT -->
<main class="main">
  <div class="topbar">
    <div style="display:flex;align-items:center;gap:12px;">
      <button class="hamburger" id="hamburger"><i class="fas fa-bars"></i></button>
      <div class="page-title" id="pageTitle"><span>Dashboard</span></div>
    </div>
    <div class="topbar-right">
      <div class="topbar-date" id="currentDate"></div>
    </div>
  </div>

  <!-- DASHBOARD VIEW -->
  <div class="view active" id="view-dashboard">
    <div class="view-wrap">
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon ic-total"><i class="fas fa-layer-group"></i></div>
          <div class="stat-value" data-target="<?= $stats['total'] ?>">0</div>
          <div class="stat-label">Total Bookings</div>
        </div>
        <div class="stat-card">
          <div class="stat-icon ic-pending"><i class="fas fa-hourglass-half"></i></div>
          <div class="stat-value" data-target="<?= $stats['pending'] ?>">0</div>
          <div class="stat-label">Pending</div>
        </div>
        <div class="stat-card">
          <div class="stat-icon ic-confirmed"><i class="fas fa-check-circle"></i></div>
          <div class="stat-value" data-target="<?= $stats['confirmed'] ?>">0</div>
          <div class="stat-label">Confirmed</div>
        </div>
        <div class="stat-card">
          <div class="stat-icon ic-completed"><i class="fas fa-trophy"></i></div>
          <div class="stat-value" data-target="<?= $stats['completed'] ?>">0</div>
          <div class="stat-label">Completed</div>
        </div>
        <div class="stat-card">
          <div class="stat-icon ic-cancelled"><i class="fas fa-times-circle"></i></div>
          <div class="stat-value" data-target="<?= $stats['cancelled'] ?>">0</div>
          <div class="stat-label">Cancelled</div>
        </div>
      </div>

      <div class="section-card">
        <div class="section-head">
          <h3><i class="fas fa-clock"></i> Recent Bookings</h3>
          <button class="btn btn-ghost" onclick="switchView('bookings', document.querySelectorAll('.nav-item')[1])" style="padding:7px 14px;font-size:12px;">View All</button>
        </div>
        <div class="tbl-wrap">
          <table>
            <thead><tr><th>Client</th><th>Service</th><th>Date</th><th>Status</th></tr></thead>
            <tbody>
              <?php foreach(array_slice($bookings, 0, 5) as $b): ?>
              <tr class="booking-row" onclick="openModal(<?= htmlspecialchars(json_encode($b), ENT_QUOTES) ?>)">
                <td><div class="booking-name"><?= htmlspecialchars($b['name']) ?></div><div class="booking-meta"><i class="fas fa-phone" style="font-size:10px;margin-right:4px;"></i><?= htmlspecialchars($b['phone']) ?></div></td>
                <td><?= htmlspecialchars($b['survey_type']) ?></td>
                <td><?= htmlspecialchars($b['preferred_date']) ?></td>
                <td><span class="badge badge-<?= $b['status'] ?>"><?= ucfirst($b['status']) ?></span></td>
              </tr>
              <?php endforeach; ?>
              <?php if(empty($bookings)): ?>
              <tr><td colspan="4"><div class="empty-state"><i class="fas fa-inbox"></i>No bookings yet</div></td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- BOOKINGS VIEW -->
  <div class="view" id="view-bookings">
    <div class="view-wrap">
      <div class="section-card">
        <div class="section-head">
          <h3><i class="fas fa-calendar-check"></i> All Bookings</h3>
          <div style="display:flex;gap:8px;align-items:center;">
            <input id="searchInput" type="text" placeholder="Search..." class="form-input" style="padding:7px 12px;font-size:12px;width:180px;" oninput="filterTable()">
            <select id="filterStatus" class="form-input" style="padding:7px 12px;font-size:12px;width:130px;" onchange="filterTable()">
              <option value="">All Status</option>
              <option value="pending">Pending</option>
              <option value="confirmed">Confirmed</option>
              <option value="completed">Completed</option>
              <option value="cancelled">Cancelled</option>
            </select>
          </div>
        </div>
        <div class="tbl-wrap">
          <table>
            <thead><tr><th>#</th><th>Client</th><th>Location</th><th>Service</th><th>Date</th><th>Booked On</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody id="bookingsTable">
              <?php foreach($bookings as $b): ?>
              <tr class="booking-row" data-name="<?= strtolower(htmlspecialchars($b['name'])) ?>" data-status="<?= $b['status'] ?>" onclick="openModal(<?= htmlspecialchars(json_encode($b), ENT_QUOTES) ?>)">
                <td style="color:var(--muted);font-size:12px;"><?= $b['id'] ?></td>
                <td>
                  <div class="booking-name"><?= htmlspecialchars($b['name']) ?></div>
                  <div class="booking-meta"><i class="fas fa-phone" style="font-size:10px;margin-right:4px;"></i><?= htmlspecialchars($b['phone']) ?></div>
                </td>
                <td style="font-size:12px;color:var(--muted);"><?= htmlspecialchars($b['location']) ?></td>
                <td style="font-size:13px;"><?= htmlspecialchars($b['survey_type']) ?></td>
                <td style="font-size:12px;"><?= htmlspecialchars($b['preferred_date']) ?></td>
                <td style="font-size:11px;color:var(--muted);"><?= date('d M Y', strtotime($b['created_at'])) ?></td>
                <td><span class="badge badge-<?= $b['status'] ?>"><?= ucfirst($b['status']) ?></span></td>
                <td onclick="event.stopPropagation()">
                  <button class="btn btn-ghost" style="padding:5px 10px;font-size:11px;" title="View" onclick="openModal(<?= htmlspecialchars(json_encode($b), ENT_QUOTES) ?>)"><i class="fas fa-eye"></i></button>
                  <button class="btn btn-ghost btn-icon-danger" style="padding:5px 10px;font-size:11px;margin-left:4px;" title="Delete" onclick="deleteBookingRow(<?= (int)$b['id'] ?>)"><i class="fas fa-trash"></i></button>
                </td>
              </tr>
              <?php endforeach; ?>
              <?php if(empty($bookings)): ?>
              <tr><td colspan="8"><div class="empty-state"><i class="fas fa-inbox"></i>No bookings yet</div></td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- SETTINGS VIEW -->
  <div class="view" id="view-settings">
    <div class="view-wrap">
      <div class="section-card">
        <div class="section-head"><h3><i class="fas fa-sliders-h"></i> Site Settings</h3></div>
        <div class="settings-grid">
          <div class="form-group"><label>Primary Phone</label><input class="form-input" id="s_phone_primary" value="<?= htmlspecialchars($site['phone_primary'] ?? '') ?>"></div>
          <div class="form-group"><label>Secondary Phone</label><input class="form-input" id="s_phone_secondary" value="<?= htmlspecialchars($site['phone_secondary'] ?? '') ?>"></div>
          <div class="form-group"><label>Email</label><input class="form-input" id="s_email" value="<?= htmlspecialchars($site['email'] ?? '') ?>"></div>
          <div class="form-group"><label>Address</label><input class="form-input" id="s_location" value="<?= htmlspecialchars($site['location'] ?? '') ?>"></div>
        </div>
        <div class="section-head" style="margin-top:4px;"><h3><i class="fas fa-tags"></i> Service Pricing (₹)</h3></div>
        <div class="settings-grid">
          <div class="form-group"><label>Land Survey</label><input class="form-input" id="s_charge_land_survey" type="number" value="<?= htmlspecialchars($site['charge_land_survey'] ?? '') ?>"></div>
          <div class="form-group"><label>Digital Survey</label><input class="form-input" id="s_charge_digital_survey" type="number" value="<?= htmlspecialchars($site['charge_digital_survey'] ?? '') ?>"></div>
          <div class="form-group"><label>AutoCAD Sketch</label><input class="form-input" id="s_charge_autocad_sketch" type="number" value="<?= htmlspecialchars($site['charge_autocad_sketch'] ?? '') ?>"></div>
          <div class="form-group"><label>Laser Survey</label><input class="form-input" id="s_charge_laser_survey" type="number" value="<?= htmlspecialchars($site['charge_laser_survey'] ?? '') ?>"></div>
        </div>
        <div class="settings-footer">
          <button class="btn btn-accent" onclick="saveSettings()"><i class="fas fa-save"></i> Save Changes</button>
        </div>
      </div>
    </div>
  </div>

  <!-- NOTICES & THEMES VIEW -->
  <div class="view" id="view-notices">
    <div class="view-wrap">

      <!-- Add Notice Form -->
      <div class="section-card">
        <div class="section-head"><h3><i class="fas fa-plus-circle"></i> Post a Notice / Update</h3></div>
        <div class="notice-form">
          <div class="notice-form-grid">
            <div class="form-group full"><label>Notice Title</label><input class="form-input" id="n_title" placeholder="e.g. Special Discount — 20% Off This Week!"></div>
            <div class="form-group full"><label>Message</label><textarea class="form-input" id="n_message" rows="3" placeholder="Write the full notice or announcement here..." style="resize:vertical;"></textarea></div>
            <div class="form-group">
              <label>Type</label>
              <select class="form-input" id="n_type">
                <option value="info">ℹ️ Info — General Information</option>
                <option value="offer">🏷️ Offer — Special Discount / Deal</option>
                <option value="event">🎉 Event — Festival / Occasion</option>
                <option value="alert">🚨 Alert — Important Notice</option>
                <option value="update">✅ Update — New Service / Change</option>
              </select>
            </div>
            <div class="form-group">
              <label>Expires On (optional)</label>
              <input class="form-input" id="n_expires" type="date">
            </div>
          </div>
          <button class="btn btn-accent" onclick="addNotice()"><i class="fas fa-paper-plane"></i> Publish Notice</button>
        </div>
        <!-- Notice List -->
        <div style="padding:0 22px 6px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;">
          <span style="font-size:13px;color:var(--muted);">Published Notices</span>
          <span style="font-size:12px;color:var(--muted);"><?= count($notices) ?> total</span>
        </div>
        <div class="notice-list" id="noticeList">
          <?php if(empty($notices)): ?>
          <div class="empty-state" style="padding:30px 20px;"><i class="fas fa-bell-slash"></i>No notices yet. Post your first one above.</div>
          <?php endif; ?>
          <?php foreach($notices as $n):
            $expired = $n['expires_at'] && $n['expires_at'] < date('Y-m-d');
          ?>
          <div class="notice-item <?= !$n['is_active'] ? 'inactive' : '' ?>" id="notice-<?= $n['id'] ?>">
            <div class="notice-dot nt-<?= $n['type'] ?>"></div>
            <div class="notice-content">
              <div class="notice-title"><?= htmlspecialchars($n['title']) ?><?= $expired ? ' <span style="color:var(--red);font-size:10px;margin-left:6px;">[EXPIRED]</span>' : '' ?></div>
              <div class="notice-msg"><?= htmlspecialchars($n['message']) ?></div>
              <div class="notice-meta">
                <span class="badge badge-<?= $n['type']==='info'?'confirmed':($n['type']==='offer'?'pending':($n['type']==='event'?'confirmed':'cancelled')) ?>" style="font-size:10px;padding:2px 8px;"><?= strtoupper($n['type']) ?></span>
                &nbsp;·&nbsp; <?= date('d M Y', strtotime($n['created_at'])) ?>
                <?= $n['expires_at'] ? ' &nbsp;·&nbsp; Expires: '.date('d M Y', strtotime($n['expires_at'])) : '' ?>
              </div>
            </div>
            <div class="notice-actions">
              <button class="toggle-btn <?= !$n['is_active'] ? 'off' : '' ?>" title="<?= $n['is_active'] ? 'Deactivate' : 'Activate' ?>" onclick="toggleNotice(<?= $n['id'] ?>, this)">
                <i class="fas fa-<?= $n['is_active'] ? 'eye' : 'eye-slash' ?>"></i>
              </button>
              <button class="del-btn" title="Delete" onclick="deleteNotice(<?= $n['id'] ?>)">
                <i class="fas fa-trash"></i>
              </button>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Event Theme Selector -->
      <div class="section-card">
        <div class="section-head">
          <h3><i class="fas fa-palette"></i> Site Event Theme</h3>
          <button class="btn btn-accent" onclick="saveTheme()" style="padding:7px 16px;font-size:12px;"><i class="fas fa-save"></i> Apply Theme</button>
        </div>
        <p style="padding:12px 22px 0;font-size:13px;color:var(--muted);">Choose a theme to change the website's look for special occasions. Visitors will see the themed colours automatically.</p>
        <div class="theme-grid" id="themeGrid">
          <?php
          $themes = [
            ['normal',         '⚖️',  'Default',         'Classic navy & gold'],
            ['independence_day','🇮🇳', 'Independence Day', 'Tiranga saffron, white & green'],
            ['new_year',       '🎆',  'New Year',         'Festive gold & purple sparks'],
            ['diwali',         '🪔',  'Diwali',           'Warm orange & golden glow'],
            ['eid',            '🌙',  'Eid',              'Crescent green & gold'],
            ['offer',          '🏷️',  'Special Offer',    'Bold red & gold discount look'],
            ['christmas',      '🎄',  'Christmas',        'Festive red & green'],
          ];
          foreach($themes as [$val,$icon,$name,$desc]):
          ?>
          <div class="theme-card th-<?= str_replace('_','',$val) === 'independence_day' ? 'independence' : str_replace('_','',$val) ?> <?= $activeTheme===$val?'selected':'' ?>" onclick="selectTheme('<?= $val ?>', this)">
            <div class="selected-check"><i class="fas fa-check"></i></div>
            <div class="theme-icon"><?= $icon ?></div>
            <div class="theme-name"><?= $name ?></div>
            <div class="theme-desc"><?= $desc ?></div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

    </div>
  </div>
</main>

<!-- BOOKING DETAIL MODAL -->
<div class="modal-overlay" id="modalOverlay" onclick="closeModalOnOverlay(event)">
  <div class="modal" id="modalBox">
    <div class="modal-head">
      <h3><i class="fas fa-clipboard-list" style="color:var(--accent);margin-right:8px;"></i> Booking Details</h3>
      <button class="modal-close" onclick="closeModal()"><i class="fas fa-times"></i></button>
    </div>
    <div class="modal-body">
      <div class="detail-grid">
        <div class="detail-item"><div class="detail-label">Full Name</div><div class="detail-value" id="m_name">—</div></div>
        <div class="detail-item"><div class="detail-label">Phone</div><div class="detail-value" id="m_phone">—</div></div>
        <div class="detail-item full"><div class="detail-label">Location / Address</div><div class="detail-value" id="m_location">—</div></div>
        <div class="detail-item"><div class="detail-label">Survey Type</div><div class="detail-value" id="m_type">—</div></div>
        <div class="detail-item"><div class="detail-label">Preferred Date</div><div class="detail-value" id="m_date">—</div></div>
        <div class="detail-item full"><div class="detail-label">Message / Notes</div><div class="detail-value" id="m_message" style="color:var(--muted);font-size:13px;">—</div></div>
        <div class="detail-item"><div class="detail-label">Booking ID</div><div class="detail-value" id="m_id">—</div></div>
        <div class="detail-item"><div class="detail-label">Booked On</div><div class="detail-value" id="m_created">—</div></div>
        <div class="detail-item full">
          <div class="detail-label">Status</div>
          <select class="form-input" id="m_status_select" style="margin-top:4px;" onchange="updateStatus()">
            <option value="pending">Pending</option>
            <option value="confirmed">Confirmed</option>
            <option value="completed">Completed</option>
            <option value="cancelled">Cancelled</option>
          </select>
        </div>
      </div>
      <div class="modal-actions">
        <button class="btn btn-accent" id="m_whatsapp" onclick="openWhatsApp()"><i class="fab fa-whatsapp"></i> WhatsApp</button>
        <button class="btn btn-blue" id="m_call" onclick="callClient()"><i class="fas fa-phone"></i> Call</button>
        <button class="btn btn-danger" id="m_delete" onclick="deleteBooking()"><i class="fas fa-trash"></i> Delete</button>
        <button class="btn btn-ghost" onclick="closeModal()">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- TOAST -->
<div class="toast" id="toast"><i class="fas fa-check-circle"></i> <span id="toastMsg">Saved!</span></div>

<script>
let currentBookingId = null;
let currentPhone = null;

// Date
document.getElementById('currentDate').textContent = new Date().toLocaleDateString('en-IN',{weekday:'long',year:'numeric',month:'long',day:'numeric'});

// Loader
window.addEventListener('load', () => {
  setTimeout(() => document.getElementById('loader').classList.add('hide'), 700);
});

// Animate counters
document.querySelectorAll('.stat-value[data-target]').forEach(el => {
  const target = parseInt(el.dataset.target);
  if (!target) { el.textContent = '0'; return; }
  let current = 0;
  const step = Math.max(1, Math.ceil(target / 40));
  const timer = setInterval(() => {
    current = Math.min(current + step, target);
    el.textContent = current;
    if (current >= target) clearInterval(timer);
  }, 30);
});

// Sidebar toggle
document.getElementById('hamburger').onclick = () => {
  document.getElementById('sidebar').classList.toggle('open');
};

// View switching
function switchView(name, el) {
  document.querySelectorAll('.view').forEach(v => v.classList.remove('active'));
  document.getElementById('view-' + name).classList.add('active');
  document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
  el.classList.add('active');
  const titles = {dashboard:'Dashboard', bookings:'Bookings', settings:'Settings', notices:'Notices & Themes'};
  document.getElementById('pageTitle').innerHTML = '<span>' + (titles[name]||name) + '</span>';
  document.getElementById('sidebar').classList.remove('open');
}

// Search / Filter
function filterTable() {
  const q = document.getElementById('searchInput').value.toLowerCase();
  const s = document.getElementById('filterStatus').value;
  document.querySelectorAll('#bookingsTable tr').forEach(row => {
    const name = row.dataset.name || '';
    const status = row.dataset.status || '';
    const matchQ = !q || name.includes(q);
    const matchS = !s || status === s;
    row.style.display = (matchQ && matchS) ? '' : 'none';
  });
}

// Modal
function openModal(b) {
  currentBookingId = b.id;
  currentPhone = b.phone;
  document.getElementById('m_name').textContent = b.name;
  document.getElementById('m_phone').textContent = b.phone;
  document.getElementById('m_location').textContent = b.location;
  document.getElementById('m_type').textContent = b.survey_type;
  document.getElementById('m_date').textContent = b.preferred_date;
  document.getElementById('m_message').textContent = b.message || 'No message provided';
  document.getElementById('m_id').textContent = '#' + b.id;
  document.getElementById('m_created').textContent = new Date(b.created_at).toLocaleDateString('en-IN',{day:'numeric',month:'long',year:'numeric'});
  document.getElementById('m_status_select').value = b.status;
  document.getElementById('modalOverlay').classList.add('open');
}

function closeModal() {
  document.getElementById('modalOverlay').classList.remove('open');
  currentBookingId = null;
}

function closeModalOnOverlay(e) {
  if (e.target === document.getElementById('modalOverlay')) closeModal();
}

// Update status
function updateStatus() {
  if (!currentBookingId) return;
  const status = document.getElementById('m_status_select').value;
  const bookingId = currentBookingId;
  fetch('', {
    method:'POST',
    headers:{'Content-Type':'application/x-www-form-urlencoded'},
    body: `action=update_status&id=${bookingId}&status=${status}`
  }).then(r => r.json()).then(data => {
    if (data.success) {
      // Find all rows for this booking (dashboard recent + bookings table)
      const targetRows = [];
      document.querySelectorAll('#bookingsTable tr, #view-dashboard tbody tr').forEach(row => {
        if (row.getAttribute('onclick') && row.getAttribute('onclick').includes('"id":' + bookingId + ',')) {
          targetRows.push(row);
        }
      });

      if (status === 'completed') {
        // ✓ Mark complete → animate row out & remove from view
        showToast('✓ Booking completed and cleared from list');
        closeModal();
        targetRows.forEach(row => {
          row.style.transition = 'opacity 0.5s ease, transform 0.5s ease, max-height 0.5s ease, padding 0.5s ease';
          row.style.maxHeight = row.offsetHeight + 'px';
          row.style.background = 'rgba(34,197,94,0.12)';
          // small delay to show the green flash
          setTimeout(() => {
            row.style.opacity = '0';
            row.style.transform = 'translateX(40px)';
            row.style.maxHeight = '0';
            row.style.padding = '0';
            setTimeout(() => {
              row.remove();
              // Show empty state if no rows left in main bookings table
              const tbody = document.getElementById('bookingsTable');
              if (tbody && !tbody.querySelector('tr.booking-row')) {
                tbody.innerHTML = '<tr><td colspan="8"><div class="empty-state"><i class="fas fa-inbox"></i>No active bookings</div></td></tr>';
              }
            }, 500);
          }, 250);
        });
      } else {
        // Other statuses → just update badge & data attr
        showToast('Status updated to ' + status);
        targetRows.forEach(row => {
          const badge = row.querySelector('.badge');
          if (badge) {
            badge.className = 'badge badge-' + status;
            badge.textContent = status.charAt(0).toUpperCase() + status.slice(1);
          }
          row.dataset.status = status;
        });
      }
    } else {
      showToast('Error: ' + (data.error || 'unknown'));
    }
  }).catch(() => showToast('Error updating status'));
}

// Delete booking from MODAL (uses currentBookingId)
function deleteBooking() {
  if (!currentBookingId) return;
  const name = document.getElementById('m_name').textContent || 'this booking';
  if (!confirm(`Permanently delete the booking for "${name}"?\n\nThis action CANNOT be undone.`)) return;
  performDelete(currentBookingId);
}

// Delete booking from a TABLE ROW (icon trash button)
function deleteBookingRow(id) {
  if (!id) return;
  if (!confirm('Permanently delete this booking?\n\nThis action CANNOT be undone.')) return;
  performDelete(id);
}

// Shared delete worker — animates row out, calls API, removes from DOM
function performDelete(id) {
  // Find every row matching this booking (recent + bookings table)
  const targetRows = [];
  document.querySelectorAll('#bookingsTable tr, #view-dashboard tbody tr').forEach(row => {
    if (row.getAttribute('onclick') && row.getAttribute('onclick').includes('"id":' + id + ',')) {
      targetRows.push(row);
    }
  });

  fetch('', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `action=delete_booking&id=${id}`
  }).then(r => r.json()).then(data => {
    if (!data.success) { showToast('Error: ' + (data.error || 'delete failed')); return; }

    showToast('🗑 Booking #' + id + ' deleted permanently');
    if (currentBookingId === id) closeModal();

    targetRows.forEach(row => {
      row.style.transition = 'opacity 0.4s ease, transform 0.4s ease, max-height 0.4s ease, padding 0.4s ease, background 0.3s ease';
      row.style.maxHeight = row.offsetHeight + 'px';
      row.style.background = 'rgba(239,68,68,0.15)';
      setTimeout(() => {
        row.style.opacity = '0';
        row.style.transform = 'translateX(-40px)';
        row.style.maxHeight = '0';
        row.style.padding = '0';
        setTimeout(() => {
          row.remove();
          const tbody = document.getElementById('bookingsTable');
          if (tbody && !tbody.querySelector('tr.booking-row')) {
            tbody.innerHTML = '<tr><td colspan="8"><div class="empty-state"><i class="fas fa-inbox"></i>No bookings yet</div></td></tr>';
          }
        }, 400);
      }, 200);
    });

    // Decrement Total Bookings stat counter on dashboard
    const totalEl = document.querySelector('#view-dashboard .stat-card:first-child .stat-value');
    if (totalEl) {
      const cur = parseInt(totalEl.textContent || '0', 10);
      if (!isNaN(cur) && cur > 0) totalEl.textContent = cur - 1;
    }
  }).catch(() => showToast('Network error — booking not deleted'));
}

// WhatsApp & Call
function openWhatsApp() {
  if (!currentPhone) return;
  const name = document.getElementById('m_name').textContent;
  const type = document.getElementById('m_type').textContent;
  const date = document.getElementById('m_date').textContent;
  const msg = encodeURIComponent(`Hello ${name}, your booking for *${type}* on ${date} has been received. We will contact you shortly. — SG Survey`);
  window.open(`https://wa.me/91${currentPhone}?text=${msg}`, '_blank');
}

function callClient() {
  if (currentPhone) window.open('tel:+91' + currentPhone);
}

// Save settings
function saveSettings() {
  const fields = ['phone_primary','phone_secondary','email','location','charge_land_survey','charge_digital_survey','charge_autocad_sketch','charge_laser_survey'];
  const body = 'action=save_site_data&' + fields.map(f => `${f}=${encodeURIComponent(document.getElementById('s_'+f).value)}`).join('&');
  fetch('', {method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body})
    .then(r => r.json())
    .then(d => { if (d.success) showToast('Settings saved successfully!'); });
}

// Toast
function showToast(msg) {
  const t = document.getElementById('toast');
  document.getElementById('toastMsg').textContent = msg;
  t.classList.add('show');
  setTimeout(() => t.classList.remove('show'), 3000);
}

// ── NOTICES ──
let selectedTheme = '<?= $activeTheme ?>';

function addNotice() {
  const title   = document.getElementById('n_title').value.trim();
  const message = document.getElementById('n_message').value.trim();
  const type    = document.getElementById('n_type').value;
  const expires = document.getElementById('n_expires').value;
  if (!title || !message) { showToast('Title and message are required'); return; }
  fetch('', {
    method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'},
    body:`action=add_notice&title=${encodeURIComponent(title)}&message=${encodeURIComponent(message)}&type=${type}&expires_at=${expires}`
  }).then(r=>r.json()).then(d=>{
    if (!d.success) { showToast('Error: '+(d.error||'failed')); return; }
    showToast('✅ Notice published!');
    document.getElementById('n_title').value = '';
    document.getElementById('n_message').value = '';
    document.getElementById('n_expires').value = '';
    // Inject new row
    const list = document.getElementById('noticeList');
    const emptyEl = list.querySelector('.empty-state');
    if (emptyEl) emptyEl.remove();
    const typeEmoji = {info:'ℹ️',offer:'🏷️',event:'🎉',alert:'🚨',update:'✅'};
    list.insertAdjacentHTML('afterbegin', `
      <div class="notice-item" id="notice-${d.id}" style="animation:fadeUp .3s ease">
        <div class="notice-dot nt-${type}"></div>
        <div class="notice-content">
          <div class="notice-title">${title}</div>
          <div class="notice-msg">${message}</div>
          <div class="notice-meta"><span style="font-size:10px;text-transform:uppercase;font-weight:600;">${typeEmoji[type]||''} ${type}</span> &nbsp;·&nbsp; Just now</div>
        </div>
        <div class="notice-actions">
          <button class="toggle-btn" title="Deactivate" onclick="toggleNotice(${d.id}, this)"><i class="fas fa-eye"></i></button>
          <button class="del-btn" title="Delete" onclick="deleteNotice(${d.id})"><i class="fas fa-trash"></i></button>
        </div>
      </div>`);
  }).catch(()=>showToast('Network error'));
}

function toggleNotice(id, btn) {
  fetch('',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:`action=toggle_notice&id=${id}`})
    .then(r=>r.json()).then(d=>{
      const item = document.getElementById('notice-'+id);
      if (d.is_active) {
        item.classList.remove('inactive');
        btn.classList.remove('off');
        btn.title='Deactivate';
        btn.innerHTML='<i class="fas fa-eye"></i>';
        showToast('Notice is now LIVE on the website');
      } else {
        item.classList.add('inactive');
        btn.classList.add('off');
        btn.title='Activate';
        btn.innerHTML='<i class="fas fa-eye-slash"></i>';
        showToast('Notice hidden from website');
      }
    });
}

function deleteNotice(id) {
  if (!confirm('Delete this notice permanently?')) return;
  fetch('',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:`action=delete_notice&id=${id}`})
    .then(r=>r.json()).then(d=>{
      if (d.success) {
        const el = document.getElementById('notice-'+id);
        if (el) { el.style.opacity='0'; el.style.transform='translateX(40px)'; el.style.transition='.3s'; setTimeout(()=>el.remove(),300); }
        showToast('🗑 Notice deleted');
      }
    });
}

function selectTheme(val, card) {
  selectedTheme = val;
  document.querySelectorAll('.theme-card').forEach(c=>c.classList.remove('selected'));
  card.classList.add('selected');
}

function saveTheme() {
  fetch('',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:`action=save_theme&theme=${selectedTheme}`})
    .then(r=>r.json()).then(d=>{
      if (d.success) showToast('🎨 Theme applied! Visitors will see the new look.');
    });
}

// Logout
function confirmLogout() {
  if (confirm('Are you sure you want to logout?')) {
    window.location.href = 'logout.php';
  }
}

// Keyboard escape
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });
</script>
</body>
</html>
