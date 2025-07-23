<?php
// Include database connection
require 'db_connection.php';

// Start the session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['UserId'])) {
    // Redirect to login page if not logged in
    header("Location: Login.php");
    exit;
}

// Get the logged-in user's ID
$userId = $_SESSION['UserId'];
$pageTitle = "Dashboard";
include 'header.php'; // Include the header
?>

<!-- Page Content -->
<div class="container mt-5">
    <div class="text-center mb-5">
        <h1 class="display-4 text-dark">Welcome, <span class="text-primary"><?php echo htmlspecialchars($userId); ?></span>!</h1>
        <p class="lead text-secondary">Your personal space to manage friends, albums, and pictures effortlessly.</p>
    </div>

    <div class="row g-4">
        <!-- Manage Friends -->
        <div class="col-lg-4 col-md-6">
            <div class="card border-0 shadow h-100">
                <div class="card-body text-center">
                    <i class="fas fa-users fa-4x mb-3" style="color: #3A6351;"></i>
                    <h5 class="card-title text-dark">Manage Friends</h5>
                    <p class="card-text text-muted">Connect and manage your friendships.</p>
                    <a href="MyFriends.php" class="btn btn-outline-primary w-100">Go to Friends</a>
                </div>
            </div>
        </div>
        <!-- View Albums -->
        <div class="col-lg-4 col-md-6">
            <div class="card border-0 shadow h-100">
                <div class="card-body text-center">
                    <i class="fas fa-images fa-4x mb-3" style="color: #5D8233;"></i>
                    <h5 class="card-title text-dark">View Albums</h5>
                    <p class="card-text text-muted">Explore your photo albums and memories.</p>
                    <a href="MyAlbums.php" class="btn btn-outline-success w-100">Go to Albums</a>
                </div>
            </div>
        </div>
        <!-- Upload Pictures -->
        <div class="col-lg-4 col-md-6">
            <div class="card border-0 shadow h-100">
                <div class="card-body text-center">
                    <i class="fas fa-upload fa-4x mb-3" style="color: #AA6F39;"></i>
                    <h5 class="card-title text-dark">Upload Pictures</h5>
                    <p class="card-text text-muted">Share your favorite moments by uploading pictures.</p>
                    <a href="UploadPictures.php" class="btn btn-outline-warning w-100">Upload Now</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; // Include the footer ?>
