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
    <link rel="stylesheet" href="styles.css">
    <script>
        function confirmDeletion() {
            return confirm("Are you sure you want to delete the selected registrations?");
        }
    </script>
</head>
<body>
    <?php include('Header.php'); ?>

    <div class="container">
        <h2>Your Current Registrations</h2>

        <form method="POST" onsubmit="return confirmDeletion();">
            <table>
                <tr>
                    <th>Course Code</th>
                    <th>Title</th>
                    <th>Weekly Hours</th>
                    <th>Semester</th>
                    <th>Select</th>
                </tr>
                <?php foreach ($registrations as $registration): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($registration['CourseCode']); ?></td>
                        <td><?php echo htmlspecialchars($registration['Title']); ?></td>
                        <td><?php echo htmlspecialchars($registration['WeeklyHours']); ?></td>
                        <td><?php echo htmlspecialchars($registration['SemesterCode']); ?></td>
                        <td><input type="checkbox" name="deleteCourses[]" value="<?php echo $registration['CourseCode'] . '|' . $registration['SemesterCode']; ?>"></td>
                    </tr>
                <?php endforeach; ?>
            </table>

            <div class="button-container">
                <button type="button" class="back-button" onclick="history.back();">Back</button>
                <button type="submit" class="next-button">Delete Selected</button>
            </div>
        </form>
    </div>

    <?php include('Footer.php'); ?>
</body>
</html>
