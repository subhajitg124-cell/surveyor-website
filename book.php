<?php
require_once 'db.php';

function sanitizeInput($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request");
}

// GET DATA
$name = sanitizeInput($_POST['name'] ?? '');
$phone = sanitizeInput($_POST['phone'] ?? '');
$location = sanitizeInput($_POST['location'] ?? '');
$surveyType = sanitizeInput($_POST['surveyType'] ?? $_POST['type'] ?? '');
$date = sanitizeInput($_POST['date'] ?? '');
$message = sanitizeInput($_POST['message'] ?? '');

// VALIDATION
$errors = [];

if (!$name) $errors[] = "Name required";
if (!preg_match('/^[0-9]{10}$/', $phone)) $errors[] = "Invalid phone";
if (!$location) $errors[] = "Location required";
if (!$surveyType) $errors[] = "Survey type required";
if (!$date) $errors[] = "Date required";

// ERROR RESPONSE
if (!empty($errors)) {
    echo "<script>alert('".implode("\\n",$errors)."');history.back();</script>";
    exit;
}

// DATABASE
try {
    $pdo = getDBConnection();

    $stmt = $pdo->prepare("
        INSERT INTO bookings 
        (name, phone, location, survey_type, preferred_date, message)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([$name,$phone,$location,$surveyType,$date,$message]);

    // SUCCESS
   echo "<script>
alert('Booking Successful!');
window.open('https://wa.me/91$phone?text=New Booking: $name, $location, $surveyType', '_blank');
window.location.href='index.php';
</script>";

} catch (Exception $e) {
    echo "<script>alert('Error: Try again later');history.back();</script>";
}
?>