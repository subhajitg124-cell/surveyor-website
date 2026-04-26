<?php
session_start();
require_once 'db.php';
require_once 'mailer.php';

function sanitizeInput($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

$isAjax = (
    isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
);

function respond($success, $message = '', $extra = []) {
    global $isAjax;
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra));
        exit;
    }
    if ($success) {
        header('Location: booking-confirmation.php');
    } else {
        echo "<script>alert(" . json_encode($message) . ");history.back();</script>";
    }
    exit;
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
    respond(false, implode(' ', $errors));
}

try {
    $pdo = getDBConnection();

    $stmt = $pdo->prepare("
        INSERT INTO bookings (name, phone, location, survey_type, preferred_date, message)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$name, $phone, $location, $surveyType, $date, $message]);
    $bookingId = $pdo->lastInsertId();

    $bookingData = [
        'id'             => $bookingId,
        'name'           => $name,
        'phone'          => $phone,
        'location'       => $location,
        'survey_type'    => $surveyType,
        'preferred_date' => $date,
        'message'        => $message,
        'created_at'     => date('Y-m-d H:i:s'),
    ];
    $_SESSION['booking_data'] = $bookingData;

    // ── AUTOMATION: notify owner via Email + WhatsApp ──
    // Both calls are wrapped in try so a failure NEVER blocks the booking.
    $notifyResults = ['email' => null, 'whatsapp' => null];
    try {
        $notifyResults['email'] = sendBookingEmail($bookingData);
    } catch (\Throwable $e) {
        error_log('sendBookingEmail exception: ' . $e->getMessage());
        $notifyResults['email'] = ['success' => false, 'error' => 'exception'];
    }
    try {
        $notifyResults['whatsapp'] = sendBookingWhatsApp($bookingData, '919749332827');
    } catch (\Throwable $e) {
        error_log('sendBookingWhatsApp exception: ' . $e->getMessage());
        $notifyResults['whatsapp'] = ['success' => false, 'error' => 'exception'];
    }
    $_SESSION['notify_results'] = $notifyResults;

    respond(true, 'Booking saved.', [
        'id'      => $bookingId,
        'notify'  => [
            'email'    => !empty($notifyResults['email']['success']),
            'whatsapp' => !empty($notifyResults['whatsapp']['success']),
        ],
    ]);

} catch (Exception $e) {
    error_log("Booking error: " . $e->getMessage());
    respond(false, 'Sorry, something went wrong. Please try again.');
}
