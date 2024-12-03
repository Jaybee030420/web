<?php
@include 'config.php';


session_name('frontdesk');
session_start();

if (isset($_SESSION['username']) && isset($_SESSION['position'])) {

    $username = $_SESSION['username'];
    $position = $_SESSION['position'];
}else{
    header("Location: verify.php");  
}


if (isset($_POST['book'])) {
    // Get form values
    $arrival = $_POST['arrival']; 
    $depart = $_POST['depart'];  
    $discount = $_POST['discount']; 
    $promo = $_POST['promo'];
    $mop = $_POST['mop']; 
    $roomnumber = $_POST['roomnumber'];  
    $reference = $_POST['reference']; 
    $personCount = $_POST['person'];  
    $booking_id = $_POST['booking_id'];  
    $downpayment = $_POST['downpayment'];
    $booking_id = $_POST['booking_id'];
    $guest = $_POST['guest'];



    $currentDate = date("Y-m-d");

    if ($arrival < $currentDate) {
        echo "<script>alert('Check-in date cannot be in the past.'); window.history.back();</script>";
        exit;
    }

    if ($depart <= $arrival) {
        echo "<script>alert('Check-out date must be after the check-in date.'); window.history.back();</script>";
        exit;
    }

    // 2. Check room availability
    $roomCheckSql = "SELECT * FROM roomcat WHERE roomnumber = '$roomnumber' AND roombit = 0 AND bit = 1";
    $roomCheckResult = $conn->query($roomCheckSql);

    if ($roomCheckResult->num_rows == 0) {
        echo "<script>alert('Selected room is not available for check-in.'); window.history.back();</script>";
        exit;
    }

    // 3. Insert check-in record into the `checkin` table
    $sql = "INSERT INTO checkin (roomnum, arrival, depart, discount, promo, mop, reference, person, booking_id, downpayment, guest_id) 
            VALUES ('$roomnumber', '$arrival', '$depart', '$discount', '$promo', '$mop', '$reference', '$personCount', '$booking_id', '$downpayment', '$guest')";

    if ($conn->query($sql) === TRUE) {
        // Get the ID of the newly inserted check-in record
        $checkinID = $conn->insert_id;

        // 4. Update room status to 'occupied' (roombit = 1)
        $updateRoomSql = "UPDATE roomcat SET roombit = 1 WHERE roomnumber = '$roomnumber'";
        if (!$conn->query($updateRoomSql)) {
            echo "Error updating room status: " . $conn->error;
            exit;
        }

        // 5. Insert guest details into the `guests` table
        for ($i = 1; $i <= $personCount; $i++) {
            if (isset($_POST["name_$i"]) && isset($_POST["contact_$i"])) {
                $name = $conn->real_escape_string($_POST["name_$i"]);
                $contact = $conn->real_escape_string($_POST["contact_$i"]);

                // Insert each guest into the `guests` table
                $guestSql = "INSERT INTO guests (checkinID, name, contact) 
                             VALUES ('$checkinID', '$name', '$contact')";

                if (!$conn->query($guestSql)) {
                    echo "Error: " . $conn->error;
                    exit;
                }
            }
        }

        // 6. Update booking status to 'Done'
        $updateBookingStatusSql = "UPDATE bookings SET status = 'Done' WHERE bookingiD = '$booking_id'";

        if ($conn->query($updateBookingStatusSql) === TRUE) {
            // 7. Display success message and redirect to check-in page
            echo "<script>alert('Check-in successful!'); window.location.href = 'check-in.php';</script>";
        } else {
            echo "Error updating booking status: " . $conn->error;
        }
    } else {
        echo "Error: " . $conn->error;
    }
}












if (isset($_POST['remove_booking'])) {
    $bookingID = mysqli_real_escape_string($conn, $_POST['bookingID']);


    $updateQuery = "UPDATE bookings SET status = 'Failed' WHERE bookingID = '$bookingID'";

    if (mysqli_query($conn, $updateQuery)) {
        echo "<script>alert('Booking has been cancelled.'); window.location.href='Book.php';</script>";
    } else {
        echo "Error updating booking status: " . mysqli_error($conn);
    }
}







if (isset($_POST['cancel_booking'])) {
    $bookingID = mysqli_real_escape_string($conn, $_POST['bookingID']);


    $updateQuery = "UPDATE bookings SET status = 'Cancelled' WHERE bookingID = '$bookingID'";

    if (mysqli_query($conn, $updateQuery)) {
        echo "<script>alert('Booking has been cancelled.'); window.location.href='Book.php';</script>";
    } else {
        echo "Error updating booking status: " . mysqli_error($conn);
    }
}




