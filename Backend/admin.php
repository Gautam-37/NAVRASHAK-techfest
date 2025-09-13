<?php
// backend/admin.php - Simple admin panel to view registrations
include 'db_connection.php';

// Simple authentication (in production, use proper session management)
$admin_password = 'admin123'; // Change this!
$authenticated = false;

if (isset($_POST['login'])) {
    if ($_POST['password'] === $admin_password) {
        session_start();
        $_SESSION['admin_logged_in'] = true;
        $authenticated = true;
    } else {
        $error = "Invalid password!";
    }
} elseif (isset($_SESSION)) {
    session_start();
    $authenticated = isset($_SESSION['admin_logged_in']);
}

if (isset($_GET['logout'])) {
    session_start();
    session_destroy();
    $authenticated = false;
}

// Get registrations if authenticated
$registrations = [];
$stats = [];
if ($authenticated) {
    try {
        // Get all registrations
        $result = $conn->query("SELECT * FROM registrations ORDER BY registration_date DESC");
        if ($result) {
            $registrations = $result->fetch_all(MYSQLI_ASSOC);
        }

        // Get registration statistics
        $stats_query = "SELECT 
            event,
            COUNT(*) as total_registrations,
            COUNT(CASE WHEN status = 'confirmed' THEN 1 END) as confirmed_registrations,
            COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_registrations,
            SUM(team_size) as total_participants
            FROM registrations 
            GROUP BY event";
        
        $stats_result = $conn->query($stats_query);
        if ($stats_result) {
            $stats = $stats_result->fetch_all(MYSQLI_ASSOC);
        }
    } catch (Exception $e) {
        $error = "Database error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NAVAKSHARA Admin Panel</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background: #2c3e50;
            color: white;
            padding: 1rem 0;
            margin-bottom: 2rem;
        }

        .header h1 {
            text-align: center;
        }

        .logout-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            background: #e74c3c;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
        }

        .login-form {
            max-width: 400px;
            margin: 100px auto;
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .login-form h2 {
            text-align: center;
            margin-bottom: 1rem;
            color: #2c3e50;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }

        .btn {
            background: #3498db;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
        }

        .btn:hover {
            background: #2980b9;
        }

        .error {
            color: #e74c3c;
            text-align: center;
            margin-bottom: 1rem;
        }

        .success {
            color: #27ae60;
            text-align: center;
            margin-bottom: 1rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .stat-card h3 {
            color: #2c3e50;
            margin-bottom: 1rem;
            text-transform: uppercase;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #3498db;
        }

        .table-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .table-header {
            background: #3498db;
            color: white;
            padding: 1rem;
        }

        .table-header h3 {
            margin: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background: #f8f9fa;
            font-weight: bold;
            color: #2c3e50;
        }

        tr:hover {
            background: #f8f9fa;
        }

        .status-pending {
            background: #f39c12;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
        }

        .status-confirmed {
            background: #27ae60;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
        }

        .event-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }

        .event-rc_plane {
            background: #e8f4fd;
            color: #3498db;
        }

        .event-drone_racing {
            background: #fce4ec;
            color: #e91e63;
        }

        .event-robot_war {
            background: #f3e5f5;
            color: #9c27b0;
        }

        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }
            
            table {
                font-size: 14px;
            }
            
            th, td {
                padding: 8px;
            }
        }
    </style>
</head>
<body>
    <?php if (!$authenticated): ?>
        <!-- Login Form -->
        <div class="login-form">
            <h2>NAVAKSHARA Admin Login</h2>
            <?php if (isset($error)): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <form method="post">
                <div class="form-group">
                    <label>Password:</label>
                    <input type="password" name="password" required>
                </div>
                <button type="submit" name="login" class="btn">Login</button>
            </form>
            <div style="text-align: center; margin-top: 1rem; font-size: 14px; color: #666;">
                Default password: admin123
            </div>
        </div>
    <?php else: ?>
        <!-- Admin Dashboard -->
        <div class="header">
            <div class="container">
                <h1>NAVAKSHARA Admin Dashboard</h1>
                <a href="?logout=1" class="logout-btn">Logout</a>
            </div>
        </div>

        <div class="container">
            <!-- Statistics -->
            <div class="stats-grid">
                <?php foreach ($stats as $stat): ?>
                    <div class="stat-card">
                        <h3><?php echo ucwords(str_replace('_', ' ', $stat['event'])); ?></h3>
                        <div class="stat-number"><?php echo $stat['total_registrations']; ?></div>
                        <div>Total Registrations</div>
                        <div style="margin-top: 10px; font-size: 14px; color: #666;">
                            Participants: <?php echo $stat['total_participants']; ?><br>
                            Confirmed: <?php echo $stat['confirmed_registrations']; ?><br>
                            Pending: <?php echo $stat['pending_registrations']; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <div class="stat-card">
                    <h3>Total Overview</h3>
                    <div class="stat-number"><?php echo count($registrations); ?></div>
                    <div>All Registrations</div>
                    <div style="margin-top: 10px; font-size: 14px; color: #666;">
                        Total Participants: <?php echo array_sum(array_column($registrations, 'team_size')); ?>
                    </div>
                </div>
            </div>

            <!-- Registrations Table -->
            <div class="table-container">
                <div class="table-header">
                    <h3>All Registrations</h3>
                </div>
                
                <?php if (count($registrations) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>College</th>
                                <th>Event</th>
                                <th>Team Name</th>
                                <th>Team Size</th>
                                <th>Status</th>
                                <th>Registration Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($registrations as $reg): ?>
                                <tr>
                                    <td><?php echo $reg['id']; ?></td>
                                    <td><?php echo htmlspecialchars($reg['name']); ?></td>
                                    <td><?php echo htmlspecialchars($reg['email']); ?></td>
                                    <td><?php echo htmlspecialchars($reg['phone']); ?></td>
                                    <td><?php echo htmlspecialchars($reg['college']); ?></td>
                                    <td>
                                        <span class="event-badge event-<?php echo $reg['event']; ?>">
                                            <?php echo ucwords(str_replace('_', ' ', $reg['event'])); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($reg['team_name']); ?></td>
                                    <td><?php echo $reg['team_size']; ?></td>
                                    <td>
                                        <span class="status-<?php echo $reg['status']; ?>">
                                            <?php echo ucfirst($reg['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M j, Y H:i', strtotime($reg['registration_date'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div style="padding: 2rem; text-align: center; color: #666;">
                        No registrations found.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</body>
</html>