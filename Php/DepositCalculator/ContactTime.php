<?php
session_start();
if (!isset($_SESSION['agreed'])) {
    header("Location: CustomerInfo.php");
    exit();
}

$error = "";
// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($_POST['contact_times'])) {
        $error = "Please select at least one preferred contact time.";
    } else {
        // Store selected contact times in session
        $_SESSION['contact_times'] = $_POST['contact_times'];
        header("Location: DepositCalculator.php");
        exit();
    }
}

// Retrieve previously selected contact times from session
$selectedTimes = $_SESSION['contact_times'] ?? [];
?>

<?php include('Header.php'); ?>

<main class="container mt-5">
    <h2 class="display-4">Preferred Contact Times</h2>
    
    <form method="post" action="ContactTime.php" class="border p-4 rounded" style="box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">

        <div class="form-group">
            <p class="font-size-24">When can we contact you? Check all applicable: </p>
            <?php if ($error): ?>
                <p class="text-danger"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>

            <div>
                <!-- Adding margin-right to checkboxes for spacing -->
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" name="contact_times[]" value="9am-10am" <?php echo in_array('9am-10am', $selectedTimes) ? 'checked' : ''; ?> style="margin-right: 10px;"> 
                    <label>9am-10am</label><br>
                    
                    <input type="checkbox" class="form-check-input" name="contact_times[]" value="10am-11am" <?php echo in_array('10am-11am', $selectedTimes) ? 'checked' : ''; ?> style="margin-right: 10px;"> 
                    <label>10am-11am</label><br>
                    
                    <input type="checkbox" class="form-check-input" name="contact_times[]" value="11am-12pm" <?php echo in_array('11am-12pm', $selectedTimes) ? 'checked' : ''; ?> style="margin-right: 10px;"> 
                    <label>11am-12pm</label><br>
                    
                    <input type="checkbox" class="form-check-input" name="contact_times[]" value="12pm-1pm" <?php echo in_array('12pm-1pm', $selectedTimes) ? 'checked' : ''; ?> style="margin-right: 10px;"> 
                    <label>12pm-1pm</label><br>
                    
                    <input type="checkbox" class="form-check-input" name="contact_times[]" value="1pm-2pm" <?php echo in_array('1pm-2pm', $selectedTimes) ? 'checked' : ''; ?> style="margin-right: 10px;"> 
                    <label>1pm-2pm</label><br>
                    
                    <input type="checkbox" class="form-check-input" name="contact_times[]" value="2pm-3pm" <?php echo in_array('2pm-3pm', $selectedTimes) ? 'checked' : ''; ?> style="margin-right: 10px;"> 
                    <label>2pm-3pm</label><br>
                    
                    <input type="checkbox" class="form-check-input" name="contact_times[]" value="3pm-4pm" <?php echo in_array('3pm-4pm', $selectedTimes) ? 'checked' : ''; ?> style="margin-right: 10px;"> 
                    <label>3pm-4pm</label><br>
                    
                    <input type="checkbox" class="form-check-input" name="contact_times[]" value="4pm-5pm" <?php echo in_array('4pm-5pm', $selectedTimes) ? 'checked' : ''; ?> style="margin-right: 10px;"> 
                    <label>4pm-5pm</label><br>
                    
                    <input type="checkbox" class="form-check-input" name="contact_times[]" value="5pm-6pm" <?php echo in_array('5pm-6pm', $selectedTimes) ? 'checked' : ''; ?> style="margin-right: 10px;"> 
                    <label>5pm-6pm</label><br>
                </div>
            </div>
        </div>
        
        <a href="CustomerInfo.php" class="btn btn-secondary">< Back</a>
        <button type="submit" class="btn btn-primary">Next ></button>
    </form>
</main>

<?php include('Footer.php'); ?>