if (isset($_POST['confirm_booking'])) {
    $bookingID = $_POST['bookingID'];
    $bookingQuery = "SELECT b.*, g.id AS guest_id, g.first_name, g.last_name FROM bookings b
                     LEFT JOIN guest g ON b.guest_id = g.id 
                     WHERE b.bookingID = ?";
    if ($stmt = $conn->prepare($bookingQuery)) {
        $stmt->bind_param("i", $bookingID);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            $bookingData = $result->fetch_assoc();
            $roomtype = $bookingData['roomtype'];
            $guestID = $bookingData['guest_id'];
            $dateFrom = $bookingData['arrival'];
            $dateTo = $bookingData['depart'];
            $guestName = empty($guestID) ? '' : $bookingData['first_name'] . ' ' . $bookingData['last_name'];
        }
        $stmt->close();
    }
}















if (isset($_POST['submit_downpayment'])) {
    $reference_number = htmlspecialchars($_POST['reference_number']);
    $amount = htmlspecialchars($_POST['amount']);
    $roomnum = htmlspecialchars($_POST['roomnum']);
    $guestName = htmlspecialchars($_POST['guestName']);
    $dateFrom = htmlspecialchars($_POST['dateFrom']);
    $dateTo = htmlspecialchars($_POST['dateTo']);
    $bookingID = $_POST['bookingID'];  


    $sql = "UPDATE bookings 
            SET reference = ?, amount = ?, status = 'Success', updated_at = CURRENT_TIMESTAMP 
            WHERE bookingID = ?";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("sii", $reference_number, $amount, $bookingID);

        if ($stmt->execute()) {
            echo "<script>alert('Payment recorded successfully.'); window.location.href='Book.php';</script>";
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Error preparing statement: " . $conn->error;
    }
}


?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GoldenGrainHotel</title>
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.9.0/fullcalendar.min.css" rel="stylesheet" />
    <link rel="shortcut icon" href="./assets/img/1.png" type="image/x-icon">
    <link rel="stylesheet" href="book.css">
</head>
<body>

<nav id="sidebar" class='mx-lt-5'>
    <div class="title">
        <h1 style="color:darkorange;">Golden Grain Hotel <p style="font-weight: 500; color:darkgoldenrod">frontdesk</p></h1>
    </div>
    <div class="sidebar-list">
        <ul>
            <li><a href="Dashboard.php" class=""><span class='icon-field'><i class="fa fa-book"></i>Dashboard</span></a></li>
            <li><a href="Book.php" class="cat"><span class='icon-field'><i class="fa fa-book"></i>Booking List</span></a></li>
            <li><a href="extra.php" class=""><span class='icon-field'><i class="fa fa-sign-in-alt"></i>Service</span></a></li>
            <li><a href="check-in.php" class=""><span class='icon-field'><i class="fa fa-sign-in-alt"></i>Check - in</span></a></li>
            <li><a href="check-out.php" class=""><span class='icon-field'><i class="fa fa-sign-out-alt"></i>Check - out</span></a></li>
            <li><a href="room.php" class=""><span class='icon-field'><i class="fa fa-list"></i>Room Availability</span></a></li>
        </ul>
    </div>
</nav>

