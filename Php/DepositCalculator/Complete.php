    <?php
    session_start();

    // Ensure that the session exists, and user comes from the DepositCalculator.php page
    if (!isset($_SESSION['deposit_amount']) || !isset($_SESSION['years'])) {
        // Redirect user to DepositCalculator.php if they attempt to access Complete.php without submission
        header("Location: DepositCalculator.php");
        exit();
    }

    // Retrieve values from the session before destroying the session
    $name = $_SESSION['name'] ?? 'Customer'; // Default to 'Customer' if name is not set
    $contactMethod = $_SESSION['preferred_contact'] ?? ''; // This will hold 'phone' or 'email'
    $contactTimes = $_SESSION['contact_times'] ?? []; // This should be an array of contact times (if any)
    $phoneNumber = $_SESSION['phone'] ?? ''; // The phone number from the session
    $emailAddress = $_SESSION['email'] ?? ''; // The email address from the session

    // Now destroy the session data for the next user
    session_unset(); // Unset all session variables
    session_destroy(); // Destroy the session
    ?>

    <?php include('Header.php'); ?>

    <main class="container">
        <h1>Thank You, <span style="color: blue;"><?php echo htmlspecialchars($name); ?></span>, for using our deposit calculator!</h1>

        <?php
        if ($contactMethod === 'phone') {
            // Join selected times into a single string
            $times = !empty($contactTimes) ? implode(', ', $contactTimes) : 'the time you specified';
            echo "<p style='font-size: 17px;'>Our customer service department will call you tomorrow at the following times: $times at the phone number: " . htmlspecialchars($phoneNumber) . ".</p>";
        } else {
            echo "<p style='font-size: 24px;'>An email about the details of our GIC has been sent to " . htmlspecialchars($emailAddress) . ".</p>";
        }
        ?>
    </main>

    <?php include('Footer.php'); ?>
