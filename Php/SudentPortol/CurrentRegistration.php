<?php
session_start();
include('db_connect.php');

// Check if user is logged in
if (!isset($_SESSION['studentId'])) {
    header("Location: Login.php");
    exit();
}

$db = getDbConnection();
$studentId = $_SESSION['studentId'];

// Fetch registered courses for the user
$stmt = $db->prepare("SELECT Course.CourseCode, Course.Title, Course.WeeklyHours, Semester.SemesterCode 
                      FROM Registration 
                      JOIN Course ON Course.CourseCode = Registration.CourseCode 
                      JOIN Semester ON Semester.SemesterCode = Registration.SemesterCode 
                      WHERE Registration.StudentId = :studentId");
$stmt->execute([':studentId' => $studentId]);
$registrations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle course deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deleteCourses'])) {
    $deleteCourses = $_POST['deleteCourses'];
    $deleteStmt = $db->prepare("DELETE FROM Registration WHERE StudentId = :studentId AND CourseCode = :courseCode AND SemesterCode = :semesterCode");

    foreach ($deleteCourses as $course) {
        list($courseCode, $semesterCode) = explode("|", $course);
        $deleteStmt->execute([':studentId' => $studentId, ':courseCode' => $courseCode, ':semesterCode' => $semesterCode]);
    }
    header("Location: CurrentRegistration.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Current Registration</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script>
        function confirmDeletion() {
            return confirm("Are you sure you want to delete the selected registrations?");
        }
    </script>
</head>
<body>
    <?php include('Header_1.php'); ?>

    <main class="container">
        <h1 class="display-4 text-center">Your Current Registrations</h1>

        <form method="POST" onsubmit="return confirmDeletion();" class="border p-4 rounded" style="box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">
               <table class="table table-hover">
                <thead class="thead-light">
                    <tr>
                        <th>Course Code</th>
                        <th>Title</th>
                        <th>Weekly Hours</th>
                        <th>Semester</th>
                        <th>Select</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($registrations as $registration): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($registration['CourseCode']); ?></td>
                            <td><?php echo htmlspecialchars($registration['Title']); ?></td>
                            <td><?php echo htmlspecialchars($registration['WeeklyHours']); ?></td>
                            <td><?php echo htmlspecialchars($registration['SemesterCode']); ?></td>
                            <td><input type="checkbox" name="deleteCourses[]" value="<?php echo $registration['CourseCode'] . '|' . $registration['SemesterCode']; ?>"></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="form-group">
                <button type="button" class="btn btn-secondary" onclick="history.back();">Back</button>
                <button type="submit" class="btn btn-danger">Delete Selected</button>
            </div>
        </form>
    </main>

    <?php include('Footer.php'); ?>
</body>
</html>