<div class="container1">
    <div class="container" style="margin-top: 30px;">
        <h2 style="font-weight: 700;">Booking (Pending)</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Room Type</th>
                    <th>Arrival</th>
                    <th>Depart</th>
                    <th>Guest Name</th>
                    <th>Contact Number</th>
                    <th>Promo</th>
                    <th>MOP</th>
                    <th>Image</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $bookingSql = "SELECT b.*, bi.image_path, m.name, g.*, p.name as promo_name FROM bookings b
                               LEFT JOIN booking_images bi ON b.bookingID = bi.booking_id 
                               left join mop m on b.mop = m.ID
                               left join guest g on b.guest_id = g.id
                               left join promo p on b.promo = p.ID
                               WHERE b.status = 'Pending' ORDER BY b.bookingID LIMIT 3";
                $bookingResult = $conn->query($bookingSql);
                if ($bookingResult) {
                    while ($booking = $bookingResult->fetch_assoc()) {
                        $imagePath = !empty($booking['image_path']) ? $booking['image_path'] : null;
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($booking['roomtype']); ?></td>
                            <td><?php echo date('Y-m-d', strtotime($booking['arrival'])); ?></td>
                            <td><?php echo date('Y-m-d', strtotime($booking['depart'])); ?></td>
                            <td><?php echo htmlspecialchars($booking['last_name']);?></td>
                            <td><?php echo htmlspecialchars($booking['phone']); ?></td>
                            <td><?php echo htmlspecialchars($booking['promo_name']); ?></td>
                            <td><?php echo htmlspecialchars($booking['name']); ?></td>
                            <td>
                                <?php if ($imagePath): ?>
                                    <img src="uploaded_img/<?php echo htmlspecialchars($imagePath); ?>" height="100" alt="Booking Image">
                                    <br><br>
                                    <a href="uploaded_img/<?php echo htmlspecialchars($imagePath); ?>" download class="btn" style="margin-top: 5px;">Download</a>
                                <?php else: ?>
                                    <p style="color: red;">No Image available</p>
                                <?php endif; ?>
                            </td>
                            <td>
                                <form action="" method="post" id="confirmForm<?php echo $booking['bookingID']; ?>">
                                    <input type="hidden" name="bookingID" value="<?php echo htmlspecialchars($booking['bookingID']); ?>">
                                    <button class="btn" type="submit" name="remove_booking">Remove</button>
                                    <button class="btn" type="submit" name="confirm_booking">Confirm</button>
                                </form>
                            </td>
                        </tr>
                        <?php
                    }
                } else {
                    echo "<tr><td colspan='8'>No pending bookings found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div><br><br><br>
















    <?php

@include 'config.php';  
$sql = "SELECT * FROM discount WHERE bit = 1";
$discountResult = $conn->query($sql);



$sqlPromo = "SELECT * FROM promo WHERE bit = 1";
$promoResult = $conn->query($sqlPromo);
?>
    <div class="container" style="margin-top: 30px;">
        <h2 style="font-weight: 700;">Booking List</h2>
<table class="table">
    <thead>
        <tr>
            <th>Room Type</th>
            <th>Arrival</th>
            <th>Depart</th>
            <th>Guest Name</th>
            <th>Contact Number</th>
            <th>Promo</th>
            <th>Reference</th>
            <th>Amount</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $currentYear = date('Y');
        
        $selectedMonth = isset($_POST['month']) ? $_POST['month'] : '';
        
        $today = date('Y-m-d');

        $updatePastBookingsSql = "
            UPDATE bookings 
            SET status = 'Cancelled' 
            WHERE status = 'Success' 
            AND arrival < '$today'
        ";

        if ($conn->query($updatePastBookingsSql) === TRUE) {
            if ($selectedMonth) {
                $bookingSql = "SELECT b.*, bi.image_path, m.name, g.*, r.* 
                            FROM bookings b
                            LEFT JOIN booking_images bi ON b.bookingID = bi.booking_id 
                            LEFT JOIN mop m ON b.mop = m.ID
                            LEFT JOIN roomcat r ON b.roomtype = r.name
                            LEFT JOIN guest g ON b.guest_id = g.id
                            WHERE b.status = 'Success'
                            AND YEAR(b.arrival) = $currentYear
                            AND MONTH(b.arrival) = $selectedMonth
                            AND b.arrival >= '$today'  
                            ORDER BY b.arrival ASC";
            } else {
                $bookingSql = "SELECT b.*, bi.image_path, m.name, g.*, r.* 
                            FROM bookings b
                            LEFT JOIN booking_images bi ON b.bookingID = bi.booking_id 
                            LEFT JOIN mop m ON b.mop = m.ID
                            LEFT JOIN roomcat r ON b.roomtype = r.name
                            LEFT JOIN guest g ON b.guest_id = g.id
                            WHERE b.status = 'Success'
                            AND YEAR(b.arrival) = $currentYear
                            AND b.arrival >= '$today'  
                            ORDER BY b.arrival ASC";
            }

            $result = $conn->query($bookingSql);
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                }
            } else {
                echo "No bookings found for the selected criteria.";
            }
        } else {
            echo "Error updating past bookings: " . $conn->error;
        }


        
        $bookingResult = $conn->query($bookingSql);
        
        if ($bookingResult->num_rows > 0) {
            while ($booking = $bookingResult->fetch_assoc()) {
                $formattedArrival = date('F j, Y', strtotime($booking['arrival']));
                $formattedDepart = date('F j, Y', strtotime($booking['depart']));
        ?>
                <tr>
                    <td><?php echo htmlspecialchars($booking['roomtype']); ?></td>
                    <td><?php echo htmlspecialchars($formattedArrival); ?></td>
                    <td><?php echo htmlspecialchars($formattedDepart); ?></td>
                    <td><?php echo htmlspecialchars($booking['last_name']); ?></td>
                    <td><?php echo htmlspecialchars($booking['phone']); ?></td>
                    <td><?php echo htmlspecialchars($booking['promo']); ?></td>
                    <td><?php echo htmlspecialchars($booking['reference']); ?></td>
                    <td><?php echo htmlspecialchars($booking['amount']); ?></td>
                    <td>
                        <form action="" method="POST">
                            <input type="hidden" name="bookingID" value="<?php echo htmlspecialchars($booking['bookingID']); ?>">
                            <input type="hidden" name="roomtype" value="<?php echo htmlspecialchars($booking['roomtype']); ?>">
                            <input type="hidden" name="arrival" value="<?php echo htmlspecialchars($booking['arrival']); ?>">
                            <input type="hidden" name="depart" value="<?php echo htmlspecialchars($booking['depart']); ?>">
                            <input type="hidden" name="guestName" value="<?php echo htmlspecialchars($booking['last_name']); ?>">
                            <input type="hidden" name="guestPhone" value="<?php echo htmlspecialchars($booking['phone']); ?>">
                            <input type="hidden" name="reference" value="<?php echo htmlspecialchars($booking['reference']); ?>">
                            <input type="hidden" name="promo" value="<?php echo htmlspecialchars($booking['promo']); ?>">
                            <input type="hidden" name="amount" value="<?php echo htmlspecialchars($booking['amount']); ?>">
                            <input type="hidden" name="mop" value="<?php echo htmlspecialchars($booking['mop']); ?>">
                            <input type="hidden" name="guest" value="<?php echo htmlspecialchars($booking['guest_id']); ?>">


                            <button class="btn" type="submit" name="cancel_booking">Cancel</button>
                            <button class="btn" type="submit" name="checkin">Confirm</button>
                        </form>
                    </td>
                </tr>
        <?php
            }
        } else {
            echo "<tr><td colspan='9'>No successful bookings found for this month.</td></tr>";
        }
        ?>
    </tbody>
