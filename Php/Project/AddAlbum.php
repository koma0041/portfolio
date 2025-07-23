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

// Fetch accessibility options from the database
$accessibilityOptions = $pdo->query("SELECT * FROM Accessibility")->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['Title']);
    $accessibility = trim($_POST['Accessibility']);
    $description = trim($_POST['Description'] ?? '');

    // Validate inputs
    $errors = [];
    if (empty($title)) {
        $errors[] = "Album title is required.";
    }
    if (empty($accessibility)) {
        $errors[] = "Accessibility selection is required.";
    }

    if (empty($errors)) {
        try {
            // Insert the new album into the database
            $sql = "INSERT INTO Album (Title, Description, Owner_Id, Accessibility_Code) 
                    VALUES (:title, :description, :ownerId, :accessibility)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':title' => $title,
                ':description' => $description,
                ':ownerId' => $userId,
                ':accessibility' => $accessibility
            ]);
            $successMessage = "Album created successfully!";
        } catch (PDOException $e) {
            $errorMessage = "Error: " . $e->getMessage();
        }
    }
}
$pageTitle = "Add New Album";
include 'header.php';
?>

<div class="container mt-5">
    <h1 class="text-primary text-center">Create a New Album</h1>

    <!-- Success and Error Messages -->
    <?php if (isset($successMessage)): ?>
        <div class="alert alert-success text-center"><?= htmlspecialchars($successMessage) ?></div>
    <?php elseif (isset($errorMessage)): ?>
        <div class="alert alert-danger text-center"><?= htmlspecialchars($errorMessage) ?></div>
    <?php elseif (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- Album Form -->
    <form method="POST" action="AddAlbum.php" class="mt-4">
        <div class="mb-3">
            <label for="Title" class="form-label">Album Title</label>
            <input type="text" name="Title" id="Title" class="form-control" placeholder="Enter album title" required>
        </div>

        <div class="mb-3">
            <label for="Accessibility" class="form-label">Accessibility</label>
            <select name="Accessibility" id="Accessibility" class="form-select" required>
                <option value="">Select Accessibility</option>
                <?php foreach ($accessibilityOptions as $option): ?>
                    <option value="<?= $option['Accessibility_Code'] ?>">
                        <?= htmlspecialchars($option['Description']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="Description" class="form-label">Description (Optional)</label>
            <textarea name="Description" id="Description" class="form-control" rows="3" placeholder="Enter album description"></textarea>
        </div>

        <div class="text-center">
            <button type="submit" class="btn btn-primary btn-lg">Create Album</button>
        </div>
    </form>

    <!-- Back Link -->
    <div class="text-center mt-4">
        <a href="MyAlbums.php" class="btn btn-secondary">Back to My Albums</a>
    </div>
</div>

<?php include 'footer.php'; ?>
