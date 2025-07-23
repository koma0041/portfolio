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
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <?php include('Header.php'); ?>

    <main class="container">
        <h1 class="display-4 text-center">Login</h1>
        <p class="text-center">If you are new, you need to <a href='NewUser.php'>register</a></p>
        <form method="post" action="Login.php" class="border p-4 rounded" style="box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">
            <div class="form-group row">
                <label for="studentId" class="col-sm-3 col-form-label">Student ID:</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" name="studentId" id="studentId" value="<?php echo htmlspecialchars($studentId ?? ''); ?>" />
                    <?php if (isset($errors['studentId'])): ?>
                        <div class="text-danger"><?php echo htmlspecialchars($errors['studentId']); ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-group row">
                <label for="password" class="col-sm-3 col-form-label">Password:</label>
                <div class="col-sm-9">
                    <input type="password" class="form-control" name="password" id="password" />
                    <?php if (isset($errors['password'])): ?>
                        <div class="text-danger"><?php echo htmlspecialchars($errors['password']); ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (isset($errors['login'])): ?>
                <div class="form-group row">
                    <div class="col-sm-9 offset-sm-3">
                        <div class="text-danger"><?php echo htmlspecialchars($errors['login']); ?></div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="form-group row">
                <div class="col-sm-9 offset-sm-3">
                    <button type="submit" class="btn btn-primary">Login</button>
                    <button type="reset" class="btn btn-secondary">Clear</button>
                </div>
            </div>
        </form>
    </main>

    <?php include('Footer.php'); ?>
</body>
</html>