</table>





<?php
// Check if the user clicked the "Confirm" button
if (isset($_POST['checkin'])) {
    // Get booking details from POST data
    $roomtype = $_POST['roomtype'];
    $arrival = $_POST['arrival'];
    $depart = $_POST['depart'];
    $guestName = $_POST['guestName'];
    $guestPhone = $_POST['guestPhone'];
    $reference = $_POST['reference'];
    $downpayment = $_POST['amount'];
    $promo = $_POST['promo'];
    $bookingID = $_POST['bookingID'];
    $mop = $_POST['mop'];
    $guest = $_POST['guest'];

    // Escape the roomtype variable to prevent SQL injection
    $roomtype = mysqli_real_escape_string($conn, $roomtype);

    // Fetch room numbers based on roomtype, bit=1, and roombit=0
    $roomQuery = "SELECT roomnumber, name FROM roomcat 
                  WHERE name = '$roomtype' AND bit = 1 AND roombit = 0";
    $roomResult = $conn->query($roomQuery);

    // Now display the confirmation modal HTML (separate PHP from echo)
    echo '
    <div id="checkinModal" class="modal" style="display: block;">
        <div class="modal-content">
            <span class="close" style="margin-left: 165vh;" onclick="this.parentElement.parentElement.style.display=\'none\'">&times;</span>
            <h2>Confirm Check-in</h2>
            <p><strong>Room Type:</strong> ' . htmlspecialchars($roomtype) . '</p>
            <p><strong>Arrival Date:</strong> ' . htmlspecialchars($arrival) . '</p>
            <p><strong>Departure Date:</strong> ' . htmlspecialchars($depart) . '</p>
            <p><strong>Guest Name:</strong> ' . htmlspecialchars($guestName) . '</p>
            <p><strong>Contact Number:</strong> ' . htmlspecialchars($guestPhone) . '</p>
            <p><strong>Price:</strong> ' . htmlspecialchars($price) . '</p>
            <p><strong>Reference Number:</strong> ' . htmlspecialchars($reference) . '</p>
            <p><strong>Downpayment:</strong> ₱ ' . htmlspecialchars($downpayment) . '</p>

            <form action="" method="POST">
                <label for="roomNumber">Room Number: </label>
                <select class="jb" name="roomnumber" required>';

    // Output room numbers for the selected room type (bit=1, roombit=0)
    if ($roomResult->num_rows > 0) {
        while ($row = $roomResult->fetch_assoc()) {
            echo "<option value='" . htmlspecialchars($row['roomnumber']) . "'>" 
               . htmlspecialchars($row['name']) . " (" . htmlspecialchars($row['roomnumber']) . ")</option>";
        }
    } else {
        echo "<option value=''>No available rooms</option>";
    }

    echo '
                </select><br><br>

                <label>Discount:</label><br>
                <select class="jb" id="room" name="discount" required>';
                    // Output discount options (ensure $discountResult is fetched)
                    while ($row = $discountResult->fetch_assoc()) {
                        echo "<option value='" . $row['ID'] . "'>" . htmlspecialchars($row['name']) . "</option>";
                    }
echo '
                </select><br><br>

                <label for="numPeople">Number of People:</label>
                <input type="number" name="person" required><br><br>
                
                <input type="hidden" name="guest" value="' . htmlspecialchars($guest) . '">
                <input type="hidden" name="booking_id" value="' . htmlspecialchars($bookingID) . '">
                <input type="hidden" name="arrival" value="' . htmlspecialchars($arrival) . '">
                <input type="hidden" name="depart" value="' . htmlspecialchars($depart) . '">
                <input type="hidden" name="reference" value="' . htmlspecialchars($reference) . '">
                <input type="hidden" name="downpayment" value=" ' . htmlspecialchars($downpayment) . '">
                <input type="hidden" name="promo" value="' . htmlspecialchars($promo) . '">
                <input type="hidden" name="mop" value="' . htmlspecialchars($mop) . '">

                <button type="submit" class="btn" name="personCount">Confirm</button>
            </form>
        </div>
    </div>';
}
?>




