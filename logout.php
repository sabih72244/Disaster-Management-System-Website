<?php
session_start();

// Check if the user is logged in
if (isset($_SESSION['role'])) {
    // Destroy session variables
    session_unset();
    session_destroy();

    // Redirect to the login page or home page
    header("Location: index.php");
    exit;
} else {
    // If no session exists, redirect to login page
    header("Location: index.php");
    exit;
}
?>
