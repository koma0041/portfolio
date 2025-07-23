<?php
// Include database connection
require 'db_connection.php';

// Start session and check login
session_start();
if (!isset($_SESSION['UserId'])) {
    header("Location: Login.php");
    exit;
}

// Get the logged-in user's ID
$userId = $_SESSION['UserId'];

// Handle form submissions for accessibility changes or album deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['SaveChanges'])) {
        // Update album accessibility
        foreach ($_POST['accessibility'] as $albumId => $accessibilityCode) {
            $sql = "UPDATE Album SET Accessibility_Code = :accessibility WHERE Album_Id = :albumId AND Owner_Id = :userId";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':accessibility' => $accessibilityCode,
                ':albumId' => $albumId,
                ':userId' => $userId
            ]);
        }
        $successMessage = "Changes saved successfully!";
    } elseif (isset($_POST['DeleteAlbum'])) {
        // Delete album and its pictures
        $albumId = $_POST['DeleteAlbum'];
        // First, delete pictures
        $pdo->prepare("DELETE FROM Picture WHERE Album_Id = :albumId")->execute([':albumId' => $albumId]);
        // Then, delete the album
        $pdo->prepare("DELETE FROM Album WHERE Album_Id = :albumId AND Owner_Id = :userId")->execute([':albumId' => $albumId, ':userId' => $userId]);
        $successMessage = "Album deleted successfully!";
    }
}

// Fetch the user's albums
$sql = "SELECT a.Album_Id, a.Title, a.Accessibility_Code, COUNT(p.Picture_Id) AS PictureCount
        FROM Album a
        LEFT JOIN Picture p ON a.Album_Id = p.Album_Id
        WHERE a.Owner_Id = :userId
        GROUP BY a.Album_Id";
$stmt = $pdo->prepare($sql);
$stmt->execute([':userId' => $userId]);
$albums = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch accessibility options
$accessibilityOptions = $pdo->query("SELECT * FROM Accessibility")->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = "My Albums";
include 'header.php';
?>

<div class="container mt-5">
    <div class="text-center">
        <h1 class="text-primary">My Albums</h1>
        <p class="text-muted">Browse, manage, and update your photo albums.</p>
        <a href="AddAlbum.php" class="btn btn-success btn-lg my-3">Create a New Album</a>
    </div>

    <?php if (isset($successMessage)): ?>
        <div class="alert alert-success text-center">
            <?= htmlspecialchars($successMessage) ?>
        </div>
    <?php endif; ?>

    <?php if ($albums): ?>
        <form method="POST" action="MyAlbums.php">
            <div class="row">
                <?php foreach ($albums as $album): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card shadow">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($album['Title']) ?></h5>
                                <p class="card-text">
                                    <strong>Pictures:</strong> <?= $album['PictureCount'] ?><br>
                                    <strong>Accessibility:</strong>
                                    <select name="accessibility[<?= $album['Album_Id'] ?>]" class="form-select mt-2">
                                        <?php foreach ($accessibilityOptions as $option): ?>
                                            <option value="<?= $option['Accessibility_Code'] ?>" <?= $album['Accessibility_Code'] === $option['Accessibility_Code'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($option['Description']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </p>
                                <div class="d-flex justify-content-between">
                                    <a href="MyPictures.php?albumId=<?= $album['Album_Id'] ?>" class="btn btn-primary btn-sm">View Album</a>
                                    <button type="submit" name="DeleteAlbum" value="<?= $album['Album_Id'] ?>" class="btn btn-danger btn-sm">Delete</button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="text-center mt-3">
                <button type="submit" name="SaveChanges" class="btn btn-primary btn-lg">Save Changes</button>
            </div>
        </form>
    <?php else: ?>
        <div class="alert alert-warning text-center mt-4">No albums found. Create a new album to get started!</div>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>