</div>
    





<?php
// Check if the form has been submitted with personCount
if (isset($_POST['personCount'])) {
    $personCount = (int)$_POST['person']; // Number of people

    $arrival = $_POST['arrival'];  // Arrival date
    $depart = $_POST['depart'];  // Departure date
    $discount = $_POST['discount'];  // Discount ID
    $promo = $_POST['promo'];  // Promo ID
    $mop = $_POST['mop'];  // Mode of Payment
    $roomnumber = $_POST['roomnumber'];  // Room number
    $reference = $_POST['reference'];  // Reference
    $person = $_POST['person'];  // Number of persons
    $booking_id = $_POST['booking_id'];  // Booking ID
    $downpayment = $_POST['downpayment'];
    $guest = $_POST['guest'];



    // Check if the number of people is valid and greater than 0
    if ($personCount > 0): ?>
        <div id="personModal" class="modal" style="display: block;">
            <div class="modal-content">
                <span class="close" style="margin-left: 165vh;" onclick="this.parentElement.parentElement.style.display='none'">&times;</span>
                <h2>Golden Grain Hotel - Check-in</h2>
                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
                    
                    <!-- Hidden Fields -->
                    <input type="hidden" name="arrival" value="<?php echo htmlspecialchars($arrival); ?>">
                    <input type="hidden" name="depart" value="<?php echo htmlspecialchars($depart); ?>">
                    <input type="hidden" name="roomnumber" value="<?php echo htmlspecialchars($roomnumber); ?>">
                    <input type="hidden" name="booking_id" value="<?php echo htmlspecialchars($booking_id); ?>">
                    <input type="hidden" name="downpayment" value="<?php echo htmlspecialchars($downpayment); ?>">
                    <input type="hidden" name="promo" value="<?php echo htmlspecialchars($promo); ?>">
                    <input type="hidden" name="discount" value="<?php echo htmlspecialchars($discount); ?>">
                    <input type="hidden" name="mop" value="<?php echo htmlspecialchars($mop); ?>">
                    <input type="hidden" name="reference" value="<?php echo htmlspecialchars($reference); ?>">
                    <input type="hidden" name="person" value="<?php echo htmlspecialchars($person); ?>">
                    <input type="hidden" name="guest" value="<?php echo htmlspecialchars($guest); ?>">


                    <!-- Dynamic Fields for Person 1, Person 2, Person 3, etc. -->
                    <?php for ($i = 1; $i <= $personCount; $i++): ?>
                        <h3>Person <?php echo $i; ?></h3>
                        <label for="name_<?php echo $i; ?>">Name:</label><br>
                        <input type="text" id="name_<?php echo $i; ?>" name="name_<?php echo $i; ?>" required><br>
                        <label for="contact_<?php echo $i; ?>">Contact:</label><br>
                        <input type="number" id="contact_<?php echo $i; ?>" name="contact_<?php echo $i; ?>" required><br><br>
                    <?php endfor; ?>

                    <!-- Submit Button -->
                    <input type="submit" class="btn" name="book" value="Check-in">
                </form>
            </div>
        </div>
    <?php endif; ?>
<?php
}
?>























    <div class="container" style="margin-top: 30px;">
        <h2 style="font-weight: 700;">Booking (Cancelled)</h2>
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
        // Fetch bookings for the guest
        $sql = "SELECT * FROM bookings WHERE status = 'Cancelled' ORDER BY bookingID DESC LIMIT 5";
        $stmt = $conn->prepare($sql);

        // Handle search functionality
        if (isset($_POST['searchBtn'])) {
            $month = (int)$_POST['month'];
            $year = (int)$_POST['year'];
            $sql .= " AND (MONTH(arrival) = ? AND YEAR(arrival) = ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iii", $guest_id, $month, $year);
        }

        // Execute the query
        $stmt->execute();
        $result = $stmt->get_result();

        // Check if there are results
        if ($result->num_rows > 0) {
            while ($booking = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($booking['bookingID']) . "</td>";
                echo "<td>" . htmlspecialchars($booking['roomtype']) . "</td>";
                echo "<td>" . htmlspecialchars($booking['arrival']) . "</td>";
                echo "<td>" . htmlspecialchars($booking['depart']) . "</td>";
                echo "<td>" . htmlspecialchars($booking['mop']) . "</td>";
                echo "<td>" . htmlspecialchars($booking['status']) . "</td>";
                echo "</tr>";
            }
        } else {
            echo '<tr><td colspan="6">No records found for the selected month and year.</td></tr>';
        }
        ?>
        </tbody>
    </table>
    </div><br><br><br>
