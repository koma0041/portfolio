<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$loggedIn = isset($_SESSION['UserId']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Social Media Project'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet"> <!-- Link to your stylesheet -->
</head>
<body>
<div class="wrapper d-flex flex-column min-vh-100"> <!-- Wrapper starts here -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="Index.php">
                <img src="logo.png" alt="Social Media Logo" style="width: 100px; height: auto;">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="Index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="MyFriends.php">My Friends</a></li>
                    <li class="nav-item"><a class="nav-link" href="MyAlbums.php">My Albums</a></li>
                    <li class="nav-item"><a class="nav-link" href="MyPictures.php">My Pictures</a></li> <!-- Added My Pictures -->
                    <li class="nav-item"><a class="nav-link" href="UploadPictures.php">Upload Pictures</a></li>
                </ul>
                <ul class="navbar-nav">
                    <?php if ($loggedIn): ?>
                        <li class="nav-item"><a class="nav-link" href="Logout.php">Log Out</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="Login.php">Log In</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    <main class="flex-fill container mt-4">
