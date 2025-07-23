<?php
session_start();
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['agree'])) {
        $_SESSION['agreed'] = true;
        header("Location: CustomerInfo.php");
        exit();
    } else {
        $error = "You must agree to the terms to continue.";
    }
}
?>

<?php include('Header.php'); ?>

<main>
    <form method="post" action="Disclaimer.php">
        <div class="container">
            <h2 class="text-center">Terms and Conditions</h2>
            <div class="terms-container">
                <div class="term">
                    <p>
                        I agree to abide by the Bank's Terms and Conditions and rules in force and the changes thereto in Terms and Conditions from time to time relating to my account as communicated and made available on the Bank's website.
                    </p>                        
                </div>
                <hr>
                <div class="term">
                    <p>
                        I agree that the bank before opening any deposit account, will carry out a due diligence as required under Know Your Customer guidelines of the bank. I would be required to submit necessary documents or proofs, such as identity, address, photograph and any such information, I agree to submit the above documents again at periodic intervals, as may be required by the Bank.
                    </p>
                </div>
                <hr>
                <div class="term">
                    <p>
                        I agree that the Bank can at its sole discretion, amend any of the services/facilities given in my account either wholly or partially at any time by giving me at least 30 days notice and/or provide an option to me to switch to other services/facilities.
                    </p>
                </div>
            </div><br> <BR>
            <?php if ($error): ?>
                <p style="color: red; margin-left: 10px; "><?php echo $error; ?></p>
            <?php endif; ?>
            <input type="checkbox" name="agree" id="agree" style="margin-left: 10px"> I have read and agree to the terms and conditions.
            <br><br>
        <button type="submit" class="btn btn-primary" style="margin-left: 10px">Start</button>
        </div>
    </form>
</main>

<?php include('Footer.php'); ?>

<style>
    .terms-container {
        border: 1px solid #ccc; /* Border around the entire section */
        padding: 20px;
        border-radius: 5px; /* Optional: rounded corners */
    }
    
    .term {
        margin: 10px 0; /* Spacing between terms */
    }
    
    hr {
        border: 1px solid #ccc; /* Line between terms */
        margin: 10px 0; /* Space above and below the line */
    }
</style>
