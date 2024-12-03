<?php

session_name('guest_session'); 
session_start(); 

@include 'config.php'; 

$message = ''; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username']; 
    $password = $_POST['password']; 
    $captcha_response = $_POST['g-recaptcha-response']; 


    $secret_key = '6LdWUFEqAAAAAC-Ysv_eFUwZYBVD0W0RQEWDh1dq';
    $response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret={$secret_key}&response={$captcha_response}");
    $response_keys = json_decode($response, true);

    if (intval($response_keys["success"]) !== 1) {
        $message = "Please complete the CAPTCHA.";
    } else {
        
        $stmt = $conn->prepare("SELECT id, email, password, bit, position, first_name, last_name FROM guest WHERE email = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($guest_id, $name, $hashed_password, $bit, $role, $first_name, $last_name);
            $stmt->fetch();

            if (password_verify($password, $hashed_password)) {
                if ($bit == 1) {
                    
                    // Set session variables
                    $_SESSION['loggedin'] = true;
                    $_SESSION['username'] = $name;
                    $_SESSION['guest_id'] = $guest_id;
                    $_SESSION['position'] = 'guest';
                    $_SESSION['first_name'] = $first_name;  // Add first name to session
                    $_SESSION['last_name'] = $last_name;    // Add last name to session
                


                    
                    session_regenerate_id(true);

                    // Redirect based on role
                    if ($role === 'admin') {
                        header("Location: dashboard_admin.php"); 
                    } else {
                        header("Location: dashboard1.php");  
                    }
                    exit();
                } else {
                    // If account is under review
                    echo "<script>window.location.href = 'login1.php'; alert('Please wait in your Gmail as we review your information. Thanks for understanding!');</script>";
                }
            } else {
                $message = "Invalid credentials."; // Incorrect password
            }
        } else {
            $message = "Invalid credentials."; // Guest not found
        }
    }
}
?>

<!doctype html>
<html lang="en">
<head>
    <title>Login</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-iYQeCzEYFbKjA/T2uDLTpkwGzCiq6soy8tYaI1GyVh/UjpbCx/TYkiZhlZB6+fzT" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container py-5">
    <div class="card login-card">
        <div class="card-body">
            <div class="row d-flex align-items-center justify-content-center">
                <div class="col-md-6">
                    <img src="./assets/images/logo/logo.jpg" class="img-fluid" alt="Logo">
                </div>
                <div class="col-md-6">
                    <form action="" method="post">
                        <p class="text-center h1 fw-bold mb-4">Login</p>
                        <div class="mb-4">
                            <label class="form-label" for="form1Example13"><i class="bi bi-person-circle"></i> Email</label>
                            <input type="text" id="form1Example13" class="form-control py-3" name="username" autocomplete="off" placeholder="Enter your email" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label" for="form1Example23"><i class="bi bi-chat-left-dots-fill"></i> Password</label>
                            <input type="password" id="form1Example23" class="form-control py-3" name="password" autocomplete="off" placeholder="Enter your password" required>
                        </div>
                        <div class="mb-4">
                            <div class="g-recaptcha" data-sitekey="6LdWUFEqAAAAAE1FsCin3xQkNjBpmcqSpHD_a2Ch"></div>
                        </div>
                        <div class="mb-4">
                            <input type="submit" value="Sign in" name="login" class="btn btn-warning btn-lg text-light py-3" style="width: 100%;"><br><br>
                            <a href="index.php" style="font-size: 15px; width: 100%; background-color: #000;" class="btn btn-warning btn-lg text-light py-3"> Cancel</a>
                        </div>
                        <?php if (!empty($message)): ?>
                            <div class="alert alert-danger" role="alert"><?php echo $message; ?></div>
                        <?php endif; ?>
                    </form>
                    <p class="text-center mb-0">Forgot Password? <a href="reset.php" class="text-warning">click Here.</a></p>
                    <a href="register.php" style="margin-left: 260px;" class="text-warning">Register Here!</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/js/bootstrap.min.js" integrity="sha384-7VPbUDkoPSGFnVtYi0QogXtr74QeVeeIs99Qfg5YCF+TidwNdjvaKZX19NZ/e6oz" crossorigin="anonymous"></script>
</body>
</html>
