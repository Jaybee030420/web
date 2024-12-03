<?php
session_name('guest_session');
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['position'] !== 'guest') {
    header("Location: index.php");
    exit();
}

@include 'config.php'; 

$guest_id = $_SESSION['guest_id'];
$guest_name = $_SESSION['first_name'] . ' ' . $_SESSION['last_name'];


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['book'])) {

    $dateFrom = $_POST['dateFrom'];
    $dateTo = $_POST['dateTo'];
    $roomtype = $_POST['roomtype'];
    $mop = $_POST['mop'];
    $promo = $_POST['promo'];


  
    $insert_sql = "INSERT INTO bookings (guest_id, roomtype, arrival, depart, mop, promo,status) VALUES (?, ?, ?, ?, ?, ?, 'pending')";
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param("isssss", $guest_id, $roomtype, $dateFrom, $dateTo, $mop, $promo);
    
    if ($insert_stmt->execute()) {
        echo "<script>
                alert('Booking successful! Please ensure you secure payment within an hour and pay at least 50% of the room price (PHP $room_price)! Thank you.');
                window.location.href = 'dashboard1.php';
              </script>";
    } else {
        echo "<script>alert('Could not complete the booking. Please try again!'); window.location.href = 'dashboard1.php';</script>";
    }
}

if (isset($_POST['upload_images'])) {
    $bookingID = $_POST['bookingID'];
    $image = $_FILES['image']['name'];
    $image_tmp = $_FILES['image']['tmp_name'];
    $image_error = $_FILES['image']['error'];

    if (empty($bookingID) || empty($image)) {
        echo "<script>alert('Please fill all fields!'); window.location.href = 'dashboard1.php';</script>";
        exit();
    }

    // Check if the booking belongs to the logged-in user
    $check_booking_sql = "SELECT * FROM bookings WHERE bookingID = ? AND guest_id = ? LIMIT 1";
    $check_stmt = $conn->prepare($check_booking_sql);
    $check_stmt->bind_param("ii", $bookingID, $guest_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows == 0) {
        echo "<script>alert('Invalid Booking ID or this booking does not belong to you.'); window.location.href = 'dashboard1.php';</script>";
        exit();
    }

    // Handle file upload
    if ($image_error === UPLOAD_ERR_OK) {
        $fileTmpName = $_FILES['image']['tmp_name'];
        $fileType = $_FILES['image']['type'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];

        // Validate file type and extension
        if (!in_array($fileType, $allowedTypes)) {
            echo "<script>alert('Invalid file type! Only JPEG, PNG, and JPG are allowed.'); window.location.href = 'dashboard1.php';</script>";
            exit();
        }

        // Sanitize the file name
        $sanitizedFileName = basename($image);
        $sanitizedFileName = preg_replace("/[^a-zA-Z0-9\._-]/", "_", $sanitizedFileName); // Sanitize filename
        $target = "uploaded_img/";

        // Ensure the target directory exists
        if (!is_dir($target)) {
            mkdir($target, 0777, true);
        }

        $targetFile = $target . $sanitizedFileName;

        // Check if image already exists for this booking
        $check_image_sql = "SELECT * FROM booking_images WHERE booking_id = ? LIMIT 1";
        $check_stmt = $conn->prepare($check_image_sql);
        $check_stmt->bind_param("i", $bookingID);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            // Delete the existing image if it exists
            $existing_image = $check_result->fetch_assoc();
            $existing_image_path = $target . $existing_image['image_path'];
            if (file_exists($existing_image_path)) {
                unlink($existing_image_path); // Remove the old image
            }

            // Update the image
            $update_sql = "UPDATE booking_images SET image_path = ? WHERE booking_id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("si", $sanitizedFileName, $bookingID);

            if (move_uploaded_file($fileTmpName, $targetFile) && $update_stmt->execute()) {
                echo "<script>alert('Image successfully updated!'); window.location.href = 'dashboard1.php';</script>";
            } else {
                echo "<script>alert('Error updating image!'); window.location.href = 'dashboard1.php';</script>";
            }
        } else {
            // Insert new image if none exists
            $insert_sql = "INSERT INTO booking_images (booking_id, image_path) VALUES (?, ?)";
            $insert_stmt = $conn->prepare($insert_sql);     
            $insert_stmt->bind_param("is", $bookingID, $sanitizedFileName);

            if (move_uploaded_file($fileTmpName, $targetFile) && $insert_stmt->execute()) {
                echo "<script>alert('Image successfully uploaded!'); window.location.href = 'dashboard1.php';</script>";
            } else {
                echo "<script>alert('Error uploading image!'); window.location.href = 'dashboard1.php';</script>";
            }
        }
    } else {
        // Handle file upload errors
        echo "<script>alert('No file uploaded or there was an error!'); window.location.href = 'dashboard1.php';</script>";
    }
}


