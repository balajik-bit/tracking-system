<?php
// Start the session
session_start();

// Check if user is admin before destroying session
$is_admin = isset($_SESSION['admin_id']);

// Unset all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Redirect based on user type
if ($is_admin) {
    header("Location: admin_login.html");
} else {
    header("Location: login.html");
}
exit();
?> 