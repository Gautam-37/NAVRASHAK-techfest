<?php
// backend/db_connection.php

// Database configuration
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'navakshara_techfest';

// Set error reporting for development (disable in production)
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // Create connection
    $conn = new mysqli($host, $user, $password, $database);
    
    // Set charset to utf8mb4 for better Unicode support
    $conn->set_charset("utf8mb4");
    
} catch (mysqli_sql_exception $e) {
    // Log the error for debugging (in production, don't expose database errors to users)
    error_log("Database connection failed: " . $e->getMessage());
    
    // For production, show a generic error message
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode(['error' => 'Database connection failed. Please try again later.']);
    } else {
        http_response_code(500);
        echo "<html><body><h2>Service Temporarily Unavailable</h2><p>We're experiencing technical difficulties. Please try again later.</p></body></html>";
    }
    exit();
}
?>