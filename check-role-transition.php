<?php

session_start();
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';

header('Content-Type: application/json');

// Check if user is authenticated
if (!isLoggedIn()) {
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

// Check database connection
if (!isset($pdo)) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

try {
    $user_id = $_SESSION['user_id'];
    $current_session_role = $_SESSION['role'];
    
    // Get current role from database
    $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        echo json_encode(['error' => 'User not found']);
        exit;
    }
    
    $current_db_role = $user['role'];
    
    // Check if role has changed
    if ($current_db_role !== $current_session_role) {
        // Update session with new role
        $_SESSION['role'] = $current_db_role;
        
        // Determine redirect URL based on new role
        $redirect_url = '';
        switch ($current_db_role) {
            case 'employee':
                $redirect_url = 'employee-dashboard.php?transitioned=true';
                break;
            case 'hr':
                $redirect_url = 'hr-dashboard.php';
                break;
            case 'candidate':
                $redirect_url = 'candidate-dashboard.php';
                break;
            case 'admin':
                $redirect_url = 'admin-dashboard.php';
                break;
            default:
                $redirect_url = 'index.php';
                break;
        }
        
        // Log the role transition for audit purposes
        try {
            $stmt = $pdo->prepare("
                INSERT INTO activity_logs (user_id, action, description, created_at) 
                VALUES (?, 'role_transition', ?, CURRENT_TIMESTAMP)
            ");
            $description = "Role changed from {$current_session_role} to {$current_db_role}";
            $stmt->execute([$user_id, $description]);
        } catch (Exception $e) {
            // Log error but don't fail the role transition
            error_log("Failed to log role transition: " . $e->getMessage());
        }
        
        echo json_encode([
            'role_changed' => true,
            'old_role' => $current_session_role,
            'new_role' => $current_db_role,
            'redirect_url' => $redirect_url,
            'message' => 'Your role has been updated. You will be redirected.'
        ]);
    } else {
        // No role change
        echo json_encode([
            'role_changed' => false,
            'current_role' => $current_db_role,
            'message' => 'No role change detected'
        ]);
    }
    
} catch (Exception $e) {
    error_log("Role transition check error: " . $e->getMessage());
    echo json_encode(['error' => 'An error occurred while checking role transition']);
}
?>
