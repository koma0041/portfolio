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

// Fetch user's albums for the dropdown
$sql = "SELECT Album_Id, Title FROM Album WHERE Owner_Id = :userId";
$stmt = $pdo->prepare($sql);
$stmt->execute([':userId' => $userId]);
$albums = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle album selection and fetch pictures
$selectedAlbumId = $_GET['albumId'] ?? '';
$pictures = [];
if ($selectedAlbumId) {
    // Fetch pictures for the selected album
    $sql = "SELECT * FROM Picture WHERE Album_Id = :albumId";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':albumId' => $selectedAlbumId]);
    $pictures = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Handle adding comments
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['AddComment'])) {
    $commentText = trim($_POST['CommentText']);
    $pictureId = $_POST['PictureId'];

    if (!empty($commentText)) {
        $sql = "INSERT INTO Comment (Picture_Id, Author_Id, Comment_Text) 
                VALUES (:pictureId, :authorId, :commentText)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':pictureId' => $pictureId,
            ':authorId' => $userId,
            ':commentText' => $commentText
        ]);
        header("Location: MyPictures.php?albumId=$selectedAlbumId"); // Reload the page to avoid duplicate submissions
        exit;
    }
}

$pageTitle = "My Pictures";
include 'header.php';
?>

<div class="container mt-5">
    <h1 class="text-primary text-center">My Pictures</h1>

    <!-- Album Dropdown -->
    <form method="GET" action="MyPictures.php" class="mb-4">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <select class="form-select" name="albumId" onchange="this.form.submit()">
                    <option value="">Choose an Album</option>
                    <?php foreach ($albums as $album): ?>
                        <option value="<?= $album['Album_Id'] ?>" <?= $selectedAlbumId == $album['Album_Id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($album['Title']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </form>

    <?php if ($selectedAlbumId && $pictures): ?>
        <!-- Big Image Display -->
        <div id="pictureCarousel" class="carousel slide mb-3" data-bs-ride="carousel">
            <div class="carousel-inner" style="max-height: 500px;">
                <?php foreach ($pictures as $index => $picture): ?>
                    <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                        <img src="uploads/<?= htmlspecialchars($picture['File_Name']) ?>" 
                             class="d-block w-100 img-fluid rounded" 
                             alt="<?= htmlspecialchars($picture['Title']) ?>" 
                             style="max-height: 400px; object-fit: contain;">
                    </div>
                <?php endforeach; ?>
            </div>
            <!-- Carousel controls -->
            <button class="carousel-control-prev" type="button" data-bs-target="#pictureCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#pictureCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>

        <!-- Title and Description -->
        <div class="mt-4 text-center">
            <h3 class="text-secondary"><?= htmlspecialchars($pictures[0]['Title'] ?? "Picture Title") ?></h3>
            <p class="text-muted"><?= htmlspecialchars($pictures[0]['Description'] ?? "No description available.") ?></p>
        </div>

        <!-- Thumbnail Gallery -->
        <div class="row mt-3">
            <?php foreach ($pictures as $index => $picture): ?>
                <div class="col-2">
                    <a href="#pictureCarousel" data-bs-slide-to="<?= $index ?>">
                        <img src="uploads/<?= htmlspecialchars($picture['File_Name']) ?>" 
                             class="img-thumbnail" 
                             alt="<?= htmlspecialchars($picture['Title']) ?>" 
                             style="height: 80px; object-fit: cover;">
                    </a>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Comments Section -->
        <div class="mt-5">
            <h2 class="text-secondary">Comments</h2>
            <?php
            // Fetch comments for the first picture (or currently active picture)
            $firstPictureId = $pictures[0]['Picture_Id'];
            $sql = "SELECT c.Comment_Text, u.Name 
                    FROM Comment c 
                    INNER JOIN User u ON c.Author_Id = u.UserId 
                    WHERE c.Picture_Id = :pictureId 
                    ORDER BY c.Comment_Id DESC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':pictureId' => $firstPictureId]);
            $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
            ?>
            <?php if ($comments): ?>
                <ul class="list-group">
                    <?php foreach ($comments as $comment): ?>
                        <li class="list-group-item">
                            <strong><?= htmlspecialchars($comment['Name']) ?>:</strong>
                            <?= htmlspecialchars($comment['Comment_Text']) ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>No comments yet for this picture.</p>
            <?php endif; ?>

            <!-- Add Comment Form -->
            <form method="POST" action="MyPictures.php?albumId=<?= $selectedAlbumId ?>" class="mt-3">
                <input type="hidden" name="PictureId" value="<?= $firstPictureId ?>">
                <textarea class="form-control mb-3" name="CommentText" rows="3" placeholder="Add a comment"></textarea>
                <button type="submit" name="AddComment" class="btn btn-primary btn-sm">Add Comment</button>
            </form>
        </div>
    <?php elseif ($selectedAlbumId): ?>
        <div class="alert alert-warning text-center mt-4">No pictures found in this album.</div>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>