</div>











<?php if (isset($_POST['confirm_booking'])): ?>
    <div id="downpaymentModal" class="modal" style="display: block;">
        <div class="modal-content">
            <span class="close" style="margin-left: 165vh;" onclick="this.parentElement.parentElement.style.display='none'">&times;</span>
            <h2>Payment Form</h2>
            <div class="payment-details">
                <p><strong>Room Type:</strong> <?php echo htmlspecialchars($roomtype); ?></p>
                <p><strong>Guest Name:</strong>
                    <?php echo htmlspecialchars($guestName); ?>
                    <input type="hidden" name="guestName" value="<?php echo htmlspecialchars($guestName); ?>">
                </p>
                <p><strong>Check-in Date:</strong> <?php echo htmlspecialchars($dateFrom); ?></p>
                <p><strong>Check-out Date:</strong> <?php echo htmlspecialchars($dateTo); ?></p>
            </div>
            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" id="paymentForm">
                <label for="reference_number">Reference Number:</label>
                <input type="text" name="reference_number" id="reference_number" placeholder="Reference number" required><br><br>

                <label for="amount">Amount:</label>
                <input type="number" name="amount" id="amount" placeholder="Enter amount" required><br><br>

                <input type="hidden" name="bookingID" value="<?php echo htmlspecialchars($bookingID); ?>">
                <input type="hidden" name="roomnum" value="<?php echo htmlspecialchars($roomnum); ?>">
                <input type="hidden" name="dateFrom" value="<?php echo htmlspecialchars($dateFrom); ?>">
                <input type="hidden" name="dateTo" value="<?php echo htmlspecialchars($dateTo); ?>">

                <div>
                    <input type="checkbox" id="termsCheckbox" name="termsCheckbox" required>
                    <label for="termsCheckbox">Please double-check the information before submission. If you are sure, check this box to proceed.</label>
                </div><br>

                <input type="submit" class="btn" name="submit_downpayment" value="Confirm" id="submitBtn" disabled>
            </form>
        </div>
    </div>

















    <script>
        // JavaScript to enable/disable the submit button based on checkbox status
        document.getElementById('termsCheckbox').addEventListener('change', function() {
            var submitButton = document.getElementById('submitBtn');
            if (this.checked) {
                submitButton.disabled = false; 
            } else {
                submitButton.disabled = true; 
            }
        });
    </script>

<?php endif; ?>



<script>
    var modal = document.getElementById("downpaymentModal");
    var btn = document.getElementById("checkinBtn");

    function openModal() {
        modal.style.display = "block";
    }

    function closeModal() {
        modal.style.display = "none";
    }
</script>

</body>
</html>
