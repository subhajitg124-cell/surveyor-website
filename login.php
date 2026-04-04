<?php
/**
 * Admin Login Authentication Handler
 * 
 * Processes admin login requests and creates secure sessions
 */

// Start session
session_start();

// Enable error reporting for debugging (disable in production)
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Set headers for JSON response
header('Content-Type: application/json');

// Include database configuration
require_once 'db.php';

// Response function
function sendResponse($success, $message, $data = null) {
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, 'Invalid request method');
}

// Sanitize input
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// Get form data
$username = isset($_POST['username']) ? sanitizeInput($_POST['username']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : ''; // Don't sanitize password
$remember = isset($_POST['remember']) ? true : false;

// Validation
if (empty($username) || empty($password)) {
    sendResponse(false, 'Username and password are required');
}

// Get database connection
$pdo = getDBConnection();

if (!$pdo) {
    sendResponse(false, 'Database connection failed. Please try again later.');
}

try {
    // Fetch user from database
    $sql = "SELECT id, username, password_hash, email, full_name, is_active 
            FROM admin_users 
            WHERE username = :username 
            LIMIT 1";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch();
    
    // Check if user exists
    if (!$user) {
        // Add small delay to prevent brute force attacks
        sleep(1);
        sendResponse(false, 'Invalid username or password');
    }
    
    // Check if user is active
    if (!$user['is_active']) {
        sendResponse(false, 'Your account has been deactivated. Please contact administrator.');
    }
    
    // Verify password
    if (!password_verify($password, $user['password_hash'])) {
        // Add small delay to prevent brute force attacks
        sleep(1);
        sendResponse(false, 'Invalid username or password');
    }
    
    // Password is correct - create session
    session_regenerate_id(true); // Prevent session fixation
    
    $_SESSION['admin_logged_in'] = true;
    $_SESSION['admin_id'] = $user['id'];
    $_SESSION['admin_username'] = $user['username'];
    $_SESSION['admin_email'] = $user['email'];
    $_SESSION['admin_name'] = $user['full_name'];
    $_SESSION['login_time'] = time();
    
    // Update last login time
    $updateSql = "UPDATE admin_users SET last_login = NOW() WHERE id = :id";
    $updateStmt = $pdo->prepare($updateSql);
    $updateStmt->execute([':id' => $user['id']]);
    
    // Set remember me cookie if checked (30 days)
    if ($remember) {
        $token = bin2hex(random_bytes(32));
        setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/', '', true, true);
        
        // Store token in database (you would need to create a table for this)
        // For simplicity, we're just setting the cookie here
    }
    
    sendResponse(true, 'Login successful!', [
        'username' => $user['username'],
        'name' => $user['full_name']
    ]);
    
} catch (PDOException $e) {
    // Log error (in production, use proper logging)
    error_log("Login error: " . $e->getMessage());
    
    sendResponse(false, 'An error occurred during login. Please try again later.');
}

?>
