<?php

session_start(); // Start the session

// Check if the required session variables are set
if (!isset($_SESSION['name']) || 
    !isset($_SESSION['email']) || 
    !isset($_SESSION['postal_code']) || 
    !isset($_SESSION['preferred_contact']) || 
    !isset($_SESSION['phone'])) {
    header("Location: CustomerInfo.php"); // Redirect to Customer Info if not set
    exit();
}


// Check if preferred contact is 'phone'
if ($_SESSION['preferred_contact'] === 'phone') {
    // Check if the 'checked' session variable is set
    if (!isset($_SESSION['contact_times'])) {
        header("Location: ContactTime.php"); // Redirect to ContactTime page if timing is not set
        exit();
    }
}

$error = "";
$result = "";

// Populate form with session values if they exist
$deposit_amount = $_SESSION['deposit_amount'] ?? '';
$years_selected = $_SESSION['years'] ?? '';
if ($_SESSION['preferred_contact'] === 'phone') {
    if (!isset($_SESSION['contact_times'])) {
        // If contact times are not set, redirect to ContactTime.php
        header("Location: ContactTime.php");
        exit();
    }
    $previous_page = 'ContactTime.php'; // Set previous page for phone
} elseif ($_SESSION['preferred_contact'] === 'email') {
    $previous_page = 'CustomerInfo.php'; // Set previous page for email
} else {
    // Handle any unexpected values
    header("Location: CustomerInfo.php"); // Default fallback
    exit();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['complete'])) { // Check if it's not the "Complete" button
    // Retain values if an error occurs
    $deposit_amount = $_POST['deposit_amount'] ?? $deposit_amount; // Retain entered deposit amount
    $years_selected = $_POST['years'] ?? $years_selected; // Retain selected years

    if (empty($deposit_amount)) {
        $error = "Please enter the deposit amount.";
    } elseif (empty($years_selected)) {
        $error = "Please select the number of years.";
    } else {
        $_SESSION['deposit_amount'] = $deposit_amount; // Store in session
        $_SESSION['years'] = $years_selected; // Store in session

        $years = $years_selected; // Use the validated selected years
        $principal = $deposit_amount; // Use the validated deposit amount
        $interestRate = 0.03; // Interest rate of 3%
        $totalAmount = $principal;

        $result .= "<p>The following is the calculation results at the current interest rate of 3%:</p>";
        $result .= "<table class='table table-bordered table-striped table-hover'><thead><tr><th>Year</th><th>Principal at Year Start</th><th>Interest for the Year</th></tr></thead><tbody>";

        for ($i = 1; $i <= $years; $i++) {
            $interestPayment = $totalAmount * $interestRate;
            $result .= sprintf("<tr><td>%d</td><td>$%.2f</td><td>$%.2f</td></tr>", $i, $totalAmount, $interestPayment);
            $totalAmount += $interestPayment;
        }
        $result .= "</tbody></table>";

        // Enable the "Complete" button after successful calculation
        $completeEnabled = true; // Use a flag to indicate the button should be enabled
    }
}

// Clear session data if "Complete" button is pressed
if (isset($_POST['complete'])) {
    session_unset(); // Clears session data
    header("Location: Complete.php"); // Redirect to Complete page
    exit();
}
?>

<?php include('Header.php'); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deposit Calculator</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            function populateYears() {
                const yearsSelect = document.getElementById('years');
                for (let i = 1; i <= 25; i++) {
                    const option = document.createElement('option');
                    option.value = i;
                    option.textContent = `${i} year${i > 1 ? 's' : ''}`;
                    // Check if this option should be selected based on PHP value
                    if (i == <?php echo json_encode($years_selected); ?>) {
                        option.selected = true; // Set selected option
                    }
                    yearsSelect.appendChild(option);
                }
            }

            function validateForm(event) {
                let isValid = true;
                document.querySelectorAll('.error-message').forEach(errorElem => {
                    errorElem.textContent = '';
                });

                const depositAmount = document.getElementById('deposit_amount').value.trim();
                const years = document.getElementById('years').value;

                if (depositAmount === '') {
                    document.getElementById('depositError').textContent = 'Deposit amount is required.';
                    isValid = false;
                } else if (isNaN(depositAmount) || depositAmount <= 0) {
                    document.getElementById('depositError').textContent = 'Please enter a valid positive number.';
                    isValid = false;
                }

                if (years === '') {
                    document.getElementById('yearsError').textContent = 'Please select the number of years.';
                    isValid = false;
                }

                if (!isValid) {
                    event.preventDefault();
                } 
            }

            populateYears(); // Populate years dropdown
            document.querySelector('form').addEventListener('submit', validateForm);
        });
    </script>
</head>
<body>
<main class="container mt-5">
    <p class="text-center" style="font-size: 24px;">Enter principal amount, and select numbers of years to deposit</p>
    <form method="post" action="DepositCalculator.php" class="border p-4 rounded shadow-sm">
        <div class="form-group row">
            <label for="deposit_amount" class="col-sm col-form-label text-right">Deposit Amount:</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" id="deposit_amount" name="deposit_amount" value="<?php echo htmlspecialchars($deposit_amount); ?>">
                <span id="depositError" class="text-danger error-message"></span>
            </div>
        </div>
        <div class="form-group row">
            <label for="years" class="col-sm col-form-label text-right">Number of Years:</label>
            <div class="col-sm-10">
                <select class="form-control" id="years" name="years">
                    <option value="">Select one...</option>
                    <!-- Options are dynamically populated by JavaScript -->
                </select>
                <span id="yearsError" class="text-danger error-message"></span>
            </div>
        </div>
        <div class="m-4">
        <!-- Dynamic back button based on session -->
        <a href="<?php echo $previous_page; ?>" class="btn btn-secondary">< Back</a>
        
        <!-- Calculate button -->
        <button type="submit" id="calculateButton" class="btn btn-primary">Calculate</button>
        
        <!-- Complete button enabled conditionally -->
        <a href="complete.php" id="completeButton" class="btn btn-success" 
           <?php if (!isset($completeEnabled)) echo 'style="pointer-events: none; opacity: 0.5;"'; ?>>Complete</a>
        </div>
        <script>
            document.getElementById('completeButton').onclick = function(event) {
                <?php if (!isset($completeEnabled)): ?>
                    event.preventDefault(); // Prevent the link from being followed if disabled
                <?php endif; ?>
            };
        </script>
    </form>

    <br><br>
    <?php if ($error): ?>
        <p class="text-danger"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    <?php if ($result): ?>
        <?php echo $result; ?>
    <?php endif; ?>
</main>

<?php include('Footer.php'); ?>

</