$sql = "SELECT * FROM bookings WHERE guest_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $guest_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guest Dashboard</title>
    <link rel="stylesheet" href="check-in.css">
</head>
<body>

<div class="container">
    <nav>
        <div class="logo">
            <h1><strong>Golden Grain Hotel</strong></h1>
        </div>

        <ul>
            
            <li><a href="?page=home">Home</a></li>
            <li><a href="?page=booking-list">Booking History</a></li>
            <li><a href="?page=payment">Payment</a></li>
            <li><a href="?page=announcement">Announcements</a></li>
            <!-- Log Out Button -->
            <form method="post">
                    <button type="submit" name="logout" class="btn" style="margin-left: 20px;">Log Out</button>
                </form>
        </ul>
        
    </nav>

    

    <!-- Content Sections -->
    <?php
    $page = isset($_GET['page']) ? $_GET['page'] : 'home';

    switch ($page) {
        case 'home':
            // Display the booking form
            ?>
            <div class="booking-form">
                <h1>Welcome, <?php echo htmlspecialchars($guest_name); ?>!</h1>
                <!-- Welcome note -->
                <p><strong>We are happy to have you with us! Feel free to book a room and enjoy your stay!</strong></p><br><br>
                
                
        
                <h2>Book a Room</h2>
                <hr>
                <form method="post">
                    <label for="dateFrom">Check-in Date:</label>
                    <input type="date" name="dateFrom" required>
                    
                    <label for="dateTo">Check-out Date:</label>
                    <input type="date" name="dateTo" required>
                    
                    <label for="roomType">Room:</label>
                    <select name="roomtype" required>
                        <?php
                        // Query to get room details from the database
                        $sql = "SELECT * FROM roomcat WHERE bit = 1";
                        $result = $conn->query($sql);
                        while ($row = $result->fetch_assoc()) {
                            $name = htmlspecialchars($row['name']);
                            $price = "PHP " . htmlspecialchars($row['price']);
                            echo "<option value='$name'>$name ($$price)</option>";
                        }
                        ?>
                    </select><br><br>
            
                    <label>Promo:</label><br>            
                    <select name="promo">
                        <?php
                        $sql = "SELECT * FROM promo WHERE bit = 1";
                        $result = $conn->query($sql);
                        while ($row = $result->fetch_assoc()) {
                            $name = htmlspecialchars($row['name']);
                            $promo_id = htmlspecialchars($row['ID']);
                            echo "<option value='$promo_id'>$name</option>";
                        }
                        ?>
                    </select><br><br>
            
                    <label>Mode Payment:</label><br>
                    <select name="mop" required>
                        <?php
                        $sql = "SELECT * FROM mop WHERE bit = 1";
                        $result = $conn->query($sql);
                        while ($row = $result->fetch_assoc()) {
                            $name = htmlspecialchars($row['name']);
                            $mop_id = htmlspecialchars($row['ID']);
                            echo "<option value='$mop_id'>$name</option>";
                        }
                        ?>
                    </select><br><br>
            
                    <button type="submit" name="book" class="btn">Book Now</button><br><br>
                </form>
            </div>
            <?php

            if (isset($_POST['logout'])) {

                session_unset(); 
                session_destroy(); 
                header("Location: login1.php");  
                exit();  
            }
            break;
        
        

            case 'booking-list':
                // Display the booking history
                ?><br><br>
                <h2>Booking List</h2>
                <hr>
                <form method="post">
                    <label for="month">Month:</label>
                    <select name="month" required>
                        <?php
                        for ($m = 1; $m <= 12; $m++) {
                            echo "<option value='$m'>" . date('F', mktime(0, 0, 0, $m, 1)) . "</option>";
                        }
                        ?>
                    </select>
            
                    <label for="year">Year:</label>
                    <select name="year" required>
                        <?php
                        $currentYear = date('Y');
                        for ($y = $currentYear - 5; $y <= $currentYear + 5; $y++) {
                            echo "<option value='$y'>$y</option>";
                        }
                        ?>
                    </select>
            
                    <button type="submit" name="searchBtn" class="btn">Search</button>
                </form>
            
                <table class="table">
                    <thead>
                        <tr>
                            <th>Booking ID</th>
                            <th>Room Type</th>
                            <th>Check-in</th>
                            <th>Check-out</th>
                            <th>Payment Method</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT b.*, m.* FROM bookings b
                                LEFT JOIN mop m ON b.mop = m.ID
                                WHERE guest_id = ? ORDER BY bookingID DESC LIMIT 5";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("i", $guest_id);
            
                        if (isset($_POST['searchBtn'])) {
                            $month = (int)$_POST['month'];
                            $year = (int)$_POST['year'];
                            $sql .= " AND (MONTH(arrival) = ? AND YEAR(arrival) = ?)";
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param("iii", $guest_id, $month, $year);
                        }
            
                        $stmt->execute();
                        $result = $stmt->get_result();
            
                        if ($result->num_rows > 0) {
                            while ($booking = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($booking['bookingID']) . "</td>";
                                echo "<td>" . htmlspecialchars($booking['name']) . "</td>";
                                echo "<td>" . htmlspecialchars($booking['arrival']) . "</td>";
                                echo "<td>" . htmlspecialchars($booking['depart']) . "</td>";
                                echo "<td>" . htmlspecialchars($booking['name']) . "</td>";
                                echo "<td>" . htmlspecialchars($booking['status']) . "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo '<tr><td colspan="6">No records found for the selected month and year.</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            
                <!-- Add a note about securing the 50% payment -->
                <div class="payment-note">
                    <p><strong>Important:</strong> After making a booking, please ensure you secure at least 50% of the payment to confirm your reservation. The remaining balance should be settled before your check-in date. Thank you for choosing us!</p>
                </div>
                <?php
                break;
            

                case 'payment':
                    // Display payment form
                    ?><br><br>
                    <h2>Upload Proof of Payment</h2>
                    <hr>
                
                    <!-- Note about payment and booking ID -->
                    <p><strong>Important Note:</strong> Please ensure that you enter a valid Booking ID, which can be found in your <a href="?page=booking-list">Booking List</a>. You need to upload the payment proof for your booking after securing at least 50% of the payment.</p>
                    
                    <form method="post" enctype="multipart/form-data">
                        <label for="bookingID">Booking ID:</label><br>
                        <input type="text" name="bookingID" placeholder="Enter the booking ID" required><br><br>
                
                        <label for="image">Upload Screenshot here:</label><br>
                        <input type="file" accept="image/png, image/jpeg, image/jpg" name="image"><br><br>
                
                        <button type="submit" name="upload_images" class="btn">Upload Image</button>
                    </form><br><br>
                    <?php
                    break;
                

        case 'announcement':
            // Display payment methods
            ?><br><br>
            <h2>Available Payment Methods</h2>
            <hr>
            <div class="box-container">
                <?php
                $select_products = mysqli_query($conn, "SELECT * FROM mop WHERE bit = 1");
                while ($fetch_product = mysqli_fetch_assoc($select_products)) {
                    ?>
                    <div class="box">
                        <img src="uploaded_img/<?php echo $fetch_product['img']; ?>" alt="">
                        <h3><?php echo $fetch_product['name']; ?></h3>
                    </div>
                    <?php
                }
                ?>
            </div>
            <?php
            break;
    }
    ?>
</div>

</body>
</html>




<style>

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f7f6;
            color: #333;
        }

        h1, h2, h3 {
            color: #444;
        }

        .container {
            width: 80%;
            margin: 0 auto;
            padding: 20px;
        }

        .booking-form input, .booking-form select {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }


        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .table th, .table td {
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
        }

        .table th {
            background-color: rgba(210, 137, 2, 0.812);
            color: black;
        }

        .table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .table tr:hover {
            background-color: #f1f1f1;
        }

        hr {
            border-top: 1.5px solid #d4af37;
            width: 300vh;
            margin: 0.5rem auto 1rem;
        }
        /* Navigation Bar Styles */
        nav {
            background-color: #333; /* Dark background for navigation */
            padding: 15px 0; /* Add padding to the top and bottom */
            margin-bottom: 20px; /* Space below the navigation */
        }

        nav ul {
            list-style: none; /* Remove bullet points */
            display: flex; /* Flexbox for horizontal layout */
            justify-content: center; /* Center the navigation items */
            margin: 0;
            padding: 0;
        }

        nav ul li {
            margin: 0 20px; /* Add space between navigation items */
        }

        nav ul li a {
            color: white; /* White text color */
            text-decoration: none; /* Remove underline from links */
            font-size: 18px; /* Font size */
            font-weight: bold; /* Bold text */
            padding: 10px 15px; /* Padding around the link */
            border-radius: 5px; /* Rounded corners */
            transition: background-color 0.3s ease, color 0.3s ease; /* Smooth transitions */
        }

        nav ul li a:hover {
            background-color: #d48c3b; /* Highlight color on hover */
            color: #fff; /* Ensure text remains white on hover */
        }

        nav ul li a:active {
            background-color: #f4a261; /* Slightly different color when active */
        }

        /* Add responsiveness to the nav bar for mobile devices */
        @media screen and (max-width: 768px) {
            nav ul {
                flex-direction: column; /* Stack items vertically on smaller screens */
                align-items: center; /* Center items */
            }

            nav ul li {
                margin-bottom: 10px; /* Add space between items */
            }
        }

        .logo h1 {
    font-family: 'Arial', sans-serif; /* Font style */
    font-size: 1.8em; /* Logo size */
    color: #FFD700; /* Golden color */
    text-align: center; /* Center the logo */
    margin-top: 1px; /* Space above the logo */
    margin-bottom: 20px; /* Space below the logo */
}

    </style>