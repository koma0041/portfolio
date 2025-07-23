<?php
include('db_connect.php');
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $studentId = trim($_POST['studentId']);
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $password = trim($_POST['password']);
    $confirmPassword = trim($_POST['confirmPassword']);

        // Validation
        if (empty($studentId)) $errors['studentId'] = "Student ID is required.";
        if (empty($name)) $errors['name'] = "Name is required.";
        if (!preg_match("/^\d{3}-\d{3}-\d{4}$/", $phone)) $errors['phone'] = "Phone format should be nnn-nnn-nnnn.";
        if (strlen($password) < 6 || !preg_match("/[A-Z]/", $password) || !preg_match("/[a-z]/", $password) || !preg_match("/\d/", $password)) {
            $errors['password'] = "Password must have at least 6 characters with an uppercase, a lowercase, and a digit.";
        }
        if ($password !== $confirmPassword) $errors['confirmPassword'] = "Passwords do not match.";

    // Database insertion if no errors
    if (empty($errors)) {
        $db = getDbConnection();
        $stmt = $db->prepare("SELECT * FROM Student WHERE StudentId = :studentId");
        $stmt->execute([':studentId' => $studentId]);

        if ($stmt->rowCount() > 0) {
            $errors['studentId'] = "This Student ID is already registered.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO Student (StudentId, Name, Phone, Password) VALUES (:studentId, :name, :phone, :password)");
            $stmt->execute([
                ':studentId' => $studentId,
                ':name' => $name,
                ':phone' => $phone,
                ':password' => $hashedPassword
            ]);
            header("Location: CourseSelection.php");
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .error {
            font-size: 0.9rem;
            color: red;
        }
        .container {
            margin-top: 50px;
            max-width: 600px;
        }
        .button-container {
            text-align: right;
        }
    </style>
</head>
<body>
    <?php include('Header.php'); ?>

    <div class="container">
        <h1 class="display-4 text-center mb-4">New User Signup</h1>
        <form method="POST" class="border p-4 rounded" style="box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">
            <div class="form-group">
                <label for="studentId">Student ID:</label>
                <input type="text" name="studentId" id="studentId" class="form-control" value="<?php echo htmlspecialchars($studentId ?? ''); ?>">
                <?php if (isset($errors['studentId'])): ?>
                    <div class="error"><?php echo $errors['studentId']; ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="name">Name:</label>
                    <input type="text" name="name" id="name" class="form-control" value="<?php echo htmlspecialchars($name ?? ''); ?>">
                <?php if (isset($errors['name'])): ?>
                    <div class="error"><?php echo $errors['name']; ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="phone">Phone Number (nnn-nnn-nnnn):</label>
                <input type="text" name="phone" id="phone" class="form-control" value="<?php echo htmlspecialchars($phone ?? ''); ?>">
                <?php if (isset($errors['phone'])): ?>
                    <div class="error"><?php echo $errors['phone']; ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" name="password" id="password" class="form-control">
                <?php if (isset($errors['password'])): ?>
                    <div class="error"><?php echo $errors['password']; ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="confirmPassword">Password Again:</label>
                <input type="password" name="confirmPassword" id="confirmPassword" class="form-control">
                <?php if (isset($errors['confirmPassword'])): ?>
                    <div class="error"><?php echo $errors['confirmPassword']; ?></div>
                <?php endif; ?>
            </div>

            <div class="button-container mt-3">
                <button type="button" class="btn btn-secondary" onclick="history.back();">Back</button>
                <button type="submit" class="btn btn-primary">Submit</button>
            </div>
        </form>
    </div>

    <?php include('Footer.php'); ?>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.4.4/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
