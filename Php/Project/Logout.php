<?php
// Start the session
session_start();

// Destroy the session
session_unset();
session_destroy();

// Redirect to login page
header("Location: Login.php");
exit;
?>
