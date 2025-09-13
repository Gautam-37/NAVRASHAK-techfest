<?php
// backend/process_registration.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

include 'db_connection.php';

// Function to send JSON error response
function sendError($message, $code = 400) {
    http_response_code($code);
    echo json_encode(['error' => $message]);
    exit();
}

// Function to send JSON success response
function sendSuccess($message, $data = []) {
    echo json_encode(['success' => $message, 'data' => $data]);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Get JSON input or form data
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            $input = $_POST; // Fallback to form data
        }

        // Get and sanitize form data
        $name = trim($input['name'] ?? '');
        $email = trim($input['email'] ?? '');
        $phone = trim($input['phone'] ?? '');
        $college = trim($input['college'] ?? '');
        $event = trim($input['event'] ?? '');
        $team_name = trim($input['team_name'] ?? '');
        $team_size = intval($input['team_size'] ?? 1);
        
        // Collect team member names
        $team_members = [];
        if ($team_size > 1) {
            for ($i = 2; $i <= $team_size; $i++) {
                $memberKey = "member_$i";
                if (isset($input[$memberKey]) && !empty(trim($input[$memberKey]))) {
                    $team_members[] = trim($input[$memberKey]);
                }
            }
        }
        $team_members_json = json_encode($team_members);
        
        // Additional fields based on event type
        $drone_model = trim($input['drone_model'] ?? '');
        
        // Validate required fields
        if (empty($name) || empty($email) || empty($phone) || empty($college) || empty($event) || empty($team_name)) {
            sendError("Please fill all required fields.");
        }
        
        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            sendError("Invalid email format.");
        }
        
        // Validate phone number (basic validation)
        if (!preg_match('/^[0-9+\-\s()]{10,15}$/', $phone)) {
            sendError("Invalid phone number format.");
        }
        
        // Validate event type
        $valid_events = ['rc_plane', 'drone_racing', 'robot_war'];
        if (!in_array($event, $valid_events)) {
            sendError("Invalid event type.");
        }
        
        // Validate team size
        if ($team_size < 1 || $team_size > 5) {
            sendError("Invalid team size. Must be between 1 and 5 members.");
        }
        
        // Check if email already registered for the same event
        $check_stmt = $conn->prepare("SELECT id FROM registrations WHERE email = ? AND event = ?");
        if (!$check_stmt) {
            sendError("Database error: " . $conn->error, 500);
        }
        
        $check_stmt->bind_param("ss", $email, $event);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $check_stmt->close();
            sendError("You have already registered for this event.");
        }
        $check_stmt->close();
        
        // Insert into database using prepared statement
        $stmt = $conn->prepare(
            "INSERT INTO registrations (name, email, phone, college, event, team_name, team_size, team_members, drone_model, registration_date) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())"
        );
        
        if (!$stmt) {
            sendError("Database error: " . $conn->error, 500);
        }
        
        $stmt->bind_param("ssssssiss", $name, $email, $phone, $college, $event, $team_name, $team_size, $team_members_json, $drone_model);
        
        if ($stmt->execute()) {
            $registration_id = $conn->insert_id;
            
            // Close statement
            $stmt->close();
            
            // Send confirmation email (configure your mail server)
            // For now, we'll just log it
            error_log("Registration successful for: $email, Event: $event, ID: $registration_id");
            
            // Send JSON success response
            sendSuccess("Registration successful! You will receive a confirmation email shortly.", [
                'registration_id' => $registration_id,
                'event' => $event,
                'team_name' => $team_name
            ]);
            
        } else {
            $stmt->close();
            sendError("Registration failed. Please try again.", 500);
        }
        
    } catch (Exception $e) {
        error_log("Registration error: " . $e->getMessage());
        sendError("An unexpected error occurred. Please try again.", 500);
    } finally {
        if (isset($conn)) {
            $conn->close();
        }
    }
} else {
    sendError("Invalid request method.", 405);
}
?>