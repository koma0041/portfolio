<?php
// Include database connection
require 'db_connection.php'; // Connect to the database

// Start the session
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form inputs
    $userId = trim($_POST['UserId']);
    $password = trim($_POST['Password']);

    // Input validation
    $errors = [];
    if (empty($userId)) {
        $errors[] = "User ID is required.";
    }
    if (empty($password)) {
        $errors[] = "Password is required.";
    }

    if (empty($errors)) {
        try {
            // Fetch user record from the database
            $sql = "SELECT Password FROM User WHERE UserId = :userId";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':userId', $userId);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                // Verify the password
                if (password_verify($password, $user['Password'])) {
                    // Password is correct, log the user in
                    $_SESSION['UserId'] = $userId; // Store user ID in session
                    header("Location: index.php"); // Redirect to dashboard
                    exit;
                } else {
                    $errors[] = "Invalid password.";
                }
            } else {
                $errors[] = "User ID not found.";
            }
        } catch (PDOException $e) {
            $errors[] = "Error: " . htmlspecialchars($e->getMessage());
        }
    }
}
$pageTitle = "User Login";
include 'header.php'; // Include the header
?>

<!-- Login Form -->
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <h1 class="text-center text-primary mb-4">Login</h1>
            <?php
            // Display validation or login errors
            if (!empty($errors)) {
                foreach ($errors as $error) {
                    echo "<div class='alert alert-danger'>" . htmlspecialchars($error) . "</div>";
                }
            }
            ?>
            <form method="POST" action="Login.php" class="shadow p-4 rounded bg-light">
                <div class="form-group mb-3">
                    <label for="UserId" class="form-label">User ID</label>
                    <input type="text" name="UserId" id="UserId" class="form-control" >
                </div>
                <div class="form-group mb-3">
                    <label for="Password" class="form-label">Password</label>
                    <input type="password" name="Password" id="Password" class="form-control" >
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg">Login</button>
                </div>
            </form>
            <div class="text-center mt-3">
                <p>Don't have an account?</p>
                <a href="NewUser.php" class="btn btn-secondary btn-lg">Create New Account</a>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; // Include the footer ?>
