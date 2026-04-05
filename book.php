<?php
session_start();
require_once 'db.php';

function sanitizeInput($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$name       = sanitizeInput($_POST['name'] ?? '');
$phone      = sanitizeInput($_POST['phone'] ?? '');
$location   = sanitizeInput($_POST['location'] ?? '');
$surveyType = sanitizeInput($_POST['surveyType'] ?? $_POST['type'] ?? '');
$date       = sanitizeInput($_POST['date'] ?? '');
$message    = sanitizeInput($_POST['message'] ?? '');

$errors = [];
if (!$name)                                   $errors[] = "Name is required.";
if (!preg_match('/^[0-9]{10}$/', $phone))     $errors[] = "A valid 10-digit phone number is required.";
if (!$location)                               $errors[] = "Location is required.";
if (!$surveyType)                             $errors[] = "Survey type is required.";
if (!$date)                                   $errors[] = "Preferred date is required.";

if (!empty($errors)) {
    echo "<script>alert('" . implode("\\n", $errors) . "');history.back();</script>";
    exit;
}

try {
    $pdo = getDBConnection();

    $stmt = $pdo->prepare("
        INSERT INTO bookings (name, phone, location, survey_type, preferred_date, message)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$name, $phone, $location, $surveyType, $date, $message]);
    $bookingId = $pdo->lastInsertId();

    $_SESSION['booking_data'] = [
        'id'          => $bookingId,
        'name'        => $name,
        'phone'       => $phone,
        'location'    => $location,
        'survey_type' => $surveyType,
        'preferred_date' => $date,
        'message'     => $message,
    ];

    header('Location: booking-confirmation.php');
    exit;

} catch (Exception $e) {
    echo "<script>alert('Sorry, something went wrong. Please try again.');history.back();</script>";
}
?>
