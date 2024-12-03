<?php
@include 'config.php';

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = $_POST['fname'];
    $last_name = $_POST['lname'];
    $middle_name = $_POST['mname'];
    $birthday = $_POST['birthday'];
    $address = $_POST['address'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = $_POST['password'];

    // Check if the email already exists
    $stmt = $conn->prepare("SELECT * FROM guest WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo "<script>alert('Email already registered!');</script>";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO guest (first_name, last_name, middle_name, birthday, address, email, phone, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssss", $first_name, $last_name, $middle_name, $birthday, $address, $email, $phone, $hashed_password);

        if ($stmt->execute()) {
            echo "<script>window.location.href = 'register.php'; alert('Please wait as we review your request! Check your email for confirmation! Thank you.');</script>";
        } else {
            echo "Error: " . $stmt->error;
        }
    }

    $stmt->close();
}

$conn->close();
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="register.css">
    <title>Registration Form</title>
</head>
<body>
    <div class="container">
        <h2>Register</h2>
        <form method="POST" action="">

            <label for="first_name">First Name:</label>
            <input type="text" name="fname" required>

            <label for="first_name">Middle Name:</label>
            <input type="text" name="mname" required>

            <label for="last_name">Last Name:</label>
            <input type="text" name="lname" required>

            <label>Gender:</label><br>
            <select name="gender" required>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
            </select><br><br>
            
            <label for="username">Birthday:</label>
            <input type="date" name="birthday" required>
            
            <label for="username">Address:</label>
            <input type="text" name="address" required>

            <label for="email">Email:</label>
            <input type="email" name="email" required>

            <label for="phone">Phone Number:</label>
            <input type="number" name="phone" required>

            <label for="password">Password:</label>
            <input type="password" name="password" required>

            <button type="submit">Register</button><br><br>

            <a href="login1.php" style="margin-left: 450px;" class="text-warning">Login Here!</a>
        </form>
    </div>
</body>
</html>
