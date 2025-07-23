<?php
// Include database connection
require 'db_connection.php'; // Connect to the database

$pageTitle = "Register New User"; // Page title
include 'header.php'; // Include the header

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form inputs
    $userId = trim($_POST['UserId']);
    $name = trim($_POST['Name']);
    $phone = trim($_POST['Phone']);
    $password = trim($_POST['Password']);
    $confirmPassword = trim($_POST['ConfirmPassword']);
    
    // Input validation
    $errors = [];
    if (empty($userId) || strlen($userId) > 16) {
        $errors['userId'] = "User ID is required and must be less than 16 characters.";
    }
    if (empty($name)) {
        $errors['name'] = "Name is required.";
    }
    if (!preg_match("/^\d{3}-\d{3}-\d{4}$/", $phone)) {
        $errors['phone'] = "Phone format should be nnn-nnn-nnnn.";
    }
    if (strlen($password) < 6 || !preg_match("/[A-Z]/", $password) || !preg_match("/[a-z]/", $password) || !preg_match("/\d/", $password)) {
        $errors['password'] = "Password must have at least 6 characters with an uppercase letter, a lowercase letter, and a digit.";
    }
    if ($password !== $confirmPassword) {
        $errors['confirmPassword'] = "Passwords do not match.";
    }
    
    if (empty($errors)) {
        try {
            // Hash the password
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            
            // Prepare SQL statement
            $sql = "INSERT INTO User (UserId, Name, Phone, Password) VALUES (:userId, :name, :phone, :password)";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':userId', $userId);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':password', $hashedPassword);
            
            // Execute the query
            $stmt->execute();
            
            // Success message
            echo "<div class='alert alert-success'>User successfully registered!</div>";
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { // Duplicate entry error
                echo "<div class='alert alert-danger'>Error: User ID already exists.</div>";
            } else {
                echo "<div class='alert alert-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
            }
        }
    } else {
        // Display validation errors
        foreach ($errors as $error) {
            echo "<div class='alert alert-danger'>" . htmlspecialchars($error) . "</div>";
        }
    }
}
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <h1 class="text-center mb-4">Register New User</h1>
            <form method="POST" action="NewUser.php">
                <div class="form-group mb-3">
                    <label for="UserId">User ID</label>
                    <input type="text" name="UserId" id="UserId" class="form-control" required maxlength="16">
                </div>
                <div class="form-group mb-3">
                    <label for="Name">Name</label>
                    <input type="text" name="Name" id="Name" class="form-control">
                </div>
                <div class="form-group mb-3">
                    <label for="Phone">Phone</label>
                    <input type="text" name="Phone" id="Phone" class="form-control" placeholder="nnn-nnn-nnnn">
                </div>
                <div class="form-group mb-3">
                    <label for="Password">Password</label>
                    <input type="password" name="Password" id="Password" class="form-control">
                </div>
                <div class="form-group mb-3">
                    <label for="ConfirmPassword">Confirm Password</label>
                    <input type="password" name="ConfirmPassword" id="ConfirmPassword" class="form-control">
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg">Register</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'footer.php'; // Include the footer ?>
