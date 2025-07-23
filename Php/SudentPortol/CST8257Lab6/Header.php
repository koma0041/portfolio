<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>

<nav>
    <div class="logo">
        <a href="Index.php">
            <img src="images/icon.png" alt="Algonquin College Logo">
        </a>
    </div>
    <ul>
        <li><a href="Index.php">Home</a></li>
        <li><a href="CourseSelection.php">Course Selection</a></li>
        <li><a href="CurrentRegistration.php">Current Registration</a></li>
        <?php if (isset($_SESSION['studentId'])): ?>
            <li><a href="Logout.php">Log Out</a></li>
        <?php else: ?>
            <li><a href="Login.php">Log In</a></li>
        <?php endif; ?>
    </ul>
</nav>
