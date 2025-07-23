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

// Handle friend request actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['AcceptRequest'])) {
        $requesterId = $_POST['AcceptRequest'];
        $sql = "UPDATE Friendship SET Status = 'accepted' 
                WHERE Friend_RequesterId = :requesterId AND Friend_RequesteeId = :userId";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':requesterId' => $requesterId, ':userId' => $userId]);
        $successMessage = "Friend request accepted!";
    } elseif (isset($_POST['DenyRequest'])) {
        $requesterId = $_POST['DenyRequest'];
        $sql = "DELETE FROM Friendship 
                WHERE Friend_RequesterId = :requesterId AND Friend_RequesteeId = :userId AND Status = 'request'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':requesterId' => $requesterId, ':userId' => $userId]);
        $successMessage = "Friend request denied!";
    } elseif (isset($_POST['RemoveFriend'])) {
        $friendId = $_POST['RemoveFriend'];
        $sql = "DELETE FROM Friendship 
                WHERE (Friend_RequesterId = :userId AND Friend_RequesteeId = :friendId) 
                   OR (Friend_RequesterId = :friendId AND Friend_RequesteeId = :userId) AND Status = 'accepted'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':userId' => $userId, ':friendId' => $friendId]);
        $successMessage = "Friend removed successfully!";
    }
}

// Fetch friends
$sql = "SELECT u.UserId, u.Name, COUNT(a.Album_Id) AS SharedAlbums 
        FROM Friendship f
        INNER JOIN User u ON (f.Friend_RequesterId = u.UserId OR f.Friend_RequesteeId = u.UserId) AND u.UserId != :userId
        LEFT JOIN Album a ON a.Owner_Id = u.UserId AND a.Accessibility_Code = 'shared'
        WHERE (f.Friend_RequesterId = :userId OR f.Friend_RequesteeId = :userId) AND f.Status = 'accepted'
        GROUP BY u.UserId";
$stmt = $pdo->prepare($sql);
$stmt->execute([':userId' => $userId]);
$friends = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch friend requests
$sql = "SELECT u.UserId, u.Name 
        FROM Friendship f
        INNER JOIN User u ON f.Friend_RequesterId = u.UserId
        WHERE f.Friend_RequesteeId = :userId AND f.Status = 'request'";
$stmt = $pdo->prepare($sql);
$stmt->execute([':userId' => $userId]);
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = "My Friends";
include 'header.php';
?>

<div class="container mt-5">
    <h1 class="text-primary text-center">My Friends</h1>

    <!-- Success Messages -->
    <?php if (isset($successMessage)): ?>
        <div class="alert alert-success text-center"><?= htmlspecialchars($successMessage) ?></div>
    <?php endif; ?>

    <!-- Friends Section -->
    <h2 class="text-secondary mt-4">Friends</h2>
    <?php if ($friends): ?>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr class="table-primary">
                        <th>Name</th>
                        <th>Shared Albums</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($friends as $friend): ?>
                        <tr>
                            <td>
                                <a href="FriendPictures.php?friendId=<?= $friend['UserId'] ?>" class="text-decoration-none text-primary">
                                    <?= htmlspecialchars($friend['Name']) ?>
                                </a>
                            </td>
                            <td><?= $friend['SharedAlbums'] ?></td>
                            <td>
                                <form method="POST" action="MyFriends.php" class="d-inline">
                                    <button type="submit" name="RemoveFriend" value="<?= $friend['UserId'] ?>" class="btn btn-danger btn-sm">
                                        Remove Friend
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-warning text-center">You have no friends yet.</div>
    <?php endif; ?>

    <!-- Friend Requests Section -->
    <h2 class="text-secondary mt-5">Friend Requests</h2>
    <?php if ($requests): ?>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr class="table-success">
                        <th>Name</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($requests as $request): ?>
                        <tr>
                            <td><?= htmlspecialchars($request['Name']) ?></td>
                            <td>
                                <form method="POST" action="MyFriends.php" class="d-inline">
                                    <button type="submit" name="AcceptRequest" value="<?= $request['UserId'] ?>" class="btn btn-success btn-sm">
                                        Accept
                                    </button>
                                    <button type="submit" name="DenyRequest" value="<?= $request['UserId'] ?>" class="btn btn-danger btn-sm">
                                        Deny
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info text-center">No pending friend requests.</div>
    <?php endif; ?>

    <div class="text-center mt-4">
        <a href="AddFriend.php" class="btn btn-primary">Add Friend</a>
    </div>
</div>

<?php include 'footer.php'; ?>
+