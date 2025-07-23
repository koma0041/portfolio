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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $friendId = trim($_POST['FriendId']);
    $errors = [];

    // Validate input
    if (empty($friendId)) {
        $errors[] = "Please enter a User ID.";
    } elseif ($friendId === $userId) {
        $errors[] = "You cannot send a friend request to yourself.";
    } else {
        // Check if the UserId exists
        $sql = "SELECT * FROM User WHERE UserId = :friendId";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':friendId' => $friendId]);
        $friend = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$friend) {
            $errors[] = "The entered User ID does not exist.";
        } else {
            // Check for existing friendship or pending requests
            $sql = "SELECT * FROM Friendship 
                    WHERE (Friend_RequesterId = :userId AND Friend_RequesteeId = :friendId)
                       OR (Friend_RequesterId = :friendId AND Friend_RequesteeId = :userId)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':userId' => $userId, ':friendId' => $friendId]);
            $existingRequest = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existingRequest) {
                if ($existingRequest['Status'] === 'accepted') {
                    $errors[] = "You are already friends with this user.";
                } elseif ($existingRequest['Friend_RequesterId'] === $friendId) {
                    // Automatically establish friendship if the other user has already sent a request
                    $sql = "UPDATE Friendship SET Status = 'accepted' 
                            WHERE Friend_RequesterId = :friendId AND Friend_RequesteeId = :userId";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([':friendId' => $friendId, ':userId' => $userId]);
                    $successMessage = "You are now friends with {$friend['Name']}!";
                } else {
                    $errors[] = "You have already sent a friend request to this user.";
                }
            } else {
                // Send a new friend request
                $sql = "INSERT INTO Friendship (Friend_RequesterId, Friend_RequesteeId, Status) 
                        VALUES (:userId, :friendId, 'request')";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':userId' => $userId, ':friendId' => $friendId]);
                $successMessage = "Friend request sent successfully to {$friend['Name']}!";
            }
        }
    }
}
$pageTitle = "Add Friend";
include 'header.php';
?>

<div class="container mt-5">
    <h1 class="text-primary text-center">Send a Friend Request</h1>

    <!-- Success or Error Messages -->
    <?php if (isset($successMessage)): ?>
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

    <!-- Friend Request Form -->
    <form method="POST" action="AddFriend.php" class="mt-4">
        <div class="mb-3">
            <label for="FriendId" class="form-label">Enter User ID</label>
            <input type="text" name="FriendId" id="FriendId" class="form-control" placeholder="User ID" required>
        </div>
        <div class="text-center">
            <button type="submit" class="btn btn-primary btn-lg">Send Friend Request</button>
        </div>
    </form>

    <!-- Back Link -->
    <div class="text-center mt-4">
        <a href="MyFriends.php" class="btn btn-secondary">Back to My Friends</a>
    </div>
</div>

<?php include 'footer.php'; ?>
