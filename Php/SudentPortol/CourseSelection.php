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
$courses = [];
$message = '';
$registeredCourseNames = [];

if ($selectedSemester) {
    try {
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
    } catch (PDOException $e) {
        $message = "An error occurred while fetching course data. Please try again.";
    }
}

// Handle course registration on form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['selectedCourses'])) {
        $message = "Please select at least one course.";
    } else {
        $selectedCourses = $_POST['selectedCourses'];
        $selectedCoursesHours = 0;

        foreach ($selectedCourses as $courseCode) {
            foreach ($courses as $course) {
                if ($course['Code'] === $courseCode) {
                    $selectedCoursesHours += $course['WeeklyHours'];
                    $registeredCourseNames[] = $course['Title']; // Store course names
                }
            }
        }

        // Validation for total hours
        if ($selectedCoursesHours + $totalWeeklyHours > 16) {
            $message = "Selected courses exceed the maximum of 16 weekly hours.";
        } else {
            try {
                // Register selected courses
                $registerStmt = $db->prepare("INSERT INTO Registration (StudentId, CourseCode, SemesterCode) VALUES (:studentId, :courseCode, :semesterCode)");
                foreach ($selectedCourses as $courseCode) {
                    $registerStmt->execute([':studentId' => $studentId, ':courseCode' => $courseCode, ':semesterCode' => $selectedSemester]);
                }
                // Display a success message
                $message = "Registration successful! Registered course(s): " . implode(", ", $registeredCourseNames);
            } catch (PDOException $e) {
                $message = "An error occurred while registering courses. Please try again.";
                $registeredCourseNames = []; // Clear names if registration fails
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Course Selection</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script>
        function updateCourses() {
            document.getElementById('semesterForm').submit();
        }
    </script>
</head>
<body>
    <?php include('Header_1.php'); ?>

    <main class="container">
        <h1 class="display-4 text-center">Course Selection</h1>
        <p>Welcome <?php echo htmlspecialchars($studentId); ?>! (not you? change user <a href="Login.php">here</a>)</p>
        
        <p>You can register <?php echo $remainingHours; ?> more hours of course(s) for the semester</p>
        <p>Please note that the courses you have registered will not be displayed in the list.</p>
        
        <form id="semesterForm" method="POST" class="mb-3">
            <label for="semesterCode">Select Semester:</label>
            <select name="semesterCode" id="semesterCode" class="form-control" onchange="updateCourses()">
                <option value="">--Select Semester--</option>
                <?php foreach ($semesters as $semester): ?>
                    <option value="<?php echo htmlspecialchars($semester['SemesterCode']); ?>" <?php echo $selectedSemester === $semester['SemesterCode'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($semester['Term'] . ' ' . $semester['Year']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>

        <?php if (!empty($message)): ?>
            <p class="text-success"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <?php if ($selectedSemester && !empty($courses)): ?>
            <form method="POST">
                <input type="hidden" name="semesterCode" value="<?php echo htmlspecialchars($selectedSemester); ?>">

                <table class="table table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th>Code</th>
                            <th>Course Title</th>
                            <th>Hours</th>
                            <th>Select</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($courses as $course): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($course['Code']); ?></td>
                                <td><?php echo htmlspecialchars($course['Title']); ?></td>
                                <td><?php echo htmlspecialchars($course['WeeklyHours']); ?></td>
                                <td><input type="checkbox" name="selectedCourses[]" value="<?php echo htmlspecialchars($course['Code']); ?>"></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="form-group">
                    <button type="reset" class="btn btn-secondary">Clear</button>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
        <?php endif; ?>
    </main>

    <?php include('Footer.php'); ?>
</body>
</html>
