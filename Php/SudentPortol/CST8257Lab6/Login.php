<?php
include('db_connect.php');
session_start();

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $studentId = trim($_POST['studentId']);
    $password = trim($_POST['password']);

    if (empty($studentId)) $errors['studentId'] = "Student ID is required.";
    if (empty($password)) $errors['password'] = "Password is required.";

    if (empty($errors)) {
        $db = getDbConnection();
        $stmt = $db->prepare("SELECT * FROM Student WHERE StudentId = :studentId");
        $stmt->execute([':studentId' => $studentId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['Password'])) {
            $_SESSION['studentId'] = $studentId;
            header("Location: CourseSelection.php");
            exit();
        } else {
            $errors['login'] = "Incorrect student ID and/or password!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log In</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php include('Header.php'); ?>

    <div class="container">
        <h2>Log In</h2>
        <form method="POST">
            <label for="studentId">Student ID:</label>
            <input type="text" name="studentId" id="studentId" value="<?php echo htmlspecialchars($studentId ?? ''); ?>">
            <?php if (isset($errors['studentId'])): ?><p class="error"><?php echo $errors['studentId']; ?></p><?php endif; ?>

            <label for="password">Password:</label>
            <input type="password" name="password" id="password">
            <?php if (isset($errors['password'])): ?><p class="error"><?php echo $errors['password']; ?></p><?php endif; ?>

            <?php if (isset($errors['login'])): ?><p class="error"><?php echo $errors['login']; ?></p><?php endif; ?>

            <div class="button-container">
                <button type="button" class="back-button" onclick="history.back();">Back</button>
                <button type="submit" class="next-button">Submit</button>
            </div>
        </form>
    </div>

    <?php include('Footer.php'); ?>
</body>
</html>
