<?php
@include 'config.php';
session_name('frontdesk');
session_start();

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data and sanitize it
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];  // We don't need to escape the password, we just verify it later

    // Prepare SQL statement to retrieve user information
    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // User found, fetch user data
        $user = $result->fetch_assoc();

        // Verify the password using password_verify()
        if (password_verify($password, $user['password'])) {
            // Password is correct, log in successful
            $_SESSION['username'] = $user['username']; 
            $_SESSION['position'] = $user['position']; 

            if ($_SESSION['position'] == 'Owner') {
                $_SESSION['role'] = 'admin';
                echo "<script>window.location.href = 'Profiles.php'; alert('Access authorized!');</script>";
            } else {
                // For guests (frontdesk users)
                $_SESSION['position'] == 'frontdesk';
                $_SESSION['guest_id'] = $user['id']; 
                echo "<script>window.location.href = 'Dashboard.php'; alert('Access authorized!');</script>";
            }
        } else {
            // Invalid password
            echo "<script>window.location.href = 'index.php'; alert('Invalid username or password. Please try again.');</script>";
        }
    } else {
        // User not found
        echo "<script>window.location.href = 'index.php'; alert('Invalid username or password. Please try again.');</script>";
    }

    // Close connection
    $stmt->close();
    $conn->close();
} else {
    // If form is not submitted, redirect to login page
    header("Location: index.php");
    exit();
}

