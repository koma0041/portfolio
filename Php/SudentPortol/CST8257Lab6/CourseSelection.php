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

// Fetch available semesters for dropdown
$semestersStmt = $db->prepare("SELECT SemesterCode, Term, Year FROM Semester ORDER BY Year DESC, Term DESC");
$semestersStmt->execute();
$semesters = $semestersStmt->fetchAll(PDO::FETCH_ASSOC);

// Handle semester selection
$selectedSemester = $_POST['semesterCode'] ?? '';
$remainingHours = 16;
$totalWeeklyHours = 0;

// Fetch registered weekly hours and courses available for the selected semester
if ($selectedSemester) {
    // Get total weekly hours registered
    $hoursStmt = $db->prepare("SELECT SUM(WeeklyHours) as totalWeeklyHours FROM Registration 
                                JOIN Course ON Registration.CourseCode = Course.CourseCode 
                                WHERE StudentId = :studentId AND SemesterCode = :semesterCode");
    $hoursStmt->execute([':studentId' => $studentId, ':semesterCode' => $selectedSemester]);
    $totalWeeklyHours = $hoursStmt->fetchColumn() ?? 0;
    $remainingHours = max(16 - $totalWeeklyHours, 0);

    // Get available courses for the selected semester
    $coursesStmt = $db->prepare("SELECT Course.CourseCode Code, Title, WeeklyHours 
                                FROM Course 
                                INNER JOIN CourseOffer ON Course.CourseCode = CourseOffer.CourseCode 
                                WHERE CourseOffer.SemesterCode = :semesterCode 
                                AND Course.CourseCode NOT IN 
                                    (SELECT CourseCode FROM Registration WHERE StudentId = :studentId)");
    $coursesStmt->execute([':semesterCode' => $selectedSemester, ':studentId' => $studentId]);
    $courses = $coursesStmt->fetchAll(PDO::FETCH_ASSOC);
}

// Handle course registration on form submission
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selectedCourses'])) {
    $selectedCourses = $_POST['selectedCourses'];
    $selectedCoursesHours = 0;

    foreach ($selectedCourses as $courseCode) {
        foreach ($courses as $course) {
            if ($course['Code'] === $courseCode) {
                $selectedCoursesHours += $course['WeeklyHours'];
            }
        }
    }

    // Validation for total hours
    if ($selectedCoursesHours + $totalWeeklyHours > 16) {
        $message = "Selected courses exceed the maximum of 16 weekly hours.";
    } else {
        // Register selected courses
        $registerStmt = $db->prepare("INSERT INTO Registration (StudentId, CourseCode, SemesterCode) VALUES (:studentId, :courseCode, :semesterCode)");
        foreach ($selectedCourses as $courseCode) {
            $registerStmt->execute([':studentId' => $studentId, ':courseCode' => $courseCode, ':semesterCode' => $selectedSemester]);
        }
        header("Location: CourseSelection.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Course Selection</title>
    <link rel="stylesheet" href="styles.css">
    <script>
        function updateCourses() {
            document.getElementById('semesterForm').submit();
        }
    </script>
</head>
<body>
    <?php include('Header.php'); ?>

    <div class="container">
        <h2>Course Selection</h2>

        <form id="semesterForm" method="POST">
            <label for="semesterCode">Select Semester:</label>
            <select name="semesterCode" id="semesterCode" onchange="updateCourses()">
                <option value="">--Select Semester--</option>
                <?php foreach ($semesters as $semester): ?>
                    <option value="<?php echo $semester['SemesterCode']; ?>" <?php echo $selectedSemester === $semester['SemesterCode'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($semester['Term'] . ' ' . $semester['Year']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>

        <?php if ($selectedSemester && isset($courses)): ?>
            <p>Total Weekly Hours: <?php echo $totalWeeklyHours; ?></p>
            <p>Remaining Weekly Hours: <?php echo $remainingHours; ?></p>

            <form method="POST">
                <input type="hidden" name="semesterCode" value="<?php echo htmlspecialchars($selectedSemester); ?>">
                <?php if (!empty($message)): ?><p class="error"><?php echo $message; ?></p><?php endif; ?>

                <table>
                    <tr>
                        <th>Course Code</th>
                        <th>Title</th>
                        <th>Weekly Hours</th>
                        <th>Select</th>
                    </tr>
                    <?php foreach ($courses as $course): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($course['Code']); ?></td>
                            <td><?php echo htmlspecialchars($course['Title']); ?></td>
                            <td><?php echo htmlspecialchars($course['WeeklyHours']); ?></td>
                            <td><input type="checkbox" name="selectedCourses[]" value="<?php echo $course['Code']; ?>"></td>
                        </tr>
                    <?php endforeach; ?>
                </table>

                <div class="button-container">
                    <button type="reset" class="clear-button">Clear</button>
                    <button type="submit" class="next-button">Register</button>
                </div>
            </form>
        <?php endif; ?>
    </div>

    <?php include('Footer.php'); ?>
</body>
</html>
