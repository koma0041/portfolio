<?php
include('db_connect.php');
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $studentId = trim($_POST['studentId']);
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $password = trim($_POST['password']);
    $confirmPassword = trim($_POST['confirmPassword']);

    if (empty($studentId)) $errors['studentId'] = "Student ID is required.";
    if (empty($name)) $errors['name'] = "Name is required.";
    if (!preg_match("/^\d{3}-\d{3}-\d{4}$/", $phone)) $errors['phone'] = "Phone format should be nnn-nnn-nnnn.";
    if (strlen($password) < 6 || !preg_match("/[A-Z]/", $password) || !preg_match("/[a-z]/", $password) || !preg_match("/\d/", $password)) {
        $errors['password'] = "Password must have 6 characters with an uppercase, a lowercase, and a digit.";
    }
    if ($password !== $confirmPassword) $errors['confirmPassword'] = "Passwords do not match.";

    if (empty($errors)) {
        $db = getDbConnection();
        $stmt = $db->prepare("SELECT * FROM Student WHERE StudentId = :studentId");
        $stmt->execute([':studentId' => $studentId]);

        if ($stmt->rowCount() > 0) {
            $errors['studentId'] = "This Student ID is already registered.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO Student (StudentId, Name, Phone, Password) VALUES (:studentId, :name, :phone, :password)");
            $stmt->execute([':studentId' => $studentId, ':name' => $name, ':phone' => $phone, ':password' => $hashedPassword]);
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
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php include('Header.php'); ?>

    <div class="container">
        <h2>Sign Up</h2>
        <form method="POST">
            <label for="studentId">Student ID:</label>
            <input type="text" name="studentId" id="studentId" value="<?php echo htmlspecialchars($studentId ?? ''); ?>">
            <?php if (isset($errors['studentId'])): ?><p class="error"><?php echo $errors['studentId']; ?></p><?php endif; ?>

            <label for="name">Name:</label>
            <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($name ?? ''); ?>">
            <?php if (isset($errors['name'])): ?><p class="error"><?php echo $errors['name']; ?></p><?php endif; ?>

            <label for="phone">Phone Number (nnn-nnn-nnnn):</label>
            <input type="text" name="phone" id="phone" value="<?php echo htmlspecialchars($phone ?? ''); ?>">
            <?php if (isset($errors['phone'])): ?><p class="error"><?php echo $errors['phone']; ?></p><?php endif; ?>

            <label for="password">Password:</label>
            <input type="password" name="password" id="password">
            <?php if (isset($errors['password'])): ?><p class="error"><?php echo $errors['password']; ?></p><?php endif; ?>

            <label for="confirmPassword">Password Again:</label>
            <input type="password" name="confirmPassword" id="confirmPassword">
            <?php if (isset($errors['confirmPassword'])): ?><p class="error"><?php echo $errors['confirmPassword']; ?></p><?php endif; ?>

            <div class="button-container">
                <button type="button" class="back-button" onclick="history.back();">Back</button>
                <button type="submit" class="next-button">Submit</button>
            </div>
        </form>
    </div>

    <?php include('Footer.php'); ?>
</body>
</html>
