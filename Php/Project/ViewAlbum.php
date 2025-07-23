<?php
// Include database connection
require 'db_connection.php';

// Start the session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['UserId'])) {
    header("Location: Login.php");
    exit;
}

// Get the album ID from the query parameter
if (!isset($_GET['albumId']) || empty($_GET['albumId'])) {
    die("Invalid album ID.");
}

$albumId = intval($_GET['albumId']); // Sanitize input

// Fetch album details from the database
try {
    $sql = "SELECT * FROM Album WHERE Album_Id = :albumId";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':albumId' => $albumId]);
    $album = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$album) {
        die("Album not found.");
    }
} catch (PDOException $e) {
    die("Error: " . htmlspecialchars($e->getMessage()));
}

// Fetch pictures in the album
try {
    $sql = "SELECT * FROM Picture WHERE Album_Id = :albumId";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':albumId' => $albumId]);
    $pictures = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error: " . htmlspecialchars($e->getMessage()));
}

$pageTitle = "View Album - " . htmlspecialchars($album['Title']);
include 'header.php'; // Include the header
?>

<div class="container mt-5">
    <h1 class="text-primary"><?= htmlspecialchars($album['Title']) ?></h1>
    <p class="text-muted"><?= htmlspecialchars($album['Description'] ?: "No description available.") ?></p>
    
    <?php if ($pictures): ?>
        <div class="row g-4 mt-4">
            <?php foreach ($pictures as $picture): ?>
                <div class="col-md-4">
                    <div class="card shadow-sm">
                        <img src="uploads/<?= htmlspecialchars($picture['File_Name']) ?>" class="card-img-top" alt="<?= htmlspecialchars($picture['Title']) ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($picture['Title']) ?></h5>
                            <p class="card-text"><?= htmlspecialchars($picture['Description'] ?: "No description available.") ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-warning mt-4">No pictures found in this album.</div>
    <?php endif; ?>
</div>

<?php include 'footer.php'; // Include the footer ?>
