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

// Fetch friends with shared albums
$sql = "SELECT DISTINCT u.UserId, u.Name
        FROM Friendship f
        INNER JOIN User u ON (f.Friend_RequesterId = u.UserId OR f.Friend_RequesteeId = u.UserId) AND u.UserId != :userId
        INNER JOIN Album a ON a.Owner_Id = u.UserId AND a.Accessibility_Code = 'shared'
        WHERE (f.Friend_RequesterId = :userId OR f.Friend_RequesteeId = :userId) AND f.Status = 'accepted'";
$stmt = $pdo->prepare($sql);
$stmt->execute([':userId' => $userId]);
$friends = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle selected friend's shared albums
$selectedFriendId = $_GET['friendId'] ?? '';
$albums = [];
if ($selectedFriendId) {
    // Fetch shared albums of the selected friend
    $sql = "SELECT Album_Id, Title FROM Album 
            WHERE Owner_Id = :friendId AND Accessibility_Code = 'shared'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':friendId' => $selectedFriendId]);
    $albums = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Handle selected album's pictures
$selectedAlbumId = $_GET['albumId'] ?? '';
$pictures = [];
if ($selectedAlbumId) {
    $sql = "SELECT * FROM Picture WHERE Album_Id = :albumId";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':albumId' => $selectedAlbumId]);
    $pictures = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch comments sorted alphabetically by author name
$comments = [];
if ($selectedAlbumId) {
    $sql = "SELECT c.Comment_Text, u.Name 
            FROM Comment c 
            INNER JOIN User u ON c.Author_Id = u.UserId 
            WHERE c.Picture_Id IN (SELECT Picture_Id FROM Picture WHERE Album_Id = :albumId)
            ORDER BY u.Name ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':albumId' => $selectedAlbumId]);
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Handle adding comments
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['AddComment'])) {
    $commentText = trim($_POST['CommentText']);
    $pictureId = $_POST['PictureId'] ?? null;

    if (!empty($commentText) && $pictureId) {
        $sql = "INSERT INTO Comment (Picture_Id, Author_Id, Comment_Text) 
                VALUES (:pictureId, :authorId, :commentText)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':pictureId' => $pictureId,
            ':authorId' => $userId,
            ':commentText' => $commentText
        ]);
        // Redirect to avoid duplicate submissions
        header("Location: FriendPictures.php?friendId=$selectedFriendId&albumId=$selectedAlbumId");
        exit;
    }
}

include 'header.php'; // Include the header
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Friend's Pictures</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f9f9f9;
        }
        h1 {
            font-family: 'Arial', sans-serif;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .thumbnail {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 5px;
        }
        .carousel img {
            border: 2px solid #ddd;
            border-radius: 5px;
        }
        .list-group-item {
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        textarea {
            resize: none;
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #004085;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-primary text-center">Friend's Pictures</h1>

        <!-- Friend Dropdown -->
        <form method="GET" action="FriendPictures.php" class="mb-4">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <label for="friend" class="form-label">Select Friend:</label>
                    <select class="form-select" name="friendId" id="friend" onchange="this.form.submit()">
                        <option value="">Choose a Friend</option>
                        <?php foreach ($friends as $friend): ?>
                            <option value="<?= $friend['UserId'] ?>" <?= $selectedFriendId == $friend['UserId'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($friend['Name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </form>

        <!-- Album Dropdown -->
        <?php if ($selectedFriendId && $albums): ?>
            <form method="GET" action="FriendPictures.php" class="mb-4">
                <div class="row justify-content-center">
                    <input type="hidden" name="friendId" value="<?= $selectedFriendId ?>">
                    <div class="col-md-6">
                        <label for="album" class="form-label">Select Album:</label>
                        <select class="form-select" name="albumId" id="album" onchange="this.form.submit()">
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
        <?php elseif ($selectedFriendId): ?>
            <div class="alert alert-warning text-center">This friend has no shared albums.</div>
        <?php endif; ?>

        <!-- Carousel and Pictures -->
        <?php if ($selectedAlbumId && $pictures): ?>
            <!-- Carousel -->
            <div id="pictureCarousel" class="carousel slide mb-5" data-bs-ride="carousel">
                <div class="carousel-inner">
                    <?php foreach ($pictures as $index => $picture): ?>
                        <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                            <img src="uploads/<?= htmlspecialchars($picture['File_Name']) ?>" 
                                 class="d-block w-100 img-fluid" 
                                 alt="<?= htmlspecialchars($picture['Title']) ?>" 
                                 style="max-height: 500px; object-fit: contain;">
                            <div class="carousel-caption">
                                <h5><?= htmlspecialchars($picture['Title']) ?></h5>
                                <p><?= htmlspecialchars($picture['Description']) ?></p>
                            </div>
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

            <!-- Thumbnail Gallery -->
            <div class="row">
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
                    <p>No comments yet for this album.</p>
                <?php endif; ?>

                <!-- Add Comment Form -->
                <form method="POST" action="FriendPictures.php?friendId=<?= $selectedFriendId ?>&albumId=<?= $selectedAlbumId ?>" class="mt-3">
                    <input type="hidden" name="PictureId" value="<?= $pictures[0]['Picture_Id'] ?? '' ?>">
                    <textarea class="form-control mb-3" name="CommentText" rows="3" placeholder="Add a comment" required></textarea>
                    <button type="submit" name="AddComment" class="btn btn-primary btn-sm">Add Comment</button>
                </form>
            </div>
        <?php elseif ($selectedAlbumId): ?>
            <div class="alert alert-warning text-center mt-4">No pictures found in this album.</div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php include 'footer.php'; // Include the footer ?>