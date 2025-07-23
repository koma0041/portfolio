<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Redirect to disclaimer if user has not agreed
if (!isset($_SESSION['agreed'])) {
    header("Location: Disclaimer.php");
    exit();
}

$error = [
    'name' => '',
    'email' => '',
    'phone' => '',
    'postal_code' => '',
    'preferred_contact' => ''
];

// Clear session data if "Complete" is pressed
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['complete'])) {
    unset($_SESSION['name']);
    unset($_SESSION['email']);
    unset($_SESSION['phone']);
    unset($_SESSION['postal_code']);
    unset($_SESSION['preferred_contact']);
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['complete'])) {
    // Validate required fields
    if (empty($_POST['name'])) {
        $error['name'] = "Name is required.";
    } else {
        $_SESSION['name'] = $_POST['name'];
    }

    if (empty($_POST['email']) || !ValidateEmail($_POST['email'])) {
        $error['email'] = "Email must be in the form of aaa@xxx.yyy.";
    } else {
        $_SESSION['email'] = $_POST['email'];
    }

    if (empty($_POST['phone']) || !ValidatePhone($_POST['phone'])) {
        $error['phone'] = "Phone number must be in the form of nnn-nnn-nnnn..";
    } else {
        $_SESSION['phone'] = $_POST['phone'];
    }

    if (empty($_POST['postal_code']) || !ValidatePostalCode($_POST['postal_code'])) {
        $error['postal_code'] = "Postal code must be in the form of XnX nXn.";
    } else {
        $_SESSION['postal_code'] = $_POST['postal_code'];
    }

    if (empty($_POST['preferred_contact'])) {
        $error['preferred_contact'] = "Please select a preferred contact method.";
    } else {
        $_SESSION['preferred_contact'] = $_POST['preferred_contact'];
    }

    // If there are no errors, redirect based on preferred contact method
    if (!array_filter($error)) {
        if ($_POST['preferred_contact'] == 'phone') {
            header("Location: ContactTime.php");
        } else {
            header("Location: DepositCalculator.php");
        }
        exit();
    }
}

include('Header.php'); // Include the header

function ValidatePostalCode($postalCode) {
    return preg_match("/^[A-Za-z]\d[A-Za-z] ?\d[A-Za-z]\d$/", $postalCode);
}

function ValidatePhone($phone) {
    return preg_match("/^[2-9]\d{2}-[2-9]\d{2}-\d{4}$/", $phone);
}

function ValidateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) &&
           preg_match("/^[^@]+@[^@]+\.[a-zA-Z]{2,4}$/", $email);
}
?><main class="container">
    <h1 class="display-4 text-center">Customer Information</h1>
    <form method="post" action="CustomerInfo.php" class="border p-4 rounded" style="box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">
        <div class="form-group row">
            <label for="name" class="col-sm-3 col-form-label">Name:</label>
            <div class="col-sm-9">
                <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($_SESSION['name'] ?? '', ENT_QUOTES); ?>">
                <?php if ($error['name']): ?>
                    <div class="text-danger"><?php echo htmlspecialchars($error['name'], ENT_QUOTES); ?></div>
                <?php endif; ?>
            </div>
        </div>
        <div class="form-group row">
            <label for="email" class="col-sm-3 col-form-label">Email:</label>
            <div class="col-sm-9">
                <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($_SESSION['email'] ?? '', ENT_QUOTES); ?>">
                <?php if ($error['email']): ?>
                    <div class="text-danger"><?php echo htmlspecialchars($error['email'], ENT_QUOTES); ?></div>
                <?php endif; ?>
            </div>
        </div>
        <div class="form-group row">
            <label for="phone" class="col-sm-3 col-form-label">Phone:</label>
            <div class="col-sm-9">
                <input type="text" class="form-control" name="phone" value="<?php echo htmlspecialchars($_SESSION['phone'] ?? '', ENT_QUOTES); ?>">
                <?php if ($error['phone']): ?>
                    <div class="text-danger"><?php echo htmlspecialchars($error['phone'], ENT_QUOTES); ?></div>
                <?php endif; ?>
            </div>
        </div>
        <div class="form-group row">
            <label for="postal_code" class="col-sm-3 col-form-label">Postal Code:</label>
            <div class="col-sm-9">
                <input type="text" class="form-control" name="postal_code" value="<?php echo htmlspecialchars($_SESSION['postal_code'] ?? '', ENT_QUOTES); ?>">
                <?php if ($error['postal_code']): ?>
                    <div class="text-danger"><?php echo htmlspecialchars($error['postal_code'], ENT_QUOTES); ?></div>
                <?php endif; ?>
            </div>
        </div>
        <div class="form-group row">
            <label for="preferred_contact" class="col-sm-3 col-form-label">Preferred Contact Method:</label>
            <div class="col-sm-9">
                <select name="preferred_contact" class="form-control">
                    <option value="">Select one...</option>
                    <option value="phone" <?php echo (isset($_SESSION['preferred_contact']) && $_SESSION['preferred_contact'] == 'phone') ? 'selected' : ''; ?>>Phone</option>
                    <option value="email" <?php echo (isset($_SESSION['preferred_contact']) && $_SESSION['preferred_contact'] == 'email') ? 'selected' : ''; ?>>Email</option>
                </select>
                <?php if ($error['preferred_contact']): ?>
                    <div class="text-danger"><?php echo htmlspecialchars($error['preferred_contact'], ENT_QUOTES); ?></div>
                <?php endif; ?>
            </div>
        </div>
        <div class="form-group row">
            <div class="col-sm-9 offset-sm-3 margin-bottom:0">
                <button type="submit" name="next" class="btn btn-primary">Next</button>
            </div>
        </div>
    </form>
</main>


<?php include('Footer.php'); ?> 