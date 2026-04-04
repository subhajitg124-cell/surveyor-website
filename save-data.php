<?php
/**
 * Save Data Handler
 * 
 * Handles all admin data operations (update, delete, etc.)
 */

session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Set headers
header('Content-Type: application/json');

// Include database configuration
require_once 'db.php';

// Get JSON input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data || !isset($data['action'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

// Get database connection
$pdo = getDBConnection();

if (!$pdo) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

try {
    switch ($data['action']) {
        case 'update_booking':
            // Update booking status
            $bookingId = $data['booking_id'];
            $status = $data['status'];
            
            $validStatuses = ['pending', 'confirmed', 'completed', 'cancelled'];
            if (!in_array($status, $validStatuses)) {
                echo json_encode(['success' => false, 'message' => 'Invalid status']);
                exit;
            }
            
            $sql = "UPDATE bookings SET status = :status WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute([
                ':status' => $status,
                ':id' => $bookingId
            ]);
            
            echo json_encode([
                'success' => $result,
                'message' => $result ? 'Booking updated successfully' : 'Failed to update booking'
            ]);
            break;
            
        case 'delete_booking':
            // Delete booking
            $bookingId = $data['booking_id'];
            
            $sql = "DELETE FROM bookings WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute([':id' => $bookingId]);
            
            echo json_encode([
                'success' => $result,
                'message' => $result ? 'Booking deleted successfully' : 'Failed to delete booking'
            ]);
            break;
            
        case 'update_settings':
            // Update site settings
            $fieldsToUpdate = [
                'phone_primary',
                'phone_secondary',
                'email',
                'location',
                'charge_land_survey',
                'charge_digital_survey',
                'charge_autocad_sketch',
                'charge_laser_survey'
            ];
            
            $pdo->beginTransaction();
            
            try {
                $stmt = $pdo->prepare("INSERT INTO site_data (data_key, data_value)
                                      VALUES (:key, :value)
                                      ON CONFLICT(data_key) DO UPDATE SET data_value = excluded.data_value, updated_at = CURRENT_TIMESTAMP");
                
                foreach ($fieldsToUpdate as $field) {
                    if (isset($data[$field])) {
                        $stmt->execute([
                            ':key' => $field,
                            ':value' => $data[$field]
                        ]);
                    }
                }
                
                $pdo->commit();
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Settings updated successfully'
                ]);
            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Unknown action']);
            break;
    }
    
} catch (PDOException $e) {
    error_log("Save data error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while processing your request'
    ]);
}

?>
