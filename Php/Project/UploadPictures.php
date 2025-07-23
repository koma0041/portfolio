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

// Fetch user's albums
$sql = "SELECT Album_Id, Title FROM Album WHERE Owner_Id = :userId";
$stmt = $pdo->prepare($sql);
$stmt->execute([':userId' => $userId]);
$albums = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['pictures'])) {
    $albumId = $_POST['Album'];
    $title = trim($_POST['Title']);
    $description = trim($_POST['Description']);
    $files = $_FILES['pictures'];

    // Validate input
    $errors = [];
    if (empty($albumId)) {
        $errors[] = "Please select an album.";
    }
    if (!isset($files['name']) || empty($files['name'][0])) {
        $errors[] = "Please upload at least one picture.";
    }

    // Process uploaded files
    if (empty($errors)) {
        $uploadDir = 'uploads/'; // Directory to store uploaded pictures
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true); // Create the directory if it doesn't exist
        }

        foreach ($files['name'] as $index => $fileName) {
            $fileTmpPath = $files['tmp_name'][$index];
            $fileSize = $files['size'][$index];
            $fileError = $files['error'][$index];

            // Validate file
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            if (!in_array($fileExtension, $allowedExtensions)) {
                $errors[] = "Invalid file type: $fileName";
                continue;
            }

            if ($fileError !== UPLOAD_ERR_OK) {
                $errors[] = "Error uploading file: $fileName";
                continue;
            }

            // Move file to upload directory
            $newFileName = uniqid() . '.' . $fileExtension;
            $filePath = $uploadDir . $newFileName;
            if (move_uploaded_file($fileTmpPath, $filePath)) {
                // Insert picture record into the database
                $sql = "INSERT INTO Picture (Album_Id, File_Name, Title, Description) 
                        VALUES (:albumId, :fileName, :title, :description)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':albumId' => $albumId,
                    ':fileName' => $newFileName,
                    ':title' => $title ?: $fileName, // Use original file name if title is not provided
                    ':description' => $description
                ]);
            } else {
                $errors[] = "Failed to save file: $fileName";
            }
        }
    }

    if (empty($errors)) {
        $successMessage = "Pictures uploaded successfully!";
    }
}
$pageTitle = "Upload Pictures";
include 'header.php';
?>

<div class="container mt-5">
    <h1 class="text-primary text-center">Upload Pictures</h1>

    <!-- Success and Error Messages -->
    <?php if (!empty($successMessage)): ?>
        <div class="alert alert-success text-center"><?= htmlspecialchars($successMessage) ?></div>
    <?php elseif (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- Upload Form -->
    <form method="POST" action="UploadPictures.php" enctype="multipart/form-data" class="mt-4">
        <div class="mb-3">
            <label for="Album" class="form-label">Select Album</label>
            <select name="Album" id="Album" class="form-select" required>
                <option value="">Choose an Album</option>
                <?php foreach ($albums as $album): ?>
                    <option value="<?= $album['Album_Id'] ?>"><?= htmlspecialchars($album['Title']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="Title" class="form-label">Picture Title (Optional)</label>
            <input type="text" name="Title" id="Title" class="form-control" placeholder="Enter picture title">
        </div>

        <div class="mb-3">
            <label for="Description" class="form-label">Picture Description (Optional)</label>
            <textarea name="Description" id="Description" class="form-control" rows="3" placeholder="Enter picture description"></textarea>
        </div>

        <div class="mb-3">
            <label for="pictures" class="form-label">Choose Pictures</label>
            <input type="file" name="pictures[]" id="pictures" class="form-control" multiple accept="image/*">
        </div>

        <div class="text-center">
            <button type="submit" class="btn btn-primary btn-lg">Upload Pictures</button>
        </div>
    </form>

    <div class="text-center mt-4">
        <a href="MyAlbums.php" class="btn btn-secondary">Back to My Albums</a>
    </div>
</div>

<?php include 'footer.php'; ?>
