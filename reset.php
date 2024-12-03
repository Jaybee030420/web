<?php
@include 'config.php';

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $captcha_response = $_POST['g-recaptcha-response'];

    // Verify CAPTCHA
    $secret_key  ='6LdWUFEqAAAAAC-Ysv_eFUwZYBVD0W0RQEWDh1dq';
    $response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret={$secret_key}&response={$captcha_response}");
    $response_keys = json_decode($response, true);

    if (intval($response_keys["success"]) !== 1) {
        $message = "Please complete the CAPTCHA.";
    } else if ($new_password !== $confirm_password) {
        $message = "Passwords do not match.";
    } else {
        // Hash the new password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        // Update the password in the database
        $stmt = $conn->prepare("UPDATE guest SET password = ? WHERE email = ?");
        $stmt->bind_param("ss", $hashed_password, $email);

        if ($stmt->execute()) {
            echo "<script>window.location.href = 'login1.php'; alert('Your password successfully change!!');</script>";
        } else {
            $message = "Error updating password.";
        }

        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="register.css">
    <title>Update Password</title>
</head>
<body>
    <div class="container">
        <h2>Update Password</h2>
        <form method="POST" action="">
            <label for="email">Email:</label>
            <input type="email" name="email" required>

            <div class="otpverify">
                <input type="text" id="otp_inp" placeholder="Enter OTP here... ">
                <button class="btn" id="otp_btn">Verify</button>
            </div>
            <button class="btn" onclick="sendOTP()">Send OTP</button>


            <label for="new_password">New Password:</label>
            <input type="password" id="new_password" name="new_password" required>

            <label for="confirm_password">Re-enter New Password:</label>
            <input type="password" id="confirm_password" name="confirm_password" required>

            <div id="password-message"></div>

            <div class="g-recaptcha" data-sitekey="6LdWUFEqAAAAAE1FsCin3xQkNjBpmcqSpHD_a2Ch"></div>

            <button type="submit">Confirm</button><br><br>
            <a href="login1.php" style="margin-left: 450px;" class="text-warning">Login Here!</a>
        </form>

        <?php if (!empty($message)): ?>
            <p><?php echo $message; ?></p>
        <?php endif; ?>
    </div>

    <!-- Add Google reCAPTCHA script -->
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const newPassword = document.getElementById("new_password");
        const confirmPassword = document.getElementById("confirm_password");
        const messageDiv = document.getElementById("password-message");

        function validatePassword() {
            if (newPassword.value === confirmPassword.value) {
                messageDiv.textContent = "Passwords match!";
                messageDiv.classList.remove("no-match");
                messageDiv.classList.add("match");
            } else {
                messageDiv.textContent = "Passwords do not match!";
                messageDiv.classList.remove("match");
                messageDiv.classList.add("no-match");
            }
        }

        newPassword.addEventListener("input", validatePassword);
        confirmPassword.addEventListener("input", validatePassword);
    });
</script>

</body>
</html>
